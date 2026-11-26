<?php
declare(strict_types=1);

namespace Tests\Part3_QuanTri_Kho_DonHang;

use Tests\Support\DatabaseTestCase;

/**
 * Phần 3 - Quản trị sản phẩm, kho, nhập hàng, đơn hàng và tự động hủy đơn
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: includes/db.php: cancelOrderAndRestoreStock()
 * Loại test: Integration: hàm có dùng CSDL test (tự rollback)
 * Hàm hủy đơn và hoàn tồn kho dùng chung cho khách hàng và quản trị viên.
 *
 * @group part3
 * @group integration
 */
final class CancelOrderHelperTest extends DatabaseTestCase
{
    /**
     * @testdox Đơn không tồn tại thì trả về thất bại
     */
    public function testReturnsFailureWhenOrderNotFound(): void
    {
        $this->markTestIncomplete('TODO: Đơn không tồn tại thì trả về thất bại');
    }

    /**
     * @testdox Hủy đơn của người khác (khi truyền user_id) bị từ chối
     */
    public function testReturnsFailureWhenOrderBelongsToAnotherUser(): void
    {
        $this->markTestIncomplete('TODO: Hủy đơn của người khác (khi truyền user_id) bị từ chối');
    }

    /**
     * @testdox Đơn đã hủy trước đó không hủy và hoàn kho lần nữa
     */
    public function testReturnsFailureWhenOrderAlreadyCancelled(): void
    {
        $this->markTestIncomplete('TODO: Đơn đã hủy trước đó không hủy và hoàn kho lần nữa');
    }

    /**
     * @testdox Hoàn tồn kho đúng cho từng dòng chi tiết (size, màu, số lượng)
     */
    public function testRestoresStockOfEveryOrderLine(): void
    {
        $this->markTestIncomplete('TODO: Hoàn tồn kho đúng cho từng dòng chi tiết (size, màu, số lượng)');
    }

    /**
     * @testdox Trạng thái đơn chuyển sang cancelled
     */
    public function testOrderStatusBecomesCancelled(): void
    {
        $this->markTestIncomplete('TODO: Trạng thái đơn chuyển sang cancelled');
    }

    /**
     * @testdox Dòng chi tiết cũ không có size/màu vẫn xử lý được
     */
    public function testLegacyRowsWithoutVariantStillHandled(): void
    {
        $this->markTestIncomplete('TODO: Dòng chi tiết cũ không có size/màu vẫn xử lý được');
    }
}
