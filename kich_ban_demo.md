# KỊCH BẢN DEMO ĐỒ ÁN MÔN HỌC
## Dự án: Website Thương Mại Điện Tử Bán Giày Sneaker (Sneaker Shop)
**Thời lượng dự kiến:** 10 - 15 phút  
**Người thực hiện:** Sinh viên báo cáo  

---

## 📋 THỜI GIAN VÀ CÁC PHÂN ĐOẠN DEMO

| Thời lượng | Phân đoạn Demo | Nội dung chính |
| :--- | :--- | :--- |
| **01 - 02 phút** | **Phần 1: Mở đầu & Tổng quan** | Giới thiệu tên dự án, bài toán thực tế, công nghệ sử dụng và kiến trúc hệ thống. |
| **04 - 05 phút** | **Phần 2: Phân hệ Khách hàng (User)** | Tìm kiếm, xem biến thể size/color, giỏ hàng, áp dụng voucher, đặt hàng & thanh toán VNPay/ZaloPay. |
| **04 - 05 phút** | **Phần 3: Phân hệ Quản trị (Admin)** | Dashboard báo cáo, quản lý giá nhập/% lợi nhuận, phiếu nhập hàng, quản lý mã giảm giá, xử lý đơn hàng & khóa tài khoản. |
| **02 - 03 phút** | **Phần 4: Điểm nổi bật & Trả lời Q&A** | Tính năng tự động quét hủy đơn hết hạn, bảo mật session, giải trình kiến trúc database & mã nguồn. |

---

## 🚀 CHI TIẾT KỊCH BẢN THEO TỪNG BƯỚC

### 1. PHẦN MỞ ĐẦU (1 - 2 phút)
* **Lời thoại mở đầu:**
  > *"Em xin chào Thầy/Cô! Hôm nay em xin phép demo sản phẩm Đồ án môn học: Website thương mại điện tử bán giày Sneaker (Sneaker Shop).*  
  > *Dự án được xây dựng bằng ngôn ngữ PHP 8.x, CSDL MySQL 8.0, giao diện Bootstrap 5 và có tích hợp các cổng thanh toán trực tuyến như VNPay và ZaloPay.*  
  > *Hệ thống giải quyết bài toán quản lý biến thể sản phẩm phức tạp (như Size, Màu sắc), tự động tính giá bán theo tỷ lệ lợi nhuận, quản lý tồn kho qua phiếu nhập hàng và phân tách phân quyền an toàn giữa Khách hàng và Admin."*

---

### 2. PHẦN DEMO KHÁCH HÀNG / FRONT-END (4 - 5 phút)

#### **Bước 1: Khám phá Trang chủ & Tìm kiếm Sản phẩm**
* **Thao tác:** 
  1. Mở trang chủ: `http://localhost:8080/TMDT-UD_sneaker_shop/`
  2. Bấm vào ô Tìm kiếm hoặc lọc theo Danh mục (ví dụ: *Nike*, *Adidas*).
  3. Lọc sản phẩm theo khoảng giá.
* **Lời thuyết minh:**
  > *"Đầu tiên là giao diện Trang chủ dành cho Khách hàng. Khách hàng có thể tìm kiếm sản phẩm theo tên, danh mục hoặc lọc theo mức giá mong muốn."*

#### **Bước 2: Xem Chi tiết Sản phẩm & Chọn Biến thể (Size & Màu sắc)**
* **Thao tác:**
  1. Bấm vào một sản phẩm (Ví dụ: *Nike Air Force 1*).
  2. Thử bấm chọn các nút **Kích thước (Size)** và **Màu sắc (Color)** khác nhau.
  3. Chỉ ra số lượng tồn kho khả dụng thay đổi theo real-time.
* **Lời thuyết minh:**
  > *"Đặc thù của ngành thời trang giày là mỗi sản phẩm có nhiều biến thể Size và Màu sắc. Hệ thống của em lưu trữ theo mô hình Biến thể (Product Variety). Khi khách chọn đúng Size 41 và Màu Đen, hệ thống sẽ truy vấn số lượng tồn kho thực tế của đúng biến thể đó."*

#### **Bước 3: Thêm Giỏ hàng & Đồng bộ CSDL**
* **Thao tác:**
  1. Thêm 1-2 sản phẩm kèm biến thể vào Giỏ hàng.
  2. Đăng nhập bằng tài khoản khách hàng: `nguyenvana` / mật khẩu `password`.
  3. Giải thích giỏ hàng được đồng bộ tự động từ Session sang CSDL (`cart_items`).
* **Lời thuyết minh:**
  > *"Giỏ hàng hỗ trợ lưu tạm khi khách chưa đăng nhập. Khi khách đăng nhập, giỏ hàng trong Session sẽ tự động được đồng bộ lưu vào Cơ sở dữ liệu để khách có thể tiếp tục mua sắm trên nhiều thiết bị."*

#### **Bước 4: Nhận Voucher & Áp dụng Mã giảm giá**
* **Thao tác:**
  1. Bấm vào nút **Nổi (Floating Button)** "Kho Voucher" ở góc dưới màn hình.
  2. Bấm **"Lưu mã"** một voucher (VD: `DISCOUNT10`).
  3. Vào trang Thanh toán (Checkout), nhập/chọn mã giảm giá để thấy số tiền tổng đơn hàng giảm ngay lập tức.
