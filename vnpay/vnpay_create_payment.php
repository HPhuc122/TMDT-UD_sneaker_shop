<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
date_default_timezone_set('Asia/Ho_Chi_Minh');

require_once("config.php");
require_once("../includes/db.php");

// db.php đã xử lý session_name + session_start rồi

if (!isLoggedIn()) {
    die("Lỗi: Phiên đăng nhập không hợp lệ");
}

$user_id = (int)$_SESSION['user_id'];
$order_id = (int)($_GET['order_id'] ?? 0);
if ($order_id <= 0) {
    die("Lỗi: Thiếu mã đơn hàng");
}

$order = $conn->query("SELECT * FROM orders WHERE id=$order_id AND user_id=$user_id")->fetch_assoc();
if (!$order) {
    die("Lỗi: Không tìm thấy đơn hàng");
}

if ($order['payment_method'] !== 'online' || !isPendingPaymentOrderStatus($conn, $order['status'])) {
    die("Lỗi: Trạng thái đơn hàng không hợp lệ để thanh toán VNPay");
}

$vnp_TxnRef = $order['order_code'];
$vnp_OrderInfo = "Thanh toan don hang " . $order['order_code'];
$vnp_Amount = (int)$order['total_amount'];
$vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

// Persist gateway reference if schema has app_trans_id
$hasAppTransId = ($conn->query("SHOW COLUMNS FROM orders LIKE 'app_trans_id'")->num_rows > 0);
if ($hasAppTransId) {
    $conn->query("UPDATE orders SET app_trans_id='" . $conn->real_escape_string($vnp_TxnRef) . "' WHERE id=$order_id");
}

// Kiểm tra số tiền
if ($vnp_Amount <= 0) {
    die("Số tiền không hợp lệ");
}

// Chuẩn bị dữ liệu gửi VNPAY
$inputData = array(
    "vnp_Version"    => $vnp_Version,
    "vnp_TmnCode"    => $vnp_TmnCode,
    "vnp_Amount"     => $vnp_Amount * 100,                    // BẮT BUỘC nhân 100
    "vnp_Command"    => $vnp_Command,
    "vnp_CreateDate" => date('YmdHis'),
    "vnp_CurrCode"   => $vnp_CurrCode,
    "vnp_IpAddr"     => $vnp_IpAddr,
    "vnp_Locale"     => $vnp_Locale,
    "vnp_OrderInfo"  => $vnp_OrderInfo,
    "vnp_OrderType"  => $vnp_OrderType,
    "vnp_ReturnUrl"  => $vnp_Returnurl,
    "vnp_TxnRef"     => $vnp_TxnRef,
    "vnp_ExpireDate" => date('YmdHis', strtotime('+15 minutes'))  // Hết hạn sau 15 phút
);

// Sắp xếp tham số theo thứ tự tăng dần (bắt buộc)
ksort($inputData);

$hashdata = "";
$query    = "";
$i = 0;
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashdata .= urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
    $query .= urlencode($key) . "=" . urlencode($value) . '&';
}

// Tạo chữ ký SecureHash
$vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);

// Tạo URL thanh toán hoàn chỉnh
$vnp_Url = $vnp_Url . "?" . $query . 'vnp_SecureHash=' . $vnpSecureHash;

// Chuyển hướng người dùng sang VNPAY
header('Location: ' . $vnp_Url);
exit;
?>