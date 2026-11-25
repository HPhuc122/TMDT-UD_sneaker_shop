<?php
declare(strict_types=1);

namespace Tests\Support;

use mysqli;
use PHPUnit\Framework\TestCase;

/**
 * Lớp nền cho test có dùng CSDL (tương ứng @BeforeAll / @BeforeEach / @AfterEach của JUnit).
 *  - setUpBeforeClass(): lấy kết nối $conn do includes/db.php tạo ở bootstrap.
 *  - setUp():            xóa $_SESSION, mở transaction.
 *  - tearDown():         rollback => dữ liệu của mỗi test không ảnh hưởng test khác.
 *
 * Lưu ý: chỉ dùng cho test gọi hàm PHP trực tiếp. Test qua HTTP chạy ở tiến trình khác
 * nên không rollback được, hãy dùng HttpTestCase (nạp lại dữ liệu mẫu cho mỗi lớp test).
 */
abstract class DatabaseTestCase extends TestCase
{
    protected static mysqli $conn;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$conn = $GLOBALS['conn'];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
        self::$conn->begin_transaction();
    }

    protected function tearDown(): void
    {
        self::$conn->rollback();
        $_SESSION = [];
        parent::tearDown();
    }

    /** Chạy câu SELECT và trả về mọi dòng (mảng kết hợp). */
    protected function fetchAll(string $sql): array
    {
        return self::$conn->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    /** Chạy câu SELECT và trả về giá trị ở dòng đầu, cột đầu (hoặc null). */
    protected function fetchValue(string $sql): mixed
    {
        $row = self::$conn->query($sql)->fetch_row();
        return $row[0] ?? null;
    }
}
