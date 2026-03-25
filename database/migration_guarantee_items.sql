-- ============================================================
-- Migration: guarantee_items table + new columns
-- Run once: mysql -u root -p facturation < database/migration_guarantee_items.sql
-- ============================================================

USE `facturation`;

-- Make product_details optional (was NOT NULL)
ALTER TABLE `guarantee_certificates`
  MODIFY COLUMN `product_details` TEXT NULL,
  ADD COLUMN  `reference`         VARCHAR(100) NULL COMMENT 'Order / delivery reference' AFTER `invoice_id`,
  ADD COLUMN  `delivery_date`     DATE         NULL COMMENT 'Delivery / purchase date'   AFTER `end_date`;

-- Line items for each certificate (optional structured items)
CREATE TABLE IF NOT EXISTS `guarantee_items` (
  `id`             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `certificate_id` INT UNSIGNED    NOT NULL,
  `label`          VARCHAR(500)    NOT NULL,
  `quantity`       DECIMAL(10,2)   NOT NULL DEFAULT 1.00,
  `unit_price`     DECIMAL(12,2)       NULL COMMENT 'NULL = no price shown',
  `total`          DECIMAL(12,2)       NULL,
  PRIMARY KEY (`id`),
  KEY `fk_gitem_cert` (`certificate_id`),
  CONSTRAINT `fk_gitem_cert`
    FOREIGN KEY (`certificate_id`) REFERENCES `guarantee_certificates` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
