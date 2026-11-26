<?php
declare(strict_types=1);

namespace Tests\Part3_QuanTri_Kho_DonHang;

use Tests\Support\HttpTestCase;

/**
 * Phần 3 - Quản trị sản phẩm, kho, nhập hàng, đơn hàng và tự động hủy đơn
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: admin/index.php (FR-16)
 * Loại test: HTTP: gọi trang/API như trình duyệt (cần composer serve)
 * Dashboard thống kê. Gọi trang qua HttpTestCase (loginAs(), post(), get()) rồi kiểm tra mã trạng
 * thái, nội dung trang, chuyển hướng và dữ liệu trong CSDL test.
 *
 * @group part3
 * @group http
 */
final class AdminDashboardTest extends HttpTestCase
{
    /**
     * @testdox Hiển thị đúng tổng sản phẩm, đơn hàng, khách hàng
     */
    public function testDashboardShowsTotals(): void
    {
        $this->markTestIncomplete('TODO: Hiển thị đúng tổng sản phẩm, đơn hàng, khách hàng');
    }

    /**
     * @testdox Doanh thu chỉ tính COD đã giao và online đã xác nhận/đã giao
     */
    public function testRevenueCountsOnlyCompletedOrders(): void
    {
        $this->markTestIncomplete('TODO: Doanh thu chỉ tính COD đã giao và online đã xác nhận/đã giao');
    }

    /**
     * @testdox Đơn đã hủy và chờ thanh toán không tính vào doanh thu
     */
    public function testCancelledAndPendingOrdersExcludedFromRevenue(): void
    {
        $this->markTestIncomplete('TODO: Đơn đã hủy và chờ thanh toán không tính vào doanh thu');
    }

    /**
     * @testdox Danh sách sắp hết hàng dùng tổng tồn các biến thể (ngưỡng 5)
     */
    public function testLowStockListUsesSumOfVariantStock(): void
    {
        $this->markTestIncomplete('TODO: Danh sách sắp hết hàng dùng tổng tồn các biến thể (ngưỡng 5)');
    }

    /**
     * @testdox Chỉ admin mới xem được Dashboard
     */
    public function testDashboardRequiresAdmin(): void
    {
        $this->markTestIncomplete('TODO: Chỉ admin mới xem được Dashboard');
    }
}
