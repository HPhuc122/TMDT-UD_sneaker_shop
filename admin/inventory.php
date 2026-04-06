<?php
// admin/inventory.php
require_once '_layout.php';
adminHeader('Tồn kho & Báo cáo');

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'stock';
$per_page = 15;

// ── Low stock threshold ──────────────────────────────────────────────────────
$low_threshold = 5;
if (isset($_GET['threshold']) && is_numeric($_GET['threshold'])) {
    $low_threshold = max(1, (int)$_GET['threshold']);
}

// ── Stock tab params ─────────────────────────────────────────────────────────
$stock_date = isset($_GET['stock_date']) ? sanitize($conn, $_GET['stock_date']) : date('Y-m-d');
$stock_cat  = isset($_GET['stock_cat']) ? (int)$_GET['stock_cat'] : 0;
$page_stock = max(1, (int)($_GET['page'] ?? 1));
$where_cat  = $stock_cat ? "AND p.category_id=$stock_cat" : '';

$stock_count_sql = "SELECT COUNT(*) as c FROM products p
    JOIN categories c ON p.category_id=c.id
    WHERE p.status='active' $where_cat";
$total_stock = $conn->query($stock_count_sql)->fetch_assoc()['c'];
$offset_stock = ($page_stock - 1) * $per_page;

$stock_sql = "SELECT p.id, p.code, p.name, c.name as cat_name, pv.color_id, pv.size_id, pv.stock_quantity, pv.import_price,
    ROUND(pv.import_price*(1+p.profit_rate/100)) as sell_price,
    COALESCE((
        SELECT SUM(id2.quantity) 
        FROM import_details id2
        JOIN import_receipts ir2 ON id2.receipt_id=ir2.id
        WHERE id2.product_id = p.id 
          AND id2.color_id = pv.color_id
          AND id2.size_id = pv.size_id
          AND ir2.status='completed'
    ), 0) as total_imported_all,
    COALESCE((
        SELECT SUM(id2.quantity) 
        FROM import_details id2
        JOIN import_receipts ir2 ON id2.receipt_id=ir2.id
        WHERE id2.product_id = p.id 
          AND id2.color_id = pv.color_id
          AND id2.size_id = pv.size_id
          AND ir2.status='completed'
          AND ir2.import_date > '$stock_date'
    ), 0) as imported_after
FROM products p 
JOIN categories c ON p.category_id=c.id
JOIN product_varieties pv ON pv.product_id = p.id
WHERE p.status='active' $where_cat
ORDER BY p.name
LIMIT $per_page OFFSET $offset_stock";
$stocks = $conn->query($stock_sql);

// ── Order stats tab params ────────────────────────────────────────────────────
$ord_from = isset($_GET['ord_from']) ? sanitize($conn, $_GET['ord_from']) : date('Y-m-01');
$ord_to   = isset($_GET['ord_to'])   ? sanitize($conn, $_GET['ord_to'])   : date('Y-m-d');

