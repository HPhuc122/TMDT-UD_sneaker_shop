<?php
require_once 'includes/header.php';

$cat_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

$category = null;
if ($cat_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id=?");
    $stmt->bind_param("i", $cat_id);
    $stmt->execute();
    $category = $stmt->get_result()->fetch_assoc();
}

$where = "p.status = 'active'";
$params = [];
$types = "";

if ($cat_id > 0) {
    $where .= " AND p.category_id=?";
    $params[] = $cat_id;
    $types .= "i";
}
$sql_count = " SELECT COUNT(DISTINCT p.id) as cnt FROM products p JOIN product_varieties pv ON p.id = pv.product_id
WHERE $where AND pv.stock_quantity > 0";

$stmt = $conn->prepare($sql_count);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['cnt'];

$total_pages = ceil($total / $per_page);

$sql = " SELECT p.*, c.name as cat_name, MIN(pv.price) as sell_price, SUM(pv.stock_quantity) as total_stock
        FROM products p JOIN categories c ON p.category_id = c.id
                        JOIN product_varieties pv ON p.id = pv.product_id
        WHERE $where AND pv.stock_quantity > 0
        GROUP BY p.id
        ORDER BY p.created_at DESC
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);

$params[] = $per_page;
$params[] = $offset;
$types .= "ii";

$stmt->bind_param($types, ...$params);

$stmt->execute();
$products = $stmt->get_result();

$pageTitle = $category ? $category['name'] : 'Tất cả sản phẩm';
?>

<div class="container my-4">
    <h4><?= htmlspecialchars($pageTitle) ?></h4>

    <div class="row g-4">
        <?php while ($p = $products->fetch_assoc()): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card product-card h-100 shadow-sm">

                    <?php if ($p['image'] && file_exists('uploads/' . $p['image'])): ?>
                        <img src="uploads/<?= htmlspecialchars($p['image']) ?>" class="card-img-top product-img" alt="<?= htmlspecialchars($p['name']) ?>">
                    <?php else: ?>
                        <div class="product-img d-flex align-items-center justify-content-center bg-light">
                            <i class="bi bi-shoe fs-1 text-secondary"></i>
                        </div>
                    <?php endif; ?>

                    <div class="card-body d-flex flex-column">
                        <span class="badge bg-dark mb-2"><?= htmlspecialchars($p['cat_name']) ?></span>

                        <h6><?= htmlspecialchars($p['name']) ?></h6>

                        <!-- GIÁ -->
                        <p class="text-danger fw-bold">
                            <?= formatPrice($p['sell_price']) ?>
                        </p>

                        <!-- STOCK -->
                        <?php if ($p['total_stock'] <= 5): ?>
                            <small class="text-danger">
                                Còn <?= $p['total_stock'] ?> sản phẩm
                            </small>
                        <?php endif; ?>

                        <a href="product.php?id=<?= $p['id'] ?>" class="btn btn-primary mt-auto">
                            Xem chi tiết
                        </a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>