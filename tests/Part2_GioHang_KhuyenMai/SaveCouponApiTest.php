<?php
declare(strict_types=1);

namespace Tests\Part2_GioHang_KhuyenMai;

use Tests\Support\HttpTestCase;

/**
 * Phần 2 - Giỏ hàng và khuyến mãi
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: api/save_coupon.php (FR-07)
 * Loại test: HTTP: gọi trang/API như trình duyệt (cần composer serve)
 * Lưu mã giảm giá vào ví cá nhân. Các thông báo trong code: Vui lòng đăng nhập để lưu mã. / Dữ
 * liệu không hợp lệ. / Mã giảm giá không khả dụng. / Đã lưu mã thành công! / Mã này đã được lưu
 * trước đó. Gọi trang qua HttpTestCase (loginAs(), post(), get()) rồi kiểm tra mã trạng thái, nội
 * dung trang, chuyển hướng và dữ liệu trong CSDL test.
 *
 * @group part2
 * @group http
 */
final class SaveCouponApiTest extends HttpTestCase
{
    /**
     * @testdox Chưa đăng nhập: 'Vui lòng đăng nhập để lưu mã.'
     */
    public function testRequiresLogin(): void
    {
        $this->markTestIncomplete('TODO: Chưa đăng nhập: \'Vui lòng đăng nhập để lưu mã.\'');
    }

    /**
     * @testdox Thiếu hoặc sai coupon_id: 'Dữ liệu không hợp lệ.'
     */
    public function testRejectsMissingOrInvalidCouponId(): void
    {
        $this->markTestIncomplete('TODO: Thiếu hoặc sai coupon_id: \'Dữ liệu không hợp lệ.\'');
    }

    /**
     * @testdox Mã không khả dụng (hết hạn, hết lượt, bị tắt): 'Mã giảm giá không khả dụng.'
     */
    public function testRejectsUnavailableCoupon(): void
    {
        $this->markTestIncomplete('TODO: Mã không khả dụng (hết hạn, hết lượt, bị tắt): \'Mã giảm giá không khả dụng.\'');
    }

    /**
     * @testdox Lưu mã thành công: 'Đã lưu mã thành công!' và có dòng trong user_saved_coupons
     */
    public function testSavesCouponForUser(): void
    {
        $this->markTestIncomplete('TODO: Lưu mã thành công: \'Đã lưu mã thành công!\' và có dòng trong user_saved_coupons');
    }

    /**
     * @testdox Lưu lại mã đã lưu: 'Mã này đã được lưu trước đó.'
     */
    public function testSavingSameCouponTwiceIsRejected(): void
    {
        $this->markTestIncomplete('TODO: Lưu lại mã đã lưu: \'Mã này đã được lưu trước đó.\'');
    }
}