$report = $conn->query("SELECT 
    p.code, p.name, c.name as cat_name,
    pv.color_id,
    pv.size_id,

    COALESCE((
        SELECT SUM(id2.quantity) 
        FROM import_details id2 
        JOIN import_receipts ir2 ON id2.receipt_id=ir2.id
        WHERE id2.product_id=p.id 
          AND id2.color_id = pv.color_id
          AND id2.size_id = pv.size_id
          AND ir2.status='completed'
          AND ir2.import_date BETWEEN '$rep_from' AND '$rep_to'
    ), 0) as qty_imported,

    COALESCE((
        SELECT SUM(od.quantity) 
        FROM order_details od 
        JOIN orders o ON od.order_id=o.id
        WHERE od.product_id=p.id 
          AND od.color_id = pv.color_id
          AND od.size_id = pv.size_id
          AND o.status IN ('pending','confirmed','delivered')
          AND DATE(o.created_at) BETWEEN '$rep_from' AND '$rep_to'
    ), 0) as qty_sold,

    pv.stock_quantity,

    COALESCE((
        SELECT SUM(od2.quantity) 
        FROM order_details od2 
        JOIN orders o2 ON od2.order_id=o2.id
        WHERE od2.product_id=p.id 
          AND od2.color_id = pv.color_id
          AND od2.size_id = pv.size_id
          AND o2.status IN ('pending','confirmed','delivered')
    ), 0) as total_sold_ever

FROM products p 
JOIN categories c ON p.category_id=c.id
JOIN product_varieties pv ON pv.product_id = p.id

WHERE p.status='active'
ORDER BY qty_sold DESC, p.name
LIMIT $per_page OFFSET $offset_rep;");
$ord_stats = $conn->query("SELECT
    COUNT(*) as tong_don,
    SUM(CASE WHEN status IN ('confirmed','delivered') THEN 1 ELSE 0 END) as thanh_cong,
    SUM(CASE WHEN status = 'pending' AND payment_method != 'online' THEN 1 ELSE 0 END) as cho_xu_ly_cod,
    SUM(CASE WHEN status IN ('awaiting_payment','pending_payment') THEN 1 ELSE 0 END) as cho_thanh_toan,
    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as da_huy
    FROM orders
    WHERE DATE(created_at) BETWEEN '$ord_from' AND '$ord_to'")->fetch_assoc();

$ord_list = $conn->query("SELECT o.order_code, o.status, o.payment_method, o.total_amount, o.created_at, u.full_name
    FROM orders o JOIN users u ON o.user_id=u.id
    WHERE DATE(o.created_at) BETWEEN '$ord_from' AND '$ord_to'
    ORDER BY o.created_at DESC");

// ── Revenue tab params ────────────────────────────────────────────────────────
$rev_from = isset($_GET['rev_from']) ? sanitize($conn, $_GET['rev_from']) : date('Y-m-01');
$rev_to   = isset($_GET['rev_to'])   ? sanitize($conn, $_GET['rev_to'])   : date('Y-m-d');

$rev_stats = $conn->query("SELECT
    SUM(total_amount) as tong_gia_tri,
    SUM(CASE WHEN status IN ('confirmed','delivered') THEN total_amount ELSE 0 END) as da_thanh_toan,
    SUM(CASE WHEN status = 'pending' AND payment_method != 'online' THEN total_amount ELSE 0 END) as cod_cho_xu_ly,
    SUM(CASE WHEN status IN ('awaiting_payment','pending_payment') THEN total_amount ELSE 0 END) as cho_thanh_toan,
    SUM(CASE WHEN status = 'cancelled' THEN total_amount ELSE 0 END) as da_huy
    FROM orders
    WHERE DATE(created_at) BETWEEN '$rev_from' AND '$rev_to'")->fetch_assoc();

// ── Alert tab params ──────────────────────────────────────────────────────────
$page_alert  = max(1, (int)($_GET['page'] ?? 1));
$total_alert = $conn->query("SELECT COUNT(*) as c 
                            FROM products p JOIN categories c ON p.category_id=c.id
                            WHERE p.status='active' AND (SELECT SUM(pv.stock_quantity) 
                                                        FROM product_varieties pv 
                                                        WHERE pv.product_id = p.id) <= $low_threshold")->fetch_assoc()['c'];
$offset_alert = ($page_alert - 1) * $per_page;
$low_products = $conn->query("SELECT p.*, c.name as cat_name, 
                            COALESCE(SUM(pv.stock_quantity),0) as stock_quantity
                            FROM products p
                            JOIN categories c ON p.category_id=c.id
                            LEFT JOIN product_varieties pv ON pv.product_id = p.id
                            WHERE p.status='active'
                            GROUP BY p.id
                            HAVING stock_quantity <= $low_threshold
                            ORDER BY stock_quantity ASC, p.name ASC
                            LIMIT $per_page OFFSET $offset_alert");

// ── Price tab params ──────────────────────────────────────────────────────────
$page_price  = max(1, (int)($_GET['page'] ?? 1));
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_price'])) {
    $pid  = (int)$_POST['product_id'];
    $rate = (float)$_POST['profit_rate'];
    $conn->query("UPDATE products SET profit_rate=$rate WHERE id=$pid");
    echo '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>Đã cập nhật tỉ lệ lợi nhuận.</div>';
}
$total_price  = $conn->query("SELECT COUNT(*) as c FROM products p WHERE p.status='active'")->fetch_assoc()['c'];
$offset_price = ($page_price - 1) * $per_page;
$prices = $conn->query("SELECT p.*, c.name as cat_name, ROUND(p.import_price*(1+p.profit_rate/100)) as sell_price
    FROM products p JOIN categories c ON p.category_id=c.id
    WHERE p.status='active' ORDER BY p.name
    LIMIT $per_page OFFSET $offset_price");

$categories = $conn->query("SELECT * FROM categories ORDER BY name");
?>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'stock' ? 'active' : '' ?>" href="?tab=stock&stock_date=<?= $stock_date ?>&stock_cat=<?= $stock_cat ?>">
            <i class="bi bi-box me-1"></i>Tra cứu tồn kho
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'orders' ? 'active' : '' ?>" href="?tab=orders&ord_from=<?= $ord_from ?>&ord_to=<?= $ord_to ?>">
            <i class="bi bi-bar-chart-line me-1"></i>Thống kê đơn hàng
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'revenue' ? 'active' : '' ?>" href="?tab=revenue&rev_from=<?= $rev_from ?>&rev_to=<?= $rev_to ?>">
            <i class="bi bi-cash-coin me-1"></i>Doanh thu
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'alert' ? 'active' : '' ?>" href="?tab=alert&threshold=<?= $low_threshold ?>">
            <i class="bi bi-exclamation-triangle me-1"></i>Cảnh báo hết hàng
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'price' ? 'active' : '' ?>" href="?tab=price">
            <i class="bi bi-tags me-1"></i>Quản lý giá bán
        </a>
    </li>
</ul>

<?php // ══════════════════════════════════════════════════════════════════════
// TAB 1: TRA CỨU TỒN KHO
// ══════════════════════════════════════════════════════════════════════════
if ($tab === 'stock'): ?>
    <form method="GET" class="row g-3 mb-4">
        <input type="hidden" name="tab" value="stock">
        <div class="col-md-3">
            <label class="form-label">Xem tồn kho tại ngày</label>
            <input type="date" name="stock_date" class="form-control" value="<?= $stock_date ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Danh mục</label>
            <select name="stock_cat" class="form-select">
                <option value="">Tất cả danh mục</option>
                <?php while ($c = $categories->fetch_assoc()): ?>
                    <option value="<?= $c['id'] ?>" <?= $stock_cat == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Tra cứu</button>
        </div>
    </form>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 fw-bold">
            Tồn kho tại ngày <?= date('d/m/Y', strtotime($stock_date)) ?>
            <span class="badge bg-secondary ms-2"><?= $total_stock ?> sản phẩm</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Mã</th>
                        <th>Sản phẩm</th>
                        <th>Danh mục</th>
                        <th class="text-center">Tổng nhập</th>
                        <th class="text-center">Tổng bán</th>
                        <th class="text-center">Tồn kho</th>
                        <th class="text-end">Giá vốn</th>
                        <th class="text-end">Giá bán</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($s = $stocks->fetch_assoc()):
                        $ton_at_date  = $s['stock_quantity'] - $s['imported_after'] + $s['sold_after'];
                        $ton_at_date  = max(0, $ton_at_date);
                        $imp_to_date  = $s['total_imported_all'] - $s['imported_after'];
                        $sold_to_date = $s['total_sold_all'] - $s['sold_after'];
                        if ($s['total_imported_all'] == 0) {
                            $imp_to_date = $s['stock_quantity'] + $s['total_sold_all'];
                        }
                    ?>
                        <tr>
                            <td class="small text-muted"><?= htmlspecialchars($s['code']) ?></td>
                            <td><?= htmlspecialchars($s['name']) ?></td>
                            <td class="small"><?= htmlspecialchars($s['cat_name']) ?></td>
                            <td class="text-center"><?= $imp_to_date ?></td>
                            <td class="text-center"><?= $sold_to_date ?></td>
                            <td class="text-center">
                                <span class="badge bg-<?= $ton_at_date <= 0 ? 'danger' : ($ton_at_date <= 5 ? 'warning' : 'success') ?>"><?= $ton_at_date ?></span>
                            </td>
                            <td class="text-end small"><?= formatPrice($s['import_price']) ?></td>
                            <td class="text-end small fw-bold" style="color:#e74c3c"><?= formatPrice($s['sell_price']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php if ($total_stock > $per_page): ?>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">Hiển thị <?= min($offset_stock + 1, $total_stock) ?>–<?= min($offset_stock + $per_page, $total_stock) ?> / <?= $total_stock ?></small>
                    <?= renderPagination($total_stock, $page_stock, $per_page, ['tab' => 'stock', 'stock_date' => $stock_date, 'stock_cat' => $stock_cat]) ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

<?php // ══════════════════════════════════════════════════════════════════════
// TAB 2: THỐNG KÊ ĐƠN HÀNG
// ══════════════════════════════════════════════════════════════════════════
elseif ($tab === 'orders'): ?>
    <form method="GET" class="row g-3 mb-4">
        <input type="hidden" name="tab" value="orders">
        <div class="col-md-3">
            <label class="form-label">Từ ngày</label>
            <input type="date" name="ord_from" class="form-control" value="<?= $ord_from ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Đến ngày</label>
            <input type="date" name="ord_to" class="form-control" value="<?= $ord_to ?>">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Xem thống kê</button>
        </div>
    </form>

    <!-- Summary cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body py-3">
                    <div class="fs-3 fw-bold text-dark"><?= $ord_stats['tong_don'] ?></div>
                    <div class="small text-muted">Tổng đơn</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm text-center h-100" style="border-left:4px solid #28a745!important">
                <div class="card-body py-3">
                    <div class="fs-3 fw-bold text-success"><?= $ord_stats['thanh_cong'] ?></div>
                    <div class="small text-muted">Thành công</div>
                    <div class="xsmall text-muted" style="font-size:.7rem">(Đã xác nhận + Đã giao)</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm text-center h-100" style="border-left:4px solid #ffc107!important">
                <div class="card-body py-3">
                    <div class="fs-3 fw-bold text-warning"><?= $ord_stats['cho_xu_ly_cod'] ?></div>
                    <div class="small text-muted">Chờ xử lý</div>
                    <div class="xsmall text-muted" style="font-size:.7rem">(COD đang xử lý)</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm text-center h-100" style="border-left:4px solid #6c757d!important">
                <div class="card-body py-3">
                    <div class="fs-3 fw-bold text-secondary"><?= $ord_stats['cho_thanh_toan'] ?></div>
                    <div class="small text-muted">Chờ thanh toán</div>
                    <div class="xsmall text-muted" style="font-size:.7rem">(Online chưa TT)</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm text-center h-100" style="border-left:4px solid #dc3545!important">
                <div class="card-body py-3">
                    <div class="fs-3 fw-bold text-danger"><?= $ord_stats['da_huy'] ?></div>
                    <div class="small text-muted">Đã hủy</div>
                </div>
            </div>
        </div>

    </div>

    <!-- Detail table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 fw-bold">
            <i class="bi bi-list-ul me-2"></i>Chi tiết đơn hàng từ <?= date('d/m/Y', strtotime($ord_from)) ?> đến <?= date('d/m/Y', strtotime($ord_to)) ?>
        </div>
        <?php
        $statusColor = ['awaiting_payment' => 'secondary', 'pending_payment' => 'secondary', 'pending' => 'warning', 'confirmed' => 'info', 'delivered' => 'success', 'cancelled' => 'danger'];
        $statusLabel = ['awaiting_payment' => 'Chờ thanh toán', 'pending_payment' => 'Chờ thanh toán', 'pending' => 'Chờ xử lý', 'confirmed' => 'Đã xác nhận', 'delivered' => 'Đã giao', 'cancelled' => 'Đã hủy'];
        $pmLabel = ['cash' => 'COD', 'transfer' => 'Chuyển khoản', 'online' => 'Online'];
        ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Thanh toán</th>
                        <th class="text-end">Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Ngày đặt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($ord_list->num_rows === 0): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Không có đơn hàng nào trong khoảng thời gian này.</td>
                        </tr>
                    <?php endif; ?>
                    <?php while ($o = $ord_list->fetch_assoc()): ?>
                        <tr>
                            <td><a href="orders.php?id=<?= htmlspecialchars($o['order_code']) ?>" class="fw-semibold text-decoration-none"><?= htmlspecialchars($o['order_code']) ?></a></td>
                            <td><?= htmlspecialchars($o['full_name']) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= $pmLabel[$o['payment_method']] ?? $o['payment_method'] ?></span></td>
                            <td class="text-end fw-bold" style="color:#e74c3c"><?= formatPrice($o['total_amount']) ?></td>
                            <td><span class="badge bg-<?= $statusColor[$o['status']] ?? 'dark' ?>"><?= $statusLabel[$o['status']] ?? $o['status'] ?></span></td>
                            <td class="text-muted small"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php // ══════════════════════════════════════════════════════════════════════
// TAB 3 (MỚI): DOANH THU
// ══════════════════════════════════════════════════════════════════════════
elseif ($tab === 'revenue'): ?>
    <form method="GET" class="row g-3 mb-4">
        <input type="hidden" name="tab" value="revenue">
        <div class="col-md-3">
            <label class="form-label">Từ ngày</label>
            <input type="date" name="rev_from" class="form-control" value="<?= $rev_from ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Đến ngày</label>
            <input type="date" name="rev_to" class="form-control" value="<?= $rev_to ?>">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Xem doanh thu</button>
        </div>
    </form>

    <!-- Revenue summary cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #6c757d!important">
                <div class="card-body">
                    <div class="small text-muted mb-1">Tổng giá trị tất cả đơn</div>
                    <div class="fs-5 fw-bold text-dark"><?= formatPrice($rev_stats['tong_gia_tri'] ?? 0) ?></div>
                    <div class="small text-muted mt-1">(Kỳ <?= date('d/m', strtotime($rev_from)) ?> – <?= date('d/m/Y', strtotime($rev_to)) ?>)</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #28a745!important">
                <div class="card-body">
                    <div class="small text-muted mb-1"><i class="bi bi-check-circle-fill text-success me-1"></i>Đã thanh toán thành công</div>
                    <div class="fs-5 fw-bold text-success"><?= formatPrice($rev_stats['da_thanh_toan'] ?? 0) ?></div>
                    <div class="small text-muted mt-1">Đơn đã xác nhận + đã giao</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #ffc107!important">
                <div class="card-body">
                    <div class="small text-muted mb-1"><i class="bi bi-clock-history text-warning me-1"></i>Đang chờ thanh toán / xử lý</div>
                    <div class="fs-5 fw-bold text-warning"><?= formatPrice(($rev_stats['cod_cho_xu_ly'] ?? 0) + ($rev_stats['cho_thanh_toan'] ?? 0)) ?></div>
                    <div class="small text-muted mt-1">
                        COD chờ xử lý: <?= formatPrice($rev_stats['cod_cho_xu_ly'] ?? 0) ?><br>
                        Online chờ TT: <?= formatPrice($rev_stats['cho_thanh_toan'] ?? 0) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #dc3545!important">
                <div class="card-body">
                    <div class="small text-muted mb-1"><i class="bi bi-x-circle-fill text-danger me-1"></i>Đơn đã hủy</div>
                    <div class="fs-5 fw-bold text-danger"><?= formatPrice($rev_stats['da_huy'] ?? 0) ?></div>
                    <div class="small text-muted mt-1">Tổng giá trị đơn bị hủy</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue breakdown table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 fw-bold">
            <i class="bi bi-table me-2"></i>Bảng tóm tắt doanh thu từ <?= date('d/m/Y', strtotime($rev_from)) ?> đến <?= date('d/m/Y', strtotime($rev_to)) ?>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Loại</th>
                        <th>Mô tả</th>
                        <th class="text-end">Giá trị</th>
                        <th class="text-end">Tỉ lệ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $tong = max(1, $rev_stats['tong_gia_tri'] ?? 1);
                    $rows = [
                        ['success', 'Đã thanh toán thành công', 'Đơn đã xác nhận + đã giao', $rev_stats['da_thanh_toan'] ?? 0],
                        ['warning', 'COD chờ xử lý', 'Đơn COD đang ở trạng thái chờ xử lý', $rev_stats['cod_cho_xu_ly'] ?? 0],
                        ['secondary', 'Online chờ thanh toán', 'Đơn online chưa thanh toán', $rev_stats['cho_thanh_toan'] ?? 0],
                        ['danger', 'Đã hủy', 'Tổng giá trị đơn bị hủy', $rev_stats['da_huy'] ?? 0],
                    ];
                    foreach ($rows as [$color, $label, $desc, $val]):
                    ?>
                        <tr>
                            <td><span class="badge bg-<?= $color ?>"><?= $label ?></span></td>
                            <td class="text-muted small"><?= $desc ?></td>
                            <td class="text-end fw-bold"><?= formatPrice($val) ?></td>
                            <td class="text-end text-muted"><?= $tong > 0 ? number_format($val / $tong * 100, 1) : 0 ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-secondary">
                    <tr>
                        <td colspan="2" class="fw-bold">Tổng cộng</td>
                        <td class="text-end fw-bold"><?= formatPrice($rev_stats['tong_gia_tri'] ?? 0) ?></td>
                        <td class="text-end fw-bold">100%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

<?php // ══════════════════════════════════════════════════════════════════════
// TAB 3: CẢNH BÁO HẾT HÀNG
// ══════════════════════════════════════════════════════════════════════════
elseif ($tab === 'alert'): ?>
    <form method="GET" class="row g-3 mb-4">
        <input type="hidden" name="tab" value="alert">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Ngưỡng cảnh báo (số lượng tồn kho)</label>
            <div class="input-group">
                <input type="number" name="threshold" class="form-control" value="<?= $low_threshold ?>" min="1" max="9999">
                <button class="btn btn-warning fw-semibold"><i class="bi bi-funnel me-1"></i>Áp dụng</button>
            </div>
            <div class="form-text">Hiển thị sản phẩm có tồn kho ≤ ngưỡng này.</div>
        </div>
    </form>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-warning text-dark border-0 fw-bold">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>Sản phẩm sắp hết hàng (≤ <?= $low_threshold ?>)
            <span class="badge bg-dark ms-2"><?= $total_alert ?></span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Mã</th>
                        <th>Sản phẩm</th>
                        <th>Danh mục</th>
                        <th class="text-center">Tồn kho</th>
                        <th class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($low_products->num_rows === 0): ?>
                        <tr>
                            <td colspan="5" class="text-center text-success py-4">
                                <i class="bi bi-check-circle me-2"></i>Không có sản phẩm nào sắp hết hàng.
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php while ($p = $low_products->fetch_assoc()): ?>
                        <tr>
                            <td class="small text-muted"><?= htmlspecialchars($p['code']) ?></td>
                            <td><?= htmlspecialchars($p['name']) ?></td>
                            <td><?= htmlspecialchars($p['cat_name']) ?></td>
                            <td class="text-center">
                                <span class="badge bg-<?= $p['stock_quantity'] == 0 ? 'danger' : 'warning' ?>"><?= $p['stock_quantity'] ?></span>
                            </td>
                            <td class="text-center">
                                <a href="imports.php" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-truck me-1"></i>Nhập hàng
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php if ($total_alert > $per_page): ?>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">Hiển thị <?= min($offset_alert + 1, $total_alert) ?>–<?= min($offset_alert + $per_page, $total_alert) ?> / <?= $total_alert ?></small>
                    <?= renderPagination($total_alert, $page_alert, $per_page, ['tab' => 'alert', 'threshold' => $low_threshold]) ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

<?php // ══════════════════════════════════════════════════════════════════════
// TAB 4: QUẢN LÝ GIÁ BÁN
// ══════════════════════════════════════════════════════════════════════════
elseif ($tab === 'price'): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 fw-bold">
            <i class="bi bi-tags me-2"></i>Quản lý giá bán theo sản phẩm
            <span class="badge bg-secondary ms-2"><?= $total_price ?> sản phẩm</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Sản phẩm</th>
                        <th class="text-end">Giá vốn (bình quân)</th>
                        <th class="text-center" style="width:200px">% Lợi nhuận</th>
                        <th class="text-end">Giá bán</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($p = $prices->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($p['name']) ?>
                                <br><small class="text-muted"><?= htmlspecialchars($p['cat_name']) ?></small>
                            </td>
                            <td class="text-end"><?= formatPrice($p['import_price']) ?></td>
                            <td class="text-center">
                                <form method="POST" class="d-flex align-items-center gap-2 justify-content-center">
                                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                    <input type="hidden" name="tab" value="price">
                                    <input type="hidden" name="page" value="<?= $page_price ?>">
                                    <div class="input-group input-group-sm" style="width:110px">
                                        <input type="number" name="profit_rate" class="form-control" value="<?= $p['profit_rate'] ?>" step="0.01" min="0">
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <button type="submit" name="update_price" class="btn btn-sm btn-primary">Lưu</button>
                                </form>
                            </td>
                            <td class="text-end fw-bold" style="color:#e74c3c"><?= formatPrice($p['sell_price']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php if ($total_price > $per_page): ?>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">Hiển thị <?= min($offset_price + 1, $total_price) ?>–<?= min($offset_price + $per_page, $total_price) ?> / <?= $total_price ?></small>
                    <?= renderPagination($total_price, $page_price, $per_page, ['tab' => 'price']) ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php adminFooter(); ?>