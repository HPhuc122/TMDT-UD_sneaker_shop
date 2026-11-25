<?php
declare(strict_types=1);

namespace Tests\Part4_DatHang_ThanhToan;

use Tests\Support\DatabaseTestCase;

/**
 * Phần 4 - Đặt hàng và thanh toán (COD, VNPay, ZaloPay)
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: includes/db.php: getPendingPaymentStatuses(), getOnlinePendingStatus(), isPendingPaymentOrderStatus()
 * Loại test: Integration: hàm có dùng CSDL test (tự rollback)
 * Các hàm xác định trạng thái chờ thanh toán theo cột status của bảng orders. Đúng với
 * database/sneaker_shop.sql (cột có cả pending_payment và awaiting_payment). Toàn bộ lớp này là
 * MẪU chạy được ngay.
 *
 * @group part4
 * @group integration
 */
final class PendingPaymentStatusHelpersTest extends DatabaseTestCase
{
    /**
     * @testdox getPendingPaymentStatuses() trả về các giá trị chờ thanh toán có trong cột status
     */
    public function testGetPendingPaymentStatusesFollowsOrdersEnum(): void
    {
        $this->assertSame(['pending_payment', 'awaiting_payment'], getPendingPaymentStatuses(self::$conn));
    }

    /**
     * @testdox getOnlinePendingStatus() ưu tiên pending_payment
     */
    public function testGetOnlinePendingStatusPrefersPendingPayment(): void
    {
        $this->assertSame('pending_payment', getOnlinePendingStatus(self::$conn));
    }

    /**
     * @testdox isPendingPaymentOrderStatus() chỉ true với trạng thái chờ thanh toán
     * @dataProvider statusProvider
     */
    public function testIsPendingPaymentOrderStatus(string $status, bool $expected): void
    {
        $this->assertSame($expected, isPendingPaymentOrderStatus(self::$conn, $status));
    }

    public static function statusProvider(): array
    {
        return [
            'pending_payment' => ['pending_payment', true],
            'awaiting_payment' => ['awaiting_payment', true],
            'pending' => ['pending', false],
            'confirmed' => ['confirmed', false],
            'delivered' => ['delivered', false],
            'cancelled' => ['cancelled', false],
        ];
    }
}
