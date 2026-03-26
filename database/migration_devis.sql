-- Migration: devis (quotes)
-- Run once against the facturation database.

CREATE TABLE IF NOT EXISTS devis (
    id              INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    company_id      INT            NULL     DEFAULT NULL,
    use_watermark   TINYINT(1)     NOT NULL DEFAULT 0,
    devis_number    VARCHAR(50)    NOT NULL,
    client_id       INT UNSIGNED   NOT NULL,
    date            DATE           NOT NULL,
    validity_date   DATE               NULL DEFAULT NULL,
    total_ht        DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
    tax_rate        DECIMAL(5,2)   NOT NULL DEFAULT 20.00,
    tax_amount      DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
    total_ttc       DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
    notes           TEXT               NULL,
    status          ENUM('draft','sent','accepted','rejected') NOT NULL DEFAULT 'draft',
    payment_method  VARCHAR(20)        NULL DEFAULT NULL,
    created_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_devis_number (devis_number),
    KEY idx_devis_client (client_id),
    KEY idx_devis_date   (date),
    CONSTRAINT fk_devis_client  FOREIGN KEY (client_id)  REFERENCES clients(id)    ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_devis_company FOREIGN KEY (company_id) REFERENCES companies(id)  ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS devis_items (
    id         INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    devis_id   INT UNSIGNED   NOT NULL,
    product_id INT UNSIGNED       NULL DEFAULT NULL,
    label      VARCHAR(300)   NOT NULL,
    quantity   DECIMAL(10,2)  NOT NULL DEFAULT 1.00,
    unit_price DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
    total      DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
    PRIMARY KEY (id),
    KEY idx_devis_items_devis    (devis_id),
    KEY idx_devis_items_product  (product_id),
    CONSTRAINT fk_devis_items_devis   FOREIGN KEY (devis_id)   REFERENCES devis(id)     ON DELETE CASCADE  ON UPDATE CASCADE,
    CONSTRAINT fk_devis_items_product FOREIGN KEY (product_id) REFERENCES products(id)  ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
