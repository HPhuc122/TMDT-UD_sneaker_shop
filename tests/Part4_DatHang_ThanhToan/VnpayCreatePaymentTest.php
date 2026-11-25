<?php
declare(strict_types=1);

namespace Tests\Part4_DatHang_ThanhToan;

use Tests\Support\HttpTestCase;

/**
 * Phần 4 - Đặt hàng và thanh toán (COD, VNPay, ZaloPay)
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: vnpay/vnpay_create_payment.php (FR-11)
 * Loại test: HTTP: gọi trang/API như trình duyệt (cần composer serve)
 * Tạo yêu cầu thanh toán VNPay. Gọi trang qua HttpTestCase (loginAs(), post(), get()) rồi kiểm tra
 * mã trạng thái, nội dung trang, chuyển hướng và dữ liệu trong CSDL test.
 *
 * @group part4
 * @group http
 */
final class VnpayCreatePaymentTest extends HttpTestCase
{
    /**
     * @testdox Chưa đăng nhập không tạo được thanh toán
     */
    public function testRequiresLogin(): void
    {
        $this->markTestIncomplete('TODO: Chưa đăng nhập không tạo được thanh toán');
    }

    /**
     * @testdox Chuyển sang cổng VNPay Sandbox kèm vnp_SecureHash
     */
    public function testRedirectsToVnpaySandboxWithSecureHash(): void
    {
        $this->markTestIncomplete('TODO: Chuyển sang cổng VNPay Sandbox kèm vnp_SecureHash');
    }

    /**
     * @testdox vnp_Amount = số tiền x 100
     */
    public function testAmountIsMultipliedBy100(): void
    {
        $this->markTestIncomplete('TODO: vnp_Amount = số tiền x 100');
    }

    /**
     * @testdox Không thanh toán được đơn của người khác
     */
    public function testCannotPayOrderOfAnotherUser(): void
    {
        $this->markTestIncomplete('TODO: Không thanh toán được đơn của người khác');
    }

    /**
     * @testdox Mã giao dịch lưu đúng cột (không ghi nhầm vào cột của ZaloPay)
     */
    public function testStoresOrderCodeAsTransactionReference(): void
    {
        // Gợi ý: Liên quan lỗi BUG-02 trong Defect Report.
        $this->markTestIncomplete('TODO: Mã giao dịch lưu đúng cột (không ghi nhầm vào cột của ZaloPay)');
    }
}
