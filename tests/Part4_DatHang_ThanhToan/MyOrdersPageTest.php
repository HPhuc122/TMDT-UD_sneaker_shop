<?php
declare(strict_types=1);

namespace Tests\Part4_DatHang_ThanhToan;

use Tests\Support\HttpTestCase;

/**
 * Phần 4 - Đặt hàng và thanh toán (COD, VNPay, ZaloPay)
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: my_orders.php (FR-14, FR-15)
 * Loại test: HTTP: gọi trang/API như trình duyệt (cần composer serve)
 * Lịch sử đơn hàng, hủy đơn, thanh toán lại, đổi hình thức thanh toán. Test hủy đơn bám 7 đường cơ
 * sở của chức năng hủy trong code (order_action = cancel_order). Gọi trang qua HttpTestCase
 * (loginAs(), post(), get()) rồi kiểm tra mã trạng thái, nội dung trang, chuyển hướng và dữ liệu
 * trong CSDL test.
 *
 * @group part4
 * @group http
 */
final class MyOrdersPageTest extends HttpTestCase
{
    /**
     * @testdox Danh sách chỉ gồm đơn của tài khoản đang đăng nhập
     */
    public function testListShowsOnlyOrdersOfCurrentUser(): void
    {
        $this->markTestIncomplete('TODO: Danh sách chỉ gồm đơn của tài khoản đang đăng nhập');
    }

    /**
     * @testdox Chi tiết đơn có sản phẩm, tổng tiền, trạng thái
     */
    public function testOrderDetailShowsItemsTotalAndStatus(): void
    {
        $this->markTestIncomplete('TODO: Chi tiết đơn có sản phẩm, tổng tiền, trạng thái');
    }

    /**
     * @testdox Không xem được đơn của người khác
     */
    public function testCannotViewOrderOfAnotherUser(): void
    {
        $this->markTestIncomplete('TODO: Không xem được đơn của người khác');
    }

    /**
     * @testdox Quy tắc hủy đơn của khách hàng
     * @dataProvider cancelRuleProvider
     */
    public function testCancelOrderRules(string $paymentMethod, string $status, bool $cancellable): void
    {
        // Gợi ý: Chỉ hủy được COD đang pending hoặc online đang chờ thanh toán; còn lại bị từ chối.
        $this->markTestIncomplete('TODO: Quy tắc hủy đơn của khách hàng');
    }

    /**
     * @testdox Hủy đơn không tồn tại (order_id sai) báo không tìm thấy
     */
    public function testCancelNonexistentOrderShowsNotFound(): void
    {
        // Gợi ý: Cần gửi POST thủ công vì giao diện không cho chọn.
        $this->markTestIncomplete('TODO: Hủy đơn không tồn tại (order_id sai) báo không tìm thấy');
    }

    /**
     * @testdox Hủy đơn của người khác bị từ chối
     */
    public function testCancelOrderOfAnotherUserIsRejected(): void
    {
        $this->markTestIncomplete('TODO: Hủy đơn của người khác bị từ chối');
    }

    /**
     * @testdox Hủy đơn hoàn lại tồn kho
     */
    public function testCancelRestoresStock(): void
    {
        $this->markTestIncomplete('TODO: Hủy đơn hoàn lại tồn kho');
    }

    /**
     * @testdox Thanh toán lại đơn online chuyển đúng sang cổng đã chọn (VNPay không bị chuyển sang ZaloPay)
     */
    public function testPayNowRedirectsOnlineOrderToGateway(): void
    {
        $this->markTestIncomplete('TODO: Thanh toán lại đơn online chuyển đúng sang cổng đã chọn (VNPay không bị chuyển sang ZaloPay)');
    }

    /**
     * @testdox Đổi hình thức thanh toán chỉ áp dụng cho đơn chưa thanh toán
     */
    public function testChangePaymentMethodOnlyForUnpaidOnlineOrders(): void
    {
        // Gợi ý: Các giá trị hợp lệ: cash, transfer, online.
        $this->markTestIncomplete('TODO: Đổi hình thức thanh toán chỉ áp dụng cho đơn chưa thanh toán');
    }

    public static function cancelRuleProvider(): array
    {
        return [
            'COD chờ xử lý' => ['cash', 'pending', true],
            'COD đã xác nhận' => ['cash', 'confirmed', false],
            'online chờ thanh toán' => ['online', 'pending_payment', true],
            'online đã xác nhận' => ['online', 'confirmed', false],
            'chuyển khoản' => ['transfer', 'pending', false],
        ];
    }
}
