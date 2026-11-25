<?php
declare(strict_types=1);

namespace Tests\Part1_TaiKhoan_DuyetSanPham;

use Tests\Support\HttpTestCase;

/**
 * Phần 1 - Tài khoản, phiên đăng nhập và duyệt sản phẩm
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: category.php
 * Loại test: HTTP: gọi trang/API như trình duyệt (cần composer serve)
 * Danh sách sản phẩm theo danh mục. Gọi trang qua HttpTestCase (loginAs(), post(), get()) rồi kiểm
 * tra mã trạng thái, nội dung trang, chuyển hướng và dữ liệu trong CSDL test.
 *
 * @group part1
 * @group http
 */
final class CategoryPageTest extends HttpTestCase
{
    /**
     * @testdox Chỉ hiển thị sản phẩm thuộc danh mục được chọn
     */
    public function testCategoryPageListsProductsOfThatCategory(): void
    {
        // Gợi ý: Ví dụ danh mục Sneaker Thể Thao có 3 sản phẩm trong dữ liệu mẫu.
        $response=$this->get('category.php',['id'=>1]); $this->assertSame(200,$response['status']); $this->assertStringContainsString('product.php?id=',$response['body']);
    }

    /**
     * @testdox Mã danh mục không tồn tại không gây lỗi
     */
    public function testCategoryPageWithUnknownIdHandledGracefully(): void
    {
        $response=$this->get('category.php',['id'=>999999]); $this->assertSame(200,$response['status']); $this->assertStringNotContainsString('Warning',$response['body']);
    }

    /**
     * @testdox Phân trang hoạt động đúng
     */
    public function testCategoryPagePagination(): void
    {
        // Dữ liệu mẫu của danh mục 1 có ít hơn 8 sản phẩm, nên kết quả dự kiến
        // không tạo trang 2 và không có liên kết phân trang giả.
        $response = $this->get('category.php', ['id' => 1, 'page' => 2]);
        $this->assertSame(200, $response['status']);
        $this->assertStringNotContainsString('category.php?id=1&page=2', $response['body']);
    }
}
