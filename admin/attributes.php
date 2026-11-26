<?php
// admin/attributes.php
require_once '_layout.php';
adminHeader('Quản lý thuộc tính (Size & Màu)');

$msg = '';

// Helper for safe query execution returning single count
function getSafeCount($conn, $sql) {
    try {
        $res = $conn->query($sql);
        if ($res && $row = $res->fetch_assoc()) {
            return (int)($row['c'] ?? 0);
        }
    } catch (Throwable $e) {}
    return 0;
}

// Add Size
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_size') {
    $size_val = (int)($_POST['size'] ?? 0);
    if ($size_val <= 0 || $size_val > 60) {
        $msg = '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>Giá trị Size không hợp lệ.</div>';
    } else {
        $check = $conn->query("SELECT id FROM sizes WHERE size=$size_val");
        $exists = ($check && $check->num_rows > 0);
        if ($exists) {
            $msg = '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>Kích thước Size <strong>' . $size_val . '</strong> đã tồn tại.</div>';
        } else {
            $conn->query("INSERT INTO sizes (size) VALUES ($size_val)");
            $msg = '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>Đã thêm Kích thước Size <strong>' . $size_val . '</strong> mới thành công.</div>';
        }
    }
}

// Add Color
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_color') {
    $color_name = sanitize($conn, $_POST['color_name'] ?? '');
    if (empty($color_name)) {
        $msg = '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>Vui lòng nhập tên màu sắc.</div>';
    } else {
        $check = $conn->query("SELECT id FROM colors WHERE name='$color_name'");
        $exists = ($check && $check->num_rows > 0);
        if ($exists) {
            $msg = '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>Màu sắc <strong>' . htmlspecialchars($color_name) . '</strong> đã tồn tại.</div>';
        } else {
            $conn->query("INSERT INTO colors (name) VALUES ('$color_name')");
            $msg = '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>Đã thêm Màu sắc <strong>' . htmlspecialchars($color_name) . '</strong> mới thành công.</div>';
        }
    }
}

