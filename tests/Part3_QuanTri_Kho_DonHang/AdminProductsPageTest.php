<?php
declare(strict_types=1);

namespace Tests\Part3_QuanTri_Kho_DonHang;

use Tests\Support\HttpTestCase;

/**
 * Phần 3 - Quản trị sản phẩm, kho, nhập hàng, đơn hàng và tự động hủy đơn
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: admin/products.php (FR-18, FR-19)
 * Loại test: HTTP: gọi trang/API như trình duyệt (cần composer serve)
 * Quản lý sản phẩm. Thông báo trong code: Vui lòng điền đầy đủ Tên sản phẩm và Danh mục. / Định
 * dạng ảnh không hợp lệ. / Sản phẩm đã phát sinh dữ liệu → đã chuyển sang ẩn. / Đã xóa sản phẩm.
 * Gọi trang qua HttpTestCase (loginAs(), post(), get()) rồi kiểm tra mã trạng thái, nội dung
 * trang, chuyển hướng và dữ liệu trong CSDL test.
 *
 * @group part3
 * @group http
 */
final class AdminProductsPageTest extends HttpTestCase
{
    /**
     * @testdox Thêm sản phẩm đầy đủ thông tin
     */
    public function testAddProductWithFullInformation(): void
    {
        $this->markTestIncomplete('TODO: Thêm sản phẩm đầy đủ thông tin');
    }

    /**
     * @testdox Thiếu tên hoặc danh mục bị từ chối
     */
    public function testAddProductRequiresNameAndCategory(): void
    {
        $this->markTestIncomplete('TODO: Thiếu tên hoặc danh mục bị từ chối');
    }

    /**
     * @testdox Ảnh sai định dạng (ví dụ .pdf) bị từ chối
     */
    public function testAddProductRejectsInvalidImageFormat(): void
    {
        $this->markTestIncomplete('TODO: Ảnh sai định dạng (ví dụ .pdf) bị từ chối');
    }

    /**
     * @testdox Giá bán = giá nhập x (1 + %lợi nhuận)
     */
    public function testSellPriceIsImportPriceTimesProfitRate(): void
    {
        // Gợi ý: Giá nhập 2.000.000, lợi nhuận 35% cho giá bán 2.700.000; lợi nhuận 0% cho 2.000.000.
        $this->markTestIncomplete('TODO: Giá bán = giá nhập x (1 + %lợi nhuận)');
    }

    /**
     * @testdox Sửa thông tin sản phẩm
     */
    public function testEditProductUpdatesInformation(): void
    {
        $this->markTestIncomplete('TODO: Sửa thông tin sản phẩm');
    }

    /**
     * @testdox Xóa sản phẩm chưa phát sinh dữ liệu: xóa hẳn
     */
    public function testDeleteProductWithoutHistoryRemovesIt(): void
    {
        $this->markTestIncomplete('TODO: Xóa sản phẩm chưa phát sinh dữ liệu: xóa hẳn');
    }

    /**
     * @testdox Xóa sản phẩm đã phát sinh đơn hàng/phiếu nhập: chuyển sang ẩn
     */
    public function testDeleteProductWithOrdersOnlyHidesIt(): void
    {
        $this->markTestIncomplete('TODO: Xóa sản phẩm đã phát sinh đơn hàng/phiếu nhập: chuyển sang ẩn');
    }

    /**
     * @testdox Mã sản phẩm trùng bị từ chối
     */
    public function testDuplicateProductCodeRejected(): void
    {
        $this->markTestIncomplete('TODO: Mã sản phẩm trùng bị từ chối');
    }
}
