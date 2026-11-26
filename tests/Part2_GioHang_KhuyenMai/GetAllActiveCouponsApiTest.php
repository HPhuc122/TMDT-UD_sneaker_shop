<?php
declare(strict_types=1);

namespace Tests\Part2_GioHang_KhuyenMai;

use Tests\Support\HttpTestCase;

/**
 * Phần 2 - Giỏ hàng và khuyến mãi
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: api/get_all_active_coupons.php
 * Loại test: HTTP: gọi trang/API như trình duyệt (cần composer serve)
 * Kho voucher công khai. Gọi trang qua HttpTestCase (loginAs(), post(), get()) rồi kiểm tra mã
 * trạng thái, nội dung trang, chuyển hướng và dữ liệu trong CSDL test.
 *
 * @group part2
 * @group http
 */
final class GetAllActiveCouponsApiTest extends HttpTestCase
{
    /**
     * @testdox Chỉ trả về mã đang hoạt động, còn hạn, còn lượt
     */
    public function testReturnsOnlyActiveAndValidCoupons(): void
    {
        $this->markTestIncomplete('TODO: Chỉ trả về mã đang hoạt động, còn hạn, còn lượt');
    }

    /**
     * @testdox Đánh dấu mã người dùng đã lưu
     */
    public function testMarksCouponsAlreadySavedByUser(): void
    {
        $this->markTestIncomplete('TODO: Đánh dấu mã người dùng đã lưu');
    }

    /**
     * @testdox Không hiển thị mã hết hạn hoặc hết lượt
     */
    public function testHidesUsedUpAndExpiredCoupons(): void
    {
        // Gợi ý: HETHAN, HETLUOT không xuất hiện.
        $this->markTestIncomplete('TODO: Không hiển thị mã hết hạn hoặc hết lượt');
    }
}
