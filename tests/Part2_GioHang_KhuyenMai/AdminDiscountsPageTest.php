<?php
declare(strict_types=1);

namespace Tests\Part2_GioHang_KhuyenMai;

use Tests\Support\HttpTestCase;

/**
 * Phần 2 - Giỏ hàng và khuyến mãi
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: admin/discounts.php (FR-25)
 * Loại test: HTTP: gọi trang/API như trình duyệt (cần composer serve)
 * Quản trị mã giảm giá. Thông báo trong code: Phần trăm giảm phải lớn hơn 0 và không được vượt quá
 * 100%. / Tổng lượt dùng phải ít nhất là 1. / Số lượt dùng mỗi khách phải ít nhất là 1. / Số lượt
 * dùng mỗi khách không được lớn hơn tổng lượt dùng của mã. Gọi trang qua HttpTestCase (loginAs(),
 * post(), get()) rồi kiểm tra mã trạng thái, nội dung trang, chuyển hướng và dữ liệu trong CSDL
 * test.
 *
 * @group part2
 * @group http
 */
final class AdminDiscountsPageTest extends HttpTestCase
{
    /**
     * @testdox Tạo mã giảm tiền cố định hợp lệ
     */
    public function testCreateFixedAmountCoupon(): void
    {
        $this->markTestIncomplete('TODO: Tạo mã giảm tiền cố định hợp lệ');
    }

    /**
     * @testdox Mã giảm phần trăm phải trong (0, 100]: kiểm thử lớp tương đương và biên
     * @dataProvider percentageProvider
     */
    public function testPercentageValueBoundaries(int $value, bool $accepted): void
    {
        // Gợi ý: Mong đợi: 0 và 101 bị từ chối; 1, 2, 60, 99, 100 được chấp nhận.
        $this->markTestIncomplete('TODO: Mã giảm phần trăm phải trong (0, 100]: kiểm thử lớp tương đương và biên');
    }

    /**
     * @testdox Ngày bắt đầu không được ở quá khứ
     */
    public function testRejectsStartDateInThePast(): void
    {
        $this->markTestIncomplete('TODO: Ngày bắt đầu không được ở quá khứ');
    }

    /**
     * @testdox Ngày bắt đầu phải nhỏ hơn hoặc bằng ngày kết thúc
     */
    public function testRejectsEndDateBeforeStartDate(): void
    {
        $this->markTestIncomplete('TODO: Ngày bắt đầu phải nhỏ hơn hoặc bằng ngày kết thúc');
    }

    /**
     * @testdox Mã code không được trùng
     */
    public function testRejectsDuplicateCode(): void
    {
        // Gợi ý: Dùng mã 'KM50k'.
        $this->markTestIncomplete('TODO: Mã code không được trùng');
    }

    /**
     * @testdox Tổng lượt dùng phải ít nhất là 1
     */
    public function testTotalUsesMustBeAtLeastOne(): void
    {
        $this->markTestIncomplete('TODO: Tổng lượt dùng phải ít nhất là 1');
    }

    /**
     * @testdox Lượt mỗi khách không được lớn hơn tổng lượt dùng
     */
    public function testPerUserUsesMustNotExceedTotalUses(): void
    {
        // Gợi ý: Tổng 3, mỗi khách 5.
        $this->markTestIncomplete('TODO: Lượt mỗi khách không được lớn hơn tổng lượt dùng');
    }

    /**
     * @testdox Phạm vi danh mục/sản phẩm được lưu vào bảng liên kết
     */
    public function testScopeByCategoryAndProductIsSaved(): void
    {
        // Gợi ý: discount_code_categories, discount_code_products.
        $this->markTestIncomplete('TODO: Phạm vi danh mục/sản phẩm được lưu vào bảng liên kết');
    }

    /**
     * @testdox Số tiền giảm tối đa không được nhỏ hơn 0
     */
    public function testNegativeMaxDiscountAmountRejected(): void
    {
        $this->markTestIncomplete('TODO: Số tiền giảm tối đa không được nhỏ hơn 0');
    }

    public static function percentageProvider(): array
    {
        return [
            '0 (dưới biên dưới)' => [0, false],
            '1 (biên dưới)' => [1, true],
            '2' => [2, true],
            '60 (giữa)' => [60, true],
            '99' => [99, true],
            '100 (biên trên)' => [100, true],
            '101 (trên biên trên)' => [101, false],
        ];
    }
}
