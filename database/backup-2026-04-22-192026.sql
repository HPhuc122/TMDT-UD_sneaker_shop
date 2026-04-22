-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: sneaker_shop
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` (`id`, `name`, `description`, `created_at`) VALUES (1,'Sneaker Thể Thao','Giày sneaker dành cho hoạt động thể thao','2026-03-17 07:45:05'),(2,'Sneaker Lifestyle','Giày sneaker thời trang hàng ngày','2026-03-17 07:45:05'),(3,'Sneaker Running','Giày chạy bộ chuyên dụng hiệu năng cao','2026-03-17 07:45:05'),(4,'Sneaker Basketball','Giày bóng rổ chuyên nghiệp','2026-03-17 07:45:05'),(5,'Sneaker Skateboard','Giày trượt ván bền bỉ','2026-03-17 07:45:05');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `colors`
--

DROP TABLE IF EXISTS `colors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `colors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `colors`
--

LOCK TABLES `colors` WRITE;
/*!40000 ALTER TABLE `colors` DISABLE KEYS */;
INSERT INTO `colors` (`id`, `name`) VALUES (10,'Be'),(2,'Đen'),(3,'Đỏ'),(8,'Hồng'),(9,'Nâu'),(1,'Trắng'),(6,'Vàng'),(7,'Xám'),(4,'Xanh dương'),(5,'Xanh lá');
/*!40000 ALTER TABLE `colors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `discount_code_categories`
--

