<?php
// register.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/db.php';
if (isLoggedIn()) redirect('index.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($conn, $_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $fullname = sanitize($conn, $_POST['full_name'] ?? '');
    $email    = sanitize($conn, $_POST['email'] ?? '');
    $phone    = sanitize($conn, $_POST['phone'] ?? '');
    $address  = sanitize($conn, $_POST['address'] ?? '');
    $ward     = sanitize($conn, $_POST['ward'] ?? '');
    $district = sanitize($conn, $_POST['district'] ?? '');
    $city     = sanitize($conn, $_POST['city'] ?? '');

    if (!$username || !$password || !$fullname || !$email || !$phone || !$address || !$ward || !$district || !$city) {
        $error = 'Vui lòng điền đầy đủ thông tin.';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
    } elseif ($password !== $confirm) {
        $error = 'Mật khẩu xác nhận không khớp.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ.';
    } else {
        $check = $conn->query("SELECT id FROM users WHERE username='$username'");
        if ($check->num_rows > 0) {
            $error = 'Tên đăng nhập đã tồn tại.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username,password,full_name,email,phone,address,ward,district,city,role) VALUES (?,?,?,?,?,?,?,?,?,'customer')");
            $stmt->bind_param('sssssssss', $username, $hashed, $fullname, $email, $phone, $address, $ward, $district, $city);
            if ($stmt->execute()) {
                $success = 'Đăng ký thành công! <a href="login.php">Đăng nhập ngay</a>';
            } else {
                $error = 'Có lỗi xảy ra, vui lòng thử lại.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - SneakerShop</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: linear-gradient(135deg,#1a1a2e,#0f3460); min-height:100vh; padding: 30px 0; }
        .card { border-radius: 16px; }
        .btn-primary { background:#ff6b35; border-color:#ff6b35; }
        .btn-primary:hover { background:#e55a24; border-color:#e55a24; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="text-center mb-4">
                    <a href="index.php" class="text-decoration-none">
                        <h2 class="fw-bold text-white"><i class="bi bi-lightning-fill" style="color:#ff6b35"></i> SneakerShop</h2>
                    </a>
                </div>
                <div class="card shadow-lg border-0 p-4">
                    <h4 class="text-center mb-4 fw-bold">Đăng ký tài khoản</h4>

                    <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>

                    <form method="POST" onsubmit="return validateForm()">
                        <h6 class="text-muted fw-bold mb-3">THÔNG TIN TÀI KHOẢN</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Tên đăng nhập <span class="text-danger">*</span></label>
                                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                                <input type="password" name="password" id="password" class="form-control" minlength="6" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                                <div id="pwMatch" class="form-text"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                <input type="tel" name="phone" class="form-control" pattern="[0-9]{10,11}" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
                            </div>
                        </div>

                        <h6 class="text-muted fw-bold mb-3">ĐỊA CHỈ GIAO HÀNG</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label">Địa chỉ (số nhà, tên đường) <span class="text-danger">*</span></label>
                                <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Phường/Xã <span class="text-danger">*</span></label>
                                <input type="text" name="ward" class="form-control" value="<?= htmlspecialchars($_POST['ward'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Quận/Huyện <span class="text-danger">*</span></label>
                                <input type="text" name="district" class="form-control" value="<?= htmlspecialchars($_POST['district'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tỉnh/Thành phố <span class="text-danger">*</span></label>
                                <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($_POST['city'] ?? '') ?>" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                            <i class="bi bi-person-plus me-2"></i>Đăng ký
                        </button>
                    </form>
                    <hr>
                    <p class="text-center text-muted mb-0">Đã có tài khoản? <a href="login.php" style="color:#ff6b35">Đăng nhập</a></p>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('confirm_password').addEventListener('input', function() {
        const pw = document.getElementById('password').value;
        const msg = document.getElementById('pwMatch');
        if (this.value === pw) { msg.className = 'form-text text-success'; msg.textContent = '✓ Mật khẩu khớp'; }
        else { msg.className = 'form-text text-danger'; msg.textContent = '✗ Mật khẩu không khớp'; }
    });
    function validateForm() {
        const pw = document.getElementById('password').value;
        const cpw = document.getElementById('confirm_password').value;
        if (pw !== cpw) { alert('Mật khẩu xác nhận không khớp!'); return false; }
        return true;
    }
    </script>
</body>
</html>
