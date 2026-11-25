<?php
declare(strict_types=1);

namespace Tests\Part4_DatHang_ThanhToan;

use Tests\Support\HttpTestCase;

/**
 * Phần 4 - Đặt hàng và thanh toán (COD, VNPay, ZaloPay)
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: zalo_pay/zalopay_return.php
 * Loại test: HTTP: gọi trang/API như trình duyệt (cần composer serve)
 * Trang kết quả khi khách quay về từ ZaloPay. Lưu ý các liên kết trên header phải đúng khi trang
 * nằm trong thư mục con zalo_pay/ (lỗi BUG-10). Gọi trang qua HttpTestCase (loginAs(), post(),
 * get()) rồi kiểm tra mã trạng thái, nội dung trang, chuyển hướng và dữ liệu trong CSDL test.
 *
 * @group part4
 * @group http
 */
final class ZalopayReturnTest extends HttpTestCase
{
    /**
     * @testdox Thanh toán thành công: đơn đã thanh toán
     */
    public function testSuccessfulPaymentMarksOrderPaid(): void
    {
        $this->markTestIncomplete('TODO: Thanh toán thành công: đơn đã thanh toán');
    }

    /**
     * @testdox Khách hủy: đơn giữ trạng thái chờ thanh toán
     */
    public function testCancelledPaymentKeepsOrderPendingPayment(): void
    {
        $this->markTestIncomplete('TODO: Khách hủy: đơn giữ trạng thái chờ thanh toán');
    }

    /**
     * @testdox Liên kết ở header hoạt động khi trang nằm trong thư mục con
     */
    public function testHeaderLinksWorkFromSubdirectory(): void
    {
        $this->markTestIncomplete('TODO: Liên kết ở header hoạt động khi trang nằm trong thư mục con');
    }
}
