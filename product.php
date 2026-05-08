<?php
// product.php — ALL logic before header.php to prevent "headers already sent"
require_once 'includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$sql = "SELECT p.*, c.name as cat_name, SUM(pv.stock_quantity) as total_stock,
        ROUND(p.import_price * (1 + p.profit_rate/100)) as sell_price
        FROM products p JOIN categories c ON p.category_id = c.id
                        JOIN product_varieties pv ON p.id = pv.product_id
        WHERE p.id=$id AND p.status='active'
        GROUP BY p.id";
$product = $conn->query($sql)->fetch_assoc();
$varieties = $conn->query("SELECT pv.*, s.size, c.name as color
    FROM product_varieties pv
    JOIN sizes s ON s.id = pv.size_id
    JOIN colors c ON c.id = pv.color_id
    WHERE pv.product_id = $id
");

if (!$product) {
    // Still need header for proper page render
    $pageTitle = 'Không tìm thấy';
    require_once 'includes/header.php';
    echo "<div class='container my-5'><div class='alert alert-danger'>Sản phẩm không tồn tại.</div></div>";
    require_once 'includes/footer.php';
    exit;
}

// Parse sizes, colors
$colors = [];
$sizes_by_color = [];

while ($v = $varieties->fetch_assoc()) {
    $color_id = $v['color_id'];
    $color_name = $v['color'];
    $size_id = $v['size_id'];
    $size_name = $v['size'];

    if (!isset($colors[$color_id])) {
        $colors[$color_id] = $color_name;
    }

    $sizes_by_color[$color_id][] = [
        'size_id' => $size_id,
        'size' => $size_name,
        'stock' => $v['stock_quantity']
    ];
}
$genderLabel = ['nam' => 'Nam', 'nu' => 'Nữ', 'unisex' => 'Unisex'];

// Handle POST — must be before header.php outputs HTML
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_cart'])) {

    if (!isLoggedIn()) {
        redirect('login.php?redirect=product.php?id=' . $id);
    }

    $qty = max(1, (int)$_POST['quantity']);
    $selected_size_id = (int)($_POST['selected_size_id'] ?? 0);
    $selected_color_id = (int)($_POST['selected_color_id'] ?? 0);

    if (empty($selected_size_id)) {
        redirect('product.php?id=' . $id . '&err=size');
    }
    if (empty($selected_color_id)) {
        redirect('product.php?id=' . $id . '&err=color');
    }

    // Lấy variant theo size_id và color_id
    $variant_sql = "SELECT pv.*, s.size, c.name as color
                    FROM product_varieties pv
                    JOIN sizes s ON s.id = pv.size_id
                    JOIN colors c ON c.id = pv.color_id
                    WHERE pv.product_id = $id 
                      AND pv.size_id = $selected_size_id 
                      AND pv.color_id = $selected_color_id";
    $variant = $conn->query($variant_sql)->fetch_assoc();

    if (!$variant) {
        redirect('product.php?id=' . $id . '&err=stock');
    }

    $stock = (int)$variant['stock_quantity'];

    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    $cart_key = $id . '_' . $variant['size_id'] . '_' . $variant['color_id'];
    
    // Tính tổng số lượng hiện có trong giỏ hàng cho variant này
    $qty_in_cart = 0;
    foreach ($_SESSION['cart'] as $item) {
        if (($item['cart_key'] ?? '') === $cart_key) {
            $qty_in_cart += (int)$item['qty'];
        }
    }

    $total_qty = $qty_in_cart + $qty;

    // Kiểm tra tổng số lượng so với tồn kho
    if ($total_qty > $stock) {
        redirect('product.php?id=' . $id . '&err=stock');
    }

    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if (($item['cart_key'] ?? '') === $cart_key) {
            $item['qty'] += $qty;
            $found = true;
            break;
        }
    }

    if (!$found) {
        $_SESSION['cart'][] = [
            'product_id' => $product['id'],
            'variant_id' => $variant['id'],
            'name'  => $product['name'],
            'price' => $product['sell_price'],
            'qty'   => $qty,
            'image' => $product['image'],
            'size_id'  => $variant['size_id'],
            'color_id' => $variant['color_id'],
            'size'  => $variant['size'],
            'color' => $variant['color'],
            'cart_key' => $cart_key
        ];
    }

    redirect('product.php?id=' . $id . '&added=1');
}


