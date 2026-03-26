-- ============================================================
-- Migration: User Registration & Multi-tenancy bridge
-- Bridges the gap between live DB schema and PHP codebase.
-- Run once.
-- ============================================================

-- 1. Add PHP-expected columns to companies
ALTER TABLE `companies`
  ADD COLUMN `company_name`           VARCHAR(255)  NULL DEFAULT NULL AFTER `id`,
  ADD COLUMN `is_active`              TINYINT(1)    UNSIGNED NOT NULL DEFAULT 0,
  ADD COLUMN `user_id`                INT UNSIGNED  NULL DEFAULT NULL,
  ADD COLUMN `invoice_notes`          TEXT          NULL DEFAULT NULL,
  ADD COLUMN `default_warranty_terms` TEXT          NULL DEFAULT NULL,
  ADD COLUMN `default_payment_method` VARCHAR(50)   NULL DEFAULT NULL,
  ADD COLUMN `logo_path`              VARCHAR(500)  NULL DEFAULT NULL,
  ADD COLUMN `watermark_path`         VARCHAR(500)  NULL DEFAULT NULL,
  ADD COLUMN `watermark_opacity`      DECIMAL(3,2)  NOT NULL DEFAULT 0.15;

-- Populate company_name from name for existing rows
UPDATE `companies` SET `company_name` = `name`, `is_active` = 1;

-- 2. Add PHP-expected columns to users
ALTER TABLE `users`
  ADD COLUMN `name`                   VARCHAR(100)  NULL DEFAULT NULL,
  ADD COLUMN `onboarding_completed`   TINYINT(1)    UNSIGNED NOT NULL DEFAULT 1;

UPDATE `users` SET `name` = `full_name`;

-- 3. Add company_id to clients so records are scoped per company
-- (already exists in live DB, this is a no-op if it already has values)
UPDATE `clients` SET `company_id` = 1 WHERE `company_id` IS NULL OR `company_id` = 0;
