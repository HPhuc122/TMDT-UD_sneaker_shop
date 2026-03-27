<?php
// zalopay_test.php — Sandbox only: mô phỏng kết quả ZaloPay
// CHỈ dùng trong môi trường test/sandbox, KHÔNG đưa lên production
require_once '../includes/db.php';
require_once 'zalopay_config.php';

if (!isLoggedIn()) redirect('../login.php');

$order_id     = (int)($_GET['order_id'] ?? 0);
$app_trans_id = $_GET['app_trans_id'] ?? '';
$result       = $_GET['result'] ?? 'fail'; // 'success' hoặc 'fail'
$user_id      = $_SESSION['user_id'];

// Xác nhận đơn hàng thuộc về user đang đăng nhập
$order = $conn->query("SELECT * FROM orders WHERE id=$order_id AND user_id=$user_id")->fetch_assoc();
if (!$order) redirect('../cart.php');

if ($result === 'success') {
    // Cập nhật app_trans_id và status
    $ats = $conn->real_escape_string($app_trans_id);
    $conn->query("UPDATE orders SET app_trans_id='$ats', status='confirmed' WHERE id=$order_id");
    // Xóa giỏ hàng
    $_SESSION['cart'] = [];
    redirect('../checkout.php?zp_success=' . $order_id);
} else {
    // Thất bại: giữ nguyên, giỏ hàng vẫn còn
    redirect('../checkout.php?zp_fail=1&order_id=' . $order_id);
}