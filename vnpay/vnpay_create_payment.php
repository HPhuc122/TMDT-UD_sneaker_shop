<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
date_default_timezone_set('Asia/Ho_Chi_Minh');

require_once("config.php");
require_once("../includes/db.php");

// db.php đã xử lý session_name + session_start rồi

// ==================== Nhận dữ liệu từ form checkout ====================
// Lấy từ session (từ checkout.php)
if (!isset($_SESSION['vnpay_info'])) {
    die("Lỗi: Thông tin thanh toán không hợp lệ");
}

$vnpay_info = $_SESSION['vnpay_info'];
$vnp_TxnRef = time();
$vnp_OrderInfo = "Thanh toan don hang";
$vnp_Amount = (int)$vnpay_info['total_amount'];
$vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

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