* **Lời thuyết minh:**
  > *"Hệ thống hỗ trợ kho Voucher đa dạng. Khách hàng có thể lưu voucher vào ví cá nhân. Khi đặt hàng, hệ thống sẽ tự động kiểm tra các điều kiện: Đơn hàng tối thiểu, hạn sử dụng, phạm vi áp dụng (cho toàn shop hay danh mục/sản phẩm cụ thể) và tính toán số tiền giảm giá chính xác."*

#### **Bước 5: Đặt hàng & Thanh toán (COD & VNPay/ZaloPay)**
* **Thao tác:**
  1. Nhập thông tin giao hàng.
  2. Chọn phương thức thanh toán trực tuyến **VNPay** (hoặc **ZaloPay**).
  3. Bấm Đặt hàng -> Chuyển sang Cổng thanh toán Sandbox -> Thực hiện giả lập thanh toán thành công -> Chuyển về trang kết quả đơn hàng.
* **Lời thuyết minh:**
  > *"Hệ thống tích hợp cổng thanh toán trực tuyến VNPay Sandbox. Khi khách thanh toán thành công, VNPay gửi phản hồi (Return URL/IPN) để hệ thống tự động cập nhật trạng thái đơn hàng thành 'Đã thanh toán' (Paid) và lập tức trừ tồn kho biến thể."*

#### **Bước 6: Theo dõi Đơn hàng Cá nhân**
* **Thao tác:** Truy cập `my_orders.php` để xem danh sách đơn hàng vừa đặt, trạng thái đơn và nút Hủy đơn hàng.

---

### 3. PHẦN DEMO QUẢN TRỊ / ADMIN PANEL (4 - 5 phút)

#### **Bước 1: Đăng nhập Admin thông minh**
* **Thao tác:**
  1. Mở trang đăng nhập: `http://localhost:8080/TMDT-UD_sneaker_shop/login.php`
  2. Nhập `admin` / `password`.
  3. Nhấn Đăng nhập -> Hệ thống tự động chuyển thẳng tới Dashboard Admin.
* **Lời thuyết minh:**
  > *"Trang đăng nhập của hệ thống hỗ trợ phân loại tự động. Khi tài khoản Admin đăng nhập từ trang login chính, hệ thống tự động chuyển sang phiên làm việc Admin và đưa người dùng vào Dashboard Quản trị."*

#### **Bước 2: Dashboard Thống kê Báo cáo**
* **Thao tác:** Trình chiếu các thẻ chỉ số (Tổng doanh thu, Tổng đơn hàng, Số khách hàng, Khai báo sản phẩm sắp hết hàng - Low Stock Alert).
* **Lời thuyết minh:**
  > *"Tại trang Dashboard, Quản trị viên có cái nhìn tổng quan về Doanh thu thực tế, tổng đơn hàng, cùng danh sách các sản phẩm sắp hết hàng để chủ động nhập bổ sung."*

#### **Bước 3: Quản lý Sản phẩm & Công thức Tự động Tính Giá Bán**
* **Thao tác:** 
  1. Vào mục **Sản phẩm** (`admin/products.php`).
  2. Mở form Thêm/Sửa sản phẩm. Chỉ ra 2 trường: **Giá nhập (Import Price)** và **Tỷ lệ lợi nhuận (% Profit Rate)**.
* **Lời thuyết minh:**
  > *"Điểm đặc biệt trong quản lý sản phẩm là hệ thống không nhập cứng Giá bán. Quản trị viên chỉ cần nhập Giá nhập và Tỷ lệ lợi nhuận mong muốn (VD: Giá nhập 1,000,000đ, Lợi nhuận 30%), hệ thống sẽ tự động tính Giá bán lẻ là 1,300,000đ."*

#### **Bước 4: Quản lý Tồn kho & Tạo Phiếu nhập hàng (Import Receipt)**
* **Thao tác:**
  1. Vào mục **Nhập hàng** (`admin/imports.php`).
  2. Tạo một Phiếu nhập mới: Chọn các biến thể sản phẩm, nhập số lượng nhập và đơn giá nhập.
  3. Bấm Lưu -> Chỉ ra tồn kho của các biến thể tương ứng trong `admin/inventory.php` được tự động cộng thêm.
* **Lời thuyết minh:**
  > *"Khi cửa hàng nhập hàng về, Admin tạo Phiếu nhập hàng. Hệ thống sẽ tự động cập nhật cộng thêm số lượng vào kho của từng biến thể sản phẩm, đồng thời cập nhật lại giá nhập mới nhất."*

#### **Bước 5: Quản lý Mã giảm giá (Discounts)**
* **Thao tác:** Vào `admin/discounts.php`, chỉ ra cách tạo mã giảm giá theo %, số tiền cố định, mốc giảm tối đa và phạm vi áp dụng.

