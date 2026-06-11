-- =====================================================================
-- Asaan Marketplace — Complete Database Schema + Demo Seed
-- =====================================================================
-- Target engine : MariaDB 10.4+ (InnoDB, utf8mb4 / utf8mb4_unicode_ci)
-- Build / reset : mysql -u USER -p DB_NAME < database/final.sql
--
-- Demo login (password for every account below is  Demo@2026 ):
--   admin@investmatch.com   — admin + entrepreneur
--   investor@nepal.com      — investor
--   anjali@aarohan.com      — entrepreneur (has pitches & businesses)
--   sunita@vc.com           — investor
--
-- IMPORTANT: every table is DROPped and RECREATEd. Running this against a
-- populated database WILL erase existing data. It is meant for a fresh
-- install or a full local reset, not an in-place production migration.
--
-- All tables share collation utf8mb4_unicode_ci so cross-table UNIONs
-- (e.g. discover/search.php) do not raise "Illegal mix of collations".
-- =====================================================================

SET NAMES utf8mb4;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- Drop existing objects (children first; FK checks are disabled above)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `homepage_contents`;
DROP TABLE IF EXISTS `faqs`;
DROP TABLE IF EXISTS `blog_posts`;
DROP TABLE IF EXISTS `smart_suggestion_cache`;
DROP TABLE IF EXISTS `admin_audit_log`;
DROP TABLE IF EXISTS `password_reset_tokens`;
DROP TABLE IF EXISTS `verification_documents`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `saved_listings`;
DROP TABLE IF EXISTS `broadcasts`;
DROP TABLE IF EXISTS `reports`;
DROP TABLE IF EXISTS `matches`;
DROP TABLE IF EXISTS `interest_requests`;
DROP TABLE IF EXISTS `pitch_team_members`;
DROP TABLE IF EXISTS `pitch_media`;
DROP TABLE IF EXISTS `pitches`;
DROP TABLE IF EXISTS `investor_profiles`;
DROP TABLE IF EXISTS `advisors`;
DROP TABLE IF EXISTS `franchises`;
DROP TABLE IF EXISTS `business_photos`;
DROP TABLE IF EXISTS `businesses`;
DROP TABLE IF EXISTS `sectors`;
DROP TABLE IF EXISTS `users`;

