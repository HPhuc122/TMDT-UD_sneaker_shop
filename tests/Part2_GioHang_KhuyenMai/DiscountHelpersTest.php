<?php
declare(strict_types=1);

namespace Tests\Part2_GioHang_KhuyenMai;

use PHPUnit\Framework\TestCase;

/**
 * Phần 2 - Giỏ hàng và khuyến mãi
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: includes/discount_helpers.php: discountDateTimestamp(), isDiscountExpired(), isDiscountCurrentlyValid()
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
    public function testDiscountDateTimestamp(mixed $input, ?int $expected): void
    {
        $this->assertSame($expected, discountDateTimestamp($input));
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
            'null' => [null, null],
            'chuỗi rỗng' => ['', null],
            'không phải ngày' => ['abc', null],
            'ngày không tồn tại' => ['2026-02-30 12:00:00', null],
            'ngày nhuận sai' => ['2026-02-29', null],
            'ngày nhuận đúng' => ['2024-02-29 00:00:00 UTC', 1709164800],
            'giờ không tồn tại' => ['2026-06-01 25:00:00', null],
            'ngày tương đối' => ['tomorrow', null],
            'ký tự null trong ngày' => ["2026-06-01\0", null],
            'dữ liệu mảng' => [[], null],
            'chuỗi số không phải ngày' => ['0', null],
            'định dạng form' => ['2026-01-01T07:00', 1767225600],
            'mốc Unix bằng 0' => ['1970-01-01 00:00:00 UTC', 0],
            'ngày hợp lệ' => ['2026-12-31 23:59:59 +07:00', 1798736399],
        ];
    }

    public static function expiredProvider(): array
    {
        return [
            'trước hạn 1 giây' => [strtotime('2025-12-31 23:59:59'), '2026-01-01 00:00:00', false],
            'đúng thời điểm hết hạn' => [strtotime('2026-01-01 00:00:00'), '2026-01-01 00:00:00', false],
            'sau hạn 1 giây' => [strtotime('2026-01-01 00:00:01'), '2026-01-01 00:00:00', true],
            'ngày kết thúc không hợp lệ' => [1767225600, 'abc', false],
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
            'sau ngày bắt đầu 1 giây' => [['start_date' => '2026-06-01 00:00:00'], strtotime('2026-06-01 00:00:01'), true],
            'trước ngày kết thúc 1 giây' => [['end_date' => '2026-06-30 23:59:59'], strtotime('2026-06-30 23:59:58'), true],
            'còn lượt với giới hạn nhỏ nhất' => [['max_uses' => 1, 'total_used' => 0], 0, true],
            'mã lưu trong CSDL có cap bằng 0' => [['discount_type' => 'percentage', 'discount_value' => '10', 'max_discount_amount' => '0.00'], 0, false],
            'mã lưu trong CSDL có loại sai' => [['discount_type' => 'other', 'discount_value' => '10'], 0, false],
            'mã lưu trong CSDL hợp lệ' => [['discount_type' => 'percentage', 'discount_value' => '10', 'max_discount_amount' => null], 0, true],
            'ngày không tồn tại trong CSDL' => [['end_date' => '2026-02-30'], 0, false],
            'khoảng ngày đảo ngược' => [['start_date' => '2026-06-02', 'end_date' => '2026-06-01'], strtotime('2026-06-01'), false],
            'vượt lượt' => [['max_uses' => 10, 'total_used' => 11], strtotime('2026-06-15 12:00:00'), false],
            'giới hạn bằng 0' => [['max_uses' => 0, 'total_used' => 0], strtotime('2026-06-15 12:00:00'), false],
            'lượt từ DB dạng chuỗi' => [['max_uses' => '10', 'total_used' => '10'], strtotime('2026-06-15 12:00:00'), false],
            'thiếu điều kiện giới hạn' => [[], strtotime('2026-06-15 12:00:00'), true],
            'ngày không hợp lệ bị từ chối' => [['start_date' => 'abc', 'end_date' => 'abc'], strtotime('2026-06-15 12:00:00'), false],
            'thời điểm hiện tại bằng 0' => [['start_date' => '1970-01-01 00:00:01 UTC'], 0, false],
            'đang trong thời hạn nhưng hết lượt' => [['start_date' => '2026-06-01', 'end_date' => '2026-06-30', 'max_uses' => 1, 'total_used' => 1], strtotime('2026-06-15'), false],
            'không giới hạn lượt (max_uses = null)' => [['max_uses' => null, 'total_used' => 999], strtotime('2026-06-15 12:00:00'), true],
        ];
    }
}
