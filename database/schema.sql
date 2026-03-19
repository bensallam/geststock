-- ============================================================
-- Facturation - Stock Management & Invoicing System
-- Database Schema with Sample Data
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- ============================================================
-- Database
-- ============================================================
CREATE DATABASE IF NOT EXISTS `facturation`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `facturation`;

-- ============================================================
-- Table: users
-- ============================================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(100)    NOT NULL,
  `email`      VARCHAR(150)    NOT NULL,
  `password`   VARCHAR(255)    NOT NULL,
  `created_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: categories
-- ============================================================
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_categories_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: products
-- ============================================================
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(200)     NOT NULL,
  `sku`           VARCHAR(100)     NOT NULL,
  `category_id`   INT UNSIGNED         NULL,
  `unit_price`    DECIMAL(12,2)    NOT NULL DEFAULT 0.00,
  `cost_price`    DECIMAL(12,2)    NOT NULL DEFAULT 0.00,
  `quantity`      INT              NOT NULL DEFAULT 0,
  `minimum_stock` INT              NOT NULL DEFAULT 5,
  `description`   TEXT                 NULL,
  `created_at`    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_products_sku` (`sku`),
  KEY `idx_products_category` (`category_id`),
  KEY `idx_products_quantity` (`quantity`),
  CONSTRAINT `fk_products_category`
    FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: stock_movements
-- ============================================================
DROP TABLE IF EXISTS `stock_movements`;
CREATE TABLE `stock_movements` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `type`       ENUM('IN','OUT') NOT NULL,
  `quantity`   INT          NOT NULL,
  `note`       VARCHAR(500)     NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_stock_product` (`product_id`),
  KEY `idx_stock_date`    (`created_at`),
  CONSTRAINT `fk_stock_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: clients
-- ============================================================
DROP TABLE IF EXISTS `clients`;
CREATE TABLE `clients` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(200) NOT NULL,
  `address`    TEXT             NULL,
  `ice`        VARCHAR(50)      NULL  COMMENT 'Identifiant Commun de l\'Entreprise',
  `phone`      VARCHAR(30)      NULL,
  `email`      VARCHAR(150)     NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_clients_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: invoices
