<?php
// =====================================================
// zalopay_test.php
// Giả lập ZaloPay thanh toán thành công (sandbox)
// =====================================================

require_once '../includes/db.php';
require_once 'zalopay_config.php';

$order_id = (int)($_GET['order_id'] ?? 0);

if (!$order_id) {
    die("Thiếu order_id");
}

// Lấy đơn hàng
$order = $conn->query("SELECT * FROM orders WHERE id=$order_id")->fetch_assoc();

if (!$order) {
    die("Không tìm thấy đơn hàng");
}

$app_trans_id = $order['app_trans_id'];

if (!$app_trans_id) {
    die("Đơn hàng chưa có app_trans_id");
}

// ====== TẠO DATA GIỐNG ZALOPAY CALLBACK ======
$data = [
    "app_id"       => ZALOPAY_APP_ID,
    "app_trans_id" => $app_trans_id,
    "zp_trans_id"  => rand(100000000, 999999999),
    "amount"       => (int)$order['total_amount'],
];

$json_data = json_encode($data);

// Tạo MAC bằng KEY2 (giống callback thật)
$mac = hash_hmac('sha256', $json_data, ZALOPAY_KEY2);

// Payload gửi đi
$post = json_encode([
    "data" => $json_data,
    "mac"  => $mac
]);

// ====== GỌI CALLBACK ======
$callback_url = ZALOPAY_CALLBACK_URL;

$ch = curl_init($callback_url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $post,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json']
]);

$response = curl_exec($ch);
$error    = curl_error($ch);

curl_close($ch);

// ====== KẾT QUẢ ======
echo "<h3>Kết quả callback:</h3>";
echo "<pre>$response</pre>";

if ($error) {
    echo "<p style='color:red'>Lỗi: $error</p>";
} else {
    echo "<p style='color:green'>✔ Giả lập thanh toán thành công!</p>";
    echo "<a href='zalopay_return.php?status=1&apptransid=$app_trans_id'>➡ Xem trang kết quả</a>";
}