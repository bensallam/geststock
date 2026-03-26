-- Add custom columns support to invoices
-- Run once. Safe to re-run only if columns don't already exist.

ALTER TABLE `invoices`
  ADD COLUMN `custom_columns` JSON NULL DEFAULT NULL AFTER `notes`;

ALTER TABLE `invoice_items`
  ADD COLUMN `custom_data` JSON NULL DEFAULT NULL AFTER `total`;