-- ---------------------------------------------------------------------
-- Schema
-- ---------------------------------------------------------------------
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'entrepreneur',
  `account_type` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `district` varchar(255) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `website_url` varchar(255) DEFAULT NULL,
  `verification_status` varchar(255) NOT NULL DEFAULT 'unverified',
  `verified_at` timestamp NULL DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `is_suspended` tinyint(1) NOT NULL DEFAULT 0,
  `daily_request_count` int(11) NOT NULL DEFAULT 0,
  `daily_request_date` date DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `failed_login_attempts` int(11) DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `company_size` varchar(255) DEFAULT NULL,
  `usage_goal` varchar(255) DEFAULT NULL,
  `notifications` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `sectors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sectors_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `businesses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `business_name` varchar(255) NOT NULL,
  `listing_type` varchar(50) NOT NULL,
  `sector_id` bigint(20) unsigned DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `established_year` smallint(6) DEFAULT NULL,
  `employee_count` int(11) DEFAULT NULL,
  `annual_revenue` decimal(15,2) DEFAULT NULL,
  `ebitda_pct` decimal(5,2) DEFAULT NULL,
  `asking_price` decimal(15,2) DEFAULT NULL,
  `valuation` decimal(15,2) DEFAULT NULL,
  `stake_offered_pct` decimal(5,2) DEFAULT NULL,
  `loan_amount` decimal(15,2) DEFAULT NULL,
  `loan_interest_pct` decimal(5,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `reason_for_sale` text DEFAULT NULL,
  `assets_included` text DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT 0,
  `is_hidden` tinyint(1) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `views` int(11) DEFAULT 0,
  `rating` decimal(3,1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `sector_id` (`sector_id`),
  CONSTRAINT `businesses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `businesses_ibfk_2` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `business_photos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` bigint(20) unsigned NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `business_id` (`business_id`),
  CONSTRAINT `business_photos_ibfk_1` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `franchises` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `brand_name` varchar(255) NOT NULL,
  `sector_id` bigint(20) unsigned DEFAULT NULL,
  `established_year` smallint(6) DEFAULT NULL,
  `existing_units` int(11) DEFAULT NULL,
  `countries_present` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ideal_partner_profile` text DEFAULT NULL,
  `franchise_fee` decimal(15,2) DEFAULT NULL,
  `royalty_pct` decimal(5,2) DEFAULT NULL,
  `marketing_fee_pct` decimal(5,2) DEFAULT NULL,
  `total_investment_min` decimal(15,2) DEFAULT NULL,
  `total_investment_max` decimal(15,2) DEFAULT NULL,
  `expected_payback_months` int(11) DEFAULT NULL,
  `training_provided` tinyint(1) DEFAULT 1,
  `territory_protection` tinyint(1) DEFAULT 0,
  `logo_path` varchar(255) DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT 0,
  `is_hidden` tinyint(1) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `views` int(11) DEFAULT 0,
  `rating` decimal(3,1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `sector_id` (`sector_id`),
  CONSTRAINT `franchises_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `franchises_ibfk_2` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `advisors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `firm_name` varchar(255) DEFAULT NULL,
  `specialties` longtext DEFAULT NULL CHECK (json_valid(`specialties`)),
  `years_experience` int(11) DEFAULT NULL,
  `past_deals_count` int(11) DEFAULT NULL,
  `total_deal_value` decimal(15,2) DEFAULT NULL,
  `credentials` text DEFAULT NULL,
  `bar_council_id` varchar(100) DEFAULT NULL,
  `service_fee_structure` varchar(100) DEFAULT NULL,
  `fee_min` decimal(15,2) DEFAULT NULL,
  `fee_max` decimal(15,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT 0,
  `is_hidden` tinyint(1) DEFAULT 0,
  `rating` decimal(3,1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `advisors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `investor_profiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `past_investments` int(11) NOT NULL DEFAULT 0,
  `portfolio_companies` text DEFAULT NULL,
  `total_capital_deployed` decimal(15,2) DEFAULT NULL,
  `preferred_sectors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferred_sectors`)),
  `preferred_stages` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferred_stages`)),
  `ticket_min` decimal(15,2) DEFAULT NULL,
  `ticket_max` decimal(15,2) DEFAULT NULL,
  `preferred_geography` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferred_geography`)),
  `references` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `investor_profiles_user_id_foreign` (`user_id`),
  CONSTRAINT `investor_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `pitches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `tagline` varchar(140) DEFAULT NULL,
  `company_registration_number` varchar(255) DEFAULT NULL,
  `company_type` varchar(255) DEFAULT NULL,
  `short_summary` varchar(300) DEFAULT NULL,
  `product_stage` varchar(255) DEFAULT NULL,
  `problem_statement` text DEFAULT NULL,
  `solution` text DEFAULT NULL,
  `market_size` text DEFAULT NULL,
  `business_model` text DEFAULT NULL,
  `revenue_model` varchar(255) DEFAULT NULL,
  `monthly_revenue` decimal(15,2) DEFAULT NULL,
  `monthly_users` int(11) DEFAULT NULL,
  `growth_rate` decimal(5,2) DEFAULT NULL,
  `customer_retention` decimal(5,2) DEFAULT NULL,
  `traction` text DEFAULT NULL,
  `target_customers` text DEFAULT NULL,
  `competitors` varchar(255) DEFAULT NULL,
  `competitive_advantage` varchar(255) DEFAULT NULL,
  `funding_amount` decimal(15,2) DEFAULT NULL,
  `minimum_investment` decimal(15,2) DEFAULT NULL,
  `previous_funding` decimal(15,2) DEFAULT NULL,
  `previous_funding_source` varchar(255) DEFAULT NULL,
  `has_legal_disputes` tinyint(1) NOT NULL DEFAULT 0,
  `legal_details` text DEFAULT NULL,
  `existing_debt` text DEFAULT NULL,
  `business_type` varchar(255) DEFAULT NULL,
  `customer_type` varchar(255) DEFAULT NULL,
  `looking_for` varchar(255) DEFAULT NULL,
  `investor_involvement` varchar(255) DEFAULT NULL,
  `open_to_acquisition` tinyint(1) NOT NULL DEFAULT 0,
  `monthly_burn` decimal(15,2) DEFAULT NULL,
  `runway_months` int(11) DEFAULT NULL,
  `relocate_willingness` varchar(255) DEFAULT NULL,
  `matchmaking_tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`matchmaking_tags`)),
  `equity_offered` decimal(5,2) DEFAULT NULL,
  `fund_usage` text DEFAULT NULL,
  `valuation` decimal(15,2) DEFAULT NULL,
  `pitch_deck` varchar(255) DEFAULT NULL,
  `financial_projections` varchar(255) DEFAULT NULL,
  `pitch_video_url` varchar(255) DEFAULT NULL,
  `stage` varchar(255) DEFAULT NULL,
  `sector_id` bigint(20) unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_hidden` tinyint(1) NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `completeness_score` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `views` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pitches_user_id_foreign` (`user_id`),
  KEY `pitches_sector_id_foreign` (`sector_id`),
  CONSTRAINT `pitches_sector_id_foreign` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pitches_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `pitch_media` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pitch_id` bigint(20) unsigned NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pitch_media_pitch_id_foreign` (`pitch_id`),
  CONSTRAINT `pitch_media_pitch_id_foreign` FOREIGN KEY (`pitch_id`) REFERENCES `pitches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `pitch_team_members` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pitch_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `role` varchar(255) DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pitch_team_members_pitch_id_foreign` (`pitch_id`),
  CONSTRAINT `pitch_team_members_pitch_id_foreign` FOREIGN KEY (`pitch_id`) REFERENCES `pitches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `interest_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sender_id` bigint(20) unsigned NOT NULL,
  `receiver_id` bigint(20) unsigned NOT NULL,
  `pitch_id` bigint(20) unsigned DEFAULT NULL,
  `business_id` bigint(20) unsigned DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `responded_at` timestamp NULL DEFAULT NULL,
  `rejected_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `interest_requests_sender_id_foreign` (`sender_id`),
  KEY `interest_requests_receiver_id_foreign` (`receiver_id`),
  KEY `interest_requests_pitch_id_foreign` (`pitch_id`),
  KEY `business_id` (`business_id`),
  CONSTRAINT `interest_requests_ibfk_1` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `interest_requests_pitch_id_foreign` FOREIGN KEY (`pitch_id`) REFERENCES `pitches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `interest_requests_receiver_id_foreign` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `interest_requests_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `matches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `interest_request_id` bigint(20) unsigned NOT NULL,
  `user_a_id` bigint(20) unsigned NOT NULL,
  `user_b_id` bigint(20) unsigned NOT NULL,
  `context_type` varchar(50) DEFAULT NULL,
  `context_id` bigint(20) unsigned DEFAULT NULL,
  `matched_at` timestamp NULL DEFAULT current_timestamp(),
  `closed_status` varchar(50) DEFAULT 'open',
  `closed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `interest_request_id` (`interest_request_id`),
  KEY `user_a_id` (`user_a_id`),
  KEY `user_b_id` (`user_b_id`),
  CONSTRAINT `matches_ibfk_1` FOREIGN KEY (`interest_request_id`) REFERENCES `interest_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `matches_ibfk_2` FOREIGN KEY (`user_a_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `matches_ibfk_3` FOREIGN KEY (`user_b_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `reports` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reporter_id` bigint(20) unsigned NOT NULL,
  `target_type` varchar(50) NOT NULL,
  `target_id` bigint(20) unsigned NOT NULL,
  `reason` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'open',
  `resolved_by` bigint(20) unsigned DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `action_taken` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reporter_id` (`reporter_id`),
  KEY `resolved_by` (`resolved_by`),
  CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `broadcasts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sent_by` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `audience` varchar(50) NOT NULL,
  `delivery` varchar(50) NOT NULL,
  `recipients_count` int(11) DEFAULT 0,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sent_by` (`sent_by`),
  CONSTRAINT `broadcasts_ibfk_1` FOREIGN KEY (`sent_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `saved_listings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `listing_type` varchar(50) NOT NULL,
  `listing_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_save` (`user_id`,`listing_type`,`listing_id`),
  CONSTRAINT `saved_listings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  `title` text NOT NULL,
  `body` text DEFAULT NULL,
  `action_url` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_foreign` (`user_id`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `verification_documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `document_type` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `verification_documents_user_id_foreign` (`user_id`),
  KEY `verification_documents_reviewed_by_foreign` (`reviewed_by`),
  CONSTRAINT `verification_documents_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `verification_documents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `password_reset_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'password',
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_email_type` (`email`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `admin_audit_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` bigint(20) unsigned NOT NULL,
  `action` varchar(100) NOT NULL,
  `target_type` varchar(50) DEFAULT NULL,
  `target_id` bigint(20) unsigned DEFAULT NULL,
  `details` longtext DEFAULT NULL CHECK (json_valid(`details`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `admin_audit_log_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `smart_suggestion_cache` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `target_type` varchar(50) DEFAULT NULL,
  `target_id` bigint(20) unsigned DEFAULT NULL,
  `match_score` decimal(5,2) DEFAULT NULL,
  `score_breakdown` longtext DEFAULT NULL CHECK (json_valid(`score_breakdown`)),
  `cached_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_until` (`user_id`,`cached_until`),
  CONSTRAINT `smart_suggestion_cache_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `blog_posts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` varchar(500) DEFAULT NULL,
  `body` mediumtext NOT NULL,
  `author` varchar(120) NOT NULL DEFAULT 'Asaan Capital',
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blog_posts_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `faqs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `homepage_contents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `homepage_contents_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Demo / reference seed data
-- ---------------------------------------------------------------------
INSERT INTO `sectors` (`id`, `name`, `slug`, `is_active`, `created_at`, `updated_at`) VALUES (1,'AgriTech','agritech',1,NULL,NULL),(2,'CleanTech','cleantech',1,NULL,NULL),(3,'HealthTech','healthtech',1,NULL,NULL),(4,'FinTech','fintech',1,NULL,NULL),(5,'EdTech','edtech',1,NULL,NULL),(6,'Logistics','logistics',1,NULL,NULL),(7,'Manufacturing','manufacturing',1,NULL,NULL),(8,'Retail','retail',1,NULL,NULL),(9,'Hospitality','hospitality',1,NULL,NULL),(10,'RealEstate','realestate',1,NULL,NULL),(11,'Technology','technology',1,NULL,NULL),(12,'Food & Beverage','food-beverage',1,NULL,NULL),(13,'E-commerce','ecommerce',1,NULL,NULL),(14,'Construction','construction',1,NULL,NULL),(15,'Education','education',1,NULL,NULL);
INSERT INTO `users` (`id`, `name`, `email`, `role`, `account_type`, `phone`, `province`, `district`, `profile_photo`, `company_name`, `bio`, `linkedin_url`, `website_url`, `verification_status`, `verified_at`, `is_admin`, `is_suspended`, `daily_request_count`, `daily_request_date`, `email_verified_at`, `last_login_at`, `failed_login_attempts`, `locked_until`, `password`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'Admin User','admin@investmatch.com','entrepreneur',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'verified','2026-05-29 03:56:21',1,0,0,NULL,'2026-05-29 04:34:10','2026-06-01 14:16:14',0,NULL,'$2y$12$DcXPLCbxBmPesCuEaLmUH.NIYjmNm3c89OM.7nceYRPaxdTBQlM5S',NULL,'2026-05-29 03:56:21',NULL,NULL),(2,'Ramesh Thapa','investor@nepal.com','investor','individual','+977 9841 234567','Bagmati','Kathmandu',NULL,'Thapa Capital','Angel investor focused on climate and agri-tech. Previously founded two Nepali SaaS companies.',NULL,NULL,'verified','2026-05-29 03:56:21',0,0,0,NULL,'2026-05-29 04:34:10','2026-06-01 14:16:12',0,NULL,'$2y$12$DcXPLCbxBmPesCuEaLmUH.NIYjmNm3c89OM.7nceYRPaxdTBQlM5S',NULL,'2026-05-29 03:56:21',NULL,NULL),(3,'Anjali K.C.','anjali@aarohan.com','entrepreneur','company','+977 9841 765432','Bagmati','Kathmandu',NULL,'Aarohan Kitchens','Founder of Aarohan Kitchens - AI-powered cold storage for Nepali farmers.',NULL,NULL,'verified','2026-05-29 03:56:21',0,0,0,NULL,'2026-05-29 04:34:10','2026-06-01 14:25:19',0,NULL,'$2y$12$DcXPLCbxBmPesCuEaLmUH.NIYjmNm3c89OM.7nceYRPaxdTBQlM5S',NULL,'2026-05-29 03:56:21',NULL,NULL),(4,'Sunita Sharma','sunita@vc.com','investor','company','+977 9841 345678','Gandaki','Pokhara',NULL,'Himalayan Seed Fund','VC firm investing in AgriTech and CleanTech startups across Nepal.',NULL,NULL,'verified','2026-05-29 03:56:21',0,0,0,NULL,'2026-05-29 04:34:10',NULL,0,NULL,'$2y$12$DcXPLCbxBmPesCuEaLmUH.NIYjmNm3c89OM.7nceYRPaxdTBQlM5S',NULL,'2026-05-29 03:56:21',NULL,NULL);
INSERT INTO `investor_profiles` (`id`, `user_id`, `past_investments`, `portfolio_companies`, `total_capital_deployed`, `preferred_sectors`, `preferred_stages`, `ticket_min`, `ticket_max`, `preferred_geography`, `references`, `created_at`, `updated_at`) VALUES (1,2,6,'Nepal Solar Pvt Ltd, Green Agri Ventures, HealthTech Nepal, EduInnovate',50000000.00,'[\"AgriTech\",\"CleanTech\",\"HealthTech\"]','[\"Early Revenue\",\"Growth\"]',1500000.00,20000000.00,'[\"Bagmati\",\"Gandaki\"]','Mr. Rajesh Hamal - +977 9851 234567',NULL,NULL),(2,4,9,'EcoEnergy, SmartFarm Nepal, CleanWater Tech, WasteToValue, BioAgri',200000000.00,'[\"AgriTech\",\"CleanTech\",\"Deep Tech\"]','[\"MVP\",\"Early Revenue\",\"Growth\"]',4000000.00,15000000.00,'[\"Bagmati\",\"Gandaki\",\"Province 1\"]','Dr. Sagar Acharya - +977 9851 876543',NULL,NULL);
INSERT INTO `businesses` (`id`, `user_id`, `business_name`, `listing_type`, `sector_id`, `province`, `district`, `established_year`, `employee_count`, `annual_revenue`, `ebitda_pct`, `asking_price`, `stake_offered_pct`, `loan_amount`, `loan_interest_pct`, `description`, `reason_for_sale`, `assets_included`, `is_published`, `is_hidden`, `is_featured`, `views`, `rating`, `created_at`, `updated_at`) VALUES (1,3,'Enterprise Software Co.','sale',4,'Bagmati','Kathmandu',2018,45,120000000.00,18.00,120000000.00,NULL,NULL,NULL,'Cloud B2B SaaS platform serving 200+ clients across 12 countries. Strong recurring revenue with 92% retention rate.','Founder pursuing new venture in EdTech space',NULL,1,0,1,1421,9.3,'2026-05-29 04:34:11','2026-05-29 04:34:11'),(2,3,'Manufacturing Unit Expansion','partial_stake',8,'Bagmati','Kathmandu',2015,120,80000000.00,12.00,60000000.00,NULL,NULL,NULL,'Food processing unit with modern equipment. 30% YoY growth. Looking for strategic partner for expansion.','Seeking capital for new product line',NULL,1,0,0,890,8.1,'2026-05-29 04:34:11','2026-05-29 04:34:11'),(3,3,'Retail Pharmacy Chain','sale',9,'Bagmati','Lalitpur',2010,30,50000000.00,15.00,50000000.00,NULL,NULL,NULL,'Chain of 5 retail pharmacy stores in Kathmandu Valley. Established brand with loyal customer base.','Owner relocating abroad',NULL,1,0,0,670,7.5,'2026-05-29 04:34:11','2026-05-29 04:34:11'),(4,4,'Hotel Equity Stake','partial_stake',11,'Gandaki','Pokhara',2012,55,35000000.00,22.00,30000000.00,NULL,NULL,NULL,'Boutique hotel in Pokhara with 20 rooms. Strong tourism revenue. Offering 40% equity stake.',NULL,NULL,1,0,0,540,8.6,'2026-05-29 04:34:11','2026-05-29 04:34:11'),(5,4,'Tech Startup Portfolio','sale',4,'Bagmati','Kathmandu',2020,8,15000000.00,25.00,25000000.00,NULL,NULL,NULL,'Portfolio of 3 bootstrapped SaaS products with 5,000+ paying users across SEA.',NULL,NULL,1,0,0,310,7.8,'2026-05-29 04:34:11','2026-05-29 04:34:11');
INSERT INTO `pitches` (`id`, `user_id`, `tagline`, `company_registration_number`, `company_type`, `short_summary`, `product_stage`, `problem_statement`, `solution`, `market_size`, `business_model`, `revenue_model`, `monthly_revenue`, `monthly_users`, `growth_rate`, `customer_retention`, `traction`, `target_customers`, `competitors`, `competitive_advantage`, `funding_amount`, `minimum_investment`, `previous_funding`, `previous_funding_source`, `has_legal_disputes`, `legal_details`, `existing_debt`, `business_type`, `customer_type`, `looking_for`, `investor_involvement`, `open_to_acquisition`, `monthly_burn`, `runway_months`, `relocate_willingness`, `matchmaking_tags`, `equity_offered`, `fund_usage`, `valuation`, `pitch_deck`, `financial_projections`, `pitch_video_url`, `stage`, `sector_id`, `is_active`, `is_hidden`, `is_featured`, `completeness_score`, `is_published`, `views`, `created_at`, `updated_at`) VALUES (1,3,'AI-powered cold storage reducing post-harvest losses for 2,400+ farmers across Nepal.',NULL,NULL,NULL,NULL,'34% of Nepal perishable produce is lost before reaching market due to lack of reliable cold storage. Small farmers lose NPR 18,000-40,000 per season.','Low-cost, solar-hybrid smart cold rooms with IoT monitoring and AI demand forecasting. Farmers pay per use via mobile.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2,400 farmers onboarded (Q1 2026)\nNPR 9.2M revenue run-rate\n3 provinces live, 2 more in pipeline\nPartnership with Nepal Agricultural Research Council',NULL,NULL,NULL,28000000.00,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,12.00,NULL,NULL,NULL,NULL,NULL,'early',1,1,0,0,0,0,1,NULL,NULL),(2,3,'Making quality education accessible in rural areas through AI-powered learning platforms',NULL,NULL,'EdTech for Rural Nepal - AI-powered learning platform',NULL,'Rural Nepal lacks access to quality education. 70% of students in rural areas have no access to digital learning.','AI-powered mobile learning platform that works offline. Adaptive curriculum in Nepali language.','NPR 500 Cr TAM in Nepal alone',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,5000000.00,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,15.00,NULL,33333333.00,NULL,NULL,NULL,'seed',4,1,0,1,0,1,1,'2026-05-29 04:34:33','2026-05-29 04:34:33');
INSERT INTO `franchises` (`id`, `user_id`, `brand_name`, `sector_id`, `established_year`, `existing_units`, `countries_present`, `description`, `ideal_partner_profile`, `franchise_fee`, `royalty_pct`, `marketing_fee_pct`, `total_investment_min`, `total_investment_max`, `expected_payback_months`, `training_provided`, `territory_protection`, `logo_path`, `is_published`, `is_hidden`, `is_featured`, `views`, `rating`, `created_at`, `updated_at`) VALUES (1,2,'Nepal Bites Express',9,2018,12,'Nepal','Fast-casual Nepali restaurant chain. Serving authentic momo, chowmein, and thalis in modern format.','Experienced restaurateur with passion for Nepali cuisine. Minimum 2 years F&B experience.',500000.00,5.00,2.00,2500000.00,5000000.00,18,1,1,NULL,1,0,1,1,8.9,'2026-05-29 04:34:33','2026-05-29 04:34:33');
INSERT INTO `advisors` (`id`, `user_id`, `firm_name`, `specialties`, `years_experience`, `past_deals_count`, `total_deal_value`, `credentials`, `bar_council_id`, `service_fee_structure`, `fee_min`, `fee_max`, `description`, `is_published`, `is_hidden`, `rating`, `created_at`, `updated_at`) VALUES (1,2,'Thapa Advisory Services','[\"m_and_a\",\"brokerage\",\"due_diligence\"]',15,42,850000000.00,'CA, CFA Level 3',NULL,'success_fee',50000.00,500000.00,'15+ years in M&A advisory. Have successfully closed 42 deals across various sectors in Nepal.',1,0,9.1,'2026-05-29 04:34:33','2026-05-29 04:34:33');
INSERT INTO `blog_posts` (`id`, `title`, `slug`, `excerpt`, `body`, `author`, `status`, `published_at`, `created_at`, `updated_at`) VALUES (1,'How to Value a Small Business in Nepal','how-to-value-a-small-business-in-nepal','A practical walkthrough of the three methods investors use to value SMEs — and how to apply them to a Nepali business.','Valuing a business is part science, part judgement. Most buyers and investors lean on three approaches, and the truth usually sits somewhere between them.\n\nThe first is trading comparables: looking at how publicly listed companies in the same sector are priced relative to their earnings (EV/EBITDA). The second is transaction comparables, which uses the prices paid in actual deals for similar private businesses. The third is discounted cash flow, which projects future cash and discounts it back to today.\n\nFor a Nepali SME, comparable multiples are usually the most reliable starting point. Apply a sector multiple to your EBITDA, then adjust for growth and how long you have been operating. Our free calculator does exactly this — try it before you talk to any buyer.','Asaan Capital','published','2026-05-10 03:15:00','2026-05-10 03:15:00','2026-05-10 03:15:00'),(2,'5 Things Investors Look For Before They Fund You','5-things-investors-look-for-before-they-fund-you','Capital follows conviction. Here is what convinces a Nepali investor to move from interest to a cheque.','Raising money is less about a perfect pitch and more about reducing the investor\'s perceived risk. Five things move the needle more than anything else.\n\nClean financials. If your numbers are organised and believable, you are already ahead of most. Traction. Revenue, repeat customers, or signed contracts speak louder than projections. A clear use of funds. Investors want to know exactly what their money buys and what milestone it unlocks.\n\nA capable team is the fourth — people back people. And finally, a realistic valuation. Over-pricing your round is the fastest way to stall a deal. Get these five right and the conversation changes completely.','Asaan Capital','published','2026-05-20 03:15:00','2026-05-20 03:15:00','2026-05-20 03:15:00'),(3,'Selling Your Business Confidentially: A Short Guide','selling-your-business-confidentially-a-short-guide','How to find a buyer without tipping off staff, suppliers, and competitors.','The biggest fear most owners have when selling is exposure. If word gets out too early, staff get nervous, competitors pounce, and suppliers renegotiate.\n\nThe answer is a staged disclosure. Start with an anonymous profile that shares the shape of the opportunity — sector, size, and financial highlights — without naming the business. Only when a genuine, verified buyer expresses interest do you reveal your identity, and even then on your terms.\n\nThis is exactly how matching works on our platform: contact details stay private until there is mutual interest. You stay in control of who learns what, and when.','Asaan Capital','published','2026-05-28 03:15:00','2026-05-28 03:15:00','2026-05-28 03:15:00');
INSERT INTO `faqs` (`id`, `question`, `answer`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES (1,'How does the platform ensure profiles are genuine?','Every profile is manually reviewed by our analysts. We verify email, phone, and social accounts. Businesses also undergo document verification (GST, registration certificate, financials).',1,1,NULL,NULL),(2,'When do contact details get shared?','Contact info is revealed only when both parties express mutual interest. This prevents unsolicited outreach and protects your identity until you are ready to connect.',2,1,NULL,NULL),(3,'What types of transactions are supported?','Full business sale, partial stake sale, investment, business loan, asset sale, franchise, and distributorship opportunities across all industries and regions.',3,1,NULL,NULL),(4,'Is there a fee to use InvestMatch?','Basic registration is free. Premium plans start at NPR 25,500. A 1% finders fee applies post deal closure on paid plans. No hidden charges.',4,1,NULL,NULL),(5,'How long does verification take?','Verification typically takes 24-48 hours after you upload your documents. Our admin team reviews each submission manually.',5,1,NULL,NULL);
INSERT INTO `homepage_contents` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES (1,'hero_title','Connect with <span class=\"highlight\">Investors</span>.<br>Sell or Grow Your Business <span class=\"highlight\">Faster</span>.',NULL,NULL),(2,'hero_subtitle','The premium marketplace where verified business owners meet qualified investors, buyers, and franchise partners. Close deals with confidence.',NULL,NULL),(3,'stats_businesses','67,500+',NULL,NULL),(4,'stats_investors','44,000+',NULL,NULL),(5,'stats_matches','12,800+',NULL,NULL),(6,'stats_deal_value','NPR 850 Cr+',NULL,NULL);
INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `body`, `action_url`, `is_read`, `created_at`, `updated_at`) VALUES (1,1,'new_user','New User Registered','Bikash Rana registered as a business owner','/admin/verification',0,'2026-05-29 04:34:33',NULL),(2,2,'match','Match Made!','You have been matched with Enterprise Software Co.','/connections',0,'2026-05-29 04:34:33',NULL),(3,3,'interest','New Interest Request','Ramesh Thapa has expressed interest in your business','/connections',0,'2026-05-29 04:34:33',NULL);

-- Demo accounts for remaining roles (password for all: Demo@2026)
INSERT INTO `users` (`id`, `name`, `email`, `role`, `account_type`, `phone`, `province`, `district`, `profile_photo`, `company_name`, `bio`, `linkedin_url`, `website_url`, `verification_status`, `verified_at`, `is_admin`, `is_suspended`, `daily_request_count`, `daily_request_date`, `email_verified_at`, `last_login_at`, `failed_login_attempts`, `locked_until`, `password`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(5,'Bikash Rana','owner@nepal.com','business_owner','company','+977 9841 556677','Bagmati','Kathmandu',NULL,'Rana Retail Group','Second-generation retailer running a profitable supermarket chain in the Kathmandu Valley.',NULL,NULL,'verified','2026-05-30 04:00:00',0,0,0,NULL,'2026-05-30 04:00:00',NULL,0,NULL,'$2y$12$DcXPLCbxBmPesCuEaLmUH.NIYjmNm3c89OM.7nceYRPaxdTBQlM5S',NULL,'2026-05-30 04:00:00',NULL,NULL),
(6,'Maya Gurung','franchise@nepal.com','franchisor','company','+977 9846 112233','Gandaki','Pokhara',NULL,'Himalaya Brews','Founder of a fast-growing specialty coffee brand expanding through franchising across Nepal.',NULL,NULL,'verified','2026-05-30 04:00:00',0,0,0,NULL,'2026-05-30 04:00:00',NULL,0,NULL,'$2y$12$DcXPLCbxBmPesCuEaLmUH.NIYjmNm3c89OM.7nceYRPaxdTBQlM5S',NULL,'2026-05-30 04:00:00',NULL,NULL),
(7,'Prakash Joshi','advisor@nepal.com','advisor','company','+977 9851 998877','Bagmati','Lalitpur',NULL,'Joshi & Partners','Corporate lawyer and M&A advisor with two decades of cross-border transaction experience.',NULL,NULL,'verified','2026-05-30 04:00:00',0,0,0,NULL,'2026-05-30 04:00:00',NULL,0,NULL,'$2y$12$DcXPLCbxBmPesCuEaLmUH.NIYjmNm3c89OM.7nceYRPaxdTBQlM5S',NULL,'2026-05-30 04:00:00',NULL,NULL);
INSERT INTO `businesses` (`id`, `user_id`, `business_name`, `listing_type`, `sector_id`, `province`, `district`, `established_year`, `employee_count`, `annual_revenue`, `ebitda_pct`, `asking_price`, `stake_offered_pct`, `loan_amount`, `loan_interest_pct`, `description`, `reason_for_sale`, `assets_included`, `is_published`, `is_hidden`, `is_featured`, `views`, `rating`, `created_at`, `updated_at`) VALUES
(6,5,'Rana Supermart Chain','sale',8,'Bagmati','Kathmandu',2014,85,95000000.00,16.00,90000000.00,NULL,NULL,NULL,'Chain of 6 neighbourhood supermarkets across the Kathmandu Valley with strong daily footfall and established supplier relationships.','Owner consolidating to focus on wholesale distribution',NULL,1,0,1,760,8.4,'2026-05-30 04:00:00','2026-05-30 04:00:00'),
(7,5,'Himalayan Handicrafts Export','partial_stake',13,'Bagmati','Bhaktapur',2017,40,42000000.00,20.00,30000000.00,30.00,NULL,NULL,'Export-focused handicraft business shipping authentic Nepali goods to Europe and North America. Seeking a growth partner for 30% equity.',NULL,NULL,1,0,0,410,7.9,'2026-05-30 04:00:00','2026-05-30 04:00:00');
INSERT INTO `franchises` (`id`, `user_id`, `brand_name`, `sector_id`, `established_year`, `existing_units`, `countries_present`, `description`, `ideal_partner_profile`, `franchise_fee`, `royalty_pct`, `marketing_fee_pct`, `total_investment_min`, `total_investment_max`, `expected_payback_months`, `training_provided`, `territory_protection`, `logo_path`, `is_published`, `is_hidden`, `is_featured`, `views`, `rating`, `created_at`, `updated_at`) VALUES
(2,6,'Everest Coffee House',9,2016,8,'Nepal','Specialty coffee franchise serving single-origin Nepali beans in a modern cafe format. Proven unit economics across 8 company-owned outlets.','Hospitality-minded operator with a prime retail location and minimum 2 years of customer-facing experience.',400000.00,5.00,2.00,2000000.00,4000000.00,20,1,1,NULL,1,0,1,520,8.7,'2026-05-30 04:00:00','2026-05-30 04:00:00');
INSERT INTO `advisors` (`id`, `user_id`, `firm_name`, `specialties`, `years_experience`, `past_deals_count`, `total_deal_value`, `credentials`, `bar_council_id`, `service_fee_structure`, `fee_min`, `fee_max`, `description`, `is_published`, `is_hidden`, `rating`, `created_at`, `updated_at`) VALUES
(2,7,'Joshi & Partners','[\"legal\",\"m_and_a\",\"consulting\"]',20,28,420000000.00,'LLB, Company Secretary','BAR-2024-1187','hourly',25000.00,300000.00,'Lalitpur-based corporate advisory firm specialising in mergers, acquisitions and due diligence for Nepali SMEs.',1,0,8.5,'2026-05-30 04:00:00','2026-05-30 04:00:00');

SET FOREIGN_KEY_CHECKS = 1;
