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
require_once("../includes/db.php");

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

$hasPaymentStatusCol = ($conn->query("SHOW COLUMNS FROM orders LIKE 'payment_status'")->num_rows > 0);
$hasZpTransIdCol = ($conn->query("SHOW COLUMNS FROM orders LIKE 'zp_trans_id'")->num_rows > 0);
$hasPaymentDeadlineCol = ($conn->query("SHOW COLUMNS FROM orders LIKE 'payment_deadline'")->num_rows > 0);

if ($secureHash == $vnp_SecureHash) {
    $vnp_TxnRef = isset($inputData['vnp_TxnRef']) ? (int)$inputData['vnp_TxnRef'] : 0;
    $vnp_Amount = isset($inputData['vnp_Amount']) ? (int)$inputData['vnp_Amount'] : 0; // amount * 100
    $vnp_ResponseCode = $inputData['vnp_ResponseCode'] ?? '';
    $vnp_TransactionStatus = $inputData['vnp_TransactionStatus'] ?? '';
    $vnp_TransactionNo = sanitize($conn, $inputData['vnp_TransactionNo'] ?? '');

    if ($vnp_TxnRef <= 0) {
        $RspCode = "01";
        $Message = "Order not found";
    } else {
        $order = $conn->query("SELECT * FROM orders WHERE id=$vnp_TxnRef AND payment_method='online' LIMIT 1")->fetch_assoc();
        if (!$order) {
            $RspCode = "01";
            $Message = "Order not found";
        } elseif (((int)$order['total_amount'] * 100) !== $vnp_Amount) {
            $RspCode = "04";
            $Message = "Invalid amount";
        } elseif ($order['status'] === 'confirmed' || $order['status'] === 'delivered') {
            $setSql = "status=status";
            if ($hasPaymentStatusCol) $setSql .= ", payment_status='paid'";
            if ($hasPaymentDeadlineCol) $setSql .= ", payment_deadline=NULL";
            if ($hasZpTransIdCol && $vnp_TransactionNo !== '') $setSql .= ", zp_trans_id='" . $conn->real_escape_string($vnp_TransactionNo) . "'";
            $conn->query("UPDATE orders SET $setSql WHERE id=$vnp_TxnRef");
            $RspCode = "02";
            $Message = "Order already confirmed";
        } elseif ($vnp_ResponseCode == '00' && $vnp_TransactionStatus == '00') {
            $setSql = "status='confirmed'";
            if ($hasPaymentStatusCol) $setSql .= ", payment_status='paid'";
            if ($hasPaymentDeadlineCol) $setSql .= ", payment_deadline=NULL";
            if ($hasZpTransIdCol && $vnp_TransactionNo !== '') $setSql .= ", zp_trans_id='" . $conn->real_escape_string($vnp_TransactionNo) . "'";
            $conn->query("UPDATE orders SET $setSql WHERE id=$vnp_TxnRef");

            $RspCode = "00";
            $Message = "Success";
        } else {
            if ($hasPaymentStatusCol) {
                $conn->query("UPDATE orders SET payment_status='failed' WHERE id=$vnp_TxnRef");
            }
            $RspCode = "00";
            $Message = "Confirm Success";
        }
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