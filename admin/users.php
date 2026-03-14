<?php
require_once '_layout.php';
adminHeader('Quản lý người dùng');

$msg = '';
$per_page = 10;
$page     = max(1, (int)($_GET['page'] ?? 1));
$search   = isset($_GET['q']) ? sanitize($conn, $_GET['q']) : '';
$filter_role = isset($_GET['role']) ? sanitize($conn, $_GET['role']) : '';

// Add user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $username = sanitize($conn, $_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $fullname = sanitize($conn, $_POST['full_name'] ?? '');
        $email    = sanitize($conn, $_POST['email'] ?? '');
        $phone    = sanitize($conn, $_POST['phone'] ?? '');
        $address  = sanitize($conn, $_POST['address'] ?? '');
        $ward     = sanitize($conn, $_POST['ward'] ?? '');
        $district = sanitize($conn, $_POST['district'] ?? '');
        $city     = sanitize($conn, $_POST['city'] ?? '');
        $role     = sanitize($conn, $_POST['role'] ?? 'customer');

        if (!$username || !$password || !$fullname) {
            $msg = '<div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i>Vui lòng nhập đầy đủ thông tin bắt buộc.</div>';
        } elseif (strlen($password) < 6) {
            $msg = '<div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i>Mật khẩu phải có ít nhất 6 ký tự.</div>';
        } else {
            $check = $conn->query("SELECT id FROM users WHERE username='$username'");
            if ($check->num_rows > 0) {
                $msg = '<div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i>Tên đăng nhập đã tồn tại.</div>';
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $conn->query("INSERT INTO users (username,password,full_name,email,phone,address,ward,district,city,role)
                    VALUES ('$username','$hashed','$fullname','$email','$phone','$address','$ward','$district','$city','$role')");
                $msg = '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>Đã thêm tài khoản <strong>'.$username.'</strong> thành công.</div>';
            }
        }
    }
    if ($_POST['action'] === 'reset_password') {
        $uid      = (int)$_POST['user_id'];
        $password = $_POST['new_password'] ?? '';
        if (strlen($password) >= 6) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password='$hashed' WHERE id=$uid");
            $msg = '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>Đã đặt lại mật khẩu.</div>';
        } else {
            $msg = '<div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i>Mật khẩu phải ít nhất 6 ký tự.</div>';
        }
    }
}

// Toggle lock
if (isset($_GET['toggle'])) {
    $uid = (int)$_GET['toggle'];
    $u   = $conn->query("SELECT status, username FROM users WHERE id=$uid")->fetch_assoc();
    if ($u['status'] === 'active') {
        $conn->query("UPDATE users SET status='locked' WHERE id=$uid");
        $msg = '<div class="alert alert-warning"><i class="bi bi-lock me-2"></i>Đã khóa <strong>'.htmlspecialchars($u['username']).'</strong>. User bị đăng xuất ngay lần tải trang tiếp theo.</div>';
    } else {
        $conn->query("UPDATE users SET status='active' WHERE id=$uid");
        $msg = '<div class="alert alert-success"><i class="bi bi-unlock me-2"></i>Đã mở khóa <strong>'.htmlspecialchars($u['username']).'</strong>.</div>';
    }
}

// Build query
$where = "1=1";
if ($search)      $where .= " AND (username LIKE '%$search%' OR full_name LIKE '%$search%' OR email LIKE '%$search%')";
if ($filter_role) $where .= " AND role='$filter_role'";
$offset = ($page - 1) * $per_page;
$total  = $conn->query("SELECT COUNT(*) as c FROM users WHERE $where")->fetch_assoc()['c'];
$users  = $conn->query("SELECT * FROM users WHERE $where ORDER BY role DESC, created_at DESC LIMIT $per_page OFFSET $offset");
$params = array_filter(['q'=>$search,'role'=>$filter_role]);
?>

<?= $msg ?>

<!-- Add user modal trigger -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-0">Danh sách tài khoản <span class="badge bg-secondary"><?= $total ?></span></h5>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="bi bi-person-plus me-2"></i>Thêm tài khoản
    </button>
</div>

