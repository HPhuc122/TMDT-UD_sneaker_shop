<?php
declare(strict_types=1);

namespace Tests\Part2_GioHang_KhuyenMai;

use PHPUnit\Framework\TestCase;

/**
 * @group part2
 * @group unit
 */
final class CartTotalTest extends TestCase
{
    /** @dataProvider cartProvider */
    public function testCartTotal(array $cart, float $expected): void
    {
        $this->assertEqualsWithDelta($expected, calculateCartTotal($cart), 0.000001);
    }

    public static function cartProvider(): array
    {
        return [
            'giỏ rỗng' => [[], 0],
            'một sản phẩm' => [[['price' => 2700000, 'qty' => 1]], 2700000],
            'nhiều sản phẩm và số lượng' => [[['price' => 2700000, 'qty' => 2], ['price' => 3036000, 'qty' => 1]], 8436000],
            'số dạng chuỗi từ DB' => [[['price' => '100000.50', 'qty' => '2']], 200001],
            'giá bằng 0' => [[['price' => 0, 'qty' => 3]], 0],
            'số lượng bằng 0' => [[['price' => 100000, 'qty' => 0]], 0],
            'giá có phần thập phân' => [[['price' => 19.95, 'qty' => 3]], 59.85],
            'chỉ số giỏ không liên tục' => [[2 => ['price' => 100, 'qty' => 2], 5 => ['price' => 50, 'qty' => 3]], 350],
        ];
    }
}
