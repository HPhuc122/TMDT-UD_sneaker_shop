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
            $conn->begin_transaction();
            try {
                $oid = (int)$order['id'];
                $locked = $conn->query("SELECT * FROM orders WHERE id=$oid FOR UPDATE")->fetch_assoc();

                if ($locked && $locked['status'] !== 'confirmed') {
                    $items = $conn->query(
                        "SELECT product_id, size_id, color_id, quantity FROM order_details WHERE order_id=$oid"
                    );
                    while ($item = $items->fetch_assoc()) {
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

                    $conn->query("UPDATE orders SET zp_trans_id='$zp_trans_id', status='confirmed' WHERE id=$oid AND status='awaiting_payment'");
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