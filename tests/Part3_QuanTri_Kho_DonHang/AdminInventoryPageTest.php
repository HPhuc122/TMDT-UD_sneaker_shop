<?php
declare(strict_types=1);

namespace Tests\Part3_QuanTri_Kho_DonHang;

use Tests\Support\HttpTestCase;

/**
 * Phần 3 - Quản trị sản phẩm, kho, nhập hàng, đơn hàng và tự động hủy đơn
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: admin/inventory.php (FR-22)
 * Loại test: HTTP: gọi trang/API như trình duyệt (cần composer serve)
 * Tồn kho và cập nhật tỷ lệ lợi nhuận. Gọi trang qua HttpTestCase (loginAs(), post(), get()) rồi
 * kiểm tra mã trạng thái, nội dung trang, chuyển hướng và dữ liệu trong CSDL test.
 *
 * @group part3
 * @group http
 */
final class AdminInventoryPageTest extends HttpTestCase
{
    /**
     * @testdox Danh sách tồn kho theo biến thể
     */
    public function testInventoryListsVariantStock(): void
    {
        $this->markTestIncomplete('TODO: Danh sách tồn kho theo biến thể');
    }

    /**
     * @testdox Cảnh báo sắp hết hàng khi tổng tồn từ 5 trở xuống (biên 4, 5, 6)
     * @dataProvider lowStockProvider
     */
    public function testLowStockWarningBoundary(int $totalStock, bool $warned): void
    {
        // Gợi ý: Mong đợi: tổng 4 và 5 có cảnh báo; tổng 6 không có.
        $this->markTestIncomplete('TODO: Cảnh báo sắp hết hàng khi tổng tồn từ 5 trở xuống (biên 4, 5, 6)');
    }

    /**
     * @testdox Cập nhật tỷ lệ lợi nhuận (update_price) tính lại giá bán
     */
    public function testUpdateProfitRateRecalculatesSellPrice(): void
    {
        $this->markTestIncomplete('TODO: Cập nhật tỷ lệ lợi nhuận (update_price) tính lại giá bán');
    }

    /**
     * @testdox Tỷ lệ lợi nhuận không hợp lệ bị từ chối
     */
    public function testUpdateProfitRateRejectsInvalidValue(): void
    {
        $this->markTestIncomplete('TODO: Tỷ lệ lợi nhuận không hợp lệ bị từ chối');
    }

    public static function lowStockProvider(): array
    {
        return [
            'tổng 4' => [4, true],
            'tổng 5 (biên)' => [5, true],
            'tổng 6' => [6, false],
        ];
    }
}
