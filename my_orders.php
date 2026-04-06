<?php
require_once 'includes/db.php';
if (!isLoggedIn()) redirect('login.php?redirect=my_orders.php');

$pageTitle = 'Đơn hàng của tôi';
$user_id = $_SESSION['user_id'];

$msg = $_SESSION['orders_msg'] ?? '';
unset($_SESSION['orders_msg']);

$hasPaymentStatusCol = ($conn->query("SHOW COLUMNS FROM orders LIKE 'payment_status'")->num_rows > 0);
$hasPaymentDeadlineCol = ($conn->query("SHOW COLUMNS FROM orders LIKE 'payment_deadline'")->num_rows > 0);

function getCancelCountdownNotice($order, $hasPaymentDeadlineCol) {
    if (isset($order['payment_remaining_seconds'])) {
        $remaining = (int)$order['payment_remaining_seconds'];
    } else {
        $createdAtTs = strtotime($order['created_at'] ?? 'now');
        $deadlineTs = $createdAtTs + 86400;

        if ($hasPaymentDeadlineCol && !empty($order['payment_deadline'])) {
            $parsedDeadline = strtotime($order['payment_deadline']);
            if ($parsedDeadline !== false) {
                $deadlineTs = $parsedDeadline;
            }
        }

        $remaining = $deadlineTs - time();
    }

    if ($remaining > 86400) {
        $remaining = 86400;
    }
    if ($remaining <= 0) {
        return 'Đơn đã quá 24 giờ chưa thanh toán và sẽ tự động hủy sớm, hàng sẽ được hoàn về kho.';
    }

    $hours = intdiv($remaining, 3600);
    $minutes = intdiv($remaining % 3600, 60);
    return 'Đơn sẽ tự hủy sau ' . $hours . ' giờ ' . $minutes . ' phút nếu chưa thanh toán.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_action'])) {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $action = sanitize($conn, $_POST['order_action'] ?? '');
    $returnId = isset($_POST['return_id']) ? (int)$_POST['return_id'] : 0;

    $order = $conn->query("SELECT id, status, payment_method FROM orders WHERE id=$orderId AND user_id=$user_id LIMIT 1")->fetch_assoc();
    if (!$order) {
        $_SESSION['orders_msg'] = '<div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i>Không tìm thấy đơn hàng.</div>';
    } elseif (!isPendingPaymentOrderStatus($conn, $order['status'])) {
        $_SESSION['orders_msg'] = '<div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>Chỉ đơn chờ thanh toán mới thực hiện thao tác này.</div>';
    } else {
        if ($action === 'pay_now') {
            if ($order['payment_method'] !== 'online') {
                $setSql = "payment_method='online', status='" . $conn->real_escape_string(getOnlinePendingStatus($conn)) . "', app_trans_id=NULL";
                if ($hasPaymentStatusCol) $setSql .= ", payment_status='pending'";
                if ($hasPaymentDeadlineCol) $setSql .= ", payment_deadline=DATE_ADD(created_at, INTERVAL 24 HOUR)";
                $conn->query("UPDATE orders SET $setSql WHERE id=$orderId");
            } else {
                $setSql = "status='" . $conn->real_escape_string(getOnlinePendingStatus($conn)) . "'";
                if ($hasPaymentStatusCol) $setSql .= ", payment_status='pending'";
                if ($hasPaymentDeadlineCol) $setSql .= ", payment_deadline=DATE_ADD(created_at, INTERVAL 24 HOUR)";
                $conn->query("UPDATE orders SET $setSql WHERE id=$orderId");
            }
            // Phân biệt gateway dựa vào app_trans_id
            $fresh = $conn->query("SELECT app_trans_id FROM orders WHERE id=$orderId")->fetch_assoc();
            if (!empty($fresh['app_trans_id'])) {
                redirect('zalo_pay/zalopay_create.php?order_id=' . $orderId);
            }
            redirect('vnpay/vnpay_create_payment.php?order_id=' . $orderId);
        }

        if ($action === 'change_payment_method') {
            $newMethod = sanitize($conn, $_POST['new_payment_method'] ?? 'cash');
            $allowedMethods = ['cash', 'transfer', 'online'];
            if (!in_array($newMethod, $allowedMethods, true)) {
                $_SESSION['orders_msg'] = '<div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i>Phương thức thanh toán không hợp lệ.</div>';
            } else {
                if ($newMethod === 'online') {
                    $newStatus = getOnlinePendingStatus($conn);
                    $setSql = "payment_method='online', status='" . $conn->real_escape_string($newStatus) . "'";
                    if ($hasPaymentStatusCol) $setSql .= ", payment_status='pending'";
                    if ($hasPaymentDeadlineCol) $setSql .= ", payment_deadline=DATE_ADD(created_at, INTERVAL 24 HOUR)";
                    $conn->query("UPDATE orders SET $setSql WHERE id=$orderId");
                } else {
                    $setSql = "payment_method='$newMethod', status='pending'";
                    if ($hasPaymentStatusCol) $setSql .= ", payment_status=NULL";
                    if ($hasPaymentDeadlineCol) $setSql .= ", payment_deadline=NULL";
                    $conn->query("UPDATE orders SET $setSql WHERE id=$orderId");
                }

                $_SESSION['orders_msg'] = '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>Đã đổi phương thức thanh toán thành công.</div>';
            }
        }
    }

    $backUrl = 'my_orders.php';
    if ($returnId > 0) $backUrl .= '?id=' . $returnId;
    redirect($backUrl);
}

