<?php
// checkout.php
require_once 'includes/header.php';
if (!isLoggedIn()) redirect('login.php?redirect=checkout.php');

$pageTitle = 'Thanh toán';
$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) redirect('cart.php');

$total = 0;
foreach ($cart as $item) $total += $item['price'] * $item['qty'];

// Get user info
$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

$error = '';
$order_done = false;
$order_code = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $use_saved = isset($_POST['use_saved_address']) && $_POST['use_saved_address'] == '1';
    $receiver  = sanitize($conn, $_POST['receiver_name'] ?? '');
    $phone     = sanitize($conn, $_POST['receiver_phone'] ?? '');
    $address   = $use_saved ? $user['address'] : sanitize($conn, $_POST['shipping_address'] ?? '');
    $ward      = $use_saved ? $user['ward'] : sanitize($conn, $_POST['ward'] ?? '');
    $district  = $use_saved ? $user['district'] : sanitize($conn, $_POST['district'] ?? '');
    $city      = $use_saved ? $user['city'] : sanitize($conn, $_POST['city'] ?? '');
    $payment   = sanitize($conn, $_POST['payment_method'] ?? 'cash');
    $notes     = sanitize($conn, $_POST['notes'] ?? '');

    if (!$receiver || !$phone || !$address || !$ward || !$district || !$city) {
        $error = 'Vui lòng điền đầy đủ thông tin giao hàng.';
    } else {
        $order_code = generateCode('DH');
        $stmt = $conn->prepare("INSERT INTO orders (order_code,user_id,receiver_name,receiver_phone,shipping_address,ward,district,city,payment_method,total_amount,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('sisssssssds', $order_code, $user_id, $receiver, $phone, $address, $ward, $district, $city, $payment, $total, $notes);

        if ($stmt->execute()) {
            $order_id = $conn->insert_id;
            // Insert order details & update stock
            foreach ($cart as $item) {
                $pid = $item['product_id'];
                $qty = $item['qty'];
                $price = $item['price'];
                $conn->query("INSERT INTO order_details (order_id,product_id,quantity,unit_price) VALUES ($order_id,$pid,$qty,$price)");
                $conn->query("UPDATE products SET stock_quantity = stock_quantity - $qty WHERE id=$pid AND stock_quantity >= $qty");
            }
            $_SESSION['cart'] = [];
            $_SESSION['last_order'] = $order_id;
            $order_done = true;
        } else {
            $error = 'Có lỗi khi tạo đơn hàng. Vui lòng thử lại.';
        }
    }
}
?>

