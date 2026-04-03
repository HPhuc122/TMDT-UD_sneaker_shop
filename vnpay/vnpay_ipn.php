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

    $txnRefSafe = sanitize($conn, $vnp_TxnRef);
    $order = $conn->query("SELECT * FROM orders WHERE order_code='$txnRefSafe' LIMIT 1")->fetch_assoc();

    if (!$order) {
        $RspCode = "01";
        $Message = "Order not found";
    } else {
        $isSuccess = ($vnp_ResponseCode == '00' && $vnp_TransactionStatus == '00');
        $conn->begin_transaction();
        try {
            $orderId = (int)$order['id'];
            $orderRow = $conn->query("SELECT * FROM orders WHERE id=$orderId FOR UPDATE")->fetch_assoc();

            if ($orderRow['status'] === 'confirmed') {
                $RspCode = "00";
                $Message = "Success";
                $conn->commit();
            } elseif ($isSuccess) {
                if ($orderRow['status'] !== 'awaiting_payment') {
                    throw new Exception('Invalid order status for payment confirmation');
                }

                $details = $conn->query("SELECT product_id, size_id, color_id, quantity FROM order_details WHERE order_id=$orderId");
                while ($item = $details->fetch_assoc()) {
                    $pid = (int)$item['product_id'];
                    $size = (int)$item['size_id'];
                    $color = (int)$item['color_id'];
                    $qty = (int)$item['quantity'];

                    $stock = $conn->query("SELECT stock_quantity FROM product_varieties WHERE product_id=$pid AND size_id=$size AND color_id=$color FOR UPDATE")->fetch_assoc();
                    if (!$stock || (int)$stock['stock_quantity'] < $qty) {
                        throw new Exception('Insufficient stock');
                    }

                    $conn->query("UPDATE product_varieties SET stock_quantity = stock_quantity - $qty WHERE product_id=$pid AND size_id=$size AND color_id=$color AND stock_quantity >= $qty");
                    if ($conn->affected_rows !== 1) {
                        throw new Exception('Stock update race condition');
                    }
                }

                $conn->query("UPDATE orders SET status='confirmed' WHERE id=$orderId AND status='awaiting_payment'");
                if ($conn->affected_rows !== 1) {
                    throw new Exception('Order already processed');
                }
                $RspCode = "00";
                $Message = "Success";
                $conn->commit();
            } else {
                // Thất bại/hủy/lỗi -> giữ awaiting_payment
                $conn->query("UPDATE orders SET status='awaiting_payment' WHERE id=$orderId AND status='awaiting_payment'");
                $RspCode = "00";
                $Message = "Success";
                $conn->commit();
            }
        } catch (Exception $e) {
            $conn->rollback();
            $RspCode = "99";
            $Message = "Transaction failed";
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