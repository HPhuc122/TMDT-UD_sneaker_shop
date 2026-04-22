<?php
// api/get_all_active_coupons.php
header('Content-Type: application/json');
require_once '../includes/db.php';

$now_time = time();

$sql = "SELECT d.* FROM discount_codes d WHERE d.status = 'active'";
$res = $conn->query($sql);
$coupons = [];

while ($d = $res->fetch_assoc()) {
    $id = $d['id'];
    
    // Filter Date and Usage in PHP
    $start_time = $d['start_date'] ? strtotime($d['start_date']) : null;
    $end_time   = $d['end_date'] ? strtotime($d['end_date']) : null;
    $max_uses   = $d['max_uses'] !== null ? (int)$d['max_uses'] : null;
    $used_count = (int)$d['total_used'];

    if ($start_time !== null && $now_time < $start_time) continue;
    if ($end_time !== null && $now_time > $end_time) continue;
    if ($max_uses !== null && $used_count >= $max_uses) continue;

    $d['is_saved'] = false;
    if (isLoggedIn()) {
        $user_id = $_SESSION['user_id'];
        $save_check = $conn->query("SELECT 1 FROM user_saved_coupons WHERE user_id = $user_id AND discount_code_id = $id");
        if ($save_check->num_rows > 0) $d['is_saved'] = true;
    }
    
    $scope_text = "Toàn sàn";
    if ($d['apply_to'] === 'category') $scope_text = "Một số danh mục";
    if ($d['apply_to'] === 'product') $scope_text = "Một số sản phẩm";

    $coupons[] = [
        'id' => $d['id'],
        'code' => $d['code'],
        'discount_type' => $d['discount_type'],
        'discount_value' => (float)$d['discount_value'],
        'max_discount' => (float)$d['max_discount_amount'],
        'min_order' => (float)$d['min_order_amount'],
        'end_date' => $d['end_date'],
        'is_saved' => $d['is_saved'],
        'scope_text' => $scope_text,
        'description' => ($d['discount_type'] === 'percentage' 
            ? "Giảm {$d['discount_value']}%" . ($d['max_discount_amount'] ? " tối đa " . formatPrice($d['max_discount_amount']) : "")
            : "Giảm " . formatPrice($d['discount_value']))
    ];
}

echo json_encode($coupons);
?>
