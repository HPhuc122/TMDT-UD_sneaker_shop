<?php
declare(strict_types=1);

namespace Tests\Part2_GioHang_KhuyenMai;

use Tests\Support\DatabaseTestCase;

/**
 * Phần 2 - Giỏ hàng và khuyến mãi
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: includes/db.php: saveCartToDB(), loadCartFromDB()
 * Loại test: Integration: hàm có dùng CSDL test (tự rollback)
 * Lưu giỏ hàng vào bảng cart_items và nạp lại (đặc tả: giỏ hàng lưu trong DB).
 *
 * @group part2
 * @group integration
 */
final class CartPersistenceTest extends DatabaseTestCase
{
    /**
     * @testdox Lưu rồi nạp lại giỏ hàng cho ra đúng các sản phẩm, màu, size, số lượng
     */
    public function testSaveCartThenLoadReturnsSameItems(): void
    {
        $this->markTestIncomplete('TODO: Lưu rồi nạp lại giỏ hàng cho ra đúng các sản phẩm, màu, size, số lượng');
    }

    /**
     * @testdox Lưu giỏ mới thay thế nội dung giỏ cũ
     */
    public function testSaveCartReplacesPreviousContent(): void
    {
        $this->markTestIncomplete('TODO: Lưu giỏ mới thay thế nội dung giỏ cũ');
    }

    /**
     * @testdox Lưu giỏ rỗng xóa giỏ đã lưu
     */
    public function testSaveEmptyCartClearsStoredCart(): void
    {
        $this->markTestIncomplete('TODO: Lưu giỏ rỗng xóa giỏ đã lưu');
    }

    /**
     * @testdox Nạp giỏ hàng gộp đúng với giỏ đang có trong phiên
     */
    public function testLoadCartMergesWithSessionCart(): void
    {
        $this->markTestIncomplete('TODO: Nạp giỏ hàng gộp đúng với giỏ đang có trong phiên');
    }

    /**
     * @testdox Bỏ qua biến thể không còn tồn tại
     */
    public function testLoadCartIgnoresVariantsThatNoLongerExist(): void
    {
        $this->markTestIncomplete('TODO: Bỏ qua biến thể không còn tồn tại');
    }

    /**
     * @testdox Giỏ hàng của hai tài khoản không lẫn vào nhau
     */
    public function testCartsOfDifferentUsersAreIsolated(): void
    {
        $this->markTestIncomplete('TODO: Giỏ hàng của hai tài khoản không lẫn vào nhau');
    }
}
