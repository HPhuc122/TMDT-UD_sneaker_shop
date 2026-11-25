<?php
declare(strict_types=1);

namespace Tests\Part3_QuanTri_Kho_DonHang;

use Tests\Support\DatabaseTestCase;

/**
 * Phần 3 - Quản trị sản phẩm, kho, nhập hàng, đơn hàng và tự động hủy đơn
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: includes/db.php: hasTableColumn(), getStockColumnName()
 * Loại test: Integration: hàm có dùng CSDL test (tự rollback)
 * Các hàm dò cấu trúc bảng. Hai test đầu là MẪU chạy được ngay.
 *
 * @group part3
 * @group integration
 */
final class SchemaHelpersTest extends DatabaseTestCase
{
    /**
     * @testdox hasTableColumn() true với cột có thật
     */
    public function testHasTableColumnReturnsTrueForExistingColumn(): void
    {
        $this->assertTrue(hasTableColumn(self::$conn, 'users', 'username'));
    }

    /**
     * @testdox hasTableColumn() false với cột không có
     */
    public function testHasTableColumnReturnsFalseForMissingColumn(): void
    {
        $this->assertFalse(hasTableColumn(self::$conn, 'users', 'cot_khong_ton_tai'));
    }

    /**
     * @testdox getStockColumnName() trả về tên cột tồn kho của bảng products (hoặc null)
     */
    public function testGetStockColumnNameForCurrentSchema(): void
    {
        // Gợi ý: Với database/sneaker_shop.sql bảng products không có cột tồn kho nên mong đợi null.
        $this->markTestIncomplete('TODO: getStockColumnName() trả về tên cột tồn kho của bảng products (hoặc null)');
    }

    /**
     * @testdox hasTableColumn() nhớ kết quả (biến static) cho lần gọi sau
     */
    public function testHasTableColumnCachesResult(): void
    {
        $this->markTestIncomplete('TODO: hasTableColumn() nhớ kết quả (biến static) cho lần gọi sau');
    }
}
