# TÀI LIỆU ĐẶC TẢ CHỨC NĂNG HỆ THỐNG (FUNCTIONAL SPECIFICATION DOCUMENT)
## Dự án: Website Thương Mại Điện Tử Bán Giày Sneaker (Sneaker Shop)

---

## 1. GIỚI THIỆU TỔNG QUAN

### 1.1. Mục tiêu dự án
Hệ thống **Sneaker Shop** là một giải pháp thương mại điện tử trực tuyến toàn diện hỗ trợ việc kinh doanh giày sneaker. Hệ thống phục vụ 2 nhóm đối tượng chính: **Khách hàng** (xem, tìm kiếm, đặt hàng và thanh toán trực tuyến) và **Quản trị viên** (quản lý sản phẩm, tồn kho, phiếu nhập hàng, mã giảm giá, đơn hàng và khách hàng).

### 1.2. Phạm vi hệ thống
Hệ thống bao gồm các phân hệ chính:
- **Phân hệ Khách hàng (Front-end):** Tìm kiếm & lọc sản phẩm, chọn biến thể (size, màu sắc), quản lý giỏ hàng, áp dụng mã giảm giá, thanh toán trực tuyến (VNPay, ZaloPay) & COD, quản lý lịch sử đơn hàng.
- **Phân hệ Quản trị (Back-end / Admin Panel):** Dashboard thống kê báo cáo, quản lý sản phẩm & biến thể, quản lý kho hàng & nhập hàng, quản lý mã giảm giá, quản lý đơn hàng & khách hàng.
- **Phân hệ Xử lý Tự động & Tích hợp (Integrations):** Cổng thanh toán VNPay Sandbox, ZaloPay Sandbox, Tự động quét và hủy đơn hàng quá hạn thanh toán chưa hoàn tất.

---

## 2. CÔNG NGHỆ & KIẾN TRÚC HỆ THỐNG

- **Ngôn ngữ Backend:** PHP 8.x (Thuần)
- **Cơ sở dữ liệu:** MySQL 8.0 (Hỗ trợ InnoDB, Foreign Keys, Transactions)
- **Giao diện Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5, FontAwesome
- **Tích hợp Thanh toán:**
  - **VNPay API / IPN Webhook** (Thanh toán qua Thẻ ATM / QR / Credit)
  - **ZaloPay API / Callback Webhook** (Thanh toán qua ví ZaloPay)
- **Môi trường & Triển khai:** Docker, Apache Server, Cron Jobs / Automated Task Triggers

---

## 3. VAI TRÒ & PHÂN QUYỀN NGHƯỜI DÙNG (ACTORS)

| Vai trò | Ký hiệu | Mô tả quyền hạn |
| :--- | :--- | :--- |
| **Khách ghé thăm** | Guest | Xem sản phẩm, tìm kiếm, lọc, xem chi tiết sản phẩm, thêm vào giỏ hàng tạm thời. |
| **Khách hàng** | Customer | Đăng ký/đăng nhập, lưu mã giảm giá, đặt hàng, chọn phương thức thanh toán, xem/hủy đơn hàng cá nhân, lưu giỏ hàng vào DB. |
| **Quản trị viên** | Admin | Toàn quyền quản trị hệ thống: Thống kê doanh thu, quản lý danh mục, sản phẩm, nhập kho, khuyến mãi, xử lý đơn hàng, quản lý tài khoản. |

---

## 4. ĐẶC TẢ CHI TIẾT CÁC CHỨC NĂNG PHÂN HỆ KHÁCH HÀNG

### 4.1. Phân hệ Tài khoản & Xác thực
- **UC-01: Đăng ký tài khoản (Register)**
  - *Mô tả:* Khách hàng tạo tài khoản mới bằng email, họ tên, số điện thoại, địa chỉ và mật khẩu.
  - *Ràng buộc:* Mật khẩu được mã hóa an toàn (`password_hash`), email phải là duy nhất.
