<?php
// admin/discounts.php
require_once '_layout.php';
adminHeader('Quản lý mã giảm giá');

$msg = '';

// TOGGLE STATUS
if (isset($_GET['toggle_status'])) {
    $id = (int)$_GET['toggle_status'];
    $conn->query("UPDATE discount_codes SET status = IF(status='active', 'inactive', 'active') WHERE id=$id");
    redirect('discounts.php');
}

// SAVE (add/edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $code           = sanitize($conn, $_POST['code'] ?? '');
    $type           = sanitize($conn, $_POST['discount_type'] ?? 'fixed');
    $value          = (float)$_POST['discount_value'];
    $min_order      = (float)$_POST['min_order_amount'];
    $max_uses       = !empty($_POST['max_uses']) ? (int)$_POST['max_uses'] : 'NULL';
    $max_per_user   = (int)$_POST['max_uses_per_user'];
    $start_date     = !empty($_POST['start_date']) ? "'" . sanitize($conn, $_POST['start_date']) . "'" : 'NULL';
    $end_date       = !empty($_POST['end_date'])   ? "'" . sanitize($conn, $_POST['end_date'])   . "'" : 'NULL';
    $apply_to       = sanitize($conn, $_POST['apply_to'] ?? 'all');
    $categories     = $_POST['categories'] ?? [];

    if (!$code || $value <= 0) {
        $msg = '<div class="alert alert-danger">Vui lòng nhập mã code và giá trị giảm hợp lệ.</div>';
    } else {
        if ($_POST['action'] === 'add') {
            // Check unique
            $check = $conn->query("SELECT id FROM discount_codes WHERE code='$code'");
            if ($check->num_rows > 0) {
                $msg = '<div class="alert alert-danger">Mã code này đã tồn tại.</div>';
            } else {
                $sql = "INSERT INTO discount_codes (code, discount_type, discount_value, min_order_amount, max_uses, max_uses_per_user, start_date, end_date, apply_to, status) 
                        VALUES ('$code', '$type', $value, $min_order, $max_uses, $max_per_user, $start_date, $end_date, '$apply_to', 'active')";
                
                if ($conn->query($sql)) {
                    $discount_id = $conn->insert_id;
                    if ($apply_to === 'category' && !empty($categories)) {
                        foreach ($categories as $cat_id) {
                            $cat_id = (int)$cat_id;
                            $conn->query("INSERT INTO discount_code_categories (discount_code_id, category_id) VALUES ($discount_id, $cat_id)");
                        }
                    }
                    $msg = '<div class="alert alert-success">Đã tạo mã giảm giá thành công.</div>';
                } else {
                    $msg = '<div class="alert alert-danger">Lỗi: ' . $conn->error . '</div>';
                }
            }
        }
    }
}

$discounts = $conn->query("SELECT * FROM discount_codes ORDER BY created_at DESC");
$all_categories = $conn->query("SELECT * FROM categories ORDER BY name");
?>

<?= $msg ?>

<div class="row g-4">
    <!-- Form Create -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header fw-bold bg-white border-0">
                <i class="bi bi-plus-circle me-2"></i>Tạo mã mới
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Mã Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" placeholder="Ví dụ: KM50K" required style="text-transform: uppercase">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Loại giảm</label>
                            <select name="discount_type" class="form-select">
                                <option value="fixed">Tiền cố định (đ)</option>
                                <option value="percentage">Phần trăm (%)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Giá trị giảm <span class="text-danger">*</span></label>
                            <input type="number" name="discount_value" class="form-control" required min="1">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Đơn hàng tối thiểu (đ)</label>
                        <input type="number" name="min_order_amount" class="form-control" value="0" min="0">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Tổng lượt dùng</label>
                            <input type="number" name="max_uses" class="form-control" placeholder="Vô hạn" min="1">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Lượt/Khách</label>
                            <input type="number" name="max_uses_per_user" class="form-control" value="1" min="1">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Ngày bắt đầu</label>
                            <input type="datetime-local" name="start_date" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Ngày kết thúc</label>
                            <input type="datetime-local" name="end_date" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Áp dụng cho</label>
                        <select name="apply_to" class="form-select" onchange="toggleCatSelect(this.value)">
                            <option value="all">Tất cả sản phẩm</option>
                            <option value="category">Danh mục cụ thể</option>
                        </select>
                    </div>

                    <div id="catSelectArea" class="mb-3" style="display:none">
                        <label class="form-label fw-semibold small">Chọn danh mục (giữ Ctrl để chọn nhiều)</label>
                        <select name="categories[]" class="form-select" multiple size="4">
                            <?php while($c = $all_categories->fetch_assoc()): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-save me-2"></i>Lưu mã giảm giá
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- List -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header fw-bold bg-white border-0">
                <i class="bi bi-ticket-perforated me-2"></i>Danh sách mã (<?= $discounts->num_rows ?>)
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>Mã Code</th>
                            <th>Loại giảm</th>
                            <th>Giá trị</th>
                            <th>Tối thiểu</th>
                            <th>Hạn dùng</th>
                            <th class="text-center">Đã dùng</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($discounts->num_rows == 0): ?>
                            <tr><td colspan="8" class="text-center py-4 text-muted">Chưa có mã giảm giá nào.</td></tr>
                        <?php endif; ?>
                        <?php while ($d = $discounts->fetch_assoc()): ?>
                        <tr>
                            <td><strong class="text-primary"><?= htmlspecialchars($d['code']) ?></strong></td>
                            <td><?= $d['discount_type'] === 'percentage' ? 'Giảm %' : 'Giảm tiền' ?></td>
                            <td><?= $d['discount_type'] === 'percentage' ? $d['discount_value'] . '%' : formatPrice($d['discount_value']) ?></td>
                            <td><?= formatPrice($d['min_order_amount']) ?></td>
                            <td>
                                <?php 
                                if (!$d['end_date']) echo 'Vô hạn';
                                else {
                                    $end = strtotime($d['end_date']);
                                    echo date('d/m/y H:i', $end);
                                    if ($end < time()) echo ' <span class="badge bg-danger">Hết hạn</span>';
                                }
                                ?>
                            </td>
                            <td class="text-center">
                                <?= $d['total_used'] ?> / <?= $d['max_uses'] ?? '∞' ?>
                            </td>
                            <td class="text-center">
                                <a href="discounts.php?toggle_status=<?= $d['id'] ?>" class="text-decoration-none">
                                    <span class="badge bg-<?= $d['status'] == 'active' ? 'success' : 'secondary' ?>">
                                        <?= $d['status'] == 'active' ? 'Đang bật' : 'Đang tắt' ?>
                                    </span>
                                </a>
                            </td>
                            <td class="text-center">
                                <a href="discounts.php?toggle_status=<?= $d['id'] ?>" class="btn btn-sm btn-outline-primary" title="Bật/Tắt">
                                    <i class="bi bi-power"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function toggleCatSelect(val) {
    document.getElementById('catSelectArea').style.display = (val === 'category' ? 'block' : 'none');
}
</script>

<?php adminFooter(); ?>
