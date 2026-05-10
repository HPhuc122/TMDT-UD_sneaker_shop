<?php
// admin/discounts.php
require_once '_layout.php';
$msg = '';

// TOGGLE STATUS
if (isset($_GET['toggle_status'])) {
    $id = (int)$_GET['toggle_status'];
    $conn->query("UPDATE discount_codes SET status = IF(status='active', 'inactive', 'active') WHERE id=$id");
    redirect('discounts.php');
}

// DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Kiểm tra xem mã đã được sử dụng chưa
    $check_usage = $conn->query("SELECT total_used FROM discount_codes WHERE id=$id")->fetch_assoc();
    
    if ($check_usage && $check_usage['total_used'] > 0) {
        $msg = '<div class="alert alert-warning shadow-sm"><i class="bi bi-exclamation-triangle-fill me-2"></i>Mã giảm giá này đã được sử dụng trong các đơn hàng. Để đảm bảo tính toàn vẹn dữ liệu, bạn <b>không được xóa</b> mã này mà chỉ có thể <b>Tắt</b> trạng thái để ngưng áp dụng.</div>';
    } else {
        // Delete related records first (if no cascade)
        $conn->query("DELETE FROM discount_code_categories WHERE discount_code_id=$id");
        $conn->query("DELETE FROM discount_code_products WHERE discount_code_id=$id");
        $conn->query("DELETE FROM discount_code_usages WHERE discount_code_id=$id");
        $conn->query("DELETE FROM user_saved_coupons WHERE discount_code_id=$id");
        // Delete coupon
        $conn->query("DELETE FROM discount_codes WHERE id=$id");
        redirect('discounts.php');
    }
}

