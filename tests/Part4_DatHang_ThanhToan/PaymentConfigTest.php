<?php
declare(strict_types=1);

namespace Tests\Part4_DatHang_ThanhToan;

use PHPUnit\Framework\TestCase;

/**
 * Phần 4 - Đặt hàng và thanh toán (COD, VNPay, ZaloPay)
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: vnpay/config.php, zalo_pay/zalopay_config.php
 * Loại test: Unit: hàm thuần, không ghi dữ liệu
 * Cấu hình cổng thanh toán (đọc từ biến môi trường, có giá trị mặc định cho sandbox). Hai test đầu
 * là MẪU chạy được ngay.
 *
 * @group part4
 * @group unit
 */
final class PaymentConfigTest extends TestCase
{
    /**
     * @testdox vnpay/config.php ghép địa chỉ nhận kết quả và IPN từ APP_URL
     */
    public function testVnpayConfigBuildsReturnAndIpnUrlsFromAppUrl(): void
    {
        $old = getenv('APP_URL');
        putenv('APP_URL=http://localhost/ktpm');
        require PROJECT_ROOT . '/vnpay/config.php';
        $old === false ? putenv('APP_URL') : putenv('APP_URL=' . $old);
        $this->assertSame('http://localhost/ktpm/vnpay/vnpay_return.php', $vnp_Returnurl);
        $this->assertSame('http://localhost/ktpm/vnpay/vnpay_ipn.php', $vnp_IpnUrl);
    }

    /**
     * @testdox vnpay/config.php dùng phiên bản 2.1.0, tiền VND, ngôn ngữ vn
     */
    public function testVnpayConfigDefaultsForSandbox(): void
    {
        require PROJECT_ROOT . '/vnpay/config.php';
        $this->assertSame('2.1.0', $vnp_Version);
        $this->assertSame('VND', $vnp_CurrCode);
        $this->assertSame('vn', $vnp_Locale);
    }

    /**
     * @testdox zalopay_config.php định nghĩa APP_ID, KEY1, KEY2, ENDPOINT
     */
    public function testZalopayConfigDefinesRequiredConstants(): void
    {
        // Gợi ý: require_once file rồi kiểm tra defined().
        $this->markTestIncomplete('TODO: zalopay_config.php định nghĩa APP_ID, KEY1, KEY2, ENDPOINT');
    }

    /**
     * @testdox Khóa bí mật đọc được từ biến môi trường (không bắt buộc ghi cứng trong code)
     */
    public function testSecretsCanBeOverriddenByEnvironment(): void
    {
        $this->markTestIncomplete('TODO: Khóa bí mật đọc được từ biến môi trường (không bắt buộc ghi cứng trong code)');
    }
}
