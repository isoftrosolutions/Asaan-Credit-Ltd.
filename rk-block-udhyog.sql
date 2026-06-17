-- R K Block Udhyog — Production Insert
-- All IDs use 200+ to avoid conflicts
-- Run: source /path/to/rk-block-udhyog.sql
-- Or paste into phpMyAdmin SQL tab

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;

-- 1. Sectors (safe insert, ignores if already exist)
INSERT IGNORE INTO `sectors` (`id`, `name`, `slug`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'AgriTech', 'agritech', 1, NULL, NULL),
(2, 'CleanTech', 'cleantech', 1, NULL, NULL),
(3, 'HealthTech', 'healthtech', 1, NULL, NULL),
(4, 'FinTech', 'fintech', 1, NULL, NULL),
(5, 'EdTech', 'edtech', 1, NULL, NULL),
(6, 'Logistics', 'logistics', 1, NULL, NULL),
(7, 'Manufacturing', 'manufacturing', 1, NULL, NULL),
(8, 'Retail', 'retail', 1, NULL, NULL),
(9, 'Hospitality', 'hospitality', 1, NULL, NULL),
(10, 'RealEstate', 'realestate', 1, NULL, NULL),
(11, 'Technology', 'technology', 1, NULL, NULL),
(12, 'Food & Beverage', 'food-beverage', 1, NULL, NULL),
(13, 'E-commerce', 'ecommerce', 1, NULL, NULL),
(14, 'Construction', 'construction', 1, NULL, NULL),
(15, 'Education', 'education', 1, NULL, NULL);

-- 2. User account
INSERT IGNORE INTO `users` (`id`, `name`, `email`, `role`, `account_type`, `phone`, `province`, `district`, `profile_photo`, `company_name`, `bio`, `linkedin_url`, `website_url`, `verification_status`, `verified_at`, `is_admin`, `is_suspended`, `daily_request_count`, `daily_request_date`, `email_verified_at`, `last_login_at`, `failed_login_attempts`, `locked_until`, `password`, `remember_token`, `created_at`, `updated_at`, `deleted_at`, `company_size`, `usage_goal`, `notifications`) VALUES
(201, 'R K Block Udhyog', 'rkblockudhyog@gmail.com', 'owner', 'company', NULL, NULL, NULL, NULL, 'R K Block Udhyog', NULL, NULL, NULL, 'verified', NOW(), 0, 0, 0, NULL, NOW(), NULL, 0, NULL, '$2y$10$IWrxMNb9NYCB5hNvZK/kRez0PefjbPa8/ut1yX.EtczrtZRiMcZZa', NULL, NOW(), NOW(), NULL, '1-10', 'sell', 'email');

-- 3. Business listing
INSERT IGNORE INTO `businesses` (`id`, `user_id`, `business_name`, `slug`, `listing_type`, `sector_id`, `province`, `district`, `country_id`, `state_id`, `city_id`, `established_year`, `employee_count`, `legal_entity_type`, `annual_revenue`, `monthly_revenue`, `ebitda_pct`, `asking_price`, `funding_required`, `valuation`, `stake_offered_pct`, `loan_amount`, `loan_interest_pct`, `description`, `overview`, `products_services`, `reason_for_sale`, `assets_included`, `facilities`, `capitalization`, `thumbnail_url`, `is_published`, `is_hidden`, `status`, `is_featured`, `views`, `rating`, `created_at`, `updated_at`) VALUES
(201, 201, 'R K Block Udhyog', 'r-k-block-udhyog', 'investment', 7, 'Bagmati', 'Kathmandu', NULL, NULL, NULL, NULL, NULL, 'Sole Proprietorship', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Block manufacturing and construction materials supplier. Looking for investment to expand production capacity and purchase new machinery.', '', '', '', '', '', '', '', 1, 0, 'approved', 0, 22, NULL, NOW(), NOW());

-- 4. Business verification record
INSERT IGNORE INTO `business_verifications` (`id`, `business_id`, `email_verified`, `phone_verified`, `identity_verified`, `company_verified`, `verified_at`, `created_at`, `updated_at`) VALUES
(201, 201, 1, 1, 1, 1, NOW(), NOW(), NOW());

-- 5. Admin audit log (verification approval)
INSERT IGNORE INTO `admin_audit_log` (`id`, `admin_id`, `action`, `target_type`, `target_id`, `details`, `ip_address`, `created_at`) VALUES
(201, 1, 'approve_verification', 'user', 201, '{\"email\":\"rkblockudhyog@gmail.com\"}', '127.0.0.1', NOW());

-- 6. Notifications
INSERT IGNORE INTO `notifications` (`id`, `user_id`, `type`, `title`, `body`, `action_url`, `is_read`, `created_at`, `updated_at`) VALUES
(201, 201, 'verification', 'Verification Approved', 'Your account has been verified. You now have full access to the platform.', '/dashboard', 0, NOW(), NULL),
(202, 1, 'interest', 'New Inquiry', 'R K Block Udhyog is interested in Mountain Vista Resort & Spa', '/business/11', 0, NOW(), NULL),
(203, 201, 'interest', 'New Inquiry', 'Asaan Credit Ltd is interested in R K Block Udhyog', '/business/201', 0, NOW(), NULL);

-- 7. Email log
INSERT IGNORE INTO `email_log` (`id`, `recipient`, `subject`, `template_key`, `status`, `error`, `sent_by`, `sent_at`) VALUES
(201, 'rkblockudhyog@gmail.com', 'Your account has been verified — Asaan Capital', 'verification_approved', 'sent', NULL, NULL, NOW());

COMMIT;
