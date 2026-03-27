<?php
// checkout.php - ALL logic BEFORE header.php to prevent "headers already sent"
require_once 'includes/db.php';
if (!isLoggedIn()) redirect('login.php?redirect=checkout.php');

$user_id = $_SESSION['user_id'];
$user    = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

$cart  = $_SESSION['cart'] ?? [];
$total = 0;
foreach ($cart as $item) $total += $item['price'] * $item['qty'];

// Allow showing success/fail pages even when cart is empty
$has_special_get = isset($_GET['success']) || isset($_GET['zp_fail']) || isset($_GET['zp_success']);
if (empty($cart) && !$has_special_get) redirect('cart.php');

$error      = '';
$order_done = false;
$zp_fail    = false;
$ord        = null;

// ── Handle POST (đặt hàng) ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $use_saved = isset($_POST['use_saved_address']) && $_POST['use_saved_address'] == '1';
    $receiver  = sanitize($conn, $_POST['receiver_name'] ?? '');
    $phone     = sanitize($conn, $_POST['receiver_phone'] ?? '');
    $address   = $use_saved ? $user['address']  : sanitize($conn, $_POST['shipping_address'] ?? '');
    $ward      = $use_saved ? $user['ward']      : sanitize($conn, $_POST['ward'] ?? '');
    $district  = $use_saved ? $user['district']  : sanitize($conn, $_POST['district'] ?? '');
    $city      = $use_saved ? $user['city']      : sanitize($conn, $_POST['city'] ?? '');
    $payment   = sanitize($conn, $_POST['payment_method'] ?? 'cash');
    $notes     = sanitize($conn, $_POST['notes'] ?? '');

    if (!$receiver || !$phone || !$address || !$ward || !$district || !$city) {
        $error = 'Vui lòng điền đầy đủ thông tin giao hàng.';
    } elseif (!preg_match('/^0[0-9]{9,10}$/', $phone)) {
        $error = 'Số điện thoại không hợp lệ. Phải bắt đầu bằng số 0 và có 10-11 chữ số.';
    } else {
        $order_code = generateCode('DH');
        $stmt = $conn->prepare("INSERT INTO orders (order_code,user_id,receiver_name,receiver_phone,shipping_address,ward,district,city,payment_method,total_amount,notes,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
        // Online payment → status=pending (chờ ZaloPay confirm), others → pending cũng được
        $init_status = 'pending';
        $stmt->bind_param('sisssssssdss', $order_code, $user_id, $receiver, $phone, $address, $ward, $district, $city, $payment, $total, $notes, $init_status);

        if ($stmt->execute()) {
            $order_id = $conn->insert_id;
            foreach ($cart as $item) {
                $pid   = (int)$item['product_id'];
                $qty   = (int)$item['qty'];
                $price = (float)$item['price'];
                $conn->query("INSERT INTO order_details (order_id,product_id,quantity,unit_price) VALUES ($order_id,$pid,$qty,$price)");
                $conn->query("UPDATE products SET stock_quantity = stock_quantity - $qty WHERE id=$pid AND stock_quantity >= $qty");
            }

            if ($payment === 'online') {
                // Online: GIỮ giỏ hàng, redirect sang ZaloPay
                // Giỏ hàng chỉ xóa khi callback ZaloPay xác nhận thành công
                redirect('zalo_pay/zalopay_create.php?order_id=' . $order_id);
            } else {
                // COD / Chuyển khoản: xóa giỏ, vào trang thành công
                $_SESSION['cart'] = [];
                redirect('checkout.php?success=' . $order_id);
            }
        } else {
            $error = 'Có lỗi khi tạo đơn hàng. Vui lòng thử lại.';
        }
    }
}

