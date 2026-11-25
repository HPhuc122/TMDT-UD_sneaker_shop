<?php
declare(strict_types=1);

namespace Tests\Part2_GioHang_KhuyenMai;

use PHPUnit\Framework\TestCase;

/**
 * Phần 2 - Giỏ hàng và khuyến mãi
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: includes/db.php: discountDateTimestamp(), isDiscountExpired(), isDiscountCurrentlyValid()
 * Loại test: Unit: hàm thuần, không ghi dữ liệu
 * Các hàm kiểm tra thời hạn và lượt dùng của mã giảm giá. Toàn bộ lớp này là MẪU đã viết sẵn (có
 * Data Provider theo giá trị biên), chạy được ngay.
 *
 * @group part2
 * @group unit
 */
final class DiscountHelpersTest extends TestCase
{
    /**
     * @testdox discountDateTimestamp() trả về null với giá trị rỗng/không phải ngày, trả về số nguyên với ngày hợp lệ
     * @dataProvider timestampProvider
     */
    public function testDiscountDateTimestamp(mixed $input, bool $expectNull): void
    {
        $result = discountDateTimestamp($input);
        $expectNull ? $this->assertNull($result) : $this->assertIsInt($result);
    }

    /**
     * @testdox isDiscountExpired() chỉ true khi thời điểm hiện tại SAU ngày kết thúc (biên đúng giờ kết thúc là chưa hết hạn)
     * @dataProvider expiredProvider
     */
    public function testIsDiscountExpiredAroundEndDate(int $now, ?string $endDate, bool $expected): void
    {
        $this->assertSame($expected, isDiscountExpired(['end_date' => $endDate], $now));
    }

    /**
     * @testdox isDiscountCurrentlyValid() kiểm tra ngày bắt đầu, ngày kết thúc và số lượt dùng (biên total_used so với max_uses)
     * @dataProvider validityProvider
     */
    public function testIsDiscountCurrentlyValid(array $discount, int $now, bool $expected): void
    {
        $this->assertSame($expected, isDiscountCurrentlyValid($discount, $now));
    }

    public static function timestampProvider(): array
    {
        return [
            'null' => [null, true],
            'chuỗi rỗng' => ['', true],
            'không phải ngày' => ['abc', true],
            'ngày hợp lệ' => ['2026-12-31 23:59:59', false],
        ];
    }

    public static function expiredProvider(): array
    {
        return [
            'trước hạn 1 giây' => [strtotime('2025-12-31 23:59:59'), '2026-01-01 00:00:00', false],
            'đúng thời điểm hết hạn' => [strtotime('2026-01-01 00:00:00'), '2026-01-01 00:00:00', false],
            'sau hạn 1 giây' => [strtotime('2026-01-01 00:00:01'), '2026-01-01 00:00:00', true],
            'không có ngày kết thúc' => [strtotime('2030-01-01 00:00:00'), null, false],
        ];
    }

    public static function validityProvider(): array
    {
        return [
            'trước ngày bắt đầu 1 giây' => [['start_date' => '2026-06-01 00:00:00', 'end_date' => '2026-06-30 23:59:59'], strtotime('2026-05-31 23:59:59'), false],
            'đúng ngày bắt đầu' => [['start_date' => '2026-06-01 00:00:00', 'end_date' => '2026-06-30 23:59:59'], strtotime('2026-06-01 00:00:00'), true],
            'đúng giờ kết thúc' => [['start_date' => '2026-06-01 00:00:00', 'end_date' => '2026-06-30 23:59:59'], strtotime('2026-06-30 23:59:59'), true],
            'sau giờ kết thúc 1 giây' => [['start_date' => '2026-06-01 00:00:00', 'end_date' => '2026-06-30 23:59:59'], strtotime('2026-07-01 00:00:00'), false],
            'còn đúng 1 lượt (total_used = max_uses - 1)' => [['max_uses' => 10, 'total_used' => 9], strtotime('2026-06-15 12:00:00'), true],
            'hết lượt (total_used = max_uses)' => [['max_uses' => 10, 'total_used' => 10], strtotime('2026-06-15 12:00:00'), false],
            'không giới hạn lượt (max_uses = null)' => [['max_uses' => null, 'total_used' => 999], strtotime('2026-06-15 12:00:00'), true],
        ];
    }
}
