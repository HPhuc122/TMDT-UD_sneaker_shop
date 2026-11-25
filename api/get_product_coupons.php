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
if (!$p_res) {
    http_response_code(500);
    echo json_encode(['error' => 'Không thể tải thông tin sản phẩm.']);
    exit;
}
$p_row = $p_res->fetch_assoc();
if (!$p_row) {
    http_response_code(404);
    echo json_encode(['error' => 'Sản phẩm không tồn tại.']);
    exit;
}
$category_id = $p_row ? (int)$p_row['category_id'] : 0;

$now_time = time();

 // Fetch potential coupons for this product or category or all (active)
 $sql = "SELECT d.* FROM discount_codes d WHERE d.status = 'active'";
 $res = $conn->query($sql);
 if (!$res) {
     http_response_code(500);
     echo json_encode(['error' => 'Không thể tải danh sách mã khuyến mãi.']);
     exit;
 }
 $coupons = [];

 while ($d = $res->fetch_assoc()) {
     $id = (int)$d['id'];
     $valid = false;

     // 1. Scope check (product/category/all)
     $apply_to = isset($d['apply_to']) ? $d['apply_to'] : 'all';
     if ($apply_to === 'all') {
         $valid = true;
     } elseif ($apply_to === 'category') {
         $cat_check = $conn->query("SELECT 1 FROM discount_code_categories WHERE discount_code_id = $id AND category_id = $category_id");
         if ($cat_check && $cat_check->num_rows > 0) $valid = true;
     } elseif ($apply_to === 'product') {
         $prod_check = $conn->query("SELECT 1 FROM discount_code_products WHERE discount_code_id = $id AND product_id = $product_id");
         if ($prod_check && $prod_check->num_rows > 0) $valid = true;
     }

     if (!$valid) continue;

     // 2. Date and usage checks use the same exact timestamps as checkout.
     if (!isDiscountCurrentlyValid($d, $now_time)) continue;

     // 3. saved state
     $d['is_saved'] = false;
     if (isLoggedIn()) {
         $user_id = (int)$_SESSION['user_id'];
         $save_check = $conn->query("SELECT 1 FROM user_saved_coupons WHERE user_id = $user_id AND discount_code_id = $id");
         if ($save_check && $save_check->num_rows > 0) $d['is_saved'] = true;
     }

     $min_order = isset($d['min_order_amount']) && is_numeric($d['min_order_amount']) ? (float)$d['min_order_amount'] : 0.0;
     $max_discount = isset($d['max_discount_amount']) && is_numeric($d['max_discount_amount']) ? (float)$d['max_discount_amount'] : 0.0;
     $discount_value = isset($d['discount_value']) && is_numeric($d['discount_value']) ? (float)$d['discount_value'] : 0.0;

     $description = '';
     if ($d['discount_type'] === 'percentage') {
         $description = "Giảm " . rtrim(rtrim(number_format($discount_value,2), '0'), '.') . "%";
         if ($max_discount) $description .= " tối đa " . formatPrice($max_discount);
     } else {
         $description = "Giảm " . formatPrice($discount_value);
     }

     $coupons[] = [
         'id' => $id,
         'code' => $d['code'],
         'apply_to' => $apply_to,
         'discount_type' => $d['discount_type'],
         'discount_value' => $discount_value,
         'max_discount' => $max_discount,
         'min_order' => $min_order,
         'end_date' => $d['end_date'],
         'is_saved' => (bool)$d['is_saved'],
         'description' => $description
     ];
 }

 echo json_encode($coupons);
?>