// ── ZaloPay fail: hiển thị trang thất bại, giỏ hàng vẫn còn ─────────────
if (isset($_GET['zp_fail'])) {
    $oid     = (int)$_GET['order_id'];
    $zp_fail = true;
    $ord     = $conn->query("SELECT * FROM orders WHERE id=$oid AND user_id=$user_id")->fetch_assoc();
    $pageTitle = 'Thanh toán thất bại';
    require_once 'includes/header.php';
    ?>
    <div class="container my-5" style="max-width:560px">
        <div class="card border-0 shadow text-center py-5 px-4">
            <div class="mb-3"><i class="bi bi-x-circle-fill text-danger" style="font-size:4rem"></i></div>
            <h3 class="text-danger fw-bold">Thanh toán không thành công</h3>
            <p class="text-muted mb-1">Giao dịch đã bị huỷ hoặc thất bại.</p>
            <?php if ($ord): ?>
            <p class="text-muted mb-4 small">Đơn hàng <strong><?= htmlspecialchars($ord['order_code']) ?></strong> vẫn còn lưu, bạn có thể thử thanh toán lại.</p>
            <?php endif; ?>
            <div class="d-flex flex-column gap-2">
                <?php if ($ord): ?>
                <a href="zalo_pay/zalopay_create.php?order_id=<?= $oid ?>" class="btn btn-primary fw-semibold">
                    <i class="bi bi-arrow-repeat me-2"></i>Thử thanh toán ZaloPay lại
                </a>
                <a href="my_orders.php" class="btn btn-outline-secondary">
                    <i class="bi bi-bag me-2"></i>Xem đơn hàng của tôi
                </a>
                <?php endif; ?>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-house me-2"></i>Tiếp tục mua sắm
                </a>
            </div>
        </div>
    </div>
    <?php
    require_once 'includes/footer.php';
    exit;
}

// ── Success page (COD/Transfer PRG, hoặc ZaloPay success) ────────────────
if (isset($_GET['success']) || isset($_GET['zp_success'])) {
    $oid = (int)($_GET['success'] ?? $_GET['zp_success']);
    $ord = $conn->query("SELECT * FROM orders WHERE id=$oid AND user_id=$user_id")->fetch_assoc();
    if ($ord) $order_done = true;
}

