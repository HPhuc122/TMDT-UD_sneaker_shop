<?php
declare(strict_types=1);

namespace Tests\Part3_QuanTri_Kho_DonHang;

use Tests\Support\DatabaseTestCase;

/**
 * Phần 3 - Quản trị sản phẩm, kho, nhập hàng, đơn hàng và tự động hủy đơn
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: includes/db.php: cancelExpiredPendingOrders(), runExpiredOrderCancellationFallback(); cancel_expired_orders.php (FR-13)
 * Loại test: Integration: hàm có dùng CSDL test (tự rollback)
 * Tự động hủy đơn online quá hạn thanh toán và hoàn tồn kho.
 *
 * @group part3
 * @group integration
 */
final class ExpiredOrdersTest extends DatabaseTestCase
{
    /**
     * @testdox Đơn online chờ thanh toán quá hạn bị hủy
     */
    public function testExpiredUnpaidOnlineOrderIsCancelled(): void
    {
        $this->markTestIncomplete('TODO: Đơn online chờ thanh toán quá hạn bị hủy');
    }

    /**
     * @testdox Tồn kho được hoàn lại khi đơn quá hạn bị hủy
     */
    public function testStockIsRestoredForCancelledExpiredOrder(): void
    {
        $this->markTestIncomplete('TODO: Tồn kho được hoàn lại khi đơn quá hạn bị hủy');
    }

    /**
     * @testdox Đơn còn hạn thanh toán không bị hủy
     */
    public function testOrderWithinDeadlineIsKept(): void
    {
        // Gợi ý: Hạn còn 1 giờ.
        $this->markTestIncomplete('TODO: Đơn còn hạn thanh toán không bị hủy');
    }

    /**
     * @testdox Đơn đã thanh toán/đã xác nhận không bị hủy
     */
    public function testPaidOrAlreadyConfirmedOrdersAreNotCancelled(): void
    {
        $this->markTestIncomplete('TODO: Đơn đã thanh toán/đã xác nhận không bị hủy');
    }

    /**
     * @testdox Đơn COD không bị tự động hủy
     */
    public function testCodOrdersAreNotCancelled(): void
    {
        $this->markTestIncomplete('TODO: Đơn COD không bị tự động hủy');
    }

    /**
     * @testdox Hàm trả về đúng số đơn đã hủy
     */
    public function testFunctionReturnsNumberOfCancelledOrders(): void
    {
        // Gợi ý: Script in: Cancelled expired orders: N.
        $this->markTestIncomplete('TODO: Hàm trả về đúng số đơn đã hủy');
    }
}