require_once 'includes/header.php';

$remainingExpr = "TIMESTAMPDIFF(SECOND, NOW(), COALESCE(payment_deadline, DATE_ADD(created_at, INTERVAL 24 HOUR)))";
if (!$hasPaymentDeadlineCol) {
    $remainingExpr = "TIMESTAMPDIFF(SECOND, NOW(), DATE_ADD(created_at, INTERVAL 24 HOUR))";
}
$orders = $conn->query("SELECT o.*, $remainingExpr AS payment_remaining_seconds FROM orders o WHERE o.user_id=$user_id ORDER BY o.created_at DESC");

$statusLabels = [
    'pending_payment'  => ['Chờ thanh toán', 'secondary'],
    'awaiting_payment' => ['Chờ thanh toán', 'secondary'],
    'pending'          => ['Chờ xử lý',      'warning'],
    'confirmed'        => ['Đã xác nhận',    'info'],
    'delivered'        => ['Đã giao',        'success'],
    'cancelled'        => ['Đã huỷ',         'danger'],
];

$detail_id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$orderDetail = null;
if ($detail_id > 0) {
    $r = $conn->query("SELECT o.*, $remainingExpr AS payment_remaining_seconds FROM orders o WHERE o.id=$detail_id AND o.user_id=$user_id");
    $orderDetail = $r->fetch_assoc();
}
?>

