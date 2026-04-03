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
$order_id = null;
$error_msg = '';

$hasPaymentStatusCol = ($conn->query("SHOW COLUMNS FROM orders LIKE 'payment_status'")->num_rows > 0);
$hasZpTransIdCol = ($conn->query("SHOW COLUMNS FROM orders LIKE 'zp_trans_id'")->num_rows > 0);
$hasPaymentDeadlineCol = ($conn->query("SHOW COLUMNS FROM orders LIKE 'payment_deadline'")->num_rows > 0);

// Kiểm tra chữ ký và xác thực thanh toán
if ($secureHash == $vnp_SecureHash && ($_GET['vnp_ResponseCode'] ?? '') == '00') {
    // ===== THANH TOÁN THÀNH CÔNG =====
    $order_id = isset($_GET['vnp_TxnRef']) ? (int)$_GET['vnp_TxnRef'] : 0;
    $ord = $conn->query("SELECT * FROM orders WHERE id=$order_id AND payment_method='online' LIMIT 1")->fetch_assoc();

    if (!$ord) {
        $error_msg = 'Không tìm thấy đơn hàng online tương ứng giao dịch.';
    } else {
        $vnpTransactionNo = sanitize($conn, $_GET['vnp_TransactionNo'] ?? '');

        if ($ord['status'] !== 'confirmed' && $ord['status'] !== 'delivered') {
            $setSql = "status='confirmed'";
            if ($hasPaymentStatusCol) $setSql .= ", payment_status='paid'";
            if ($hasPaymentDeadlineCol) $setSql .= ", payment_deadline=NULL";
            if ($hasZpTransIdCol && $vnpTransactionNo !== '') $setSql .= ", zp_trans_id='" . $conn->real_escape_string($vnpTransactionNo) . "'";
            $conn->query("UPDATE orders SET $setSql WHERE id=$order_id");
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
} else {
    // ===== THANH TOÁN THẤT BẠI HOẶC INVALID SIGNATURE =====
    $error_msg = 'Thanh toán không thành công. Vui lòng thử lại.';

    $order_id = isset($_GET['vnp_TxnRef']) ? (int)$_GET['vnp_TxnRef'] : 0;
    if ($order_id > 0) {
        $ord = $conn->query("SELECT * FROM orders WHERE id=$order_id AND payment_method='online' LIMIT 1")->fetch_assoc();
        if ($ord && $hasPaymentStatusCol) {
            $conn->query("UPDATE orders SET payment_status='failed' WHERE id=$order_id");
        }
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
                    <p class="text-muted mb-3">Cảm ơn bạn đã mua hàng</p>
                    
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
                    <p class="text-muted"><strong>Lưu ý:</strong> Đơn hàng đã được lưu ở trạng thái chờ thanh toán. Bạn có thể thanh toán lại từ trang đơn hàng.</p>
                    
                    <div class="mt-4 d-flex gap-2 justify-content-center flex-wrap">
                        <a href="../cart.php" class="btn btn-primary"><i class="bi bi-bag me-2"></i>Quay lại giỏ hàng</a>
                        <a href="../checkout.php" class="btn btn-outline-primary"><i class="bi bi-arrow-clockwise me-2"></i>Thử lại</a>
                        <a href="../index.php" class="btn btn-outline-secondary"><i class="bi bi-house me-2"></i>Trang chủ</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