- **UC-02: Đăng nhập (Login)**
  - *Mô tả:* Đăng nhập bằng Email và Mật khẩu. Hệ thống kiểm tra vai trò (`customer`) và trạng thái tài khoản.
  - *Ràng buộc:* Nếu tài khoản bị Admin khóa (`status = 'locked'`), hệ thống tự động đăng xuất và thông báo tài khoản bị vô hiệu hóa.
- **UC-03: Đăng xuất & Quản lý Session**
  - *Mô tả:* Đăng xuất khỏi hệ thống. Hỗ trợ Session riêng biệt giữa User và Admin (`USER_SESSION_NAME` & `ADMIN_SESSION_NAME`) cho phép đăng nhập đồng thời 2 vai trò trên cùng một trình duyệt.

### 4.2. Phân hệ Khám phá & Tìm kiếm Sản phẩm
- **UC-04: Trang chủ & Danh mục sản phẩm**
  - *Mô tả:* Hiển thị banner khuyến mãi, danh mục nổi bật, sản phẩm mới nhất, sản phẩm bán chạy.
- **UC-05: Tìm kiếm & Bộ lọc thông minh**
  - *Mô tả:* Lọc sản phẩm theo từ khóa tên, theo Danh mục (Nike, Adidas, Jordan...), theo khoảng giá (Price range).
- **UC-06: Chi tiết sản phẩm & Biến thể (Product Detail & Varieties)**
  - *Mô tả:* Hiển thị ảnh sản phẩm, mô tả, giá bán (tính tự động từ giá nhập + tỷ lệ lợi nhuận). Người dùng chọn **Kích thước (Size)** và **Màu sắc (Color)** để kiểm tra số lượng tồn kho khả dụng theo real-time.

### 4.3. Phân hệ Giỏ hàng (Cart Management)
- **UC-07: Thêm / Cập nhật / Xóa sản phẩm trong giỏ hàng**
  - *Mô tả:* Thêm sản phẩm kèm biến thể (Size, Màu) vào giỏ hàng. Cập nhật số lượng hoặc xóa sản phẩm.
- **UC-08: Đồng bộ giỏ hàng (Cart Synchronization)**
  - *Mô tả:* Khi người dùng chưa đăng nhập, giỏ hàng lưu trong Session. Khi đăng nhập, giỏ hàng được đồng bộ và lưu vào bảng `cart_items` trong CSDL.

### 4.4. Phân hệ Khuyến mãi & Mã giảm giá (Coupons & Discounts)
- **UC-09: Lưu mã giảm giá vào Ví Voucher**
  - *Mô tả:* Khách hàng có thể bấm lưu các mã giảm giá công khai vào ví cá nhân (`user_saved_coupons`).
- **UC-10: Áp dụng mã giảm giá khi đặt hàng**
  - *Mô tả:* Nhập hoặc chọn mã giảm giá tại trang thanh toán/giỏ hàng. Hệ thống tự động kiểm tra các điều kiện:
    - Giá trị đơn hàng tối thiểu (Minimum Order Amount).
    - Hạn sử dụng (Start Date / End Date).
    - Giới hạn lượt sử dụng tổng & lượt sử dụng/người dùng.
    - Scope áp dụng: Toàn bộ cửa hàng, danh mục cụ thể hoặc sản phẩm cụ thể.
    - Tính toán số tiền giảm (Giảm theo % có mức giảm tối đa hoặc Giảm tiền cố định).

### 4.5. Phân hệ Đặt hàng & Thanh toán (Checkout & Payment)
- **UC-11: Tạo đơn hàng (Checkout)**
  - *Mô tả:* Nhập thông tin người nhận (Họ tên, SĐT, Địa chỉ giao hàng, Ghi chú). Lựa chọn phương thức thanh toán.
  - *Ràng buộc:* Trừ số lượng tồn kho của biến thể sản phẩm tương ứng ngay khi tạo đơn hàng thành công.
