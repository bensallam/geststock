-- ============================================================
-- Migration: onboarding_completed column on users table
-- Run once: mysql -u root -p facturation < database/migration_onboarding.sql
-- ============================================================

USE `facturation`;

-- Skip if column already exists (procedure workaround for MySQL < 8.0)
SET @col_exists = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME   = 'users'
    AND COLUMN_NAME  = 'onboarding_completed'
);

SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `users` ADD COLUMN `onboarding_completed` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER `password`',
  'SELECT "Column already exists"'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Existing users are already set up — mark them as completed
-- so they don't get redirected through the wizard unnecessarily.
UPDATE `users` SET `onboarding_completed` = 1 WHERE `onboarding_completed` = 0;
