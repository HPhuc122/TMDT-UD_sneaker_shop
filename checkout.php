<?php
// checkout.php
require_once 'includes/db.php';
if (!isLoggedIn()) redirect('login.php?redirect=checkout.php');

$user_id = $_SESSION['user_id'];
$user    = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

// ══════════════════════════════════════════════════════════════════
// REPAY FLOW — Đổi phương thức / thanh toán lại đơn awaiting_payment
// ══════════════════════════════════════════════════════════════════
$repay_order = null;
$repay_action = sanitize($conn, $_GET['action'] ?? 'change');
if (isset($_GET['repay'])) {
    $repay_id    = (int)$_GET['repay'];
    $repay_order = $conn->query(
        "SELECT * FROM orders WHERE id=$repay_id AND user_id=$user_id AND status='awaiting_payment'"
    )->fetch_assoc();
    if (!$repay_order) redirect('my_orders.php');

    if ($repay_action === 'pay') {
        if ($repay_order['payment_method'] === 'online') {
            redirect('vnpay/vnpay_create_payment.php?order_id=' . $repay_id);
        }

        $repay_action = 'change';
    }
}

// POST: xử lý repay (đổi phương thức hoặc thanh toán lại online)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['repay_order_id'])) {
    $repay_id  = (int)$_POST['repay_order_id'];
    $repay_ord = $conn->query(
        "SELECT * FROM orders WHERE id=$repay_id AND user_id=$user_id AND status='awaiting_payment'"
    )->fetch_assoc();
    if (!$repay_ord) redirect('my_orders.php');

    $payment    = sanitize($conn, $_POST['payment_method'] ?? 'cash');
    $online_sub = sanitize($conn, $_POST['online_sub'] ?? 'vnpay');

    if ($payment === 'online' && ($online_sub === 'vnpay' || $online_sub === 'zalopay')) {
        // Thanh toán lại online -> giữ đơn awaiting_payment và chuyển sang cổng tương ứng
        $conn->query("UPDATE orders SET payment_method='online' WHERE id=$repay_id");
        if ($online_sub === 'zalopay') {
            redirect('zalo_pay/zalopay_create.php?order_id=' . $repay_id);
        }
        redirect('vnpay/vnpay_create_payment.php?order_id=' . $repay_id);
    } else {
        // Đổi sang COD → trừ tồn kho lúc này, status='pending'
        $items = $conn->query("SELECT product_id, quantity, size_id, color_id FROM order_details WHERE order_id=$repay_id");
        while ($item = $items->fetch_assoc()) {
            $pid = (int)$item['product_id'];
            $qty = (int)$item['quantity'];
            $size = (int)$item['size_id'];
            $color = (int)$item['color_id'];
            if ($size > 0 && $color > 0) {
                $conn->query("UPDATE product_varieties SET stock_quantity = stock_quantity - $qty WHERE product_id=$pid AND size_id=$size AND color_id=$color AND stock_quantity >= $qty");
            }
        }
        $pm_safe = $conn->real_escape_string($payment);
        $conn->query("UPDATE orders SET payment_method='$pm_safe', status='pending' WHERE id=$repay_id");
        $_SESSION['cart'] = [];
        redirect('checkout.php?success=' . $repay_id);
    }
}

// ══════════════════════════════════════════════════════════════════
// NORMAL CHECKOUT FLOW
// ══════════════════════════════════════════════════════════════════
$cart = $_SESSION['cart'] ?? [];
// Cho phép vào nếu: có giỏ hàng, hoặc success page, hoặc đang repay
if (empty($cart) && !isset($_GET['success']) && !$repay_order) redirect('cart.php');

$total = 0;
foreach ($cart as $item) $total += $item['price'] * $item['qty'];

$error      = '';
$order_done = false;
$ord        = null;