-- ============================================================
DROP TABLE IF EXISTS `invoices`;
CREATE TABLE `invoices` (
  `id`             INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `invoice_number` VARCHAR(50)    NOT NULL,
  `client_id`      INT UNSIGNED   NOT NULL,
  `date`           DATE           NOT NULL,
  `total_ht`       DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
  `tax_rate`       DECIMAL(5,2)   NOT NULL DEFAULT 20.00 COMMENT 'TVA %',
  `tax_amount`     DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
  `total_ttc`      DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
  `notes`          TEXT               NULL,
  `status`         ENUM('draft','sent','paid','cancelled') NOT NULL DEFAULT 'draft',
  `created_at`     DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_invoices_number` (`invoice_number`),
  KEY `idx_invoices_client` (`client_id`),
  KEY `idx_invoices_date`   (`date`),
  CONSTRAINT `fk_invoices_client`
    FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: invoice_items
-- ============================================================
DROP TABLE IF EXISTS `invoice_items`;
CREATE TABLE `invoice_items` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `invoice_id` INT UNSIGNED  NOT NULL,
  `product_id` INT UNSIGNED      NULL,
  `label`      VARCHAR(300)  NOT NULL,
  `quantity`   DECIMAL(10,2) NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total`      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_items_invoice` (`invoice_id`),
  KEY `idx_items_product` (`product_id`),
  CONSTRAINT `fk_items_invoice`
    FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_items_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- Sample Data
-- ============================================================

-- Admin user  (password: admin123)
INSERT INTO `users` (`name`, `email`, `password`) VALUES
('Administrateur', 'admin@facturation.ma', '$2y$12$7abHBVtNEfdyjup/8zfBl.JNWXUFNwVSRKA8dGe/umt.rM/UOntKS');

-- Categories
INSERT INTO `categories` (`name`) VALUES
('Informatique'),
('Fournitures de bureau'),
('Mobilier'),
('Électronique'),
('Consommables');

-- Products
INSERT INTO `products` (`name`, `sku`, `category_id`, `unit_price`, `cost_price`, `quantity`, `minimum_stock`, `description`) VALUES
('Ordinateur portable Dell Inspiron',  'DELL-INS-15',  1, 8500.00, 7000.00, 12, 3, 'Laptop 15 pouces, Intel Core i5, 8GB RAM, 256GB SSD'),
('Écran LCD 24"',                      'MON-LCD-24',   1, 2200.00, 1700.00,  8, 2, 'Moniteur Full HD 24 pouces'),
('Clavier sans fil Logitech',          'LOG-KEY-WL',   1,  350.00,  250.00, 25, 5, 'Clavier AZERTY sans fil'),
('Souris optique',                     'MOU-OPT-01',   1,  150.00,  100.00, 30, 5, 'Souris optique USB'),
('Ramette de papier A4',               'PAP-A4-500',   2,   45.00,   30.00, 50,10, 'Ramette 500 feuilles 80g/m²'),
('Stylos BIC (boîte de 50)',           'STY-BIC-50',   2,   80.00,   55.00, 20, 5, 'Stylos à bille bleu'),
('Chaise de bureau ergonomique',       'CHR-ERG-01',   3, 1800.00, 1300.00,  5, 2, 'Chaise pivotante avec accoudoirs'),
('Bureau en L 160cm',                  'BUR-L-160',    3, 3500.00, 2800.00,  3, 1, 'Bureau en L mélaminé blanc'),
('Tablette Samsung Galaxy Tab',        'SAM-TAB-A8',   4, 2800.00, 2200.00,  6, 2, 'Tablette 10.5", 64GB'),
('Câble HDMI 2m',                      'CAB-HDMI-2M',  4,   85.00,   50.00, 40,10, 'Câble HDMI haute vitesse 2 mètres'),
('Cartouche d\'encre HP 302 noir',     'HP-302-BLK',   5,  180.00,  130.00, 15, 5, 'Cartouche originale HP noir'),
('Cartouche d\'encre HP 302 couleur',  'HP-302-CLR',   5,  220.00,  160.00,  4, 5, 'Cartouche originale HP couleur');

-- Stock movements (initial stock IN)
INSERT INTO `stock_movements` (`product_id`, `type`, `quantity`, `note`, `created_at`) VALUES
(1,  'IN', 12, 'Stock initial', '2026-01-05 09:00:00'),
(2,  'IN',  8, 'Stock initial', '2026-01-05 09:00:00'),
(3,  'IN', 25, 'Stock initial', '2026-01-05 09:00:00'),
(4,  'IN', 30, 'Stock initial', '2026-01-05 09:00:00'),
(5,  'IN', 50, 'Stock initial', '2026-01-05 09:00:00'),
(6,  'IN', 20, 'Stock initial', '2026-01-05 09:00:00'),
(7,  'IN',  5, 'Stock initial', '2026-01-05 09:00:00'),
(8,  'IN',  3, 'Stock initial', '2026-01-05 09:00:00'),
(9,  'IN',  6, 'Stock initial', '2026-01-05 09:00:00'),
(10, 'IN', 40, 'Stock initial', '2026-01-05 09:00:00'),
(11, 'IN', 15, 'Stock initial', '2026-01-05 09:00:00'),
(12, 'IN',  4, 'Stock initial', '2026-01-05 09:00:00');

-- Clients
INSERT INTO `clients` (`name`, `address`, `ice`, `phone`, `email`) VALUES
('ACME Maroc SARL',        'Rue des Fleurs, N°12, Casablanca 20000', '001234567000012', '0522-123456', 'contact@acmemaroc.ma'),
('Tech Solutions SA',      'Boulevard Zerktouni, Casablanca',        '009876543000034', '0522-654321', 'info@techsolutions.ma'),
('Bureau Conseil SARL',    'Avenue Hassan II, Rabat 10000',          '005678901000056', '0537-112233', 'direction@bureauconseil.ma'),
('Maroc Digital Agency',   'Quartier Industriel, Fès',               '002345678000078', '0535-445566', 'hello@morocdigital.ma'),
('Société Générale Maroc', 'Tour Casablanca, Casablanca',            '007890123000090', '0800-200300', 'pro@sgmaroc.ma');

-- Invoices
INSERT INTO `invoices` (`invoice_number`, `client_id`, `date`, `total_ht`, `tax_rate`, `tax_amount`, `total_ttc`, `notes`, `status`) VALUES
('FAC-2026-001', 1, '2026-01-10', 17000.00, 20.00, 3400.00, 20400.00, 'Livraison sous 48h.', 'paid'),
('FAC-2026-002', 2, '2026-01-20', 8500.00,  20.00, 1700.00, 10200.00, NULL, 'sent'),
('FAC-2026-003', 3, '2026-02-05', 2750.00,  20.00,  550.00,  3300.00, 'Acompte de 50% reçu.', 'paid'),
('FAC-2026-004', 1, '2026-02-15', 5600.00,  20.00, 1120.00,  6720.00, NULL, 'draft'),
('FAC-2026-005', 4, '2026-03-01', 3200.00,  20.00,  640.00,  3840.00, NULL, 'sent');

-- Invoice items
INSERT INTO `invoice_items` (`invoice_id`, `product_id`, `label`, `quantity`, `unit_price`, `total`) VALUES
-- FAC-2026-001
(1, 1, 'Ordinateur portable Dell Inspiron', 2, 8500.00, 17000.00),
-- FAC-2026-002
(2, 1, 'Ordinateur portable Dell Inspiron', 1, 8500.00, 8500.00),
-- FAC-2026-003
(3, 3, 'Clavier sans fil Logitech', 5, 350.00, 1750.00),
(3, 4, 'Souris optique',            5, 150.00,  750.00),
(3, 5, 'Ramette de papier A4',      5,  45.00,  225.00),
-- FAC-2026-004
(4, 2, 'Écran LCD 24"',             2, 2200.00, 4400.00),
(4, 9, 'Tablette Samsung Galaxy Tab',1, 2800.00, 2800.00),
-- FAC-2026-005
(5, 7, 'Chaise de bureau ergonomique', 1, 1800.00, 1800.00),
(5, 6, 'Stylos BIC (boîte de 50)',    5,   80.00,  400.00),
(5, 5, 'Ramette de papier A4',       10,   45.00,  450.00),
(5,10, 'Câble HDMI 2m',               6,   85.00,  510.00);

-- Stock movements for invoices (OUT)
INSERT INTO `stock_movements` (`product_id`, `type`, `quantity`, `note`, `created_at`) VALUES
(1, 'OUT', 2, 'Facture FAC-2026-001', '2026-01-10 10:00:00'),
(1, 'OUT', 1, 'Facture FAC-2026-002', '2026-01-20 11:00:00'),
(3, 'OUT', 5, 'Facture FAC-2026-003', '2026-02-05 09:30:00'),
(4, 'OUT', 5, 'Facture FAC-2026-003', '2026-02-05 09:30:00'),
(5, 'OUT', 5, 'Facture FAC-2026-003', '2026-02-05 09:30:00'),
(2, 'OUT', 2, 'Facture FAC-2026-004', '2026-02-15 14:00:00'),
(9, 'OUT', 1, 'Facture FAC-2026-004', '2026-02-15 14:00:00'),
(7, 'OUT', 1, 'Facture FAC-2026-005', '2026-03-01 10:00:00'),
(6, 'OUT', 5, 'Facture FAC-2026-005', '2026-03-01 10:00:00'),
(5, 'OUT',10, 'Facture FAC-2026-005', '2026-03-01 10:00:00'),
(10,'OUT', 6, 'Facture FAC-2026-005', '2026-03-01 10:00:00');
