<?php
declare(strict_types=1);

namespace Tests\Part3_QuanTri_Kho_DonHang;

use Tests\Support\HttpTestCase;

/**
 * Phần 3 - Quản trị sản phẩm, kho, nhập hàng, đơn hàng và tự động hủy đơn
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: admin/orders.php (FR-26, FR-27)
 * Loại test: HTTP: gọi trang/API như trình duyệt (cần composer serve)
 * Xử lý đơn hàng của quản trị viên. Gọi trang qua HttpTestCase (loginAs(), post(), get()) rồi kiểm
 * tra mã trạng thái, nội dung trang, chuyển hướng và dữ liệu trong CSDL test.
 *
 * @group part3
 * @group http
 */
final class AdminOrdersPageTest extends HttpTestCase
{
    /**
     * @testdox Lọc danh sách đơn theo trạng thái, ngày đặt
     */
    public function testOrderListFiltersByStatusAndDate(): void
    {
        $this->markTestIncomplete('TODO: Lọc danh sách đơn theo trạng thái, ngày đặt');
    }

    /**
     * @testdox Chi tiết đơn hiện sản phẩm, mã giảm giá, mã giao dịch thanh toán online
     */
    public function testOrderDetailShowsItemsAndTransactionId(): void
    {
        $this->markTestIncomplete('TODO: Chi tiết đơn hiện sản phẩm, mã giảm giá, mã giao dịch thanh toán online');
    }

    /**
     * @testdox Chỉ cho phép chuyển trạng thái tuần tự
     * @dataProvider transitionProvider
     */
    public function testAllowedStatusTransitions(string $from, string $to, bool $allowed): void
    {
        // Gợi ý: pending → confirmed → delivered; pending/awaiting → cancelled.
        $this->markTestIncomplete('TODO: Chỉ cho phép chuyển trạng thái tuần tự');
    }

    /**
     * @testdox Đơn đã giao là trạng thái cuối, không đổi được nữa
     */
    public function testDeliveredOrderStatusIsFinal(): void
    {
        $this->markTestIncomplete('TODO: Đơn đã giao là trạng thái cuối, không đổi được nữa');
    }

    /**
     * @testdox Hủy đơn hoàn lại đúng tồn kho từng biến thể
     */
    public function testCancelOrderRestoresStock(): void
    {
        // Gợi ý: Thông báo: 'Đã huỷ đơn hàng và hoàn lại tồn kho.'
        $this->markTestIncomplete('TODO: Hủy đơn hoàn lại đúng tồn kho từng biến thể');
    }

    /**
     * @testdox Đơn online đã xác nhận chỉ được chuyển sang Đã giao
     */
    public function testOnlineConfirmedOrderRules(): void
    {
        $this->markTestIncomplete('TODO: Đơn online đã xác nhận chỉ được chuyển sang Đã giao');
    }

    /**
     * @testdox Khách hàng không đổi được trạng thái đơn
     */
    public function testNonAdminCannotUpdateStatus(): void
    {
        $this->markTestIncomplete('TODO: Khách hàng không đổi được trạng thái đơn');
    }

    public static function transitionProvider(): array
    {
        return [
            'pending sang confirmed' => ['pending', 'confirmed', true],
            'confirmed sang delivered' => ['confirmed', 'delivered', true],
            'pending nhảy cóc sang delivered' => ['pending', 'delivered', false],
            'delivered không đổi được' => ['delivered', 'cancelled', false],
        ];
    }
}
