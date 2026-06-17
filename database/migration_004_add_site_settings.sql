CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT DEFAULT NULL,
  `setting_type` ENUM('text','textarea','image','boolean') NOT NULL DEFAULT 'text',
  `description` VARCHAR(255) DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `site_settings` (setting_key, setting_value, setting_type, description) VALUES
('payment_qr_code', '', 'image', 'QR code image for payment (upload PNG/JPG)'),
('payment_instructions', '1. Scan QR\n2. Complete payment\n3. Upload receipt', 'textarea', 'Instructions shown on payment page'),
('payment_phone', '', 'text', 'Phone number for payment (eSewa/Khalti)'),
('site_tagline', 'Connect. Grow. Invest.', 'text', 'Site tagline shown on premium page'),
('premium_contact_email', '', 'text', 'Contact email for premium inquiries');