- **UC-12: Thanh toán COD (Cash on Delivery)**
  - *Mô tả:* Đặt hàng thanh toán khi nhận hàng. Trạng thái đơn hàng khởi tạo: `pending` (Chờ xử lý).
- **UC-13: Thanh toán trực tuyến VNPay Sandbox**
  - *Mô tả:* Chuyển hướng người dùng sang Cổng thanh toán VNPay. Nhận kết quả qua `vnpay_return.php` và xử lý IPN tự động tại `vnpay_ipn.php` để cập nhật trạng thái đơn hàng thành `paid`.
- **UC-14: Thanh toán trực tuyến ZaloPay Sandbox**
  - *Mô tả:* Tạo đơn hàng qua ZaloPay API, chuyển hướng người dùng thanh toán qua cổng ZaloPay. Xử lý webhook kết quả tại `zalopay_callback.php` và `zalopay_return.php`.
- **UC-15: Tự động Hủy đơn hàng hết hạn thanh toán**
  - *Mô tả:* Đơn hàng chọn thanh toán online có mốc thời gian hết hạn (`payment_deadline`). Nếu sau 24h không hoàn tất thanh toán, hệ thống (Cron Script `cancel_expired_orders.php` hoặc Fallback Trigger) tự động chuyển trạng thái đơn hàng sang `cancelled` và hoàn lại số lượng tồn kho cho các biến thể sản phẩm.

### 4.6. Phân hệ Quản lý Đơn hàng Cá nhân
- **UC-16: Xem lịch sử & Chi tiết đơn hàng (`my_orders.php`)**
  - *Mô tả:* Khách hàng xem danh sách đơn hàng đã đặt, theo dõi trạng thái (Chờ thanh toán, Đã thanh toán, Đang giao, Đã hoàn thành, Đã hủy).
- **UC-17: Hủy đơn hàng**
  - *Mô tả:* Khách hàng có thể chủ động bấm Hủy đơn hàng nếu đơn hàng chưa chuyển sang trạng thái Đang giao/Đã hoàn thành. Số lượng tồn kho được hoàn trả tự động.

---

## 5. ĐẶC TẢ CHI TIẾT CÁC CHỨC NĂNG PHÂN HỆ QUẢN TRỊ (ADMIN PANEL)

### 5.1. Dashboard Thống kê & Báo cáo (`admin/index.php`)
- **UC-18: Thống kê tổng quan**
  - *Mô tả:* Hiển thị các chỉ số kinh doanh cốt lõi:
    - Tổng doanh thu (chỉ tính các đơn hàng đã hoàn tất/đã thanh toán).
    - Tổng số đơn hàng & Phân loại theo trạng thái.
    - Tổng số sản phẩm & Số biến thể sắp hết hàng (Low stock alert).
    - Tổng số tài khoản khách hàng.

### 5.2. Quản lý Danh mục Sản phẩm (`admin/categories.php`)
- **UC-19: CRUD Danh mục**
  - *Mô tả:* Thêm mới, Chỉnh sửa tên/mô tả danh mục, Xóa danh mục (kiểm tra ràng buộc dữ liệu sản phẩm liên quan).

### 5.3. Quản lý Sản phẩm (`admin/products.php`)
- **UC-20: Thêm & Chỉnh sửa sản phẩm**
  - *Mô tả:* Nhập tên sản phẩm, chọn danh mục, tải lên hình ảnh đại diện, nhập mô tả.
- **UC-21: Cấu hình Giá nhập & Tỷ lệ lợi nhuận (Automated Selling Price)**
  - *Mô tả:* Quản trị viên nhập **Giá nhập (Import Price)** và **Tỷ lệ lợi nhuận % (Profit Rate)**. Hệ thống tự động tính toán **Giá bán lẻ (Selling Price)** theo công thức:
    $$\text{Giá bán} = \text{Giá nhập} \times \left(1 + \frac{\text{Tỷ lệ lợi nhuận}}{100}\right)$$

