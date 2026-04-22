<?php
// api/get_coupons_by_codes.php
header('Content-Type: application/json');
require_once '../includes/db.php';

$codes = isset($_POST['codes']) ? json_decode($_POST['codes'], true) : [];
if (empty($codes)) {
    echo json_encode([]);
    exit;
}

$placeholders = implode(',', array_fill(0, count($codes), '?'));
$types = str_repeat('s', count($codes));

$stmt = $conn->prepare("SELECT * FROM discount_codes WHERE code IN ($placeholders) AND status = 'active'");
$stmt->bind_param($types, ...$codes);
$stmt->execute();
$res = $stmt->get_result();

$cart = $_SESSION['cart'] ?? [];
$cart_total = 0;
$product_ids = [];
$category_ids = [];

foreach ($cart as $item) {
    $cart_total += $item['price'] * $item['qty'];
    $product_ids[] = (int)$item['product_id'];
    $pid = (int)$item['product_id'];
    $p_res = $conn->query("SELECT category_id FROM products WHERE id = $pid");
    $p_row = $p_res->fetch_assoc();
    if ($p_row) $category_ids[] = (int)$p_row['category_id'];
}

$now = date('Y-m-d H:i:s');
$coupons = [];

while ($d = $res->fetch_assoc()) {
    $id = $d['id'];
    
    // Check validity
    if ($d['start_date'] && $now < $d['start_date']) continue;
    if ($d['end_date'] && $now > $d['end_date']) continue;
    if ($d['max_uses'] !== null && $d['total_used'] >= $d['max_uses']) continue;

    $is_applicable = false;
    $reason = "";

    if ($cart_total < $d['min_order_amount']) {
        $reason = "Chưa đủ đơn hàng tối thiểu " . formatPrice($d['min_order_amount']);
    } else {
        if ($d['apply_to'] === 'all') {
            $is_applicable = true;
        } elseif ($d['apply_to'] === 'category') {
            $cat_res = $conn->query("SELECT category_id FROM discount_code_categories WHERE discount_code_id = $id");
            $allowed_cats = [];
            while ($cr = $cat_res->fetch_assoc()) $allowed_cats[] = (int)$cr['category_id'];
            if (array_intersect($category_ids, $allowed_cats)) $is_applicable = true;
            else $reason = "Không áp dụng cho sản phẩm trong giỏ hàng";
        } elseif ($d['apply_to'] === 'product') {
            $prod_res = $conn->query("SELECT product_id FROM discount_code_products WHERE discount_code_id = $id");
            $allowed_prods = [];
            while ($pr = $prod_res->fetch_assoc()) $allowed_prods[] = (int)$pr['product_id'];
            if (array_intersect($product_ids, $allowed_prods)) $is_applicable = true;
            else $reason = "Không áp dụng cho sản phẩm trong giỏ hàng";
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
