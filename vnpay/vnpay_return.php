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

// Kiểm tra chữ ký và xác thực thanh toán
if ($secureHash == $vnp_SecureHash && $_GET['vnp_ResponseCode'] == '00') {
    // ===== THANH TOÁN THÀNH CÔNG =====
    // Kiểm tra thông tin vnpay có trong session
    if (!isset($_SESSION['vnpay_info']) || !isset($_SESSION['user_id'])) {
        $error_msg = 'Thông tin thanh toán không hợp lệ. Vui lòng liên hệ hỗ trợ.';
    } else {
        $user_id = $_SESSION['user_id'];
        $vnpay_info = $_SESSION['vnpay_info'];
        $cart_backup = $_SESSION['cart_backup'] ?? [];
        
        // Tạo mã đơn hàng
        require_once '../includes/db.php';
        $order_code = generateCode('DH');
        $total_amount = (int)$vnpay_info['total_amount'];
        
        // Tạo đơn hàng
        $stmt = $conn->prepare("INSERT INTO orders (order_code,user_id,receiver_name,receiver_phone,shipping_address,ward,district,city,payment_method,total_amount,notes,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
        $status = 'confirmed';
        $receiver_name = $vnpay_info['receiver_name'];
        $receiver_phone = $vnpay_info['receiver_phone'];
        $shipping_address = $vnpay_info['shipping_address'];
        $ward = $vnpay_info['ward'];
        $district = $vnpay_info['district'];
        $city = $vnpay_info['city'];
        $payment_method = 'online';
        $notes = $vnpay_info['notes'];

        $stmt->bind_param(
            'sisssssssdss',
            $order_code,
            $user_id,
            $receiver_name,
            $receiver_phone,
            $shipping_address,
            $ward,
            $district,
            $city,
            $payment_method,
            $total_amount,
            $notes,
            $status
        );
        
        if ($stmt->execute()) {
            $order_id = $conn->insert_id;
            $payment_success = true;
            
            // Thêm chi tiết đơn hàng + cập nhật stock
            foreach ($cart_backup as $item) {
                $pid   = (int)$item['product_id'];
                $qty   = (int)$item['qty'];
                $price = (float)$item['price'];
                $conn->query("INSERT INTO order_details (order_id,product_id,quantity,unit_price) VALUES ($order_id,$pid,$qty,$price)");
                $conn->query("UPDATE products SET stock_quantity = stock_quantity - $qty WHERE id=$pid AND stock_quantity >= $qty");
            }
            
            // Lấy thông tin đơn hàng để hiển thị
            $ord = $conn->query("SELECT * FROM orders WHERE id=$order_id")->fetch_assoc();
            
            // Xóa session vnpay
            unset($_SESSION['vnpay_info']);
            unset($_SESSION['cart_backup']);
            $_SESSION['cart'] = [];
        } else {
            $error_msg = 'Lỗi khi tạo đơn hàng. Vui lòng liên hệ hỗ trợ.';
        }
    }
} else {
    // ===== THANH TOÁN THẤT BẠI HOẶC INVALID SIGNATURE =====
    $error_msg = 'Thanh toán không thành công. Vui lòng thử lại.';
    
    // Clear vnpay session nhưng giữ lại cart để user có thể thử lại
    unset($_SESSION['vnpay_info']);
    unset($_SESSION['cart_backup']);
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
                                <p class="fw-bold"><?= htmlspecialchars($_GET['vnp_TxnRef'] ?? '') ?></p>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">Số tiền thanh toán:</small>
                                <p class="fw-bold" style="color:#ff6b35"><?= number_format($_GET['vnp_Amount']/100 ?? 0); ?> VND</p>
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
                    <p class="text-muted"><strong>Lưu ý:</strong> Đơn hàng chưa được tạo. Sản phẩm trong giỏ hàng vẫn được giữ lại.</p>
                    
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>