### 5.4. Quản lý Tồn kho & Biến thể (`admin/inventory.php`)
- **UC-22: Quản lý Thuộc tính (Sizes & Colors)**
  - *Mô tả:* Thêm/sửa/xóa các kích thước giày (Size 38, 39, 40, 41...) và Màu sắc (Đen, Trắng, Đỏ...).
- **UC-23: Quản lý Biến thể sản phẩm (Product Varieties)**
  - *Mô tả:* Tạo sự kết hợp giữa **Sản phẩm + Size + Màu sắc**, thiết lập số lượng tồn kho ban đầu.
- **UC-24: Cảnh báo & Điều chỉnh Tồn kho**
  - *Mô tả:* Hiển thị danh sách các biến thể có số lượng tồn kho dưới ngưỡng cảnh báo để kịp thời lập phiếu nhập hàng.

### 5.5. Quản lý Nhập hàng (`admin/imports.php`)
- **UC-25: Tạo Phiếu nhập hàng (Import Receipt)**
  - *Mô tả:* Chọn nhà cung cấp/nguồn hàng, ngày nhập. Thêm danh sách các biến thể sản phẩm nhập vào kèm số lượng nhập và đơn giá nhập.
- **UC-26: Cập nhật Tồn kho & Giá nhập tự động**
  - *Mô tả:* Khi lưu phiếu nhập hàng:
    - Số lượng nhập tự động được cộng vào tồn kho của các biến thể tương ứng trong `product_varieties`.
    - Cập nhật lại giá nhập mới nhất cho sản phẩm chính trong `products`.

### 5.6. Quản lý Chương trình Khuyến mãi (`admin/discounts.php`)
- **UC-27: Tạo & Cấu hình Mã giảm giá**
  - *Mô tả:* Quản trị viên tạo mã voucher với các thuộc tính:
    - Loại giảm giá: Theo phần trăm (%) hoặc Số tiền cố định (VNĐ).
    - Giá trị giảm & Mức giảm tối đa.
    - Điều kiện: Đơn hàng tối thiểu.
    - Phạm vi áp dụng (Scope): Tất cả sản phẩm, Danh mục chỉ định, hoặc Sản phẩm chỉ định.
    - Thời gian hiệu lực: Ngày bắt đầu và Ngày kết thúc.
    - Giới hạn lượt dùng toàn hệ thống & Số lần dùng/khách hàng.

### 5.7. Quản lý Đơn hàng (`admin/orders.php`)
- **UC-28: Danh sách & Lọc đơn hàng**
  - *Mô tả:* Xem toàn bộ đơn hàng, lọc theo trạng thái (`pending`, `pending_payment`, `paid`, `shipping`, `completed`, `cancelled`).
- **UC-29: Cập nhật Trạng thái Đơn hàng**
  - *Mô tả:* Duyệt đơn hàng, chuyển trạng thái xử lý: Xác nhận -> Đang giao -> Hoàn thành. Hoặc Hủy đơn hàng nếu khách yêu cầu.
- **UC-30: Xem Chi tiết Đơn hàng & Thông tin Thanh toán**
  - *Mô tả:* Xem danh sách sản phẩm, biến thể size/color, mã giảm giá đã áp dụng, địa chỉ giao hàng, thông tin giao dịch qua VNPay / ZaloPay (Transaction No, Mã phản hồi).

### 5.8. Quản lý Người dùng (`admin/users.php`)
- **UC-31: Danh sách & Phân quyền Tài khoản**
  - *Mô tả:* Quản lý danh sách người dùng. Thay đổi vai trò người dùng giữa `customer` và `admin`.
