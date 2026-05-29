-- Asaan Marketplace — Auth Module DB Migration
-- Adds type column to password_reset_tokens for dual-purpose (password reset + email verification)
-- Run: mysql -u root invest_match < database/migration-auth-tokens.sql

ALTER TABLE password_reset_tokens
  DROP PRIMARY KEY,
  ADD COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST,
  ADD COLUMN type VARCHAR(50) NOT NULL DEFAULT 'password' AFTER token,
  ADD INDEX idx_email_type (email, type);