// POST: tạo đơn mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['repay_order_id'])) {
    $use_saved  = isset($_POST['use_saved_address']) && $_POST['use_saved_address'] == '1';
    $receiver   = sanitize($conn, $_POST['receiver_name']    ?? '');
    $phone      = sanitize($conn, $_POST['receiver_phone']   ?? '');
    $address    = $use_saved ? $user['address']  : sanitize($conn, $_POST['shipping_address'] ?? '');
    $ward       = $use_saved ? $user['ward']      : sanitize($conn, $_POST['ward']             ?? '');
    $district   = $use_saved ? $user['district']  : sanitize($conn, $_POST['district']         ?? '');
    $city       = $use_saved ? $user['city']      : sanitize($conn, $_POST['city']             ?? '');
    $payment    = sanitize($conn, $_POST['payment_method'] ?? 'cash');
    $online_sub = sanitize($conn, $_POST['online_sub']     ?? 'vnpay');
    $notes      = sanitize($conn, $_POST['notes']          ?? '');

    if (!$receiver || !$phone || !$address || !$ward || !$district || !$city) {
        $error = 'Vui lòng điền đầy đủ thông tin giao hàng.';
    } elseif (!preg_match('/^0[0-9]{9,10}$/', $phone)) {
        $error = 'Số điện thoại không hợp lệ. Phải bắt đầu bằng số 0 và có 10-11 chữ số.';
    } else {
        // Thanh toán online: tạo đơn trước ở trạng thái awaiting_payment, chưa trừ kho
        if ($payment === 'online' && ($online_sub === 'vnpay' || $online_sub === 'zalopay')) {
            $conn->begin_transaction();
            try {
                $order_code = generateCode('DH');
            $status = 'awaiting_payment';

            $stmt = $conn->prepare("INSERT INTO orders (order_code,user_id,receiver_name,receiver_phone,shipping_address,ward,district,city,payment_method,total_amount,status,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param('sisssssssdss', $order_code, $user_id, $receiver, $phone, $address, $ward, $district, $city, $payment, $total, $status, $notes);
                $stmt->execute();
                $order_id = $conn->insert_id;

                $detailStmt = $conn->prepare("INSERT INTO order_details (order_id,product_id,quantity,unit_price,size_id,color_id) VALUES (?,?,?,?,?,?)");
                foreach ($cart as $item) {
                    $pid   = (int)($item['product_id'] ?? 0);
                    $size  = (int)($item['size_id'] ?? 0);
                    $color = (int)($item['color_id'] ?? 0);
                    $qty   = (int)($item['qty'] ?? 0);
                    $price = (float)($item['price'] ?? 0);
                    if ($pid <= 0 || $size <= 0 || $color <= 0 || $qty <= 0) {
                        throw new Exception('Dữ liệu giỏ hàng không hợp lệ.');
                    }
                    $detailStmt->bind_param('iiidii', $order_id, $pid, $qty, $price, $size, $color);
                    $detailStmt->execute();
                }

                $conn->commit();
                $_SESSION['cart'] = [];
                if ($online_sub === 'zalopay') {
                    redirect('zalo_pay/zalopay_create.php?order_id=' . $order_id);
                }
                redirect('vnpay/vnpay_create_payment.php?order_id=' . $order_id);
            } catch (Exception $e) {
                $conn->rollback();
                $error = $e->getMessage();
            }
        } else {
            // Thanh toán COD hoặc chuyển khoản → tạo đơn hàng ngay
            $order_code = generateCode('DH');
            $stmt = $conn->prepare("INSERT INTO orders (order_code,user_id,receiver_name,receiver_phone,shipping_address,ward,district,city,payment_method,total_amount,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param('sisssssssds', $order_code, $user_id, $receiver, $phone, $address, $ward, $district, $city, $payment, $total, $notes);
            if ($stmt->execute()) {
                $order_id = $conn->insert_id;
                foreach ($cart as $item) {
                    $pid   = (int)$item['product_id'];
                    $size  = (int)($item['size_id'] ?? 0);
                    $color = (int)($item['color_id'] ?? 0);
                    $qty   = (int)$item['qty'];
                    $price = (float)$item['price'];
                    $conn->query("INSERT INTO order_details (order_id,product_id,quantity,unit_price,size_id,color_id) VALUES ($order_id,$pid,$qty,$price,$size,$color)");
                    if ($size > 0 && $color > 0) {
                        $conn->query("UPDATE product_varieties SET stock_quantity = stock_quantity - $qty WHERE product_id=$pid AND size_id=$size AND color_id=$color AND stock_quantity >= $qty");
                    }
                }
                $_SESSION['cart'] = [];
                redirect('checkout.php?success=' . $order_id);
            } else {
                $error = 'Có lỗi khi tạo đơn hàng. Vui lòng thử lại.';
            }
        }
    }
}

// Success page via PRG
if (isset($_GET['success'])) {
    $oid = (int)$_GET['success'];
    $ord = $conn->query("SELECT * FROM orders WHERE id=$oid AND user_id=$user_id")->fetch_assoc();
    if ($ord) $order_done = true;
}

$pageTitle = $order_done ? 'Đặt hàng thành công' : ($repay_order ? 'Thanh toán đơn hàng' : 'Thanh toán');
require_once 'includes/header.php';
?>

<div class="container my-4">
    <h3 class="section-title mb-4">
        <?= $order_done ? 'Xác nhận đơn hàng' : ($repay_order ? 'Thanh toán đơn ' . htmlspecialchars($repay_order['order_code']) : 'Thanh Toán') ?>
    </h3>

    <?php if ($order_done): ?>
    <!-- ✅ THÀNH CÔNG -->
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-check-circle-fill" style="font-size:5rem;color:#28a745"></i>
            <h3 class="text-success fw-bold mt-3">Đặt hàng thành công!</h3>
            <p class="text-muted mb-1">Mã đơn hàng: <strong style="color:#ff6b35"><?= htmlspecialchars($ord['order_code']) ?></strong></p>
            <p class="text-muted mb-4">Chúng tôi sẽ liên hệ xác nhận đơn hàng sớm nhất.</p>
            <div class="card mx-auto text-start" style="max-width:520px">
                <div class="card-header fw-bold bg-light">Tóm tắt đơn hàng</div>
                <div class="card-body">
                    <?php
                    $details = $conn->query("SELECT od.*, p.name FROM order_details od JOIN products p ON od.product_id=p.id WHERE od.order_id={$ord['id']}");
                    while ($d = $details->fetch_assoc()):
                    ?>
                    <div class="d-flex justify-content-between mb-1 small">
                        <span><?= htmlspecialchars($d['name']) ?> <span class="text-muted">×<?= $d['quantity'] ?></span></span>
                        <strong><?= formatPrice($d['unit_price'] * $d['quantity']) ?></strong>
                    </div>
                    <?php endwhile; ?>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between fw-bold" style="color:#ff6b35">
                        <span>Tổng cộng:</span><span><?= formatPrice($ord['total_amount']) ?></span>
                    </div>
                    <hr class="my-2">
                    <div class="small text-muted">
                        <p class="mb-1"><i class="bi bi-person me-1"></i><?= htmlspecialchars($ord['receiver_name']) ?> · <?= htmlspecialchars($ord['receiver_phone']) ?></p>
                        <p class="mb-1"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($ord['shipping_address'].', '.$ord['ward'].', '.$ord['district'].', '.$ord['city']) ?></p>
                        <?php $pm=['cash'=>'Tiền mặt (COD)','online'=>'Trực tuyến']; ?>
                        <p class="mb-0"><i class="bi bi-credit-card me-1"></i><?= $pm[$ord['payment_method']] ?></p>
                    </div>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2 justify-content-center">
                <a href="my_orders.php" class="btn btn-primary"><i class="bi bi-bag-check me-2"></i>Xem đơn hàng</a>
                <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-house me-2"></i>Về trang chủ</a>
            </div>
        </div>
    </div>

    <?php elseif ($repay_order && $repay_action === 'change'): ?>
    <!-- 🔄 REPAY FORM — Đổi phương thức / Thanh toán lại -->
    <?php if ($error): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="repay_order_id" value="<?= $repay_order['id'] ?>">
        <div class="row g-4">
            <div class="col-lg-7">
                <!-- Thông tin giao hàng (chỉ đọc) -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header fw-bold bg-white border-0"><i class="bi bi-truck me-2"></i>Thông tin giao hàng</div>
                    <div class="card-body">
                        <div class="p-3 bg-light rounded">
                            <p class="mb-1"><i class="bi bi-person me-2"></i><strong><?= htmlspecialchars($repay_order['receiver_name']) ?></strong> · <?= htmlspecialchars($repay_order['receiver_phone']) ?></p>
                            <p class="mb-0"><i class="bi bi-geo-alt me-2"></i><?= htmlspecialchars($repay_order['shipping_address'].', '.$repay_order['ward'].', '.$repay_order['district'].', '.$repay_order['city']) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Phương thức thanh toán -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header fw-bold bg-white border-0"><i class="bi bi-credit-card me-2"></i>Chọn phương thức thanh toán</div>
                    <div class="card-body">
                        <div class="form-check mb-3 p-3 border rounded">
                            <input class="form-check-input" type="radio" name="payment_method" id="cash" value="cash" checked onchange="showPayment('cash')">
                            <label class="form-check-label fw-semibold" for="cash"><i class="bi bi-cash-coin me-2 text-success"></i>Tiền mặt khi nhận hàng (COD)</label>
                        </div>
                        <div class="form-check p-3 border rounded">
                            <input class="form-check-input" type="radio" name="payment_method" id="online" value="online" onchange="showPayment('online')">
                            <label class="form-check-label fw-semibold" for="online"><i class="bi bi-phone me-2 text-warning"></i>Thanh toán trực tuyến</label>
                        </div>
                        <div id="onlineSubOptions" style="display:none" class="mt-3 ps-2">
                            <div class="d-flex flex-column gap-2">
                                <label class="d-flex align-items-center gap-2 p-2 border rounded" style="cursor:pointer;background:#f0f6ff;border-color:#0068ff!important">
                                    <input class="form-check-input m-0" type="radio" name="online_sub" value="vnpay" checked>
                                    <span class="fw-semibold" style="color:#0068ff">VNPay</span>
                                </label>
                                <label class="d-flex align-items-center gap-2 p-2 border rounded" style="cursor:pointer;background:#e8fbff;border-color:#00a1e4!important">
                                    <input class="form-check-input m-0" type="radio" name="online_sub" value="zalopay">
                                    <span class="fw-semibold" style="color:#00a1e4">ZaloPay</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm" style="position:sticky;top:80px">
                    <div class="card-header fw-bold text-white" style="background:#ff6b35"><i class="bi bi-receipt me-2"></i>Đơn hàng của bạn</div>
                    <div class="card-body">
                        <?php
                        $rep_items = $conn->query("SELECT od.*, p.name FROM order_details od JOIN products p ON od.product_id=p.id WHERE od.order_id={$repay_order['id']}");
                        while ($ri = $rep_items->fetch_assoc()):
                        ?>
                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-truncate me-2" style="max-width:200px"><?= htmlspecialchars($ri['name']) ?> <span class="badge bg-secondary"><?= $ri['quantity'] ?></span></span>
                            <strong class="text-nowrap"><?= formatPrice($ri['unit_price'] * $ri['quantity']) ?></strong>
                        </div>
                        <?php endwhile; ?>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5">
                            <span>Tổng cộng:</span>
                            <span style="color:#ff6b35"><?= formatPrice($repay_order['total_amount']) ?></span>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mt-3 py-2 fw-semibold">
                            <i class="bi bi-bag-check me-2"></i>Xác nhận thanh toán
                        </button>
                        <a href="my_orders.php?id=<?= $repay_order['id'] ?>" class="btn btn-outline-secondary w-100 mt-2 btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>Quay lại
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <?php elseif ($repay_order && $repay_action === 'pay'): ?>
    <?php
    // Nếu đây là action pay và chưa redirect được thì mở thẳng form gọn để người dùng bấm xác nhận
    $pay_label = $repay_order['payment_method'] === 'online' ? 'Thanh toán ngay bằng VNPay' : 'Thanh toán đơn hàng';
    ?>
    <div class="card border-0 shadow-sm mx-auto" style="max-width:720px">
        <div class="card-body text-center py-5">
            <i class="bi bi-credit-card-2-front" style="font-size:4rem;color:#ff6b35"></i>
            <h4 class="fw-bold mt-3"><?= $pay_label ?></h4>
            <p class="text-muted mb-4">Nhấn nút bên dưới để tiếp tục thanh toán đơn <?= htmlspecialchars($repay_order['order_code']) ?>.</p>
            <div class="d-flex gap-2 justify-content-center flex-wrap">
                <a href="vnpay/vnpay_create_payment.php?order_id=<?= (int)$repay_order['id'] ?>" class="btn btn-primary">
                    <i class="bi bi-bag-check me-2"></i>Thanh toán
                </a>
                <a href="checkout.php?repay=<?= (int)$repay_order['id'] ?>" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-repeat me-2"></i>Đổi phương thức thanh toán
                </a>
                <a href="my_orders.php?id=<?= (int)$repay_order['id'] ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Quay lại
                </a>
            </div>
        </div>
    </div>

    <?php else: ?>
    <!-- NORMAL CHECKOUT FORM -->
    <?php if ($error): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i><?= $error ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['zp_error'])): ?>
    <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($_GET['zp_error']) ?></div>
    <?php endif; ?>

    <form method="POST" id="checkoutForm" onsubmit="return validateCheckout()">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header fw-bold bg-white border-0"><i class="bi bi-truck me-2"></i>Thông tin giao hàng</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Người nhận <span class="text-danger">*</span></label>
                                <input type="text" name="receiver_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                <input type="text" name="receiver_phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" required>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="useSaved" name="use_saved_address" value="1" checked onchange="toggleAddress(this)">
                                <label class="form-check-label" for="useSaved">Dùng địa chỉ đã lưu trong tài khoản</label>
                            </div>
                            <div id="savedAddress" class="p-3 bg-light rounded mb-2">
                                <i class="bi bi-geo-alt me-2 text-muted"></i>
                                <strong><?= htmlspecialchars($user['address'].', '.$user['ward'].', '.$user['district'].', '.$user['city']) ?></strong>
                            </div>
                            <div id="newAddress" style="display:none">
                                <div class="row g-2">
                                    <div class="col-12"><input type="text" name="shipping_address" class="form-control" placeholder="Số nhà, tên đường"></div>
                                    <div class="col-md-4"><input type="text" name="ward" class="form-control" placeholder="Phường/Xã"></div>
                                    <div class="col-md-4"><input type="text" name="district" class="form-control" placeholder="Quận/Huyện"></div>
                                    <div class="col-md-4"><input type="text" name="city" class="form-control" placeholder="Tỉnh/TP"></div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label">Ghi chú (tùy chọn)</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Yêu cầu đặc biệt, giờ giao..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header fw-bold bg-white border-0"><i class="bi bi-credit-card me-2"></i>Phương thức thanh toán</div>
                    <div class="card-body">
                        <div class="form-check mb-3 p-3 border rounded">
                            <input class="form-check-input" type="radio" name="payment_method" id="cash" value="cash" checked onchange="showPayment('cash')">
                            <label class="form-check-label fw-semibold" for="cash"><i class="bi bi-cash-coin me-2 text-success"></i>Tiền mặt khi nhận hàng (COD)</label>
                        </div>
                       
                        <div class="form-check p-3 border rounded">
                            <input class="form-check-input" type="radio" name="payment_method" id="online" value="online" onchange="showPayment('online')">
                            <label class="form-check-label fw-semibold" for="online"><i class="bi bi-phone me-2 text-warning"></i>Thanh toán trực tuyến</label>
                        </div>
                        <div id="onlineSubOptions" style="display:none" class="mt-3 ps-2">
                            <div class="d-flex flex-column gap-2">
                                <label class="d-flex align-items-center gap-2 p-2 border rounded" style="cursor:pointer;background:#f0f6ff;border-color:#0068ff!important">
                                    <input class="form-check-input m-0" type="radio" name="online_sub" value="vnpay" checked>
                                    <span class="fw-semibold" style="color:#0068ff">VNPay</span>
                                </label>
                                <label class="d-flex align-items-center gap-2 p-2 border rounded" style="cursor:pointer;background:#e8fbff;border-color:#00a1e4!important">
                                    <input class="form-check-input m-0" type="radio" name="online_sub" value="zalopay">
                                    <span class="fw-semibold" style="color:#00a1e4">ZaloPay</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm" style="position:sticky;top:80px">
                    <div class="card-header fw-bold text-white" style="background:#ff6b35"><i class="bi bi-receipt me-2"></i>Đơn hàng của bạn</div>
                    <div class="card-body">
                        <?php foreach ($cart as $item): ?>
                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-truncate me-2" style="max-width:200px"><?= htmlspecialchars($item['name']) ?> <span class="badge bg-secondary"><?= $item['qty'] ?></span></span>
                            <strong class="text-nowrap"><?= formatPrice($item['price'] * $item['qty']) ?></strong>
                        </div>
                        <?php endforeach; ?>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5">
                            <span>Tổng cộng:</span>
                            <span style="color:#ff6b35"><?= formatPrice($total) ?></span>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mt-3 py-2 fw-semibold">
                            <i class="bi bi-bag-check me-2"></i>Đặt hàng ngay
                        </button>
                        <a href="cart.php" class="btn btn-outline-secondary w-100 mt-2 btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>Quay lại giỏ hàng
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <?php endif; ?>
</div>

