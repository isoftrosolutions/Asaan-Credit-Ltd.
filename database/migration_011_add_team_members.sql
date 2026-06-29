-- Migration 011: Add team members table
CREATE TABLE IF NOT EXISTS `team_members` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `position` VARCHAR(255) NOT NULL,
  `bio` TEXT DEFAULT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `photo` VARCHAR(500) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `team_members` (`name`, `position`, `bio`, `phone`, `sort_order`, `is_active`) VALUES
('Shyam Sundar Yadav', 'CEO & Founder', 'Visionary leader with extensive experience in business development, M&A advisory, and strategic financial consulting. Driving Asaan Capital\'s mission to transform Nepal\'s investment landscape.', '9848714991', 1, 1),
('Rabin Thapa', 'Head of Advisory', '12+ years in corporate finance, project finance, and due diligence. Previously served at leading financial institutions in Nepal.', '9848714992', 2, 1);
