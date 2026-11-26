<?php
// admin/categories.php
require_once '_layout.php';
adminHeader('Quản lý danh mục');

$msg = '';
// Validate both creation and editing before writing.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $name = is_string($_POST['name'] ?? null) ? trim($_POST['name']) : '';
    $desc = is_string($_POST['description'] ?? null) ? trim($_POST['description']) : '';
    $id = (int)($_POST['id'] ?? 0);
    $error = '';
    if (!in_array($action, ['add', 'edit'], true)) {
        $error = 'Thao tác không hợp lệ.';
    } elseif ($name === '' || preg_match('/^\s*$/u', $name)) {
        $error = 'Vui lòng nhập tên danh mục.';
    } elseif (mb_strlen($name, 'UTF-8') > 100) {
        $error = 'Tên danh mục không được vượt quá 100 ký tự.';
    } else {
        try {
            if ($action === 'edit') {
                $existing = $conn->prepare('SELECT id FROM categories WHERE id=?');
                $existing->bind_param('i', $id);
                $existing->execute();
                if (!$existing->get_result()->fetch_assoc()) $error = 'Không tìm thấy danh mục.';
            }
            $excludeId = $action === 'edit' ? $id : 0;
            $check = $conn->prepare('SELECT id FROM categories WHERE name=? AND id<>? LIMIT 1');
            $check->bind_param('si', $name, $excludeId);
            $check->execute();
            if ($check->get_result()->fetch_assoc()) $error = 'Tên danh mục đã tồn tại.';
            if ($error === '') {
                if ($action === 'add') {
                    $save = $conn->prepare('INSERT INTO categories (name, description) VALUES (?,?)');
                    $save->bind_param('ss', $name, $desc);
                } else {
                    $save = $conn->prepare('UPDATE categories SET name=?, description=? WHERE id=?');
                    $save->bind_param('ssi', $name, $desc, $id);
                }
                if ($save->execute()) {
                    $msg = '<div class="alert alert-success">' . ($action === 'add' ? 'Đã thêm danh mục thành công.' : 'Đã cập nhật danh mục.') . '</div>';
                } else {
                    $error = 'Không thể lưu danh mục. Vui lòng thử lại.';
                }
            }
        } catch (mysqli_sql_exception $e) {
            error_log($e->getMessage());
            $error = 'Không thể lưu danh mục. Vui lòng thử lại.';
        }
    }
    if ($error !== '') $msg = '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>';
}
// Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $check = $conn->query("SELECT COUNT(*) as c FROM products WHERE category_id=$id")->fetch_assoc()['c'];
    if ($check > 0) {
        $msg = '<div class="alert alert-warning">Không thể xóa danh mục đang có sản phẩm.</div>';
    } else {
        $conn->query("DELETE FROM categories WHERE id=$id");
        $msg = '<div class="alert alert-success">Đã xóa danh mục.</div>';
    }
}

$edit_cat = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $edit_cat = $conn->query("SELECT * FROM categories WHERE id=$id")->fetch_assoc();
}

$categories = $conn->query("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id=p.category_id GROUP BY c.id ORDER BY c.name");
?>

<?= $msg ?>

<div class="row g-4">
    <!-- Form -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header fw-bold bg-white border-0">
                <?= $edit_cat ? '<i class="bi bi-pencil me-2"></i>Sửa danh mục' : '<i class="bi bi-plus-circle me-2"></i>Thêm danh mục' ?>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="<?= $edit_cat ? 'edit' : 'add' ?>">
                    <?php if ($edit_cat): ?>
                        <input type="hidden" name="id" value="<?= $edit_cat['id'] ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tên danh mục <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($edit_cat['name'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mô tả</label>
                        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($edit_cat['description'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <?= $edit_cat ? 'Cập nhật' : 'Thêm danh mục' ?>
                    </button>
                    <?php if ($edit_cat): ?>
                        <a href="categories.php" class="btn btn-outline-secondary w-100 mt-2">Hủy</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <!-- List -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header fw-bold bg-white border-0">
                <i class="bi bi-grid me-2"></i>Danh sách danh mục (<?= $categories->num_rows ?>)
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Tên danh mục</th>
                            <th>Mô tả</th>
                            <th class="text-center">Số SP</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($c = $categories->fetch_assoc()): ?>
                            <tr>
                                <td class="text-muted"><?= $c['id'] ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($c['name']) ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($c['description'] ?? '') ?></td>
                                <td class="text-center"><span class="badge bg-secondary"><?= $c['product_count'] ?></span></td>
                                <td class="text-center">
                                    <a href="categories.php?edit=<?= $c['id'] ?>" class="btn btn-sm btn-outline-warning me-1"><i class="bi bi-pencil"></i></a>
                                    <a href="categories.php?delete=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Xóa danh mục này?')"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php adminFooter(); ?>