<?php
declare(strict_types=1);

namespace Tests\Part3_QuanTri_Kho_DonHang;

use Tests\Support\HttpTestCase;

/**
 * Phần 3 - Quản trị sản phẩm, kho, nhập hàng, đơn hàng và tự động hủy đơn
 * Thành viên phụ trách: ______________________
 * Đối tượng kiểm thử: admin/attributes.php (FR-20, FR-21)
 * Loại test: HTTP: gọi trang/API như trình duyệt (cần composer serve)
 * Quản lý Size, Màu và biến thể. Gọi trang qua HttpTestCase (loginAs(), post(), get()) rồi kiểm
 * tra mã trạng thái, nội dung trang, chuyển hướng và dữ liệu trong CSDL test.
 *
 * @group part3
 * @group http
 */
final class AdminAttributesPageTest extends HttpTestCase
{
    /**
     * @testdox Thêm size mới (action=add_size)
     */
    public function testAddSize(): void
    {
        $this->markTestIncomplete('TODO: Thêm size mới (action=add_size)');
    }

    /**
     * @testdox Thêm màu mới (action=add_color)
     */
    public function testAddColor(): void
    {
        $this->markTestIncomplete('TODO: Thêm màu mới (action=add_color)');
    }

    /**
     * @testdox Thêm size/màu trùng bị từ chối
     */
    public function testAddDuplicateSizeOrColorRejected(): void
    {
        $this->markTestIncomplete('TODO: Thêm size/màu trùng bị từ chối');
    }

    /**
     * @testdox Không xóa size/màu đang dùng trong biến thể hoặc đơn hàng
     */
    public function testDeleteSizeOrColorInUseRejected(): void
    {
        $this->markTestIncomplete('TODO: Không xóa size/màu đang dùng trong biến thể hoặc đơn hàng');
    }

    /**
     * @testdox Không tạo trùng biến thể (cùng sản phẩm, màu, size)
     */
    public function testDuplicateVariantIsNotCreated(): void
    {
        $this->markTestIncomplete('TODO: Không tạo trùng biến thể (cùng sản phẩm, màu, size)');
    }
}
