<?php
// =====================================================
// zalopay_return.php
// ZaloPay redirect user về trang này sau khi thanh toán
// Hiển thị kết quả thành công / thất bại cho user
// =====================================================
require_once '../includes/db.php';
require_once 'zalopay_config.php';

// ZaloPay gửi về các params: status, apptransid, appid,
//                            pmcid, bankcode, amount,
//                            discountamount, checksum
$status       = (int)($_GET['status'] ?? -1);
$app_trans_id = $conn->real_escape_string($_GET['apptransid'] ?? '');

// Xác thực checksum để đảm bảo dữ liệu không bị giả mạo
$checksum_data = ($_GET['appid']    ?? '') . '|' .
                 ($_GET['apptransid'] ?? '') . '|' .
                 ($_GET['pmcid']    ?? '') . '|' .
                 ($_GET['bankcode'] ?? '') . '|' .
                 ($_GET['amount']   ?? '') . '|' .
                 ($_GET['discountamount'] ?? '') . '|' .
                 ($_GET['status']   ?? '');

$expected_checksum = hash_hmac('sha256', $checksum_data, ZALOPAY_KEY1);
$valid = ($expected_checksum === ($_GET['checksum'] ?? ''));

// Lấy thông tin đơn hàng từ DB
$order = null;
if ($app_trans_id) {
    $user_id = $_SESSION['user_id'] ?? 0;
    $order   = $conn->query("SELECT * FROM orders WHERE app_trans_id='$app_trans_id' AND user_id=$user_id")->fetch_assoc();
}

$success     = ($status == 1);
$pageTitle   = $success ? 'Thanh toán thành công' : 'Thanh toán thất bại';
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
          $details = $conn->query("SELECT od.*, p.name FROM order_details od
                                   JOIN products p ON od.product_id=p.id
                                   WHERE od.order_id={$order['id']}");
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
            <p class="mb-0"><i class="bi bi-geo-alt me-1"></i>
              <?= htmlspecialchars($order['shipping_address'].', '.$order['ward'].', '.$order['district'].', '.$order['city']) ?>
            </p>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <div class="mt-4 d-flex gap-2 justify-content-center">
        <a href="../my_orders.php" class="btn btn-primary">
          <i class="bi bi-bag-check me-2"></i>Xem đơn hàng
        </a>
        <a href="../index.php" class="btn btn-outline-secondary">
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
      <p class="text-muted mb-4">Đơn hàng chưa được xử lý. Bạn có thể thử lại.</p>
      <div class="d-flex gap-2 justify-content-center">
        <a href="../cart.php" class="btn btn-primary">
          <i class="bi bi-arrow-left me-2"></i>Quay lại giỏ hàng
        </a>
        <a href="../index.php" class="btn btn-outline-secondary">
          <i class="bi bi-house me-2"></i>Trang chủ
        </a>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
