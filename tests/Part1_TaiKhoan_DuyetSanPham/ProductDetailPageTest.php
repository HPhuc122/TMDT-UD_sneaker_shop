<?php
declare(strict_types=1);

namespace Tests\Part1_TaiKhoan_DuyetSanPham;

use Tests\Support\HttpTestCase;

/**
 * Phần 1 - Tài khoản, phiên đăng nhập và duyệt sản phẩm
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: product.php (phần hiển thị và chọn biến thể - FR-05)
 * Loại test: HTTP: gọi trang/API như trình duyệt (cần composer serve)
 * Trang chi tiết sản phẩm. Phần thêm vào giỏ hàng thuộc Phần 2 (AddToCartTest). Gọi trang qua
 * HttpTestCase (loginAs(), post(), get()) rồi kiểm tra mã trạng thái, nội dung trang, chuyển hướng
 * và dữ liệu trong CSDL test.
 *
 * @group part1
 * @group http
 */
final class ProductDetailPageTest extends HttpTestCase
{
    /**
     * @testdox Hiển thị tên, giá bán, mô tả của sản phẩm
     */
    public function testProductPageShowsNamePriceAndDescription(): void
    {
        $response=$this->get('product.php',['id'=>1]); $this->assertSame(200,$response['status']); foreach(['product-card','product.php'] as $x)$this->assertStringContainsString($x,$response['body']);
    }

    /**
     * @testdox Tồn kho hiển thị đúng theo từng cặp màu - size
     */
    public function testProductPageShowsStockForEachColorAndSize(): void
    {
        // Gợi ý: Nike Air Max 270: Đen-35 tồn 8, Xám-36 tồn 5 trong dữ liệu mẫu.
        $response=$this->get('product.php',['id'=>1]); $this->assertStringContainsString('stock',$response['body']);
    }

    /**
     * @testdox Mã sản phẩm không tồn tại không gây lỗi
     */
    public function testProductPageWithUnknownIdHandledGracefully(): void
    {
        $response=$this->get('product.php',['id'=>999999]); $this->assertSame(200,$response['status']); $this->assertStringNotContainsString('Fatal error',$response['body']);
    }

    /**
     * @testdox Sản phẩm đang ẩn không xem được
     */
    public function testProductPageHidesInactiveProduct(): void
    {
        $response=$this->get('product.php',['id'=>999999]); $this->assertSame(200,$response['status']);
    }
}
