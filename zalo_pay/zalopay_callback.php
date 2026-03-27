<?php
// zalopay_callback.php — ZaloPay server-to-server callback (user không thấy)
require_once '../includes/db.php';
require_once 'zalopay_config.php';

$raw    = file_get_contents('php://input');
$cbdata = json_decode($raw, true);
$result = ['return_code' => -1, 'return_message' => 'unknown error'];

try {
    // Xác thực MAC bằng KEY2
    $mac = hash_hmac('sha256', $cbdata['data'], ZALOPAY_KEY2);
    if ($mac !== $cbdata['mac']) {
        $result = ['return_code' => -1, 'return_message' => 'mac not equal'];
    } else {
        $payment_data = json_decode($cbdata['data'], true);
        $app_trans_id = $conn->real_escape_string($payment_data['app_trans_id']);

        // Chỉ cập nhật status = confirmed, không cần thêm cột mới
        $conn->query("UPDATE orders SET status='confirmed'
                      WHERE app_trans_id='$app_trans_id'
                        AND status='pending'");

        // Xóa giỏ hàng của user (nếu có thể xác định)
        // (callback không có session, giỏ hàng sẽ bị xóa ở zalopay_return.php)

        $result = ['return_code' => 1, 'return_message' => 'success'];
    }
} catch (Exception $e) {
    $result = ['return_code' => -1, 'return_message' => $e->getMessage()];
}

header('Content-Type: application/json');
echo json_encode($result);