-- ============================================================
-- Migration: company_settings table
-- Run once: mysql -u root -p facturation < database/migration_settings.sql
-- ============================================================

USE `facturation`;

CREATE TABLE IF NOT EXISTS `company_settings` (
  `id`              TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `company_name`    VARCHAR(200)     NOT NULL DEFAULT '',
  `address`         TEXT                 NULL,
  `phone`           VARCHAR(50)          NULL,
  `email`           VARCHAR(150)         NULL,
  `tax_id`          VARCHAR(100)         NULL COMMENT 'ICE / IF / RC',
  `logo_path`       VARCHAR(500)         NULL,
  `invoice_notes`   TEXT                 NULL COMMENT 'Default notes printed on every invoice',
  `invoice_footer`  VARCHAR(500)         NULL COMMENT 'Footer line on printed invoices',
  `updated_at`      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `chk_settings_singleton` CHECK (`id` = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default row (safe to run multiple times)
INSERT INTO `company_settings`
  (`id`, `company_name`, `address`, `phone`, `email`, `tax_id`, `invoice_notes`, `invoice_footer`)
VALUES
  (1, 'GestStock', '', '', '', '', '', 'Merci pour votre confiance.')
ON DUPLICATE KEY UPDATE `id` = `id`;
