<?php
// zalopay_return.php — ZaloPay redirect user về đây sau khi thanh toán
require_once '../includes/db.php';
require_once 'zalopay_config.php';

$status       = (int)($_GET['status'] ?? -1);
$app_trans_id = $conn->real_escape_string($_GET['apptransid'] ?? '');

// Xác thực checksum
$checksum_data = ($_GET['appid']          ?? '') . '|' .
                 ($_GET['apptransid']     ?? '') . '|' .
                 ($_GET['pmcid']          ?? '') . '|' .
                 ($_GET['bankcode']       ?? '') . '|' .
                 ($_GET['amount']         ?? '') . '|' .
                 ($_GET['discountamount'] ?? '') . '|' .
                 ($_GET['status']         ?? '');
$expected = hash_hmac('sha256', $checksum_data, ZALOPAY_KEY1);
$valid    = ($expected === ($_GET['checksum'] ?? ''));

// Lấy đơn hàng theo app_trans_id
$order = null;
if ($app_trans_id) {
    $order = $conn->query("SELECT * FROM orders WHERE app_trans_id='$app_trans_id'")->fetch_assoc();
}

$success = ($status == 1 && $valid);

if ($success && $order) {
    // ✅ Thành công: cập nhật confirmed + xóa giỏ hàng
    $oid = (int)$order['id'];
    $conn->query("UPDATE orders SET status='confirmed' WHERE id=$oid");
    $_SESSION['cart'] = [];
    redirect('../checkout.php?zp_success=' . $oid);
} else {
    // ❌ Thất bại/hủy: giữ nguyên đơn hàng + giỏ hàng
    $oid = $order ? (int)$order['id'] : 0;
    redirect('../checkout.php?zp_fail=1&order_id=' . $oid);
}