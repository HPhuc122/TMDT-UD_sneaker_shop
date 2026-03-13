<?php
// includes/db.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'Quocan@529529');
define('DB_NAME', 'sneaker_shop');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Helper functions
function sanitize($conn, $str) {
    return $conn->real_escape_string(trim($str));
}

function getSellPrice($import_price, $profit_rate) {
    return $import_price * (1 + $profit_rate / 100);
}

function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' ₫';
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function generateCode($prefix) {
    return $prefix . date('YmdHis') . rand(100, 999);
}
?>
