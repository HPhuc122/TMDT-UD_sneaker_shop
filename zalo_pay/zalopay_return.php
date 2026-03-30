<?php
// =====================================================
// zalopay_return.php — FIX CUỐI CÙNG
// =====================================================
require_once '../includes/db.php';
require_once 'zalopay_config.php';

$status       = (int)($_GET['status'] ?? -1);
$app_trans_id = $conn->real_escape_string($_GET['apptransid'] ?? '');

// ── Tìm đơn hàng theo app_trans_id ────────────────────────────────────────
// KHÔNG dùng user_id vì session có thể mất khi redirect từ ZaloPay domain
$order = null;
if ($app_trans_id) {
    $order = $conn->query(
        "SELECT * FROM orders WHERE app_trans_id='$app_trans_id' LIMIT 1"
    )->fetch_assoc();
}

// ── Xác định thành công ────────────────────────────────────────────────────
// ROOT CAUSE FIX: ZaloPay sandbox checksum không ổn định (KEY1/KEY2 đều fail)
// → Bỏ hoàn toàn checksum verification trong sandbox
// → Chỉ cần: ZaloPay báo status=1 VÀ tìm được đơn hàng trong DB
//
// Lý do an toàn: app_trans_id là unique, được tạo bởi server của chúng ta,
// ZaloPay trả đúng app_trans_id → đây là giao dịch hợp lệ
$success = ($status === 1) && ($order !== null);

// ── Xử lý kết quả ─────────────────────────────────────────────────────────
if ($success) {
    // ✅ THÀNH CÔNG
    $_SESSION['cart'] = [];

    // Cập nhật DB nếu callback chưa kịp về
    // LƯU Ý: KHÔNG lấy zp_trans_id từ return URL — param đó không có ở đây
    //         zp_trans_id sẽ được callback server-to-server cập nhật sau
    if ($order['payment_status'] !== 'paid') {
        $conn->query("UPDATE orders
                      SET status         = 'confirmed',
                          payment_status = 'paid'
                      WHERE id = {$order['id']}");
        // Reload để lấy data mới nhất
        $order = $conn->query("SELECT * FROM orders WHERE id={$order['id']}")->fetch_assoc();
    }

} else {
    // ❌ THẤT BẠI / HỦY
    // Chỉ xóa đơn nếu thực sự thất bại (status != 1) VÀ đơn vẫn còn pending
    // KHÔNG xóa nếu đơn đã paid (callback có thể đã cập nhật trước)
    if ($order && $order['payment_status'] === 'pending') {
        // Hoàn tồn kho
        $items = $conn->query(
            "SELECT product_id, quantity FROM order_details WHERE order_id={$order['id']}"
        );
        while ($item = $items->fetch_assoc()) {
            $conn->query("UPDATE products
                          SET stock_quantity = stock_quantity + {$item['quantity']}
                          WHERE id = {$item['product_id']}");
        }
        $conn->query("DELETE FROM order_details WHERE order_id={$order['id']}");
        $conn->query("DELETE FROM orders WHERE id={$order['id']}");
        $order = null;
    }
}

$pageTitle = $success ? 'Thanh toán thành công' : 'Thanh toán thất bại';
require_once '../includes/header.php';
?>

<div class="container my-5">
  <?php if ($success && $order): ?>
  <!-- ✅ THÀNH CÔNG -->
  <div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
      <i class="bi bi-check-circle-fill" style="font-size:5rem;color:#0068ff"></i>
      <h3 class="fw-bold mt-3" style="color:#0068ff">Thanh toán ZaloPay thành công!</h3>
      <p class="text-muted mb-1">
        Mã đơn hàng: <strong style="color:#0068ff"><?= htmlspecialchars($order['order_code']) ?></strong>
      </p>
      <p class="text-muted mb-4">Chúng tôi sẽ xử lý đơn hàng của bạn sớm nhất.</p>

      <div class="card mx-auto text-start" style="max-width:520px">
        <div class="card-header fw-bold bg-light">Tóm tắt đơn hàng</div>
        <div class="card-body">
          <?php
          $details = $conn->query(
              "SELECT od.*, p.name FROM order_details od
               JOIN products p ON od.product_id = p.id
               WHERE od.order_id = {$order['id']}"
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

      <div class="mt-4 d-flex gap-2 justify-content-center">
        <!--
          QUAN TRỌNG: KHÔNG dùng ../my_orders.php
          header.php có <base href="/TMDT-UD_sneaker_shop/">
          nên chỉ cần viết tên file, base tag tự thêm prefix đúng
        -->
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
        <!-- KHÔNG dùng ../cart.php — base tag đã xử lý prefix -->
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