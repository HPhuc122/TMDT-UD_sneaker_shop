<?php
declare(strict_types=1);

namespace Tests\Support;

use mysqli;
use RuntimeException;

/**
 * Đưa CSDL test về đúng dữ liệu mẫu (database/sneaker_shop.sql): xóa mọi bảng rồi nạp lại.
 * Dùng cho test HTTP (không rollback được). Từ chối chạy nếu DB_NAME không kết thúc bằng _test.
 */
final class DatabaseSeeder
{
    public static function reload(): void
    {
        $dbName = (string) getenv('DB_NAME');
        if (!str_ends_with($dbName, '_test')) {
            throw new RuntimeException("DatabaseSeeder chỉ chạy trên CSDL *_test (hiện là '$dbName').");
        }

        $db = new mysqli(getenv('DB_HOST') ?: 'localhost', getenv('DB_USER') ?: 'root', getenv('DB_PASS') ?: '', $dbName);
        $db->set_charset('utf8mb4');
        $db->query('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($db->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetch_all(MYSQLI_NUM) as [$table]) {
            $db->query("DROP TABLE `$table`");
        }

        $sql = (string) file_get_contents(PROJECT_ROOT . '/database/sneaker_shop.sql');
        if ($db->multi_query($sql)) {
            do {
                if ($result = $db->store_result()) {
                    $result->free();
                }
            } while ($db->more_results() && $db->next_result());
        }
        if ($db->errno) {
            throw new RuntimeException('Nạp dữ liệu mẫu thất bại: ' . $db->error);
        }
        $db->query('SET FOREIGN_KEY_CHECKS = 1');
        $db->close();
    }
}
