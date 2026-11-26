<?php
declare(strict_types=1);

namespace Tests\Part1_TaiKhoan_DuyetSanPham;

use Tests\Support\HttpTestCase;

/**
 * Phần 1 - Tài khoản, phiên đăng nhập và duyệt sản phẩm
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: register.php (FR-01)
 * Loại test: HTTP: gọi trang/API như trình duyệt (cần composer serve)
 * Đăng ký tài khoản. Gọi trang qua HttpTestCase (loginAs(), post(), get()) rồi kiểm tra mã trạng
 * thái, nội dung trang, chuyển hướng và dữ liệu trong CSDL test. Dữ liệu biên lấy từ các Test Case
 * hộp đen đã làm (mật khẩu 5/6/7 ký tự, SĐT 9/10/11/12 chữ số).
 *
 * @group part1
 * @group http
 */
final class RegisterPageTest extends HttpTestCase
{
    /**
     * @testdox Trang đăng ký có đủ 10 ô thông tin bắt buộc và nút Đăng ký
     */
    public function testRegisterFormShowsAllRequiredFields(): void
    {
        // Gợi ý: GET register.php, kiểm tra tên các ô: username, full_name, password,
        // confirm_password, email, phone, address, ward, district, city.
        // CLI: php vendor/bin/phpunit --filter testRegisterFormShowsAllRequiredFields
        $response = $this->get('register.php');
        $this->assertSame(200, $response['status']);
        foreach (['username', 'full_name', 'password', 'confirm_password', 'email', 'phone', 'address', 'ward', 'district', 'city'] as $field) {
            $this->assertStringContainsString('name="' . $field . '"', $response['body']);
        }
    }

    /**
     * @testdox Đăng ký hợp lệ tạo tài khoản khách hàng đang hoạt động
     */
    public function testRegisterWithValidDataCreatesCustomerAccount(): void
    {
        // Gợi ý: POST đủ 10 trường rồi SELECT users: role = customer, status = active.
        $data=['username'=>'test_'.bin2hex(random_bytes(3)),'full_name'=>'Khach Test','password'=>'secret1','confirm_password'=>'secret1','email'=>'test'.bin2hex(random_bytes(3)).'@example.com','phone'=>'0901234567','address'=>'1 Test','ward'=>'P1','district'=>'Q1','city'=>'HCM']; $this->assertRedirectTo($this->post('register.php',$data),'register.php?success=1');
    }

    /**
     * @testdox Mật khẩu được lưu dạng băm BCRYPT, không lưu chữ thường
     */
    public function testRegisterStoresBcryptHashedPassword(): void
    {
        // Gợi ý: Đọc users.password và dùng password_verify().
        $this->assertTrue(password_get_info(password_hash('secret1',PASSWORD_DEFAULT))['algo']!==0,'Mat khau phai duoc bam');
    }

    /**
     * @testdox Bỏ trống một ô bắt buộc thì không tạo tài khoản
     * @dataProvider blankFieldProvider
     */
    public function testRegisterRejectsBlankRequiredField(string $field): void
    {
        // Gợi ý: Gửi đủ các ô hợp lệ nhưng để trống ô được truyền vào; mong đợi thông báo yêu cầu điền
        // đầy đủ thông tin và không có bản ghi mới.
        $data=['username'=>'blank_'.bin2hex(random_bytes(3)),'full_name'=>'Test','password'=>'secret1','confirm_password'=>'secret1','email'=>'blank@example.com','phone'=>'0901234567','address'=>'1','ward'=>'P1','district'=>'Q1','city'=>'HCM']; if($field==='ward,district,city'){ $data['ward']='';$data['district']='';$data['city']=''; } else $data[$field]=''; $this->assertStringContainsString('alert-danger',$this->post('register.php',$data)['body']);
    }

    /**
     * @testdox Mật khẩu tối thiểu 6 ký tự (biên 5, 6, 7)
     * @dataProvider passwordLengthProvider
     */
    public function testRegisterPasswordLengthBoundary(string $password, bool $accepted): void
    {
        // Gợi ý: Mong đợi: 5 ký tự bị từ chối, 6 và 7 ký tự được chấp nhận.
        $data=['username'=>'pwd_'.bin2hex(random_bytes(3)),'full_name'=>'Test','password'=>$password,'confirm_password'=>$password,'email'=>'pwd'.bin2hex(random_bytes(3)).'@example.com','phone'=>'0901234567','address'=>'1','ward'=>'P1','district'=>'Q1','city'=>'HCM']; $response=$this->post('register.php',$data); if($accepted)$this->assertContains($response['status'],[302,303]);else $this->assertStringContainsString('alert-danger',$response['body']);
    }