- **UC-32: Khóa & Mở khóa Tài khoản (Account Locking)**
  - *Mô tả:* Chuyển trạng thái tài khoản thành `locked` để chặn quyền truy cập đối với các tài khoản vi phạm. Khi tài khoản bị khóa, hệ thống sẽ đá (kick) người dùng ra khỏi phiên làm việc ngay lập tức.

---

## 6. SƠ ĐỒ & CẤU TRÚC DỮ LIỆU CHÍNH (DATABASE SCHEMA)

| Tên Bảng | Chức năng chính |
| :--- | :--- |
| `users` | Lưu thông tin tài khoản (Họ tên, email, password hash, sđt, địa chỉ, role, status). |
| `categories` | Danh mục sản phẩm (Nike, Adidas, Puma...). |
| `colors` | Danh mục màu sắc sản phẩm. |
| `sizes` | Danh mục kích thước giày. |
| `products` | Thông tin sản phẩm (Tên, mô tả, ảnh, giá nhập, tỷ lệ lợi nhuận, category_id). |
| `product_varieties` | Biến thể sản phẩm (product_id, size_id, color_id, stock_quantity). |
| `import_receipts` | Phiếu nhập hàng (Mã phiếu, ngày nhập, người nhập, tổng tiền). |
| `import_details` | Chi tiết phiếu nhập hàng (import_id, variety_id, quantity, import_price). |
| `discount_codes` | Thông tin mã giảm giá, điều kiện áp dụng, hạn dùng. |
| `discount_code_categories` | Bảng liên kết áp dụng mã giảm giá cho danh mục cụ thể. |
| `discount_code_products` | Bảng liên kết áp dụng mã giảm giá cho sản phẩm cụ thể. |
| `user_saved_coupons` | Ví lưu mã giảm giá của từng người dùng. |
| `discount_code_usages` | Nơi ghi nhận lịch sử khách hàng đã dùng mã giảm giá nào cho đơn hàng nào. |
| `orders` | Đơn hàng (Mã đơn, user_id, tổng tiền, phương thức TT, trạng thái TT, trạng thái đơn, payment_deadline). |
| `order_details` | Chi tiết đơn hàng (order_id, product_id, size_id, color_id, quantity, price). |
| `cart_items` | Giỏ hàng lưu trong CSDL của người dùng đã đăng nhập. |

---

## 7. YÊU CẦU PHI CHỨC NĂNG (NON-FUNCTIONAL REQUIREMENTS)

### 7.1. Bảo mật (Security)
- Mã hóa mật khẩu người dùng bằng chuẩn `password_hash()` (BCRYPT).
- Chống tấn công SQL Injection bằng cách chuẩn hóa dữ liệu với `real_escape_string` / Prepared Statements.
- Phân tách Session giữa Admin và Customer để tránh xung đột tài khoản.
- Tự động hủy phiên đăng nhập khi tài khoản bị vô hiệu hóa (`status = 'locked'`).
- Kiểm tra tính hợp lệ của Chữ ký số (Checksum/HMAC SHA512/SHA256) đối với các Callback thanh toán từ VNPay và ZaloPay.

### 7.2. Hiệu năng & Khả năng mở rộng (Performance & Scalability)
- Tối ưu CSDL với chỉ mục (Index) trên các khóa ngoại và các trường tìm kiếm thường xuyên.
- Thiết lập containerization bằng **Docker** giúp dễ dàng đóng gói và triển khai lên các hạ tầng Cloud (Render, Railway, AWS).

### 7.3. Trải nghiệm người dùng (Usability & UX)
- Giao diện đáp ứng (Responsive Design) tương thích tốt trên Desktop, Tablet và Mobile nhờ Bootstrap 5.
- Cập nhật số lượng tồn kho và áp dụng mã giảm giá linh hoạt qua AJAX/API endpoints.

---
*Tài liệu đặc tả chức năng được tổng hợp và xây dựng dựa trên mã nguồn thực tế của dự án **Sneaker Shop**.*
