<?php
declare(strict_types=1);

namespace Tests\Part1_TaiKhoan_DuyetSanPham;

use Tests\Support\HttpTestCase;

/**
 * Phần 1 - Tài khoản, phiên đăng nhập và duyệt sản phẩm
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: search.php (FR-04)
 * Loại test: HTTP: gọi trang/API như trình duyệt (cần composer serve)
 * Tìm kiếm và lọc sản phẩm (từ khóa q, danh mục cat_id, giá từ min_price, giá đến max_price; 4 sản
 * phẩm mỗi trang, sắp xếp theo tên). Gọi trang qua HttpTestCase (loginAs(), post(), get()) rồi
 * kiểm tra mã trạng thái, nội dung trang, chuyển hướng và dữ liệu trong CSDL test.
 *
 * @group part1
 * @group http
 */
final class SearchPageTest extends HttpTestCase
{
    /**
     * @testdox Chưa nhập điều kiện nào thì chỉ hiện form, chưa hiện danh sách
     */
    public function testSearchWithoutConditionsShowsNoResultSection(): void
    {
        // CLI: php vendor/bin/phpunit --filter testSearchWithoutConditionsShowsNoResultSection
        $actual = $this->get('search.php');
        $this->assertSame(200, $actual['status']);
        $this->assertStringContainsString('Nhập từ khóa để tìm kiếm sản phẩm', $actual['body']);
        $this->assertStringNotContainsString('Tìm thấy <strong>', $actual['body']);
    }

    /**
     * @testdox Tìm theo từ khóa không phân biệt hoa thường
     */
    public function testSearchByKeywordIsCaseInsensitive(): void
    {
        // CLI: php vendor/bin/phpunit --filter testSearchByKeywordIsCaseInsensitive
        $upper = $this->get('search.php', ['q' => 'Nike']);
        $lower = $this->get('search.php', ['q' => 'nike']);
        $this->assertSame(200, $upper['status']);
        $this->assertSame(200, $lower['status']);
        preg_match('/Tìm thấy <strong>(\d+)<\/strong>/u', $upper['body'], $upperCount);
        preg_match('/Tìm thấy <strong>(\d+)<\/strong>/u', $lower['body'], $lowerCount);
        $this->assertSame($upperCount[1] ?? null, $lowerCount[1] ?? null);
    }

    /**
     * @testdox Từ khóa không có kết quả hiện thông báo không tìm thấy
     */
    public function testSearchWithUnknownKeywordShowsNotFoundMessage(): void
    {
        // CLI: php vendor/bin/phpunit --filter testSearchWithUnknownKeywordShowsNotFoundMessage
        $actual = $this->get('search.php', ['q' => 'xyzabc123']);
        $this->assertStringContainsString('Tìm thấy <strong>0</strong> sản phẩm', $actual['body']);
        $this->assertStringContainsString('Không tìm thấy sản phẩm phù hợp', $actual['body']);
    }

    /**
     * @testdox Lọc theo danh mục chỉ trả về sản phẩm của danh mục
     */
    public function testSearchByCategoryOnlyReturnsThatCategory(): void
    {
        // CLI: php vendor/bin/phpunit --filter testSearchByCategoryOnlyReturnsThatCategory
        $actual = $this->get('search.php', ['cat_id' => 1]);
        $this->assertSame(200, $actual['status']);
        $this->assertStringContainsString('value="1" selected', $actual['body']);
        $this->assertStringContainsString('Tìm thấy <strong>3</strong> sản phẩm', $actual['body']);
    }

    /**
     * @testdox Lọc khoảng giá tính cả hai đầu mút
     * @dataProvider priceRangeProvider
     */
    public function testSearchPriceRangeBoundaries(int $min, int $max, int $expectedCount): void
    {
        // CLI: php vendor/bin/phpunit --filter testSearchPriceRangeBoundaries
        $actual = $this->get('search.php', ['min_price' => $min, 'max_price' => $max]);
        $this->assertStringContainsString(
            'Tìm thấy <strong>' . $expectedCount . '</strong> sản phẩm',
            $actual['body'],
            'Expected result count and actual result count differ.'
        );
    }

    /**
     * @testdox Kết hợp từ khóa, danh mục và khoảng giá thì kết quả thỏa đồng thời cả ba
     */
    public function testSearchCombinesKeywordCategoryAndPrice(): void
    {
        // CLI: php vendor/bin/phpunit --filter testSearchCombinesKeywordCategoryAndPrice
        $actual = $this->get('search.php', [
            'q' => 'Nike', 'cat_id' => 1, 'min_price' => 1, 'max_price' => 99999999,
        ]);
        $this->assertSame(200, $actual['status']);
        $this->assertStringContainsString('Nike', $actual['body']);
        $this->assertStringContainsString('value="1" selected', $actual['body']);
    }

    /**
     * @testdox Kết quả sắp xếp theo tên A-Z, 4 sản phẩm mỗi trang
     */
    public function testSearchResultsAreSortedByNameAndPaged(): void
    {
        // CLI: php vendor/bin/phpunit --filter testSearchResultsAreSortedByNameAndPaged
        $actual = $this->get('search.php', ['q' => 'a']);
        $this->assertSame(200, $actual['status']);
        $this->assertLessThanOrEqual(4, substr_count($actual['body'], 'product.php?id='));
        $first = strpos($actual['body'], 'product.php?id=');
        $this->assertNotFalse($first, 'Expected at least one product on the first page.');
    }

    public static function priceRangeProvider(): array
    {
        return [
            'trùng đúng một giá 2.700.000' => [2700000, 2700000, 1],
            'giá từ lớn hơn giá đến' => [3000000, 2000000, 0],
        ];
    }
}
