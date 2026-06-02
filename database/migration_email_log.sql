-- Migration: email_log — tracks all sent emails
-- Run: mysql -u USER -p DB_NAME < database/migration_email_log.sql

CREATE TABLE IF NOT EXISTS `email_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `recipient` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `template_key` varchar(100) DEFAULT NULL,
  `status` enum('sent','failed') NOT NULL DEFAULT 'sent',
  `error` text DEFAULT NULL,
  `sent_by` int(10) unsigned DEFAULT NULL COMMENT 'admin user ID if triggered manually',
  `sent_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_recipient` (`recipient`),
  KEY `idx_sent_at` (`sent_at`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
