<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Chỉ include config + db; db.php đã xử lý session_name + session_start
require_once("config.php");
require_once("../includes/db.php");

$vnp_SecureHash = $_GET['vnp_SecureHash'] ?? '';
$inputData = array();
foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}

unset($inputData['vnp_SecureHash']);
ksort($inputData);

$hashData = "";
$i = 0;
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashData .= urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
}

$secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

$payment_success = false;
$ord = null;
$error_msg = '';

// Kiểm tra chữ ký
if ($secureHash !== $vnp_SecureHash) {
    $error_msg = 'Chữ ký VNPay không hợp lệ.';
} else {
    $txnRef = sanitize($conn, $_GET['vnp_TxnRef'] ?? '');
    $responseCode = $_GET['vnp_ResponseCode'] ?? '';
    $transactionStatus = $_GET['vnp_TransactionStatus'] ?? '';
    $isSuccess = ($responseCode === '00' && ($transactionStatus === '' || $transactionStatus === '00'));

    if ($txnRef === '') {
        $error_msg = 'Thiếu mã giao dịch VNPay.';
    } else {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("SELECT * FROM orders WHERE order_code=? LIMIT 1 FOR UPDATE");
            $stmt->bind_param('s', $txnRef);
            $stmt->execute();
            $ord = $stmt->get_result()->fetch_assoc();

            if (!$ord) {
                throw new Exception('Không tìm thấy đơn hàng cần đối soát.');
            }

            // Đơn đã xử lý thành công trước đó (idempotent)
            if ($ord['status'] === 'confirmed') {
                $payment_success = true;
                $conn->commit();
            } elseif ($isSuccess) {
                if ($ord['status'] !== 'awaiting_payment') {
                    throw new Exception('Trạng thái đơn hàng không hợp lệ để xác nhận VNPay.');
                }

                $details = $conn->query("SELECT product_id, size_id, color_id, quantity FROM order_details WHERE order_id=" . (int)$ord['id']);
                while ($item = $details->fetch_assoc()) {
                    $pid = (int)$item['product_id'];
                    $size = (int)$item['size_id'];
                    $color = (int)$item['color_id'];
                    $qty = (int)$item['quantity'];

                    if ($pid <= 0 || $size <= 0 || $color <= 0 || $qty <= 0) {
                        throw new Exception('Dữ liệu chi tiết đơn hàng không hợp lệ.');
                    }

                    $stockStmt = $conn->prepare("SELECT stock_quantity FROM product_varieties WHERE product_id=? AND size_id=? AND color_id=? FOR UPDATE");
                    $stockStmt->bind_param('iii', $pid, $size, $color);
                    $stockStmt->execute();
                    $stockRow = $stockStmt->get_result()->fetch_assoc();

                    if (!$stockRow || (int)$stockRow['stock_quantity'] < $qty) {
                        throw new Exception('Sản phẩm không đủ tồn kho để hoàn tất thanh toán.');
                    }

                    $updateStockStmt = $conn->prepare("UPDATE product_varieties SET stock_quantity = stock_quantity - ? WHERE product_id=? AND size_id=? AND color_id=? AND stock_quantity >= ?");
                    $updateStockStmt->bind_param('iiiii', $qty, $pid, $size, $color, $qty);
                    $updateStockStmt->execute();
                    if ($updateStockStmt->affected_rows !== 1) {
                        throw new Exception('Không thể cập nhật tồn kho do cạnh tranh dữ liệu.');
                    }
                }

                $updateOrderStmt = $conn->prepare("UPDATE orders SET status='confirmed' WHERE id=? AND status='awaiting_payment'");
                $orderId = (int)$ord['id'];
                $updateOrderStmt->bind_param('i', $orderId);
                $updateOrderStmt->execute();

                if ($updateOrderStmt->affected_rows !== 1) {
                    throw new Exception('Đơn hàng đã được xử lý bởi giao dịch khác.');
                }

                $ord['status'] = 'confirmed';
                $payment_success = true;
                $conn->commit();
            } else {
                // Thất bại/hủy/lỗi -> giữ nguyên awaiting_payment, không trừ kho
                if ($ord['status'] !== 'pending') {
                    $keepStmt = $conn->prepare("UPDATE orders SET status='awaiting_payment' WHERE id=? AND status='awaiting_payment'");
                    $orderId = (int)$ord['id'];
                    $keepStmt->bind_param('i', $orderId);
                    $keepStmt->execute();
                    $ord['status'] = 'awaiting_payment';
                }
                $conn->commit();
                $error_msg = 'Thanh toán chưa thành công. Đơn hàng vẫn ở trạng thái chờ thanh toán.';
            }
        } catch (Exception $e) {
            $conn->rollback();
            $payment_success = false;
            $ord = null;
            $error_msg = $e->getMessage();
        }

        if ($ord['status'] === 'confirmed' || $ord['status'] === 'delivered') {
            $setSql = "status=status";
            if ($hasPaymentStatusCol) $setSql .= ", payment_status='paid'";
            if ($hasPaymentDeadlineCol) $setSql .= ", payment_deadline=NULL";
            if ($hasZpTransIdCol && $vnpTransactionNo !== '') $setSql .= ", zp_trans_id='" . $conn->real_escape_string($vnpTransactionNo) . "'";
            $conn->query("UPDATE orders SET $setSql WHERE id=$order_id");
        }

        $payment_success = true;
        $ord = $conn->query("SELECT * FROM orders WHERE id=$order_id")->fetch_assoc();
        $_SESSION['cart'] = [];
        unset($_SESSION['pending_online_order_id']);
    }
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Kết quả thanh toán VNPAY</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f5f5f5; }
        .payment-card { max-width: 600px; margin: 50px auto; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card payment-card border-0 shadow">
            <?php if ($payment_success && $ord) : ?>
                <!-- THANH TOÁN THÀNH CÔNG -->
                <div class="card-body text-center py-5">
                    <i class="bi bi-check-circle-fill" style="font-size:5rem;color:#28a745"></i>
                    <h3 class="text-success fw-bold mt-3">Thanh toán thành công!</h3>
                    <p class="text-muted mb-3">Đơn hàng đã được xác nhận.</p>
                    
                    <div class="card mx-auto text-start mt-4" style="max-width:500px">
                        <div class="card-header fw-bold bg-light">Thông tin đơn hàng</div>
                        <div class="card-body">
                            <div class="mb-2">
                                <small class="text-muted">Mã đơn hàng:</small>
                                <p class="fw-bold" style="color:#ff6b35"><?= htmlspecialchars($ord['order_code']) ?></p>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">Mã giao dịch VNPay:</small>
                                <p class="fw-bold"><?= htmlspecialchars((string)($_GET['vnp_TxnRef'] ?? '')) ?></p>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">Số tiền thanh toán:</small>
                                <p class="fw-bold" style="color:#ff6b35"><?= number_format(((int)($_GET['vnp_Amount'] ?? 0)) / 100); ?> VND</p>
                            </div>
                            <hr>
                            <div>
                                <small class="text-muted">Người nhận:</small>
                                <p><?= htmlspecialchars($ord['receiver_name']) ?> · <?= htmlspecialchars($ord['receiver_phone']) ?></p>
                            </div>
                            <div>
                                <small class="text-muted">Địa chỉ giao hàng:</small>
                                <p><?= htmlspecialchars($ord['shipping_address'].', '.$ord['ward'].', '.$ord['district'].', '.$ord['city']) ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 d-flex gap-2 justify-content-center">
                        <a href="../my_orders.php" class="btn btn-primary"><i class="bi bi-bag-check me-2"></i>Xem đơn hàng</a>
                        <a href="../index.php" class="btn btn-outline-secondary"><i class="bi bi-house me-2"></i>Trang chủ</a>
                    </div>
                </div>
            <?php else : ?>
                <!-- THANH TOÁN THẤT BẠI -->
                <div class="card-body text-center py-5">
                    <i class="bi bi-x-circle-fill" style="font-size:5rem;color:#dc3545"></i>
                    <h3 class="text-danger fw-bold mt-3">Thanh toán không thành công</h3>
                    <p class="text-muted mb-3">
                        <?php
                        if (empty($error_msg)) {
                            $error_codes = [
                                '07' => 'Giao dịch không được phép',
                                '09' => 'Thẻ/Tài khoản bị khóa',
                                '10' => 'Mã xác thực không đúng',
                                '11' => 'Hạn mức giao dịch vượt quá',
                                '12' => 'Thẻ hết hạn',
                                '13' => 'Sai mã PIN',
                                '24' => 'Khách hàng hủy giao dịch',
                                '51' => 'Tài khoản không đủ tiền',
                                '65' => 'Tài khoản bị khóa vì nhập sai PIN'
                            ];
                            $code = $_GET['vnp_ResponseCode'] ?? 'Unknown';
                            echo $error_codes[$code] ?? 'Lỗi thanh toán (Mã: '.$code.')';
                        } else {
                            echo $error_msg;
                        }
                        ?>
                    </p>
                    <p class="text-muted"><strong>Lưu ý:</strong> Đơn hàng vẫn ở trạng thái chờ thanh toán và chưa bị trừ tồn kho.</p>
                    
                    <div class="mt-4 d-flex gap-2 justify-content-center flex-wrap">
                        <a href="../my_orders.php" class="btn btn-primary"><i class="bi bi-bag-check me-2"></i>Xem đơn hàng</a>
                        <a href="../checkout.php?repay=<?= (int)($ord['id'] ?? 0) ?>" class="btn btn-outline-primary"><i class="bi bi-arrow-clockwise me-2"></i>Thử lại</a>
                        <a href="../index.php" class="btn btn-outline-secondary"><i class="bi bi-house me-2"></i>Trang chủ</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
