-- ============================================================
-- Migration: guarantee_certificates table
-- Run once: mysql -u root -p facturation < database/migration_guarantees.sql
-- ============================================================

USE `facturation`;

CREATE TABLE IF NOT EXISTS `guarantee_certificates` (
  `id`                 INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `certificate_number` VARCHAR(50)     NOT NULL,
  `client_id`          INT UNSIGNED        NULL COMMENT 'Optional FK to clients',
  `invoice_id`         INT UNSIGNED        NULL COMMENT 'Optional FK to invoices',
  `customer_name`      VARCHAR(200)    NOT NULL,
  `product_details`    TEXT            NOT NULL COMMENT 'Product/service description',
  `start_date`         DATE            NOT NULL,
  `end_date`           DATE            NOT NULL,
  `terms`              TEXT                NULL COMMENT 'Warranty terms & conditions',
  `notes`              TEXT                NULL COMMENT 'Internal notes',
  `created_at`         DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`         DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE  KEY `uq_cert_number`  (`certificate_number`),
  KEY `fk_cert_client`  (`client_id`),
  KEY `fk_cert_invoice` (`invoice_id`),
  CONSTRAINT `fk_cert_client`  FOREIGN KEY (`client_id`)  REFERENCES `clients`  (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_cert_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
