-- ─── Multi-company support ──────────────────────────────────

CREATE TABLE IF NOT EXISTS companies (
    id                     INT AUTO_INCREMENT PRIMARY KEY,
    company_name           VARCHAR(200) NOT NULL,
    address                TEXT,
    phone                  VARCHAR(50),
    email                  VARCHAR(150),
    tax_id                 VARCHAR(100),
    logo_path              VARCHAR(255)    DEFAULT NULL,
    watermark_path         VARCHAR(255)    DEFAULT NULL,
    watermark_opacity      DECIMAL(3,2)    NOT NULL DEFAULT 0.15,
    invoice_notes          TEXT,
    invoice_footer         VARCHAR(500),
    default_warranty_terms TEXT,
    default_payment_method VARCHAR(20)     DEFAULT NULL,
    is_active              TINYINT(1)      NOT NULL DEFAULT 0,
    created_at             TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at             TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Link documents to a company (nullable – old docs keep NULL, fall back to company_settings)
ALTER TABLE invoices
    ADD COLUMN company_id    INT NULL DEFAULT NULL AFTER id,
    ADD COLUMN use_watermark TINYINT(1) NOT NULL DEFAULT 0 AFTER company_id,
    ADD CONSTRAINT fk_invoices_company
        FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL;

ALTER TABLE guarantee_certificates
    ADD COLUMN company_id    INT NULL DEFAULT NULL AFTER id,
    ADD COLUMN use_watermark TINYINT(1) NOT NULL DEFAULT 0 AFTER company_id,
    ADD CONSTRAINT fk_guarantee_company
        FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL;

ALTER TABLE delivery_notes
    ADD COLUMN company_id    INT NULL DEFAULT NULL AFTER id,
    ADD COLUMN use_watermark TINYINT(1) NOT NULL DEFAULT 0 AFTER company_id,
    ADD CONSTRAINT fk_delivery_company
        FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL;