// Delete Size
if (isset($_GET['delete_size'])) {
    $sid = (int)$_GET['delete_size'];
    $pv_count = getSafeCount($conn, "SELECT COUNT(*) as c FROM product_varieties WHERE size_id=$sid");
    $od_count = getSafeCount($conn, "SELECT COUNT(*) as c FROM order_details WHERE size_id=$sid");
    $id_count = 0;
    if (hasTableColumn($conn, 'import_details', 'size_id')) {
        $id_count = getSafeCount($conn, "SELECT COUNT(*) as c FROM import_details WHERE size_id=$sid");
    }

    $total_used = $pv_count + $od_count + $id_count;
    if ($total_used > 0) {
        $details = [];
        if ($pv_count > 0) $details[] = "$pv_count biến thể sản phẩm";
        if ($od_count > 0) $details[] = "$od_count chi tiết đơn hàng";
        if ($id_count > 0) $details[] = "$id_count phiếu nhập hàng";
        $detail_str = implode(', ', $details);
        $msg = '<div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>Size này đã được sử dụng trong hệ thống (' . $detail_str . '), không thể xóa.</div>';
    } else {
        try {
            $conn->query("DELETE FROM sizes WHERE id=$sid");
            $msg = '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>Đã xóa Kích thước Size.</div>';
        } catch (Throwable $e) {
            $msg = '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>Không thể xóa Size do vướng ràng buộc dữ liệu: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}

// Delete Color
if (isset($_GET['delete_color'])) {
    $cid = (int)$_GET['delete_color'];
    $pv_count = getSafeCount($conn, "SELECT COUNT(*) as c FROM product_varieties WHERE color_id=$cid");
    $od_count = getSafeCount($conn, "SELECT COUNT(*) as c FROM order_details WHERE color_id=$cid");
    $id_count = 0;
    if (hasTableColumn($conn, 'import_details', 'color_id')) {
        $id_count = getSafeCount($conn, "SELECT COUNT(*) as c FROM import_details WHERE color_id=$cid");
    }

    $total_used = $pv_count + $od_count + $id_count;
    if ($total_used > 0) {
        $details = [];
        if ($pv_count > 0) $details[] = "$pv_count biến thể sản phẩm";
        if ($od_count > 0) $details[] = "$od_count chi tiết đơn hàng";
        if ($id_count > 0) $details[] = "$id_count phiếu nhập hàng";
        $detail_str = implode(', ', $details);
        $msg = '<div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>Màu sắc này đã được sử dụng trong hệ thống (' . $detail_str . '), không thể xóa.</div>';
    } else {
        try {
            $conn->query("DELETE FROM colors WHERE id=$cid");
            $msg = '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>Đã xóa Màu sắc.</div>';
        } catch (Throwable $e) {
            $msg = '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>Không thể xóa màu sắc do vướng ràng buộc dữ liệu: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}

// Dynamically check columns before building list queries
$has_import_size = hasTableColumn($conn, 'import_details', 'size_id');
$has_import_color = hasTableColumn($conn, 'import_details', 'color_id');

$import_size_sql = $has_import_size ? "(SELECT COUNT(*) FROM import_details id_tab WHERE id_tab.size_id=s.id)" : "0";
$import_color_sql = $has_import_color ? "(SELECT COUNT(*) FROM import_details id_tab WHERE id_tab.color_id=c.id)" : "0";

// Fetch lists with usage count across product varieties, orders, and imports
$sizes_list = $conn->query("SELECT s.*, 
    (SELECT COUNT(DISTINCT product_id) FROM product_varieties pv WHERE pv.size_id=s.id) as prod_count,
    (SELECT COUNT(*) FROM order_details od WHERE od.size_id=s.id) as order_count,
    $import_size_sql as import_count
FROM sizes s ORDER BY s.size ASC");

$colors_list = $conn->query("SELECT c.*, 
    (SELECT COUNT(DISTINCT product_id) FROM product_varieties pv WHERE pv.color_id=c.id) as prod_count,
    (SELECT COUNT(*) FROM order_details od WHERE od.color_id=c.id) as order_count,
    $import_color_sql as import_count
FROM colors c ORDER BY c.name ASC");

?>

<?= $msg ?>

<div class="row g-4">
    <!-- Managed Sizes -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 fw-bold">
                <i class="bi bi-ruler me-2 text-primary"></i>Quản lý Kích thước Giày (Size)
            </div>
            <div class="card-body">
                <form method="POST" class="row g-2 mb-4">
                    <input type="hidden" name="action" value="add_size">
                    <div class="col-8 col-sm-9">
                        <input type="number" name="size" class="form-control" placeholder="Nhập số Size mới (VD: 46)" min="20" max="60" required>
                    </div>
                    <div class="col-4 col-sm-3">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus me-1"></i>Thêm</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width:70px">ID</th>
                                <th>Kích thước (Size)</th>
                                <th class="text-center">Tình trạng sử dụng</th>
                                <th class="text-center" style="width:100px">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($sizes_list && $sizes_list->num_rows > 0): ?>
                                <?php while ($s = $sizes_list->fetch_assoc()): ?>
                                    <tr>
                                        <td class="text-center text-muted small"><?= $s['id'] ?></td>
                                        <td class="fw-bold fs-6">Size <?= $s['size'] ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-<?= $s['prod_count'] > 0 ? 'info' : 'secondary' ?>" title="Số sản phẩm đang sử dụng biến thể size này"><?= $s['prod_count'] ?> SP</span>
                                            <?php if (!empty($s['order_count']) && $s['order_count'] > 0): ?>
                                                <span class="badge bg-warning text-dark ms-1" title="Size đã xuất hiện trong <?= $s['order_count'] ?> chi tiết đơn hàng"><i class="bi bi-receipt me-1"></i><?= $s['order_count'] ?> đơn</span>
                                            <?php endif; ?>
                                            <?php if (!empty($s['import_count']) && $s['import_count'] > 0): ?>
                                                <span class="badge bg-secondary ms-1" title="Size đã xuất hiện trong <?= $s['import_count'] ?> phiếu nhập"><i class="bi bi-box-arrow-in-right me-1"></i><?= $s['import_count'] ?> nhập</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="attributes.php?delete_size=<?= $s['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Xóa Size này?')" title="Xóa Size"><i class="bi bi-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center text-muted py-3">Chưa có Kích thước Size nào.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Managed Colors -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 fw-bold">
                <i class="bi bi-palette me-2 text-danger"></i>Quản lý Màu sắc Sản phẩm
            </div>
            <div class="card-body">
                <form method="POST" class="row g-2 mb-4">
                    <input type="hidden" name="action" value="add_color">
                    <div class="col-8 col-sm-9">
                        <input type="text" name="color_name" class="form-control" placeholder="Nhập tên màu mới (VD: Tím Pastel)" required>
                    </div>
                    <div class="col-4 col-sm-3">
                        <button type="submit" class="btn btn-danger w-100"><i class="bi bi-plus me-1"></i>Thêm</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width:70px">ID</th>
                                <th>Tên màu sắc</th>
                                <th class="text-center">Tình trạng sử dụng</th>
                                <th class="text-center" style="width:100px">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($colors_list && $colors_list->num_rows > 0): ?>
                                <?php while ($c = $colors_list->fetch_assoc()): ?>
                                    <tr>
                                        <td class="text-center text-muted small"><?= $c['id'] ?></td>
                                        <td class="fw-semibold"><i class="bi bi-circle-fill me-2 text-secondary" style="font-size:.8rem"></i><?= htmlspecialchars($c['name']) ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-<?= $c['prod_count'] > 0 ? 'info' : 'secondary' ?>" title="Số sản phẩm đang sử dụng biến thể màu này"><?= $c['prod_count'] ?> SP</span>
                                            <?php if (!empty($c['order_count']) && $c['order_count'] > 0): ?>
                                                <span class="badge bg-warning text-dark ms-1" title="Màu sắc đã xuất hiện trong <?= $c['order_count'] ?> chi tiết đơn hàng"><i class="bi bi-receipt me-1"></i><?= $c['order_count'] ?> đơn</span>
                                            <?php endif; ?>
                                            <?php if (!empty($c['import_count']) && $c['import_count'] > 0): ?>
                                                <span class="badge bg-secondary ms-1" title="Màu sắc đã xuất hiện trong <?= $c['import_count'] ?> phiếu nhập"><i class="bi bi-box-arrow-in-right me-1"></i><?= $c['import_count'] ?> nhập</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="attributes.php?delete_color=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Xóa màu sắc này?')" title="Xóa Màu"><i class="bi bi-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center text-muted py-3">Chưa có Màu sắc nào.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php adminFooter(); ?>
