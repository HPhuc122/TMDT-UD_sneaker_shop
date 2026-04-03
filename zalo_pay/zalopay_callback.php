<?php
// zalopay_callback.php — ZaloPay gọi ngầm, trừ tồn kho khi thành công
require_once '../includes/db.php';
require_once 'zalopay_config.php';

$raw    = file_get_contents('php://input');
$cbdata = json_decode($raw, true);
$result = ['return_code' => -1, 'return_message' => 'unknown error'];

try {
    // Xác thực MAC bằng KEY2 (server-to-server dùng KEY2)
    $mac = hash_hmac('sha256', $cbdata['data'], ZALOPAY_KEY2);

    if ($mac !== $cbdata['mac']) {
        $result = ['return_code' => -1, 'return_message' => 'mac not equal'];
    } else {
        $payment_data = json_decode($cbdata['data'], true);
        $app_trans_id = $conn->real_escape_string($payment_data['app_trans_id']);
        $zp_trans_id  = $conn->real_escape_string((string)($payment_data['zp_trans_id'] ?? ''));

        // Tìm đơn hàng
        $order = $conn->query(
            "SELECT * FROM orders WHERE app_trans_id='$app_trans_id' LIMIT 1"
        )->fetch_assoc();

        if ($order) {
            $hasPaymentStatusCol = hasTableColumn($conn, 'orders', 'payment_status');
            $hasPaymentDeadlineCol = hasTableColumn($conn, 'orders', 'payment_deadline');
            $conn->begin_transaction();
            try {
                $oid = (int)$order['id'];
                $locked = $conn->query("SELECT * FROM orders WHERE id=$oid FOR UPDATE")->fetch_assoc();

                if ($locked && $locked['status'] !== 'confirmed') {
                    if (!isPendingPaymentOrderStatus($conn, $locked['status'])) {
                        throw new Exception('Invalid order status for payment confirmation');
                    }

                    $setSql = "status='confirmed', zp_trans_id='$zp_trans_id'";
                    if ($hasPaymentStatusCol) $setSql .= ", payment_status='paid'";
                    if ($hasPaymentDeadlineCol) $setSql .= ", payment_deadline=NULL";
                    $fromStatus = $conn->real_escape_string($locked['status']);
                    $conn->query("UPDATE orders SET $setSql WHERE id=$oid AND status='$fromStatus'");
                    if ($conn->affected_rows !== 1) {
                        throw new Exception('Order already processed');
                    }
                }

                $conn->commit();
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
        }

        $result = ['return_code' => 1, 'return_message' => 'success'];
    }
} catch (Exception $e) {
    $result = ['return_code' => -1, 'return_message' => $e->getMessage()];
}

header('Content-Type: application/json');
echo json_encode($result);