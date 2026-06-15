-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 15, 2026 at 07:08 AM
-- Server version: 10.11.18-MariaDB
-- PHP Version: 8.4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `asaancapital_assan_capital`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_audit_log`
--

CREATE TABLE `admin_audit_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(100) NOT NULL,
  `target_type` varchar(50) DEFAULT NULL,
  `target_id` bigint(20) UNSIGNED DEFAULT NULL,
  `details` longtext DEFAULT NULL CHECK (json_valid(`details`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_audit_log`
--

INSERT INTO `admin_audit_log` (`id`, `admin_id`, `action`, `target_type`, `target_id`, `details`, `ip_address`, `created_at`) VALUES
(1, 1, 'edit_user_role', 'user', 10, '{\"role\":\"entrepreneur\"}', '27.34.73.156', '2026-06-09 16:59:38'),
(2, 1, 'suspend_user', 'user', 10, NULL, '27.34.73.156', '2026-06-09 17:00:01'),
(3, 1, 'update_email_settings', 'email_settings', 1, '{\"is_active\":0}', '27.34.73.156', '2026-06-09 17:05:57'),
(4, 1, 'update_email_settings', 'email_settings', 1, '{\"is_active\":1}', '27.34.73.156', '2026-06-09 17:06:03'),
(5, 1, 'update_email_settings', 'email_settings', 1, '{\"is_active\":1}', '27.34.73.156', '2026-06-09 17:06:12'),
(6, 1, 'update_email_template', 'email_templates', 8, NULL, '27.34.73.156', '2026-06-09 17:06:38'),
(7, 1, 'update_email_template', 'email_templates', 1, NULL, '27.34.73.156', '2026-06-09 17:06:53'),
(8, 1, 'update_email_template', 'email_templates', 7, NULL, '27.34.73.156', '2026-06-09 17:07:07'),
(9, 1, 'update_email_template', 'email_templates', 5, NULL, '27.34.73.156', '2026-06-09 17:07:19'),
(10, 1, 'update_email_settings', 'email_settings', 1, '{\"is_active\":1}', '103.167.233.230', '2026-06-11 10:28:11'),
(11, 1, 'approve_verification', 'user', 21, '{\"email\":\"pdewbrath@gmail.com\"}', '103.167.233.230', '2026-06-11 11:40:35'),
(12, 1, 'approve_verification', 'user', 20, '{\"email\":\"isoftrosolutions@gmail.com\"}', '103.167.233.230', '2026-06-11 11:40:39'),
(13, 1, 'approve_verification', 'user', 19, '{\"email\":\"rkblockudhyog@gmail.com\"}', '103.167.233.230', '2026-06-11 11:40:41'),
(14, 1, 'approve_verification', 'user', 22, '{\"email\":\"asaancredit@gmail.com\"}', '103.167.233.50', '2026-06-12 12:01:32'),
(15, 1, 'flag_pitch', 'pitch', 1, '{\"reason\":\"Flagged for review\"}', '103.104.234.102', '2026-06-12 16:16:58'),
(16, 1, 'flag_pitch', 'pitch', 1, '{\"reason\":\"Flagged for review\"}', '103.104.234.102', '2026-06-12 16:17:09'),
(17, 1, 'unhide_pitch', 'pitch', 1, NULL, '103.104.234.102', '2026-06-12 16:17:17'),
(18, 1, 'unhide_pitch', 'pitch', 1, NULL, '103.104.234.102', '2026-06-12 16:17:37'),
(19, 1, 'flag_pitch', 'pitch', 1, '{\"reason\":\"Flagged for review\"}', '103.104.234.102', '2026-06-12 16:17:46'),
(20, 1, 'flag_pitch', 'pitch', 1, '{\"reason\":\"Flagged for review\"}', '103.104.234.102', '2026-06-12 16:17:48'),
(21, 1, 'unhide_pitch', 'pitch', 1, NULL, '103.104.234.102', '2026-06-12 16:18:07'),
(22, 1, 'resolve_report', 'report', 1, '{\"action_taken\":\"Action\"}', '103.104.234.102', '2026-06-12 16:19:10'),
(23, 1, 'toggle_verification', 'business_verifications', 33, '{\"field\":\"email_verified\"}', '103.104.234.102', '2026-06-12 16:30:17'),
(24, 1, 'toggle_verification', 'business_verifications', 33, '{\"field\":\"phone_verified\"}', '103.104.234.102', '2026-06-12 16:30:21');

-- --------------------------------------------------------

--
-- Table structure for table `advisors`
--

CREATE TABLE `advisors` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `advisors`
--

INSERT INTO `advisors` (`id`, `user_id`, `firm_name`, `specialties`, `years_experience`, `past_deals_count`, `total_deal_value`, `credentials`, `bar_council_id`, `service_fee_structure`, `fee_min`, `fee_max`, `description`, `is_published`, `is_hidden`, `rating`, `created_at`, `updated_at`) VALUES
(1, 2, 'Thapa Advisory Services', '[\"m_and_a\",\"brokerage\",\"due_diligence\"]', 15, 42, 850000000.00, 'CA, CFA Level 3', NULL, 'success_fee', 50000.00, 500000.00, '15+ years in M&A advisory. Have successfully closed 42 deals across various sectors in Nepal.', 1, 0, 9.1, '2026-05-29 04:34:33', '2026-05-29 04:34:33'),
(2, 7, 'Joshi & Partners', '[\"legal\",\"m_and_a\",\"consulting\"]', 20, 28, 420000000.00, 'LLB, Company Secretary', 'BAR-2024-1187', 'hourly', 25000.00, 300000.00, 'Lalitpur-based corporate advisory firm specialising in M&A and due diligence for Nepali SMEs.', 1, 0, 8.5, '2026-05-29 22:15:00', '2026-05-29 22:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts`
--

CREATE TABLE `blog_posts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` varchar(500) DEFAULT NULL,
  `body` mediumtext NOT NULL,
  `author` varchar(120) NOT NULL DEFAULT 'Asaan Capital',
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blog_posts`
--

INSERT INTO `blog_posts` (`id`, `title`, `slug`, `excerpt`, `body`, `author`, `status`, `published_at`, `created_at`, `updated_at`) VALUES
(1, 'How to Value a Small Business in Nepal', 'how-to-value-a-small-business-in-nepal', 'A practical walkthrough of the three methods investors use to value SMEs ΓÇö and how to apply them to a Nepali business.', 'Valuing a business is part science, part judgement. Most buyers and investors lean on three approaches, and the truth usually sits somewhere between them.\n\nThe first is trading comparables: looking at how publicly listed companies in the same sector are priced relative to their earnings (EV/EBITDA). The second is transaction comparables, which uses the prices paid in actual deals for similar private businesses. The third is discounted cash flow, which projects future cash and discounts it back to today.\n\nFor a Nepali SME, comparable multiples are usually the most reliable starting point. Apply a sector multiple to your EBITDA, then adjust for growth and how long you have been operating. Our free calculator does exactly this ΓÇö try it before you talk to any buyer.', 'Asaan Capital', 'published', '2026-05-10 03:15:00', '2026-05-10 03:15:00', '2026-05-10 03:15:00'),
(2, '5 Things Investors Look For Before They Fund You', '5-things-investors-look-for-before-they-fund-you', 'Capital follows conviction. Here is what convinces a Nepali investor to move from interest to a cheque.', 'Raising money is less about a perfect pitch and more about reducing the investor\'s perceived risk. Five things move the needle more than anything else.\n\nClean financials. If your numbers are organised and believable, you are already ahead of most. Traction. Revenue, repeat customers, or signed contracts speak louder than projections. A clear use of funds. Investors want to know exactly what their money buys and what milestone it unlocks.\n\nA capable team is the fourth ΓÇö people back people. And finally, a realistic valuation. Over-pricing your round is the fastest way to stall a deal. Get these five right and the conversation changes completely.', 'Asaan Capital', 'published', '2026-05-20 03:15:00', '2026-05-20 03:15:00', '2026-05-20 03:15:00'),
(3, 'Selling Your Business Confidentially: A Short Guide', 'selling-your-business-confidentially-a-short-guide', 'How to find a buyer without tipping off staff, suppliers, and competitors.', 'The biggest fear most owners have when selling is exposure. If word gets out too early, staff get nervous, competitors pounce, and suppliers renegotiate.\n\nThe answer is a staged disclosure. Start with an anonymous profile that shares the shape of the opportunity ΓÇö sector, size, and financial highlights ΓÇö without naming the business. Only when a genuine, verified buyer expresses interest do you reveal your identity, and even then on your terms.\n\nThis is exactly how matching works on our platform: contact details stay private until there is mutual interest. You stay in control of who learns what, and when.', 'Asaan Capital', 'published', '2026-05-28 03:15:00', '2026-05-28 03:15:00', '2026-05-28 03:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `broadcasts`
--

CREATE TABLE `broadcasts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sent_by` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `audience` varchar(50) NOT NULL,
  `delivery` varchar(50) NOT NULL,
  `recipients_count` int(11) DEFAULT 0,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `businesses`
--

CREATE TABLE `businesses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `business_name` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `listing_type` varchar(50) NOT NULL,
  `sector_id` bigint(20) UNSIGNED DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `country_id` bigint(20) UNSIGNED DEFAULT NULL,
  `state_id` bigint(20) UNSIGNED DEFAULT NULL,
  `city_id` bigint(20) UNSIGNED DEFAULT NULL,
  `established_year` smallint(6) DEFAULT NULL,
  `employee_count` int(11) DEFAULT NULL,
  `legal_entity_type` varchar(100) DEFAULT NULL,
  `annual_revenue` decimal(15,2) DEFAULT NULL,
  `monthly_revenue` decimal(15,2) DEFAULT NULL,
  `ebitda_pct` decimal(5,2) DEFAULT NULL,
  `asking_price` decimal(15,2) DEFAULT NULL,
  `funding_required` decimal(15,2) DEFAULT NULL,
  `valuation` decimal(15,2) DEFAULT NULL,
  `stake_offered_pct` decimal(5,2) DEFAULT NULL,
  `loan_amount` decimal(15,2) DEFAULT NULL,
  `loan_interest_pct` decimal(5,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `overview` longtext DEFAULT NULL,
  `products_services` longtext DEFAULT NULL,
  `reason_for_sale` text DEFAULT NULL,
  `assets_included` text DEFAULT NULL,
  `facilities` longtext DEFAULT NULL,
  `capitalization` longtext DEFAULT NULL,
  `thumbnail_url` varchar(500) DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT 0,
  `is_hidden` tinyint(1) DEFAULT 0,
  `status` enum('draft','pending','approved','rejected','sold') NOT NULL DEFAULT 'approved',
  `is_featured` tinyint(1) DEFAULT 0,
  `views` int(11) DEFAULT 0,
  `rating` decimal(3,1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `businesses`
--

INSERT INTO `businesses` (`id`, `user_id`, `business_name`, `slug`, `listing_type`, `sector_id`, `province`, `district`, `country_id`, `state_id`, `city_id`, `established_year`, `employee_count`, `legal_entity_type`, `annual_revenue`, `monthly_revenue`, `ebitda_pct`, `asking_price`, `funding_required`, `valuation`, `stake_offered_pct`, `loan_amount`, `loan_interest_pct`, `description`, `overview`, `products_services`, `reason_for_sale`, `assets_included`, `facilities`, `capitalization`, `thumbnail_url`, `is_published`, `is_hidden`, `status`, `is_featured`, `views`, `rating`, `created_at`, `updated_at`) VALUES
(1, 3, 'Enterprise Software Co.', 'enterprise-software-co-1', 'sale', 4, 'Bagmati', 'Kathmandu', 1, 3, 27, 2018, 45, NULL, 120000000.00, NULL, 18.00, 120000000.00, NULL, NULL, NULL, NULL, NULL, 'Cloud B2B SaaS platform serving 200+ clients across 12 countries. Strong recurring revenue with 92% retention rate.', NULL, NULL, 'Founder pursuing new venture in EdTech space', NULL, NULL, NULL, NULL, 1, 0, 'approved', 1, 1450, 9.3, '2026-05-29 04:34:11', '2026-05-29 04:34:11'),
(2, 3, 'Manufacturing Unit Expansion', 'manufacturing-unit-expansion-2', 'partial_stake', 7, 'Bagmati', 'Kathmandu', 1, 3, 27, 2015, 120, NULL, 80000000.00, NULL, 12.00, 60000000.00, NULL, NULL, NULL, NULL, NULL, 'Food processing unit with modern equipment. 30% YoY growth. Looking for strategic partner for expansion.', NULL, NULL, 'Seeking capital for new product line', NULL, NULL, NULL, NULL, 1, 0, 'approved', 0, 895, 8.1, '2026-05-29 04:34:11', '2026-05-29 04:34:11'),
(3, 3, 'Retail Pharmacy Chain', 'retail-pharmacy-chain-3', 'sale', 8, 'Bagmati', 'Lalitpur', 1, 3, 29, 2010, 30, NULL, 50000000.00, NULL, 15.00, 50000000.00, NULL, NULL, NULL, NULL, NULL, 'Chain of 5 retail pharmacy stores in Kathmandu Valley. Established brand with loyal customer base.', NULL, NULL, 'Owner relocating abroad', NULL, NULL, NULL, NULL, 1, 0, 'approved', 0, 675, 7.5, '2026-05-29 04:34:11', '2026-05-29 04:34:11'),
(4, 4, 'Hotel Equity Stake', 'hotel-equity-stake-4', 'partial_stake', 9, 'Gandaki', 'Pokhara', 1, 4, NULL, 2012, 55, NULL, 35000000.00, NULL, 22.00, 30000000.00, NULL, NULL, NULL, NULL, NULL, 'Boutique hotel in Pokhara with 20 rooms. Strong tourism revenue. Offering 40% equity stake.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'approved', 0, 544, 8.6, '2026-05-29 04:34:11', '2026-05-29 04:34:11'),
(5, 4, 'Tech Startup Portfolio', 'tech-startup-portfolio-5', 'sale', 4, 'Bagmati', 'Kathmandu', 1, 3, 27, 2020, 8, NULL, 15000000.00, NULL, 25.00, 25000000.00, NULL, NULL, NULL, NULL, NULL, 'Portfolio of 3 bootstrapped SaaS products with 5,000+ paying users across SEA.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'approved', 0, 315, 7.8, '2026-05-29 04:34:11', '2026-05-29 04:34:11'),
(6, 5, 'Rana Supermart Chain', 'rana-supermart-chain-6', 'sale', 8, 'Bagmati', 'Kathmandu', 1, 3, 27, 2014, 85, NULL, 95000000.00, NULL, 16.00, 90000000.00, NULL, NULL, NULL, NULL, NULL, 'Chain of 6 neighbourhood supermarkets across the Kathmandu Valley.', NULL, NULL, 'Owner consolidating to focus on wholesale distribution', NULL, NULL, NULL, NULL, 1, 0, 'approved', 1, 760, 8.4, '2026-05-29 22:15:00', '2026-05-29 22:15:00'),
(7, 5, 'Himalayan Handicrafts Export', 'himalayan-handicrafts-export-7', 'partial_stake', 13, 'Bagmati', 'Bhaktapur', 1, 3, 23, 2017, 40, NULL, 42000000.00, NULL, 20.00, 30000000.00, NULL, NULL, 30.00, NULL, NULL, 'Export-focused handicraft business shipping to Europe and North America.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 'approved', 0, 410, 7.9, '2026-05-29 22:15:00', '2026-05-29 22:15:00'),
(11, 1, 'Mountain Vista Resort & Spa', 'mountain-vista-resort-spa-11', 'sale', 9, 'Gandaki', 'Pokhara', 1, 4, NULL, 2012, 48, NULL, 65000000.00, NULL, 22.00, 120000000.00, NULL, NULL, NULL, NULL, NULL, 'A 30-room boutique resort with panoramic Himalayan views, full-service spa, and multi-cuisine restaurant. Located on the scenic Pokhara lakeside. 85% average occupancy rate with 4.7-star guest rating.', NULL, NULL, 'Owner retiring after 12 successful years in hospitality', NULL, NULL, NULL, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=400&h=300&fit=crop', 1, 0, 'approved', 1, 275, 9.1, '2026-06-08 11:03:52', '2026-06-08 11:03:52'),
(12, 1, 'Downtown Kathmandu Business Hotel', 'downtown-kathmandu-business-hotel-12', 'sale', 9, 'Bagmati', 'Kathmandu', 1, 3, 27, 2015, 35, NULL, 42000000.00, NULL, 18.50, 75000000.00, NULL, NULL, NULL, NULL, NULL, 'Well-established 20-room business hotel in central Kathmandu with conference facilities, restaurant, and bar. Strong corporate client base and consistent year-round revenue.', NULL, NULL, 'Seeking to divest for larger development project', NULL, NULL, NULL, 'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=400&h=300&fit=crop', 1, 0, 'approved', 0, 899, 8.3, '2026-06-08 11:03:52', '2026-06-08 11:03:52'),
(13, 1, 'Lakeside Restaurant & Bar', 'lakeside-restaurant-bar-13', 'sale', 9, 'Gandaki', 'Pokhara', 1, 4, NULL, 2018, 18, NULL, 18000000.00, NULL, 25.00, 28000000.00, NULL, NULL, NULL, NULL, NULL, 'Popular multi-cuisine restaurant and bar on Pokhara lakefront with 80-seat capacity, outdoor terrace, and live music license. Strong tourist and expat clientele.', NULL, NULL, 'Owner relocating abroad', NULL, NULL, NULL, 'https://images.unsplash.com/photo-1552566626-52f8b828add9?w=400&h=300&fit=crop', 1, 0, 'approved', 0, 359, 8.7, '2026-06-08 11:03:52', '2026-06-08 11:03:52'),
(14, 1, 'Everest Wellness Pharmacy Chain', 'everest-wellness-pharmacy-chain-14', 'sale', 8, 'Bagmati', 'Kathmandu', 1, 3, 27, 2008, 42, NULL, 72000000.00, NULL, 14.00, 85000000.00, NULL, NULL, NULL, NULL, NULL, 'Chain of 7 pharmacies across Kathmandu Valley with wholesale distribution license. Established supplier relationships with major pharmaceutical companies. Loyal customer base of 15,000+.', NULL, NULL, 'Founder expanding into hospital management', NULL, NULL, NULL, 'https://images.unsplash.com/photo-1585435557343-3b092031a831?w=400&h=300&fit=crop', 1, 0, 'approved', 0, 228, 8.0, '2026-06-08 11:03:52', '2026-06-08 11:03:52'),
(15, 1, 'Himalayan General Store & Provisions', 'himalayan-general-store-provisions-15', 'sale', 8, 'Bagmati', 'Lalitpur', 1, 3, 29, 2010, 22, NULL, 35000000.00, NULL, 12.00, 40000000.00, NULL, NULL, NULL, NULL, NULL, 'Full-service general store in a high-traffic residential area of Lalitpur. Stocking groceries, household items, and local specialties. Stable annual growth of 8-10%.', NULL, NULL, 'Owner pursuing franchise opportunity', NULL, NULL, NULL, 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=400&h=300&fit=crop', 1, 0, 'approved', 0, 808, 7.8, '2026-06-08 11:03:52', '2026-06-08 11:03:52'),
(16, 1, 'Organic & Natural Products Boutique', 'organic-natural-products-boutique-16', 'partial_stake', 8, 'Bagmati', 'Kathmandu', 1, 3, 27, 2019, 12, NULL, 15000000.00, NULL, 20.00, 12000000.00, NULL, NULL, NULL, NULL, NULL, 'Specialty retail store offering organic foods, natural cosmetics, and eco-friendly home products. Growing health-conscious customer segment. Seeking partner for 40% equity to fund expansion.', NULL, NULL, 'Expanding to second location, need growth capital', NULL, NULL, NULL, 'https://images.unsplash.com/photo-1558618666-fcd25c85f82e?w=400&h=300&fit=crop', 1, 0, 'approved', 0, 749, 8.5, '2026-06-08 11:03:52', '2026-06-08 11:03:52'),
(17, 1, 'LearnNepal Online Academy', 'learnnepal-online-academy-17', 'partial_stake', 5, 'Bagmati', 'Kathmandu', 1, 3, 27, 2020, 28, NULL, 22000000.00, NULL, 28.00, 35000000.00, NULL, NULL, NULL, NULL, NULL, 'Online learning platform offering STEM courses for grades 8-12. 8,500+ active students across 35 districts. Offline-capable mobile app. Partnerships with 50+ schools.', NULL, NULL, 'Scaling to include vocational training vertical', NULL, NULL, NULL, 'https://images.unsplash.com/photo-1509062522246-3755977927d7?w=400&h=300&fit=crop', 1, 0, 'approved', 1, 312, 9.0, '2026-06-08 11:03:52', '2026-06-08 11:03:52'),
(18, 1, 'SkillBridge Vocational Training Center', 'skillbridge-vocational-training-center-18', 'sale', 5, 'Province 1', 'Biratnagar', NULL, NULL, NULL, 2016, 25, NULL, 28000000.00, NULL, 16.00, 45000000.00, NULL, NULL, NULL, NULL, NULL, 'CTEVT-affiliated vocational training center offering IT, hospitality, and healthcare assistant courses. 500+ graduates annually with 85% placement rate. Government-recognized certification.', NULL, NULL, 'Owner retiring, looking for successor', NULL, NULL, NULL, 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=400&h=300&fit=crop', 1, 0, 'approved', 0, 717, 8.1, '2026-06-08 11:03:52', '2026-06-08 11:03:52'),
(19, 1, 'GreenFarm Hydroponics', 'greenfarm-hydroponics-19', 'partial_stake', 1, 'Bagmati', 'Kathmandu', 1, 3, 27, 2021, 15, NULL, 12000000.00, NULL, 30.00, 18000000.00, NULL, NULL, NULL, NULL, NULL, 'Commercial hydroponic farm supplying premium lettuce, herbs, and microgreens to 40+ hotels and restaurants in Kathmandu. Year-round production with 3 greenhouse facilities.', NULL, NULL, 'Seeking capital to build 5 more greenhouse units', NULL, NULL, NULL, 'https://images.unsplash.com/photo-1585515321484-4e8e8e6b8f7b?w=400&h=300&fit=crop', 1, 0, 'approved', 0, 845, 8.8, '2026-06-08 11:03:52', '2026-06-08 11:03:52'),
(20, 1, 'Nepal Organic Tea Plantation', 'nepal-organic-tea-plantation-20', 'sale', 1, 'Province 1', 'Ilam', NULL, NULL, NULL, 2005, 60, NULL, 38000000.00, NULL, 20.00, 95000000.00, NULL, NULL, NULL, NULL, NULL, 'Established 25-acre organic tea estate in Ilam producing premium orthodox teas. Exports to 8 countries. Certified organic and Fair Trade. On-site processing facility.', NULL, NULL, 'Founder looking for strategic acquisition partner', NULL, NULL, NULL, 'https://images.unsplash.com/photo-1563822249366-3efb23b8e0c9?w=400&h=300&fit=crop', 1, 0, 'approved', 0, 242, 9.2, '2026-06-08 11:03:52', '2026-06-08 11:03:52'),
(21, 1, 'Annapurna Steel Fabrication', 'annapurna-steel-fabrication-21', 'sale', 7, 'Bagmati', 'Hetauda', 1, 3, NULL, 2014, 85, NULL, 95000000.00, NULL, 15.00, 110000000.00, NULL, NULL, NULL, NULL, NULL, 'Steel fabrication and structural manufacturing unit with 30,000 sq ft factory. Supplies construction companies across central Nepal. ISO 9001 certified. Modern machinery.', NULL, NULL, 'Owner pursuing import business opportunities', NULL, NULL, NULL, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=400&h=300&fit=crop', 1, 0, 'approved', 0, 904, 8.2, '2026-06-08 11:03:52', '2026-06-08 11:03:52'),
(22, 1, 'Himalayan Pashmina Weaving Mill', 'himalayan-pashmina-weaving-mill-22', 'partial_stake', 7, 'Bagmati', 'Kathmandu', 1, 3, 27, 2009, 55, NULL, 52000000.00, NULL, 22.00, 65000000.00, NULL, NULL, NULL, NULL, NULL, 'Traditional pashmina shawl and scarf manufacturer with 40 handlooms. Exports to luxury retailers in Europe, Japan, and North America. Ethical production certified.', NULL, NULL, 'Seeking investment for digital marketing and US market entry', NULL, NULL, NULL, 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=400&h=300&fit=crop', 1, 0, 'approved', 0, 380, 8.6, '2026-06-08 11:03:52', '2026-06-08 11:03:52'),
(23, 1, 'Everest Bottled Water Co.', 'everest-bottled-water-co-23', 'sale', 7, 'Bagmati', 'Dhulikhel', 1, 3, NULL, 2011, 38, NULL, 45000000.00, NULL, 18.00, 55000000.00, NULL, NULL, NULL, NULL, NULL, 'Premium spring water bottling plant with source at 1,600m elevation. 5-stage purification system. Supplies 20,000+ bottles daily to hotels, offices, and retail outlets.', NULL, NULL, 'Competitive market prompting owner to exit', NULL, NULL, NULL, 'https://images.unsplash.com/photo-1616118132534-3815a88f96b6?w=400&h=300&fit=crop', 1, 0, 'approved', 0, 585, 7.9, '2026-06-08 11:03:52', '2026-06-08 11:03:52'),
(24, 1, 'Newa Momo Kitchen Franchise', 'newa-momo-kitchen-franchise-24', 'loan', 12, 'Bagmati', 'Kathmandu', 1, 3, 27, 2017, 20, NULL, 25000000.00, NULL, 24.00, 15000000.00, NULL, NULL, NULL, NULL, NULL, 'Popular momo chain with 3 outlets in Kathmandu. Famous for authentic Newari-style momos. Strong brand recognition. Looking for loan to open 2 more outlets in Lalitpur.', NULL, NULL, 'Expansion capital for new outlets', NULL, NULL, NULL, 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=400&h=300&fit=crop', 1, 0, 'approved', 0, 789, 8.4, '2026-06-08 11:03:52', '2026-06-08 11:03:52'),
(25, 1, 'Himalayan Coffee Roasters', 'himalayan-coffee-roasters-25', 'partial_stake', 12, 'Bagmati', 'Lalitpur', 1, 3, 29, 2018, 14, NULL, 18000000.00, NULL, 26.00, 25000000.00, NULL, NULL, NULL, NULL, NULL, 'Specialty coffee roastery sourcing beans from 200+ small farmers in Palpa and Gulmi. Supplies 60+ cafes across Nepal. Single-origin and blend offerings. 35% YoY growth.', NULL, NULL, 'Scaling production capacity and retail presence', NULL, NULL, NULL, 'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?w=400&h=300&fit=crop', 1, 0, 'approved', 1, 395, 9.3, '2026-06-08 11:03:52', '2026-06-08 11:03:52'),
(26, 1, 'Bakery & Patisserie Shop', 'bakery-patisserie-shop-26', 'sale', 12, 'Bagmati', 'Bhaktapur', 1, 3, 23, 2019, 10, NULL, 9500000.00, NULL, 32.00, 15000000.00, NULL, NULL, NULL, NULL, NULL, 'Artisan bakery in heritage area of Bhaktapur producing sourdough, pastries, and celebration cakes. Strong repeat customer base. Equipment and recipes included.', NULL, NULL, 'Owner focusing on food consultancy', NULL, NULL, NULL, 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=400&h=300&fit=crop', 1, 0, 'approved', 0, 975, 8.9, '2026-06-08 11:03:52', '2026-06-08 11:03:52'),
(27, 1, 'NepalCraft Online Marketplace', 'nepalcraft-online-marketplace-27', 'partial_stake', 13, 'Bagmati', 'Kathmandu', 1, 3, 27, 2020, 18, NULL, 28000000.00, NULL, 16.00, 40000000.00, NULL, NULL, NULL, NULL, NULL, 'E-commerce platform connecting Nepali artisans with global buyers. 1,200+ registered sellers, 15,000+ products. Monthly orders: 2,500+. Shipping to 25+ countries.', NULL, NULL, 'Need capital for logistics infrastructure and marketing', NULL, NULL, NULL, 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=400&h=300&fit=crop', 1, 0, 'approved', 0, 311, 8.0, '2026-06-08 11:03:52', '2026-06-08 11:03:52'),
(28, 1, 'FreshGrocery Nepal', 'freshgrocery-nepal-28', 'sale', 13, 'Bagmati', 'Kathmandu', 1, 3, 27, 2021, 25, NULL, 32000000.00, NULL, 10.00, 22000000.00, NULL, NULL, NULL, NULL, NULL, 'Online grocery delivery service covering Kathmandu Valley. 4,000+ SKUs, own warehouse and delivery fleet. 300+ daily orders. 2-hour delivery window.', NULL, NULL, 'Competitive pressure prompting sale to larger player', NULL, NULL, NULL, 'https://images.unsplash.com/photo-1594398901394-4e34939a4e17?w=400&h=300&fit=crop', 1, 0, 'approved', 0, 833, 7.5, '2026-06-08 11:03:52', '2026-06-08 11:03:52'),
(29, 1, 'Shiva Construction & Engineering', 'shiva-construction-engineering-29', 'sale', 14, 'Bagmati', 'Kathmandu', 1, 3, 27, 2007, 120, NULL, 150000000.00, NULL, 12.00, 180000000.00, NULL, NULL, NULL, NULL, NULL, 'Class A construction company with 17 years of project delivery. Completed 35+ commercial and residential projects. Owns heavy equipment fleet. Government-approved contractor.', NULL, NULL, 'Founders approaching retirement age', NULL, NULL, NULL, 'https://images.unsplash.com/photo-1504917595217-d4dc5ebe6122?w=400&h=300&fit=crop', 1, 0, 'approved', 0, 633, 8.2, '2026-06-08 11:03:52', '2026-06-08 11:03:52'),
(30, 1, 'GreenBuild Materials Supply', 'greenbuild-materials-supply-30', 'sale', 14, 'Bagmati', 'Lalitpur', 1, 3, 29, 2016, 28, NULL, 42000000.00, NULL, 14.00, 35000000.00, NULL, NULL, NULL, NULL, NULL, 'Eco-friendly construction materials supplier offering bamboo panels, recycled aggregates, and energy-efficient blocks. Exclusive distributor for 5 international brands in Nepal.', NULL, NULL, 'Owner shifting focus to architectural consultancy', NULL, NULL, NULL, 'https://images.unsplash.com/photo-1579003106855-42f65416a5e5?w=400&h=300&fit=crop', 1, 0, 'approved', 0, 463, 8.0, '2026-06-08 11:03:52', '2026-06-08 11:03:52'),
(31, 20, 'esagwglkwnegoin', 'esagwglkwnegoin', 'partial_stake', 14, NULL, NULL, 1, 6, 66, 2003, 10, 'Sole Proprietorship', 150000.00, 15000.00, 15.00, 1500.00, 2000000.00, 100000.00, 1.00, NULL, NULL, 'it uryhrn;odisrgnoeirgiuwtbiguwubibi', 'jniweeubnwgioubweou', 'onoierngoiw4ue', 'oginwoginoq', 'uhneiughwiu', 'goeirgorig', 'oignowigwnoi', 'https://unsplash.com/photos/woman-planting-a-small-houseplant-in-a-pot-MJLy1fUvX_w', 1, 0, 'approved', 0, 11, NULL, '2026-06-11 11:33:17', '2026-06-11 11:33:17'),
(32, 20, 'hello  enterprises', 'hello-enterprises', 'investment', 10, NULL, NULL, 1, 6, 66, 2003, 101454, 'Sole Proprietorship', 1000000.00, 14.00, 10.00, 100000.00, 100000.00, 100000.00, 10.00, NULL, NULL, 'wfgwqfgwfgg', 'wggwgw', 'gwwegwge', 'wgwgewge', 'aeesage', 'wegweegw', 'wweew', 'https://unsplash.com/photos/woman-planting-a-small-houseplant-in-a-pot-MJLy1fUvX_w', 1, 0, 'approved', 0, 16, NULL, '2026-06-12 11:57:36', '2026-06-12 11:57:36'),
(33, 19, 'R K Block Udhyog', 'r-k-block-udhyog', 'investment', 7, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Sole Proprietorship', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', '', '', '', 1, 0, 'approved', 0, 22, NULL, '2026-06-12 12:00:45', '2026-06-12 12:00:45');

-- --------------------------------------------------------

--
-- Table structure for table `business_assets`
--

CREATE TABLE `business_assets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `asset_name` varchar(255) NOT NULL,
  `asset_type` enum('land','building','equipment','inventory','vehicle','intellectual_property','other') DEFAULT NULL,
  `estimated_value` decimal(15,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `business_assets`
--

INSERT INTO `business_assets` (`id`, `business_id`, `asset_name`, `asset_type`, `estimated_value`, `description`, `created_at`, `updated_at`) VALUES
(1, 31, 'wegwgwgt', 'building', 100.00, '141000', '2026-06-11 11:33:17', '2026-06-11 11:33:17'),
(2, 32, 'wegwgwgt', 'land', 10000.00, '0tyiojjy', '2026-06-12 11:57:36', '2026-06-12 11:57:36');

-- --------------------------------------------------------

--
-- Table structure for table `business_financials`
--

CREATE TABLE `business_financials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `fiscal_year` int(11) NOT NULL,
  `revenue` decimal(15,2) DEFAULT NULL,
  `expenses` decimal(15,2) DEFAULT NULL,
  `profit` decimal(15,2) DEFAULT NULL,
  `ebitda` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `business_financials`
--

INSERT INTO `business_financials` (`id`, `business_id`, `fiscal_year`, `revenue`, `expenses`, `profit`, `ebitda`, `created_at`, `updated_at`) VALUES
(1, 31, 2025, 4274277.00, 10000.00, 54472522.00, 10.00, '2026-06-11 11:33:17', '2026-06-11 11:33:17'),
(2, 32, 2026, 84945656.00, 84985656.00, 651516516516.00, 516516516.00, '2026-06-12 11:57:36', '2026-06-12 11:57:36');

-- --------------------------------------------------------

--
-- Table structure for table `business_inquiries`
--

CREATE TABLE `business_inquiries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('new','contacted','qualified','rejected') NOT NULL DEFAULT 'new',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `business_inquiries`
--

INSERT INTO `business_inquiries` (`id`, `business_id`, `user_id`, `message`, `status`, `created_at`, `updated_at`) VALUES
(2, 11, 19, 'dfz', '', '2026-06-10 02:14:32', '2026-06-12 16:28:41'),
(3, 33, 22, 'hi i want to buy the projucts', 'new', '2026-06-13 12:39:37', '2026-06-13 12:39:37');

-- --------------------------------------------------------

--
-- Table structure for table `business_media`
--

CREATE TABLE `business_media` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `file_url` text DEFAULT NULL,
  `media_type` enum('image','video','document') NOT NULL DEFAULT 'image',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `business_media`
--

INSERT INTO `business_media` (`id`, `business_id`, `file_url`, `media_type`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 31, '/public/uploads/business-photos/20_1781177597_a1368960.jpg', 'image', 0, '2026-06-11 11:33:17', NULL),
(2, 32, '/public/uploads/business-photos/20_1781265456_798184d5.jpg', 'image', 0, '2026-06-12 11:57:36', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `business_photos`
--

CREATE TABLE `business_photos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `business_verifications`
--

CREATE TABLE `business_verifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `phone_verified` tinyint(1) NOT NULL DEFAULT 0,
  `identity_verified` tinyint(1) NOT NULL DEFAULT 0,
  `company_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `business_verifications`
--

INSERT INTO `business_verifications` (`id`, `business_id`, `email_verified`, `phone_verified`, `identity_verified`, `company_verified`, `verified_at`, `created_at`, `updated_at`) VALUES
(1, 33, 1, 1, 0, 0, NULL, '2026-06-12 16:30:17', '2026-06-12 16:30:21');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

CREATE TABLE `cities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `state_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cities`
--

INSERT INTO `cities` (`id`, `state_id`, `name`, `is_active`) VALUES
(1, 1, 'Bhojpur', 1),
(2, 1, 'Dhankuta', 1),
(3, 1, 'Ilam', 1),
(4, 1, 'Jhapa', 1),
(5, 1, 'Khotang', 1),
(6, 1, 'Morang', 1),
(7, 1, 'Okhaldhunga', 1),
(8, 1, 'Panchthar', 1),
(9, 1, 'Sankhuwasabha', 1),
(10, 1, 'Solukhumbu', 1),
(11, 1, 'Sunsari', 1),
(12, 1, 'Taplejung', 1),
(13, 1, 'Terhathum', 1),
(14, 1, 'Udayapur', 1),
(15, 2, 'Bara', 1),
(16, 2, 'Dhanusha', 1),
(17, 2, 'Mahottari', 1),
(18, 2, 'Parsa', 1),
(19, 2, 'Rautahat', 1),
(20, 2, 'Saptari', 1),
(21, 2, 'Sarlahi', 1),
(22, 2, 'Siraha', 1),
(23, 3, 'Bhaktapur', 1),
(24, 3, 'Chitwan', 1),
(25, 3, 'Dhading', 1),
(26, 3, 'Dolakha', 1),
(27, 3, 'Kathmandu', 1),
(28, 3, 'Kavrepalanchok', 1),
(29, 3, 'Lalitpur', 1),
(30, 3, 'Makwanpur', 1),
(31, 3, 'Nuwakot', 1),
(32, 3, 'Ramechhap', 1),
(33, 3, 'Rasuwa', 1),
(34, 3, 'Sindhuli', 1),
(35, 3, 'Sindhupalchok', 1),
(36, 4, 'Baglung', 1),
(37, 4, 'Gorkha', 1),
(38, 4, 'Kaski', 1),
(39, 4, 'Lamjung', 1),
(40, 4, 'Manang', 1),
(41, 4, 'Mustang', 1),
(42, 4, 'Myagdi', 1),
(43, 4, 'Nawalpur', 1),
(44, 4, 'Parbat', 1),
(45, 4, 'Syangja', 1),
(46, 4, 'Tanahun', 1),
(47, 5, 'Arghakhanchi', 1),
(48, 5, 'Banke', 1),
(49, 5, 'Bardiya', 1),
(50, 5, 'Dang', 1),
(51, 5, 'Gulmi', 1),
(52, 5, 'Kapilvastu', 1),
(53, 5, 'Palpa', 1),
(54, 5, 'Pyuthan', 1),
(55, 5, 'Rolpa', 1),
(56, 5, 'Rukum East', 1),
(57, 5, 'Rupandehi', 1),
(58, 6, 'Dailekh', 1),
(59, 6, 'Dolpa', 1),
(60, 6, 'Humla', 1),
(61, 6, 'Jajarkot', 1),
(62, 6, 'Jumla', 1),
(63, 6, 'Kalikot', 1),
(64, 6, 'Mugu', 1),
(65, 6, 'Salyan', 1),
(66, 6, 'Surkhet', 1),
(67, 6, 'Western Rukum', 1),
(68, 7, 'Achham', 1),
(69, 7, 'Baitadi', 1),
(70, 7, 'Bajhang', 1),
(71, 7, 'Bajura', 1),
(72, 7, 'Dadeldhura', 1),
(73, 7, 'Darchula', 1),
(74, 7, 'Doti', 1),
(75, 7, 'Kailali', 1),
(76, 7, 'Kanchanpur', 1);

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `iso_code` varchar(2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `name`, `iso_code`, `is_active`) VALUES
(1, 'Nepal', 'NP', 1);

-- --------------------------------------------------------

--
-- Table structure for table `email_log`
--

CREATE TABLE `email_log` (
  `id` int(10) UNSIGNED NOT NULL,
  `recipient` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `template_key` varchar(100) DEFAULT NULL,
  `status` enum('sent','failed') NOT NULL DEFAULT 'sent',
  `error` text DEFAULT NULL,
  `sent_by` int(10) UNSIGNED DEFAULT NULL COMMENT 'admin user ID if triggered manually',
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_log`
--

INSERT INTO `email_log` (`id`, `recipient`, `subject`, `template_key`, `status`, `error`, `sent_by`, `sent_at`) VALUES
(1, 'isoftrosolutions@gmail.com', 'Reset your password ΓÇö Asaan Capital', 'password_reset', 'sent', NULL, NULL, '2026-06-11 10:50:50'),
(2, 'isoftrosolutions@gmail.com', 'Reset your password - Asaan Capital', 'password_reset', 'sent', NULL, NULL, '2026-06-11 10:52:28'),
(3, 'isoftrosolutions@gmail.com', 'Reset your password - Asaan Capital', 'password_reset', 'sent', NULL, NULL, '2026-06-11 10:55:50'),
(4, 'pdewbrath@gmail.com', 'Welcome to Asaan Capital ΓÇö Devbarat Prasad Patel', 'welcome', 'sent', NULL, NULL, '2026-06-11 10:56:48'),
(5, 'pdewbrath@gmail.com', 'Reset your password - Asaan Capital', 'password_reset', 'sent', NULL, NULL, '2026-06-11 10:57:31'),
(6, 'pdewbrath@gmail.com', 'Your account has been verified ΓÇö Asaan Capital', 'verification_approved', 'sent', NULL, NULL, '2026-06-11 11:40:35'),
(7, 'isoftrosolutions@gmail.com', 'Your account has been verified ΓÇö Asaan Capital', 'verification_approved', 'sent', NULL, NULL, '2026-06-11 11:40:39'),
(8, 'rkblockudhyog@gmail.com', 'Your account has been verified ΓÇö Asaan Capital', 'verification_approved', 'sent', NULL, NULL, '2026-06-11 11:40:41'),
(9, 'asaancredit@gmail.com', 'Welcome to Asaan Capital ΓÇö Asaan Credit Ltd', 'welcome', 'sent', NULL, NULL, '2026-06-12 11:06:32'),
(10, 'asaancredit@gmail.com', 'Your account has been verified ΓÇö Asaan Capital', 'verification_approved', 'sent', NULL, NULL, '2026-06-12 12:01:32'),
(11, 'asaancredit@gmail.com', 'Test Email from Asaan Capital', NULL, 'sent', NULL, NULL, '2026-06-12 16:32:57');

-- --------------------------------------------------------

--
-- Table structure for table `email_settings`
--

CREATE TABLE `email_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `smtp_host` varchar(255) NOT NULL DEFAULT 'smtp.gmail.com',
  `smtp_port` int(10) UNSIGNED NOT NULL DEFAULT 587,
  `smtp_encryption` varchar(10) NOT NULL DEFAULT 'tls',
  `smtp_username` varchar(255) NOT NULL DEFAULT '',
  `smtp_password` varchar(255) NOT NULL DEFAULT '',
  `from_email` varchar(255) NOT NULL DEFAULT 'noreply@asaancapital.com',
  `from_name` varchar(255) NOT NULL DEFAULT 'Asaan Capital Ltd',
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_settings`
--

INSERT INTO `email_settings` (`id`, `smtp_host`, `smtp_port`, `smtp_encryption`, `smtp_username`, `smtp_password`, `from_email`, `from_name`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'mail.asaancapital.com', 587, 'tls', 'info@asaancapital.com', 'W{~F0+GR4g7&b[u~', 'info@asaancapital.com', 'Asaan Capital Ltd', 1, '2026-06-02 03:02:30', '2026-06-11 10:45:04');

-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

CREATE TABLE `email_templates` (
  `id` int(10) UNSIGNED NOT NULL,
  `template_key` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` longtext NOT NULL,
  `variables` longtext DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_templates`
--

INSERT INTO `email_templates` (`id`, `template_key`, `name`, `subject`, `body`, `variables`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'email_verification', 'Email Verification', 'Verify your email ΓÇö Asaan Capital Ltd.', '<div style=\"font-family:\'Inter\',\'Helvetica Neue\',sans-serif;max-width:600px;margin:20px auto;padding:40px;border:1px solid #eef2f6;border-radius:24px;color:#2a2a2a;background:#ffffff;box-shadow:0 10px 30px rgba(0,0,0,0.02);\">\r\n            <div style=\"text-align:center;margin-bottom:32px;\">\r\n                <span style=\"font-size:28px;font-weight:900;color:#1E4866;letter-spacing:-0.5px;\">Asaan<span style=\"color:#98202A;\">Capital</span></span>\r\n            </div>\r\n            <div style=\"text-align:center;margin-bottom:32px;\">\r\n                <h2 style=\"color:#1E4866;font-size:26px;font-weight:800;margin:0;letter-spacing:-0.5px;\">Verify Your Email</h2>\r\n                <p style=\"color:#5A5A5A;margin-top:8px;font-size:15px;\">One last step to activate your account.</p>\r\n            </div>\r\n            <p style=\"font-size:16px;margin-bottom:24px;line-height:1.6;\">Hello <strong style=\"color:#1E4866;\">{{user_name}}</strong>,</p>\r\n            <p style=\"font-size:15px;margin-bottom:28px;line-height:1.6;color:#5A5A5A;\">Welcome to Asaan Capital ΓÇö Nepal\'s premier marketplace for business investment and matchmaking. To start exploring opportunities and connecting with verified investors, please confirm your email address.</p>\r\n            <div style=\"text-align:center;margin:32px 0;\">\r\n                <a href=\"{{verification_link}}\" style=\"display:inline-block;padding:16px 36px;background:#1E4866;color:#ffffff;text-decoration:none;border-radius:12px;font-weight:700;font-size:15px;box-shadow:0 4px 15px rgba(30,72,102,0.2);\">Verify My Email</a>\r\n            </div>\r\n            <p style=\"font-size:14px;color:#C3C6C5;margin-bottom:32px;text-align:center;\">This link expires in 24 hours. If you did not create this account, please ignore this email.</p>\r\n            <div style=\"border-top:1px solid #ECECEC;padding-top:24px;text-align:center;\">\r\n                <p style=\"margin:0;font-size:13px;color:#5A5A5A;\">Asaan Capital Ltd ΓÇö Kathmandu, Nepal</p>\r\n            </div>\r\n        </div>', '[\"user_name\",\"verification_link\"]', 1, '2026-06-02 03:02:30', '2026-06-09 17:06:53'),
(3, 'password_changed', 'Password Changed Confirmation', 'Password changed successfully ΓÇö Asaan Capital', '<div style=\"font-family:\'Inter\',\'Helvetica Neue\',sans-serif;max-width:600px;margin:20px auto;padding:40px;border:1px solid #eef2f6;border-radius:24px;color:#2a2a2a;background:#ffffff;box-shadow:0 10px 30px rgba(0,0,0,0.02);\">\n            <div style=\"text-align:center;margin-bottom:32px;\">\n                <span style=\"font-size:28px;font-weight:900;color:#1E4866;letter-spacing:-0.5px;\">Asaan<span style=\"color:#98202A;\">Capital</span></span>\n            </div>\n            <div style=\"text-align:center;margin-bottom:32px;\">\n                <h2 style=\"color:#1E7A4D;font-size:26px;font-weight:800;margin:0;letter-spacing:-0.5px;\">Password Changed</h2>\n                <p style=\"color:#5A5A5A;margin-top:8px;font-size:15px;\">Your account security has been updated.</p>\n            </div>\n            <p style=\"font-size:16px;margin-bottom:24px;line-height:1.6;\">Hello <strong style=\"color:#1E4866;\">{{user_name}}</strong>,</p>\n            <p style=\"font-size:15px;margin-bottom:28px;line-height:1.6;color:#5A5A5A;\">Your password was changed successfully. If you made this change, no further action is required.</p>\n            <div style=\"background:#fff9f0;padding:20px;border-radius:12px;margin-bottom:24px;border:1px solid #fde68a;\">\n                <p style=\"margin:0;font-size:14px;color:#92400e;line-height:1.5;\"><svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"#d97706\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" style=\"display:inline-block;vertical-align:middle;margin-right:6px;\"><path d=\"M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z\"/><line x1=\"12\" y1=\"9\" x2=\"12\" y2=\"13\"/><line x1=\"12\" y1=\"17\" x2=\"12.01\" y2=\"17\"/></svg> <strong>Did not change your password?</strong> Please contact our support team immediately at <a href=\"mailto:support@asaancapital.com\" style=\"color:#98202A;font-weight:600;\">support@asaancapital.com</a>.</p>\n            </div>\n            <div style=\"border-top:1px solid #ECECEC;padding-top:24px;text-align:center;\">\n                <p style=\"margin:0;font-size:13px;color:#5A5A5A;\">Asaan Capital Ltd ΓÇö Kathmandu, Nepal</p>\n            </div>\n        </div>', '[\"user_name\"]', 1, '2026-06-02 03:02:30', NULL),
(4, 'welcome', 'Welcome New User', 'Welcome to Asaan Capital ΓÇö {{user_name}}', '<div style=\"font-family:\'Inter\',\'Helvetica Neue\',sans-serif;max-width:600px;margin:20px auto;padding:40px;border:1px solid #eef2f6;border-radius:24px;color:#2a2a2a;background:#ffffff;box-shadow:0 10px 30px rgba(0,0,0,0.02);\">\n            <div style=\"text-align:center;margin-bottom:32px;\">\n                <span style=\"font-size:28px;font-weight:900;color:#1E4866;letter-spacing:-0.5px;\">Asaan<span style=\"color:#98202A;\">Capital</span></span>\n            </div>\n            <div style=\"text-align:center;margin-bottom:32px;\">\n                <h2 style=\"color:#1E4866;font-size:26px;font-weight:800;margin:0;letter-spacing:-0.5px;\">Welcome Aboard!</h2>\n                <p style=\"color:#5A5A5A;margin-top:8px;font-size:15px;\">Your account is ready to explore.</p>\n            </div>\n            <p style=\"font-size:16px;margin-bottom:24px;line-height:1.6;\">Hello <strong style=\"color:#1E4866;\">{{user_name}}</strong>,</p>\n            <p style=\"font-size:15px;margin-bottom:28px;line-height:1.6;color:#5A5A5A;\">Thank you for joining Asaan Capital. You are now part of Nepal\'s most trusted marketplace where investors, entrepreneurs, and business owners connect to grow together.</p>\n            <div style=\"background:#F8F8F8;padding:24px;border-radius:16px;margin-bottom:32px;border:1px solid #ECECEC;\">\n                <h3 style=\"font-size:13px;text-transform:uppercase;letter-spacing:1px;color:#C3C6C5;margin-top:0;margin-bottom:16px;\">Your account details</h3>\n                <p style=\"margin:8px 0;font-size:14px;\"><strong>Role :</strong> {{role}}</p>\n                <p style=\"margin:8px 0;font-size:14px;\"><strong>Email :</strong> {{user_email}}</p>\n            </div>\n            <div style=\"background:linear-gradient(135deg, #1E4866 0%, #205880 100%);padding:28px;border-radius:16px;margin-bottom:32px;color:#ffffff;box-shadow:0 8px 20px rgba(30,72,102,0.2);\">\n                <h3 style=\"font-size:14px;text-transform:uppercase;letter-spacing:1px;margin-top:0;margin-bottom:16px;opacity:0.9;\">What you can do next</h3>\n                <div style=\"font-size:14px;line-height:1.8;\">\n                    <div><svg width=\"16\" height=\"16\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" style=\"display:inline-block;vertical-align:middle;margin-right:8px;\"><circle cx=\"11\" cy=\"11\" r=\"8\"/><path d=\"m21 21-4.35-4.35\"/></svg> <strong>Discover</strong> ΓÇö Browse vetted businesses and pitches</div>\n                    <div><svg width=\"16\" height=\"16\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" style=\"display:inline-block;vertical-align:middle;margin-right:8px;\"><path d=\"M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2\"/><circle cx=\"9\" cy=\"7\" r=\"4\"/><path d=\"M23 21v-2a4 4 0 0 0-3-3.87\"/><path d=\"M16 3.13a4 4 0 0 1 0 7.75\"/></svg> <strong>Connect</strong> ΓÇö Express interest and match with partners</div>\n                    <div><svg width=\"16\" height=\"16\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" style=\"display:inline-block;vertical-align:middle;margin-right:8px;\"><polyline points=\"22 12 18 12 15 21 9 3 6 12 2 12\"/></svg> <strong>Grow</strong> ΓÇö Find the right capital or acquisition opportunity</div>\n                </div>\n            </div>\n            <div style=\"text-align:center;margin:32px 0;\">\n                <a href=\"{{login_url}}\" style=\"display:inline-block;padding:16px 36px;background:#98202A;color:#ffffff;text-decoration:none;border-radius:12px;font-weight:800;font-size:15px;box-shadow:0 4px 15px rgba(152,32,42,0.3);\">Explore the Marketplace</a>\n            </div>\n            <div style=\"border-top:1px solid #ECECEC;padding-top:24px;text-align:center;\">\n                <p style=\"margin:0;font-size:13px;color:#5A5A5A;\">Asaan Capital Ltd ΓÇö Kathmandu, Nepal</p>\n            </div>\n        </div>', '[\"user_name\",\"login_url\",\"role\",\"user_email\"]', 1, '2026-06-02 03:02:30', NULL),
(5, 'interest_received', 'Interest Request Received', 'New interest received for your {{listing_type}} ΓÇö Asaan Capital Ltd.', '<div style=\"font-family:\'Inter\',\'Helvetica Neue\',sans-serif;max-width:600px;margin:20px auto;padding:40px;border:1px solid #eef2f6;border-radius:24px;color:#2a2a2a;background:#ffffff;box-shadow:0 10px 30px rgba(0,0,0,0.02);\">\r\n            <div style=\"text-align:center;margin-bottom:32px;\">\r\n                <span style=\"font-size:28px;font-weight:900;color:#1E4866;letter-spacing:-0.5px;\">Asaan<span style=\"color:#98202A;\">Capital</span></span>\r\n            </div>\r\n            <div style=\"text-align:center;margin-bottom:32px;\">\r\n                <h2 style=\"color:#1E7A4D;font-size:26px;font-weight:800;margin:0;letter-spacing:-0.5px;\">New Interest Received</h2>\r\n                <p style=\"color:#5A5A5A;margin-top:8px;font-size:15px;\">Someone is interested in your {{listing_type}}.</p>\r\n            </div>\r\n            <p style=\"font-size:16px;margin-bottom:24px;line-height:1.6;\">Hello <strong style=\"color:#1E4866;\">{{user_name}}</strong>,</p>\r\n            <p style=\"font-size:15px;margin-bottom:28px;line-height:1.6;color:#5A5A5A;\"><strong>{{sender_name}}</strong> ({{sender_role}}) has expressed interest in your {{listing_type}}: <strong>{{listing_name}}</strong>.</p>\r\n            <div style=\"background:#F8F8F8;padding:24px;border-radius:16px;margin-bottom:32px;border:1px solid #ECECEC;\">\r\n                <h3 style=\"font-size:13px;text-transform:uppercase;letter-spacing:1px;color:#C3C6C5;margin-top:0;margin-bottom:12px;\">Message</h3>\r\n                <p style=\"margin:0;font-size:15px;color:#2a2a2a;line-height:1.6;font-style:italic;\">{{message}}</p>\r\n            </div>\r\n            <div style=\"text-align:center;margin:32px 0;\">\r\n                <a href=\"{{login_url}}\" style=\"display:inline-block;padding:16px 36px;background:#1E7A4D;color:#ffffff;text-decoration:none;border-radius:12px;font-weight:700;font-size:15px;box-shadow:0 4px 15px rgba(30,122,77,0.2);\">View & Respond</a>\r\n            </div>\r\n            <div style=\"border-top:1px solid #ECECEC;padding-top:24px;text-align:center;\">\r\n                <p style=\"margin:0;font-size:13px;color:#5A5A5A;\">Asaan Capital Ltd ΓÇö Kathmandu, Nepal</p>\r\n            </div>\r\n        </div>', '[\"user_name\",\"sender_name\",\"sender_role\",\"listing_type\",\"listing_name\",\"message\",\"login_url\"]', 1, '2026-06-02 03:02:30', '2026-06-09 17:07:19'),
(6, 'match_confirmed', 'Match Confirmed', 'You have a new match! ΓÇö Asaan Capital', '<div style=\"font-family:\'Inter\',\'Helvetica Neue\',sans-serif;max-width:600px;margin:20px auto;padding:40px;border:1px solid #eef2f6;border-radius:24px;color:#2a2a2a;background:#ffffff;box-shadow:0 10px 30px rgba(0,0,0,0.02);\">\n            <div style=\"text-align:center;margin-bottom:32px;\">\n                <span style=\"font-size:28px;font-weight:900;color:#1E4866;letter-spacing:-0.5px;\">Asaan<span style=\"color:#98202A;\">Capital</span></span>\n            </div>\n            <div style=\"text-align:center;margin-bottom:32px;\">\n                <div style=\"text-align:center;margin-bottom:16px;\">\n                    <svg width=\"48\" height=\"48\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"#1E7A4D\" stroke-width=\"1.5\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2\"/><circle cx=\"9\" cy=\"7\" r=\"4\"/><path d=\"M23 21v-2a4 4 0 0 0-3-3.87\"/><path d=\"M16 3.13a4 4 0 0 1 0 7.75\"/></svg>\n                </div>\n                <h2 style=\"color:#1E7A4D;font-size:26px;font-weight:800;margin:0;letter-spacing:-0.5px;\">It\'s a Match!</h2>\n                <p style=\"color:#5A5A5A;margin-top:8px;font-size:15px;\">Both sides are interested ΓÇö time to connect.</p>\n            </div>\n            <p style=\"font-size:16px;margin-bottom:24px;line-height:1.6;\">Hello <strong style=\"color:#1E4866;\">{{user_name}}</strong>,</p>\n            <p style=\"font-size:15px;margin-bottom:28px;line-height:1.6;color:#5A5A5A;\">Great news! <strong>{{matched_user_name}}</strong> ({{matched_user_role}}) has also expressed interest in your {{context_type}}. You are now connected and can view each other\'s contact details.</p>\n            <div style=\"background:#f0fdf4;padding:20px;border-radius:12px;margin-bottom:24px;border:1px solid #bbf7d0;\">\n                <p style=\"margin:0;font-size:15px;color:#166534;line-height:1.5;\"><strong>Context :</strong> {{context_name}}</p>\n            </div>\n            <div style=\"text-align:center;margin:32px 0;\">\n                <a href=\"{{login_url}}\" style=\"display:inline-block;padding:16px 36px;background:#1E7A4D;color:#ffffff;text-decoration:none;border-radius:12px;font-weight:700;font-size:15px;box-shadow:0 4px 15px rgba(30,122,77,0.2);\">Start Conversation</a>\n            </div>\n            <div style=\"border-top:1px solid #ECECEC;padding-top:24px;text-align:center;\">\n                <p style=\"margin:0;font-size:13px;color:#5A5A5A;\">Asaan Capital Ltd ΓÇö Kathmandu, Nepal</p>\n            </div>\n        </div>', '[\"user_name\",\"matched_user_name\",\"matched_user_role\",\"context_type\",\"context_name\",\"login_url\"]', 1, '2026-06-02 03:02:30', NULL),
(7, 'interest_accepted', 'Interest Request Accepted', 'Your interest was accepted! ΓÇö Asaan Capital Ltd.', '<div style=\"font-family:\'Inter\',\'Helvetica Neue\',sans-serif;max-width:600px;margin:20px auto;padding:40px;border:1px solid #eef2f6;border-radius:24px;color:#2a2a2a;background:#ffffff;box-shadow:0 10px 30px rgba(0,0,0,0.02);\">\r\n            <div style=\"text-align:center;margin-bottom:32px;\">\r\n                <span style=\"font-size:28px;font-weight:900;color:#1E4866;letter-spacing:-0.5px;\">Asaan<span style=\"color:#98202A;\">Capital</span></span>\r\n            </div>\r\n            <div style=\"text-align:center;margin-bottom:32px;\">\r\n                <h2 style=\"color:#1E7A4D;font-size:26px;font-weight:800;margin:0;letter-spacing:-0.5px;\">Interest Accepted!</h2>\r\n                <p style=\"color:#5A5A5A;margin-top:8px;font-size:15px;\">{{responder_name}} wants to connect with you.</p>\r\n            </div>\r\n            <p style=\"font-size:16px;margin-bottom:24px;line-height:1.6;\">Hello <strong style=\"color:#1E4866;\">{{user_name}}</strong>,</p>\r\n            <p style=\"font-size:15px;margin-bottom:28px;line-height:1.6;color:#5A5A5A;\"><strong>{{responder_name}}</strong> has accepted your interest request for <strong>{{listing_name}}</strong>. You can now exchange contact details and move forward.</p>\r\n            <div style=\"text-align:center;margin:32px 0;\">\r\n                <a href=\"{{login_url}}\" style=\"display:inline-block;padding:16px 36px;background:#1E7A4D;color:#ffffff;text-decoration:none;border-radius:12px;font-weight:700;font-size:15px;box-shadow:0 4px 15px rgba(30,122,77,0.2);\">View Connection</a>\r\n            </div>\r\n            <div style=\"border-top:1px solid #ECECEC;padding-top:24px;text-align:center;\">\r\n                <p style=\"margin:0;font-size:13px;color:#5A5A5A;\">Asaan Capital Ltd ΓÇö Kathmandu, Nepal</p>\r\n            </div>\r\n        </div>', '[\"user_name\",\"responder_name\",\"listing_type\",\"listing_name\",\"login_url\"]', 1, '2026-06-02 03:02:30', '2026-06-09 17:07:07'),
(8, 'broadcast', 'Admin Broadcast', '{{subject}} ΓÇö Asaan Capital Ltd.', '<div style=\"font-family:\'Inter\',\'Helvetica Neue\',sans-serif;max-width:600px;margin:20px auto;padding:40px;border:1px solid #eef2f6;border-radius:24px;color:#2a2a2a;background:#ffffff;box-shadow:0 10px 30px rgba(0,0,0,0.02);\">\r\n            <div style=\"text-align:center;margin-bottom:32px;\">\r\n                <span style=\"font-size:28px;font-weight:900;color:#1E4866;letter-spacing:-0.5px;\">Asaan<span style=\"color:#98202A;\">Capital</span></span>\r\n            </div>\r\n            <div style=\"text-align:center;margin-bottom:24px;\">\r\n                <h2 style=\"color:#1E4866;font-size:22px;font-weight:800;margin:0;letter-spacing:-0.5px;\">{{subject}}</h2>\r\n            </div>\r\n            <p style=\"font-size:16px;margin-bottom:24px;line-height:1.6;\">Hello <strong style=\"color:#1E4866;\">{{user_name}}</strong>,</p>\r\n            <div style=\"font-size:15px;margin-bottom:28px;line-height:1.6;color:#5A5A5A;\">\r\n                {{message}}\r\n            </div>\r\n            <div style=\"text-align:center;margin:32px 0;\">\r\n                <a href=\"{{login_url}}\" style=\"display:inline-block;padding:14px 32px;background:#1E4866;color:#ffffff;text-decoration:none;border-radius:12px;font-weight:700;font-size:14px;box-shadow:0 4px 15px rgba(30,72,102,0.2);\">Go to Dashboard</a>\r\n            </div>\r\n            <div style=\"border-top:1px solid #ECECEC;padding-top:24px;text-align:center;\">\r\n                <p style=\"margin:0;font-size:13px;color:#5A5A5A;\">Asaan Capital Ltd ΓÇö Kathmandu, Nepal</p>\r\n            </div>\r\n        </div>', '[\"user_name\",\"subject\",\"message\",\"login_url\"]', 1, '2026-06-02 03:02:30', '2026-06-09 17:06:38'),
(9, 'verification_approved', 'Verification Approved', 'Your account has been verified ΓÇö Asaan Capital', '<div style=\"font-family:\'Inter\',\'Helvetica Neue\',sans-serif;max-width:600px;margin:20px auto;padding:40px;border:1px solid #eef2f6;border-radius:24px;color:#2a2a2a;background:#ffffff;box-shadow:0 10px 30px rgba(0,0,0,0.02);\">\n            <div style=\"text-align:center;margin-bottom:32px;\">\n                <span style=\"font-size:28px;font-weight:900;color:#1E4866;letter-spacing:-0.5px;\">Asaan<span style=\"color:#98202A;\">Capital</span></span>\n            </div>\n            <div style=\"text-align:center;margin-bottom:32px;\">\n                <h2 style=\"color:#1E7A4D;font-size:26px;font-weight:800;margin:0;letter-spacing:-0.5px;\">Verification Approved &#10003;</h2>\n                <p style=\"color:#5A5A5A;margin-top:8px;font-size:15px;\">Your identity has been verified.</p>\n            </div>\n            <p style=\"font-size:16px;margin-bottom:24px;line-height:1.6;\">Hello <strong style=\"color:#1E4866;\">{{user_name}}</strong>,</p>\n            <p style=\"font-size:15px;margin-bottom:28px;line-height:1.6;color:#5A5A5A;\">Good news! Your account has been verified. You now have full access to all features including posting listings, sending interest requests, and connecting with potential partners.</p>\n            <div style=\"background:#f0fdf4;padding:20px;border-radius:12px;margin-bottom:32px;border:1px solid #bbf7d0;\">\n                <p style=\"margin:0;font-size:14px;color:#166534;line-height:1.5;\">A verified badge will appear on your profile, increasing trust and credibility with other members.</p>\n            </div>\n            <div style=\"text-align:center;margin:32px 0;\">\n                <a href=\"{{login_url}}\" style=\"display:inline-block;padding:16px 36px;background:#1E7A4D;color:#ffffff;text-decoration:none;border-radius:12px;font-weight:700;font-size:15px;box-shadow:0 4px 15px rgba(30,122,77,0.2);\">Go to Dashboard</a>\n            </div>\n            <div style=\"border-top:1px solid #ECECEC;padding-top:24px;text-align:center;\">\n                <p style=\"margin:0;font-size:13px;color:#5A5A5A;\">Asaan Capital Ltd ΓÇö Kathmandu, Nepal</p>\n            </div>\n        </div>', '[\"user_name\",\"login_url\"]', 1, '2026-06-02 03:02:30', NULL),
(10, 'verification_rejected', 'Verification Rejected', 'Verification document needs attention ΓÇö Asaan Capital', '<div style=\"font-family:\'Inter\',\'Helvetica Neue\',sans-serif;max-width:600px;margin:20px auto;padding:40px;border:1px solid #eef2f6;border-radius:24px;color:#2a2a2a;background:#ffffff;box-shadow:0 10px 30px rgba(0,0,0,0.02);\">\n            <div style=\"text-align:center;margin-bottom:32px;\">\n                <span style=\"font-size:28px;font-weight:900;color:#1E4866;letter-spacing:-0.5px;\">Asaan<span style=\"color:#98202A;\">Capital</span></span>\n            </div>\n            <div style=\"text-align:center;margin-bottom:32px;\">\n                <h2 style=\"color:#98202A;font-size:26px;font-weight:800;margin:0;letter-spacing:-0.5px;\">Verification Update</h2>\n                <p style=\"color:#5A5A5A;margin-top:8px;font-size:15px;\">Your document requires attention.</p>\n            </div>\n            <p style=\"font-size:16px;margin-bottom:24px;line-height:1.6;\">Hello <strong style=\"color:#1E4866;\">{{user_name}}</strong>,</p>\n            <p style=\"font-size:15px;margin-bottom:28px;line-height:1.6;color:#5A5A5A;\">We reviewed your verification document and found an issue. Please see the feedback below and resubmit.</p>\n            <div style=\"background:#fef2f2;padding:24px;border-radius:16px;margin-bottom:32px;border:1px solid #fecaca;\">\n                <h3 style=\"font-size:13px;text-transform:uppercase;letter-spacing:1px;color:#98202A;margin-top:0;margin-bottom:12px;\">Reason</h3>\n                <p style=\"margin:0;font-size:15px;color:#991b1b;line-height:1.6;\">{{rejection_reason}}</p>\n            </div>\n            <div style=\"text-align:center;margin:32px 0;\">\n                <a href=\"{{login_url}}\" style=\"display:inline-block;padding:16px 36px;background:#98202A;color:#ffffff;text-decoration:none;border-radius:12px;font-weight:700;font-size:15px;box-shadow:0 4px 15px rgba(152,32,42,0.2);\">Re-upload Document</a>\n            </div>\n            <div style=\"border-top:1px solid #ECECEC;padding-top:24px;text-align:center;\">\n                <p style=\"margin:0;font-size:13px;color:#5A5A5A;\">Asaan Capital Ltd ΓÇö Kathmandu, Nepal</p>\n            </div>\n        </div>', '[\"user_name\",\"rejection_reason\",\"login_url\"]', 1, '2026-06-02 03:02:30', NULL),
(11, 'new_message', 'New Message Notification', 'New message from {{sender_name}} ΓÇö Asaan Capital', '<div style=\"font-family:\'Inter\',\'Helvetica Neue\',sans-serif;max-width:600px;margin:20px auto;padding:40px;border:1px solid #eef2f6;border-radius:24px;color:#2a2a2a;background:#ffffff;box-shadow:0 10px 30px rgba(0,0,0,0.02);\">\n            <div style=\"text-align:center;margin-bottom:32px;\">\n                <span style=\"font-size:28px;font-weight:900;color:#1E4866;letter-spacing:-0.5px;\">Asaan<span style=\"color:#98202A;\">Capital</span></span>\n            </div>\n            <div style=\"text-align:center;margin-bottom:32px;\">\n                <h2 style=\"color:#1E4866;font-size:26px;font-weight:800;margin:0;letter-spacing:-0.5px;\">New Message</h2>\n                <p style=\"color:#5A5A5A;margin-top:8px;font-size:15px;\">You have a new message from {{sender_name}}.</p>\n            </div>\n            <p style=\"font-size:16px;margin-bottom:24px;line-height:1.6;\">Hello <strong style=\"color:#1E4866;\">{{user_name}}</strong>,</p>\n            <div style=\"background:#F8F8F8;padding:24px;border-radius:16px;margin-bottom:32px;border:1px solid #ECECEC;\">\n                <p style=\"margin:0;font-size:15px;color:#2a2a2a;line-height:1.6;font-style:italic;\">\"{{message_preview}}\"</p>\n            </div>\n            <div style=\"text-align:center;margin:32px 0;\">\n                <a href=\"{{login_url}}\" style=\"display:inline-block;padding:16px 36px;background:#1E4866;color:#ffffff;text-decoration:none;border-radius:12px;font-weight:700;font-size:15px;box-shadow:0 4px 15px rgba(30,72,102,0.2);\">View Message</a>\n            </div>\n            <div style=\"border-top:1px solid #ECECEC;padding-top:24px;text-align:center;\">\n                <p style=\"margin:0;font-size:13px;color:#5A5A5A;\">Asaan Capital Ltd ΓÇö Kathmandu, Nepal</p>\n            </div>\n        </div>', '[\"user_name\",\"sender_name\",\"message_preview\",\"login_url\"]', 1, '2026-06-02 03:02:30', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `faqs`
--

INSERT INTO `faqs` (`id`, `question`, `answer`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'How does the platform ensure profiles are genuine?', 'Every profile is manually reviewed by our analysts. We verify email, phone, and social accounts. Businesses also undergo document verification (GST, registration certificate, financials).', 1, 1, NULL, NULL),
(2, 'When do contact details get shared?', 'Contact info is revealed only when both parties express mutual interest. This prevents unsolicited outreach and protects your identity until you are ready to connect.', 2, 1, NULL, NULL),
(3, 'What types of transactions are supported?', 'Full business sale, partial stake sale, investment, business loan, asset sale, franchise, and distributorship opportunities across all industries and regions.', 3, 1, NULL, NULL),
(4, 'Is there a fee to use Asaan Capital?', 'Basic registration is free. Premium plans start at NPR 25,500. A 1% finders fee applies post deal closure on paid plans. No hidden charges.', 4, 1, NULL, NULL),
(5, 'How long does verification take?', 'Verification typically takes 24-48 hours after you upload your documents. Our admin team reviews each submission manually.', 5, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `franchises`
--

CREATE TABLE `franchises` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `brand_name` varchar(255) NOT NULL,
  `sector_id` bigint(20) UNSIGNED DEFAULT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `franchises`
--

INSERT INTO `franchises` (`id`, `user_id`, `brand_name`, `sector_id`, `established_year`, `existing_units`, `countries_present`, `description`, `ideal_partner_profile`, `franchise_fee`, `royalty_pct`, `marketing_fee_pct`, `total_investment_min`, `total_investment_max`, `expected_payback_months`, `training_provided`, `territory_protection`, `logo_path`, `is_published`, `is_hidden`, `is_featured`, `views`, `rating`, `created_at`, `updated_at`) VALUES
(1, 2, 'Nepal Bites Express', 9, 2018, 12, 'Nepal', 'Fast-casual Nepali restaurant chain. Serving authentic momo, chowmein, and thalis in modern format.', 'Experienced restaurateur with passion for Nepali cuisine. Minimum 2 years F&B experience.', 500000.00, 5.00, 2.00, 2500000.00, 5000000.00, 18, 1, 1, NULL, 1, 0, 1, 1, 8.9, '2026-05-29 04:34:33', '2026-05-29 04:34:33'),
(2, 6, 'Everest Coffee House', 9, 2016, 8, 'Nepal', 'Specialty coffee franchise serving single-origin Nepali beans.', 'Hospitality-minded operator with a prime retail location.', 400000.00, 5.00, 2.00, 2000000.00, 4000000.00, 20, 1, 1, NULL, 1, 0, 1, 520, 8.7, '2026-05-29 22:15:00', '2026-05-29 22:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `homepage_contents`
--

CREATE TABLE `homepage_contents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `homepage_contents`
--

INSERT INTO `homepage_contents` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'hero_title', 'Connect with <span class=\"highlight\">Investors</span>.<br>Sell or Grow Your Business <span class=\"highlight\">Faster</span>.', NULL, NULL),
(2, 'hero_subtitle', 'The premium marketplace where verified business owners meet qualified investors, buyers, and franchise partners. Close deals with confidence.', NULL, NULL),
(3, 'stats_businesses', '67,500+', NULL, NULL),
(4, 'stats_investors', '44,000+', NULL, NULL),
(5, 'stats_matches', '12,800+', NULL, NULL),
(6, 'stats_deal_value', 'NPR 850 Cr+', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `interest_requests`
--

CREATE TABLE `interest_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sender_id` bigint(20) UNSIGNED NOT NULL,
  `receiver_id` bigint(20) UNSIGNED NOT NULL,
  `pitch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `business_id` bigint(20) UNSIGNED DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `responded_at` timestamp NULL DEFAULT NULL,
  `rejected_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `investor_profiles`
--

CREATE TABLE `investor_profiles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `investor_profiles`
--

INSERT INTO `investor_profiles` (`id`, `user_id`, `past_investments`, `portfolio_companies`, `total_capital_deployed`, `preferred_sectors`, `preferred_stages`, `ticket_min`, `ticket_max`, `preferred_geography`, `references`, `created_at`, `updated_at`) VALUES
(1, 2, 6, 'Nepal Solar Pvt Ltd, Green Agri Ventures, HealthTech Nepal, EduInnovate', 50000000.00, '[\"AgriTech\",\"CleanTech\",\"HealthTech\"]', '[\"Early Revenue\",\"Growth\"]', 1500000.00, 20000000.00, '[\"Bagmati\",\"Gandaki\"]', 'Mr. Rajesh Hamal - +977 9851 234567', NULL, NULL),
(2, 4, 9, 'EcoEnergy, SmartFarm Nepal, CleanWater Tech, WasteToValue, BioAgri', 200000000.00, '[\"AgriTech\",\"CleanTech\",\"Deep Tech\"]', '[\"MVP\",\"Early Revenue\",\"Growth\"]', 4000000.00, 15000000.00, '[\"Bagmati\",\"Gandaki\",\"Province 1\"]', 'Dr. Sagar Acharya - +977 9851 876543', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `matches`
--

CREATE TABLE `matches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `interest_request_id` bigint(20) UNSIGNED NOT NULL,
  `user_a_id` bigint(20) UNSIGNED NOT NULL,
  `user_b_id` bigint(20) UNSIGNED NOT NULL,
  `context_type` varchar(50) DEFAULT NULL,
  `context_id` bigint(20) UNSIGNED DEFAULT NULL,
  `matched_at` timestamp NULL DEFAULT current_timestamp(),
  `closed_status` varchar(50) DEFAULT 'open',
  `closed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_05_27_000001_add_role_and_profile_fields_to_users', 1),
(5, '2026_05_27_000002_create_sectors_table', 1),
(6, '2026_05_27_000003_create_investor_profiles_table', 1),
(7, '2026_05_27_000004_create_pitches_table', 1),
(8, '2026_05_27_000005_create_pitch_media_table', 1),
(9, '2026_05_27_000006_create_pitch_team_members_table', 1),
(10, '2026_05_27_000007_create_verification_documents_table', 1),
(11, '2026_05_27_000008_create_interest_requests_table', 1),
(12, '2026_05_27_000009_create_notifications_table', 1),
(13, '2026_05_27_000010_create_faqs_table', 1),
(14, '2026_05_27_000011_create_homepage_contents_table', 1),
(15, '2026_05_27_000012_add_detailed_pitch_fields', 1),
(16, '2026_05_27_000013_add_pitch_extras_for_v1_5', 2);

-- --------------------------------------------------------

--
-- Table structure for table `nda_requests`
--

CREATE TABLE `nda_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `investor_id` bigint(20) UNSIGNED NOT NULL,
  `signed` tinyint(1) NOT NULL DEFAULT 0,
  `signed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `nda_requests`
--

INSERT INTO `nda_requests` (`id`, `business_id`, `investor_id`, `signed`, `signed_at`, `created_at`, `updated_at`) VALUES
(1, 33, 19, 1, '2026-06-13 12:40:39', '2026-06-13 12:40:39', '2026-06-13 12:40:39');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `title` text NOT NULL,
  `body` text DEFAULT NULL,
  `action_url` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `body`, `action_url`, `is_read`, `created_at`, `updated_at`) VALUES
(1, 1, 'new_user', 'New User Registered', 'Bikash Rana registered as a business owner', '/admin/verification', 0, '2026-05-29 04:34:33', NULL),
(2, 2, 'match', 'Match Made!', 'You have been matched with Enterprise Software Co.', '/connections', 0, '2026-05-29 04:34:33', NULL),
(3, 3, 'interest', 'New Interest Request', 'Ramesh Thapa has expressed interest in your business', '/connections', 0, '2026-05-29 04:34:33', NULL),
(4, 1, 'interest', 'New Inquiry', 'R K Block Udhyog is interested in Mountain Vista Resort & Spa', '/business/11', 0, '2026-06-10 02:14:32', NULL),
(5, 1, 'report', 'New Report Submitted', 'business #11 reported: inaccurate_info', '/admin/reports', 0, '2026-06-10 02:15:28', NULL),
(6, 21, 'verification', 'Verification Approved', 'Your account has been verified. You now have full access to the platform.', '/dashboard', 0, '2026-06-11 11:40:34', NULL),
(7, 20, 'verification', 'Verification Approved', 'Your account has been verified. You now have full access to the platform.', '/dashboard', 0, '2026-06-11 11:40:39', NULL),
(8, 19, 'verification', 'Verification Approved', 'Your account has been verified. You now have full access to the platform.', '/dashboard', 0, '2026-06-11 11:40:41', NULL),
(9, 22, 'verification', 'Verification Approved', 'Your account has been verified. You now have full access to the platform.', '/dashboard', 0, '2026-06-12 12:01:32', NULL),
(10, 19, 'interest', 'New Inquiry', 'Asaan Credit Ltd is interested in R K Block Udhyog', '/business/33', 0, '2026-06-13 12:39:37', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `slug` varchar(120) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content_html` mediumtext NOT NULL,
  `meta_description` varchar(320) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `slug`, `title`, `content_html`, `meta_description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'about', 'Our Story', '\r\n<div class=\"pub-wrap-narrow\" style=\"padding-top:var(--space-6);padding-bottom:var(--space-8);\">\r\n  <div class=\"breadcrumbs pub-text\" style=\"margin-bottom:var(--space-5);\">\r\n    <a href=\"/\" style=\"color:var(--dash-ink-soft);text-decoration:none;\">Home</a>\r\n    <span style=\"margin:0 0.5rem;\">/</span>\r\n    <span>Our Story</span>\r\n  </div>\r\n\r\n  <h1 class=\"pub-h1\" style=\"margin-bottom:var(--space-2);\">Our Story</h1>\r\n  <p class=\"pub-text-lead\" style=\"font-size:1.2rem;line-height:1.7;color:var(--dash-ink-soft);margin-bottom:var(--space-6);\">\r\n    Nepal\'s trusted marketplace connecting business owners with investors, buyers, and advisors.\r\n  </p>\r\n\r\n  <div class=\"pub-prose\">\r\n    <h2>Who We Are</h2>\r\n    <p>Asaan Capital Ltd is a Kathmandu-based financial services company operating Nepal\'s first dedicated online marketplace for business matching, M&A, and fundraising. We bridge the gap between capital seekers and capital providers — making business transactions <em>asaan</em> (easy).</p>\r\n\r\n    <h2>Our Promise</h2>\r\n    <p>Every listing on our platform is manually verified. Contact details stay private until both parties express mutual interest. We do not facilitate payments or execute transactions — we connect, you close.</p>\r\n\r\n    <h2>Our Reach</h2>\r\n    <p>We serve all seven provinces of Nepal with a growing network of entrepreneurs, investors, franchisors, and advisors across sectors including agriculture, technology, manufacturing, tourism, and services.</p>\r\n\r\n    <h2>Our Journey</h2>\r\n    <div style=\"display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:var(--space-4);margin:var(--space-6) 0;\">\r\n      <div style=\"background:var(--dash-card);border:1px solid var(--dash-border);border-radius:var(--radius-lg);padding:var(--space-5);text-align:center;\">\r\n        <div style=\"font-size:2rem;font-weight:700;color:var(--dash-primary);\">2024</div>\r\n        <div style=\"font-size:0.9rem;color:var(--dash-ink-soft);\">Platform conceived &amp; built</div>\r\n      </div>\r\n      <div style=\"background:var(--dash-card);border:1px solid var(--dash-border);border-radius:var(--radius-lg);padding:var(--space-5);text-align:center;\">\r\n        <div style=\"font-size:2rem;font-weight:700;color:var(--dash-primary);\">2025</div>\r\n        <div style=\"font-size:0.9rem;color:var(--dash-ink-soft);\">Soft launch + first matches</div>\r\n      </div>\r\n      <div style=\"background:var(--dash-card);border:1px solid var(--dash-border);border-radius:var(--radius-lg);padding:var(--space-5);text-align:center;\">\r\n        <div style=\"font-size:2rem;font-weight:700;color:var(--dash-primary);\">2026</div>\r\n        <div style=\"font-size:0.9rem;color:var(--dash-ink-soft);\">Full platform + nationwide presence</div>\r\n      </div>\r\n    </div>\r\n\r\n    <h2>Our Team</h2>\r\n    <p>We are a small, dedicated team of finance professionals, technologists, and relationship managers based in Bhaktapur, Nepal. We understand the Nepali market because we are part of it.</p>\r\n  </div>\r\n</div>\r\n', 'Learn the story behind Asaan Capital Ltd. Nepal\'s trusted marketplace for business matching, M&A, and fundraising.', 1, '2026-06-10 11:44:12', '2026-06-10 11:44:12'),
(2, 'contact', 'Contact Us', '\r\n<div class=\"pub-wrap-narrow\" style=\"padding-top:var(--space-6);padding-bottom:var(--space-8);\">\r\n  <div class=\"breadcrumbs pub-text\" style=\"margin-bottom:var(--space-5);\">\r\n    <a href=\"/\" style=\"color:var(--dash-ink-soft);text-decoration:none;\">Home</a>\r\n    <span style=\"margin:0 0.5rem;\">/</span>\r\n    <span>Contact Us</span>\r\n  </div>\r\n\r\n  <h1 class=\"pub-h1\" style=\"margin-bottom:var(--space-6);\">Contact Us</h1>\r\n\r\n  <div style=\"display:grid;grid-template-columns:1fr 1fr;gap:var(--space-6);\">\r\n    <div>\r\n      <form method=\"post\" action=\"/contact\">\r\n        <input type=\"hidden\" name=\"_csrf\" value=\"{{CSRF_TOKEN}}\">\r\n        <div style=\"display:none;\"><input type=\"text\" name=\"website\" tabindex=\"-1\" autocomplete=\"off\"></div>\r\n        <div class=\"input-group\" style=\"margin-bottom:var(--space-4);\">\r\n          <label>Your Name</label>\r\n          <input type=\"text\" name=\"name\" class=\"input\" required>\r\n        </div>\r\n        <div class=\"input-group\" style=\"margin-bottom:var(--space-4);\">\r\n          <label>Email</label>\r\n          <input type=\"email\" name=\"email\" class=\"input\" required>\r\n        </div>\r\n        <div class=\"input-group\" style=\"margin-bottom:var(--space-4);\">\r\n          <label>Subject</label>\r\n          <input type=\"text\" name=\"subject\" class=\"input\" required>\r\n        </div>\r\n        <div class=\"input-group\" style=\"margin-bottom:var(--space-4);\">\r\n          <label>Message</label>\r\n          <textarea name=\"message\" class=\"input\" rows=\"5\" required></textarea>\r\n        </div>\r\n        <button type=\"submit\" class=\"btn btn-primary\" style=\"font-size:1rem;padding:0.75rem 2rem;\">Send Message</button>\r\n      </form>\r\n    </div>\r\n    <div>\r\n      <div style=\"background:var(--dash-card);border:1px solid var(--dash-border);border-radius:var(--radius-lg);padding:var(--space-5);margin-bottom:var(--space-4);\">\r\n        <h3 style=\"margin:0 0 var(--space-2);font-size:1rem;\">Office</h3>\r\n        <p style=\"margin:0;color:var(--dash-ink-soft);font-size:0.95rem;\">Madhyapur Thimi Municipality-9<br>Bhaktapur, Nepal</p>\r\n      </div>\r\n      <div style=\"background:var(--dash-card);border:1px solid var(--dash-border);border-radius:var(--radius-lg);padding:var(--space-5);margin-bottom:var(--space-4);\">\r\n        <h3 style=\"margin:0 0 var(--space-2);font-size:1rem;\">Phone</h3>\r\n        <p style=\"margin:0;color:var(--dash-ink-soft);font-size:0.95rem;\">+977-9848714990<br>+977-982000470</p>\r\n      </div>\r\n      <div style=\"background:var(--dash-card);border:1px solid var(--dash-border);border-radius:var(--radius-lg);padding:var(--space-5);margin-bottom:var(--space-4);\">\r\n        <h3 style=\"margin:0 0 var(--space-2);font-size:1rem;\">Email</h3>\r\n        <p style=\"margin:0;color:var(--dash-ink-soft);font-size:0.95rem;\"><a href=\"mailto:info@asaancapital.com\" style=\"color:var(--dash-primary);\">info@asaancapital.com</a></p>\r\n      </div>\r\n      <div style=\"background:var(--dash-card);border:1px solid var(--dash-border);border-radius:var(--radius-lg);padding:var(--space-5);\">\r\n        <h3 style=\"margin:0 0 var(--space-2);font-size:1rem;\">Hours</h3>\r\n        <p style=\"margin:0;color:var(--dash-ink-soft);font-size:0.95rem;\">Sunday – Friday<br>9:00 AM – 5:00 PM NPT</p>\r\n      </div>\r\n    </div>\r\n  </div>\r\n</div>\r\n', 'Contact Asaan Capital Ltd. Get in touch with Nepal\'s trusted business marketplace.', 1, '2026-06-10 11:44:12', '2026-06-10 11:44:12'),
(3, 'terms', 'Terms of Service', '\r\n<div class=\"pub-wrap-narrow\" style=\"padding-top:var(--space-6);padding-bottom:var(--space-8);\">\r\n  <div class=\"breadcrumbs pub-text\" style=\"margin-bottom:var(--space-5);\">\r\n    <a href=\"/\" style=\"color:var(--dash-ink-soft);text-decoration:none;\">Home</a>\r\n    <span style=\"margin:0 0.5rem;\">/</span>\r\n    <span>Terms of Service</span>\r\n  </div>\r\n\r\n  <h1 class=\"pub-h1\" style=\"margin-bottom:var(--space-2);\">Terms of Service</h1>\r\n  <p class=\"pub-text\" style=\"color:var(--dash-ink-soft);margin-bottom:var(--space-6);\">Last updated: June 10, 2026</p>\r\n\r\n  <div class=\"pub-prose\">\r\n    <h3>1. Platform Role</h3>\r\n    <p>Asaan Capital Ltd is a discovery and matching platform. We connect business owners, investors, buyers, lenders, franchisors, and advisors. We do not facilitate payments, execute transactions, or provide investment advice. All deals are conducted directly between parties.</p>\r\n\r\n    <h3>2. Eligibility</h3>\r\n    <p>Users must be at least 18 years old. Businesses must be legally registered entities in Nepal. All users must pass manual verification before their profiles go live. We reserve the right to reject any registration without explanation.</p>\r\n\r\n    <h3>3. Verification &amp; Data</h3>\r\n    <p>Verification documents (citizenship, PAN, company registration, VAT/GST certificate) are stored securely and accessible only to platform administrators. Documents are never shared publicly. Standard security measures including SSL encryption are employed throughout the platform.</p>\r\n\r\n    <h3>4. Profile Rules</h3>\r\n    <p>Each user may maintain one active business profile at a time. Profiles must be accurate, current, and not misleading. We prohibit scraping, automated data collection, unsolicited messaging, and any form of spam or solicitation outside the platform\'s intended use.</p>\r\n\r\n    <h3>5. Confidentiality</h3>\r\n    <p>Contact information is revealed only after mutual interest is established through the platform. Users may not share or misuse contact details obtained through the platform. Violation may result in permanent account suspension without refund.</p>\r\n\r\n    <h3>6. Refund Policy</h3>\r\n    <p>Profile activation typically completes within 2 business days. CIM and Valuation Model services have a 25 business day SLA. A 5% processing fee applies in eligible refund cases. No refund for change-of-mind or non-use. Refund requests must be submitted within 3 months via email. Credits process within 5–15 business days.</p>\r\n\r\n    <h3>7. Finder\'s Fee</h3>\r\n    <p>Paid subscription plans include a 1% finder\'s fee payable upon successful transaction completion between matched parties. This fee is due only when a deal closes.</p>\r\n\r\n    <h3>8. Governing Law</h3>\r\n    <p>These terms are governed by the laws of Nepal. Disputes shall be subject to the exclusive jurisdiction of courts in Kathmandu, Nepal.</p>\r\n\r\n    <h3>9. Indemnification</h3>\r\n    <p>Users agree to indemnify and hold Asaan Capital Ltd harmless from any claims arising from their use of the platform, including but not limited to transaction disputes, misrepresentation, or breach of these terms.</p>\r\n  </div>\r\n</div>\r\n', 'Terms of Service for Asaan Capital Ltd — Nepal\'s business matching marketplace.', 1, '2026-06-10 11:44:12', '2026-06-10 11:44:12'),
(4, 'privacy', 'Privacy Policy', '\r\n<div class=\"pub-wrap-narrow\" style=\"padding-top:var(--space-6);padding-bottom:var(--space-8);\">\r\n  <div class=\"breadcrumbs pub-text\" style=\"margin-bottom:var(--space-5);\">\r\n    <a href=\"/\" style=\"color:var(--dash-ink-soft);text-decoration:none;\">Home</a>\r\n    <span style=\"margin:0 0.5rem;\">/</span>\r\n    <span>Privacy Policy</span>\r\n  </div>\r\n\r\n  <h1 class=\"pub-h1\" style=\"margin-bottom:var(--space-2);\">Privacy Policy</h1>\r\n  <p class=\"pub-text\" style=\"color:var(--dash-ink-soft);margin-bottom:var(--space-6);\">Last updated: June 10, 2026</p>\r\n\r\n  <div class=\"pub-prose\">\r\n    <h3>1. Information We Collect</h3>\r\n    <p>We collect the following information when you register and use our platform:</p>\r\n    <ul>\r\n      <li><strong>Identity data:</strong> full name, email address, phone number, profile photo</li>\r\n      <li><strong>Business data:</strong> company name, registration documents, financial information, business descriptions, PAN/VAT details</li>\r\n      <li><strong>Verification data:</strong> citizenship certificate, company registration certificate, tax clearance, and other verification documents</li>\r\n      <li><strong>Usage data:</strong> pages visited, searches performed, interests expressed, time spent on platform</li>\r\n      <li><strong>Technical data:</strong> IP address, browser type, device information, session data</li>\r\n    </ul>\r\n\r\n    <h3>2. How We Use Your Data</h3>\r\n    <p>Your data is used exclusively for:</p>\r\n    <ul>\r\n      <li>Platform operation and account management</li>\r\n      <li>Matching algorithms that suggest relevant connections</li>\r\n      <li>Identity verification and fraud prevention</li>\r\n      <li>Platform communications (interest requests, matches, notifications)</li>\r\n      <li>Analytics and platform improvement</li>\r\n      <li>Compliance with legal and regulatory obligations</li>\r\n    </ul>\r\n\r\n    <h3>3. Data Sharing</h3>\r\n    <p>We do not sell, rent, or trade your personal data. Limited data is shared only when you express mutual interest with another user — and only to the extent necessary to facilitate the connection. Verification documents are never shared with other users.</p>\r\n\r\n    <h3>4. Data Retention</h3>\r\n    <p>We retain your data for as long as your account is active. After account deletion, we retain backup copies for 90 days for legal compliance, after which all data is permanently deleted.</p>\r\n\r\n    <h3>5. Your Rights</h3>\r\n    <p>You may request access to, correction of, or deletion of your personal data at any time by contacting us at info@asaancapital.com. We will respond within 15 business days.</p>\r\n\r\n    <h3>6. Security</h3>\r\n    <p>We implement SSL/TLS encryption, password hashing (bcrypt), session security measures, and regular security audits. However, no online platform can guarantee absolute security. Users are responsible for maintaining strong, unique passwords.</p>\r\n\r\n    <h3>7. Cookies</h3>\r\n    <p>We use essential session cookies for platform operation. No tracking, advertising, or third-party cookies are used. You may disable cookies in your browser, but some platform features may not function correctly.</p>\r\n\r\n    <h3>8. Contact</h3>\r\n    <p>For privacy-related inquiries, contact:<br>\r\n    Asaan Capital Ltd<br>\r\n    Madhyapur Thimi Municipality-9, Bhaktapur, Nepal<br>\r\n    Email: <a href=\"mailto:info@asaancapital.com\" style=\"color:var(--dash-primary);\">info@asaancapital.com</a></p>\r\n  </div>\r\n</div>\r\n', 'Privacy Policy for Asaan Capital Ltd. How we collect, use, and protect your data.', 1, '2026-06-10 11:44:12', '2026-06-10 11:44:12'),
(5, 'faq', 'Frequently Asked Questions', '\r\n<div class=\"pub-wrap-narrow\" style=\"padding-top:var(--space-6);padding-bottom:var(--space-8);\">\r\n  <div class=\"breadcrumbs pub-text\" style=\"margin-bottom:var(--space-5);\">\r\n    <a href=\"/\" style=\"color:var(--dash-ink-soft);text-decoration:none;\">Home</a>\r\n    <span style=\"margin:0 0.5rem;\">/</span>\r\n    <span>FAQ</span>\r\n  </div>\r\n\r\n  <h1 class=\"pub-h1\" style=\"margin-bottom:var(--space-6);\">Frequently Asked Questions</h1>\r\n\r\n  <div style=\"display:grid;gap:var(--space-3);\">\r\n    <div style=\"background:white;border-radius:var(--radius-lg);padding:var(--space-5);border:1px solid var(--dash-border);\">\r\n      <h3 style=\"margin:0 0 var(--space-2);font-size:1rem;\">What is Asaan Capital?</h3>\r\n      <p style=\"margin:0;color:var(--dash-ink-soft);font-size:0.95rem;\">Asaan Capital Ltd is Nepal\'s first online marketplace connecting business owners with investors, buyers, franchisors, and advisors. We make business matching <em>asaan</em> (easy).</p>\r\n    </div>\r\n    <div style=\"background:white;border-radius:var(--radius-lg);padding:var(--space-5);border:1px solid var(--dash-border);\">\r\n      <h3 style=\"margin:0 0 var(--space-2);font-size:1rem;\">Is Asaan Capital free to join?</h3>\r\n      <p style=\"margin:0;color:var(--dash-ink-soft);font-size:0.95rem;\">Registration and basic profile creation are free. Premium features such as CIM preparation, business valuation, and priority listing may require subscription or one-time fees.</p>\r\n    </div>\r\n    <div style=\"background:white;border-radius:var(--radius-lg);padding:var(--space-5);border:1px solid var(--dash-border);\">\r\n      <h3 style=\"margin:0 0 var(--space-2);font-size:1rem;\">How does verification work?</h3>\r\n      <p style=\"margin:0;color:var(--dash-ink-soft);font-size:0.95rem;\">After registration, you submit verification documents (citizenship, PAN, company registration). Our admin team reviews and approves within 1–2 business days. Your profile goes live only after verification.</p>\r\n    </div>\r\n    <div style=\"background:white;border-radius:var(--radius-lg);padding:var(--space-5);border:1px solid var(--dash-border);\">\r\n      <h3 style=\"margin:0 0 var(--space-2);font-size:1rem;\">When is my contact information shared?</h3>\r\n      <p style=\"margin:0;color:var(--dash-ink-soft);font-size:0.95rem;\">Your contact details remain private until you express mutual interest with another user. Only then are both parties\' contact details revealed to facilitate direct communication.</p>\r\n    </div>\r\n    <div style=\"background:white;border-radius:var(--radius-lg);padding:var(--space-5);border:1px solid var(--dash-border);\">\r\n      <h3 style=\"margin:0 0 var(--space-2);font-size:1rem;\">What types of businesses can list?</h3>\r\n      <p style=\"margin:0;color:var(--dash-ink-soft);font-size:0.95rem;\">Any legally registered business in Nepal can list, including sole proprietorships, partnerships, private limited companies, and public limited companies. We cover sectors from agriculture and manufacturing to technology and services.</p>\r\n    </div>\r\n    <div style=\"background:white;border-radius:var(--radius-lg);padding:var(--space-5);border:1px solid var(--dash-border);\">\r\n      <h3 style=\"margin:0 0 var(--space-2);font-size:1rem;\">Do you facilitate payments or transactions?</h3>\r\n      <p style=\"margin:0;color:var(--dash-ink-soft);font-size:0.95rem;\">No. We are a discovery and matching platform only. All transactions, negotiations, due diligence, and payments are conducted directly between the involved parties outside our platform.</p>\r\n    </div>\r\n    <div style=\"background:white;border-radius:var(--radius-lg);padding:var(--space-5);border:1px solid var(--dash-border);\">\r\n      <h3 style=\"margin:0 0 var(--space-2);font-size:1rem;\">How do I delete my account?</h3>\r\n      <p style=\"margin:0;color:var(--dash-ink-soft);font-size:0.95rem;\">Email us at info@asaancapital.com with your account details. We will process deletion within 15 business days. Backups are retained for 90 days per legal requirements.</p>\r\n    </div>\r\n  </div>\r\n</div>\r\n', 'Frequently Asked Questions about Asaan Capital Ltd — Nepal\'s business matching marketplace.', 1, '2026-06-10 11:44:12', '2026-06-10 11:44:12');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'password',
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`id`, `email`, `token`, `type`, `created_at`) VALUES
(10, 'isoftrosolutions@gmail.com', 'f71f9c2a58ff7a082924cd139c88ece505d26f2daaa81b3dfc382578eee75b30', 'password', '2026-06-11 10:55:50');

-- --------------------------------------------------------

--
-- Table structure for table `pitches`
--

CREATE TABLE `pitches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
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
  `pitch_image` varchar(500) DEFAULT NULL,
  `stage` varchar(255) DEFAULT NULL,
  `sector_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_hidden` tinyint(1) NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `completeness_score` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `views` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pitches`
--

INSERT INTO `pitches` (`id`, `user_id`, `tagline`, `company_registration_number`, `company_type`, `short_summary`, `product_stage`, `problem_statement`, `solution`, `market_size`, `business_model`, `revenue_model`, `monthly_revenue`, `monthly_users`, `growth_rate`, `customer_retention`, `traction`, `target_customers`, `competitors`, `competitive_advantage`, `funding_amount`, `minimum_investment`, `previous_funding`, `previous_funding_source`, `has_legal_disputes`, `legal_details`, `existing_debt`, `business_type`, `customer_type`, `looking_for`, `investor_involvement`, `open_to_acquisition`, `monthly_burn`, `runway_months`, `relocate_willingness`, `matchmaking_tags`, `equity_offered`, `fund_usage`, `valuation`, `pitch_deck`, `financial_projections`, `pitch_video_url`, `pitch_image`, `stage`, `sector_id`, `is_active`, `is_hidden`, `is_featured`, `completeness_score`, `is_published`, `views`, `created_at`, `updated_at`) VALUES
(1, 3, 'AI-powered cold storage reducing post-harvest losses for 2,400+ farmers across Nepal.', NULL, NULL, NULL, NULL, '34% of Nepal perishable produce is lost before reaching market due to lack of reliable cold storage. Small farmers lose NPR 18,000-40,000 per season.', 'Low-cost, solar-hybrid smart cold rooms with IoT monitoring and AI demand forecasting. Farmers pay per use via mobile.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2,400 farmers onboarded (Q1 2026)\nNPR 9.2M revenue run-rate\n3 provinces live, 2 more in pipeline\nPartnership with Nepal Agricultural Research Council', NULL, NULL, NULL, 28000000.00, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 12.00, NULL, NULL, NULL, NULL, NULL, NULL, 'Early Revenue', 1, 1, 0, 0, 0, 0, 0, NULL, NULL),
(2, 3, 'Making quality education accessible in rural areas through AI-powered learning platforms', NULL, NULL, 'EdTech for Rural Nepal - AI-powered learning platform', NULL, 'Rural Nepal lacks access to quality education. 70% of students in rural areas have no access to digital learning.', 'AI-powered mobile learning platform that works offline. Adaptive curriculum in Nepali language.', 'NPR 500 Cr TAM in Nepal alone', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5000000.00, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 15.00, NULL, 33333333.00, NULL, NULL, NULL, NULL, 'seed', 4, 1, 0, 1, 0, 1, 9, '2026-05-29 04:34:33', '2026-05-29 04:34:33');

-- --------------------------------------------------------

--
-- Table structure for table `pitch_media`
--

CREATE TABLE `pitch_media` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pitch_id` bigint(20) UNSIGNED NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pitch_team_members`
--

CREATE TABLE `pitch_team_members` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pitch_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `role` varchar(255) DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reporter_id` bigint(20) UNSIGNED NOT NULL,
  `target_type` varchar(50) NOT NULL,
  `target_id` bigint(20) UNSIGNED NOT NULL,
  `reason` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'open',
  `resolved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `action_taken` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `reporter_id`, `target_type`, `target_id`, `reason`, `details`, `status`, `resolved_by`, `resolved_at`, `action_taken`, `created_at`, `updated_at`) VALUES
(1, 19, 'business', 11, 'inaccurate_info', 'dfgsd', 'resolved', 1, '2026-06-12 16:19:10', 'Action', '2026-06-10 02:15:28', '2026-06-10 02:15:28');

-- --------------------------------------------------------

--
-- Table structure for table `saved_listings`
--

CREATE TABLE `saved_listings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `listing_type` varchar(50) NOT NULL,
  `listing_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sectors`
--

CREATE TABLE `sectors` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sectors`
--

INSERT INTO `sectors` (`id`, `name`, `slug`, `is_active`, `created_at`, `updated_at`) VALUES
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

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('6q6OtoyEl1P5jrlat4m5S1iO2BisUJACAi07ck4z', NULL, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiJNMzljVklKWVZ1dldOQjYxVFVVUkxPSDVwT0w5NmoxU0JwZ0xVN2ZCIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1Iiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1779896805),
('72L48Gpk2BdsEVLcxRj4Z9zCkSOxI0mj4mUYXWIU', NULL, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiJrVHM2VnFXemtRa1BwNlpKWmluQkhjSWlGRVRWYXFjYTY0bmhqSlhaIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9ob3ctaXQtd29ya3MiLCJyb3V0ZSI6Imhvdy1pdC13b3JrcyJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1779896830),
('8aYu6R7JKl78dIhF6yk7n24VMEOIWTAUiMFbt8Gv', NULL, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiJHbGYzYTJTSUpmZ0ZzVVA2UWhJZ1dIYUFkaEJ0OW14dmdlWWtvTDlwIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9sZWdhbCIsInJvdXRlIjoibGVnYWwifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1779896830),
('8RFFUWqR0wRoLjjJmqMnhT55TxV3ZE9473F1oYRy', NULL, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiJrU3VhWDFzUEE4bEdLZmE2V0tlT0dWZFlxZ3p4Um9pV29pcWMyTEhpIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9zdXBwb3J0Iiwicm91dGUiOiJzdXBwb3J0In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1779896831),
('a3tQoMitJILr1E87fUiJdPgvN7mwnyAv1Aeg2e91', NULL, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiJZdmo4S3BVOVRTV01LT3BXREJiNVFSV0tJY1JYOWZGSVdQckJEQjhCIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9waXRjaGVzXC8xIiwicm91dGUiOiJwaXRjaC5zaG93In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1779897183),
('ANO0mKUJRsK4lLtTOChQq4bbKynDq898AWuAdZzO', NULL, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiJYaVpSNlJveW1ZTmpYS2t2Zjg2eXp2RFdubzRWQXBRUmJjdmhyWWxnIiwidXJsIjp7ImludGVuZGVkIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjg3NjVcL2Rhc2hib2FyZCJ9LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjg3NjVcL2Rhc2hib2FyZCIsInJvdXRlIjoiZGFzaGJvYXJkIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1779896831),
('Az2InZOMwc1o8CIC0yL4enajGdytWmawBTM7fFsb', NULL, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiJrbXJCaVM4YlZoU09LZHR5Y1EyQlJ1SjdTSWJVWkZXYURiWGpmbHd2IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1779896805),
('DlHozGaDuzXCE6YRZzPpea6cJwgOhu3cV5Kr9mNB', 2, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiIyM29kTDRvdU9KSWhUeDI5dWZUenZTTEw3aDhNalgxSjZPZklWUGd0IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9waXRjaGVzXC8xIiwicm91dGUiOiJwaXRjaC5zaG93In0sIl9mbGFzaCI6eyJvbGQiOlsic3VjY2VzcyJdLCJuZXciOltdfSwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjIsInN1Y2Nlc3MiOiJJbnRlcmVzdCByZXF1ZXN0IHNlbnQgc3VjY2Vzc2Z1bGx5LiJ9', 1779897315),
('ecRtfVjOWr8LQlOvzqCnsHOxvKhDEIuMvFcBZqz1', NULL, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiJNdEk0QkQ0VVpLc2ZEOWhiemd6cjcxVmZMUmh0MU9oNkxGNWpINWFnIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9mYXEiLCJyb3V0ZSI6ImZhcSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1779897184),
('EMJUSmZMQWG144RYffRlLQNNtJwpzaPbZ27g9a3Y', NULL, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiJ0M0FBVHp0eWxoTmF1a3dyMnVya0pOVVhuVTBsVGE3SkNrTFE1TW5nIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1Iiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1779897182),
('eNFnDwIGxWFZG4ZPG01w5YmBXKIVyDaqRnPpl9ja', NULL, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiJWWVk1c0JydWdjZGVvTW5GVUk1aE9td09Ja3pRVjZvZ0FYbjZZZzB6IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9pbnZlc3RvcnNcLzIiLCJyb3V0ZSI6ImludmVzdG9yLnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1779896830),
('esGPA7UkON6LqgSbxPJ0Pol1HHSc7rQlsBwIpBcQ', NULL, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiJMdDU4Mll3ZTBVTjZMUm00bTg2S0oxVnRPY0VEdnN6d1FsU00zVnY1IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1Iiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1779897354),
('fNiyC7GowNVE2ddTgbmIIUm2DitdMxAFdrVo3agJ', NULL, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiJnd1oxV1ZEVHZlcXo1aGdraXBxQWxGaDhFSnRhaWJ0Nk1wTWRmdkREIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9hYm91dCIsInJvdXRlIjoiYWJvdXQifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1779896830),
('hpiDB9U3ynwbvyePeX8NTLvOxGIYy2WIxSXZPIgB', NULL, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiI4ZFZhZG1xSXNCV2NkWjlzeHN6cFN5SDJFdzByT3JrTWhhbWNMM0t3IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9hYm91dCIsInJvdXRlIjoiYWJvdXQifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1779897184),
('J4ymTUcUqYaxccL2UBccFOMRFDuLlAyitF1GY6AH', NULL, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiJJUEJYRjRpU0EyUlI3R1pHY251Q0xtaGhGZUZoczNnUjhmcWpmc0NmIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1Iiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1779897364),
('jQu0mRx4qeT67eJfIZVagcSg3907zNyEqY8I6TS4', NULL, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiI2WGx2eGtKY0h0WWM5ZHVtV2hmcEl6bVlPNnZVeHNoRWFmQXp4SjRNIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9waXRjaGVzXC8xIiwicm91dGUiOiJwaXRjaC5zaG93In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1779896830),
('JUwxdZc56gJXK1Ji8eLFmCnmbSHgkcsigwm30wWb', NULL, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiJkYXdQWjRCU3pWRDh4RGNhYURDYnFRNjdVRmU1SDR4ZFliTEhMSm5kIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9mYXEiLCJyb3V0ZSI6ImZhcSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1779896830),
('mJC4Q95hsgmlHjpfUCtLiXojxyE56Jv5BlnPKZyV', NULL, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiJvenlFaHo2TkJyRURQa09jM28yc0UyOGM4ME93Um5BMFlYNXl4eHJUIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9waXRjaGVzXC8xIiwicm91dGUiOiJwaXRjaC5zaG93In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1779896898),
('mubIBCIaCzPM0rsiHgyhBtzTHrPkMx1Q4dhcOwdy', NULL, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiJhVlVMWUFORDI4UG1VWEZvQXZkT0pnUkd5SzZRbDFGZUk4b1NwVnM4IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9yZWdpc3RlciIsInJvdXRlIjoicmVnaXN0ZXIifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1779897182),
('np8vmiLSeDtpuh3Qfio8RRBywmX0fEg82E3pJcMP', NULL, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiJmRmRkdHF3cDgyWG1RWVBOMDdGd2ZXVmlMbXBhSklhc0c1OFdycDB2IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9icm93c2VcL2VudHJlcHJlbmV1cnMiLCJyb3V0ZSI6ImJyb3dzZS5lbnRyZXByZW5ldXJzIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1779897183),
('nqGVAewKZchJctAkHpqTc8LtiIqad4v9Cn2vReSm', NULL, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiJzTlI0SWlGUVRPRHVxYlhRQ0pKcTZkajJnU09pdGFXVU1qWGw0clp2IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9waXRjaGVzXC8xIiwicm91dGUiOiJwaXRjaC5zaG93In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1779896873),
('p0Pzv0nNWtmhh79D2hGhwi3ZOLVWg4dswElFWNps', NULL, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiJtZzdLZUtLNUYwODU2S25RSzdBTVc0a2gwMVpYNzV0c2hOZEk3aWtoIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9yZWdpc3RlciIsInJvdXRlIjoicmVnaXN0ZXIifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1779896805),
('rEK74FKOoNuE4O0LkgJmCpFnOxjSg8py91C3ukL5', NULL, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiJ3MlFSMDkxOTQ3YjNUbURQcUdlWW5xVDZaMTg5MXJ5WEFHalE2WkhrIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9zdXBwb3J0Iiwicm91dGUiOiJzdXBwb3J0In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1779897184),
('RTWCas5tc5U0vDkohoz9pWqlopNbKahAakTtRvO3', NULL, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiJMQ205WXFXMkxQVFlBTU5pTFhqdzVPcWNldWd2alVQMzZkZEo1TmtCIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1Iiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1779896790),
('s9ICfugWMW1rdRpZqLOhUB5rOg4zcOkJ3Hnmhk98', NULL, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiJ5d3FDaUhaMG5tTkRJWlY1WmJqZ3BucExJYUJsNWZwQ0xKMUZSb1l5IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1Iiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1779897369),
('sWUqOLzh5fW4jiRgEccUOY4baWm0cnub1v3En3B7', 1, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiJOSmlXbEVER29raVhyWHIzVWl0WDlBWUVYOWRBNEppOWpRVlRGM2ttIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9hZG1pblwvYnJvYWRjYXN0Iiwicm91dGUiOiJhZG1pbi5icm9hZGNhc3QifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MX0=', 1779897185),
('T2cRDu11oRPaUDFz32hdAj9nN1NmygD4Z1i7zbAJ', NULL, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiJuZE5vcW1yOTNhSGZjMHUxOEg1MVAwNG4zUXRickFieGFDN3liSTk0IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9pbnZlc3RvcnNcLzIiLCJyb3V0ZSI6ImludmVzdG9yLnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1779897183),
('t5tzBB857SwLt4aR8nhcOEx2kehtaLjI13G7Kwcz', NULL, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiJlNDVmWVlYa20xamdJNGhJQ0FUMlFmbTI0a0RZTnF6ZmdWejdEeDhNIiwidXJsIjp7ImludGVuZGVkIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjg3NjVcL2FkbWluIn0sIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODc2NVwvYWRtaW4iLCJyb3V0ZSI6ImFkbWluLmRhc2hib2FyZCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1779896831),
('uENvLWZEcpf9eHAkkRq8y9HSevkkyZ74bswAg27w', NULL, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiJIZ1FnbDV2TWN0aldZR3R1NE9uUFpDTzhQR0tVSXFhenhGY2JBYXdnIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9ob3ctaXQtd29ya3MiLCJyb3V0ZSI6Imhvdy1pdC13b3JrcyJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1779897184),
('uvaoXu04mDvE8JJG4hGvAIPcKCONbQcgnY7wvpH4', NULL, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiJnemd6VDVndzBSQTJUUTRpMXZDTzJzOE9VODZBUFdDako1M001SEhGIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9icm93c2VcL2VudHJlcHJlbmV1cnMiLCJyb3V0ZSI6ImJyb3dzZS5lbnRyZXByZW5ldXJzIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1779896806),
('Vc6NA4csKqrdUyK50JBL2jYGEdtZUGBZgUWfaiaB', NULL, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiJmQ0UxUEpoOVZGRk84MWVWcFFVbFpMdW1IbmtwR0pOTHhIZEtDZVJiIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9icm93c2VcL2ludmVzdG9ycyIsInJvdXRlIjoiYnJvd3NlLmludmVzdG9ycyJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1779896806),
('VDf9vBjpo9lT0ImTZPCo6vWYfO4mWG655MauaXvs', NULL, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiJjcXpUeUpHQkJuVklabjk2aWJsWFlNMDYwZWtSVko3WHF3VFdmOXBwIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1779897182),
('VpwyW73upWfJZbLAnVvvFwxuw5zs0bP7QL9jA46y', NULL, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiJSUkFqVDJ2UDhudXRycmFBN0UwS1BTeW5EaVk3MUlyR3NyYUVCenZyIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9icm93c2VcL2ludmVzdG9ycyIsInJvdXRlIjoiYnJvd3NlLmludmVzdG9ycyJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1779897183),
('w3dNLvh5XKkkH5on1D0cnUcaPRZ8XI39CM28cki3', 3, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiJMZGJndFBobGZJOXd6TG5MV0NaandsQjZrTGRuNDR1SnREYUJ1TW8yIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9teS1jb25uZWN0aW9ucyIsInJvdXRlIjoibXktY29ubmVjdGlvbnMifSwiX2ZsYXNoIjp7Im9sZCI6WyJzdWNjZXNzIl0sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6Mywic3VjY2VzcyI6IkludGVyZXN0IHJlcXVlc3QgYWNjZXB0ZWQuIn0=', 1779897353),
('WQvTi6hZrHcXJtfQ90VwKCEYMa3bAsz4g52OlfyM', NULL, '127.0.0.1', 'curl/8.18.0', 'eyJfdG9rZW4iOiIwcE9iQlM5VTYyVzdWb2dJQTdVYUxQelZxTjRhbll5NW50QmszOE1LIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9sZWdhbCIsInJvdXRlIjoibGVnYWwifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1779897184),
('X0dwdDT7wvI6xkXCLiiQ4I2bRmkWLYChdXdCueqS', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJ6cVJNWElVdHo0OU5WYkw4SnlsU0NpQXROa2VwSXBIOHgyclh2aW15IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1779897404),
('XQQknQ8HwXGHBejKZ4W5Pi05hs3D9gff4ogk0jA9', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJBa01qZ25xNlZWZzlEWTlUb0FlZnNhMDUwZ1FEaEh2dFFBSUJsT1dOIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdFwvaW52ZXN0LW1hdGNoLWxhcmF2ZWxcL3B1YmxpYyIsInJvdXRlIjoiaG9tZSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1779937605);

-- --------------------------------------------------------

--
-- Table structure for table `smart_suggestion_cache`
--

CREATE TABLE `smart_suggestion_cache` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `target_type` varchar(50) DEFAULT NULL,
  `target_id` bigint(20) UNSIGNED DEFAULT NULL,
  `match_score` decimal(5,2) DEFAULT NULL,
  `score_breakdown` longtext DEFAULT NULL CHECK (json_valid(`score_breakdown`)),
  `cached_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `states`
--

CREATE TABLE `states` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `country_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `states`
--

INSERT INTO `states` (`id`, `country_id`, `name`, `is_active`) VALUES
(1, 1, 'Koshi', 1),
(2, 1, 'Madhesh', 1),
(3, 1, 'Bagmati', 1),
(4, 1, 'Gandaki', 1),
(5, 1, 'Lumbini', 1),
(6, 1, 'Karnali', 1),
(7, 1, 'Sudurpashchim', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
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
  `notifications` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `role`, `account_type`, `phone`, `province`, `district`, `profile_photo`, `company_name`, `bio`, `linkedin_url`, `website_url`, `verification_status`, `verified_at`, `is_admin`, `is_suspended`, `daily_request_count`, `daily_request_date`, `email_verified_at`, `last_login_at`, `failed_login_attempts`, `locked_until`, `password`, `remember_token`, `created_at`, `updated_at`, `deleted_at`, `company_size`, `usage_goal`, `notifications`) VALUES
(1, 'Admin User', 'admin@investmatch.com', 'entrepreneur', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'verified', '2026-05-29 03:56:21', 1, 0, 0, NULL, '2026-05-29 04:34:10', '2026-06-15 07:07:29', 0, NULL, '$2y$12$DcXPLCbxBmPesCuEaLmUH.NIYjmNm3c89OM.7nceYRPaxdTBQlM5S', NULL, '2026-05-29 03:56:21', NULL, NULL, NULL, NULL, NULL),
(2, 'Ramesh Thapa', 'investor@nepal.com', 'investor', 'individual', '+977 9841 234567', 'Bagmati', 'Kathmandu', NULL, 'Thapa Capital', 'Angel investor focused on climate and agri-tech. Previously founded two Nepali SaaS companies.', NULL, NULL, 'verified', '2026-05-29 03:56:21', 0, 0, 0, NULL, '2026-05-29 04:34:10', '2026-06-15 07:07:20', 0, NULL, '$2y$12$DcXPLCbxBmPesCuEaLmUH.NIYjmNm3c89OM.7nceYRPaxdTBQlM5S', NULL, '2026-05-29 03:56:21', NULL, NULL, NULL, NULL, NULL),
(3, 'Anjali K.C.', 'anjali@aarohan.com', 'entrepreneur', 'company', '+977 9841 765432', 'Bagmati', 'Kathmandu', NULL, 'Aarohan Kitchens', 'Founder of Aarohan Kitchens - AI-powered cold storage for Nepali farmers.', NULL, NULL, 'verified', '2026-05-29 03:56:21', 0, 0, 0, NULL, '2026-05-29 04:34:10', '2026-06-02 01:13:31', 0, NULL, '$2y$12$hkoF2K0Tlde/m6OJXUCFauB3eFHiRK/SfxchHDcl5.wlvXsXOIPoy', NULL, '2026-05-29 03:56:21', '2026-06-01 15:20:42', NULL, NULL, NULL, NULL),
(4, 'Sunita Sharma', 'sunita@vc.com', 'investor', 'company', '+977 9841 345678', 'Gandaki', 'Pokhara', NULL, 'Himalayan Seed Fund', 'VC firm investing in AgriTech and CleanTech startups across Nepal.', NULL, NULL, 'verified', '2026-05-29 03:56:21', 0, 0, 0, NULL, '2026-05-29 04:34:10', NULL, 0, NULL, '$2y$12$DcXPLCbxBmPesCuEaLmUH.NIYjmNm3c89OM.7nceYRPaxdTBQlM5S', NULL, '2026-05-29 03:56:21', NULL, NULL, NULL, NULL, NULL),
(5, 'Bikash Rana', 'owner@nepal.com', 'business_owner', 'company', '+977 9841 556677', 'Bagmati', 'Kathmandu', NULL, 'Rana Retail Group', 'Second-generation retailer running a profitable supermarket chain in the Kathmandu Valley.', NULL, NULL, 'verified', '2026-05-29 22:15:00', 0, 0, 0, NULL, NULL, '2026-06-02 01:38:14', 0, NULL, '$2y$12$DcXPLCbxBmPesCuEaLmUH.NIYjmNm3c89OM.7nceYRPaxdTBQlM5S', NULL, '2026-05-29 22:15:00', NULL, NULL, NULL, NULL, NULL),
(6, 'Maya Gurung', 'franchise@nepal.com', 'franchisor', 'company', '+977 9846 112233', 'Gandaki', 'Pokhara', NULL, 'Himalaya Brews', 'Founder of a fast-growing specialty coffee brand expanding through franchising across Nepal.', NULL, NULL, 'verified', '2026-05-29 22:15:00', 0, 0, 0, NULL, NULL, '2026-06-02 01:38:15', 0, NULL, '$2y$12$DcXPLCbxBmPesCuEaLmUH.NIYjmNm3c89OM.7nceYRPaxdTBQlM5S', NULL, '2026-05-29 22:15:00', NULL, NULL, NULL, NULL, NULL),
(7, 'Prakash Joshi', 'advisor@nepal.com', 'advisor', 'company', '+977 9851 998877', 'Bagmati', 'Lalitpur', NULL, 'Joshi & Partners', 'Corporate lawyer and M&A advisor with two decades of cross-border transaction experience.', NULL, NULL, 'verified', '2026-05-29 22:15:00', 0, 0, 0, NULL, NULL, '2026-06-02 01:38:16', 0, NULL, '$2y$12$DcXPLCbxBmPesCuEaLmUH.NIYjmNm3c89OM.7nceYRPaxdTBQlM5S', NULL, '2026-05-29 22:15:00', NULL, NULL, NULL, NULL, NULL),
(19, 'R K Block Udhyog', 'rkblockudhyog@gmail.com', 'owner', 'company', NULL, NULL, NULL, NULL, 'R K Block Udhyog', NULL, NULL, NULL, 'verified', '2026-06-11 11:40:41', 0, 0, 0, NULL, NULL, '2026-06-15 04:55:54', 0, NULL, '$2y$10$IWrxMNb9NYCB5hNvZK/kRez0PefjbPa8/ut1yX.EtczrtZRiMcZZa', NULL, '2026-06-09 17:11:26', '2026-06-09 17:11:26', NULL, '1-10', 'sell', 'email'),
(20, 'Muscle Bank', 'isoftrosolutions@gmail.com', 'business_owner', 'individual', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'verified', '2026-06-11 11:40:39', 0, 0, 0, NULL, '2026-06-11 10:13:32', '2026-06-13 01:29:03', 0, NULL, '$2y$10$VUtJ1sK.Vdq1oSeE/mkVvuM2bJ0JPZrDTi/0HR6x4PSk/2WRp50d6', NULL, '2026-06-11 10:13:32', '2026-06-11 10:13:32', NULL, NULL, 'raise', NULL),
(21, 'Devbarat Prasad Patel', 'pdewbrath@gmail.com', 'investor', 'individual', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'verified', '2026-06-11 11:40:34', 0, 0, 0, NULL, '2026-06-11 10:56:48', '2026-06-11 11:13:08', 0, NULL, '$2y$10$XAnZ/R5QZs2YgOf.fIXWq.x5ClWSm/86AwYSlpF1YZ64Gbdzddb/.', NULL, '2026-06-11 10:56:48', '2026-06-11 10:56:48', NULL, NULL, 'invest', 'email'),
(22, 'Asaan Credit Ltd', 'asaancredit@gmail.com', 'investor', 'company', NULL, NULL, NULL, NULL, 'Asaan Capital', NULL, NULL, NULL, 'verified', '2026-06-12 12:01:32', 0, 0, 0, NULL, '2026-06-12 11:06:32', '2026-06-13 12:38:57', 0, NULL, '$2y$10$aFZSjugwS2N8elNeg5s6eu9MwBecGGzfhar3e.1M0I/iELqJdZ7qe', NULL, '2026-06-12 11:06:32', '2026-06-12 11:06:32', NULL, '11-50', 'buy', 'email');

-- --------------------------------------------------------

--
-- Table structure for table `verification_documents`
--

CREATE TABLE `verification_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `document_type` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_audit_log`
--
ALTER TABLE `admin_audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `advisors`
--
ALTER TABLE `advisors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `blog_posts_slug_unique` (`slug`);

--
-- Indexes for table `broadcasts`
--
ALTER TABLE `broadcasts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sent_by` (`sent_by`);

--
-- Indexes for table `businesses`
--
ALTER TABLE `businesses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `sector_id` (`sector_id`);

--
-- Indexes for table `business_assets`
--
ALTER TABLE `business_assets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `business_assets_business_id_foreign` (`business_id`);

--
-- Indexes for table `business_financials`
--
ALTER TABLE `business_financials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `business_financials_business_id_foreign` (`business_id`);

--
-- Indexes for table `business_inquiries`
--
ALTER TABLE `business_inquiries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `business_inquiries_business_id_foreign` (`business_id`),
  ADD KEY `business_inquiries_user_id_foreign` (`user_id`);

--
-- Indexes for table `business_media`
--
ALTER TABLE `business_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `business_media_business_id_foreign` (`business_id`);

--
-- Indexes for table `business_photos`
--
ALTER TABLE `business_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `business_id` (`business_id`);

--
-- Indexes for table `business_verifications`
--
ALTER TABLE `business_verifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `business_verifications_business_id_unique` (`business_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cities_state_id_foreign` (`state_id`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_log`
--
ALTER TABLE `email_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_recipient` (`recipient`),
  ADD KEY `idx_sent_at` (`sent_at`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `email_settings`
--
ALTER TABLE `email_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_template_key` (`template_key`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `franchises`
--
ALTER TABLE `franchises`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `sector_id` (`sector_id`);

--
-- Indexes for table `homepage_contents`
--
ALTER TABLE `homepage_contents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `homepage_contents_key_unique` (`key`);

--
-- Indexes for table `interest_requests`
--
ALTER TABLE `interest_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `interest_requests_sender_id_foreign` (`sender_id`),
  ADD KEY `interest_requests_receiver_id_foreign` (`receiver_id`),
  ADD KEY `interest_requests_pitch_id_foreign` (`pitch_id`),
  ADD KEY `business_id` (`business_id`);

--
-- Indexes for table `investor_profiles`
--
ALTER TABLE `investor_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `investor_profiles_user_id_foreign` (`user_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `interest_request_id` (`interest_request_id`),
  ADD KEY `user_a_id` (`user_a_id`),
  ADD KEY `user_b_id` (`user_b_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `nda_requests`
--
ALTER TABLE `nda_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nda_requests_business_id_foreign` (`business_id`),
  ADD KEY `nda_requests_investor_id_foreign` (`investor_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_user_id_foreign` (`user_id`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pages_slug_unique` (`slug`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email_type` (`email`,`type`);

--
-- Indexes for table `pitches`
--
ALTER TABLE `pitches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pitches_user_id_foreign` (`user_id`),
  ADD KEY `pitches_sector_id_foreign` (`sector_id`);

--
-- Indexes for table `pitch_media`
--
ALTER TABLE `pitch_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pitch_media_pitch_id_foreign` (`pitch_id`);

--
-- Indexes for table `pitch_team_members`
--
ALTER TABLE `pitch_team_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pitch_team_members_pitch_id_foreign` (`pitch_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reporter_id` (`reporter_id`),
  ADD KEY `resolved_by` (`resolved_by`);

--
-- Indexes for table `saved_listings`
--
ALTER TABLE `saved_listings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_save` (`user_id`,`listing_type`,`listing_id`);

--
-- Indexes for table `sectors`
--
ALTER TABLE `sectors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sectors_slug_unique` (`slug`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `smart_suggestion_cache`
--
ALTER TABLE `smart_suggestion_cache`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_until` (`user_id`,`cached_until`);

--
-- Indexes for table `states`
--
ALTER TABLE `states`
  ADD PRIMARY KEY (`id`),
  ADD KEY `states_country_id_foreign` (`country_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `verification_documents`
--
ALTER TABLE `verification_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `verification_documents_user_id_foreign` (`user_id`),
  ADD KEY `verification_documents_reviewed_by_foreign` (`reviewed_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_audit_log`
--
ALTER TABLE `admin_audit_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `advisors`
--
ALTER TABLE `advisors`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `broadcasts`
--
ALTER TABLE `broadcasts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `businesses`
--
ALTER TABLE `businesses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `business_assets`
--
ALTER TABLE `business_assets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `business_financials`
--
ALTER TABLE `business_financials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `business_inquiries`
--
ALTER TABLE `business_inquiries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `business_media`
--
ALTER TABLE `business_media`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `business_photos`
--
ALTER TABLE `business_photos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `business_verifications`
--
ALTER TABLE `business_verifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `email_log`
--
ALTER TABLE `email_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `email_settings`
--
ALTER TABLE `email_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `franchises`
--
ALTER TABLE `franchises`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `homepage_contents`
--
ALTER TABLE `homepage_contents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `interest_requests`
--
ALTER TABLE `interest_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `investor_profiles`
--
ALTER TABLE `investor_profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `matches`
--
ALTER TABLE `matches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `nda_requests`
--
ALTER TABLE `nda_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `pitches`
--
ALTER TABLE `pitches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pitch_media`
--
ALTER TABLE `pitch_media`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pitch_team_members`
--
ALTER TABLE `pitch_team_members`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `saved_listings`
--
ALTER TABLE `saved_listings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sectors`
--
ALTER TABLE `sectors`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `smart_suggestion_cache`
--
ALTER TABLE `smart_suggestion_cache`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `states`
--
ALTER TABLE `states`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `verification_documents`
--
ALTER TABLE `verification_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_audit_log`
--
ALTER TABLE `admin_audit_log`
  ADD CONSTRAINT `admin_audit_log_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `advisors`
--
ALTER TABLE `advisors`
  ADD CONSTRAINT `advisors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `broadcasts`
--
ALTER TABLE `broadcasts`
  ADD CONSTRAINT `broadcasts_ibfk_1` FOREIGN KEY (`sent_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `businesses`
--
ALTER TABLE `businesses`
  ADD CONSTRAINT `businesses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `businesses_ibfk_2` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `business_assets`
--
ALTER TABLE `business_assets`
  ADD CONSTRAINT `business_assets_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `business_financials`
--
ALTER TABLE `business_financials`
  ADD CONSTRAINT `business_financials_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `business_inquiries`
--
ALTER TABLE `business_inquiries`
  ADD CONSTRAINT `business_inquiries_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `business_inquiries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `business_media`
--
ALTER TABLE `business_media`
  ADD CONSTRAINT `business_media_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `business_photos`
--
ALTER TABLE `business_photos`
  ADD CONSTRAINT `business_photos_ibfk_1` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `business_verifications`
--
ALTER TABLE `business_verifications`
  ADD CONSTRAINT `business_verifications_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cities`
--
ALTER TABLE `cities`
  ADD CONSTRAINT `cities_state_id_foreign` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `franchises`
--
ALTER TABLE `franchises`
  ADD CONSTRAINT `franchises_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `franchises_ibfk_2` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `interest_requests`
--
ALTER TABLE `interest_requests`
  ADD CONSTRAINT `interest_requests_ibfk_1` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `interest_requests_pitch_id_foreign` FOREIGN KEY (`pitch_id`) REFERENCES `pitches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `interest_requests_receiver_id_foreign` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `interest_requests_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `investor_profiles`
--
ALTER TABLE `investor_profiles`
  ADD CONSTRAINT `investor_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `matches`
--
ALTER TABLE `matches`
  ADD CONSTRAINT `matches_ibfk_1` FOREIGN KEY (`interest_request_id`) REFERENCES `interest_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `matches_ibfk_2` FOREIGN KEY (`user_a_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `matches_ibfk_3` FOREIGN KEY (`user_b_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `nda_requests`
--
ALTER TABLE `nda_requests`
  ADD CONSTRAINT `nda_requests_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `nda_requests_investor_id_foreign` FOREIGN KEY (`investor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pitches`
--
ALTER TABLE `pitches`
  ADD CONSTRAINT `pitches_sector_id_foreign` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pitches_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pitch_media`
--
ALTER TABLE `pitch_media`
  ADD CONSTRAINT `pitch_media_pitch_id_foreign` FOREIGN KEY (`pitch_id`) REFERENCES `pitches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pitch_team_members`
--
ALTER TABLE `pitch_team_members`
  ADD CONSTRAINT `pitch_team_members_pitch_id_foreign` FOREIGN KEY (`pitch_id`) REFERENCES `pitches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `saved_listings`
--
ALTER TABLE `saved_listings`
  ADD CONSTRAINT `saved_listings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `smart_suggestion_cache`
--
ALTER TABLE `smart_suggestion_cache`
  ADD CONSTRAINT `smart_suggestion_cache_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `states`
--
ALTER TABLE `states`
  ADD CONSTRAINT `states_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `verification_documents`
--
ALTER TABLE `verification_documents`
  ADD CONSTRAINT `verification_documents_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `verification_documents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
