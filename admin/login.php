<?php
// admin/login.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/db.php';

if (isAdmin()) redirect('index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($conn, $_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $result = $conn->query("SELECT * FROM users WHERE username='$username' AND role='admin'");
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name']= $user['full_name'];
        $_SESSION['role']     = 'admin';
        redirect('index.php');
    } else {
        $error = 'Thông tin đăng nhập không chính xác.';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - SneakerShop</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: linear-gradient(135deg,#1a1a2e,#16213e); min-height:100vh; }
        .btn-admin { background:#e74c3c; border-color:#e74c3c; color:white; }
        .btn-admin:hover { background:#c0392b; border-color:#c0392b; color:white; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">
    <div class="card shadow-lg border-0 p-4" style="width:100%;max-width:380px;border-radius:16px">
        <div class="text-center mb-4">
            <i class="bi bi-shield-lock-fill" style="font-size:3rem;color:#e74c3c"></i>
            <h4 class="fw-bold mt-2">Admin Panel</h4>
            <small class="text-muted">SneakerShop Management</small>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger py-2 text-center"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Tên đăng nhập admin" required>
                </div>
            </div>
            <div class="mb-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Mật khẩu" required>
                </div>
            </div>
            <button type="submit" class="btn btn-admin w-100 py-2 fw-semibold">
                <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập
            </button>
        </form>
        <div class="text-center mt-3">
            <a href="../index.php" class="text-muted small text-decoration-none"><i class="bi bi-house me-1"></i>Về trang chủ</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
