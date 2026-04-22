<?php
// api/get_product_coupons.php
header('Content-Type: application/json');
require_once '../includes/db.php';

$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
if (!$product_id) {
    echo json_encode([]);
    exit;
}

// Get product category
$p_res = $conn->query("SELECT category_id FROM products WHERE id = $product_id");
$p_row = $p_res->fetch_assoc();
$category_id = $p_row ? (int)$p_row['category_id'] : 0;

$now_time = time();

// Fetch potential coupons for this product or category or all
$sql = "SELECT d.* FROM discount_codes d 
        WHERE d.status = 'active'";

$res = $conn->query($sql);
$coupons = [];

while ($d = $res->fetch_assoc()) {
    $id = $d['id'];
    $valid = false;

    // 1. Filter Scope (Product / Category)
    if ($d['apply_to'] === 'all') {
        $valid = true;
    } elseif ($d['apply_to'] === 'category') {
        $cat_check = $conn->query("SELECT 1 FROM discount_code_categories WHERE discount_code_id = $id AND category_id = $category_id");
        if ($cat_check->num_rows > 0) $valid = true;
    } elseif ($d['apply_to'] === 'product') {
        $prod_check = $conn->query("SELECT 1 FROM discount_code_products WHERE discount_code_id = $id AND product_id = $product_id");
        if ($prod_check->num_rows > 0) $valid = true;
    }

    if (!$valid) continue;

    // 2. Filter Date and Usage in PHP
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
    
    $coupons[] = [
        'id' => $d['id'],
        'code' => $d['code'],
        'discount_type' => $d['discount_type'],
        'discount_value' => (float)$d['discount_value'],
        'max_discount' => (float)$d['max_discount_amount'],
        'min_order' => (float)$d['min_order_amount'],
        'end_date' => $d['end_date'],
        'is_saved' => $d['is_saved'],
        'description' => ($d['discount_type'] === 'percentage' 
            ? "Giảm {$d['discount_value']}%" . ($d['max_discount_amount'] ? " tối đa " . formatPrice($d['max_discount_amount']) : "")
            : "Giảm " . formatPrice($d['discount_value']))
    ];
}

echo json_encode($coupons);
?>
