<?php
declare(strict_types=1);

namespace Tests\Support;

use PHPUnit\Framework\TestCase;

/**
 * Lớp nền cho test trang/API qua HTTP (login.php, cart.php, api/*.php, admin/*.php, vnpay/*, ...).
 * Các trang dùng header() + exit() nên không include trực tiếp được; ta gọi chúng như trình duyệt.
 *
 * Chuẩn bị: mở một cửa sổ khác chạy  composer serve  (website + CSDL test tại http://localhost:8000).
 * Nếu website chưa chạy, các test HTTP tự động bị bỏ qua (≈ @Disabled / @Ignore của JUnit).
 * Mỗi lớp test được nạp lại dữ liệu mẫu (≈ @BeforeAll); mỗi test có cookie phiên riêng.
 */
abstract class HttpTestCase extends TestCase
{
    protected static string $baseUrl;
    private string $cookieFile = '';

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$baseUrl = rtrim(getenv('APP_TEST_URL') ?: 'http://localhost:8000', '/');

        $parts = parse_url(self::$baseUrl);
        $socket = @fsockopen($parts['host'] ?? 'localhost', $parts['port'] ?? 80, $errno, $errstr, 1);
        if ($socket === false) {
            self::markTestSkipped('Website test chưa chạy tại ' . self::$baseUrl . ' (hãy chạy: composer serve).');
        }
        fclose($socket);

        DatabaseSeeder::reload();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->cookieFile = (string) tempnam(sys_get_temp_dir(), 'phpunit_cookie_');
    }

    protected function tearDown(): void
    {
        if ($this->cookieFile !== '' && is_file($this->cookieFile)) {
            @unlink($this->cookieFile);
        }
        parent::tearDown();
    }

    /**
     * Gửi một yêu cầu HTTP, giữ cookie phiên giữa các lần gọi trong cùng một test.
     * @return array{status:int, headers:array<string,string>, body:string, location:?string}
     */
    protected function request(string $method, string $path, array $data = [], bool $followRedirects = false): array
    {
        return $this->requestWithCookieFile($this->cookieFile, $method, $path, $data, $followRedirects);
    }

    /**
     * Gửi request bằng một cookie jar được chỉ định để mô phỏng nhiều người dùng đồng thời.
     */
    protected function requestWithCookieFile(string $cookieFile, string $method, string $path, array $data = [], bool $followRedirects = false): array
    {
        $url = self::$baseUrl . '/' . ltrim($path, '/');
        $ch = curl_init();
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => $followRedirects,
            CURLOPT_COOKIEJAR => $cookieFile,
            CURLOPT_COOKIEFILE => $cookieFile,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_USERAGENT => 'PHPUnit-SneakerShop',
        ];
        if (strtoupper($method) === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = http_build_query($data);
        } elseif ($data) {
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($data);
        }

        $options[CURLOPT_URL] = $url;
        curl_setopt_array($ch, $options);

        $raw = (string) curl_exec($ch);
        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        $headers = [];
        foreach (explode("\r\n", trim(substr($raw, 0, $headerSize))) as $line) {
            if (str_contains($line, ':')) {
                [$name, $value] = explode(':', $line, 2);
                $headers[strtolower(trim($name))] = trim($value);
            }
        }
        return ['status' => $status, 'headers' => $headers, 'body' => substr($raw, $headerSize),
                'location' => $headers['location'] ?? null];
    }

    /** Tạo cookie jar độc lập cho phiên trình duyệt thứ hai trong cùng một test. */
    protected function createCookieJar(): string
    {
        return (string) tempnam(sys_get_temp_dir(), 'phpunit_cookie_');
    }

    protected function deleteCookieJar(string $cookieFile): void
    {
        if (is_file($cookieFile)) {
            unlink($cookieFile);
        }
    }

    protected function get(string $path, array $query = [], bool $followRedirects = false): array
    {
        return $this->request('GET', $path, $query, $followRedirects);
    }

    protected function post(string $path, array $data = [], bool $followRedirects = false): array
    {
        return $this->request('POST', $path, $data, $followRedirects);
    }

    /** Đăng nhập khách hàng qua login.php (tài khoản mẫu: nguyenvana / password). */
    protected function loginAs(string $username = 'nguyenvana', string $password = 'password'): array
    {
        return $this->post('login.php', ['username' => $username, 'password' => $password]);
    }

    /** Đăng nhập quản trị viên qua admin/login.php (tài khoản mẫu: admin / password). */
    protected function loginAsAdmin(string $username = 'admin', string $password = 'password'): array
    {
        return $this->post('admin/login.php', ['username' => $username, 'password' => $password]);
    }

    /** Giải mã phản hồi JSON của api/*.php. */
    protected function json(array $response): array
    {
        $decoded = json_decode($response['body'], true);
        $this->assertIsArray($decoded, 'Phản hồi không phải JSON: ' . substr($response['body'], 0, 200));
        return $decoded;
    }

    protected function assertRedirectTo(array $response, string $expectedPart): void
    {
        $this->assertContains($response['status'], [301, 302, 303, 307], 'Không phải phản hồi chuyển hướng');
        $this->assertStringContainsString($expectedPart, (string) $response['location']);
    }
}
