-- Migration 001: Add image upload columns for pitches
-- Production run command (adjust DB name):
--   mysql -u asaancapital_asaancapital -p asaancapital_assan_capital < database/migration_001_add_image_columns.sql
-- Or paste into phpMyAdmin SQL runner.

-- Add pitch_image column for uploaded pitch thumbnail images
-- (safe to run even if column already exists — IF NOT EXISTS guard)
SET @db = DATABASE();
SET @stmt = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'pitches' AND COLUMN_NAME = 'pitch_image') = 0,
  'ALTER TABLE pitches ADD COLUMN pitch_image VARCHAR(500) NULL AFTER pitch_video_url',
  'SELECT 1'
));
PREPARE stmt FROM @stmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
