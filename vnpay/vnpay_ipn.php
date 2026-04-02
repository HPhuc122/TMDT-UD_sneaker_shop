<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
date_default_timezone_set('Asia/Ho_Chi_Minh');

// PHẢI gọi session_name TRƯỚC session_start() để match với USER_SESSION_NAME từ db.php (nếu cần)
define('USER_SESSION_NAME',  'sneaker_user_sess');
if (session_status() === PHP_SESSION_NONE) {
    session_name(USER_SESSION_NAME);
    session_start();
}

require_once("config.php");

// ==================== Nhận dữ liệu từ VNPAY ====================
$inputData = array();
foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}

$vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
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

if ($secureHash == $vnp_SecureHash) {
    $vnp_TxnRef      = $inputData['vnp_TxnRef'];
    $vnp_Amount      = $inputData['vnp_Amount'] / 100;   // Chia lại 100
    $vnp_ResponseCode = $inputData['vnp_ResponseCode'];
    $vnp_TransactionStatus = $inputData['vnp_TransactionStatus'];

    // TODO: Kiểm tra trong database
    // Ví dụ: $order = getOrderById($vnp_TxnRef);

    if ($vnp_ResponseCode == '00' && $vnp_TransactionStatus == '00') {
        // Thanh toán thành công → Cập nhật trạng thái đơn hàng thành "Đã thanh toán"
        // updateOrderStatus($vnp_TxnRef, 'paid');
        $RspCode = "00";
        $Message = "Success";
    } else {
        // Thanh toán thất bại hoặc đang xử lý
        $RspCode = "99";
        $Message = "Transaction failed";
    }
} else {
    $RspCode = "97";
    $Message = "Invalid checksum";
}

// Trả về cho VNPAY
echo json_encode([
    'RspCode' => $RspCode,
    'Message' => $Message
]);
?>