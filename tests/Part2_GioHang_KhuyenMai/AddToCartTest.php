<?php
declare(strict_types=1);

namespace Tests\Part2_GioHang_KhuyenMai;

use Tests\Support\HttpTestCase;

/**
 * Phần 2 - Giỏ hàng và khuyến mãi
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: product.php (xử lý action thêm vào giỏ hàng)
 * Loại test: HTTP: gọi trang/API như trình duyệt (cần composer serve)
 * Thêm sản phẩm vào giỏ từ trang chi tiết. Gọi trang qua HttpTestCase (loginAs(), post(), get())
 * rồi kiểm tra mã trạng thái, nội dung trang, chuyển hướng và dữ liệu trong CSDL test.
 *
 * @group part2
 * @group http
 */
final class AddToCartTest extends HttpTestCase
{
    /**
     * @testdox Khách chưa đăng nhập bấm Thêm vào giỏ bị chuyển sang trang đăng nhập
     */
    public function testGuestIsRedirectedToLoginWhenAddingToCart(): void
    {
        $this->markTestIncomplete('TODO: Khách chưa đăng nhập bấm Thêm vào giỏ bị chuyển sang trang đăng nhập');
    }

    /**
     * @testdox Chưa chọn size thì báo chọn size, giỏ không đổi
     */
    public function testAddToCartRequiresSize(): void
    {
        // Gợi ý: Mong đợi: 'Vui lòng chọn size trước khi thêm vào giỏ hàng.'
        $this->markTestIncomplete('TODO: Chưa chọn size thì báo chọn size, giỏ không đổi');
    }

    /**
     * @testdox Chưa chọn màu thì không thêm được
     */
    public function testAddToCartRequiresColor(): void
    {
        $this->markTestIncomplete('TODO: Chưa chọn màu thì không thêm được');
    }

    /**
     * @testdox Biến thể (màu, size) không tồn tại bị từ chối
     */
    public function testAddToCartWithNonexistentVariantRejected(): void
    {
        $this->markTestIncomplete('TODO: Biến thể (màu, size) không tồn tại bị từ chối');
    }

    /**
     * @testdox Thêm lại cùng biến thể thì cộng dồn số lượng
     */
    public function testAddSameVariantTwiceAccumulatesQuantity(): void
    {
        $this->markTestIncomplete('TODO: Thêm lại cùng biến thể thì cộng dồn số lượng');
    }

    /**
     * @testdox Không thêm vượt quá tồn kho
     */
    public function testAddToCartCannotExceedStock(): void
    {
        $this->markTestIncomplete('TODO: Không thêm vượt quá tồn kho');
    }

    /**
     * @testdox Biến thể hết hàng không thêm được
     */
    public function testAddToCartWithZeroStockRejected(): void
    {
        $this->markTestIncomplete('TODO: Biến thể hết hàng không thêm được');
    }
}
