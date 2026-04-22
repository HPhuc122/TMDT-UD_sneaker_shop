<?php
// api/get_saved_coupons.php
header('Content-Type: application/json');
require_once '../includes/db.php';

if (!isLoggedIn()) {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];
$cart = $_SESSION['cart'] ?? [];
$cart_total = 0;
$product_ids = [];
$category_ids = [];

foreach ($cart as $item) {
    $cart_total += $item['price'] * $item['qty'];
    $product_ids[] = (int)$item['product_id'];
    
    // Get category
    $pid = (int)$item['product_id'];
    $p_res = $conn->query("SELECT category_id FROM products WHERE id = $pid");
    $p_row = $p_res->fetch_assoc();
    if ($p_row) $category_ids[] = (int)$p_row['category_id'];
}

$now_time = time();

$sql = "SELECT dc.* FROM user_saved_coupons usc
        JOIN discount_codes dc ON usc.discount_code_id = dc.id
        WHERE usc.user_id = $user_id
        AND dc.status = 'active'";

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

    $is_applicable = false;
    $reason = "";

    // 1. Check Min Order
    if ($cart_total < $d['min_order_amount']) {
        $reason = "Chưa đủ đơn hàng tối thiểu " . formatPrice($d['min_order_amount']);
    } else {
        // 2. Check Scope
        if ($d['apply_to'] === 'all') {
            $is_applicable = true;
        } elseif ($d['apply_to'] === 'category') {
            $cat_res = $conn->query("SELECT category_id FROM discount_code_categories WHERE discount_code_id = $id");
            $allowed_cats = [];
            while ($cr = $cat_res->fetch_assoc()) $allowed_cats[] = (int)$cr['category_id'];
            
            if (array_intersect($category_ids, $allowed_cats)) $is_applicable = true;
            else $reason = "Không áp dụng cho các sản phẩm trong giỏ hàng";
        } elseif ($d['apply_to'] === 'product') {
            $prod_res = $conn->query("SELECT product_id FROM discount_code_products WHERE discount_code_id = $id");
            $allowed_prods = [];
            while ($pr = $prod_res->fetch_assoc()) $allowed_prods[] = (int)$pr['product_id'];
            
            if (array_intersect($product_ids, $allowed_prods)) $is_applicable = true;
            else $reason = "Không áp dụng cho các sản phẩm trong giỏ hàng";
        }
    }

    $coupons[] = [
        'id' => $d['id'],
        'code' => $d['code'],
        'discount_type' => $d['discount_type'],
        'discount_value' => (float)$d['discount_value'],
        'max_discount' => (float)$d['max_discount_amount'],
        'min_order' => (float)$d['min_order_amount'],
        'description' => ($d['discount_type'] === 'percentage' 
            ? "Giảm {$d['discount_value']}%" . ($d['max_discount_amount'] ? " tối đa " . formatPrice($d['max_discount_amount']) : "")
            : "Giảm " . formatPrice($d['discount_value'])),
        'is_applicable' => $is_applicable,
        'reason' => $reason
    ];
}

echo json_encode($coupons);
?>
