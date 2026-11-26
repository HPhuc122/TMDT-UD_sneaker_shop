<?php
declare(strict_types=1);

namespace Tests\Part2_GioHang_KhuyenMai;

use PHPUnit\Framework\TestCase;

/**
 * @group part2
 * @group unit
 */
final class DiscountAmountTest extends TestCase
{
    /** @dataProvider amountProvider */
    public function testDiscountAmount(string $type, $value, $cap, $total, float $expected): void
    {
        $discount = ['discount_type' => $type, 'discount_value' => $value, 'max_discount_amount' => $cap];
        $actual = calculateDiscountAmount($discount, $total);
        $this->assertEqualsWithDelta($expected, $actual, 0.000001);
    }

    public static function amountProvider(): array
    {
        return [
            'cố định dưới tổng giỏ' => ['fixed', 50000, null, 2700000, 50000],
            'cố định bằng tổng giỏ' => ['fixed', 50000, null, 50000, 50000],
            'cố định vượt tổng giỏ 1 đồng' => ['fixed', 50001, null, 50000, 50000],
            'cố định chỉ dùng giới hạn tổng giỏ' => ['fixed', 50000, 10000, 2700000, 50000],
            'phần trăm không giới hạn' => ['percentage', 10, null, 2700000, 270000],
            'dưới mức giảm tối đa 1 đồng' => ['percentage', 10, 100000, 999990, 99999],
            'bằng mức giảm tối đa' => ['percentage', 10, 100000, 1000000, 100000],
            'vượt mức giảm tối đa 1 đồng' => ['percentage', 10, 100000, 1000010, 100000],
            'giảm 100 phần trăm' => ['percentage', 100, null, 2700000, 2700000],
            'giá trị thập phân' => ['percentage', 12.5, null, 199, 24.875],
            'dữ liệu chuỗi từ DB' => ['percentage', '10.00', '50000.00', 1000000, 50000],
            'giỏ rỗng với mã cố định' => ['fixed', 50000, null, 0, 0],
            'giỏ rỗng với mã phần trăm' => ['percentage', 10, null, 0, 0],
        ];
    }

    /** @dataProvider invalidAmountProvider */
    public function testInvalidInputIsRejected(array $discount, $total): void
    {
        $this->expectException(\InvalidArgumentException::class);
        calculateDiscountAmount($discount, $total);
    }

    public static function invalidAmountProvider(): array
    {
        $valid = ['discount_type' => 'percentage', 'discount_value' => 10, 'max_discount_amount' => null];
        return [
            'mức giảm tối đa bằng 0' => [array_replace($valid, ['max_discount_amount' => 0]), 1000000],
            'tiền giảm bằng 0' => [array_replace($valid, ['discount_type' => 'fixed', 'discount_value' => 0]), 100000],
            'phần trăm vượt 100' => [array_replace($valid, ['discount_value' => 101]), 100000],
            'loại mã sai' => [array_replace($valid, ['discount_type' => 'other']), 100000],
            'giá trị giảm âm' => [array_replace($valid, ['discount_value' => -1]), 100000],
            'ngày sai' => [array_replace($valid, ['end_date' => '2026-02-30']), 100000],
            'tổng giỏ âm' => [$valid, -1],
            'tổng giỏ không phải số' => [$valid, 'abc'],
            'tổng giỏ vô hạn' => [$valid, INF],
            'tổng giỏ NaN' => [$valid, NAN],
        ];
    }
}
