<?php
declare(strict_types=1);

namespace Tests\Part1_TaiKhoan_DuyetSanPham;

use Tests\Support\HttpTestCase;

/**
 * Phần 1 - Tài khoản, phiên đăng nhập và duyệt sản phẩm
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: index.php, includes/header.php, includes/footer.php
 * Loại test: HTTP: gọi trang/API như trình duyệt (cần composer serve)
 * Trang chủ khách ghé thăm. Gọi trang qua HttpTestCase (loginAs(), post(), get()) rồi kiểm tra mã
 * trạng thái, nội dung trang, chuyển hướng và dữ liệu trong CSDL test.
 *
 * @group part1
 * @group http
 */
final class HomePageTest extends HttpTestCase
{
    /**
     * @testdox Trang chủ mở được khi chưa đăng nhập (HTTP 200)
     */
    public function testHomePageLoadsForGuest(): void
    {
        $response=$this->get('index.php'); $this->assertSame(200,$response['status']);
    }

    /**
     * @testdox Chỉ hiển thị sản phẩm đang hoạt động
     */
    public function testHomePageShowsOnlyActiveProducts(): void
    {
        // CLI: php vendor/bin/phpunit --filter testHomePageShowsOnlyActiveProducts
        $response = $this->get('index.php');
        $this->assertStringContainsString('product.php?id=', $response['body']);
        $this->assertStringNotContainsString('status="inactive"', $response['body']);
    }

    /**
     * @testdox Giá hiển thị đúng định dạng 2.700.000 ₫
     */
    public function testHomePageShowsFormattedPrices(): void
    {
        // Kết quả dự kiến: giá có dấu phân cách hàng nghìn và ký hiệu tiền Việt.
        $response = $this->get('index.php');
        $this->assertMatchesRegularExpression('/[0-9.]+\\s*₫/u', $response['body']);
    }

    /**
     * @testdox Menu hiện Đăng nhập/Đăng ký cho khách, hiện tên người dùng sau khi đăng nhập
     */
    public function testHeaderShowsLoginLinksForGuestAndNameForCustomer(): void
    {
        $guest = $this->get('index.php');
        $this->assertStringContainsString('Đăng nhập', $guest['body']);
        $this->loginAs();
        $user = $this->get('index.php');
        $this->assertStringContainsString('Nguyễn Văn A', $user['body']);
    }
}
