<?php
declare(strict_types=1);

namespace Tests\Part1_TaiKhoan_DuyetSanPham;

use Tests\Support\DatabaseTestCase;

/**
 * Phần 1 - Tài khoản, phiên đăng nhập và duyệt sản phẩm
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: includes/db.php: sanitize($conn, $str)
 * Loại test: Integration: hàm có dùng CSDL test (tự rollback)
 * Kiểm tra hàm làm sạch chuỗi trước khi ghép vào câu SQL (chống SQL injection). Hai test đầu là
 * MẪU chạy được ngay.
 *
 * @group part1
 * @group integration
 */
final class SanitizeHelperTest extends DatabaseTestCase
{
    /**
     * @testdox sanitize() cắt khoảng trắng hai đầu
     */
    public function testSanitizeTrimsSurroundingWhitespace(): void
    {
        $this->assertSame('abc', sanitize(self::$conn, "  abc \n"));
    }

    /**
     * @testdox sanitize() giữ nguyên chữ tiếng Việt có dấu
     */
    public function testSanitizeKeepsVietnameseCharacters(): void
    {
        $this->assertSame('Nguyễn Văn A', sanitize(self::$conn, ' Nguyễn Văn A '));
    }

    /**
     * @testdox sanitize() thêm dấu \ trước dấu nháy đơn và nháy kép
     */
    public function testSanitizeEscapesQuotes(): void
    {
        $this->assertSame("O\\'Neil", sanitize(self::$conn, "O'Neil"));
    }

    /**
     * @testdox sanitize() vô hiệu hóa chuỗi tấn công SQL injection
     */
    public function testSanitizeNeutralisesInjectionPayload(): void
    {
        // CLI: php vendor/bin/phpunit --filter testSanitizeNeutralisesInjectionPayload
        $payload = "1' OR '1'='1";
        $actual = sanitize(self::$conn, $payload);
        $this->assertSame("1\\' OR \\'1\\'=\\'1", $actual, 'Kết quả thực tế phải escape mọi dấu nháy đơn.');
        $this->assertStringNotContainsString("' OR '1'='1", $actual,
            'Payload sau khi escape không được còn cấu trúc điều kiện SQL nguyên bản.');
    }
}
