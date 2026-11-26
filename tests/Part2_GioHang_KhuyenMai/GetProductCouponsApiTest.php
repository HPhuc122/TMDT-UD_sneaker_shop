<?php
declare(strict_types=1);

namespace Tests\Part2_GioHang_KhuyenMai;

use Tests\Support\HttpTestCase;

/**
 * Phần 2 - Giỏ hàng và khuyến mãi
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: api/get_product_coupons.php
 * Loại test: HTTP: gọi trang/API như trình duyệt (cần composer serve)
 * Mã giảm giá áp dụng được cho một sản phẩm. Gọi trang qua HttpTestCase (loginAs(), post(), get())
 * rồi kiểm tra mã trạng thái, nội dung trang, chuyển hướng và dữ liệu trong CSDL test.
 *
 * @group part2
 * @group http
 */
final class GetProductCouponsApiTest extends HttpTestCase
{
    /**
     * @testdox Trả về mã áp cho toàn bộ sản phẩm
     */
    public function testReturnsCouponsApplyingToAllProducts(): void
    {
        $this->markTestIncomplete('TODO: Trả về mã áp cho toàn bộ sản phẩm');
    }

    /**
     * @testdox Trả về mã giới hạn theo danh mục của sản phẩm
     */
    public function testReturnsCouponsScopedToProductCategory(): void
    {
        $this->markTestIncomplete('TODO: Trả về mã giới hạn theo danh mục của sản phẩm');
    }

    /**
     * @testdox Trả về mã giới hạn riêng cho sản phẩm
     */
    public function testReturnsCouponsScopedToThatProduct(): void
    {
        $this->markTestIncomplete('TODO: Trả về mã giới hạn riêng cho sản phẩm');
    }

    /**
     * @testdox Mã sản phẩm sai không gây lỗi hệ thống
     */
    public function testInvalidProductIdReturnsEmptyOrError(): void
    {
        $this->markTestIncomplete('TODO: Mã sản phẩm sai không gây lỗi hệ thống');
    }
}
