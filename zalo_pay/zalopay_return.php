<?php
// zalopay_return.php — Chỉ trừ tồn kho khi thành công, giữ awaiting_payment khi thất bại
require_once '../includes/db.php';
require_once 'zalopay_config.php';

$status       = (int)($_GET['status'] ?? -1);
$app_trans_id = $conn->real_escape_string($_GET['apptransid'] ?? '');

// Tìm đơn hàng theo app_trans_id (không dùng user_id vì session có thể mất)
$order = null;
if ($app_trans_id) {
    $order = $conn->query(
        "SELECT * FROM orders WHERE app_trans_id='$app_trans_id' LIMIT 1"
    )->fetch_assoc();
}

// Xác định thành công: ZaloPay báo status=1 VÀ tìm được đơn hàng trong DB
$success = ($status === 1) && ($order !== null);

if ($success && $order) {
  // ✅ THÀNH CÔNG
  $conn->begin_transaction();
  try {
    $oid = (int)$order['id'];
    $locked = $conn->query("SELECT * FROM orders WHERE id=$oid FOR UPDATE")->fetch_assoc();

    if ($locked && $locked['status'] === 'confirmed') {
      // Đã xử lý thành công trước đó
    } else {
      $items = $conn->query(
        "SELECT product_id, size_id, color_id, quantity FROM order_details WHERE order_id=$oid"
      );
      while ($item = $items->fetch_assoc()) {
        $pid = (int)$item['product_id'];
        $size = (int)$item['size_id'];
        $color = (int)$item['color_id'];
        $qty = (int)$item['quantity'];

        $stock = $conn->query("SELECT stock_quantity FROM product_varieties WHERE product_id=$pid AND size_id=$size AND color_id=$color FOR UPDATE")->fetch_assoc();
        if (!$stock || (int)$stock['stock_quantity'] < $qty) {
          throw new Exception('Sản phẩm không đủ tồn kho để hoàn tất thanh toán.');
        }

        $conn->query("UPDATE product_varieties SET stock_quantity = stock_quantity - $qty WHERE product_id=$pid AND size_id=$size AND color_id=$color AND stock_quantity >= $qty");
        if ($conn->affected_rows !== 1) {
          throw new Exception('Không thể cập nhật tồn kho do cạnh tranh dữ liệu.');
        }
      }

      $conn->query("UPDATE orders SET status='confirmed' WHERE id=$oid AND status='awaiting_payment'");
      if ($conn->affected_rows !== 1) {
        throw new Exception('Đơn hàng đã được xử lý bởi giao dịch khác.');
      }
    }

    $conn->commit();
    $_SESSION['cart'] = [];
    $order = $conn->query("SELECT * FROM orders WHERE id=$oid")->fetch_assoc();
  } catch (Exception $e) {
    $conn->rollback();
    $success = false;
  }

} elseif (!$success && $order) {
  // ❌ THẤT BẠI / HỦY / BACK
  // KHÔNG xóa đơn, KHÔNG trừ tồn kho, giữ trạng thái chờ thanh toán
  if ($order['status'] !== 'confirmed') {
    $conn->query("UPDATE orders SET status='awaiting_payment' WHERE id={$order['id']}");
  }
  $order = $conn->query("SELECT * FROM orders WHERE id={$order['id']}")->fetch_assoc();
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
            <p class="mb-1"><i class="bi bi-person me-1"></i><?= htmlspecialchars($order['receiver_name']) ?> · <?= htmlspecialchars($order['receiver_phone']) ?></p>
            <p class="mb-0"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($order['shipping_address'].', '.$order['ward'].', '.$order['district'].', '.$order['city']) ?></p>
          </div>
        </div>
      </div>

      <div class="mt-4 d-flex gap-2 justify-content-center">
        <a href="my_orders.php" class="btn btn-primary"><i class="bi bi-bag-check me-2"></i>Xem đơn hàng</a>
        <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-house me-2"></i>Trang chủ</a>
      </div>
    </div>
  </div>

  <?php else: ?>
  <!-- ❌ THẤT BẠI / HỦY — Đơn vẫn còn, chờ thanh toán -->
  <div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
      <i class="bi bi-clock-history text-warning" style="font-size:5rem"></i>
      <h3 class="text-warning fw-bold mt-3">Thanh toán chưa hoàn tất</h3>
      <p class="text-muted mb-1">Đơn hàng của bạn vẫn được giữ lại.</p>
      <p class="text-muted mb-4">Bạn có thể thanh toán lại hoặc đổi sang phương thức khác.</p>
      <?php if ($order): ?>
      <p class="mb-4">Mã đơn: <strong style="color:#ff6b35"><?= htmlspecialchars($order['order_code']) ?></strong></p>
      <div class="d-flex gap-2 justify-content-center">
        <a href="checkout.php?repay=<?= $order['id'] ?>" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-repeat me-2"></i>Đổi phương thức thanh toán
        </a>
        <a href="zalopay_create.php?order_id=<?= $order['id'] ?>" class="btn btn-primary" style="background:#0068ff;border-color:#0068ff">
          <i class="bi bi-phone me-2"></i>Thanh toán lại qua ZaloPay
        </a>
      </div>
      <?php else: ?>
      <a href="cart.php" class="btn btn-primary"><i class="bi bi-cart3 me-2"></i>Quay lại giỏ hàng</a>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>