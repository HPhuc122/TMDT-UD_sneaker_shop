<?php
declare(strict_types=1);

namespace Tests\Part2_GioHang_KhuyenMai;

use Tests\Support\HttpTestCase;

/**
 * Phần 2 - Giỏ hàng và khuyến mãi
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: api/check_discount.php (FR-08)
 * Loại test: HTTP: gọi trang/API như trình duyệt (cần composer serve)
 * Kiểm tra và tính tiền giảm của mã. Thứ tự kiểm tra trong code: đăng nhập, mã rỗng, giỏ rỗng, mã
 * tồn tại, chưa bắt đầu, hết hạn, hết lượt, lượt mỗi khách, đơn tối thiểu, phạm vi. Gọi trang qua
 * HttpTestCase (loginAs(), post(), get()) rồi kiểm tra mã trạng thái, nội dung trang, chuyển hướng
 * và dữ liệu trong CSDL test.
 *
 * @group part2
 * @group http
 */
final class CheckDiscountApiTest extends HttpTestCase
{
    /**
     * @testdox Chưa đăng nhập thì báo cần đăng nhập
     */
    public function testRequiresLogin(): void
    {
        $this->markTestIncomplete('TODO: Chưa đăng nhập thì báo cần đăng nhập');
    }

    /**
     * @testdox Mã để trống bị từ chối
     */
    public function testRejectsEmptyCode(): void
    {
        $this->markTestIncomplete('TODO: Mã để trống bị từ chối');
    }

    /**
     * @testdox Giỏ hàng trống bị từ chối
     */
    public function testRejectsWhenCartIsEmpty(): void
    {
        $this->markTestIncomplete('TODO: Giỏ hàng trống bị từ chối');
    }

    /**
     * @testdox Mã không tồn tại bị từ chối
     */
    public function testRejectsUnknownCode(): void
    {
        $this->markTestIncomplete('TODO: Mã không tồn tại bị từ chối');
    }

    /**
     * @testdox Mã chưa tới ngày bắt đầu bị từ chối
     */
    public function testRejectsNotStartedCode(): void
    {
        $this->markTestIncomplete('TODO: Mã chưa tới ngày bắt đầu bị từ chối');
    }

    /**
     * @testdox Mã đã hết hạn bị từ chối
     */
    public function testRejectsExpiredCode(): void
    {
        // Gợi ý: Dùng mã HETHAN trong chuan_bi_du_lieu_test.sql.
        $this->markTestIncomplete('TODO: Mã đã hết hạn bị từ chối');
    }

    /**
     * @testdox Giới hạn tổng lượt: còn lượt (U < L) được dùng, đã dùng đủ (U = L) bị từ chối
     */
    public function testTotalUsesBoundary(): void
    {
        // Gợi ý: Mã CONLUOT (4/5) áp dụng được; mã HETLUOT (5/5) bị từ chối.
        $this->markTestIncomplete('TODO: Giới hạn tổng lượt: còn lượt (U < L) được dùng, đã dùng đủ (U = L) bị từ chối');
    }

    /**
     * @testdox Giới hạn lượt mỗi khách: đã dùng đủ bị từ chối, chưa dùng được áp dụng
     */
    public function testPerUserLimitBoundary(): void
    {
        // Gợi ý: nguyenvana đã dùng KM50k 1 lần (tối đa 1); tranthib chưa dùng.
        $this->markTestIncomplete('TODO: Giới hạn lượt mỗi khách: đã dùng đủ bị từ chối, chưa dùng được áp dụng');
    }

    /**
     * @testdox Đơn tối thiểu: tổng đơn = mức tối thiểu được áp dụng, thấp hơn 1 đồng thì bị từ chối
     */
    public function testMinimumOrderAmountBoundary(): void
    {
        // Gợi ý: Mã TOITHIEU (2.700.000) và TOITHIEU2 (2.700.001) với giỏ 1 đôi Nike Air Max 270.
        $this->markTestIncomplete('TODO: Đơn tối thiểu: tổng đơn = mức tối thiểu được áp dụng, thấp hơn 1 đồng thì bị từ chối');
    }

    /**
     * @testdox Mã giới hạn theo danh mục/sản phẩm chỉ áp cho hàng thuộc phạm vi
     */
    public function testScopeByCategoryOrProduct(): void
    {
        $this->markTestIncomplete('TODO: Mã giới hạn theo danh mục/sản phẩm chỉ áp cho hàng thuộc phạm vi');
    }

    /**
     * @testdox Mã giảm tiền cố định trừ đúng số tiền
     */
    public function testFixedAmountDiscount(): void
    {
        // Gợi ý: KM50k: 2.700.000 - 50.000 = 2.650.000.
        $this->markTestIncomplete('TODO: Mã giảm tiền cố định trừ đúng số tiền');
    }

    /**
     * @testdox Mã giảm phần trăm tính đúng tiền giảm
     */
    public function testPercentageDiscount(): void
    {
        // Gợi ý: GIAM10: 10% của 2.700.000 = 270.000.
        $this->markTestIncomplete('TODO: Mã giảm phần trăm tính đúng tiền giảm');
    }

    /**
     * @testdox Mã giảm phần trăm không vượt quá số tiền giảm tối đa
     */
    public function testPercentageDiscountRespectsMaxDiscountAmount(): void
    {
        $this->markTestIncomplete('TODO: Mã giảm phần trăm không vượt quá số tiền giảm tối đa');
    }
}
