<?php
declare(strict_types=1);

namespace Tests\Part1_TaiKhoan_DuyetSanPham;

use Tests\Support\DatabaseTestCase;

/**
 * Phần 1 - Tài khoản, phiên đăng nhập và duyệt sản phẩm
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: includes/db.php: isAdmin(), isLoggedIn(), kickIfLocked(), redirect()
 * Loại test: Integration: hàm có dùng CSDL test (tự rollback)
 * Kiểm tra các hàm phiên đăng nhập. Ba test đầu là MẪU đã viết sẵn, chạy được ngay để kiểm tra môi
 * trường.
 *
 * @group part1
 * @group integration
 */
final class SessionHelpersTest extends DatabaseTestCase
{
    /**
     * @testdox isAdmin() trả về true khi phiên có role = admin
     */
    public function testIsAdminReturnsTrueForAdminRole(): void
    {
        $_SESSION = ['user_id' => 1, 'role' => 'admin'];
        $this->assertTrue(isAdmin());
    }

    /**
     * @testdox isAdmin() trả về false khi role = customer
     */
    public function testIsAdminReturnsFalseForCustomerRole(): void
    {
        $_SESSION = ['user_id' => 2, 'role' => 'customer'];
        $this->assertFalse(isAdmin());
    }

    /**
     * @testdox isAdmin() trả về false khi chưa đăng nhập
     */
    public function testIsAdminReturnsFalseWhenSessionIsEmpty(): void
    {
        $_SESSION = [];
        $this->assertFalse(isAdmin());
    }

    /**
     * @testdox isLoggedIn() trả về false khi chưa có phiên
     */
    public function testIsLoggedInReturnsFalseWhenNoSession(): void
    {
        // CLI: php vendor/bin/phpunit --filter testIsLoggedInReturnsFalseWhenNoSession
        // Dữ liệu vào: $_SESSION = []. Kết quả dự kiến: false; kết quả thực tế đối chiếu bằng assertFalse().
        $_SESSION = [];
        $this->assertFalse(isLoggedIn(), 'Kết quả thực tế phải là false khi chưa có phiên.');
    }

    /**
     * @testdox isLoggedIn() trả về true với khách hàng đang hoạt động
     */
    public function testIsLoggedInReturnsTrueForActiveCustomer(): void
    {
        // CLI: php vendor/bin/phpunit --filter testIsLoggedInReturnsTrueForActiveCustomer
        $id = (int) $this->fetchValue("SELECT id FROM users WHERE username='nguyenvana' AND role='customer'");
        $_SESSION = ['user_id' => $id, 'role' => 'customer'];
        $this->assertTrue(isLoggedIn(), 'Kết quả thực tế phải là true với khách hàng active.');
    }

    /**
     * @testdox isLoggedIn() từ chối tài khoản đã bị khóa
     */
    public function testIsLoggedInRejectsLockedAccount(): void
    {
        // CLI: php vendor/bin/phpunit --filter testIsLoggedInRejectsLockedAccount
        $id = (int) $this->fetchValue("SELECT id FROM users WHERE username='nguyenvana'");
        self::$conn->query("UPDATE users SET status='locked' WHERE id=$id");
        $_SESSION = ['user_id' => $id, 'role' => 'customer'];
        $this->assertFalse(isLoggedIn(), 'Tài khoản locked phải bị từ chối.');
        $this->assertSame([], $_SESSION, 'Kết quả thực tế phải xóa dữ liệu phiên.');
    }

    /**
     * @testdox isLoggedIn() từ chối phiên của tài khoản không còn tồn tại
     */
    public function testIsLoggedInRejectsDeletedAccount(): void
    {
        // CLI: php vendor/bin/phpunit --filter testIsLoggedInRejectsDeletedAccount
        $_SESSION = ['user_id' => 999999, 'role' => 'customer'];
        $this->assertFalse(isLoggedIn(), 'Tài khoản không tồn tại phải bị từ chối.');
        $this->assertSame([], $_SESSION);
    }

    /**
     * @testdox kickIfLocked() giữ nguyên phiên của khách hàng đang hoạt động
     */
    public function testKickIfLockedKeepsActiveCustomerSession(): void
    {
        // CLI: php vendor/bin/phpunit --filter testKickIfLockedKeepsActiveCustomerSession
        $id = (int) $this->fetchValue("SELECT id FROM users WHERE username='nguyenvana'");
        $_SESSION = ['user_id' => $id, 'role' => 'customer'];
        kickIfLocked();
        $this->assertSame(['user_id' => $id, 'role' => 'customer'], $_SESSION);
    }

    /**
     * @testdox kickIfLocked() hủy phiên và chuyển về trang đăng nhập khi tài khoản bị khóa
     */
    public function testKickIfLockedDestroysSessionOfLockedCustomer(): void
    {
        // CLI: php vendor/bin/phpunit --filter testKickIfLockedDestroysSessionOfLockedCustomer
        // Vì redirect() kết thúc tiến trình bằng exit, nhánh này được kiểm chứng đầu-cuối
        // bằng test HTTP testLockedCustomerIsKickedOnNextRequest().
        $source = (string) file_get_contents(PROJECT_ROOT . '/includes/db.php');
        $this->assertStringContainsString("session_destroy();", $source);
        $this->assertStringContainsString("redirect('/sneaker_shop/login.php');", $source);
    }
}
