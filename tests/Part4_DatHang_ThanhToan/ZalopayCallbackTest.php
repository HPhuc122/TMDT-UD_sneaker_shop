<?php
declare(strict_types=1);

namespace Tests\Part4_DatHang_ThanhToan;

use Tests\Support\HttpTestCase;

/**
 * Phần 4 - Đặt hàng và thanh toán (COD, VNPay, ZaloPay)
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: zalo_pay/zalopay_callback.php
 * Loại test: HTTP: gọi trang/API như trình duyệt (cần composer serve)
 * ZaloPay gọi máy chủ để xác nhận thanh toán (return_code 1 thành công, -1 mac không hợp lệ). Gọi
 * trang qua HttpTestCase (loginAs(), post(), get()) rồi kiểm tra mã trạng thái, nội dung trang,
 * chuyển hướng và dữ liệu trong CSDL test.
 *
 * @group part4
 * @group http
 */
final class ZalopayCallbackTest extends HttpTestCase
{
    /**
     * @testdox Callback hợp lệ cập nhật đơn đã thanh toán và trả return_code = 1
     */
    public function testValidCallbackMarksOrderPaid(): void
    {
        $this->markTestIncomplete('TODO: Callback hợp lệ cập nhật đơn đã thanh toán và trả return_code = 1');
    }

    /**
     * @testdox Mac sai trả return_code = -1, đơn không đổi
     */
    public function testInvalidMacIsRejected(): void
    {
        $this->markTestIncomplete('TODO: Mac sai trả return_code = -1, đơn không đổi');
    }

    /**
     * @testdox Giao dịch không có đơn tương ứng không gây lỗi
     */
    public function testUnknownTransactionIsHandled(): void
    {
        $this->markTestIncomplete('TODO: Giao dịch không có đơn tương ứng không gây lỗi');
    }

    /**
     * @testdox Callback lặp lại không đổi gì thêm
     */
    public function testRepeatedCallbackIsIdempotent(): void
    {
        $this->markTestIncomplete('TODO: Callback lặp lại không đổi gì thêm');
    }
}
