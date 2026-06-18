CREATE TABLE IF NOT EXISTS `business_documents` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `business_id` BIGINT(20) UNSIGNED NOT NULL,
  `original_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `file_size` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `file_type` VARCHAR(100) NOT NULL DEFAULT '',
  `description` VARCHAR(500) DEFAULT NULL,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `download_count` INT(11) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_business_id` (`business_id`),
  CONSTRAINT `fk_business_documents_business` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
