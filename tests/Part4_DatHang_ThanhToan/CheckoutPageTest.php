<?php
declare(strict_types=1);

namespace Tests\Part4_DatHang_ThanhToan;

use Tests\Support\HttpTestCase;

/**
 * Phần 4 - Đặt hàng và thanh toán (COD, VNPay, ZaloPay)
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: checkout.php (FR-09, FR-10)
 * Loại test: HTTP: gọi trang/API như trình duyệt (cần composer serve)
 * Đặt hàng. Thứ tự kiểm tra trong code: thông tin giao hàng, số điện thoại, rồi tồn kho trong
 * transaction. Gọi trang qua HttpTestCase (loginAs(), post(), get()) rồi kiểm tra mã trạng thái,
 * nội dung trang, chuyển hướng và dữ liệu trong CSDL test.
 *
 * @group part4
 * @group http
 */
final class CheckoutPageTest extends HttpTestCase
{
    /**
     * @testdox Chưa đăng nhập hoặc giỏ trống thì không vào được thanh toán
     */
    public function testCheckoutRequiresLoginAndNonEmptyCart(): void
    {
        $this->markTestIncomplete('TODO: Chưa đăng nhập hoặc giỏ trống thì không vào được thanh toán');
    }

    /**
     * @testdox Trang thanh toán hiện sản phẩm, tổng tiền, 3 phương thức COD/VNPay/ZaloPay
     */
    public function testCheckoutPageShowsItemsTotalAndPaymentMethods(): void
    {
        $this->markTestIncomplete('TODO: Trang thanh toán hiện sản phẩm, tổng tiền, 3 phương thức COD/VNPay/ZaloPay');
    }

    /**
     * @testdox Đặt COD tạo đơn trạng thái pending và làm rỗng giỏ
     */
    public function testCodOrderIsCreatedPendingAndCartCleared(): void
    {
        $this->markTestIncomplete('TODO: Đặt COD tạo đơn trạng thái pending và làm rỗng giỏ');
    }

    /**
     * @testdox Tồn kho giảm ngay khi đặt hàng
     */
    public function testStockIsDecreasedWhenOrderIsPlaced(): void
    {
        // Gợi ý: Tồn 8, đặt 2 thì còn 6.
        $this->markTestIncomplete('TODO: Tồn kho giảm ngay khi đặt hàng');
    }

    /**
     * @testdox Thiếu địa chỉ/SĐT: 'Vui lòng điền đầy đủ thông tin giao hàng.'
     */
    public function testMissingDeliveryInformationRejected(): void
    {
        $this->markTestIncomplete('TODO: Thiếu địa chỉ/SĐT: \'Vui lòng điền đầy đủ thông tin giao hàng.\'');
    }

    /**
     * @testdox SĐT sai: 'Số điện thoại không hợp lệ. Phải bắt đầu bằng số 0 và có 10-11 chữ số.'
     */
    public function testInvalidPhoneRejected(): void
    {
        $this->markTestIncomplete('TODO: SĐT sai: \'Số điện thoại không hợp lệ. Phải bắt đầu bằng số 0 và có 10-11 chữ số.\'');
    }

    /**
     * @testdox COD hết hàng: 'Sản phẩm không đủ hàng!', không tạo đơn
     */
    public function testInsufficientStockRejectedForCod(): void
    {
        $this->markTestIncomplete('TODO: COD hết hàng: \'Sản phẩm không đủ hàng!\', không tạo đơn');
    }

    /**
     * @testdox Online hết hàng: 'Sản phẩm không đủ tồn kho để giữ đơn trong 24 giờ.'
     */
    public function testInsufficientStockRejectedForOnlinePayment(): void
    {
        $this->markTestIncomplete('TODO: Online hết hàng: \'Sản phẩm không đủ tồn kho để giữ đơn trong 24 giờ.\'');
    }

    /**
     * @testdox Đặt VNPay/ZaloPay tạo đơn trạng thái chờ thanh toán kèm hạn thanh toán 24 giờ
     */
    public function testOnlineOrderIsCreatedWithPendingPaymentStatus(): void
    {
        // Gợi ý: Trạng thái lấy từ getOnlinePendingStatus() (với schema mẫu là pending_payment).
        $this->markTestIncomplete('TODO: Đặt VNPay/ZaloPay tạo đơn trạng thái chờ thanh toán kèm hạn thanh toán 24 giờ');
    }

    /**
     * @testdox Áp mã giảm giá: tổng đơn đúng và ghi nhận lượt dùng vào discount_code_usages
     */
    public function testVoucherIsAppliedAndUsageRecorded(): void
    {
        // Gợi ý: KM50k: 2.700.000 - 50.000 = 2.650.000.
        $this->markTestIncomplete('TODO: Áp mã giảm giá: tổng đơn đúng và ghi nhận lượt dùng vào discount_code_usages');
    }

    /**
     * @testdox Tổng đơn trong CSDL = tổng giỏ - tiền giảm
     */
    public function testOrderTotalMatchesCartMinusDiscount(): void
    {
        $this->markTestIncomplete('TODO: Tổng đơn trong CSDL = tổng giỏ - tiền giảm');
    }

    /**
     * @testdox Đặt hàng lỗi thì không trừ kho và không tính lượt dùng mã (rollback)
     */
    public function testFailedOrderRollsBackStockAndVoucher(): void
    {
        $this->markTestIncomplete('TODO: Đặt hàng lỗi thì không trừ kho và không tính lượt dùng mã (rollback)');
    }
}
