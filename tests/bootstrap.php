<?php
declare(strict_types=1);

/**
 * Chạy MỘT LẦN trước tất cả test (≈ @BeforeAll toàn cục).
 *  1) nạp thư viện của composer
 *  2) cố định CSDL test và chặn nhầm sang CSDL thật
 *  3) nạp includes/db.php để các hàm dùng chung (getSellPrice, isAdmin, ...) và biến $conn có sẵn
 */

define('PROJECT_ROOT', dirname(__DIR__));

$autoload = PROJECT_ROOT . '/vendor/autoload.php';
if (!is_file($autoload)) {
    fwrite(STDERR, "Chưa cài thư viện. Hãy chạy: composer install\n");
    exit(1);
}
require $autoload;

foreach (['DB_HOST' => 'localhost', 'DB_USER' => 'root', 'DB_PASS' => '', 'DB_NAME' => 'sneaker_shop_test'] as $key => $default) {
    if (getenv($key) === false) {
        putenv("$key=$default");
        $_ENV[$key] = $default;
    }
}

if (!str_ends_with((string) getenv('DB_NAME'), '_test')) {
    fwrite(STDERR, "DỪNG: DB_NAME phải kết thúc bằng _test (hiện là '" . getenv('DB_NAME') . "').\n"
        . "Test có thể xóa/ghi dữ liệu, không được chạy trên database thật.\n");
    exit(1);
}

// CLI: không gửi header cookie phiên để tránh cảnh báo "headers already sent".
ini_set('session.use_cookies', '0');
ini_set('session.use_only_cookies', '0');
ini_set('session.cache_limiter', '');

try {
    require_once PROJECT_ROOT . '/includes/db.php';   // phạm vi toàn cục: tạo $conn và các hàm dùng chung
} catch (Throwable $e) {
    fwrite(STDERR, "Không kết nối được CSDL test '" . getenv('DB_NAME') . "': " . $e->getMessage() . "\n"
        . "Hãy tạo database và nạp dữ liệu mẫu:\n"
        . "  mysql -u root -e \"CREATE DATABASE sneaker_shop_test CHARACTER SET utf8mb4\"\n"
        . "  mysql -u root sneaker_shop_test < database/sneaker_shop.sql\n");
    exit(1);
}