<div class="container my-4">
    <h3 class="section-title mb-4">Thanh Toán</h3>

    <?php if ($order_done): ?>
    <!-- Order success -->
    <?php $ord = $conn->query("SELECT * FROM orders WHERE id={$_SESSION['last_order']}")->fetch_assoc(); ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <div class="mb-4">
                <i class="bi bi-check-circle-fill" style="font-size:5rem;color:#28a745"></i>
            </div>
            <h3 class="text-success fw-bold">Đặt hàng thành công!</h3>
            <p class="text-muted mb-1">Mã đơn hàng: <strong style="color:#ff6b35"><?= htmlspecialchars($ord['order_code']) ?></strong></p>
            <p class="text-muted mb-4">Chúng tôi sẽ liên hệ xác nhận đơn hàng sớm nhất.</p>

            <div class="card mx-auto" style="max-width:500px">
                <div class="card-header fw-bold">Tóm tắt đơn hàng</div>
                <div class="card-body text-start">
                    <?php
                    $details = $conn->query("SELECT od.*, p.name FROM order_details od JOIN products p ON od.product_id=p.id WHERE od.order_id={$ord['id']}");
                    while ($d = $details->fetch_assoc()):
                    ?>
                    <div class="d-flex justify-content-between mb-1">
                        <span><?= htmlspecialchars($d['name']) ?> x<?= $d['quantity'] ?></span>
                        <strong><?= formatPrice($d['unit_price'] * $d['quantity']) ?></strong>
                    </div>
                    <?php endwhile; ?>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold" style="color:#ff6b35">
                        <span>Tổng cộng:</span>
                        <span><?= formatPrice($ord['total_amount']) ?></span>
                    </div>
                    <hr class="my-2">
                    <small class="text-muted">
                        <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($ord['shipping_address'].', '.$ord['ward'].', '.$ord['district'].', '.$ord['city']) ?><br>
                        <i class="bi bi-telephone me-1"></i><?= htmlspecialchars($ord['receiver_phone']) ?><br>
                        <i class="bi bi-credit-card me-1"></i>
                        <?php
                        $pm = ['cash'=>'Tiền mặt khi nhận hàng','transfer'=>'Chuyển khoản','online'=>'Thanh toán trực tuyến'];
                        echo $pm[$ord['payment_method']] ?? $ord['payment_method'];
                        ?>
                        <?php if ($ord['payment_method'] === 'transfer'): ?>
                        <div class="alert alert-info mt-2 py-2 small">
                            <strong>Thông tin chuyển khoản:</strong><br>
                            Ngân hàng: Vietcombank<br>
                            STK: 1234567890 - Chủ TK: SNEAKER SHOP<br>
                            Nội dung: <?= htmlspecialchars($ord['order_code']) ?>
                        </div>
                        <?php endif; ?>
                    </small>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2 justify-content-center">
                <a href="my_orders.php" class="btn btn-primary"><i class="bi bi-bag-check me-2"></i>Xem đơn hàng</a>
                <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-house me-2"></i>Về trang chủ</a>
            </div>
        </div>
    </div>

    <?php else: ?>
    <!-- Checkout form -->
    <?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" onsubmit="return validateCheckout()">
        <div class="row g-4">
            <!-- Delivery info -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header fw-bold"><i class="bi bi-truck me-2"></i>Thông tin giao hàng</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Người nhận <span class="text-danger">*</span></label>
                                <input type="text" name="receiver_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                <input type="tel" name="receiver_phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" required>
                            </div>
                        </div>

                        <div class="mt-3">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="useSaved" name="use_saved_address" value="1" checked onchange="toggleAddress(this)">
                                <label class="form-check-label" for="useSaved">Dùng địa chỉ đã lưu</label>
                            </div>

                            <div id="savedAddress" class="p-3 bg-light rounded mb-3">
                                <i class="bi bi-geo-alt me-2 text-muted"></i>
                                <strong><?= htmlspecialchars($user['address'] . ', ' . $user['ward'] . ', ' . $user['district'] . ', ' . $user['city']) ?></strong>
                            </div>

                            <div id="newAddress" style="display:none">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Địa chỉ</label>
                                        <input type="text" name="shipping_address" class="form-control" placeholder="Số nhà, tên đường">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" name="ward" class="form-control" placeholder="Phường/Xã">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" name="district" class="form-control" placeholder="Quận/Huyện">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" name="city" class="form-control" placeholder="Tỉnh/Thành phố">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Ghi chú</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Ghi chú cho đơn hàng (tùy chọn)"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Payment -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header fw-bold"><i class="bi bi-credit-card me-2"></i>Phương thức thanh toán</div>
                    <div class="card-body">
                        <div class="form-check mb-3 p-3 border rounded">
                            <input class="form-check-input" type="radio" name="payment_method" id="cash" value="cash" checked onchange="showPayment('cash')">
                            <label class="form-check-label fw-semibold" for="cash">
                                <i class="bi bi-cash-coin me-2 text-success"></i>Tiền mặt khi nhận hàng (COD)
                            </label>
                        </div>
                        <div class="form-check mb-3 p-3 border rounded">
                            <input class="form-check-input" type="radio" name="payment_method" id="transfer" value="transfer" onchange="showPayment('transfer')">
                            <label class="form-check-label fw-semibold" for="transfer">
                                <i class="bi bi-bank me-2 text-primary"></i>Chuyển khoản ngân hàng
                            </label>
                        </div>
                        <div id="transferInfo" class="alert alert-info small" style="display:none">
                            <strong>Thông tin chuyển khoản:</strong><br>
                            Ngân hàng: Vietcombank<br>
                            STK: 1234567890 - Chủ TK: SNEAKER SHOP
                        </div>
                        <div class="form-check p-3 border rounded">
                            <input class="form-check-input" type="radio" name="payment_method" id="online" value="online" onchange="showPayment('online')">
                            <label class="form-check-label fw-semibold" for="online">
                                <i class="bi bi-phone me-2 text-warning"></i>Thanh toán trực tuyến (MoMo, VNPay...)
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order summary -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm sticky-top" style="top:80px">
                    <div class="card-header fw-bold" style="background:#ff6b35;color:white"><i class="bi bi-receipt me-2"></i>Đơn hàng của bạn</div>
                    <div class="card-body">
                        <?php foreach ($cart as $item): ?>
                        <div class="d-flex justify-content-between mb-2 small">
                            <span><?= htmlspecialchars($item['name']) ?> <span class="badge bg-secondary"><?= $item['qty'] ?></span></span>
                            <strong><?= formatPrice($item['price'] * $item['qty']) ?></strong>
                        </div>
                        <?php endforeach; ?>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold" style="font-size:1.1rem">
                            <span>Tổng cộng:</span>
                            <span style="color:#ff6b35"><?= formatPrice($total) ?></span>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mt-3 py-2 fw-semibold">
                            <i class="bi bi-bag-check me-2"></i>Đặt hàng
                        </button>
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
    document.getElementById('newAddress').style.display = cb.checked ? 'none' : 'block';
}
function showPayment(method) {
    document.getElementById('transferInfo').style.display = method === 'transfer' ? 'block' : 'none';
}
function validateCheckout() {
    const useSaved = document.getElementById('useSaved').checked;
    if (!useSaved) {
        const fields = ['shipping_address','ward','district','city'];
        for (let f of fields) {
            if (!document.querySelector('[name='+f+']').value.trim()) {
                alert('Vui lòng điền đầy đủ địa chỉ giao hàng!');
                return false;
            }
        }
    }
    return true;
}
</script>

<?php require_once 'includes/footer.php'; ?>
