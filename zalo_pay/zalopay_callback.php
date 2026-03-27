<?php
// =====================================================
// zalopay_callback.php
// ZaloPay tự động gọi file này (server-to-server)
// sau khi user thanh toán xong — dùng để cập nhật DB
// User KHÔNG thấy trang này
// =====================================================
require_once '../includes/db.php';
require_once 'zalopay_config.php';

// Nhận dữ liệu raw từ ZaloPay POST
$raw  = file_get_contents('php://input');
$cbdata = json_decode($raw, true);

$result = ['return_code' => -1, 'return_message' => 'unknown error'];

try {
    // Bước 1: Xác thực chữ ký MAC bằng KEY2
    $mac = hash_hmac('sha256', $cbdata['data'], ZALOPAY_KEY2);

    if ($mac !== $cbdata['mac']) {
        // Chữ ký sai → có thể bị giả mạo, bỏ qua
        $result = ['return_code' => -1, 'return_message' => 'mac not equal'];
    } else {
        // Bước 2: Chữ ký hợp lệ → xử lý dữ liệu
        $payment_data = json_decode($cbdata['data'], true);
        $app_trans_id = $conn->real_escape_string($payment_data['app_trans_id']);
        $zp_trans_id  = $conn->real_escape_string((string)($payment_data['zp_trans_id'] ?? ''));

        // Bước 3: Cập nhật trạng thái đơn hàng trong DB
        $conn->query("UPDATE orders
                      SET payment_status = 'paid',
                          zp_trans_id    = '$zp_trans_id',
                          status         = 'confirmed'
                      WHERE app_trans_id = '$app_trans_id'
                        AND payment_status = 'pending'");

        $result = ['return_code' => 1, 'return_message' => 'success'];
    }
} catch (Exception $e) {
    $result = ['return_code' => -1, 'return_message' => $e->getMessage()];
}

// Trả về JSON cho ZaloPay (bắt buộc)
header('Content-Type: application/json');
echo json_encode($result);
