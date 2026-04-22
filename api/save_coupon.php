<?php
// api/save_coupon.php
header('Content-Type: application/json');
require_once '../includes/db.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để lưu mã.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$coupon_id = isset($_POST['coupon_id']) ? (int)$_POST['coupon_id'] : 0;

if (!$coupon_id) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
    exit;
}

// Check if coupon exists and is active
$check = $conn->query("SELECT id FROM discount_codes WHERE id = $coupon_id AND status = 'active'");
if ($check->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Mã giảm giá không khả dụng.']);
    exit;
}

// Save
$stmt = $conn->prepare("INSERT IGNORE INTO user_saved_coupons (user_id, discount_code_id) VALUES (?, ?)");
$stmt->bind_param('ii', $user_id, $coupon_id);

if ($stmt->execute()) {
    if ($conn->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Đã lưu mã thành công!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Mã này đã được lưu trước đó.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $conn->error]);
}
?>