$pageTitle = $order_done ? 'Đặt hàng thành công' : 'Thanh toán';
require_once 'includes/header.php';
?>
<div class="container my-4">
    <h3 class="section-title mb-4"><?= $order_done ? 'Xác nhận đơn hàng' : 'Thanh Toán' ?></h3>

    <?php if ($order_done): ?>
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
                        <p class="mb-0"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($ord['shipping_address'].', '.$ord['ward'].', '.$ord['district'].', '.$ord['city']) ?></p>
                    </div>
                    <?php $pm=['cash'=>'Tiền mặt (COD)','transfer'=>'Chuyển khoản','online'=>'ZaloPay']; ?>
                    <?php if ($ord['payment_method']==='transfer'): ?>
                    <div class="alert alert-info mt-2 py-2 small mb-0">
                        <strong>Chuyển khoản:</strong> Vietcombank · STK: 1234567890 · Chủ TK: SNEAKER SHOP<br>
                        Nội dung: <strong><?= htmlspecialchars($ord['order_code']) ?></strong>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2 justify-content-center">
                <a href="my_orders.php" class="btn btn-primary"><i class="bi bi-bag-check me-2"></i>Xem đơn hàng</a>
                <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-house me-2"></i>Về trang chủ</a>
            </div>
        </div>
    </div>

    <?php else: ?>
    <?php if ($error): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i><?= $error ?></div>
    <?php endif; ?>
    <form method="POST" id="checkoutForm" onsubmit="return validateCheckout()">
        <div class="row g-4">
            <div class="col-lg-7">
                <!-- Delivery info -->
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

                <!-- Payment method -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header fw-bold bg-white border-0"><i class="bi bi-credit-card me-2"></i>Phương thức thanh toán</div>
                    <div class="card-body">
                        <!-- COD -->
                        <div class="form-check mb-3 p-3 border rounded">
                            <input class="form-check-input" type="radio" name="payment_method" id="cash" value="cash" checked onchange="showPayment('cash')">
                            <label class="form-check-label fw-semibold" for="cash">
                                <i class="bi bi-cash-coin me-2 text-success"></i>Tiền mặt khi nhận hàng (COD)
                            </label>
                        </div>
                        <!-- Chuyển khoản -->
                        <div class="form-check mb-3 p-3 border rounded">
                            <input class="form-check-input" type="radio" name="payment_method" id="transfer" value="transfer" onchange="showPayment('transfer')">
                            <label class="form-check-label fw-semibold" for="transfer">
                                <i class="bi bi-bank me-2 text-primary"></i>Chuyển khoản ngân hàng
                            </label>
                        </div>
                        <div id="transferInfo" class="alert alert-info small py-2 mb-3" style="display:none">
                            Vietcombank · STK: <strong>1234567890</strong> · Chủ TK: SNEAKER SHOP
                        </div>
                        <!-- Online payment -->
                        <div class="p-3 border rounded">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="online" value="online" onchange="showPayment('online')">
                                <label class="form-check-label fw-semibold" for="online">
                                    <i class="bi bi-lightning-charge me-2 text-warning"></i>Thanh toán trực tuyến
                                </label>
                            </div>
                            <!-- 3 options, chỉ ZaloPay active -->
                            <div id="onlineOptions" style="display:none;padding-left:8px">
                                <!-- MoMo - disabled -->
                                <div class="d-flex align-items-center gap-3 p-2 rounded mb-2 position-relative"
                                     style="background:#f8f8f8;opacity:.45;cursor:not-allowed;border:1px solid #eee">
                                    <img src="https://upload.wikimedia.org/wikipedia/vi/f/fe/MoMo_Logo.png"
                                         style="width:36px;height:36px;border-radius:8px;object-fit:cover" alt="MoMo">
                                    <div>
                                        <div class="fw-semibold small">MoMo</div>
                                        <div class="text-muted" style="font-size:.75rem">Chưa hỗ trợ</div>
                                    </div>
                                    <span class="badge bg-secondary ms-auto" style="font-size:.7rem">Sắp có</span>
                                </div>
                                <!-- VNPay - disabled -->
                                <div class="d-flex align-items-center gap-3 p-2 rounded mb-2 position-relative"
                                     style="background:#f8f8f8;opacity:.45;cursor:not-allowed;border:1px solid #eee">
                                    <img src="https://vinadesign.vn/uploads/images/2023/05/vnpay-logo-vinadesign-25-12-57-55.jpg"
                                         style="width:36px;height:36px;border-radius:8px;object-fit:cover" alt="VNPay">
                                    <div>
                                        <div class="fw-semibold small">VNPay</div>
                                        <div class="text-muted" style="font-size:.75rem">Chưa hỗ trợ</div>
                                    </div>
                                    <span class="badge bg-secondary ms-auto" style="font-size:.7rem">Sắp có</span>
                                </div>
                                <!-- ZaloPay - active -->
                                <div class="d-flex align-items-center gap-3 p-2 rounded"
                                     style="background:#e8f4ff;border:1.5px solid #0068ff;cursor:pointer"
                                     onclick="document.getElementById('online').checked=true;showPayment('online')">
                                    <img src="https://cdn.haitrieu.com/wp-content/uploads/2022/10/Logo-ZaloPay-Square.png"
                                         style="width:36px;height:36px;border-radius:8px;object-fit:cover" alt="ZaloPay">
                                    <div>
                                        <div class="fw-semibold small" style="color:#0068ff">ZaloPay</div>
                                        <div class="text-muted" style="font-size:.75rem">Quét QR / App ZaloPay</div>
                                    </div>
                                    <span class="badge ms-auto" style="background:#0068ff;font-size:.7rem">Khả dụng</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order summary -->
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
<script>
function toggleAddress(cb) {
    document.getElementById('savedAddress').style.display = cb.checked ? 'block' : 'none';
    document.getElementById('newAddress').style.display   = cb.checked ? 'none'  : 'block';
}
function showPayment(m) {
    document.getElementById('transferInfo').style.display  = m === 'transfer' ? 'block' : 'none';
    document.getElementById('onlineOptions').style.display = m === 'online'   ? 'block' : 'none';
}
function validateCheckout() {
    const phone = document.querySelector('[name=receiver_phone]').value.trim();
    if (!/^0[0-9]{9,10}$/.test(phone)) {
        alert('Số điện thoại không hợp lệ!\nPhải bắt đầu bằng số 0 và có 10-11 chữ số.');
        return false;
    }
    if (!document.getElementById('useSaved').checked) {
        const f = ['shipping_address','ward','district','city'];
        for (let n of f) { if (!document.querySelector('[name='+n+']').value.trim()) { alert('Vui lòng điền đầy đủ địa chỉ giao hàng!'); return false; } }
    }
    return true;
}
</script>
<?php require_once 'includes/footer.php'; ?>