#### **Bước 6: Quản lý Đơn hàng & Duyệt đơn**
* **Thao tác:**
  1. Vào `admin/orders.php`.
  2. Chuyển trạng thái đơn hàng vừa đặt ở phần demo trước từ `Chờ xử lý` -> `Đã xác nhận` -> `Đang giao`.
  3. Cho xem mã giao dịch VNPay/ZaloPay lưu trong chi tiết đơn hàng.

#### **Bước 7: Quản lý Người dùng & Khóa tài khoản Real-time**
* **Thao tác:**
  1. Vào `admin/users.php`.
  2. Đổi trạng thái tài khoản khách hàng sang `locked` (Khóa).
  3. Thử quay lại trình duyệt của khách hàng -> Bấm F5 hoặc chuyển trang -> Hệ thống tự động **Kick (đăng xuất)** và báo tài khoản bị vô hiệu hóa.
* **Lời thuyết minh:**
  > *"Về mặt bảo mật, hệ thống có cơ chế kiểm tra tự động (`kickIfLocked`). Nếu Admin khóa một tài khoản đang đăng nhập, ngay tại request tiếp theo, phiên làm việc của người dùng đó sẽ bị hủy ngay lập tức để đảm bảo an toàn."*

---

### 4. KẾT THÚC & ĐIỂM NỔI BẬT KỸ THUẬT (2 phút)
* **Lời thoại kết thúc:**
  > *"Tóm lại, dự án Sneaker Shop của em có các điểm mạnh kỹ thuật chính bao gồm:*  
  > 1. *Quản lý biến thể đa chiều (Sản phẩm - Size - Màu sắc - Tồn kho).*  
  > 2. *Tự động tính giá bán theo tỷ lệ lợi nhuận & tự động cập nhật tồn kho qua phiếu nhập.*  
  > 3. *Tích hợp Cổng thanh toán trực tuyến VNPay & ZaloPay Sandbox hoàn chỉnh.*  
  > 4. *Tính năng tự động quét và hủy đơn hàng hết hạn thanh toán online sau 24h để hoàn lại tồn kho.*  
  > 5. *Bảo mật phiên làm việc (Session isolation), mã hóa mật khẩu BCRYPT và kiểm tra khóa tài khoản real-time.*  
  >  
  > *Em xin cảm ơn Thầy/Cô đã lắng nghe. Em rất mong nhận được ý kiến đóng góp của Thầy/Cô để hoàn thiện dự án ạ!"*

---

## ❓ CÁC CÂU HỎI GIẢNG VIÊN THƯỜNG HỎI & CÁCH TRẢ LỜI MẪU (Q&A)

#### ❓ **Câu 1: Làm sao hệ thống xử lý khi 2 người cùng bấm mua 1 đôi giày cuối cùng cùng một lúc?**
* **Trả lời:**  
  > *"Thưa Thầy/Cô, khi thực hiện đặt hàng, hệ thống sử dụng **Database Transaction (`begin_transaction`, `commit`, `rollback`)** trong MySQL. Khi người đầu tiên hoàn tất checkout, query `UPDATE product_varieties SET stock_quantity = stock_quantity - qty` sẽ thực thi trước. Người thứ hai gửi request sau sẽ bị kiểm tra điều kiện tồn kho không đủ và hệ thống rollback giao dịch, báo lỗi cho người thứ hai."*

#### ❓ **Câu 2: Làm sao thanh toán online VNPay/ZaloPay hoạt động được trên localhost?**
* **Trả lời:**  
  > *"Thưa Thầy/Cô, VNPay và ZaloPay cung cấp môi trường Sandbox thử nghiệm. Khi tạo đơn thanh toán, hệ thống tạo chữ ký mã hóa Checksum (HMAC SHA512) gửi tới URL Sandbox của VNPay/ZaloPay. Sau khi thanh toán trên cổng test, cổng sẽ redirect về `vnpay_return.php` / `zalopay_return.php` trên trình duyệt để kiểm tra chữ ký và cập nhật trạng thái đơn hàng."*

#### ❓ **Câu 3: Đơn hàng hết hạn thanh toán online được xử lý như thế nào?**
* **Trả lời:**  
  > *"Thưa Thầy/Cô, mỗi đơn hàng thanh toán online được gán `payment_deadline` (hạn 24 giờ). Hệ thống có 2 cơ chế xử lý: File script `cancel_expired_orders.php` chạy định kỳ (Cron job), và một cơ chế fallback tự động quét mỗi khi có lưu lượng truy cập web. Nếu phát hiện đơn hàng quá hạn chưa thanh toán, hệ thống sẽ tự động cập nhật đơn sang `cancelled` và hoàn lại số lượng sản phẩm vào kho."*

#### ❓ **Câu 4: Mật khẩu người dùng được lưu trữ và bảo mật thế nào?**
* **Trả lời:**  
  > *"Thưa Thầy/Cô, mật khẩu không bao giờ lưu dạng văn bản thô (plain text). Hệ thống sử dụng hàm `password_hash()` của PHP với thuật toán BCRYPT để mã hóa 1 chiều trước khi lưu vào bảng `users`, và dùng `password_verify()` để kiểm tra khi đăng nhập."*
