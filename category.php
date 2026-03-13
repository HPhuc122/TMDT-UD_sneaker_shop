<?php
// category.php
require_once 'includes/header.php';

$cat_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

$category = null;
if ($cat_id > 0) {
    $result = $conn->query("SELECT * FROM categories WHERE id=$cat_id");
    $category = $result->fetch_assoc();
}

$where = "p.status = 'active' AND p.stock_quantity > 0";
if ($cat_id > 0) $where .= " AND p.category_id=$cat_id";

$total_result = $conn->query("SELECT COUNT(*) as cnt FROM products p WHERE $where");
$total = $total_result->fetch_assoc()['cnt'];
$total_pages = ceil($total / $per_page);

$sql = "SELECT p.*, c.name as cat_name,
        ROUND(p.import_price * (1 + p.profit_rate/100)) as sell_price
        FROM products p JOIN categories c ON p.category_id = c.id
        WHERE $where ORDER BY p.created_at DESC
        LIMIT $per_page OFFSET $offset";
$products = $conn->query($sql);

$pageTitle = $category ? $category['name'] : 'Tất cả sản phẩm';
?>

<div class="container my-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($pageTitle) ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Sidebar categories -->
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header fw-bold" style="background:#ff6b35; color:white">
                    <i class="bi bi-grid me-2"></i>Danh mục
                </div>
                <div class="list-group list-group-flush">
                    <a href="category.php" class="list-group-item list-group-item-action <?= !$cat_id ? 'active' : '' ?>">
                        Tất cả sản phẩm
                    </a>
                    <?php
                    $cats = $conn->query("SELECT * FROM categories ORDER BY name");
                    while ($c = $cats->fetch_assoc()):
                    ?>
                    <a href="category.php?id=<?= $c['id'] ?>" class="list-group-item list-group-item-action <?= $cat_id == $c['id'] ? 'active' : '' ?>">
                        <?= htmlspecialchars($c['name']) ?>
                    </a>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <!-- Products grid -->
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="section-title mb-0"><?= htmlspecialchars($pageTitle) ?></h4>
                <small class="text-muted">Hiển thị <?= $total ?> sản phẩm</small>
            </div>

            <?php if ($products->num_rows === 0): ?>
            <div class="alert alert-info">Không có sản phẩm nào trong danh mục này.</div>
            <?php else: ?>
            <div class="row g-4">
                <?php while ($p = $products->fetch_assoc()): ?>
                <div class="col-sm-6 col-lg-4">
                    <div class="card product-card h-100 shadow-sm">
                        <?php if ($p['image'] && file_exists('uploads/' . $p['image'])): ?>
                        <img src="uploads/<?= htmlspecialchars($p['image']) ?>" class="card-img-top product-img" alt="<?= htmlspecialchars($p['name']) ?>">
                        <?php else: ?>
                        <div class="product-img d-flex align-items-center justify-content-center bg-light">
                            <i class="bi bi-shoe fs-1 text-secondary"></i>
                        </div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <span class="badge badge-category text-white small mb-2"><?= htmlspecialchars($p['cat_name']) ?></span>
                            <h6 class="card-title flex-grow-1"><?= htmlspecialchars($p['name']) ?></h6>
                            <p class="price-tag mb-2"><?= formatPrice($p['sell_price']) ?></p>
                            <?php if ($p['stock_quantity'] <= 5): ?>
                            <small class="text-danger mb-2"><i class="bi bi-exclamation-circle"></i> Còn <?= $p['stock_quantity'] ?> sản phẩm</small>
                            <?php endif; ?>
                            <a href="product.php?id=<?= $p['id'] ?>" class="btn btn-primary btn-sm">
                                <i class="bi bi-eye me-1"></i>Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= $cat_id ? 'id='.$cat_id.'&' : '' ?>page=<?= $i ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
