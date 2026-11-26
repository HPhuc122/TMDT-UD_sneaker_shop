<?php
declare(strict_types=1);

namespace Tests\Part1_TaiKhoan_DuyetSanPham;

use Tests\Support\HttpTestCase;

/**
 * Phần 1 - Tài khoản, phiên đăng nhập và duyệt sản phẩm
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: admin/login.php, admin/logout.php
 * Loại test: HTTP: gọi trang/API như trình duyệt (cần composer serve)
 * Đăng nhập quản trị riêng biệt với khách hàng (session name khác nhau). Gọi trang qua
 * HttpTestCase (loginAs(), post(), get()) rồi kiểm tra mã trạng thái, nội dung trang, chuyển hướng
 * và dữ liệu trong CSDL test.
 *
 * @group part1
 * @group http
 */
final class AdminAuthTest extends HttpTestCase
{
    /**
     * @testdox Admin đăng nhập thành công và vào Dashboard
     */
    public function testAdminLoginSucceedsWithAdminAccount(): void
    {
        // Gợi ý: POST admin/login.php bằng admin/password.
        $this->assertRedirectTo($this->loginAsAdmin(),'index.php');
    }

    /**
     * @testdox Tài khoản khách hàng không đăng nhập được trang quản trị
     */
    public function testAdminLoginRejectsCustomerAccount(): void
    {
        // Gợi ý: Dùng nguyenvana/password ở admin/login.php.
        $response=$this->post('admin/login.php',['username'=>'nguyenvana','password'=>'password']); $this->assertStringContainsString('alert-danger',$response['body']);
    }

    /**
     * @testdox Sai mật khẩu admin bị từ chối
     */
    public function testAdminLoginRejectsWrongPassword(): void
    {
        // Gợi ý: Mong đợi thông báo lỗi và ở lại trang đăng nhập.
        $response=$this->post('admin/login.php',['username'=>'admin','password'=>'wrong']); $this->assertStringContainsString('alert-danger',$response['body']);
    }

    /**
     * @testdox Chưa đăng nhập mở trang quản trị thì bị chuyển về admin/login.php
     * @dataProvider adminPageProvider
     */
    public function testAdminPagesRedirectGuestToAdminLogin(string $page): void
    {
        // Gợi ý: Thử lần lượt các trang quản trị.
        $this->assertRedirectTo($this->get($page),'login.php');
    }

    /**
     * @testdox Đăng xuất admin xóa phiên quản trị
     */
    public function testAdminLogoutDestroysAdminSession(): void
    {
        // CLI: php vendor/bin/phpunit --filter testAdminLogoutDestroysAdminSession
        // 1) Đăng nhập admin; 2) gọi đúng endpoint logout của hệ thống;
        // 3) truy cập lại dashboard và đối chiếu kết quả thực tế là redirect login.
        $this->loginAsAdmin();
        $this->assertRedirectTo($this->get('admin/login.php?action=logout'), 'login.php');
        $this->assertRedirectTo($this->get('admin/index.php'), 'login.php');
    }

    /**
     * @testdox Phiên admin và phiên khách hàng độc lập (USER_SESSION_NAME khác ADMIN_SESSION_NAME)
     */
    public function testAdminAndCustomerSessionsAreIndependent(): void
    {
        // Gợi ý: Đăng nhập cả hai trong cùng cookie jar; đăng xuất bên này không ảnh hưởng bên kia.
        $this->loginAs(); $this->assertRedirectTo($this->loginAsAdmin(),'index.php');
    }

    public static function adminPageProvider(): array
    {
        return [
            'admin/index.php' => ['admin/index.php'],
            'admin/users.php' => ['admin/users.php'],
            'admin/products.php' => ['admin/products.php'],
            'admin/categories.php' => ['admin/categories.php'],
            'admin/attributes.php' => ['admin/attributes.php'],
            'admin/inventory.php' => ['admin/inventory.php'],
            'admin/imports.php' => ['admin/imports.php'],
            'admin/orders.php' => ['admin/orders.php'],
            'admin/discounts.php' => ['admin/discounts.php'],
        ];
    }
}
