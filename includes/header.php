<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';

// Get categories for nav
$cats = $conn->query("SELECT * FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' : '' ?>SneakerShop</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .navbar-brand { font-weight: 800; font-size: 1.5rem; color: #ff6b35 !important; }
        .product-card { transition: transform .2s, box-shadow .2s; border: none; border-radius: 12px; overflow: hidden; }
        .product-card:hover { transform: translateY(-4px); box-shadow: 0 8px 25px rgba(0,0,0,0.12); }
        .product-img { height: 220px; object-fit: cover; background: #f8f9fa; }
        .badge-category { background: #ff6b35; }
        .btn-primary { background: #ff6b35; border-color: #ff6b35; }
        .btn-primary:hover { background: #e55a24; border-color: #e55a24; }
        .price-tag { color: #ff6b35; font-weight: 700; font-size: 1.1rem; }
        .cart-badge { position: absolute; top: -6px; right: -8px; background: #ff6b35; font-size: 0.65rem; }
        footer { background: #1a1a2e; color: #ccc; }
        .section-title { border-left: 4px solid #ff6b35; padding-left: 12px; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand" href="/sneaker_shop/index.php">
            <i class="bi bi-lightning-fill"></i> SneakerShop
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/sneaker_shop/index.php">Trang chủ</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Danh mục</a>
                    <ul class="dropdown-menu">
                        <?php while ($cat = $cats->fetch_assoc()): ?>
                        <li><a class="dropdown-item" href="/sneaker_shop/category.php?id=<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></a></li>
                        <?php endwhile; ?>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/sneaker_shop/search.php">Tìm kiếm</a>
                </li>
            </ul>
            <form class="d-flex me-3" action="/sneaker_shop/search.php" method="GET">
                <input class="form-control form-control-sm me-2" type="search" name="q" placeholder="Tìm sneaker..." style="width:200px">
                <button class="btn btn-sm btn-primary" type="submit"><i class="bi bi-search"></i></button>
            </form>
            <ul class="navbar-nav">
                <?php if (isLoggedIn()): ?>
                <li class="nav-item position-relative me-2">
                    <a class="nav-link" href="/sneaker_shop/cart.php">
                        <i class="bi bi-cart3 fs-5"></i>
                        <?php
                        $cartCount = 0;
                        if (isset($_SESSION['cart'])) $cartCount = array_sum(array_column($_SESSION['cart'], 'qty'));
                        if ($cartCount > 0): ?>
                        <span class="badge rounded-pill cart-badge"><?= $cartCount ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/sneaker_shop/my_orders.php"><i class="bi bi-bag-check"></i> Đơn hàng của tôi</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="/sneaker_shop/login.php?action=logout"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a></li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="/sneaker_shop/login.php"><i class="bi bi-box-arrow-in-right"></i> Đăng nhập</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-primary btn-sm ms-2" href="/sneaker_shop/register.php">Đăng ký</a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
