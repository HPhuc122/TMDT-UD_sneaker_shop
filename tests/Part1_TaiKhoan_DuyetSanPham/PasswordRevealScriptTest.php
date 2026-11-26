<?php
declare(strict_types=1);

namespace Tests\Part1_TaiKhoan_DuyetSanPham;

use PHPUnit\Framework\TestCase;

/**
 * Phần 1 - Tài khoản, phiên đăng nhập và duyệt sản phẩm
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: includes/password-reveal.php (nút con mắt hiện/ẩn mật khẩu)
 * Loại test: Unit: hàm thuần, không ghi dữ liệu
 * Kiểm tra đoạn HTML/CSS/JS dùng chung cho nút con mắt ở các ô mật khẩu. Hai test đầu là MẪU chạy
 * được ngay.
 *
 * @group part1
 * @group unit
 */
final class PasswordRevealScriptTest extends TestCase
{
    /**
     * @testdox Nút xem mật khẩu hoạt động đúng trên form tài khoản
     * @dataProvider revealScriptProvider
     */
    public function testRevealScriptContainsRequiredBehavior(string $case, string $expected): void
    {
        // Chạy: php vendor\phpunit\phpunit\phpunit tests\Part1_TaiKhoan_DuyetSanPham\PasswordRevealScriptTest.php --filter testRevealScriptContainsRequiredBehavior
        // Dữ liệu vào: một chuỗi DOM/JavaScript bắt buộc; kết quả mong đợi:
        // chuỗi phải tồn tại; kết quả thực tế đối chiếu bằng assertion.
        ob_start();
        include PROJECT_ROOT . '/includes/password-reveal.php';
        $html = (string) ob_get_clean();
        $this->assertStringContainsString($expected, $html, 'Không đạt case: ' . $case);
    }

    /**
     * @testdox Nút xem mật khẩu dễ sử dụng
     * @dataProvider accessibilityProvider
     */
    public function testRevealButtonIsAccessible(string $case, string $expected): void
    {
        // Chạy: php vendor\phpunit\phpunit\phpunit tests\Part1_TaiKhoan_DuyetSanPham\PasswordRevealScriptTest.php --filter testRevealButtonIsAccessible
        // Dữ liệu vào: thuộc tính accessibility; kết quả mong đợi: component
        // phải chứa thuộc tính; kết quả thực tế đối chiếu bằng assertion.
        ob_start();
        include PROJECT_ROOT . '/includes/password-reveal.php';
        $html = (string) ob_get_clean();
        $this->assertStringContainsString($expected, $html, 'Không đạt case: ' . $case);
    }

    /**
     * @testdox Mật khẩu tự ẩn khi người dùng dừng xem
     * @dataProvider autoHideProvider
     */
    public function testRevealHidesPasswordOnSafetyEvents(string $case, string $expected): void
    {
        // Chạy: php vendor\phpunit\phpunit\phpunit tests\Part1_TaiKhoan_DuyetSanPham\PasswordRevealScriptTest.php --filter testRevealHidesPasswordOnSafetyEvents
        // Đây là Unit Test kiểm tra mã JavaScript được sinh ra, không cần browser.
        ob_start();
        include PROJECT_ROOT . '/includes/password-reveal.php';
        $html = (string) ob_get_clean();
        $this->assertStringContainsString($expected, $html, 'Không đạt case: ' . $case);
    }

    public static function revealScriptProvider(): array
    {
        return [
            'form có phần xử lý nút xem mật khẩu' => ['form có phần xử lý nút xem mật khẩu', '<script>'],
            'nhận biết đúng ô mật khẩu' => ['nhận biết đúng ô mật khẩu', 'input[type="password"]'],
            'bấm giữ để xem mật khẩu' => ['bấm giữ để xem mật khẩu', "input.type = 'text'"],
            'nhả nút để ẩn mật khẩu' => ['nhả nút để ẩn mật khẩu', "input.type = 'password'"],
            'có khung chứa nút xem' => ['có khung chứa nút xem', "wrapper.className = 'password-reveal'"],
            'có nút con mắt' => ['có nút con mắt', "button.className = 'password-reveal-button'"],
        ];
    }

    public static function accessibilityProvider(): array
    {
        return [
            'biết nút đang được giữ' => ['biết nút đang được giữ', 'aria-pressed'],
            'có lời hướng dẫn cho người dùng' => ['có lời hướng dẫn cho người dùng', 'aria-label'],
            'nút gắn với ô mật khẩu' => ['nút gắn với ô mật khẩu', 'aria-controls'],
        ];
    }

    public static function autoHideProvider(): array
    {
        return [
            'nhả nút chuột' => ['nhả nút chuột', "document.addEventListener('pointerup'"],
            'thao tác bị dừng' => ['thao tác bị dừng', "document.addEventListener('pointercancel'"],
            'nhấn phím Escape' => ['nhấn phím Escape', "event.key === 'Escape'"],
            'chuyển sang trang khác' => ['chuyển sang trang khác', "window.addEventListener('pagehide'"],
        ];
    }
}
