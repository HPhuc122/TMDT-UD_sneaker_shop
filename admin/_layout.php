<?php
// admin/_layout.php
// MUST set session name before ANY include that might start a session
if (session_status() === PHP_SESSION_NONE) {
    session_name('sneaker_admin_sess');
    session_start();
}
require_once '../includes/db.php';
// db.php will see session already active and skip its own session_start()

if (!isAdmin()) {
    redirect('login.php');
}

function adminHeader($title, $active = '') {
    global $_SESSION;
    echo <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>$title - Admin SneakerShop</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #f4f6f9; }
        .sidebar { min-height: 100vh; background: #1a1a2e; width: 240px; position: fixed; top:0; left:0; z-index:100; overflow-y:auto; }
        .sidebar .brand { padding:20px; border-bottom:1px solid rgba(255,255,255,.1); }
        .sidebar .nav-link { color:rgba(255,255,255,.7); padding:10px 20px; border-radius:0; transition:.2s; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color:#fff; background:rgba(231,76,60,.8); }
        .sidebar .nav-link i { width:20px; }
        .main-content { margin-left:240px; padding:20px; }
        .topbar { background:white; padding:12px 20px; border-radius:8px; margin-bottom:20px; box-shadow:0 1px 4px rgba(0,0,0,.08); }
        .stat-card { border:none; border-radius:12px; transition:.2s; }
        .stat-card:hover { transform:translateY(-2px); }
        .table th { font-weight:600; font-size:.85rem; color:#666; }
        .btn-danger { background:#e74c3c; border-color:#e74c3c; }
        .btn-danger:hover { background:#c0392b; border-color:#c0392b; }
    </style>
</head>
<body>
HTML;

    $nav = [
        ['index.php',       'bi-speedometer2', 'Dashboard'],
        ['products.php',    'bi-box-seam',      'Sản phẩm'],
        ['categories.php',  'bi-grid',          'Danh mục'],
        ['imports.php',     'bi-truck',         'Nhập hàng'],
        ['orders.php',      'bi-bag-check',     'Đơn hàng'],
        ['users.php',       'bi-people',        'Người dùng'],
        ['inventory.php',   'bi-bar-chart',     'Tồn kho & Báo cáo'],
    ];

    echo '<div class="sidebar">';
    echo '<div class="brand"><h5 class="text-white mb-0"><i class="bi bi-lightning-fill text-danger"></i> SneakerShop</h5><small class="text-secondary">Admin Panel</small></div>';
    echo '<nav class="nav flex-column mt-2">';
    foreach ($nav as $item) {
        $isActive = strpos($active, $item[0]) !== false || basename($_SERVER['PHP_SELF']) === $item[0] ? 'active' : '';
        echo "<a class='nav-link $isActive' href='{$item[0]}'><i class='bi {$item[1]} me-2'></i>{$item[2]}</a>";
    }
    echo '</nav>';
    echo '<div class="p-3 mt-4 border-top" style="border-color:rgba(255,255,255,.1)!important">';
    echo '<a href="../index.php" class="nav-link" style="color:rgba(255,255,255,.5);padding:5px 0"><i class="bi bi-house me-2"></i>Xem website</a>';
    echo '<a href="login.php?action=logout" class="nav-link" style="color:rgba(255,255,255,.5);padding:5px 0"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</a>';
    echo '</div>';
    echo '</div>';

    echo '<div class="main-content">';
    echo '<div class="topbar d-flex justify-content-between align-items-center">';
    echo "<h5 class='mb-0 fw-bold'>$title</h5>";
    echo '<span class="text-muted small"><i class="bi bi-person-circle me-1"></i>' . htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']) . '</span>';
    echo '</div>';
}

function adminFooter() {
    echo '</div>';
    echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>';
    echo '</body></html>';
}
?>