<style>
.online-opt { cursor: pointer; display: block; }
.opt-row { display:flex;align-items:center;gap:12px;border:2px solid #dee2e6;border-radius:10px;padding:10px 14px;background:#fff;transition:border-color .2s,box-shadow .2s; }
.opt-name { font-size:.95rem;font-weight:500;color:#333; }
.online-opt:not(.disabled-opt):hover .opt-row { border-color:#0068ff;background:#f0f6ff; }
.online-opt.selected .opt-row { border-color:#0068ff;background:#f0f6ff;box-shadow:0 0 0 3px rgba(0,104,255,.12); }
</style>

<script>
function toggleAddress(cb) {
    document.getElementById('savedAddress').style.display = cb.checked ? 'block' : 'none';
    document.getElementById('newAddress').style.display   = cb.checked ? 'none'  : 'block';
}
function showPayment(m) {
    document.getElementById('onlineSubOptions').style.display = m === 'online'   ? 'block' : 'none';
}
document.addEventListener('DOMContentLoaded', function() {
    var zOpt = document.getElementById('zalopayOpt');
    if (zOpt) {
        zOpt.classList.add('selected');
        zOpt.addEventListener('click', function() {
            document.querySelectorAll('.online-opt:not(.disabled-opt)').forEach(el => el.classList.remove('selected'));
            this.classList.add('selected');
            document.getElementById('zalopayRadio').checked = true;
        });
    }
});
function validateCheckout() {
    const phone = document.querySelector('[name=receiver_phone]').value.trim();
    if (!/^0[0-9]{9,10}$/.test(phone)) { alert('Số điện thoại không hợp lệ!\nPhải bắt đầu bằng số 0 và có 10-11 chữ số.'); return false; }
    if (!document.getElementById('useSaved').checked) {
        const f = ['shipping_address','ward','district','city'];
        for (let n of f) { if (!document.querySelector('[name='+n+']').value.trim()) { alert('Vui lòng điền đầy đủ địa chỉ giao hàng!'); return false; } }
    }
    return true;
}
</script>
<?php require_once 'includes/footer.php'; ?>