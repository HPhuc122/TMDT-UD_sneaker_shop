<?php
declare(strict_types=1);

namespace Tests\Part4_DatHang_ThanhToan;

use Tests\Support\HttpTestCase;

/**
 * Phần 4 - Đặt hàng và thanh toán (COD, VNPay, ZaloPay)
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: vnpay/vnpay_return.php
 * Loại test: HTTP: gọi trang/API như trình duyệt (cần composer serve)
 * Trang kết quả khi khách quay về từ VNPay. Gọi trang qua HttpTestCase (loginAs(), post(), get())
 * rồi kiểm tra mã trạng thái, nội dung trang, chuyển hướng và dữ liệu trong CSDL test.
 *
 * @group part4
 * @group http
 */
final class VnpayReturnTest extends HttpTestCase
{
    /**
     * @testdox Chữ ký đúng và mã 00: đơn chuyển đã xác nhận/đã thanh toán
     */
    public function testValidChecksumAndSuccessCodeMarksOrderPaid(): void
    {
        $this->markTestIncomplete('TODO: Chữ ký đúng và mã 00: đơn chuyển đã xác nhận/đã thanh toán');
    }

    /**
     * @testdox Mã thất bại (ví dụ 24: khách hủy): đơn giữ trạng thái chờ thanh toán
     */
    public function testFailureCodeKeepsOrderPendingPayment(): void
    {
        $this->markTestIncomplete('TODO: Mã thất bại (ví dụ 24: khách hủy): đơn giữ trạng thái chờ thanh toán');
    }

    /**
     * @testdox Chữ ký sai bị từ chối, đơn không đổi
     */
    public function testInvalidChecksumIsRejected(): void
    {
        $this->markTestIncomplete('TODO: Chữ ký sai bị từ chối, đơn không đổi');
    }

    /**
     * @testdox Mã đơn không tồn tại không gây lỗi hệ thống
     */
    public function testUnknownOrderIsHandled(): void
    {
        $this->markTestIncomplete('TODO: Mã đơn không tồn tại không gây lỗi hệ thống');
    }
}
