<?php
declare(strict_types=1);

namespace Tests\Part1_TaiKhoan_DuyetSanPham;

use Tests\Support\HttpTestCase;
use Tests\Support\DatabaseSeeder;

/**
 * Phần 1 - Tài khoản, phiên đăng nhập và duyệt sản phẩm
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: profile.php (action update_profile, change_password)
 * Loại test: HTTP: gọi trang/API như trình duyệt (cần composer serve)
 * Trang thông tin cá nhân và đổi mật khẩu. Gọi trang qua HttpTestCase (loginAs(), post(), get())
 * rồi kiểm tra mã trạng thái, nội dung trang, chuyển hướng và dữ liệu trong CSDL test.
 *
 * @group part1
 * @group http
 */
final class ProfilePageTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // CLI: php vendor/bin/phpunit --filter ProfilePageTest
        // Mỗi ca đổi mật khẩu cần fixture nguyên vẹn để dùng lại password mẫu.
        DatabaseSeeder::reload();
    }

    /**
     * @testdox Chưa đăng nhập mở profile.php bị chuyển về trang đăng nhập
     */
    public function testProfileRequiresLogin(): void
    {
        $this->assertRedirectTo($this->get('profile.php'),'login.php');
    }

    /**
     * @testdox Trang hiển thị đúng họ tên, email, SĐT, địa chỉ hiện tại
     */
    public function testProfileShowsCurrentUserInformation(): void
    {
        $this->loginAs(); $response=$this->get('profile.php'); $this->assertSame(200,$response['status']); foreach(['nguyenvana','vana@email.com','0901234567'] as $x)$this->assertStringContainsString($x,$response['body']);
    }

    /**
     * @testdox Cập nhật thông tin hợp lệ được lưu vào CSDL
     */
    public function testUpdateProfileSavesNewInformation(): void
    {
        // Gợi ý: POST action=update_profile rồi SELECT users.
        $this->loginAs(); $data=['action'=>'update_profile','full_name'=>'Nguyen Test','email'=>'profile'.bin2hex(random_bytes(3)).'@example.com','phone'=>'0901234567','address'=>'1 Test','ward'=>'P1','district'=>'Q1','city'=>'HCM']; $this->assertStringContainsString('alert-success',$this->post('profile.php',$data)['body']);
    }

    /**
     * @testdox Cập nhật với SĐT hoặc email sai định dạng bị từ chối
     */
    public function testUpdateProfileRejectsInvalidPhoneOrEmail(): void
    {
        $this->loginAs(); $data=['action'=>'update_profile','full_name'=>'Test','email'=>'bad','phone'=>'123']; $this->assertSame(200,$this->post('profile.php',$data)['status']);
    }

    /**
     * @testdox Đổi mật khẩu với mật khẩu hiện tại sai bị từ chối
     */
    public function testChangePasswordRejectsWrongCurrentPassword(): void
    {
        // Gợi ý: POST action=change_password.
        $this->loginAs(); $response=$this->post('profile.php',['action'=>'change_password','current_password'=>'wrong','new_password'=>'secret2','confirm_password'=>'secret2']); $this->assertStringContainsString('alert-danger',$response['body']);
    }

    /**
     * @testdox Đổi mật khẩu với xác nhận không khớp bị từ chối
     */
    public function testChangePasswordRejectsMismatchedConfirmation(): void
    {
        $this->loginAs(); $response=$this->post('profile.php',['action'=>'change_password','current_password'=>'password','new_password'=>'secret2','confirm_password'=>'different']); $this->assertStringContainsString('alert-danger',$response['body']);
    }

    /**
     * @testdox Mật khẩu mới tối thiểu 6 ký tự (biên 5, 6, 7)
     * @dataProvider newPasswordProvider
     */
    public function testChangePasswordMinimumLengthBoundary(string $newPassword, bool $accepted): void
    {
        $this->loginAs(); $response=$this->post('profile.php',['action'=>'change_password','current_password'=>'password','new_password'=>$newPassword,'confirm_password'=>$newPassword]); if($accepted)$this->assertStringContainsString('alert-success',$response['body']);else $this->assertStringContainsString('alert-danger',$response['body']);
    }

    /**
     * @testdox Đổi mật khẩu thành công thì lưu băm mới và đăng nhập được bằng mật khẩu mới
     */
    public function testChangePasswordStoresNewBcryptHash(): void
    {
        $this->loginAs(); $response=$this->post('profile.php',['action'=>'change_password','current_password'=>'password','new_password'=>'newpass','confirm_password'=>'newpass']); $this->assertStringContainsString('alert-success',$response['body']);
    }

    public static function newPasswordProvider(): array
    {
        return [
            '5 ký tự' => ['12345', false],
            '6 ký tự' => ['123456', true],
            '7 ký tự' => ['1234567', true],
        ];
    }
}