<!-- Search & filter bar -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Tìm tên đăng nhập, họ tên, email..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select" onchange="this.form.submit()">
                    <option value="">Tất cả vai trò</option>
                    <option value="customer" <?= $filter_role==='customer'?'selected':'' ?>>Khách hàng</option>
                    <option value="admin"    <?= $filter_role==='admin'?'selected':'' ?>>Admin</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Tìm</button>
            </div>
            <?php if ($search || $filter_role): ?>
            <div class="col-md-2">
                <a href="users.php" class="btn btn-outline-secondary w-100"><i class="bi bi-x me-1"></i>Xóa lọc</a>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- User table -->
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Tên đăng nhập</th>
                    <th>Họ tên</th>
                    <th>Email / SĐT</th>
                    <th>Địa chỉ</th>
                    <th class="text-center">Vai trò</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($users->num_rows === 0): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">Không tìm thấy tài khoản nào.</td></tr>
                <?php endif; ?>
                <?php while ($u = $users->fetch_assoc()): ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= htmlspecialchars($u['full_name']) ?></td>
                    <td class="small text-muted">
                        <?= htmlspecialchars($u['email']) ?>
                        <?php if ($u['phone']): ?><br><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($u['phone']) ?><?php endif; ?>
                    </td>
                    <td class="small text-muted">
                        <?php if ($u['address']): ?>
                        <?= htmlspecialchars($u['ward'].', '.$u['district']) ?>
                        <?php else: ?><span class="text-warning small">Chưa có địa chỉ</span><?php endif; ?>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-<?= $u['role']==='admin'?'danger':'secondary' ?>">
                            <?= $u['role']==='admin'?'Admin':'Khách hàng' ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-<?= $u['status']==='active'?'success':'warning' ?>">
                            <?= $u['status']==='active'?'Hoạt động':'Bị khóa' ?>
                        </span>
                    </td>
                    <td class="text-center text-nowrap">
                        <button class="btn btn-sm btn-outline-secondary me-1" title="Đặt lại mật khẩu"
                                data-bs-toggle="modal" data-bs-target="#pwModal<?= $u['id'] ?>">
                            <i class="bi bi-key"></i>
                        </button>
                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                        <a href="users.php?toggle=<?= $u['id'] ?>&<?= http_build_query($params) ?>"
                           class="btn btn-sm btn-outline-<?= $u['status']==='active'?'warning':'success' ?>"
                           title="<?= $u['status']==='active'?'Khóa':'Mở khóa' ?>"
                           onclick="return confirm('Xác nhận thay đổi trạng thái?')">
                            <i class="bi bi-<?= $u['status']==='active'?'lock':'unlock' ?>"></i>
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>

                <!-- Password reset modal -->
                <div class="modal fade" id="pwModal<?= $u['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-sm">
                        <div class="modal-content">
                            <div class="modal-header py-2">
                                <h6 class="modal-title"><i class="bi bi-key me-2"></i><?= htmlspecialchars($u['username']) ?></h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST">
                                <div class="modal-body">
                                    <input type="hidden" name="action" value="reset_password">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <label class="form-label small fw-semibold">Mật khẩu mới (≥ 6 ký tự)</label>
                                    <input type="text" name="new_password" class="form-control form-control-sm" placeholder="Nhập mật khẩu mới" required>
                                </div>
                                <div class="modal-footer py-2">
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy</button>
                                    <button type="submit" class="btn btn-primary btn-sm">Đặt lại</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php if ($total > $per_page): ?>
    <div class="card-footer bg-white border-top-0">
        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">Hiển thị <?= min($offset+1,$total) ?>–<?= min($offset+$per_page,$total) ?> / <?= $total ?></small>
            <?= renderPagination($total, $page, $per_page, $params) ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:#1a1a2e;color:white">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Thêm tài khoản mới</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Account info -->
                        <div class="col-12">
                            <p class="text-muted small fw-bold mb-2 border-bottom pb-1">
                                <i class="bi bi-person-badge me-1"></i>THÔNG TIN TÀI KHOẢN
                            </p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tên đăng nhập <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Vai trò</label>
                            <select name="role" class="form-select">
                                <option value="customer">Khách hàng</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mật khẩu khởi tạo <span class="text-danger">*</span></label>
                            <input type="text" name="password" class="form-control" placeholder="Tối thiểu 6 ký tự" required>
                            <div class="form-text text-warning"><i class="bi bi-info-circle me-1"></i>Yêu cầu người dùng đổi sau khi đăng nhập.</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control" placeholder="0901...">
                        </div>
                        <!-- Address -->
                        <div class="col-12 mt-2">
                            <p class="text-muted small fw-bold mb-2 border-bottom pb-1">
                                <i class="bi bi-geo-alt me-1"></i>ĐỊA CHỈ GIAO HÀNG <span class="fw-normal text-muted">(tùy chọn)</span>
                            </p>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Địa chỉ (số nhà, tên đường)</label>
                            <input type="text" name="address" class="form-control" placeholder="VD: 123 Nguyễn Huệ">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Phường/Xã</label>
                            <input type="text" name="ward" class="form-control" placeholder="Phường Bến Nghé">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Quận/Huyện</label>
                            <input type="text" name="district" class="form-control" placeholder="Quận 1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tỉnh/Thành phố</label>
                            <input type="text" name="city" class="form-control" placeholder="TP. Hồ Chí Minh">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-person-plus me-2"></i>Thêm tài khoản
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php adminFooter(); ?>
