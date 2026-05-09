# 👟 Sneaker Shop - Website Thương Mại Điện Tử (PHP & MySQL)

[![Deployment Status](https://img.shields.io/badge/Deployment-Live-brightgreen)](https://tmdt-ud-sneaker-shop.onrender.com)
[![Tech Stack](https://img.shields.io/badge/Stack-PHP%20%7C%20MySQL%20%7C%20Docker-blue)](https://github.com/HPhuc122/TMDT-UD_sneaker_shop)

## 📖 Giới thiệu
Sneaker Shop là một dự án website bán giày sneaker hoàn chỉnh, được xây dựng với mục tiêu mang lại trải nghiệm mua sắm hiện đại và tích hợp các cổng thanh toán phổ biến tại Việt Nam. Dự án được tối ưu hóa để triển khai trên các nền tảng Cloud (PaaS) bằng Docker.

### Các tính năng chính:
- **Người dùng:** Xem sản phẩm, tìm kiếm thông minh, giỏ hàng, đặt hàng, quản lý đơn hàng cá nhân.
- **Thanh toán:** Tích hợp cổng thanh toán trực tuyến **VNPay** và **ZaloPay** (Sandbox).
- **Quản trị (Admin):** Dashboard thống kê, quản lý sản phẩm (biến thể size/color), quản lý đơn hàng, tồn kho và người dùng.
- **Bảo mật:** Chặn truy cập trái phép, mã hóa mật khẩu, bảo mật phiên làm việc (Session) riêng biệt giữa Admin và User.

---

## 🛠 Công nghệ sử dụng
- **Backend:** PHP 8.x (Thuần)
- **Database:** MySQL 8.0 (Managed by Clever Cloud)
- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5
- **DevOps:** Docker, Docker Compose
- **Hosting:** Render (Web Service), Clever Cloud (Database)

---

## 🚀 Hướng dẫn Triển khai (Deployment)

Dự án này đã được cấu hình để chạy mượt mà trên môi trường Cloud bằng **Docker**.

### 1. Biến môi trường (Environment Variables)
Cần cấu hình các biến sau trên Server (Render/Railway):
- `DB_HOST`: Địa chỉ server database.
- `DB_USER`: Tên đăng nhập database.
- `DB_PASS`: Mật khẩu database.
- `DB_NAME`: Tên cơ sở dữ liệu.
- `APP_URL`: URL công khai của website (Dùng cho VNPay/ZaloPay IPN).

### 2. Triển khai bằng Docker
Dự án bao gồm `Dockerfile` tối ưu cho PHP-Apache:
```dockerfile
# Sử dụng image PHP 8.2 chính thức
FROM php:8.2-apache
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html/uploads && chmod -R 777 /var/www/html/uploads
RUN a2enmod rewrite
```

---

## 💻 Cài đặt tại Local (XAMPP)

1. **Clone dự án:**
   ```bash
   git clone https://github.com/HPhuc122/TMDT-UD_sneaker_shop.git
   ```
2. **Cấu hình Database:**
   - Tạo database `sneaker_shop` trong phpMyAdmin.
   - Import file `database/backup-2026-04-22-192026.sql`.
3. **Cấu hình kết nối:**
   - Chỉnh sửa file `includes/db.php` nếu cần thiết (mặc định đã hỗ trợ cả Local và Env).
4. **Chạy ứng dụng:**
   - Bật Apache và MySQL trong XAMPP.
   - Truy cập: `http://localhost/TMDT-UD_sneaker_shop`

---

## 📁 Cấu trúc thư mục
- `/admin`: Quản lý phía backend của quản trị viên.
- `/api`: Các endpoint xử lý dữ liệu (nếu có).
- `/database`: Chứa các file backup SQL.
- `/includes`: Chứa file kết nối DB (`db.php`), Header, Footer dùng chung.
- `/uploads`: Nơi lưu trữ hình ảnh sản phẩm.
- `/vnpay`: Mã nguồn tích hợp thanh toán VNPay.
- `/zalo_pay`: Mã nguồn tích hợp thanh toán ZaloPay.

---

## 🛡 Bảo mật & Lưu ý
- Dự án sử dụng `getenv()` để bảo mật thông tin kết nối DB, không lưu mật khẩu trực tiếp trong code.
- File `.dockerignore` và `.gitignore` đã được thiết lập để loại bỏ các file nhạy cảm khi push code.
- Khi sử dụng cổng thanh toán, hãy đảm bảo `APP_URL` là địa chỉ HTTPS để nhận được Callback/IPN từ phía ngân hàng.

---
**Dự án được thực hiện bởi Nhóm phát triển Sneaker Shop.**
