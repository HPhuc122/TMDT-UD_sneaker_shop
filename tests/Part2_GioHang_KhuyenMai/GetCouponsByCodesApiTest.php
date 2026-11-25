<?php
declare(strict_types=1);

namespace Tests\Part2_GioHang_KhuyenMai;

use Tests\Support\HttpTestCase;

/**
 * Phần 2 - Giỏ hàng và khuyến mãi
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: api/get_coupons_by_codes.php
 * Loại test: HTTP: gọi trang/API như trình duyệt (cần composer serve)
 * Tra cứu thông tin mã theo danh sách mã gửi lên. Gọi trang qua HttpTestCase (loginAs(), post(),
 * get()) rồi kiểm tra mã trạng thái, nội dung trang, chuyển hướng và dữ liệu trong CSDL test.
 *
 * @group part2
 * @group http
 */
final class GetCouponsByCodesApiTest extends HttpTestCase
{
    /**
     * @testdox Trả về thông tin các mã có thật
     */
    public function testReturnsKnownCodes(): void
    {
        $this->markTestIncomplete('TODO: Trả về thông tin các mã có thật');
    }

    /**
     * @testdox Bỏ qua mã không tồn tại
     */
    public function testIgnoresUnknownCodes(): void
    {
        $this->markTestIncomplete('TODO: Bỏ qua mã không tồn tại');
    }

    /**
     * @testdox Danh sách mã rỗng trả về rỗng
     */
    public function testEmptyCodeListReturnsEmptyResult(): void
    {
        $this->markTestIncomplete('TODO: Danh sách mã rỗng trả về rỗng');
    }
}
