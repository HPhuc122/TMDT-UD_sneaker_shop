<?php
declare(strict_types=1);

namespace Tests\Part3_QuanTri_Kho_DonHang;

use Tests\Support\HttpTestCase;

/**
 * Phần 3 - Quản trị sản phẩm, kho, nhập hàng, đơn hàng và tự động hủy đơn
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: admin/categories.php (FR-17)
 * Loại test: HTTP: gọi trang/API như trình duyệt (cần composer serve)
 * Quản lý danh mục. Thông báo trong code: Vui lòng nhập tên danh mục. / Đã thêm danh mục thành
 * công. / Đã cập nhật danh mục. / Không thể xóa danh mục đang có sản phẩm. / Đã xóa danh mục. Gọi
 * trang qua HttpTestCase (loginAs(), post(), get()) rồi kiểm tra mã trạng thái, nội dung trang,
 * chuyển hướng và dữ liệu trong CSDL test.
 *
 * @group part3
 * @group http
 */
final class AdminCategoriesPageTest extends HttpTestCase
{
    /**
     * @testdox Thêm danh mục: 'Đã thêm danh mục thành công.'
     */
    public function testAddCategory(): void
    {
        $this->markTestIncomplete('TODO: Thêm danh mục: \'Đã thêm danh mục thành công.\'');
    }

    /**
     * @testdox Tên trống: 'Vui lòng nhập tên danh mục.'
     */
    public function testAddCategoryWithBlankNameRejected(): void
    {
        $this->markTestIncomplete('TODO: Tên trống: \'Vui lòng nhập tên danh mục.\'');
    }

    /**
     * @testdox Sửa danh mục: 'Đã cập nhật danh mục.'
     */
    public function testEditCategory(): void
    {
        $this->markTestIncomplete('TODO: Sửa danh mục: \'Đã cập nhật danh mục.\'');
    }

    /**
     * @testdox Xóa danh mục chưa có sản phẩm: 'Đã xóa danh mục.'
     */
    public function testDeleteCategoryWithoutProducts(): void
    {
        $this->markTestIncomplete('TODO: Xóa danh mục chưa có sản phẩm: \'Đã xóa danh mục.\'');
    }

    /**
     * @testdox Xóa danh mục đang có sản phẩm: 'Không thể xóa danh mục đang có sản phẩm.'
     */
    public function testDeleteCategoryWithProductsRejected(): void
    {
        $this->markTestIncomplete('TODO: Xóa danh mục đang có sản phẩm: \'Không thể xóa danh mục đang có sản phẩm.\'');
    }
}
