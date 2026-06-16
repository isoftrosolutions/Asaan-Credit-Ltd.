-- Migration 002: Add is_premium column for contact visibility gating
-- Production run command (adjust DB name):
--   mysql -u asaancapital_asaancapital -p asaancapital_assan_capital < database/migration_002_add_is_premium.sql
-- Or paste into phpMyAdmin SQL runner.

-- Add is_premium column to users table
-- (safe to run even if column already exists — IF NOT EXISTS guard)
SET @db = DATABASE();
SET @stmt = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'users' AND COLUMN_NAME = 'is_premium') = 0,
  'ALTER TABLE users ADD COLUMN is_premium tinyint(1) NOT NULL DEFAULT 0 AFTER is_admin',
  'SELECT 1'
));
PREPARE stmt FROM @stmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
