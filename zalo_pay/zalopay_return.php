<?php
// =====================================================
// zalopay_return.php
// ZaloPay redirect user về trang này sau khi thanh toán
// Hiển thị kết quả thành công / thất bại cho user
// =====================================================
require_once '../includes/db.php';
require_once 'zalopay_config.php';

// ZaloPay gửi về: status, apptransid, appid, pmcid, bankcode, amount, discountamount, checksum
$status       = (int)($_GET['status'] ?? -1);
$app_trans_id = $conn->real_escape_string($_GET['apptransid'] ?? '');

// Xác thực checksum để đảm bảo dữ liệu không bị giả mạo
$checksum_data = ($_GET['appid']          ?? '') . '|' .
                 ($_GET['apptransid']     ?? '') . '|' .
                 ($_GET['pmcid']          ?? '') . '|' .
                 ($_GET['bankcode']       ?? '') . '|' .
                 ($_GET['amount']         ?? '') . '|' .
                 ($_GET['discountamount'] ?? '') . '|' .
                 ($_GET['status']         ?? '');

$expected_checksum = hash_hmac('sha256', $checksum_data, ZALOPAY_KEY1);
$valid = ($expected_checksum === ($_GET['checksum'] ?? ''));

// Lấy thông tin đơn hàng từ DB theo app_trans_id
$order = null;
if ($app_trans_id) {
    $user_id = $_SESSION['user_id'] ?? 0;
    $order   = $conn->query(
        "SELECT * FROM orders WHERE app_trans_id='$app_trans_id' AND user_id=$user_id"
    )->fetch_assoc();
}

$success = ($status == 1 && $valid);

// ── Xử lý kết quả thanh toán ──────────────────────────────────────────────

if ($success && $order) {
    // ✅ THÀNH CÔNG: xóa giỏ hàng + đánh dấu đơn là "đã xác nhận"
    $_SESSION['cart'] = [];

    // Cập nhật trạng thái nếu callback chưa làm kịp
    if ($order['status'] !== 'confirmed') {
        $zp_trans_id = $conn->real_escape_string($_GET['zptransid'] ?? '');
        $conn->query("UPDATE orders
                      SET status='confirmed', payment_status='paid', zp_trans_id='$zp_trans_id'
                      WHERE id={$order['id']} AND payment_status != 'paid'");
        // Reload để có dữ liệu mới nhất
        $order = $conn->query("SELECT * FROM orders WHERE id={$order['id']}")->fetch_assoc();
    }

} elseif (!$success && $order) {
    // ❌ THẤT BẠI / HỦY: hoàn tồn kho + xóa đơn hàng, GIỮ giỏ hàng

    // Hoàn số lượng tồn kho trước khi xóa chi tiết
    $items = $conn->query(
        "SELECT product_id, quantity FROM order_details WHERE order_id={$order['id']}"
    );
    while ($item = $items->fetch_assoc()) {
        $conn->query(
            "UPDATE products SET stock_quantity = stock_quantity + {$item['quantity']}
             WHERE id={$item['product_id']}"
        );
    }

    // Xóa đơn hàng (order_details CASCADE theo FK)
    $conn->query("DELETE FROM orders WHERE id={$order['id']}");
    $order = null; // Không còn đơn hàng
}

$pageTitle = $success ? 'Thanh toán thành công' : 'Thanh toán thất bại';
require_once '../includes/header.php';
?>

<div class="container my-5">
  <?php if ($success): ?>
  <!-- ✅ THÀNH CÔNG -->
  <div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
      <i class="bi bi-check-circle-fill" style="font-size:5rem;color:#0068ff"></i>
      <h3 class="fw-bold mt-3" style="color:#0068ff">Thanh toán ZaloPay thành công!</h3>

      <?php if ($order): ?>
      <p class="text-muted mb-1">
        Mã đơn hàng: <strong style="color:#0068ff"><?= htmlspecialchars($order['order_code']) ?></strong>
      </p>
      <p class="text-muted mb-4">Chúng tôi sẽ xử lý đơn hàng của bạn sớm nhất.</p>

      <!-- Tóm tắt đơn hàng -->
      <div class="card mx-auto text-start" style="max-width:520px">
        <div class="card-header fw-bold bg-light">Tóm tắt đơn hàng</div>
        <div class="card-body">
          <?php
          $details = $conn->query(
              "SELECT od.*, p.name FROM order_details od
               JOIN products p ON od.product_id=p.id
               WHERE od.order_id={$order['id']}"
          );
          while ($d = $details->fetch_assoc()):
          ?>
          <div class="d-flex justify-content-between mb-1 small">
            <span><?= htmlspecialchars($d['name']) ?> <span class="text-muted">×<?= $d['quantity'] ?></span></span>
            <strong><?= formatPrice($d['unit_price'] * $d['quantity']) ?></strong>
          </div>
          <?php endwhile; ?>
          <hr class="my-2">
          <div class="d-flex justify-content-between fw-bold" style="color:#0068ff">
            <span>Tổng cộng:</span>
            <span><?= formatPrice($order['total_amount']) ?></span>
          </div>
          <hr class="my-2">
          <div class="small text-muted">
            <p class="mb-1">
              <i class="bi bi-person me-1"></i>
              <?= htmlspecialchars($order['receiver_name']) ?> · <?= htmlspecialchars($order['receiver_phone']) ?>
            </p>
            <p class="mb-0">
              <i class="bi bi-geo-alt me-1"></i>
              <?= htmlspecialchars(
                  $order['shipping_address'].', '.
                  $order['ward'].', '.
                  $order['district'].', '.
                  $order['city']
              ) ?>
            </p>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Nút điều hướng — dùng đường dẫn tương đối với base href -->
      <div class="mt-4 d-flex gap-2 justify-content-center">
        <a href="my_orders.php" class="btn btn-primary">
          <i class="bi bi-bag-check me-2"></i>Xem đơn hàng
        </a>
        <a href="index.php" class="btn btn-outline-secondary">
          <i class="bi bi-house me-2"></i>Trang chủ
        </a>
      </div>
    </div>
  </div>

  <?php else: ?>
  <!-- ❌ THẤT BẠI / HỦY -->
  <div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
      <i class="bi bi-x-circle-fill text-danger" style="font-size:5rem"></i>
      <h3 class="text-danger fw-bold mt-3">Thanh toán thất bại hoặc bị hủy</h3>
      <p class="text-muted mb-1">Đơn hàng chưa được xử lý. Giỏ hàng của bạn vẫn còn nguyên.</p>
      <p class="text-muted mb-4">Bạn có thể quay lại giỏ hàng và thử thanh toán lại bất kỳ lúc nào.</p>
      <div class="d-flex gap-2 justify-content-center">
        <a href="cart.php" class="btn btn-primary">
          <i class="bi bi-cart3 me-2"></i>Quay lại giỏ hàng
        </a>
        <a href="index.php" class="btn btn-outline-secondary">
          <i class="bi bi-house me-2"></i>Trang chủ
        </a>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>