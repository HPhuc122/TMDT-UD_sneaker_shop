<?php
declare(strict_types=1);

namespace Tests\Part4_DatHang_ThanhToan;

use Tests\Support\HttpTestCase;

/**
 * Phần 4 - Đặt hàng và thanh toán (COD, VNPay, ZaloPay)
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: zalo_pay/zalopay_create.php (FR-12)
 * Loại test: HTTP: gọi trang/API như trình duyệt (cần composer serve)
 * Tạo đơn thanh toán ZaloPay. Gọi trang qua HttpTestCase (loginAs(), post(), get()) rồi kiểm tra
 * mã trạng thái, nội dung trang, chuyển hướng và dữ liệu trong CSDL test.
 *
 * @group part4
 * @group http
 */
final class ZalopayCreateTest extends HttpTestCase
{
    /**
     * @testdox Chưa đăng nhập không tạo được thanh toán
     */
    public function testRequiresLogin(): void
    {
        $this->markTestIncomplete('TODO: Chưa đăng nhập không tạo được thanh toán');
    }

    /**
     * @testdox Tạo giao dịch và lưu app_trans_id vào đơn
     */
    public function testCreatesTransactionAndStoresAppTransId(): void
    {
        $this->markTestIncomplete('TODO: Tạo giao dịch và lưu app_trans_id vào đơn');
    }

    /**
     * @testdox Chuyển sang order_url của ZaloPay
     */
    public function testRedirectsToZalopayOrderUrl(): void
    {
        $this->markTestIncomplete('TODO: Chuyển sang order_url của ZaloPay');
    }

    /**
     * @testdox Chữ ký mac của yêu cầu tính bằng KEY1
     */
    public function testMacIsComputedWithKey1(): void
    {
        $this->markTestIncomplete('TODO: Chữ ký mac của yêu cầu tính bằng KEY1');
    }

    /**
     * @testdox Không thanh toán được đơn của người khác
     */
    public function testCannotPayOrderOfAnotherUser(): void
    {
        $this->markTestIncomplete('TODO: Không thanh toán được đơn của người khác');
    }
}