// SAVE (add/edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $code           = sanitize($conn, $_POST['code'] ?? '');
    $type           = sanitize($conn, $_POST['discount_type'] ?? 'fixed');
    $value          = (float)$_POST['discount_value'];
    $max_discount   = !empty($_POST['max_discount_amount']) ? (float)$_POST['max_discount_amount'] : 'NULL';
    $min_order      = (float)$_POST['min_order_amount'];
    $max_uses       = !empty($_POST['max_uses']) ? (int)$_POST['max_uses'] : 'NULL';
    $max_per_user   = (int)$_POST['max_uses_per_user'];
    $start_date_val = $_POST['start_date'] ?? '';
    $end_date_val   = $_POST['end_date']   ?? '';
    $now_str        = date('Y-m-d\TH:i');
    $apply_to       = sanitize($conn, $_POST['apply_to'] ?? 'all');
    $categories     = $_POST['categories'] ?? [];
    $products       = $_POST['products'] ?? [];

    if (!$code || $value <= 0) {
        $msg = '<div class="alert alert-danger">Vui lòng nhập mã code và giá trị giảm hợp lệ.</div>';
    } elseif ($max_discount !== 'NULL' && (float)$_POST['max_discount_amount'] < 0) {
        $msg = '<div class="alert alert-danger">Số tiền giảm tối đa không được nhỏ hơn 0.</div>';
    } elseif ($max_uses !== 'NULL' && $max_uses < 1) {
        $msg = '<div class="alert alert-danger">Tổng lượt dùng phải ít nhất là 1.</div>';
    } elseif ($max_per_user < 1) {
        $msg = '<div class="alert alert-danger">Số lượt dùng mỗi khách phải ít nhất là 1.</div>';
    } elseif ($max_uses !== 'NULL' && $max_per_user > $max_uses) {
        $msg = '<div class="alert alert-danger">Số lượt dùng mỗi khách không được lớn hơn tổng lượt dùng của mã.</div>';
    } elseif ($start_date_val && $start_date_val < $now_str && !isset($_GET['edit'])) {
        $msg = '<div class="alert alert-danger">Ngày bắt đầu không được nhỏ hơn thời gian hiện tại.</div>';
    } elseif ($start_date_val && $end_date_val) {
        if ($start_date_val === $end_date_val) {
            $day = date('Y-m-d', strtotime($start_date_val));
            $start_date = "'$day 00:00:00'";
            $end_date   = "'$day 23:59:59'";
        } elseif ($start_date_val > $end_date_val) {
            $msg = '<div class="alert alert-danger">Ngày bắt đầu phải nhỏ hơn hoặc bằng ngày kết thúc.</div>';
        } else {
            $start_date = "'" . sanitize($conn, $start_date_val) . "'";
            $end_date   = "'" . sanitize($conn, $end_date_val) . "'";
        }
    } else {
        $start_date = !empty($start_date_val) ? "'" . sanitize($conn, $start_date_val) . "'" : 'NULL';
        $end_date   = !empty($end_date_val)   ? "'" . sanitize($conn, $end_date_val)   . "'" : 'NULL';
    }

    if (empty($msg)) {
        if ($_POST['action'] === 'add') {
            $check = $conn->query("SELECT id FROM discount_codes WHERE code='$code'");
            if ($check->num_rows > 0) {
                $msg = '<div class="alert alert-danger">Mã code này đã tồn tại.</div>';
            } else {
                $sql = "INSERT INTO discount_codes (code, discount_type, discount_value, max_discount_amount, min_order_amount, max_uses, max_uses_per_user, start_date, end_date, apply_to, status) 
                        VALUES ('$code', '$type', $value, $max_discount, $min_order, $max_uses, $max_per_user, $start_date, $end_date, '$apply_to', 'active')";
                
                if ($conn->query($sql)) {
                    $discount_id = $conn->insert_id;
                    if ($apply_to === 'category' && !empty($categories)) {
                        foreach ($categories as $cat_id) {
                            $cat_id = (int)$cat_id;
                            $conn->query("INSERT INTO discount_code_categories (discount_code_id, category_id) VALUES ($discount_id, $cat_id)");
                        }
                    } elseif ($apply_to === 'product' && !empty($products)) {
                        foreach ($products as $p_id) {
                            $p_id = (int)$p_id;
                            $conn->query("INSERT INTO discount_code_products (discount_code_id, product_id) VALUES ($discount_id, $p_id)");
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

adminHeader('Quản lý mã giảm giá');

$discounts = $conn->query("SELECT * FROM discount_codes ORDER BY created_at DESC");
$all_categories = $conn->query("SELECT * FROM categories ORDER BY name");
$all_products = $conn->query("SELECT id, name, code FROM products WHERE status='active' ORDER BY name");
?>

<?= $msg ?>

<div class="row g-4">
    <!-- Form Create -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header fw-bold bg-white border-0">
                <i class="bi bi-plus-circle me-2"></i>Tạo mã mới (Advanced)
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
                            <select name="discount_type" class="form-select" id="discountTypeSelect" onchange="toggleMaxDiscount(this.value)">
                                <option value="fixed">Tiền cố định (đ)</option>
                                <option value="percentage">Phần trăm (%)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Giá trị giảm <span class="text-danger">*</span></label>
                            <input type="number" name="discount_value" class="form-control" required min="1">
                        </div>
                    </div>

                    <div class="mb-3" id="maxDiscountArea" style="display:none">
                        <label class="form-label fw-semibold small">Giảm tối đa (đ)</label>
                        <input type="number" name="max_discount_amount" class="form-control" placeholder="Để trống nếu không giới hạn" min="0">
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
                        <select name="apply_to" class="form-select" onchange="toggleScope(this.value)">
                            <option value="all">Tất cả sản phẩm</option>
                            <option value="category">Danh mục cụ thể</option>
                            <option value="product">Sản phẩm cụ thể</option>
                        </select>
                    </div>

                    <div id="catSelectArea" class="mb-3" style="display:none">
                        <label class="form-label fw-semibold small">Chọn danh mục (giữ Ctrl)</label>
                        <select name="categories[]" class="form-select" multiple size="4">
                            <?php while($c = $all_categories->fetch_assoc()): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div id="prodSelectArea" class="mb-3" style="display:none">
                        <label class="form-label fw-semibold small">Chọn sản phẩm (giữ Ctrl)</label>
                        <select name="products[]" class="form-select" multiple size="6">
                            <?php while($p = $all_products->fetch_assoc()): ?>
                                <option value="<?= $p['id'] ?>">[<?= htmlspecialchars($p['code']) ?>] <?= htmlspecialchars($p['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-save me-2"></i>Lưu mã giảm
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
                            <th>Loại</th>
                            <th>Giá trị</th>
                            <th>Phạm vi</th>
                            <th class="text-center">Đã dùng</th>
                            <th class="text-center">TT</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($discounts->num_rows == 0): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">Chưa có mã giảm giá nào.</td></tr>
                        <?php endif; ?>
                        <?php while ($d = $discounts->fetch_assoc()): ?>
                        <tr>
                            <td><strong class="text-primary"><?= htmlspecialchars($d['code']) ?></strong></td>
                            <td><?= $d['discount_type'] === 'percentage' ? 'Giảm %' : 'Giảm tiền' ?></td>
                            <td>
                                <?= $d['discount_type'] === 'percentage' ? $d['discount_value'] . '%' : formatPrice($d['discount_value']) ?>
                                <?php if ($d['max_discount_amount']): ?>
                                    <br><small class="text-muted">(Max: <?= formatPrice($d['max_discount_amount']) ?>)</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                    if ($d['apply_to'] === 'all') echo 'Tất cả SP';
                                    elseif ($d['apply_to'] === 'category') echo 'Theo danh mục';
                                    elseif ($d['apply_to'] === 'product') echo 'Theo sản phẩm';
                                ?>
                            </td>
                            <td class="text-center">
                                <?= $d['total_used'] ?> / <?= $d['max_uses'] ?? '∞' ?>
                            </td>
                            <td class="text-center">
                                <a href="discounts.php?toggle_status=<?= $d['id'] ?>" class="text-decoration-none">
                                    <span class="badge bg-<?= $d['status'] == 'active' ? 'success' : 'secondary' ?>">
                                        <?= $d['status'] == 'active' ? 'Bật' : 'Tắt' ?>
                                    </span>
                                </a>
                            </td>
                            <td class="text-center">
                                <a href="discounts.php?toggle_status=<?= $d['id'] ?>" class="btn btn-sm btn-outline-primary" title="Đổi trạng thái">
                                    <i class="bi bi-power"></i>
                                </a>
                                <a href="discounts.php?delete=<?= $d['id'] ?>" class="btn btn-sm btn-outline-danger" title="Xóa mã" onclick="return confirm('Bạn có chắc chắn muốn xóa mã giảm giá này? Toàn bộ dữ liệu liên quan sẽ bị mất.');">
                                    <i class="bi bi-trash"></i>
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
function toggleScope(val) {
    document.getElementById('catSelectArea').style.display = (val === 'category' ? 'block' : 'none');
    document.getElementById('prodSelectArea').style.display = (val === 'product' ? 'block' : 'none');
}
function toggleMaxDiscount(val) {
    document.getElementById('maxDiscountArea').style.display = (val === 'percentage' ? 'block' : 'none');
}
</script>

<?php adminFooter(); ?>
