-- ============================================
-- SNEAKER SHOP DATABASE - Full Reset & Sample Data
-- ============================================

DROP DATABASE IF EXISTS sneaker_shop;
CREATE DATABASE sneaker_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sneaker_shop;

-- ============================================
-- CẤU TRÚC BẢNG
-- ============================================

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(200) NOT NULL,
    category_id INT NOT NULL,
    description TEXT,
    unit VARCHAR(50) DEFAULT 'đôi',
    stock_quantity INT DEFAULT 0,
    import_price DECIMAL(15,2) DEFAULT 0,
    profit_rate DECIMAL(5,2) DEFAULT 30.00,
    image VARCHAR(255),
    brand VARCHAR(100),
    gender ENUM('nam','nu','unisex') DEFAULT 'unisex',
    available_sizes VARCHAR(255) DEFAULT '',
    color VARCHAR(100),
    material VARCHAR(200),
    origin VARCHAR(100),
    status ENUM('active','hidden') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(200),
    email VARCHAR(200),
    phone VARCHAR(20),
    address TEXT,
    ward VARCHAR(100),
    district VARCHAR(100),
    city VARCHAR(100),
    role ENUM('customer','admin') DEFAULT 'customer',
    status ENUM('active','locked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE import_receipts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receipt_code VARCHAR(50) UNIQUE NOT NULL,
    import_date DATE NOT NULL,
    notes TEXT,
    status ENUM('pending','completed') DEFAULT 'pending',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE import_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receipt_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    import_price DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (receipt_id) REFERENCES import_receipts(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_code VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    receiver_name VARCHAR(200),
    receiver_phone VARCHAR(20),
    shipping_address TEXT,
    ward VARCHAR(100),
    district VARCHAR(100),
    city VARCHAR(100),
    payment_method ENUM('cash','transfer','online') DEFAULT 'cash',
    total_amount DECIMAL(15,2) DEFAULT 0,
    status ENUM('pending','confirmed','delivered','cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE order_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- ============================================
-- DỮ LIỆU MẪU
-- ============================================

-- Admin (password: 123456)
INSERT INTO users (username, password, full_name, email, phone, address, ward, district, city, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Quản Trị Viên', 'admin@sneakershop.vn', '0901000001', '1 Lê Lợi', 'Bến Nghé', 'Quận 1', 'TP. Hồ Chí Minh', 'admin');

-- Khách hàng mẫu (password: 123456)
INSERT INTO users (username, password, full_name, email, phone, address, ward, district, city, role) VALUES
('nguyenvana',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn A',  'vana@email.com',    '0901234567', '12 Nguyễn Huệ',   'Bến Nghé',  'Quận 1', 'TP. Hồ Chí Minh', 'customer'),
('tranthib',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trần Thị B',    'thib@email.com',    '0912345678', '45 Lê Văn Sỹ',    'Phường 12', 'Quận 3', 'TP. Hồ Chí Minh', 'customer'),
('lehoanghung', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lê Hoàng Hùng', 'hoanghung@email.com','0923456789', '78 Hoàng Diệu',   'Phường 9',  'Quận 4', 'TP. Hồ Chí Minh', 'customer');

-- Danh mục
INSERT INTO categories (name, description) VALUES
('Sneaker Thể Thao',   'Giày sneaker dành cho hoạt động thể thao'),
('Sneaker Lifestyle',  'Giày sneaker thời trang hàng ngày'),
('Sneaker Running',    'Giày chạy bộ chuyên dụng hiệu năng cao'),
('Sneaker Basketball', 'Giày bóng rổ chuyên nghiệp'),
('Sneaker Skateboard', 'Giày trượt ván bền bỉ');

-- 20 Sản phẩm mẫu
-- stock_quantity sẽ = 0 ban đầu, được cập nhật tự động khi hoàn thành phiếu nhập
INSERT INTO products (code, name, category_id, description, unit, stock_quantity, import_price, profit_rate, brand, gender, available_sizes, color, material, origin, status) VALUES
('NK001', 'Nike Air Force 1 Low',       2, 'Giày Nike Air Force 1 Low cổ điển, thiết kế trắng tinh khiết không bao giờ lỗi mốt.',           'đôi', 0, 1500000, 40.00, 'Nike',    'unisex', '36,37,38,39,40,41,42,43',    'Trắng',           'Da tổng hợp cao cấp',      'Việt Nam',    'active'),
('NK002', 'Nike Air Max 270',           1, 'Đệm khí Air Max lớn nhất từ trước đến nay, mang lại cảm giác êm ái tuyệt vời cả ngày dài.',       'đôi', 0, 2000000, 35.00, 'Nike',    'unisex', '38,39,40,41,42,43,44',       'Đen/Trắng',       'Vải Flyknit & da tổng hợp','Indonesia',   'active'),
('NK003', 'Nike Air Max 97',            1, 'Thiết kế gợn sóng biểu tượng từ năm 1997, đệm khí toàn phần cực kỳ êm.',                          'đôi', 0, 2200000, 38.00, 'Nike',    'unisex', '38,39,40,41,42,43',          'Bạc/Đỏ',          'Da tổng hợp & lưới',       'Trung Quốc',  'active'),
('NK004', 'Nike React Infinity Run',    3, 'Giày chạy bộ với đệm React cực êm, hỗ trợ chống chấn thương hiệu quả.',                           'đôi', 0, 2600000, 32.00, 'Nike',    'unisex', '38,39,40,41,42,43,44',       'Xanh/Trắng',      'Vải mesh thoáng khí',      'Việt Nam',    'active'),
('NK005', 'Nike Dunk Low Retro',        2, 'Phiên bản Retro của Nike Dunk Low, màu sắc tươi tắn, kết hợp hoàn hảo với mọi outfit.',            'đôi', 0, 1800000, 42.00, 'Nike',    'unisex', '36,37,38,39,40,41,42,43',    'Trắng/Xanh',      'Da thật',                  'Indonesia',   'active'),
('AD001', 'Adidas Stan Smith',          2, 'Biểu tượng thời trang đường phố suốt 50 năm qua, thiết kế đơn giản mà tinh tế.',                   'đôi', 0, 1200000, 45.00, 'Adidas',  'unisex', '36,37,38,39,40,41,42',       'Trắng/Xanh lá',   'Da tự nhiên',              'Trung Quốc',  'active'),
('AD002', 'Adidas Ultraboost 22',       3, 'Công nghệ Boost đẳng cấp trả lại năng lượng mỗi bước chạy, lý tưởng cho runner nghiêm túc.',       'đôi', 0, 2500000, 30.00, 'Adidas',  'unisex', '38,39,40,41,42,43,44',       'Đen/Xanh navy',   'Vải Primeknit+',           'Việt Nam',    'active'),
('AD003', 'Adidas NMD R1',             2, 'Phong cách street fashion tối thượng với đệm Boost thoải mái, phù hợp mọi hoàn cảnh.',              'đôi', 0, 1900000, 36.00, 'Adidas',  'unisex', '38,39,40,41,42,43',          'Xám/Đỏ',          'Vải Primeknit',             'Đức',         'active'),
('AD004', 'Adidas Gazelle',             2, 'Phong cách retro 70s, mũi giày da mềm mại, lót lưỡi gà đặc trưng tạo nên nét huyền thoại.',       'đôi', 0, 1400000, 40.00, 'Adidas',  'unisex', '36,37,38,39,40,41,42',       'Xanh navy',       'Da lộn (suede)',            'Ấn Độ',       'active'),
('AD005', 'Adidas Samba OG',            2, 'Cú trở lại ngoạn mục của dòng Samba huyền thoại, chiếm lĩnh street style toàn cầu.',               'đôi', 0, 1600000, 38.00, 'Adidas',  'unisex', '36,37,38,39,40,41,42,43',    'Đen/Trắng',       'Da tự nhiên',              'Ấn Độ',       'active'),
('JD001', 'Jordan 1 Retro High OG',    4, 'Huyền thoại bóng rổ trở thành biểu tượng streetwear, thiết kế Bred classic không đổi theo thời gian.','đôi', 0, 3000000, 50.00, 'Jordan',  'nam',    '40,41,42,43,44,45',          'Đỏ/Trắng/Đen',    'Da thật cao cấp',          'Trung Quốc',  'active'),
('JD002', 'Jordan 4 Retro',            4, 'Air Jordan 4 với thiết kế lưới đặc trưng và Air unit ở đế giữa, một trong những AJ được yêu thích nhất.', 'đôi', 0, 3500000, 48.00, 'Jordan', 'nam', '40,41,42,43,44,45',         'Trắng/Xám',       'Da thật & lưới',           'Trung Quốc',  'active'),
('JD003', 'Jordan 11 Retro Low',       4, 'Thiết kế patent leather bóng loáng sang trọng kết hợp outsole trong suốt, cực kỳ đặc biệt.',       'đôi', 0, 3200000, 45.00, 'Jordan',  'nam',    '40,41,42,43,44,45',          'Đen/Varsity Royal','Da bóng & vải',           'Trung Quốc',  'active'),
('NB001', 'New Balance 574',            2, 'Dòng giày biểu tượng từ thập niên 80, comfort từng bước chân với đế ENCAP độc quyền.',             'đôi', 0, 1700000, 38.00, 'New Balance','unisex','37,38,39,40,41,42,43',      'Xanh navy/Trắng', 'Da lộn & vải',             'Mỹ',          'active'),
('NB002', 'New Balance 990v6',          3, 'Made in USA, chất lượng đỉnh cao, đệm êm ái chuẩn mực cho runner nghiêm túc.',                     'đôi', 0, 3800000, 35.00, 'New Balance','unisex','38,39,40,41,42,43,44',      'Xám',             'Da lộn & lưới cao cấp',    'Mỹ',          'active'),
('VN001', 'Vans Old Skool',             5, 'Giày skate huyền thoại với sọc jazz đặc trưng, đế waffle bám đường tuyệt vời.',                    'đôi', 0, 1100000, 45.00, 'Vans',    'unisex', '36,37,38,39,40,41,42,43',    'Đen/Trắng',       'Canvas & da lộn',          'Việt Nam',    'active'),
('VN002', 'Vans Sk8-Hi',               5, 'Cổ cao hỗ trợ mắt cá chân, padding dày, lý tưởng cho ván trượt và street style.',                  'đôi', 0, 1200000, 43.00, 'Vans',    'unisex', '36,37,38,39,40,41,42,43,44', 'Trắng/Đen',       'Canvas',                   'Trung Quốc',  'active'),
('PU001', 'Puma RS-X',                 1, 'Running System tái sinh đầy màu sắc, đế dày chunky cực hot, công nghệ RS đệm êm.',                  'đôi', 0, 1600000, 40.00, 'Puma',    'unisex', '38,39,40,41,42,43',          'Trắng/Vàng/Đỏ',   'Da tổng hợp & lưới',       'Việt Nam',    'active'),
('AS001', 'ASICS Gel-Kayano 30',       3, 'Kiểm soát motion tối ưu với GEL technology, lý tưởng cho overpronation.',                           'đôi', 0, 2800000, 30.00, 'ASICS',   'nu',     '35,36,37,38,39,40,41',       'Tím/Hồng',        'FlyteFoam & lưới',         'Việt Nam',    'active'),
('CF001', 'Converse Chuck Taylor All Star','2','Đôi giày vải huyền thoại có mặt trên hơn 100 quốc gia, không bao giờ lỗi thời.',              'đôi', 0, 900000,  50.00, 'Converse', 'unisex', '36,37,38,39,40,41,42,43',    'Đen',             'Canvas',                   'Việt Nam',    'active');

-- ============================================
-- PHIẾU NHẬP KHO (mỗi sản phẩm có phiếu nhập)
-- ============================================

-- Phiếu 1: Nhập Nike & Adidas - 2025-10-01
INSERT INTO import_receipts (receipt_code, import_date, notes, status, created_by) VALUES
('PN2025100101', '2025-10-01', 'Nhập hàng đầu kỳ Q4/2025 - Nike & Adidas', 'completed', 1);

INSERT INTO import_details (receipt_id, product_id, quantity, import_price) VALUES
(1, 1, 60, 1500000),  -- NK001 Nike Air Force 1
(1, 2, 40, 2000000),  -- NK002 Nike Air Max 270
(1, 3, 35, 2200000),  -- NK003 Nike Air Max 97
(1, 6, 50, 1200000),  -- AD001 Adidas Stan Smith
(1, 7, 35, 2500000);  -- AD002 Adidas Ultraboost 22

-- Phiếu 2: Nhập Jordan & New Balance - 2025-10-15
INSERT INTO import_receipts (receipt_code, import_date, notes, status, created_by) VALUES
('PN2025101501', '2025-10-15', 'Nhập Jordan & New Balance theo đơn đặt hàng', 'completed', 1);

INSERT INTO import_details (receipt_id, product_id, quantity, import_price) VALUES
(2, 11, 25, 3000000), -- JD001 Jordan 1
(2, 12, 20, 3500000), -- JD002 Jordan 4
(2, 13, 18, 3200000), -- JD003 Jordan 11
(2, 14, 30, 1700000), -- NB001 New Balance 574
(2, 15, 20, 3800000); -- NB002 New Balance 990

-- Phiếu 3: Nhập Nike bổ sung & Vans - 2025-11-01
INSERT INTO import_receipts (receipt_code, import_date, notes, status, created_by) VALUES
('PN2025110101', '2025-11-01', 'Nhập bổ sung Nike Dunk & Vans chuẩn bị cuối năm', 'completed', 1);

INSERT INTO import_details (receipt_id, product_id, quantity, import_price) VALUES
(3, 4,  35, 2600000), -- NK004 Nike React
(3, 5,  40, 1800000), -- NK005 Nike Dunk Low
(3, 16, 45, 1100000), -- VN001 Vans Old Skool
(3, 17, 35, 1200000); -- VN002 Vans Sk8-Hi

-- Phiếu 4: Nhập Adidas bổ sung, Puma, ASICS, Converse - 2025-11-15
INSERT INTO import_receipts (receipt_code, import_date, notes, status, created_by) VALUES
('PN2025111501', '2025-11-15', 'Đa thương hiệu: Adidas NMD, Samba, Puma, ASICS, Converse', 'completed', 1);

INSERT INTO import_details (receipt_id, product_id, quantity, import_price) VALUES
(4, 8,  30, 1900000), -- AD003 Adidas NMD
(4, 9,  35, 1400000), -- AD004 Adidas Gazelle
(4, 10, 40, 1600000), -- AD005 Adidas Samba
(4, 18, 30, 1600000), -- PU001 Puma RS-X
(4, 19, 25, 2800000), -- AS001 ASICS
(4, 20, 50, 900000);  -- CF001 Converse

-- Phiếu 5: Nhập thêm Q1/2026 (một phiếu đang pending)
INSERT INTO import_receipts (receipt_code, import_date, notes, status, created_by) VALUES
('PN2026010101', '2026-01-10', 'Nhập hàng Q1/2026 - chưa hoàn thành', 'pending', 1);

INSERT INTO import_details (receipt_id, product_id, quantity, import_price) VALUES
(5, 1, 30, 1550000), -- NK001
(5, 6, 25, 1250000); -- AD001

-- ============================================
-- CẬP NHẬT TỒN KHO (chỉ phiếu đã hoàn thành)
-- Tính giá nhập bình quân đồng thời
-- ============================================

-- NK001: nhập 60 @ 1,500,000 → stock=60, import_price=1,500,000
UPDATE products SET stock_quantity=60,  import_price=1500000 WHERE id=1;
-- NK002: nhập 40 @ 2,000,000
UPDATE products SET stock_quantity=40,  import_price=2000000 WHERE id=2;
-- NK003: nhập 35 @ 2,200,000
UPDATE products SET stock_quantity=35,  import_price=2200000 WHERE id=3;
-- NK004: nhập 35 @ 2,600,000
UPDATE products SET stock_quantity=35,  import_price=2600000 WHERE id=4;
-- NK005: nhập 40 @ 1,800,000
UPDATE products SET stock_quantity=40,  import_price=1800000 WHERE id=5;
-- AD001: nhập 50 @ 1,200,000
UPDATE products SET stock_quantity=50,  import_price=1200000 WHERE id=6;
-- AD002: nhập 35 @ 2,500,000
UPDATE products SET stock_quantity=35,  import_price=2500000 WHERE id=7;
-- AD003: nhập 30 @ 1,900,000
UPDATE products SET stock_quantity=30,  import_price=1900000 WHERE id=8;
-- AD004: nhập 35 @ 1,400,000
UPDATE products SET stock_quantity=35,  import_price=1400000 WHERE id=9;
-- AD005: nhập 40 @ 1,600,000
UPDATE products SET stock_quantity=40,  import_price=1600000 WHERE id=10;
-- JD001: nhập 25 @ 3,000,000
UPDATE products SET stock_quantity=25,  import_price=3000000 WHERE id=11;
-- JD002: nhập 20 @ 3,500,000
UPDATE products SET stock_quantity=20,  import_price=3500000 WHERE id=12;
-- JD003: nhập 18 @ 3,200,000
UPDATE products SET stock_quantity=18,  import_price=3200000 WHERE id=13;
-- NB001: nhập 30 @ 1,700,000
UPDATE products SET stock_quantity=30,  import_price=1700000 WHERE id=14;
-- NB002: nhập 20 @ 3,800,000
UPDATE products SET stock_quantity=20,  import_price=3800000 WHERE id=15;
-- VN001: nhập 45 @ 1,100,000
UPDATE products SET stock_quantity=45,  import_price=1100000 WHERE id=16;
-- VN002: nhập 35 @ 1,200,000
UPDATE products SET stock_quantity=35,  import_price=1200000 WHERE id=17;
-- PU001: nhập 30 @ 1,600,000
UPDATE products SET stock_quantity=30,  import_price=1600000 WHERE id=18;
-- AS001: nhập 25 @ 2,800,000
UPDATE products SET stock_quantity=25,  import_price=2800000 WHERE id=19;
-- CF001: nhập 50 @ 900,000
UPDATE products SET stock_quantity=50,  import_price=900000  WHERE id=20;

-- ============================================
-- ĐƠN HÀNG MẪU
-- ============================================

-- Đơn 1: Nguyễn Văn A (đã giao)
INSERT INTO orders (order_code, user_id, receiver_name, receiver_phone, shipping_address, ward, district, city, payment_method, total_amount, status, created_at) VALUES
('DH20251101001', 2, 'Nguyễn Văn A', '0901234567', '12 Nguyễn Huệ', 'Bến Nghé', 'Quận 1', 'TP. Hồ Chí Minh', 'cash', 3780000, 'delivered', '2025-11-10 09:15:00');
INSERT INTO order_details (order_id, product_id, quantity, unit_price) VALUES
(1, 1, 1, 2100000), -- NK001
(1, 6, 1, 1740000); -- AD001
UPDATE products SET stock_quantity = stock_quantity - 1 WHERE id=1;
UPDATE products SET stock_quantity = stock_quantity - 1 WHERE id=6;

-- Đơn 2: Trần Thị B (đã giao - chuyển khoản)
INSERT INTO orders (order_code, user_id, receiver_name, receiver_phone, shipping_address, ward, district, city, payment_method, total_amount, status, created_at) VALUES
('DH20251115002', 3, 'Trần Thị B', '0912345678', '45 Lê Văn Sỹ', 'Phường 12', 'Quận 3', 'TP. Hồ Chí Minh', 'transfer', 4500000, 'delivered', '2025-11-20 14:30:00');
INSERT INTO order_details (order_id, product_id, quantity, unit_price) VALUES
(2, 11, 1, 4500000); -- JD001
UPDATE products SET stock_quantity = stock_quantity - 1 WHERE id=11;

-- Đơn 3: Lê Hoàng Hùng (đã xác nhận)
INSERT INTO orders (order_code, user_id, receiver_name, receiver_phone, shipping_address, ward, district, city, payment_method, total_amount, status, created_at) VALUES
('DH20251201003', 4, 'Lê Hoàng Hùng', '0923456789', '78 Hoàng Diệu', 'Phường 9', 'Quận 4', 'TP. Hồ Chí Minh', 'cash', 8010000, 'confirmed', '2025-12-01 10:00:00');
INSERT INTO order_details (order_id, product_id, quantity, unit_price) VALUES
(3, 2, 1, 2700000), -- NK002
(3, 7, 2, 3250000); -- AD002 x2
UPDATE products SET stock_quantity = stock_quantity - 1 WHERE id=2;
UPDATE products SET stock_quantity = stock_quantity - 2 WHERE id=7;

-- Đơn 4: Nguyễn Văn A (chờ xử lý)
INSERT INTO orders (order_code, user_id, receiver_name, receiver_phone, shipping_address, ward, district, city, payment_method, total_amount, status, created_at) VALUES
('DH20260101004', 2, 'Nguyễn Văn A', '0901234567', '12 Nguyễn Huệ', 'Bến Nghé', 'Quận 1', 'TP. Hồ Chí Minh', 'online', 5250000, 'pending', '2026-01-05 16:45:00');
INSERT INTO order_details (order_id, product_id, quantity, unit_price) VALUES
(4, 12, 1, 5250000); -- JD002

-- Đơn 5: Trần Thị B (đã huỷ - không trừ tồn kho)
INSERT INTO orders (order_code, user_id, receiver_name, receiver_phone, shipping_address, ward, district, city, payment_method, total_amount, status, created_at) VALUES
('DH20260110005', 3, 'Trần Thị B', '0912345678', '45 Lê Văn Sỹ', 'Phường 12', 'Quận 3', 'TP. Hồ Chí Minh', 'cash', 1595000, 'cancelled', '2026-01-10 11:00:00');
INSERT INTO order_details (order_id, product_id, quantity, unit_price) VALUES
(5, 16, 1, 1595000); -- VN001 (đã huỷ, không trừ tồn)

-- ============================================
-- NẾU ĐÃ CÓ DATABASE CŨ: Chạy các lệnh ALTER bên dưới
-- (Bỏ qua nếu import từ đầu)
-- ============================================
-- ALTER TABLE products
--     ADD COLUMN IF NOT EXISTS brand VARCHAR(100) AFTER image,
--     ADD COLUMN IF NOT EXISTS gender ENUM('nam','nu','unisex') DEFAULT 'unisex' AFTER brand,
--     ADD COLUMN IF NOT EXISTS available_sizes VARCHAR(255) DEFAULT '' AFTER gender,
--     ADD COLUMN IF NOT EXISTS color VARCHAR(100) AFTER available_sizes,
--     ADD COLUMN IF NOT EXISTS material VARCHAR(200) AFTER color,
--     ADD COLUMN IF NOT EXISTS origin VARCHAR(100) AFTER material;
