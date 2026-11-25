<?php
declare(strict_types=1);

namespace Tests\Part3_QuanTri_Kho_DonHang;

use Tests\Support\HttpTestCase;

/**
 * Phần 3 - Quản trị sản phẩm, kho, nhập hàng, đơn hàng và tự động hủy đơn
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: admin/imports.php (FR-23, FR-24), nhóm BR-10
 * Loại test: HTTP: gọi trang/API như trình duyệt (cần composer serve)
 * Phiếu nhập hàng. 7 test hoàn thành phiếu bám đúng 7 đường cơ sở trong tài liệu kiểm thử dòng
 * điều khiển (TC-DDK-01..07) và dùng dữ liệu của chuan_bi_du_lieu_dong_dieu_khien.sql. Gọi trang
 * qua HttpTestCase (loginAs(), post(), get()) rồi kiểm tra mã trạng thái, nội dung trang, chuyển
 * hướng và dữ liệu trong CSDL test.
 *
 * @group part3
 * @group http
 */
final class AdminImportsPageTest extends HttpTestCase
{
    /**
     * @testdox Tạo phiếu nhập mới ở trạng thái chờ
     */
    public function testCreateReceiptStartsPending(): void
    {
        $this->markTestIncomplete('TODO: Tạo phiếu nhập mới ở trạng thái chờ');
    }

    /**
     * @testdox Thêm dòng nhập: số lượng phải lớn hơn 0 (biên 0, 1, 10)
     * @dataProvider importQuantityProvider
     */
    public function testAddItemQuantityBoundary(int $quantity, bool $accepted): void
    {
        $this->markTestIncomplete('TODO: Thêm dòng nhập: số lượng phải lớn hơn 0 (biên 0, 1, 10)');
    }

    /**
     * @testdox Cùng sản phẩm trong một phiếu dùng chung giá nhập
     */
    public function testSameProductInOneReceiptSharesImportPrice(): void
    {
        // Gợi ý: Thêm lại cùng sản phẩm với giá khác: dòng sau vẫn dùng giá của dòng đầu.
        $this->markTestIncomplete('TODO: Cùng sản phẩm trong một phiếu dùng chung giá nhập');
    }

    /**
     * @testdox Xóa dòng khỏi phiếu đang chờ
     */
    public function testDeleteItemFromPendingReceipt(): void
    {
        $this->markTestIncomplete('TODO: Xóa dòng khỏi phiếu đang chờ');
    }

    /**
     * @testdox Phiếu đã hoàn thành không sửa được
     */
    public function testCompletedReceiptCannotBeEdited(): void
    {
        $this->markTestIncomplete('TODO: Phiếu đã hoàn thành không sửa được');
    }

    /**
     * @testdox Hoàn thành phiếu 1 dòng, biến thể có sẵn (đường 1, PN-CFG-01)
     */
    public function testCompleteReceiptPath1_OneRowExistingVariant(): void
    {
        // Gợi ý: Air Max 270 Đen-35 +10 giá 1.500.000: giá vốn mới 1.782.608,70; tồn Đen-35 từ 8 lên
        // 18.
        $this->markTestIncomplete('TODO: Hoàn thành phiếu 1 dòng, biến thể có sẵn (đường 1, PN-CFG-01)');
    }

    /**
     * @testdox Hoàn thành phiếu 2 dòng cùng sản phẩm (đường 2, PN-CFG-02)
     */
    public function testCompleteReceiptPath2_TwoRowsSameProduct(): void
    {
        // Gợi ý: Air Max 97: Xám-35 +6 (có sẵn) và Đen-35 +4 (mới); giá vốn mới 2.125.925,93.
        $this->markTestIncomplete('TODO: Hoàn thành phiếu 2 dòng cùng sản phẩm (đường 2, PN-CFG-02)');
    }

    /**
     * @testdox Hoàn thành phiếu 2 sản phẩm khác nhau (đường 3, PN-CFG-03)
     */
    public function testCompleteReceiptPath3_TwoDifferentProducts(): void
    {
        // Gợi ý: Giá vốn Air Max 270 = 1.782.608,70; Air Max 97 = 2.141.666,67.
        $this->markTestIncomplete('TODO: Hoàn thành phiếu 2 sản phẩm khác nhau (đường 3, PN-CFG-03)');
    }

    /**
     * @testdox Hoàn thành phiếu có biến thể chưa tồn tại: thêm mới (đường 4, PN-CFG-04)
     */
    public function testCompleteReceiptPath4_NewVariantIsInserted(): void
    {
        // Gợi ý: Air Max 270 Xám-35 +5 giá 1.800.000: giá vốn mới 1.944.444,44; biến thể mới tồn 5.
        $this->markTestIncomplete('TODO: Hoàn thành phiếu có biến thể chưa tồn tại: thêm mới (đường 4, PN-CFG-04)');
    }

    /**
     * @testdox Hoàn thành phiếu nhiều dòng nhiều sản phẩm (đường 5, PN-CFG-05)
     */
    public function testCompleteReceiptPath5_ManyRowsManyProducts(): void
    {
        // Gợi ý: Air Max 270: 2 dòng có sẵn; Air Max 97: 1 dòng mới. Giá vốn 1.861.111,11 và
        // 2.154.545,45.
        $this->markTestIncomplete('TODO: Hoàn thành phiếu nhiều dòng nhiều sản phẩm (đường 5, PN-CFG-05)');
    }

    /**
     * @testdox Hoàn thành phiếu chưa có dòng chi tiết (đường 6, PN-CFG-06)
     */
    public function testCompleteReceiptPath6_ReceiptWithoutDetails(): void
    {
        // Gợi ý: Phiếu chuyển Đã hoàn thành, tồn kho và giá vốn không đổi.
        $this->markTestIncomplete('TODO: Hoàn thành phiếu chưa có dòng chi tiết (đường 6, PN-CFG-06)');
    }

    /**
     * @testdox Hoàn thành lại phiếu đã hoàn thành: không cộng tồn kho lần nữa (đường 7, id 1)
     */
    public function testCompleteReceiptPath7_AlreadyCompletedIsIgnored(): void
    {
        // Gợi ý: GET admin/imports.php?complete=1.
        $this->markTestIncomplete('TODO: Hoàn thành lại phiếu đã hoàn thành: không cộng tồn kho lần nữa (đường 7, id 1)');
    }

    public static function importQuantityProvider(): array
    {
        return [
            '0 (dưới biên)' => [0, false],
            '1 (đúng biên)' => [1, true],
            '10' => [10, true],
        ];
    }
}
