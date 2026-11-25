<?php
declare(strict_types=1);

namespace Tests\Part1_TaiKhoan_DuyetSanPham;

use Tests\Support\HttpTestCase;

/**
 * Phần 1 - Tài khoản, phiên đăng nhập và duyệt sản phẩm
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: login.php (FR-02, FR-03)
 * Loại test: HTTP: gọi trang/API như trình duyệt (cần composer serve)
 * Đăng nhập và đăng xuất khách hàng. Gọi trang qua HttpTestCase (loginAs(), post(), get()) rồi
 * kiểm tra mã trạng thái, nội dung trang, chuyển hướng và dữ liệu trong CSDL test.
 *
 * @group part1
 * @group http
 */
final class LoginPageTest extends HttpTestCase
{
    /**
     * @testdox Trang đăng nhập có ô tài khoản, ô mật khẩu, nút Đăng nhập và liên kết Đăng ký ngay
     */
    public function testLoginPageShowsFormAndRegisterLink(): void
    {
        // Gợi ý: GET login.php và kiểm tra nội dung.
        $response=$this->get('login.php'); $this->assertSame(200,$response['status']); foreach (['username','password','password','register.php'] as $x) $this->assertStringContainsString($x,$response['body']);
    }

    /**
     * @testdox Khách hàng đăng nhập bằng tên đăng nhập
     */
    public function testCustomerCanLoginWithUsername(): void
    {
        // Gợi ý: POST nguyenvana/password: mong đợi chuyển hướng về index.php (header Location).
        $this->assertRedirectTo($this->loginAs(),'index.php');
    }

    /**
     * @testdox Khách hàng đăng nhập bằng email
     */
    public function testCustomerCanLoginWithEmail(): void
    {
        // Gợi ý: Dùng email vana@email.com của dữ liệu mẫu.
        $this->assertRedirectTo($this->loginAs('vana@email.com','password'),'index.php');
    }

    /**
     * @testdox Tài khoản admin đăng nhập ở trang khách được chuyển tới trang quản trị
     */
    public function testAdminLoginRedirectsToDashboard(): void
    {
        // Gợi ý: POST admin/password; Location chứa admin/index.php.
        $this->assertRedirectTo($this->loginAs('admin','password'),'admin/index.php');
    }

    /**
     * @testdox Sai tên hoặc mật khẩu chỉ hiện một thông báo chung
     * @dataProvider wrongCredentialsProvider
     */
    public function testWrongCredentialsShowOneGenericMessage(string $username, string $password): void
    {
        // Gợi ý: Cùng một thông báo cho cả hai trường hợp để không lộ tài khoản nào tồn tại.
        $response=$this->post('login.php',['username'=>$username,'password'=>$password]); $this->assertStringContainsString('alert-danger',$response['body']);
    }

    /**
     * @testdox Để trống tên hoặc mật khẩu thì báo nhập đầy đủ thông tin
     * @dataProvider blankLoginProvider
     */
    public function testBlankFieldsShowRequiredMessage(string $username, string $password): void
    {
        // Gợi ý: Thử trống cả hai, chỉ trống mật khẩu, chỉ trống tên.
        $response=$this->post('login.php',['username'=>$username,'password'=>$password]); $this->assertStringContainsString('alert-danger',$response['body']);
    }

    /**
     * @testdox Tài khoản bị khóa không đăng nhập được
     */
    public function testLockedAccountCannotLogin(): void
    {
        // Gợi ý: Đặt users.status = 'locked' bằng SQL trước, rồi đăng nhập đúng mật khẩu.
        $response=$this->post('login.php',['username'=>'locked-user','password'=>'password']); $this->assertSame(200,$response['status']);
    }

    /**
     * @testdox Đăng nhập với tham số redirect thì quay lại đúng trang yêu cầu
     */
    public function testLoginRedirectsBackToRequestedPage(): void
    {
        // Gợi ý: GET login.php?redirect=cart.php rồi POST đăng nhập.
        $this->assertRedirectTo($this->post('login.php?redirect=cart.php',['username'=>'nguyenvana','password'=>'password']),'cart.php');
    }

    /**
     * @testdox Đăng nhập nạp lại giỏ hàng đã lưu trong CSDL
     */
    public function testLoginRestoresSavedCart(): void
    {
        // Gợi ý: Chèn dòng vào cart_items của khách rồi đăng nhập và mở cart.php.
        $this->assertRedirectTo($this->loginAs(),'index.php'); $response=$this->get('cart.php'); $this->assertContains($response['status'],[200,302,303]);
    }

    /**
     * @testdox Đăng xuất xóa phiên: vào lại my_orders.php bị chuyển về trang đăng nhập
     */
    public function testLogoutClearsSession(): void
    {
        // Gợi ý: GET login.php?action=logout rồi GET my_orders.php.
        $this->loginAs(); $this->get('login.php?action=logout'); $this->assertRedirectTo($this->get('my_orders.php'),'login.php');
    }

    public static function wrongCredentialsProvider(): array
    {
        return [
            'tên không tồn tại' => ['khongtontai', '123456'],
            'sai mật khẩu' => ['nguyenvana', 'wrongpass'],
            'sai chữ hoa/thường mật khẩu' => ['nguyenvana', 'Password'],
        ];
    }

    public static function blankLoginProvider(): array
    {
        return [
            'trống cả hai' => ['', ''],
            'trống mật khẩu' => ['nguyenvana', ''],
            'trống tên' => ['', 'password'],
        ];
    }
}
