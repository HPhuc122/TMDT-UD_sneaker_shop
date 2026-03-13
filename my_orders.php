<?php
// my_orders.php
require_once 'includes/header.php';
if (!isLoggedIn()) redirect('login.php?redirect=my_orders.php');

$pageTitle = 'Đơn hàng của tôi';
$user_id = $_SESSION['user_id'];

$orders = $conn->query("SELECT * FROM orders WHERE user_id=$user_id ORDER BY created_at DESC");

$statusLabels = [
    'pending'   => ['Chờ xử lý',   'warning'],
    'confirmed' => ['Đã xác nhận', 'info'],
    'delivered' => ['Đã giao',     'success'],
    'cancelled' => ['Đã huỷ',      'danger'],
];

$detail_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$orderDetail = null;
if ($detail_id > 0) {
    $r = $conn->query("SELECT * FROM orders WHERE id=$detail_id AND user_id=$user_id");
    $orderDetail = $r->fetch_assoc();
}
?>

<div class="container my-4">
    <h3 class="section-title mb-4">Đơn Hàng Của Tôi</h3>

    <?php if ($orderDetail): ?>
    <!-- Order Detail View -->
    <div class="mb-3">
        <a href="my_orders.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Quay lại</a>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Đơn hàng: <?= htmlspecialchars($orderDetail['order_code']) ?></strong>
            <?php list($label, $color) = $statusLabels[$orderDetail['status']]; ?>
            <span class="badge bg-<?= $color ?>"><?= $label ?></span>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <h6 class="fw-bold">Thông tin giao hàng</h6>
                    <p class="mb-1"><i class="bi bi-person me-2"></i><?= htmlspecialchars($orderDetail['receiver_name']) ?></p>
                    <p class="mb-1"><i class="bi bi-telephone me-2"></i><?= htmlspecialchars($orderDetail['receiver_phone']) ?></p>
                    <p class="mb-0"><i class="bi bi-geo-alt me-2"></i><?= htmlspecialchars($orderDetail['shipping_address'].', '.$orderDetail['ward'].', '.$orderDetail['district'].', '.$orderDetail['city']) ?></p>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold">Chi tiết đơn hàng</h6>
                    <p class="mb-1">Ngày đặt: <?= date('d/m/Y H:i', strtotime($orderDetail['created_at'])) ?></p>
                    <?php
                    $pm = ['cash'=>'Tiền mặt (COD)','transfer'=>'Chuyển khoản','online'=>'Trực tuyến'];
                    ?>
                    <p class="mb-0">Thanh toán: <?= $pm[$orderDetail['payment_method']] ?></p>
                </div>
            </div>

            <table class="table table-bordered">
                <thead class="table-light">
                    <tr><th>Sản phẩm</th><th class="text-center">Số lượng</th><th class="text-end">Đơn giá</th><th class="text-end">Thành tiền</th></tr>
                </thead>
                <tbody>
                    <?php
                    $details = $conn->query("SELECT od.*, p.name, p.image FROM order_details od JOIN products p ON od.product_id=p.id WHERE od.order_id={$orderDetail['id']}");
                    while ($d = $details->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($d['name']) ?></td>
                        <td class="text-center"><?= $d['quantity'] ?></td>
                        <td class="text-end"><?= formatPrice($d['unit_price']) ?></td>
                        <td class="text-end"><?= formatPrice($d['unit_price'] * $d['quantity']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end fw-bold">Tổng cộng:</td>
                        <td class="text-end fw-bold" style="color:#ff6b35"><?= formatPrice($orderDetail['total_amount']) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <?php else: ?>
    <!-- Order List -->
    <?php if ($orders->num_rows === 0): ?>
    <div class="text-center py-5">
        <i class="bi bi-bag-x" style="font-size:5rem;color:#ccc"></i>
        <h4 class="mt-3 text-muted">Bạn chưa có đơn hàng nào</h4>
        <a href="index.php" class="btn btn-primary mt-3">Mua sắm ngay</a>
    </div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Mã đơn</th>
                        <th>Ngày đặt</th>
                        <th class="text-end">Tổng tiền</th>
                        <th>Thanh toán</th>
                        <th>Trạng thái</th>
                        <th class="text-center">Chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($ord = $orders->fetch_assoc()):
                        list($label, $color) = $statusLabels[$ord['status']];
                        $pm = ['cash'=>'COD','transfer'=>'CK','online'=>'Online'];
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($ord['order_code']) ?></strong></td>
                        <td><?= date('d/m/Y H:i', strtotime($ord['created_at'])) ?></td>
                        <td class="text-end fw-bold" style="color:#ff6b35"><?= formatPrice($ord['total_amount']) ?></td>
                        <td><?= $pm[$ord['payment_method']] ?></td>
                        <td><span class="badge bg-<?= $color ?>"><?= $label ?></span></td>
                        <td class="text-center">
                            <a href="my_orders.php?id=<?= $ord['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