DROP TABLE IF EXISTS `discount_code_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `discount_code_categories` (
  `discount_code_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`discount_code_id`,`category_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `discount_code_categories_ibfk_1` FOREIGN KEY (`discount_code_id`) REFERENCES `discount_codes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `discount_code_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `discount_code_categories`
--

LOCK TABLES `discount_code_categories` WRITE;
/*!40000 ALTER TABLE `discount_code_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `discount_code_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `discount_code_products`
--

DROP TABLE IF EXISTS `discount_code_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `discount_code_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `discount_code_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `discount_code_id` (`discount_code_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `discount_code_products_ibfk_1` FOREIGN KEY (`discount_code_id`) REFERENCES `discount_codes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `discount_code_products_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `discount_code_products`
--

LOCK TABLES `discount_code_products` WRITE;
/*!40000 ALTER TABLE `discount_code_products` DISABLE KEYS */;
/*!40000 ALTER TABLE `discount_code_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `discount_code_usages`
--

DROP TABLE IF EXISTS `discount_code_usages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `discount_code_usages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `discount_code_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `used_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `discount_code_id` (`discount_code_id`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `discount_code_usages_ibfk_1` FOREIGN KEY (`discount_code_id`) REFERENCES `discount_codes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `discount_code_usages_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `discount_code_usages_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `discount_code_usages`
--

LOCK TABLES `discount_code_usages` WRITE;
/*!40000 ALTER TABLE `discount_code_usages` DISABLE KEYS */;
/*!40000 ALTER TABLE `discount_code_usages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `discount_codes`
--

DROP TABLE IF EXISTS `discount_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `discount_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(15,2) NOT NULL,
  `min_order_amount` decimal(15,2) DEFAULT 0.00,
  `max_uses` int(11) DEFAULT NULL,
  `max_uses_per_user` int(11) DEFAULT 1,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `apply_to` enum('all','category','product') DEFAULT 'all',
  `total_used` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `max_discount_amount` decimal(15,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `discount_codes`
--

LOCK TABLES `discount_codes` WRITE;
/*!40000 ALTER TABLE `discount_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `discount_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `import_details`
--

DROP TABLE IF EXISTS `import_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `import_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `receipt_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `import_price` decimal(15,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `receipt_id` (`receipt_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `import_details_ibfk_1` FOREIGN KEY (`receipt_id`) REFERENCES `import_receipts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `import_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `import_details`
--

LOCK TABLES `import_details` WRITE;
/*!40000 ALTER TABLE `import_details` DISABLE KEYS */;
INSERT INTO `import_details` (`id`, `receipt_id`, `product_id`, `quantity`, `import_price`) VALUES (1,1,1,60,1500000.00),(2,1,2,40,2000000.00),(3,1,3,35,2200000.00),(4,1,6,50,1200000.00),(5,1,7,35,2500000.00),(6,2,11,25,3000000.00),(7,2,12,20,3500000.00),(8,2,13,18,3200000.00),(9,2,14,30,1700000.00),(10,2,15,20,3800000.00),(11,3,4,35,2600000.00),(12,3,5,40,1800000.00),(13,3,16,45,1100000.00),(14,3,17,35,1200000.00),(15,4,8,30,1900000.00),(16,4,9,35,1400000.00),(17,4,10,40,1600000.00),(18,4,18,30,1600000.00),(19,4,19,25,2800000.00),(20,4,20,50,900000.00),(21,5,1,30,1550000.00),(22,5,6,25,1250000.00);
/*!40000 ALTER TABLE `import_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `import_receipts`
--

DROP TABLE IF EXISTS `import_receipts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `import_receipts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `receipt_code` varchar(50) NOT NULL,
  `import_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','completed') DEFAULT 'pending',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `receipt_code` (`receipt_code`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `import_receipts_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `import_receipts`
--

LOCK TABLES `import_receipts` WRITE;
/*!40000 ALTER TABLE `import_receipts` DISABLE KEYS */;
INSERT INTO `import_receipts` (`id`, `receipt_code`, `import_date`, `notes`, `status`, `created_by`, `created_at`) VALUES (1,'PN2025100101','2025-10-01','Nhập hàng đầu kỳ Q4/2025 - Nike & Adidas','completed',1,'2026-03-17 07:45:05'),(2,'PN2025101501','2025-10-15','Nhập Jordan & New Balance theo đơn đặt hàng','completed',1,'2026-03-17 07:45:05'),(3,'PN2025110101','2025-11-01','Nhập bổ sung Nike Dunk & Vans chuẩn bị cuối năm','completed',1,'2026-03-17 07:45:05'),(4,'PN2025111501','2025-11-15','Đa thương hiệu: Adidas NMD, Samba, Puma, ASICS, Converse','completed',1,'2026-03-17 07:45:05'),(5,'PN2026010101','2026-01-10','Nhập hàng Q1/2026 - chưa hoàn thành','pending',1,'2026-03-17 07:45:05'),(6,'PN20260324010351588','2026-03-24','','pending',1,'2026-03-24 01:03:51');
/*!40000 ALTER TABLE `import_receipts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_details`
--

DROP TABLE IF EXISTS `order_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `size_id` int(11) DEFAULT NULL,
  `color_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  KEY `fk_od_size` (`size_id`),
  KEY `fk_od_color` (`color_id`),
  CONSTRAINT `fk_od_color` FOREIGN KEY (`color_id`) REFERENCES `colors` (`id`),
  CONSTRAINT `fk_od_size` FOREIGN KEY (`size_id`) REFERENCES `sizes` (`id`),
  CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_details`
--

LOCK TABLES `order_details` WRITE;
/*!40000 ALTER TABLE `order_details` DISABLE KEYS */;
INSERT INTO `order_details` (`id`, `order_id`, `product_id`, `quantity`, `unit_price`, `size_id`, `color_id`) VALUES (1,1,1,1,2100000.00,10,10),(2,1,6,1,1740000.00,6,3),(3,2,11,1,4500000.00,10,5),(4,3,2,1,2700000.00,11,6),(5,3,7,2,3250000.00,9,8),(6,4,12,1,5250000.00,4,6),(7,5,16,1,1595000.00,8,5),(8,6,3,1,3036000.00,2,2),(9,7,3,3,3036000.00,NULL,NULL),(10,8,3,3,3036000.00,1,7),(11,9,18,1,2240000.00,2,4),(12,10,8,1,2584000.00,2,4),(13,11,8,1,2584000.00,2,4),(14,12,8,1,2584000.00,1,2),(15,13,6,1,1740000.00,2,5),(16,14,8,1,2584000.00,2,4),(17,14,18,1,2240000.00,2,4),(18,15,9,1,1960000.00,2,10),(20,17,8,1,2584000.00,2,4);
/*!40000 ALTER TABLE `order_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_code` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `discount_code_id` int(11) DEFAULT NULL,
  `receiver_name` varchar(200) DEFAULT NULL,
  `receiver_phone` varchar(20) DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `ward` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `payment_method` enum('cash','transfer','online') DEFAULT 'cash',
  `total_amount` decimal(15,2) DEFAULT 0.00,
  `discount_amount` decimal(15,2) DEFAULT 0.00,
  `status` enum('pending_payment','awaiting_payment','pending','confirmed','delivered','cancelled') DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed') DEFAULT NULL COMMENT 'Trạng thái thanh toán ZaloPay',
  `payment_deadline` datetime DEFAULT NULL COMMENT 'H?n thanh to?n, qu? h?n s? t? h?y ??n',
  `app_trans_id` varchar(100) DEFAULT NULL COMMENT 'Mã giao dịch gửi lên ZaloPay (để đối chiếu callback)',
  `zp_trans_id` varchar(100) DEFAULT NULL COMMENT 'Mã giao dịch ZaloPay trả về sau khi thanh toán thành công',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_code` (`order_code`),
  KEY `user_id` (`user_id`),
  KEY `idx_orders_created_at` (`created_at`),
  KEY `idx_app_trans_id` (`app_trans_id`),
  KEY `idx_payment_deadline_status` (`status`,`payment_deadline`),
  KEY `fk_order_discount` (`discount_code_id`),
  CONSTRAINT `fk_order_discount` FOREIGN KEY (`discount_code_id`) REFERENCES `discount_codes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` (`id`, `order_code`, `user_id`, `discount_code_id`, `receiver_name`, `receiver_phone`, `shipping_address`, `ward`, `district`, `city`, `payment_method`, `total_amount`, `discount_amount`, `status`, `payment_status`, `payment_deadline`, `app_trans_id`, `zp_trans_id`, `notes`, `created_at`, `updated_at`) VALUES (1,'DH20251101001',2,NULL,'Nguyễn Văn A','0901234567','12 Nguyễn Huệ','Bến Nghé','Quận 1','TP. Hồ Chí Minh','cash',3780000.00,0.00,'delivered',NULL,NULL,NULL,NULL,NULL,'2025-11-10 02:15:00','2026-03-17 07:45:05'),(2,'DH20251115002',3,NULL,'Trần Thị B','0912345678','45 Lê Văn Sỹ','Phường 12','Quận 3','TP. Hồ Chí Minh','transfer',4500000.00,0.00,'delivered',NULL,NULL,NULL,NULL,NULL,'2025-11-20 07:30:00','2026-03-17 07:45:06'),(3,'DH20251201003',4,NULL,'Lê Hoàng Hùng','0923456789','78 Hoàng Diệu','Phường 9','Quận 4','TP. Hồ Chí Minh','cash',8010000.00,0.00,'confirmed',NULL,NULL,NULL,NULL,NULL,'2025-12-01 03:00:00','2026-03-17 07:45:06'),(4,'DH20260101004',2,NULL,'Nguyễn Văn A','0901234567','12 Nguyễn Huệ','Bến Nghé','Quận 1','TP. Hồ Chí Minh','online',5250000.00,0.00,'pending',NULL,NULL,NULL,NULL,NULL,'2026-01-05 09:45:00','2026-03-17 07:45:06'),(5,'DH20260110005',3,NULL,'Trần Thị B','0912345678','45 Lê Văn Sỹ','Phường 12','Quận 3','TP. Hồ Chí Minh','cash',1595000.00,0.00,'cancelled',NULL,NULL,NULL,NULL,NULL,'2026-01-10 04:00:00','2026-03-17 07:45:06'),(6,'DH20260402153519117',2,NULL,'Nguyễn Văn A','0901234567','12 Nguyễn Huệ','Bến Nghé','Quận 1','TP. Hồ Chí Minh','online',3036000.00,0.00,'pending',NULL,NULL,NULL,NULL,'','2026-04-02 13:35:19','2026-04-02 13:35:19'),(7,'DH20260402211626520',2,NULL,'Nguyễn Văn A','0901234567','12 Nguyễn Huệ','Bến Nghé','Quận 1','TP. Hồ Chí Minh','online',9108000.00,0.00,'confirmed',NULL,NULL,NULL,NULL,'','2026-04-02 14:16:26','2026-04-02 14:16:26'),(8,'DH20260402212457364',2,NULL,'Nguyễn Văn A','0901234567','12 Nguyễn Huệ','Bến Nghé','Quận 1','TP. Hồ Chí Minh','online',9108000.00,0.00,'confirmed',NULL,NULL,NULL,NULL,'','2026-04-02 14:24:57','2026-04-02 14:24:57'),(9,'DH20260402213240319',2,NULL,'Nguyễn Văn A','0901234567','12 Nguyễn Huệ','Bến Nghé','Quận 1','TP. Hồ Chí Minh','online',2240000.00,0.00,'confirmed',NULL,NULL,NULL,NULL,'','2026-04-02 14:32:40','2026-04-02 14:32:40'),(10,'DH20260402163546592',2,NULL,'Nguyễn Văn A','0901234567','12 Nguyễn Huệ','Bến Nghé','Quận 1','TP. Hồ Chí Minh','cash',2584000.00,0.00,'pending',NULL,NULL,NULL,NULL,'','2026-04-02 14:35:46','2026-04-02 14:35:46'),(11,'DH20260402170622321',2,NULL,'Nguyễn Văn A','0901234567','12 Nguyễn Huệ','Bến Nghé','Quận 1','TP. Hồ Chí Minh','cash',2584000.00,0.00,'pending',NULL,NULL,NULL,NULL,'','2026-04-02 15:06:22','2026-04-03 02:31:23'),(12,'DH20260402171255682',2,NULL,'Nguyễn Văn A','0901234567','12 Nguyễn Huệ','Bến Nghé','Quận 1','TP. Hồ Chí Minh','cash',2584000.00,0.00,'pending',NULL,NULL,NULL,NULL,'','2026-04-02 15:12:55','2026-04-02 15:12:55'),(13,'DH20260402171321346',2,NULL,'Nguyễn Văn A','0901234567','12 Nguyễn Huệ','Bến Nghé','Quận 1','TP. Hồ Chí Minh','online',1740000.00,0.00,'awaiting_payment',NULL,NULL,NULL,NULL,'','2026-04-02 15:13:21','2026-04-02 15:13:21'),(14,'DH20260402173504488',2,NULL,'Nguyễn Văn A','0901234567','12 Nguyễn Huệ','Bến Nghé','Quận 1','TP. Hồ Chí Minh','cash',4824000.00,0.00,'pending',NULL,NULL,'14',NULL,'','2026-04-02 15:35:04','2026-04-03 02:34:25'),(15,'DH20260403044650246',2,NULL,'Nguyễn Văn A','0901234567','12 Nguyễn Huệ','Bến Nghé','Quận 1','TP. Hồ Chí Minh','online',1960000.00,0.00,'awaiting_payment',NULL,NULL,'DH20260403044650246',NULL,'','2026-04-03 02:46:50','2026-04-03 02:48:03'),(17,'DH20260422095839271',2,2,'Nguyễn Văn A','0901234567','12 Nguyễn Huệ','Bến Nghé','Quận 1','TP. Hồ Chí Minh','cash',1989680.00,594320.00,'pending',NULL,NULL,NULL,NULL,'','2026-04-22 07:58:39','2026-04-22 07:58:39');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_varieties`
--

DROP TABLE IF EXISTS `product_varieties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_varieties` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `size_id` int(11) NOT NULL,
  `color_id` int(11) NOT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `price` decimal(15,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_variant` (`product_id`,`size_id`,`color_id`),
  KEY `product_id` (`product_id`),
  KEY `size_id` (`size_id`),
  KEY `fk_pv_color` (`color_id`),
  CONSTRAINT `fk_pv_color` FOREIGN KEY (`color_id`) REFERENCES `colors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pv_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `fk_pv_size` FOREIGN KEY (`size_id`) REFERENCES `sizes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=262 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_varieties`
--

LOCK TABLES `product_varieties` WRITE;
/*!40000 ALTER TABLE `product_varieties` DISABLE KEYS */;
INSERT INTO `product_varieties` (`id`, `product_id`, `size_id`, `color_id`, `stock_quantity`, `price`) VALUES (221,1,1,1,10,1500000.00),(222,1,2,1,8,1500000.00),(223,1,3,2,6,1500000.00),(224,2,1,2,6,2000000.00),(225,2,2,7,5,2000000.00),(226,3,1,7,3,2200000.00),(227,3,2,2,4,2200000.00),(228,4,1,4,5,2600000.00),(229,4,2,2,5,2600000.00),(230,5,1,3,8,1800000.00),(231,5,2,1,6,1800000.00),(232,6,1,1,10,1200000.00),(233,6,2,5,5,1200000.00),(234,7,1,2,6,2500000.00),(235,7,2,7,4,2500000.00),(236,8,1,2,6,1900000.00),(237,8,2,4,3,1900000.00),(238,9,1,4,6,1400000.00),(239,9,2,10,4,1400000.00),(240,10,1,2,8,1600000.00),(241,10,2,1,6,1600000.00),(242,11,1,3,10,3000000.00),(243,11,2,2,8,3000000.00),(244,12,1,2,7,3500000.00),(245,12,2,7,6,3500000.00),(246,13,1,1,8,3200000.00),(247,13,2,2,7,3200000.00),(248,14,1,7,6,1700000.00),(249,14,2,4,5,1700000.00),(250,15,1,7,5,3800000.00),(251,15,2,10,4,3800000.00),(252,16,1,2,10,1100000.00),(253,16,2,1,8,1100000.00),(254,17,1,2,9,1200000.00),(255,17,2,9,6,1200000.00),(256,18,1,3,7,1600000.00),(257,18,2,4,4,1600000.00),(258,19,1,4,6,2800000.00),(259,19,2,8,4,2800000.00),(260,20,1,1,10,900000.00),(261,20,2,2,8,900000.00);
/*!40000 ALTER TABLE `product_varieties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(200) NOT NULL,
  `category_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `unit` varchar(50) DEFAULT 'đôi',
  `import_price` decimal(15,2) DEFAULT 0.00,
  `profit_rate` decimal(5,2) DEFAULT 30.00,
  `image` varchar(255) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `gender` enum('nam','nu','unisex') DEFAULT 'unisex',
  `material` varchar(200) DEFAULT NULL,
  `origin` varchar(100) DEFAULT NULL,
  `status` enum('active','hidden') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `category_id` (`category_id`),
  KEY `idx_products_brand` (`brand`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` (`id`, `code`, `name`, `category_id`, `description`, `unit`, `import_price`, `profit_rate`, `image`, `brand`, `gender`, `material`, `origin`, `status`, `created_at`, `updated_at`) VALUES (1,'NK001','Nike Air Force 1 Low',2,'Giày Nike Air Force 1 Low cổ điển, thiết kế trắng tinh khiết không bao giờ lỗi mốt.','đôi',1500000.00,40.00,'nike-air-force-1-low.jpg','Nike','unisex','Da tổng hợp cao cấp','Việt Nam','active','2026-03-17 07:45:05','2026-03-23 10:20:02'),(2,'NK002','Nike Air Max 270',1,'Đệm khí Air Max lớn nhất từ trước đến nay, mang lại cảm giác êm ái tuyệt vời cả ngày dài.','đôi',2000000.00,35.00,'nike-air-max-270.jpg','Nike','unisex','Vải Flyknit & da tổng hợp','Indonesia','active','2026-03-17 07:45:05','2026-03-23 10:20:02'),(3,'NK003','Nike Air Max 97',1,'Thiết kế gợn sóng biểu tượng từ năm 1997, đệm khí toàn phần cực kỳ êm.','đôi',2200000.00,38.00,'nike-air-max-97.jpg','Nike','unisex','Da tổng hợp & lưới','Trung Quốc','active','2026-03-17 07:45:05','2026-03-23 10:20:02'),(4,'NK004','Nike React Infinity Run',3,'Giày chạy bộ với đệm React cực êm, hỗ trợ chống chấn thương hiệu quả.','đôi',2600000.00,32.00,'nike-react-infinity-run.jpg','Nike','unisex','Vải mesh thoáng khí','Việt Nam','active','2026-03-17 07:45:05','2026-03-23 10:20:02'),(5,'NK005','Nike Dunk Low Retro',2,'Phiên bản Retro của Nike Dunk Low, màu sắc tươi tắn, kết hợp hoàn hảo với mọi outfit.','đôi',1800000.00,42.00,'nike-dunk-low-retro.jpg','Nike','unisex','Da thật','Indonesia','active','2026-03-17 07:45:05','2026-03-23 10:20:02'),(6,'AD001','Adidas Stan Smith',2,'Biểu tượng thời trang đường phố suốt 50 năm qua, thiết kế đơn giản mà tinh tế.','đôi',1200000.00,45.00,'adidas-stan-smith.jpg','Adidas','unisex','Da tự nhiên','Trung Quốc','active','2026-03-17 07:45:05','2026-03-23 10:20:02'),(7,'AD002','Adidas Ultraboost 22',3,'Công nghệ Boost đẳng cấp trả lại năng lượng mỗi bước chạy, lý tưởng cho runner nghiêm túc.','đôi',2500000.00,30.00,'adidas-ultraboost-22.jpg','Adidas','unisex','Vải Primeknit+','Việt Nam','active','2026-03-17 07:45:05','2026-03-23 10:20:02'),(8,'AD003','Adidas NMD R1',2,'Phong cách street fashion tối thượng với đệm Boost thoải mái, phù hợp mọi hoàn cảnh.','đôi',1900000.00,36.00,'adidas-nmd-r1.jpg','Adidas','unisex','Vải Primeknit','Đức','active','2026-03-17 07:45:05','2026-03-23 10:20:02'),(9,'AD004','Adidas Gazelle',2,'Phong cách retro 70s, mũi giày da mềm mại, lót lưỡi gà đặc trưng tạo nên nét huyền thoại.','đôi',1400000.00,40.00,'adidas-gazelle.jpg','Adidas','unisex','Da lộn (suede)','Ấn Độ','active','2026-03-17 07:45:05','2026-03-23 10:20:02'),(10,'AD005','Adidas Samba OG',2,'Cú trở lại ngoạn mục của dòng Samba huyền thoại, chiếm lĩnh street style toàn cầu.','đôi',1600000.00,38.00,'adidas-samba-og.jpg','Adidas','unisex','Da tự nhiên','Ấn Độ','active','2026-03-17 07:45:05','2026-03-23 10:20:02'),(11,'JD001','Jordan 1 Retro High OG',4,'Huyền thoại bóng rổ trở thành biểu tượng streetwear, thiết kế Bred classic không đổi theo thời gian.','đôi',3000000.00,50.00,'jordan-1-retro-high-og.jpg','Jordan','nam','Da thật cao cấp','Trung Quốc','active','2026-03-17 07:45:05','2026-03-23 10:20:02'),(12,'JD002','Jordan 4 Retro',4,'Air Jordan 4 với thiết kế lưới đặc trưng và Air unit ở đế giữa, một trong những AJ được yêu thích nhất.','đôi',3500000.00,48.00,'jordan-4-retro.jpg','Jordan','nam','Da thật & lưới','Trung Quốc','active','2026-03-17 07:45:05','2026-03-23 10:20:02'),(13,'JD003','Jordan 11 Retro Low',4,'Thiết kế patent leather bóng loáng sang trọng kết hợp outsole trong suốt, cực kỳ đặc biệt.','đôi',3200000.00,45.00,'jordan-11-retro-low.jpg','Jordan','nam','Da bóng & vải','Trung Quốc','active','2026-03-17 07:45:05','2026-03-23 10:20:02'),(14,'NB001','New Balance 574',2,'Dòng giày biểu tượng từ thập niên 80, comfort từng bước chân với đế ENCAP độc quyền.','đôi',1700000.00,38.00,'new-balance-574.jpg','New Balance','unisex','Da lộn & vải','Mỹ','active','2026-03-17 07:45:05','2026-03-23 10:20:02'),(15,'NB002','New Balance 990v6',3,'Made in USA, chất lượng đỉnh cao, đệm êm ái chuẩn mực cho runner nghiêm túc.','đôi',3800000.00,35.00,'new-balance-990v6.jpg','New Balance','unisex','Da lộn & lưới cao cấp','Mỹ','active','2026-03-17 07:45:05','2026-03-23 10:20:02'),(16,'VN001','Vans Old Skool',5,'Giày skate huyền thoại với sọc jazz đặc trưng, đế waffle bám đường tuyệt vời.','đôi',1100000.00,45.00,'vans-old-skool.jpg','Vans','unisex','Canvas & da lộn','Việt Nam','active','2026-03-17 07:45:05','2026-03-23 10:20:02'),(17,'VN002','Vans Sk8-Hi',5,'Cổ cao hỗ trợ mắt cá chân, padding dày, lý tưởng cho ván trượt và street style.','đôi',1200000.00,43.00,'vans-sk8-hi.jpg','Vans','unisex','Canvas','Trung Quốc','active','2026-03-17 07:45:05','2026-03-23 10:20:02'),(18,'PU001','Puma RS-X',1,'Running System tái sinh đầy màu sắc, đế dày chunky cực hot, công nghệ RS đệm êm.','đôi',1600000.00,40.00,'puma-rs-x.jpg','Puma','unisex','Da tổng hợp & lưới','Việt Nam','active','2026-03-17 07:45:05','2026-03-23 10:20:02'),(19,'AS001','ASICS Gel-Kayano 30',3,'Kiểm soát motion tối ưu với GEL technology, lý tưởng cho overpronation.','đôi',2800000.00,30.00,'asics-gel-kayano-30.jpg','ASICS','nu','FlyteFoam & lưới','Việt Nam','active','2026-03-17 07:45:05','2026-03-23 10:20:02'),(20,'CF001','Converse Chuck Taylor All Star',2,'Đôi giày vải huyền thoại có mặt trên hơn 100 quốc gia, không bao giờ lỗi thời.','đôi',900000.00,50.00,'converse-chuck-taylor-all-star.jpg','Converse','unisex','Canvas','Việt Nam','active','2026-03-17 07:45:05','2026-03-23 10:20:02');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sizes`
--

DROP TABLE IF EXISTS `sizes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sizes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `size` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `size` (`size`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sizes`
--

LOCK TABLES `sizes` WRITE;
/*!40000 ALTER TABLE `sizes` DISABLE KEYS */;
INSERT INTO `sizes` (`id`, `size`) VALUES (1,35),(2,36),(3,37),(4,38),(5,39),(6,40),(7,41),(8,42),(9,43),(10,44),(11,45);
/*!40000 ALTER TABLE `sizes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_saved_coupons`
--

DROP TABLE IF EXISTS `user_saved_coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_saved_coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `discount_code_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_coupon` (`user_id`,`discount_code_id`),
  KEY `discount_code_id` (`discount_code_id`),
  CONSTRAINT `user_saved_coupons_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_saved_coupons_ibfk_2` FOREIGN KEY (`discount_code_id`) REFERENCES `discount_codes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_saved_coupons`
--

LOCK TABLES `user_saved_coupons` WRITE;
/*!40000 ALTER TABLE `user_saved_coupons` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_saved_coupons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(200) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `ward` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `role` enum('customer','admin') DEFAULT 'customer',
  `status` enum('active','locked') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `email`, `phone`, `address`, `ward`, `district`, `city`, `role`, `status`, `created_at`) VALUES (1,'admin','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Quản Trị Viên','admin@sneakershop.vn','0901000001','1 Lê Lợi','Bến Nghé','Quận 1','TP. Hồ Chí Minh','admin','active','2026-03-17 07:45:05'),(2,'nguyenvana','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Nguyễn Văn A','vana@email.com','0901234567','12 Nguyễn Huệ','Bến Nghé','Quận 1','TP. Hồ Chí Minh','customer','active','2026-03-17 07:45:05'),(3,'tranthib','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Trần Thị B','thib@email.com','0912345678','45 Lê Văn Sỹ','Phường 12','Quận 3','TP. Hồ Chí Minh','customer','active','2026-03-17 07:45:05'),(4,'lehoanghung','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Lê Hoàng Hùng','hoanghung@email.com','0923456789','78 Hoàng Diệu','Phường 9','Quận 4','TP. Hồ Chí Minh','customer','active','2026-03-17 07:45:05'),(5,'test','$2y$10$prq4BEMWx0Lvvlw2wY3UF.29FCEXVXxgcx3dBPOrdUHC.6kEcohFq','aavs','abc@gmail.com','0123456789','sac','sdv','xv','ddvs','admin','active','2026-03-24 00:53:42');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'sneaker_shop'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-22 19:20:27
