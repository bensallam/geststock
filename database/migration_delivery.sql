-- ============================================================
-- Migration: payment_method + company settings + delivery notes
-- Run once: mysql -u root -p facturation < database/migration_delivery.sql
-- ============================================================

USE `facturation`;

-- ── Payment method on invoices ───────────────────────────────
ALTER TABLE `invoices`
  ADD COLUMN `payment_method` VARCHAR(20) NULL AFTER `status`;

-- ── Payment method on guarantee certificates ─────────────────
ALTER TABLE `guarantee_certificates`
  ADD COLUMN `payment_method` VARCHAR(20) NULL AFTER `notes`;

-- ── Extra defaults in company_settings ───────────────────────
ALTER TABLE `company_settings`
  ADD COLUMN `default_warranty_terms` TEXT         NULL AFTER `invoice_footer`,
  ADD COLUMN `default_payment_method` VARCHAR(20)  NULL AFTER `default_warranty_terms`;

-- ── Delivery notes (Bons de livraison) ───────────────────────
CREATE TABLE IF NOT EXISTS `delivery_notes` (
  `id`             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `note_number`    VARCHAR(50)     NOT NULL,
  `client_id`      INT UNSIGNED        NULL,
  `customer_name`  VARCHAR(200)    NOT NULL,
  `delivery_date`  DATE            NOT NULL,
  `reference`      VARCHAR(100)        NULL COMMENT 'Order / invoice ref',
  `show_prices`    TINYINT(1)      NOT NULL DEFAULT 0,
  `payment_method` VARCHAR(20)         NULL,
  `notes`          TEXT                NULL,
  `created_at`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE  KEY `uq_note_number`  (`note_number`),
  KEY `fk_dn_client` (`client_id`),
  CONSTRAINT `fk_dn_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `delivery_note_items` (
  `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `note_id`    INT UNSIGNED    NOT NULL,
  `label`      VARCHAR(500)    NOT NULL,
  `quantity`   DECIMAL(10,2)   NOT NULL DEFAULT 1.00,
  `unit_price` DECIMAL(12,2)       NULL,
  `total`      DECIMAL(12,2)       NULL,
  PRIMARY KEY (`id`),
  KEY `fk_dni_note` (`note_id`),
  CONSTRAINT `fk_dni_note` FOREIGN KEY (`note_id`) REFERENCES `delivery_notes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
