# Sneaker Shop - Website bán giày sneaker (PHP + MySQL)

## 1. Giới thiệu
Sneaker Shop là dự án website bán hàng được xây dựng bằng **PHP thuần + MySQL + Bootstrap**.

Hệ thống hỗ trợ:
- Khách hàng xem sản phẩm, tìm kiếm, thêm giỏ hàng, đặt hàng.
- Quản trị viên quản lý sản phẩm, đơn hàng, tồn kho, người dùng.
- Thanh toán trực tuyến qua **VNPay** và **ZaloPay** (sandbox).

## 2. Công nghệ sử dụng
- PHP 8.x
- MySQL / MariaDB
- Bootstrap 5
- XAMPP (Apache + MySQL)

## 3. Cấu trúc thư mục chính
- `/`:
  - `index.php`, `product.php`, `cart.php`, `checkout.php`, `my_orders.php`, ...
- `includes/`:
  - `db.php`: kết nối CSDL và các hàm dùng chung
  - `header.php`, `footer.php`
  - `sneaker_shop.sql`: file CSDL mẫu
- `admin/`:
  - Quản trị dashboard, sản phẩm, đơn hàng, tồn kho, người dùng
- `vnpay/`:
  - Tích hợp VNPay (`vnpay_create_payment.php`, `vnpay_return.php`, `vnpay_ipn.php`)
- `zalo_pay/`:
  - Tích hợp ZaloPay (`zalopay_create.php`, `zalopay_return.php`, `zalopay_callback.php`)

## 4. Hướng dẫn cài đặt nhanh
### Bước 1: Đặt source code vào htdocs
Đặt project tại:
`C:/xampp/htdocs/TMDT-UD_sneaker_shop`

### Bước 2: Tạo và import CSDL
1. Tạo database tên: `sneaker_shop`
2. Import file: `includes/sneaker_shop.sql`

Có thể dùng phpMyAdmin hoặc command:

```bash
mysql -u root sneaker_shop < "C:\xampp\htdocs\TMDT-UD_sneaker_shop\includes\sneaker_shop.sql"
```

### Bước 3: Cấu hình kết nối DB
Kiểm tra file `includes/db.php`:
- `DB_HOST`
- `DB_USER`
- `DB_PASS`
- `DB_NAME`

### Bước 4: Chạy dự án
- Bật Apache + MySQL trong XAMPP
- Truy cập:
  - Frontend: `http://localhost/TMDT-UD_sneaker_shop/`
  - Admin: `http://localhost/TMDT-UD_sneaker_shop/admin/`

## 5. Luồng đặt hàng và thanh toán
### Đặt hàng thường (COD)
- Tạo đơn hàng
- Trừ tồn kho ngay

### Đặt hàng online (VNPay/ZaloPay)
- Tạo đơn hàng với trạng thái `awaiting_payment`
- Nếu thanh toán thành công: cập nhật đơn sang `confirmed` + trừ tồn kho
- Nếu thất bại/hủy: giữ trạng thái `awaiting_payment`, không trừ tồn kho

## 6. Tài khoản mẫu
Dữ liệu mẫu có sẵn trong SQL, thường có:
- Admin: `admin`
- User test: `nguyenvana`, `tranthib`, ...

> Mật khẩu phụ thuộc dữ liệu seed hiện tại trong CSDL.

## 7. Lưu ý khi tích hợp cổng thanh toán
- VNPay: cấu hình trong thư mục `vnpay/` (`config.php`)
- ZaloPay: cấu hình trong `zalo_pay/zalopay_config.php`
  - Cập nhật `APP_URL` đúng domain public (ví dụ ngrok) khi test callback

## 8. Hướng phát triển tiếp
- Bổ sung validation/phòng chống race condition cho tồn kho
- Thêm logging giao dịch thanh toán
- Viết test cho luồng checkout và callback thanh toán
- Nâng cấp UI/UX 
