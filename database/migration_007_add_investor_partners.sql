CREATE TABLE IF NOT EXISTS `investor_partners` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(160) NOT NULL,
  `partner_type` VARCHAR(120) DEFAULT NULL,
  `logo_path` VARCHAR(255) DEFAULT NULL,
  `initials` VARCHAR(8) DEFAULT NULL,
  `accent_color` VARCHAR(20) NOT NULL DEFAULT '#98202A',
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_investor_partners_active_sort` (`is_active`, `sort_order`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

