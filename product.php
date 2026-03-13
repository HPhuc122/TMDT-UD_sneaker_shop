<?php
// product.php
require_once 'includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$sql = "SELECT p.*, c.name as cat_name,
        ROUND(p.import_price * (1 + p.profit_rate/100)) as sell_price
        FROM products p JOIN categories c ON p.category_id = c.id
        WHERE p.id=$id AND p.status='active'";
$result = $conn->query($sql);
$product = $result->fetch_assoc();

if (!$product) {
    echo "<div class='container my-5'><div class='alert alert-danger'>Sản phẩm không tồn tại.</div></div>";
    require_once 'includes/footer.php';
    exit;
}

$pageTitle = $product['name'];

// Handle add to cart
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_cart'])) {
    if (!isLoggedIn()) {
        redirect('login.php?redirect=product.php?id=' . $id);
    }
    $qty = max(1, (int)$_POST['quantity']);
    if ($qty > $product['stock_quantity']) {
        $msg = '<div class="alert alert-warning">Số lượng tồn kho không đủ!</div>';
    } else {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['product_id'] == $id) {
                $item['qty'] = min($item['qty'] + $qty, $product['stock_quantity']);
                $found = true;
                break;
            }
        }
        if (!$found) {
            $_SESSION['cart'][] = [
                'product_id' => $id,
                'name' => $product['name'],
                'price' => $product['sell_price'],
                'qty' => $qty,
                'image' => $product['image']
            ];
        }
        $msg = '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>Đã thêm vào giỏ hàng! <a href="cart.php">Xem giỏ hàng</a></div>';
    }
}
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
                <img src="uploads/<?= htmlspecialchars($product['image']) ?>" class="img-fluid rounded-3" style="max-height:400px;width:100%;object-fit:cover" alt="<?= htmlspecialchars($product['name']) ?>">
                <?php else: ?>
                <div class="bg-light rounded-3 d-flex align-items-center justify-content-center" style="height:350px">
                    <i class="bi bi-shoe" style="font-size:8rem;color:#ccc"></i>
                </div>
                <?php endif; ?>
            </div>

            <!-- Product Info -->
            <div class="col-md-7">
                <span class="badge text-white mb-2" style="background:#ff6b35"><?= htmlspecialchars($product['cat_name']) ?></span>
                <h2 class="fw-bold"><?= htmlspecialchars($product['name']) ?></h2>
                <p class="text-muted small mb-1">Mã SP: <strong><?= htmlspecialchars($product['code']) ?></strong></p>
                <p class="text-muted small mb-3">Đơn vị: <?= htmlspecialchars($product['unit']) ?></p>

                <div class="p-3 rounded-3 mb-3" style="background:#fff3ee">
                    <div class="fs-2 fw-bold" style="color:#ff6b35"><?= formatPrice($product['sell_price']) ?></div>
                </div>

                <?php if ($product['stock_quantity'] > 0): ?>
                <p class="text-success mb-3"><i class="bi bi-check-circle-fill me-1"></i>Còn hàng (<?= $product['stock_quantity'] ?> <?= htmlspecialchars($product['unit']) ?>)</p>
                <?php else: ?>
                <p class="text-danger mb-3"><i class="bi bi-x-circle-fill me-1"></i>Hết hàng</p>
                <?php endif; ?>

                <?php if ($product['description']): ?>
                <div class="mb-3">
                    <h6>Mô tả sản phẩm:</h6>
                    <p class="text-muted"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                </div>
                <?php endif; ?>

                <?php if ($product['stock_quantity'] > 0): ?>
                <form method="POST">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <label class="fw-semibold">Số lượng:</label>
                        <div class="input-group" style="width:140px">
                            <button type="button" class="btn btn-outline-secondary" onclick="changeQty(-1)">-</button>
                            <input type="number" id="qty" name="quantity" class="form-control text-center" value="1" min="1" max="<?= $product['stock_quantity'] ?>">
                            <button type="button" class="btn btn-outline-secondary" onclick="changeQty(1)">+</button>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" name="add_cart" class="btn btn-primary btn-lg">
                            <i class="bi bi-cart-plus me-2"></i>Thêm vào giỏ hàng
                        </button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Related products -->
    <?php
    $related = $conn->query("SELECT p.*, ROUND(p.import_price*(1+p.profit_rate/100)) as sell_price
        FROM products p WHERE p.category_id={$product['category_id']} AND p.id!=$id AND p.status='active' LIMIT 4");
    if ($related->num_rows > 0):
    ?>
    <div class="mt-5">
        <h4 class="section-title mb-4">Sản phẩm cùng danh mục</h4>
        <div class="row g-4">
            <?php while ($rp = $related->fetch_assoc()): ?>
            <div class="col-sm-6 col-lg-3">
                <div class="card product-card shadow-sm">
                    <div class="product-img d-flex align-items-center justify-content-center bg-light">
                        <?php if ($rp['image'] && file_exists('uploads/' . $rp['image'])): ?>
                        <img src="uploads/<?= htmlspecialchars($rp['image']) ?>" style="height:100%;width:100%;object-fit:cover" alt="">
                        <?php else: ?>
                        <i class="bi bi-shoe fs-1 text-secondary"></i>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h6><?= htmlspecialchars($rp['name']) ?></h6>
                        <p class="price-tag"><?= formatPrice($rp['sell_price']) ?></p>
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
function changeQty(delta) {
    const input = document.getElementById('qty');
    const max = parseInt(input.max);
    const val = parseInt(input.value) + delta;
    if (val >= 1 && val <= max) input.value = val;
}
</script>

<?php require_once 'includes/footer.php'; ?>
