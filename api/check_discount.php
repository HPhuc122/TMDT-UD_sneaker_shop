<?php
// api/check_discount.php
header('Content-Type: application/json');
require_once '../includes/db.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để sử dụng mã giảm giá.']);
    exit;
}

$code    = sanitize($conn, $_POST['code'] ?? '');
$user_id = $_SESSION['user_id'];
$cart    = $_SESSION['cart'] ?? [];

if (!$code) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mã giảm giá.']);
    exit;
}

if (empty($cart)) {
    echo json_encode(['success' => false, 'message' => 'Giỏ hàng đang trống.']);
    exit;
}

// Get discount info
$stmt = $conn->prepare("SELECT * FROM discount_codes WHERE code = ? AND status = 'active' LIMIT 1");
$stmt->bind_param('s', $code);
$stmt->execute();
$result = $stmt->get_result();
$discount = $result->fetch_assoc();

if (!$discount) {
    echo json_encode(['success' => false, 'message' => 'Mã giảm giá không tồn tại hoặc đã bị tắt.']);
    exit;
}

// 1. Check Dates
$now = date('Y-m-d H:i:s');
if ($discount['start_date'] && $now < $discount['start_date']) {
    echo json_encode(['success' => false, 'message' => 'Mã giảm giá chưa đến thời gian sử dụng.']);
    exit;
}
if ($discount['end_date'] && $now > $discount['end_date']) {
    echo json_encode(['success' => false, 'message' => 'Mã giảm giá đã hết hạn.']);
    exit;
}

// 2. Check Total Usage
if ($discount['max_uses'] !== null && $discount['total_used'] >= $discount['max_uses']) {
    echo json_encode(['success' => false, 'message' => 'Mã giảm giá đã hết lượt sử dụng.']);
    exit;
}

// 3. Check User Usage
$usage_stmt = $conn->prepare("SELECT COUNT(*) as c FROM discount_code_usages WHERE discount_code_id = ? AND user_id = ?");
$usage_stmt->bind_param('ii', $discount['id'], $user_id);
$usage_stmt->execute();
$usage_count = $usage_stmt->get_result()->fetch_assoc()['c'];

if ($usage_count >= $discount['max_uses_per_user']) {
    echo json_encode(['success' => false, 'message' => 'Bạn đã sử dụng mã này tối đa số lần cho phép.']);
    exit;
}

// Calculate cart total
$cart_total = 0;
foreach ($cart as $item) {
    $cart_total += $item['price'] * $item['qty'];
}

// 4. Check Minimum Order
if ($cart_total < $discount['min_order_amount']) {
    echo json_encode(['success' => false, 'message' => 'Đơn hàng chưa đạt giá trị tối thiểu ' . formatPrice($discount['min_order_amount']) . ' để dùng mã này.']);
    exit;
}

// 5. Check Category Restriction
if ($discount['apply_to'] === 'category') {
    // Get allowed categories
    $cat_res = $conn->query("SELECT category_id FROM discount_code_categories WHERE discount_code_id = {$discount['id']}");
    $allowed_cats = [];
    while ($cr = $cat_res->fetch_assoc()) {
        $allowed_cats[] = (int)$cr['category_id'];
    }

    $eligible_total = 0;
    foreach ($cart as $item) {
        // We need category_id for each item. It might be in session or we fetch from DB.
        // Assuming it's not in session, we fetch it.
        $pid = (int)$item['product_id'];
        $p_res = $conn->query("SELECT category_id FROM products WHERE id = $pid");
        $p_row = $p_res->fetch_assoc();
        if ($p_row && in_array((int)$p_row['category_id'], $allowed_cats)) {
            $eligible_total += $item['price'] * $item['qty'];
        }
    }

    if ($eligible_total <= 0) {
        echo json_encode(['success' => false, 'message' => 'Mã này chỉ áp dụng cho một số danh mục giày nhất định mà bạn không chọn.']);
        exit;
    }
} elseif ($discount['apply_to'] === 'product') {
    // Get allowed products
    $prod_res = $conn->query("SELECT product_id FROM discount_code_products WHERE discount_code_id = {$discount['id']}");
    $allowed_prods = [];
    while ($pr = $prod_res->fetch_assoc()) {
        $allowed_prods[] = (int)$pr['product_id'];
    }

    $eligible_total = 0;
    foreach ($cart as $item) {
        if (in_array((int)$item['product_id'], $allowed_prods)) {
            $eligible_total += $item['price'] * $item['qty'];
        }
    }

    if ($eligible_total <= 0) {
        echo json_encode(['success' => false, 'message' => 'Mã này chỉ áp dụng cho một số sản phẩm nhất định mà bạn không chọn.']);
        exit;
    }
}

// Calculate discount amount
$discount_amount = 0;
if ($discount['discount_type'] === 'fixed') {
    $discount_amount = $discount['discount_value'];
} else {
    $discount_amount = $cart_total * ($discount['discount_value'] / 100);
    // Cap by max_discount_amount
    if ($discount['max_discount_amount'] !== null && $discount_amount > $discount['max_discount_amount']) {
        $discount_amount = $discount['max_discount_amount'];
    }
}

// Cap discount at total amount
if ($discount_amount > $cart_total) {
    $discount_amount = $cart_total;
}

echo json_encode([
    'success' => true,
    'discount_id' => $discount['id'],
    'discount_amount' => $discount_amount,
    'discount_value_label' => ($discount['discount_type'] === 'percentage' ? $discount['discount_value'] . '%' : formatPrice($discount['discount_value'])),
    'new_total' => $cart_total - $discount_amount,
    'message' => 'Áp dụng mã giảm giá thành công!'
]);
?>