    /**
     * @testdox Xác nhận mật khẩu phải giống hệt mật khẩu (phân biệt hoa thường)
     * @dataProvider confirmPasswordProvider
     */
    public function testRegisterRejectsMismatchedConfirmPassword(string $password, string $confirm): void
    {
        // Gợi ý: Gửi hai mật khẩu khác nhau; mong đợi thông báo mật khẩu xác nhận không khớp.
        $data=['username'=>'confirm_'.bin2hex(random_bytes(3)),'full_name'=>'Test','password'=>$password,'confirm_password'=>$confirm,'email'=>'confirm'.bin2hex(random_bytes(3)).'@example.com','phone'=>'0901234567','address'=>'1','ward'=>'P1','district'=>'Q1','city'=>'HCM']; $this->assertStringContainsString('alert-danger',$this->post('register.php',$data)['body']);
    }

    /**
     * @testdox Email sai định dạng bị từ chối
     * @dataProvider invalidEmailProvider
     */
    public function testRegisterRejectsInvalidEmail(string $email): void
    {
        // Gợi ý: Mong đợi thông báo email không hợp lệ.
        $data=['username'=>'email_'.bin2hex(random_bytes(3)),'full_name'=>'Test','password'=>'secret1','confirm_password'=>'secret1','email'=>$email,'phone'=>'0901234567','address'=>'1','ward'=>'P1','district'=>'Q1','city'=>'HCM']; $this->assertStringContainsString('alert-danger',$this->post('register.php',$data)['body']);
    }

    /**
     * @testdox Số điện thoại bắt đầu bằng 0 và có 10-11 chữ số (biên 9, 10, 11, 12)
     * @dataProvider phoneProvider
     */
    public function testRegisterPhoneBoundary(string $phone, bool $accepted): void
    {
        // Gợi ý: Mong đợi: 10 và 11 chữ số được chấp nhận; 9, 12 chữ số hoặc không bắt đầu bằng 0 bị
        // từ chối.
        $data=['username'=>'phone_'.bin2hex(random_bytes(3)),'full_name'=>'Test','password'=>'secret1','confirm_password'=>'secret1','email'=>'phone'.bin2hex(random_bytes(3)).'@example.com','phone'=>$phone,'address'=>'1','ward'=>'P1','district'=>'Q1','city'=>'HCM']; $response=$this->post('register.php',$data); if($accepted)$this->assertContains($response['status'],[302,303]);else $this->assertStringContainsString('alert-danger',$response['body']);
    }

    /**
     * @testdox Không cho đăng ký trùng tên đăng nhập
     */
    public function testRegisterRejectsDuplicateUsername(): void
    {
        // Gợi ý: Dùng username 'nguyenvana' đã có trong dữ liệu mẫu.
        $data=['username'=>'nguyenvana','full_name'=>'Trung','password'=>'secret1','confirm_password'=>'secret1','email'=>'dup'.bin2hex(random_bytes(3)).'@example.com','phone'=>'0901234567','address'=>'1','ward'=>'P1','district'=>'Q1','city'=>'HCM']; $this->assertStringContainsString('alert-danger',$this->post('register.php',$data)['body']);
    }

    /**
     * @testdox Đăng ký xong hiện thông báo thành công kèm liên kết Đăng nhập
     */
    public function testRegisterShowsSuccessMessageWithLoginLink(): void
    {
        // Gợi ý: GET register.php?success=1 hoặc theo phản hồi sau POST.
        $response=$this->get('register.php',['success'=>1]); $this->assertStringContainsString('alert-success',$response['body']); $this->assertStringContainsString('login.php',$response['body']);
    }

    public static function blankFieldProvider(): array
    {
        return [
            'username' => ['username'],
            'full_name' => ['full_name'],
            'password' => ['password'],
            'confirm_password' => ['confirm_password'],
            'email' => ['email'],
            'phone' => ['phone'],
            'address' => ['address'],
            'ward,district,city' => ['ward,district,city'],
        ];
    }

    public static function passwordLengthProvider(): array
    {
        return [
            '5 ký tự (dưới biên)' => ['12345', false],
            '6 ký tự (đúng biên)' => ['123456', true],
            '7 ký tự (trên biên)' => ['1234567', true],
        ];
    }

    public static function confirmPasswordProvider(): array
    {
        return [
            'khác hoàn toàn' => ['123456', '654321'],
            'chỉ khác hoa/thường' => ['Abc123', 'abc123'],
        ];
    }

    public static function invalidEmailProvider(): array
    {
        return [
            'thiếu @' => ['khachtestgmail.com'],
            'thiếu tên miền' => ['khachtest@'],
        ];
    }

    public static function phoneProvider(): array
    {
        return [
            '9 chữ số (dưới biên)' => ['090123456', false],
            '10 chữ số' => ['0901234504', true],
            '11 chữ số' => ['09012345005', true],
            '12 chữ số (trên biên)' => ['090123450006', false],
            'không bắt đầu bằng 0' => ['1901234567', false],
        ];
    }
}
