<?php
// admin/users.php
require_once '_layout.php';
adminHeader('Quản lý người dùng');

$msg = '';

// Add user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $username = sanitize($conn, $_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $fullname = sanitize($conn, $_POST['full_name'] ?? '');
        $email    = sanitize($conn, $_POST['email'] ?? '');
        $role     = sanitize($conn, $_POST['role'] ?? 'customer');

        if (!$username || !$password || !$fullname) {
            $msg = '<div class="alert alert-danger">Vui lòng nhập đầy đủ thông tin.</div>';
        } else {
            $check = $conn->query("SELECT id FROM users WHERE username='$username'");
            if ($check->num_rows > 0) {
                $msg = '<div class="alert alert-danger">Tên đăng nhập đã tồn tại.</div>';
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $conn->query("INSERT INTO users (username,password,full_name,email,role) VALUES ('$username','$hashed','$fullname','$email','$role')");
                $msg = '<div class="alert alert-success">Đã thêm tài khoản thành công.</div>';
            }
        }
    }
    if ($_POST['action'] === 'reset_password') {
        $uid      = (int)$_POST['user_id'];
        $password = $_POST['new_password'] ?? '';
        if (strlen($password) >= 6) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password='$hashed' WHERE id=$uid");
            $msg = '<div class="alert alert-success">Đã khởi tạo lại mật khẩu.</div>';
        } else {
            $msg = '<div class="alert alert-danger">Mật khẩu phải ít nhất 6 ký tự.</div>';
        }
    }
}

// Toggle lock
if (isset($_GET['toggle'])) {
    $uid = (int)$_GET['toggle'];
    $u = $conn->query("SELECT status, username FROM users WHERE id=$uid")->fetch_assoc();
    if ($u['status'] === 'active') {
        $conn->query("UPDATE users SET status='locked' WHERE id=$uid");
        // isLoggedIn() in db.php checks DB status on every request,
        // so the user will be auto-logged out on their very next page load.
        $msg = '<div class="alert alert-warning"><i class="bi bi-lock me-2"></i>Đã khóa tài khoản <strong>'
             . htmlspecialchars($u['username'])
             . '</strong>. Người dùng sẽ bị tự động đăng xuất ngay lần tải trang tiếp theo.</div>';
    } else {
        $conn->query("UPDATE users SET status='active' WHERE id=$uid");
        $msg = '<div class="alert alert-success"><i class="bi bi-unlock me-2"></i>Đã mở khóa tài khoản <strong>'
             . htmlspecialchars($u['username']) . '</strong>.</div>';
    }
}

$users = $conn->query("SELECT * FROM users ORDER BY role DESC, created_at DESC");
?>

<?= $msg ?>

<div class="row g-4">
    <!-- Add User Form -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header fw-bold bg-white border-0"><i class="bi bi-person-plus me-2"></i>Thêm tài khoản</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Tên đăng nhập <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                        <input type="text" name="password" class="form-control" placeholder="Mật khẩu khởi tạo" required>
                        <div class="form-text">Người dùng cần đổi mật khẩu sau khi đăng nhập.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Vai trò</label>
                        <select name="role" class="form-select">
                            <option value="customer">Khách hàng</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Thêm tài khoản</button>
                </form>
            </div>
        </div>
    </div>

    <!-- User list -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header fw-bold bg-white border-0"><i class="bi bi-people me-2"></i>Danh sách tài khoản</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Tên đăng nhập</th><th>Họ tên</th><th>Email</th><th class="text-center">Vai trò</th><th class="text-center">Trạng thái</th><th class="text-center">Thao tác</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($u = $users->fetch_assoc()): ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($u['username']) ?></td>
                            <td><?= htmlspecialchars($u['full_name']) ?></td>
                            <td class="small text-muted"><?= htmlspecialchars($u['email']) ?></td>
                            <td class="text-center">
                                <span class="badge bg-<?= $u['role']=='admin' ? 'danger' : 'secondary' ?>">
                                    <?= $u['role'] == 'admin' ? 'Admin' : 'Khách hàng' ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-<?= $u['status']=='active' ? 'success' : 'warning' ?>">
                                    <?= $u['status'] == 'active' ? 'Hoạt động' : 'Bị khóa' ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <!-- Reset password -->
                                <button class="btn btn-sm btn-outline-secondary me-1" data-bs-toggle="modal" data-bs-target="#pwModal<?= $u['id'] ?>" title="Đặt lại mật khẩu">
                                    <i class="bi bi-key"></i>
                                </button>
                                <!-- Lock/Unlock - can't lock self or other admins easily -->
                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                <a href="users.php?toggle=<?= $u['id'] ?>" class="btn btn-sm btn-outline-<?= $u['status']=='active' ? 'warning' : 'success' ?>" title="<?= $u['status']=='active' ? 'Khóa' : 'Mở khóa' ?>" onclick="return confirm('Xác nhận thay đổi trạng thái tài khoản?')">
                                    <i class="bi bi-<?= $u['status']=='active' ? 'lock' : 'unlock' ?>"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- Password reset modal -->
                        <div class="modal fade" id="pwModal<?= $u['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h6 class="modal-title">Đặt lại mật khẩu: <?= htmlspecialchars($u['username']) ?></h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="action" value="reset_password">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <label class="form-label">Mật khẩu mới</label>
                                            <input type="text" name="new_password" class="form-control" placeholder="Ít nhất 6 ký tự" required>
                                        </div>
                                        <div class="modal-footer">
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
        </div>
    </div>
</div>

<?php adminFooter(); ?>