<div class="container my-4">
    <h3 class="section-title mb-4">Đơn Hàng Của Tôi</h3>

    <?php if ($msg): ?>
    <?= $msg ?>
    <?php endif; ?>

    <?php if ($orderDetail): ?>
        <!-- Order Detail View -->
        <div class="mb-3">
            <a href="my_orders.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Quay lại</a>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Đơn hàng: <?= htmlspecialchars($orderDetail['order_code']) ?></strong>
                <?php list($label, $color) = $statusLabels[$orderDetail['status']] ?? ['Không xác định', 'dark']; ?>
                <span class="badge bg-<?= $color ?>"><?= $label ?></span>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <h6 class="fw-bold">Thông tin giao hàng</h6>
                        <p class="mb-1"><i class="bi bi-person me-2"></i><?= htmlspecialchars($orderDetail['receiver_name']) ?></p>
                        <p class="mb-1"><i class="bi bi-telephone me-2"></i><?= htmlspecialchars($orderDetail['receiver_phone']) ?></p>
                        <p class="mb-0"><i class="bi bi-geo-alt me-2"></i><?= htmlspecialchars($orderDetail['shipping_address'] . ', ' . $orderDetail['ward'] . ', ' . $orderDetail['district'] . ', ' . $orderDetail['city']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold">Chi tiết đơn hàng</h6>
                        <p class="mb-1">Ngày đặt: <?= date('d/m/Y H:i', strtotime($orderDetail['created_at'])) ?></p>
                        <?php
                        $pm = ['cash' => 'Tiền mặt (COD)', 'online' => 'Trực tuyến'];
                        ?>
                        <p class="mb-0">Thanh toán: <?= $pm[$orderDetail['payment_method']] ?? 'Khác' ?></p>
                        <?php if (isPendingPaymentOrderStatus($conn, $orderDetail['status'])): ?>
                        <div class="alert alert-warning py-2 px-3 mt-3 mb-0 small">
                            <i class="bi bi-clock-history me-2"></i><?= htmlspecialchars(getCancelCountdownNotice($orderDetail, $hasPaymentDeadlineCol)) ?>
                        </div>
                        <?php endif; ?>
                        <?php if (isPendingPaymentOrderStatus($conn, $orderDetail['status'])): ?>
                        <div class="mt-3 d-flex gap-2 flex-wrap">
                            <?php if ($orderDetail['payment_method'] === 'online'): ?>
                            <?php
                            $payUrl = !empty($orderDetail['app_trans_id'])
                                ? 'zalo_pay/zalopay_create.php?order_id=' . (int)$orderDetail['id']
                                : 'checkout.php?repay=' . (int)$orderDetail['id'] . '&action=pay';
                            ?>
                            <a href="<?= $payUrl ?>" class="btn btn-primary btn-sm">
                                <i class="bi bi-credit-card me-2"></i>Thanh toán
                            </a>
                            <?php endif; ?>
                            <a href="checkout.php?repay=<?= (int)$orderDetail['id'] ?>" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-arrow-repeat me-2"></i>Đổi phương thức thanh toán
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Sản phẩm</th>
                            <th class="text-center">Số lượng</th>
                            <th class="text-end">Đơn giá</th>
                            <th class="text-end">Thành tiền</th>
                        </tr>
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
                                list($label, $color) = $statusLabels[$ord['status']] ?? ['Không xác định', 'dark'];
                                $pm = ['cash' => 'COD', 'online' => 'Online'];
                            ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($ord['order_code']) ?></strong></td>
                                    <td><?= date('d/m/Y H:i', strtotime($ord['created_at'])) ?></td>
                                    <td class="text-end fw-bold" style="color:#ff6b35"><?= formatPrice($ord['total_amount']) ?></td>
                                    <td><?= $pm[$ord['payment_method']] ?? 'Khác' ?></td>
                                    <td>
                                        <span class="badge bg-<?= $color ?>"><?= $label ?></span>
                                        <?php if (isPendingPaymentOrderStatus($conn, $ord['status'])): ?>
                                            <div class="small text-muted mt-1"><?= htmlspecialchars(getCancelCountdownNotice($ord, $hasPaymentDeadlineCol)) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex align-items-center" style="width:76px;justify-content:space-between;">
                                            <a href="my_orders.php?id=<?= $ord['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if (isPendingPaymentOrderStatus($conn, $ord['status']) && $ord['payment_method'] === 'online'): ?>
                                                <?php
                                                $payUrl = !empty($ord['app_trans_id'])
                                                    ? 'zalo_pay/zalopay_create.php?order_id=' . (int)$ord['id']
                                                    : 'checkout.php?repay=' . (int)$ord['id'] . '&action=pay';
                                                ?>
                                                <a href="<?= $payUrl ?>" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-credit-card"></i>
                                                </a>
                                            <?php else: ?>
                                                <span style="display:inline-block;width:31px;"></span>
                                            <?php endif; ?>
                                        </div>
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