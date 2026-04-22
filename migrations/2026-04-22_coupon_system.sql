-- Database schema for Advanced Coupon/Voucher System

CREATE TABLE IF NOT EXISTS `discount_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `discount_type` enum('fixed','percentage') DEFAULT 'fixed',
  `discount_value` decimal(15,2) NOT NULL,
  `max_discount_amount` decimal(15,2) DEFAULT NULL,
  `min_order_amount` decimal(15,2) DEFAULT 0.00,
  `max_uses` int(11) DEFAULT NULL,
  `max_uses_per_user` int(11) DEFAULT 1,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `apply_to` enum('all','category','product') DEFAULT 'all',
  `status` enum('active','inactive') DEFAULT 'active',
  `total_used` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `discount_code_categories` (
  `discount_code_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`discount_code_id`,`category_id`),
  CONSTRAINT `fk_dc_cat_code` FOREIGN KEY (`discount_code_id`) REFERENCES `discount_codes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dc_cat_cat` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `discount_code_products` (
  `discount_code_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  PRIMARY KEY (`discount_code_id`,`product_id`),
  CONSTRAINT `fk_dc_prod_code` FOREIGN KEY (`discount_code_id`) REFERENCES `discount_codes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dc_prod_prod` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `discount_code_usages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `discount_code_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `used_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_dc_usage_code` FOREIGN KEY (`discount_code_id`) REFERENCES `discount_codes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dc_usage_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dc_usage_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_saved_coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `discount_code_id` int(11) NOT NULL,
  `saved_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_discount` (`user_id`,`discount_code_id`),
  CONSTRAINT `fk_usc_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_usc_code` FOREIGN KEY (`discount_code_id`) REFERENCES `discount_codes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add discount columns to orders table if they don't exist
-- Note: MySQL requires a separate command to add columns conditionally, so we just list the command here for reference if running manually.
-- ALTER TABLE `orders` ADD COLUMN `discount_code_id` int(11) DEFAULT NULL;
-- ALTER TABLE `orders` ADD COLUMN `discount_amount` decimal(15,2) DEFAULT 0.00;
-- ALTER TABLE `orders` ADD CONSTRAINT `fk_order_discount` FOREIGN KEY (`discount_code_id`) REFERENCES `discount_codes` (`id`) ON DELETE SET NULL;
