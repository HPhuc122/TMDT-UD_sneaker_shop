<?php
declare(strict_types=1);

namespace Tests\Part4_DatHang_ThanhToan;

use Tests\Support\HttpTestCase;

/**
 * Phần 4 - Đặt hàng và thanh toán (COD, VNPay, ZaloPay)
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: vnpay/vnpay_ipn.php
 * Loại test: HTTP: gọi trang/API như trình duyệt (cần composer serve)
 * VNPay gọi máy chủ để xác nhận thanh toán. Mã phản hồi RspCode đối chiếu với code thật: 00 thành
 * công, 01 không thấy đơn, 97 sai chữ ký, 99 lỗi khác. Gọi trang qua HttpTestCase (loginAs(),
 * post(), get()) rồi kiểm tra mã trạng thái, nội dung trang, chuyển hướng và dữ liệu trong CSDL
 * test.
 *
 * @group part4
 * @group http
 */
final class VnpayIpnTest extends HttpTestCase
{
    /**
     * @testdox IPN hợp lệ trả RspCode 00 và cập nhật đơn đã thanh toán
     */
    public function testValidIpnReturnsRspCode00AndUpdatesOrder(): void
    {
        $this->markTestIncomplete('TODO: IPN hợp lệ trả RspCode 00 và cập nhật đơn đã thanh toán');
    }

    /**
     * @testdox Chữ ký sai trả RspCode 97
     */
    public function testInvalidSignatureReturnsRspCode97(): void
    {
        $this->markTestIncomplete('TODO: Chữ ký sai trả RspCode 97');
    }

    /**
     * @testdox Không có đơn trả RspCode 01
     */
    public function testUnknownOrderReturnsRspCode01(): void
    {
        $this->markTestIncomplete('TODO: Không có đơn trả RspCode 01');
    }

    /**
     * @testdox Số tiền không khớp đơn bị từ chối
     */
    public function testAmountMismatchIsRejected(): void
    {
        $this->markTestIncomplete('TODO: Số tiền không khớp đơn bị từ chối');
    }

    /**
     * @testdox Gọi IPN lặp lại không cộng/đổi trạng thái lần hai
     */
    public function testRepeatedIpnIsIdempotent(): void
    {
        $this->markTestIncomplete('TODO: Gọi IPN lặp lại không cộng/đổi trạng thái lần hai');
    }

    /**
     * @testdox Giao dịch thất bại không đánh dấu đã thanh toán
     */
    public function testFailedTransactionDoesNotMarkOrderPaid(): void
    {
        $this->markTestIncomplete('TODO: Giao dịch thất bại không đánh dấu đã thanh toán');
    }
}
