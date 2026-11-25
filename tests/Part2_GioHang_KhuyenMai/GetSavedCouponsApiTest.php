<?php
declare(strict_types=1);

namespace Tests\Part2_GioHang_KhuyenMai;

use Tests\Support\HttpTestCase;

/**
 * Phần 2 - Giỏ hàng và khuyến mãi
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: api/get_saved_coupons.php
 * Loại test: HTTP: gọi trang/API như trình duyệt (cần composer serve)
 * Danh sách mã trong ví của người dùng. Gọi trang qua HttpTestCase (loginAs(), post(), get()) rồi
 * kiểm tra mã trạng thái, nội dung trang, chuyển hướng và dữ liệu trong CSDL test.
 *
 * @group part2
 * @group http
 */
final class GetSavedCouponsApiTest extends HttpTestCase
{
    /**
     * @testdox Chưa đăng nhập thì báo cần đăng nhập
     */
    public function testRequiresLogin(): void
    {
        $this->markTestIncomplete('TODO: Chưa đăng nhập thì báo cần đăng nhập');
    }

    /**
     * @testdox Chỉ trả về mã của chính người dùng
     */
    public function testReturnsOnlyCouponsSavedByCurrentUser(): void
    {
        $this->markTestIncomplete('TODO: Chỉ trả về mã của chính người dùng');
    }

    /**
     * @testdox Người dùng chưa lưu mã nào nhận danh sách rỗng
     */
    public function testReturnsEmptyListForUserWithoutSavedCoupons(): void
    {
        $this->markTestIncomplete('TODO: Người dùng chưa lưu mã nào nhận danh sách rỗng');
    }
}
