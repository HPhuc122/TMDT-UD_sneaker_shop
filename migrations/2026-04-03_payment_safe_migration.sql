-- Safe migration for payment columns/status/deadline.
-- This file is additive and does not modify your existing SQL dump file.

USE sneaker_shop;

ALTER TABLE orders
  ADD COLUMN IF NOT EXISTS payment_status ENUM('pending','paid','failed') DEFAULT NULL
    COMMENT 'Trang thai thanh toan (online)' AFTER status,
  ADD COLUMN IF NOT EXISTS app_trans_id VARCHAR(100) DEFAULT NULL
    COMMENT 'Ma giao dich gui len cong thanh toan de doi chieu callback' AFTER payment_status,
  ADD COLUMN IF NOT EXISTS zp_trans_id VARCHAR(100) DEFAULT NULL
    COMMENT 'Ma giao dich tra ve tu cong thanh toan khi thanh cong' AFTER app_trans_id,
  ADD COLUMN IF NOT EXISTS payment_deadline DATETIME NULL
    COMMENT 'Han thanh toan, qua han se tu huy don' AFTER zp_trans_id;

ALTER TABLE orders
  MODIFY COLUMN status ENUM('pending_payment','awaiting_payment','pending','confirmed','delivered','cancelled')
  DEFAULT 'pending';

ALTER TABLE orders
  ADD INDEX IF NOT EXISTS idx_app_trans_id (app_trans_id),
  ADD INDEX IF NOT EXISTS idx_payment_deadline_status (status, payment_deadline);