// Flash messages via GET (after PRG redirect)
$msg = '';
if (isset($_GET['added'])) {
    $sz = isset($_GET['sz']) ? ' (Size ' . htmlspecialchars($_GET['sz']) . ')' : '';
    $msg = '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>Đã thêm vào giỏ hàng' . $sz . '! <a href="cart.php">Xem giỏ hàng</a></div>';
} elseif (isset($_GET['err'])) {
    if ($_GET['err'] === 'size')  $msg = '<div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>Vui lòng chọn size trước khi thêm vào giỏ hàng.</div>';
    if ($_GET['err'] === 'color') $msg = '<div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>Vui lòng chọn màu trước khi thêm vào giỏ hàng.</div>';
    if ($_GET['err'] === 'stock') $msg = '<div class="alert alert-warning">Số lượng không hợp lệ hoặc vượt quá tồn kho hiện có!</div>';
}

// NOW safe to output HTML
$pageTitle = $product['name'];
require_once 'includes/header.php';
?>

<div class="container my-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="category.php?id=<?= $product['category_id'] ?>"><?= htmlspecialchars($product['cat_name']) ?></a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($product['name']) ?></li>
        </ol>
    </nav>

    <?= $msg ?>

    <div class="card border-0 shadow-sm p-4">
        <div class="row g-4">
            <!-- Product Image -->
            <div class="col-md-5">
                <?php if ($product['image'] && file_exists('uploads/' . $product['image'])): ?>
                    <img src="uploads/<?= htmlspecialchars($product['image']) ?>" class="img-fluid rounded-3 w-100" style="max-height:420px;object-fit:cover" alt="<?= htmlspecialchars($product['name']) ?>">
                <?php else: ?>
                    <div class="bg-light rounded-3 d-flex align-items-center justify-content-center" style="height:380px">
                        <i class="bi bi-shoe" style="font-size:8rem;color:#ddd"></i>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Product Info -->
            <div class="col-md-7">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge text-white" style="background:#ff6b35"><?= htmlspecialchars($product['cat_name']) ?></span>
                    <?php if ($product['brand']): ?>
                        <span class="badge bg-dark"><?= htmlspecialchars($product['brand']) ?></span>
                    <?php endif; ?>
                    <?php if ($product['gender']): ?>
                        <span class="badge bg-secondary"><?= $genderLabel[$product['gender']] ?? '' ?></span>
                    <?php endif; ?>
                </div>

                <h2 class="fw-bold mb-1"><?= htmlspecialchars($product['name']) ?></h2>

                <!-- Meta info -->
                <div class="text-muted small mb-3 d-flex flex-wrap gap-3">
                    <span><i class="bi bi-upc me-1"></i>Mã SP:
                        <strong class="text-dark"><?= htmlspecialchars($product['code']) ?></strong>
                    </span>

                    <?php if ($product['origin']): ?>
                        <span><i class="bi bi-globe me-1"></i>Xuất xứ:
                            <strong class="text-dark"><?= htmlspecialchars($product['origin']) ?></strong>
                        </span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($colors)): ?>
                    <div class="mb-3">
                        <span class="fw-semibold">
                            <i class="bi bi-palette me-1"></i>Chọn màu:
                        </span>

                        <div class="d-flex gap-2 mt-2">
                            <?php foreach ($colors as $cid => $cname): ?>
                                <button type="button"
                                    class="btn btn-outline-dark color-btn"
                                    data-color-id="<?= $cid ?>"
                                    data-color="<?= htmlspecialchars($cname) ?>"
                                    onclick="selectColor(this)">
                                    <?= htmlspecialchars($cname) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>

                        <input type="hidden" name="selected_color" id="selectedColor">
                    </div>
                <?php endif; ?>

                <!-- Price box -->
                <div class="p-3 rounded-3 mb-3 d-flex align-items-center gap-3" style="background:#fff3ee">
                    <div class="fs-1 fw-bold" style="color:#ff6b35"><?= formatPrice($product['sell_price']) ?></div>
                </div>
                <!-- Stock status -->
                <?php if ($product['total_stock'] > 0): ?>
                    <p class="mb-3">
                        <span id="stockLabel"
                            class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                            <i class="bi bi-check-circle-fill me-1"></i>
                            Vui lòng chọn size
                        </span>
                    </p>
                <?php else: ?>
                    <p class="mb-3"><span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2"><i class="bi bi-x-circle-fill me-1"></i>Hết hàng</span></p>
                <?php endif; ?>

                <!-- Description -->
                <?php if ($product['description']): ?>
                    <div class="mb-3">
                        <p class="text-muted mb-0"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                    </div>
                <?php endif; ?>

                <!-- Material -->
                <?php if ($product['material']): ?>
                    <p class="text-muted small mb-3"><i class="bi bi-layers me-1"></i>Chất liệu: <strong class="text-dark"><?= htmlspecialchars($product['material']) ?></strong></p>
                <?php endif; ?>

                <!-- Voucher Section -->
                <div id="productCoupons" class="mb-4 pt-3 border-top" style="display:none">
                    <h6 class="fw-bold mb-3"><i class="bi bi-ticket-perforated me-2 text-primary"></i>Ưu đãi dành cho bạn</h6>
                    <div id="couponList" class="d-flex flex-column gap-2"></div>
                </div>

                <?php if ($product['total_stock'] > 0): ?>
                    <form method="POST" id="addCartForm">
                        <!-- Size selector -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Chọn size: <span class="text-danger">*</span>
                            </label>

                            <div id="sizeContainer" class="d-flex flex-wrap gap-2 mt-2"></div>

                            <input type="hidden" name="selected_size_id" id="selectedSizeIdInput">
                            <input type="hidden" name="selected_color_id" id="selectedColorIdInput">
                        </div>

                        <!-- Quantity -->
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <label class="fw-semibold">Số lượng:</label>
                            <div class="input-group" style="width:140px">
                                <button type="button" class="btn btn-outline-secondary" onclick="changeQty(-1)">−</button>
                                <input type="number" id="qty" name="quantity" class="form-control text-center fw-bold" value="1" min="1" max="<?= $product['total_stock'] ?>">
                                <button type="button" class="btn btn-outline-secondary" onclick="changeQty(1)">+</button>
                            </div>
                        </div>

                        <button type="submit" name="add_cart" class="btn btn-primary btn-lg px-5">
                            <i class="bi bi-cart-plus me-2"></i>Thêm vào giỏ hàng
                        </button>
                    </form>
                <?php endif; ?>

                <!-- Specs table -->
                <?php
                $specs = array_filter([
                    'Thương hiệu'  => $product['brand'],
                    'Danh mục'     => $product['cat_name'],
                    'Đối tượng'    => $genderLabel[$product['gender']] ?? null,
                    'Chất liệu'    => $product['material'],
                    'Xuất xứ'      => $product['origin'],
                    'Đơn vị'       => $product['unit'],
                ]);
                if ($specs):
                ?>
                    <div class="mt-4 pt-3 border-top">
                        <h6 class="fw-bold mb-3">Thông số kỹ thuật</h6>
                        <table class="table table-sm table-borderless mb-0" style="max-width:400px">
                            <?php foreach ($specs as $label => $val): ?>
                                <tr>
                                    <td class="text-muted" style="width:130px"><?= $label ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($val) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Related products -->
    <?php
    $related = $conn->query("SELECT p.*, ROUND(p.import_price*(1+p.profit_rate/100)) as sell_price
        FROM products p JOIN product_varieties pv ON p.id = pv.product_id WHERE p.category_id={$product['category_id']} AND p.id!=$id AND p.status='active' AND pv.stock_quantity>0 LIMIT 4");
    if ($related->num_rows > 0):
    ?>
        <div class="mt-5">
            <h4 class="section-title mb-4">Sản phẩm cùng danh mục</h4>
            <div class="row g-4">
                <?php while ($rp = $related->fetch_assoc()): ?>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card product-card shadow-sm h-100">
                            <div class="product-img d-flex align-items-center justify-content-center bg-light">
                                <?php if ($rp['image'] && file_exists('uploads/' . $rp['image'])): ?>
                                    <img src="uploads/<?= htmlspecialchars($rp['image']) ?>" style="height:100%;width:100%;object-fit:cover" alt="">
                                <?php else: ?>
                                    <i class="bi bi-shoe fs-1 text-secondary"></i>
                                <?php endif; ?>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <?php if ($rp['brand']): ?>
                                    <small class="text-muted"><?= htmlspecialchars($rp['brand']) ?></small>
                                <?php endif; ?>
                                <h6 class="flex-grow-1 mt-1"><?= htmlspecialchars($rp['name']) ?></h6>
                                <p class="price-tag mb-2"><?= formatPrice($rp['sell_price']) ?></p>
                                <a href="product.php?id=<?= $rp['id'] ?>" class="btn btn-sm btn-outline-primary w-100">Xem chi tiết</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    window.productSellPrice = <?= $product['sell_price'] ?>;
    window.productCoupons = [];

    function changeQty(delta) {
        const input = document.getElementById('qty');
        const max = parseInt(input.max);
        const val = parseInt(input.value) + delta;
        if (val >= 1 && val <= max) {
            input.value = val;
            renderProductCoupons();
        }
    }
    
    document.getElementById('qty').addEventListener('change', function() {
        if (parseInt(this.value) < 1) this.value = 1;
        if (parseInt(this.value) > parseInt(this.max)) this.value = this.max;
        renderProductCoupons();
    });

    function selectSize(btn) {
        // Deselect all
        document.querySelectorAll('.size-btn').forEach(b => {
            b.classList.remove('btn-dark');
            b.classList.add('btn-outline-secondary');
        });
        // Select this one
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-dark');
        const sz = btn.getAttribute('data-size');
        document.getElementById('selectedSizeInput').value = sz;
        document.getElementById('sizeSelected').textContent = '→ Size ' + sz + ' đã chọn';
    }
    const sizesByColor = <?= json_encode($sizes_by_color) ?>;

    function selectColor(btn) {
        // highlight màu
        document.querySelectorAll('.color-btn').forEach(b => {
            b.classList.remove('btn-dark');
            b.classList.add('btn-outline-dark');
        });

        btn.classList.remove('btn-outline-dark');
        btn.classList.add('btn-dark');

        const colorId = btn.dataset.colorId;
        
        if (document.getElementById('selectedColorIdInput')) {
            document.getElementById('selectedColorIdInput').value = colorId;
        }

        // render size theo màu
        const container = document.getElementById('sizeContainer');
        container.innerHTML = '';

        sizesByColor[colorId].forEach(s => {
            const el = document.createElement('button');
            el.type = 'button';
            el.className = 'btn btn-outline-secondary';
            el.innerText = s.size;

            // disable nếu hết hàng
            if (s.stock == 0) {
                el.disabled = true;
                el.classList.add('opacity-50');
            }

            el.onclick = function() {

                document.querySelectorAll('#sizeContainer button').forEach(b => {
                    b.classList.remove('btn-dark');
                    b.classList.add('btn-outline-secondary');
                });

                this.classList.remove('btn-outline-secondary');
                this.classList.add('btn-dark');

                if (document.getElementById('selectedSizeIdInput')) {
                    document.getElementById('selectedSizeIdInput').value = s.size_id;
                }

                // Cập nhật tồn kho theo size
                const stockLabel = document.getElementById('stockLabel');
                const qtyInput = document.getElementById('qty');

                if (s.stock > 0) {
                    stockLabel.innerHTML =
                        '<i class="bi bi-check-circle-fill me-1"></i> Còn hàng · ' + s.stock + ' đôi';
                    if (qtyInput) {
                        qtyInput.max = s.stock;
                        if (parseInt(qtyInput.value) > s.stock) {
                            qtyInput.value = s.stock;
                        }
                    }
                } else {
                    stockLabel.innerHTML =
                        '<i class="bi bi-x-circle-fill me-1"></i> Hết hàng';
                    if (qtyInput) {
                        qtyInput.max = 0;
                        qtyInput.value = 1;
                    }
                }
            };

            container.appendChild(el);
        });
    }

    // Advanced Voucher Logic
    function renderProductCoupons() {
        const container = document.getElementById('couponList');
        const section = document.getElementById('productCoupons');
        const currentQty = parseInt(document.getElementById('qty')?.value || 1);
        const currentTotal = currentQty * window.productSellPrice;

        const validCoupons = window.productCoupons.filter(c => c.min_order <= currentTotal);

        if (validCoupons.length > 0) {
            section.style.display = 'block';
            container.innerHTML = '';
            
            let guestSaved = JSON.parse(localStorage.getItem('guest_coupons') || '[]');

            validCoupons.forEach(c => {
                const isSaved = c.is_saved || guestSaved.includes(c.code);
                const card = document.createElement('div');
                card.className = 'd-flex align-items-center justify-content-between p-2 rounded border bg-light small';
                card.innerHTML = `
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary text-white py-2 px-2" style="border: 1px dashed #fff">${c.code}</span>
                        <div>
                            <div class="fw-bold text-dark">${c.description}</div>
                            <div class="text-muted" style="font-size:0.75rem">Đơn từ ${new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(c.min_order)}</div>
                        </div>
                    </div>
                    <button class="btn btn-sm ${isSaved ? 'btn-secondary disabled' : 'btn-outline-primary'} save-coupon-btn" 
                                data-id="${c.id}" data-code="${c.code}" onclick="saveCoupon(this)">
                            ${isSaved ? 'Đã lưu' : 'Lưu mã'}
                        </button>
                `;
                container.appendChild(card);
            });
        } else {
            section.style.display = 'none';
        }
    }

    async function loadProductCoupons() {
        const pid = <?= $id ?>;
        try {
            const res = await fetch(`api/get_product_coupons.php?product_id=${pid}`);
            window.productCoupons = await res.json();
            renderProductCoupons();
        } catch (err) {
            console.error('Lỗi khi tải coupon:', err);
        }
    }

    async function saveCoupon(btn) {
        const cid = btn.dataset.id;
        const code = btn.dataset.code || (btn.closest('.d-flex')?.querySelector('.badge')?.innerText || '');

        if (document.body.classList.contains('logged-in') || <?= isLoggedIn() ? 'true' : 'false' ?>) {
            // Save to DB
            try {
                const formData = new FormData();
                formData.append('coupon_id', cid);
                const res = await fetch('api/save_coupon.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                
                if (data.success) {
                    btn.innerText = 'Đã lưu';
                    btn.classList.remove('btn-outline-primary');
                    btn.classList.add('btn-secondary', 'disabled');
                    alert(data.message);
                } else {
                    alert(data.message);
                }
            } catch (err) {
                alert('Lỗi kết nối. Vui lòng thử lại.');
            }
        } else {
            // Save to localStorage for guests
            let saved = JSON.parse(localStorage.getItem('guest_coupons') || '[]');
            if (!saved.includes(code)) {
                saved.push(code);
                localStorage.setItem('guest_coupons', JSON.stringify(saved));
                btn.innerText = 'Đã lưu';
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-secondary', 'disabled');
                alert('Đã lưu mã vào máy của bạn! Đăng nhập để lưu vĩnh viễn.');
            } else {
                alert('Mã này đã được lưu.');
            }
        }
    }

    document.addEventListener('DOMContentLoaded', loadProductCoupons);
</script>

<?php require_once 'includes/footer.php'; ?>