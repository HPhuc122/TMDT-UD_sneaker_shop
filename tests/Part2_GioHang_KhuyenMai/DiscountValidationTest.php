<?php
declare(strict_types=1);
namespace Tests\Part2_GioHang_KhuyenMai;
use PHPUnit\Framework\TestCase;

/**
 * @group part2
 * @group unit
 */
final class DiscountValidationTest extends TestCase
{
    /** @dataProvider validationProvider */
    public function testValidationReportsFields(array $changes, array $expectedFields): void
    {
        $discount = array_replace([
            'discount_type' => 'fixed', 'discount_value' => '50000.00',
            'max_discount_amount' => null, 'start_date' => null, 'end_date' => '',
        ], $changes);
        $this->assertSame($expectedFields, array_keys(validateDiscountData($discount)));
    }

    public static function validationProvider(): array
    {
        return [
            'hợp lệ và bỏ trống ngày' => [[], []],
            'phần trăm nhỏ hơn 1' => [['discount_type' => 'percentage', 'discount_value' => '0.5'], []],
            'phần trăm 100' => [['discount_type' => 'percentage', 'discount_value' => 100], []],
            'phần trăm 101' => [['discount_type' => 'percentage', 'discount_value' => 101], ['discount_value']],
            'loại sai' => [['discount_type' => 'other'], ['discount_type']],
            'giá trị 0' => [['discount_value' => '0'], ['discount_value']],
            'giá trị âm' => [['discount_value' => -1], ['discount_value']],
            'giá trị rỗng' => [['discount_value' => ''], ['discount_value']],
            'giá trị không phải số' => [['discount_value' => '10abc'], ['discount_value']],
            'giá trị vô hạn' => [['discount_value' => INF], ['discount_value']],
            'giá trị NaN' => [['discount_value' => NAN], ['discount_value']],
            'giá trị mảng' => [['discount_value' => []], ['discount_value']],
            'cap dương dạng chuỗi' => [['max_discount_amount' => '0.01'], []],
            'cap 0' => [['max_discount_amount' => '0'], ['max_discount_amount']],
            'cap âm' => [['max_discount_amount' => -1], ['max_discount_amount']],
            'cap sai định dạng' => [['max_discount_amount' => 'abc'], ['max_discount_amount']],
            'cap vô hạn' => [['max_discount_amount' => INF], ['max_discount_amount']],
            'ngày bắt đầu sai' => [['start_date' => 'abc'], ['start_date']],
            'ngày kết thúc sai' => [['end_date' => '2026-02-30'], ['end_date']],
            'ngày không bỏ trống bằng 0' => [['start_date' => '0'], ['start_date']],
            'ngày mảng' => [['end_date' => []], ['end_date']],
            'ngày đảo ngược' => [['start_date' => '2026-06-02', 'end_date' => '2026-06-01'], ['end_date']],
            'ngày bằng nhau' => [['start_date' => '2026-06-01T12:00', 'end_date' => '2026-06-01T12:00'], []],
            'định dạng CSDL' => [['start_date' => '2026-06-01 00:00:00', 'end_date' => '2026-06-30 23:59:59'], []],
            'nhiều lỗi theo trường' => [['start_date' => 'abc', 'discount_value' => 0], ['start_date', 'discount_value']],
        ];
    }
}
