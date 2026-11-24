<?php
// profile.php
require_once 'includes/db.php';
if (!isLoggedIn()) {
    redirect('login.php');
}

require_once 'includes/header.php';

$uid = (int)$_SESSION['user_id'];
$msg = '';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $fullname = sanitize($conn, $_POST['full_name'] ?? '');
    $email    = sanitize($conn, $_POST['email'] ?? '');
    $phone    = sanitize($conn, $_POST['phone'] ?? '');
    $address  = sanitize($conn, $_POST['address'] ?? '');
    $ward     = sanitize($conn, $_POST['ward'] ?? '');
    $district = sanitize($conn, $_POST['district'] ?? '');
    $city     = sanitize($conn, $_POST['city'] ?? '');

    if (!$fullname) {
        $msg = '<div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i>Họ tên không được để trống.</div>';
    } else {
        $conn->query("UPDATE users SET full_name='$fullname', email='$email', phone='$phone', address='$address', ward='$ward', district='$district', city='$city' WHERE id=$uid");
        $_SESSION['full_name'] = $fullname;
        $msg = '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>Đã cập nhật thông tin cá nhân thành công.</div>';
    }
}

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $cur_pass  = $_POST['current_password'] ?? '';
    $new_pass  = $_POST['new_password'] ?? '';
    $conf_pass = $_POST['confirm_password'] ?? '';

    $user_data = $conn->query("SELECT password FROM users WHERE id=$uid")->fetch_assoc();

    if (!password_verify($cur_pass, $user_data['password'])) {
        $msg = '<div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i>Mật khẩu hiện tại không chính xác.</div>';
    } elseif (strlen($new_pass) < 6) {
        $msg = '<div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i>Mật khẩu mới phải có ít nhất 6 ký tự.</div>';
    } elseif ($new_pass !== $conf_pass) {
        $msg = '<div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i>Xác nhận mật khẩu mới không khớp.</div>';
    } else {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $conn->query("UPDATE users SET password='$hashed' WHERE id=$uid");
        $msg = '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>Đã đổi mật khẩu thành công.</div>';
    }
}

// Fetch current user info
$user = $conn->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc();
$pageTitle = 'Thông tin cá nhân';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h4 class="fw-bold mb-0"><i class="bi bi-person-gear text-primary me-2"></i>Quản lý tài khoản cá nhân</h4>
                <a href="my_orders.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-bag-check me-1"></i>Đơn hàng của tôi</a>
            </div>

            <?= $msg ?>

            <div class="row g-4">
                <!-- Left: Profile Info Form -->
                <div class="col-md-7">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white border-0 fw-bold py-3">
                            <i class="bi bi-person-lines-fill me-2 text-primary"></i>Thông tin cá nhân & Địa chỉ giao hàng
                        </div>
                        <div class="card-body p-4">
                            <form method="POST">
                                <input type="hidden" name="action" value="update_profile">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Tên đăng nhập</label>
                                        <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($user['username']) ?>" readonly disabled>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                                        <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Email</label>
                                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Số điện thoại</label>
                                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" placeholder="0901...">
                                    </div>
                                    
                                    <div class="col-12 mt-4">
                                        <h6 class="fw-bold text-muted border-bottom pb-2 mb-3"><i class="bi bi-geo-alt me-1"></i>Địa chỉ mặc định khi giao hàng</h6>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Số nhà, tên đường</label>
                                        <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($user['address'] ?? '') ?>" placeholder="VD: 123 Nguyễn Huệ">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Phường / Xã</label>
                                        <input type="text" name="ward" class="form-control" value="<?= htmlspecialchars($user['ward'] ?? '') ?>" placeholder="VD: Phường Bến Nghé">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Quận / Huyện</label>
                                        <input type="text" name="district" class="form-control" value="<?= htmlspecialchars($user['district'] ?? '') ?>" placeholder="VD: Quận 1">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Tỉnh / Thành phố</label>
                                        <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($user['city'] ?? '') ?>" placeholder="VD: TP. Hồ Chí Minh">
                                    </div>
                                </div>
                                <div class="mt-4 text-end">
                                    <button type="submit" class="btn btn-primary px-4 fw-semibold"><i class="bi bi-save me-1"></i>Lưu thay đổi</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Right: Password Change Form -->
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white border-0 fw-bold py-3">
                            <i class="bi bi-shield-lock me-2 text-danger"></i>Đổi mật khẩu
                        </div>
                        <div class="card-body p-4">
                            <form method="POST">
                                <input type="hidden" name="action" value="change_password">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                                    <input type="password" name="current_password" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Mật khẩu mới <span class="text-danger">*</span></label>
                                    <input type="password" name="new_password" class="form-control" placeholder="Tối thiểu 6 ký tự" required>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Nhập lại mật khẩu mới <span class="text-danger">*</span></label>
                                    <input type="password" name="confirm_password" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-outline-danger w-100 fw-semibold"><i class="bi bi-key me-1"></i>Cập nhật mật khẩu</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
