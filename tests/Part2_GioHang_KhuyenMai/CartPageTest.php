<?php
declare(strict_types=1);

namespace Tests\Part2_GioHang_KhuyenMai;

use Tests\Support\HttpTestCase;

/**
 * Phần 2 - Giỏ hàng và khuyến mãi
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: cart.php (FR-06)
 * Loại test: HTTP: gọi trang/API như trình duyệt (cần composer serve)
 * Trang giỏ hàng. Gọi trang qua HttpTestCase (loginAs(), post(), get()) rồi kiểm tra mã trạng
 * thái, nội dung trang, chuyển hướng và dữ liệu trong CSDL test.
 *
 * @group part2
 * @group http
 */
final class CartPageTest extends HttpTestCase
{
    /**
     * @testdox Chưa đăng nhập mở cart.php bị chuyển về login.php?redirect=cart.php
     */
    public function testCartRequiresLogin(): void
    {
        $this->markTestIncomplete('TODO: Chưa đăng nhập mở cart.php bị chuyển về login.php?redirect=cart.php');
    }

    /**
     * @testdox Giỏ trống hiện thông báo giỏ hàng trống
     */
    public function testEmptyCartShowsEmptyMessage(): void
    {
        $this->markTestIncomplete('TODO: Giỏ trống hiện thông báo giỏ hàng trống');
    }

    /**
     * @testdox Cập nhật số lượng theo biên 0, 1, bằng tồn kho, vượt tồn kho 1
     * @dataProvider quantityProvider
     */
    public function testUpdateQuantityBoundaries(int $quantity, int $expectedSaved): void
    {
        // Gợi ý: Tồn kho 8: 0 bị chặn (số lượng phải từ 1); 1 và 8 chấp nhận; 9 tự điều chỉnh về 8.
        $this->markTestIncomplete('TODO: Cập nhật số lượng theo biên 0, 1, bằng tồn kho, vượt tồn kho 1');
    }

    /**
     * @testdox Xóa một sản phẩm khỏi giỏ và tổng tiền được tính lại
     */
    public function testRemoveItemFromCart(): void
    {
        $this->markTestIncomplete('TODO: Xóa một sản phẩm khỏi giỏ và tổng tiền được tính lại');
    }

    /**
     * @testdox Tổng tiền giỏ hàng bằng tổng thành tiền các dòng
     */
    public function testCartTotalIsSumOfLineTotals(): void
    {
        // Gợi ý: Nike Air Max 270 x1 (2.700.000) + Nike Air Max 97 x1 (3.036.000) = 5.736.000.
        $this->markTestIncomplete('TODO: Tổng tiền giỏ hàng bằng tổng thành tiền các dòng');
    }

    /**
     * @testdox Mỗi thay đổi giỏ hàng được lưu vào cart_items
     */
    public function testCartIsSavedToDatabaseAfterChange(): void
    {
        $this->markTestIncomplete('TODO: Mỗi thay đổi giỏ hàng được lưu vào cart_items');
    }

    public static function quantityProvider(): array
    {
        return [
            '0 (dưới biên)' => [0, 1],
            '1 (đúng biên dưới)' => [1, 1],
            '8 (bằng tồn kho)' => [8, 8],
            '9 (vượt tồn kho)' => [9, 8],
        ];
    }
}
