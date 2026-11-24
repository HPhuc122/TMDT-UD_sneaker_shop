<?php
declare(strict_types=1);

namespace Tests\Part1_TaiKhoan_DuyetSanPham;

use Tests\Support\HttpTestCase;
use Tests\Support\DatabaseSeeder;

/**
 * Phần 1 - Tài khoản, phiên đăng nhập và duyệt sản phẩm
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: admin/users.php (FR-28, FR-29)
 * Loại test: HTTP: gọi trang/API như trình duyệt (cần composer serve)
 * Quản lý người dùng: khóa, mở khóa, thêm, sửa, đặt lại mật khẩu. Hệ thống KHÔNG cho đổi vai trò
 * tài khoản đã tạo. Gọi trang qua HttpTestCase (loginAs(), post(), get()) rồi kiểm tra mã trạng
 * thái, nội dung trang, chuyển hướng và dữ liệu trong CSDL test.
 *
 * @group part1
 * @group http
 */
final class AdminUsersPageTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // CLI: php vendor/bin/phpunit --filter AdminUsersPageTest
        // Nạp lại fixture trước mỗi test để kết quả thực tế không phụ thuộc thứ tự chạy.
        DatabaseSeeder::reload();
    }

    private function userId(string $username): int
    {
        global $conn;
        $username = $conn->real_escape_string($username);
        return (int) $conn->query("SELECT id FROM users WHERE username='$username'")->fetch_assoc()['id'];
    }

    private function status(int $id): string
    {
        global $conn;
        return (string) $conn->query("SELECT status FROM users WHERE id=$id")->fetch_assoc()['status'];
    }

    private function adminSession(): array
    {
        return $this->loginAsAdmin();
    }
    /**
     * @testdox Danh sách hiển thị tên, email, vai trò và trạng thái
     */
    public function testUsersPageListsAccountsWithRoleAndStatus(): void
    {
        // Dữ liệu vào: phiên admin hợp lệ, GET admin/users.php.
        $this->adminSession();
        $actual = $this->get('admin/users.php');
        $this->assertSame(200, $actual['status'], 'Expected HTTP 200, actual: ' . $actual['status']);
        $this->assertStringContainsString('nguyenvana', $actual['body']);
        $this->assertStringContainsString('vana@email.com', $actual['body']);
        $this->assertStringContainsString('Khách hàng', $actual['body']);
        $this->assertStringContainsString('Hoạt động', $actual['body']);
    }

    /**
     * @testdox Lọc theo vai trò Khách hàng / Admin
     */
    public function testUsersPageFiltersByRole(): void
    {
        // CLI: php vendor/bin/phpunit --filter testUsersPageFiltersByRole
        $this->adminSession();
        $actual = $this->get('admin/users.php', ['role' => 'admin']);
        $this->assertSame(200, $actual['status']);
        $this->assertStringContainsString('admin@sneakershop.vn', $actual['body']);
        $this->assertStringNotContainsString('vana@email.com', $actual['body'],
            'Kết quả thực tế không được chứa khách hàng khi lọc role=admin.');
    }

    /**
     * @testdox Thêm người dùng mới (action=add) lưu mật khẩu băm
     */
    public function testAddUserCreatesAccountWithHashedPassword(): void
    {
        // CLI: php vendor/bin/phpunit --filter testAddUserCreatesAccountWithHashedPassword
        $this->adminSession();
        $actual = $this->post('admin/users.php', [
            'action' => 'add', 'username' => 'unit_add_user', 'password' => 'secret123',
            'full_name' => 'Người dùng Unit', 'email' => 'unit-add@example.com',
            'phone' => '0900000000', 'address' => 'Địa chỉ test', 'ward' => 'Phường 1',
            'district' => 'Quận 1', 'city' => 'TP.HCM', 'role' => 'customer',
        ]);
        global $conn;
        $row = $conn->query("SELECT * FROM users WHERE username='unit_add_user'")->fetch_assoc();
        $this->assertSame(200, $actual['status']);
        $this->assertNotFalse($row, 'Kết quả thực tế phải tạo bản ghi người dùng.');
        $this->assertSame('customer', $row['role']);
        $this->assertTrue(password_verify('secret123', $row['password']),
            'Expected password_hash, actual password must verify.');
        $this->assertStringContainsString('Đã thêm tài khoản', $actual['body']);
    }

    /**
     * @testdox Thêm người dùng trùng tên đăng nhập bị từ chối
     */
    public function testAddUserRejectsDuplicateUsername(): void
    {
        // CLI: php vendor/bin/phpunit --filter testAddUserRejectsDuplicateUsername
        $this->adminSession();
        $actual = $this->post('admin/users.php', [
            'action' => 'add', 'username' => 'nguyenvana', 'password' => 'secret123',
            'full_name' => 'Tên trùng', 'email' => 'duplicate@example.com',
        ]);
        global $conn;
        $count = (int) $conn->query("SELECT COUNT(*) AS c FROM users WHERE username='nguyenvana'")->fetch_assoc()['c'];
        $this->assertSame(200, $actual['status']);
        $this->assertSame(1, $count, 'Kết quả thực tế không được tạo bản ghi trùng.');
        $this->assertStringContainsString('Tên đăng nhập đã tồn tại', $actual['body']);
    }

    /**
     * @testdox Sửa thông tin (action=edit_user) không làm đổi vai trò
     */
    public function testEditUserUpdatesInformationButNotRole(): void
    {
        // CLI: php vendor/bin/phpunit --filter testEditUserUpdatesInformationButNotRole
        $this->adminSession();
        $id = $this->userId('nguyenvana');
        $actual = $this->post('admin/users.php', [
            'action' => 'edit_user', 'user_id' => $id, 'full_name' => 'Nguyễn Văn A (đã sửa)',
            'email' => 'edited@example.com', 'phone' => '0901111111',
            'address' => 'Địa chỉ mới', 'ward' => 'Phường mới', 'district' => 'Quận mới', 'city' => 'TP.HCM',
        ]);
        global $conn;
        $row = $conn->query("SELECT full_name,email,role FROM users WHERE id=$id")->fetch_assoc();
        $this->assertSame(200, $actual['status']);
        $this->assertSame('Nguyễn Văn A (đã sửa)', $row['full_name']);
        $this->assertSame('edited@example.com', $row['email']);
        $this->assertSame('customer', $row['role'], 'Kết quả thực tế phải giữ nguyên vai trò.');
    }

    /**
     * @testdox Đặt lại mật khẩu (action=reset_password) rồi đăng nhập được bằng mật khẩu mới
     */
    public function testResetPasswordLetsUserLoginWithNewPassword(): void
    {
        // CLI: php vendor/bin/phpunit --filter testResetPasswordLetsUserLoginWithNewPassword
        $this->adminSession();
        $id = $this->userId('nguyenvana');
        $actual = $this->post('admin/users.php', [
            'action' => 'reset_password', 'user_id' => $id, 'new_password' => 'newpass123',
        ]);
        global $conn;
        $hash = (string) $conn->query("SELECT password FROM users WHERE id=$id")->fetch_assoc()['password'];
        $this->assertSame(200, $actual['status']);
        $this->assertTrue(password_verify('newpass123', $hash), 'Mật khẩu mới phải xác thực được.');
        $this->assertFalse(password_verify('password', $hash), 'Mật khẩu cũ không được xác thực.');
    }

    /**
     * @testdox Khóa tài khoản khách hàng chuyển status sang locked
     */
    public function testLockCustomerChangesStatusToLocked(): void
    {
        // CLI: php vendor/bin/phpunit --filter testLockCustomerChangesStatusToLocked
        $this->adminSession();
        $id = $this->userId('nguyenvana');
        $actual = $this->get('admin/users.php', ['toggle' => $id]);
        $this->assertSame(200, $actual['status']);
        $this->assertSame('locked', $this->status($id), 'Expected locked, actual: ' . $this->status($id));
        $this->assertStringContainsString('Đã khóa', $actual['body']);
    }

    /**
     * @testdox Khách đang đăng nhập bị đá ra ở yêu cầu kế tiếp sau khi bị khóa
     */
    public function testLockedCustomerIsKickedOnNextRequest(): void
    {
        // CLI: php vendor/bin/phpunit --filter testLockedCustomerIsKickedOnNextRequest
        $customerCookie = $this->createCookieJar();
        $adminCookie = $this->createCookieJar();
        try {
            $customerLogin = $this->requestWithCookieFile($customerCookie, 'POST', 'login.php', [
                'username' => 'nguyenvana', 'password' => 'password',
            ]);
            $this->assertRedirectTo($customerLogin, 'index.php');
            $adminLogin = $this->requestWithCookieFile($adminCookie, 'POST', 'admin/login.php', [
                'username' => 'admin', 'password' => 'password',
            ]);
            $this->assertRedirectTo($adminLogin, 'index.php');
            $id = $this->userId('nguyenvana');
            $this->requestWithCookieFile($adminCookie, 'GET', 'admin/users.php', ['toggle' => $id]);
            $actual = $this->requestWithCookieFile($customerCookie, 'GET', 'profile.php');
            $this->assertRedirectTo($actual, 'login.php');
            $this->assertStringNotContainsString('Nguyễn Văn A', $actual['body']);
        } finally {
            $this->deleteCookieJar($customerCookie);
            $this->deleteCookieJar($adminCookie);
        }
    }

    /**
     * @testdox Mở khóa xong khách đăng nhập lại được
     */
    public function testUnlockCustomerAllowsLoginAgain(): void
    {
        // CLI: php vendor/bin/phpunit --filter testUnlockCustomerAllowsLoginAgain
        $this->adminSession();
        $id = $this->userId('nguyenvana');
        $this->get('admin/users.php', ['toggle' => $id]);
        $this->assertSame('locked', $this->status($id));
        $actual = $this->get('admin/users.php', ['toggle' => $id]);
        $this->assertSame(200, $actual['status']);
        $this->assertSame('active', $this->status($id));
        $this->assertStringContainsString('Đã mở khóa', $actual['body']);
    }

    /**
     * @testdox Không khóa được tài khoản quản trị viên
     */
    public function testAdminAccountCannotBeLocked(): void
    {
        // CLI: php vendor/bin/phpunit --filter testAdminAccountCannotBeLocked
        $this->adminSession();
        $id = $this->userId('admin');
        $actual = $this->get('admin/users.php', ['toggle' => $id]);
        $this->assertSame(200, $actual['status']);
        $this->assertSame('active', $this->status($id), 'Admin không được phép bị khóa.');
        $this->assertStringContainsString('Chỉ được khóa hoặc mở khóa tài khoản khách hàng', $actual['body']);
    }
}
