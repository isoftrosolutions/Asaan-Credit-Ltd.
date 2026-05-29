-- ============================================================
-- Asaan Marketplace — Complete Database Setup
-- Run: mysql -u USER -p DATABASE < database/final.sql
-- Demo: admin@investmatch.com / Demo@2026
-- ============================================================

-- MySQL dump 10.13  Distrib 8.4.6, for Win64 (x86_64)
-- Host: localhost    Database: invest_match
-- ------------------------------------------------------
-- Server version	12.0.2-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin_audit_log`
--

DROP TABLE IF EXISTS `admin_audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_audit_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` bigint(20) unsigned NOT NULL,
  `action` varchar(100) NOT NULL,
  `target_type` varchar(50) DEFAULT NULL,
  `target_id` bigint(20) unsigned DEFAULT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `admin_audit_log_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_audit_log`
--

LOCK TABLES `admin_audit_log` WRITE;
/*!40000 ALTER TABLE `admin_audit_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `advisors`
--

DROP TABLE IF EXISTS `advisors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `advisors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `firm_name` varchar(255) DEFAULT NULL,
  `specialties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`specialties`)),
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `advisors`
--

LOCK TABLES `advisors` WRITE;
/*!40000 ALTER TABLE `advisors` DISABLE KEYS */;
INSERT INTO `advisors` VALUES (1,2,'Thapa Advisory Services','[\"m_and_a\",\"brokerage\",\"due_diligence\"]',15,42,850000000.00,'CA, CFA Level 3',NULL,'success_fee',50000.00,500000.00,'15+ years in M&A advisory. Have successfully closed 42 deals across various sectors in Nepal.',1,0,9.1,'2026-05-29 04:34:33','2026-05-29 04:34:33');
/*!40000 ALTER TABLE `advisors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `broadcasts`
--

DROP TABLE IF EXISTS `broadcasts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `broadcasts`
--

LOCK TABLES `broadcasts` WRITE;
/*!40000 ALTER TABLE `broadcasts` DISABLE KEYS */;
/*!40000 ALTER TABLE `broadcasts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `business_photos`
--

DROP TABLE IF EXISTS `business_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `business_photos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` bigint(20) unsigned NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `business_id` (`business_id`),
  CONSTRAINT `business_photos_ibfk_1` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `business_photos`
--

LOCK TABLES `business_photos` WRITE;
/*!40000 ALTER TABLE `business_photos` DISABLE KEYS */;
/*!40000 ALTER TABLE `business_photos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `businesses`
--

DROP TABLE IF EXISTS `businesses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `businesses`
--

LOCK TABLES `businesses` WRITE;
/*!40000 ALTER TABLE `businesses` DISABLE KEYS */;
INSERT INTO `businesses` VALUES (1,3,'Enterprise Software Co.','sale',4,'Bagmati','Kathmandu',2018,45,120000000.00,18.00,120000000.00,NULL,NULL,NULL,'Cloud B2B SaaS platform serving 200+ clients across 12 countries. Strong recurring revenue with 92% retention rate.','Founder pursuing new venture in EdTech space',NULL,1,0,1,1420,9.3,'2026-05-29 04:34:11','2026-05-29 04:34:11'),(2,3,'Manufacturing Unit Expansion','partial_stake',8,'Bagmati','Kathmandu',2015,120,80000000.00,12.00,60000000.00,NULL,NULL,NULL,'Food processing unit with modern equipment. 30% YoY growth. Looking for strategic partner for expansion.','Seeking capital for new product line',NULL,1,0,0,890,8.1,'2026-05-29 04:34:11','2026-05-29 04:34:11'),(3,3,'Retail Pharmacy Chain','sale',9,'Bagmati','Lalitpur',2010,30,50000000.00,15.00,50000000.00,NULL,NULL,NULL,'Chain of 5 retail pharmacy stores in Kathmandu Valley. Established brand with loyal customer base.','Owner relocating abroad',NULL,1,0,0,670,7.5,'2026-05-29 04:34:11','2026-05-29 04:34:11'),(4,4,'Hotel Equity Stake','partial_stake',11,'Gandaki','Pokhara',2012,55,35000000.00,22.00,30000000.00,NULL,NULL,NULL,'Boutique hotel in Pokhara with 20 rooms. Strong tourism revenue. Offering 40% equity stake.',NULL,NULL,1,0,0,540,8.6,'2026-05-29 04:34:11','2026-05-29 04:34:11'),(5,4,'Tech Startup Portfolio','sale',4,'Bagmati','Kathmandu',2020,8,15000000.00,25.00,25000000.00,NULL,NULL,NULL,'Portfolio of 3 bootstrapped SaaS products with 5,000+ paying users across SEA.',NULL,NULL,1,0,0,310,7.8,'2026-05-29 04:34:11','2026-05-29 04:34:11');
/*!40000 ALTER TABLE `businesses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `faqs`
--

DROP TABLE IF EXISTS `faqs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `faqs`
--

LOCK TABLES `faqs` WRITE;
/*!40000 ALTER TABLE `faqs` DISABLE KEYS */;
INSERT INTO `faqs` VALUES (1,'How does the platform ensure profiles are genuine?','Every profile is manually reviewed by our analysts. We verify email, phone, and social accounts. Businesses also undergo document verification (GST, registration certificate, financials).',1,1,NULL,NULL),(2,'When do contact details get shared?','Contact info is revealed only when both parties express mutual interest. This prevents unsolicited outreach and protects your identity until you are ready to connect.',2,1,NULL,NULL),(3,'What types of transactions are supported?','Full business sale, partial stake sale, investment, business loan, asset sale, franchise, and distributorship opportunities across all industries and regions.',3,1,NULL,NULL),(4,'Is there a fee to use InvestMatch?','Basic registration is free. Premium plans start at NPR 25,500. A 1% finders fee applies post deal closure on paid plans. No hidden charges.',4,1,NULL,NULL),(5,'How long does verification take?','Verification typically takes 24-48 hours after you upload your documents. Our admin team reviews each submission manually.',5,1,NULL,NULL);
/*!40000 ALTER TABLE `faqs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `franchises`
--

DROP TABLE IF EXISTS `franchises`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `franchises`
--

LOCK TABLES `franchises` WRITE;
/*!40000 ALTER TABLE `franchises` DISABLE KEYS */;
INSERT INTO `franchises` VALUES (1,2,'Nepal Bites Express',9,2018,12,'Nepal','Fast-casual Nepali restaurant chain. Serving authentic momo, chowmein, and thalis in modern format.','Experienced restaurateur with passion for Nepali cuisine. Minimum 2 years F&B experience.',500000.00,5.00,2.00,2500000.00,5000000.00,18,1,1,NULL,1,0,1,0,8.9,'2026-05-29 04:34:33','2026-05-29 04:34:33');
/*!40000 ALTER TABLE `franchises` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `homepage_contents`
--

DROP TABLE IF EXISTS `homepage_contents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `homepage_contents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `homepage_contents_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `homepage_contents`
--

LOCK TABLES `homepage_contents` WRITE;
/*!40000 ALTER TABLE `homepage_contents` DISABLE KEYS */;
INSERT INTO `homepage_contents` VALUES (1,'hero_title','Connect with Investors. Sell or Grow Your Business Faster.',NULL,NULL),(2,'hero_subtitle','The premium marketplace where verified business owners meet qualified investors, buyers, and franchise partners. Close deals with confidence.',NULL,NULL),(3,'stats_businesses','67,500+',NULL,NULL),(4,'stats_investors','44,000+',NULL,NULL),(5,'stats_matches','12,800+',NULL,NULL),(6,'stats_deal_value','NPR 850 Cr+',NULL,NULL);
/*!40000 ALTER TABLE `homepage_contents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `interest_requests`
--

DROP TABLE IF EXISTS `interest_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `interest_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sender_id` bigint(20) unsigned NOT NULL,
  `receiver_id` bigint(20) unsigned NOT NULL,
  `pitch_id` bigint(20) unsigned DEFAULT NULL,
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
  CONSTRAINT `interest_requests_pitch_id_foreign` FOREIGN KEY (`pitch_id`) REFERENCES `pitches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `interest_requests_receiver_id_foreign` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `interest_requests_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `interest_requests`
--

LOCK TABLES `interest_requests` WRITE;
/*!40000 ALTER TABLE `interest_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `interest_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `investor_profiles`
--

DROP TABLE IF EXISTS `investor_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `investor_profiles`
--

LOCK TABLES `investor_profiles` WRITE;
/*!40000 ALTER TABLE `investor_profiles` DISABLE KEYS */;
INSERT INTO `investor_profiles` VALUES (1,2,6,'Nepal Solar Pvt Ltd, Green Agri Ventures, HealthTech Nepal, EduInnovate',50000000.00,'[\"AgriTech\",\"CleanTech\",\"HealthTech\"]','[\"Early Revenue\",\"Growth\"]',1500000.00,20000000.00,'[\"Bagmati\",\"Gandaki\"]','Mr. Rajesh Hamal - +977 9851 234567',NULL,NULL),(2,4,9,'EcoEnergy, SmartFarm Nepal, CleanWater Tech, WasteToValue, BioAgri',200000000.00,'[\"AgriTech\",\"CleanTech\",\"Deep Tech\"]','[\"MVP\",\"Early Revenue\",\"Growth\"]',4000000.00,15000000.00,'[\"Bagmati\",\"Gandaki\",\"Province 1\"]','Dr. Sagar Acharya - +977 9851 876543',NULL,NULL);
/*!40000 ALTER TABLE `investor_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `matches`
--

DROP TABLE IF EXISTS `matches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `matches`
--

LOCK TABLES `matches` WRITE;
/*!40000 ALTER TABLE `matches` DISABLE KEYS */;
/*!40000 ALTER TABLE `matches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_05_27_000001_add_role_and_profile_fields_to_users',1),(5,'2026_05_27_000002_create_sectors_table',1),(6,'2026_05_27_000003_create_investor_profiles_table',1),(7,'2026_05_27_000004_create_pitches_table',1),(8,'2026_05_27_000005_create_pitch_media_table',1),(9,'2026_05_27_000006_create_pitch_team_members_table',1),(10,'2026_05_27_000007_create_verification_documents_table',1),(11,'2026_05_27_000008_create_interest_requests_table',1),(12,'2026_05_27_000009_create_notifications_table',1),(13,'2026_05_27_000010_create_faqs_table',1),(14,'2026_05_27_000011_create_homepage_contents_table',1),(15,'2026_05_27_000012_add_detailed_pitch_fields',1),(16,'2026_05_27_000013_add_pitch_extras_for_v1_5',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,1,'new_user','New User Registered','Bikash Rana registered as a business owner','/admin/verification',0,'2026-05-29 04:34:33',NULL),(2,2,'match','Match Made!','You have been matched with Enterprise Software Co.','/connections',0,'2026-05-29 04:34:33',NULL),(3,3,'interest','New Interest Request','Ramesh Thapa has expressed interest in your business','/connections',0,'2026-05-29 04:34:33',NULL);
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'password',
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_email_type` (`email`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pitch_media`
--

DROP TABLE IF EXISTS `pitch_media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pitch_media`
--

LOCK TABLES `pitch_media` WRITE;
/*!40000 ALTER TABLE `pitch_media` DISABLE KEYS */;
/*!40000 ALTER TABLE `pitch_media` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pitch_team_members`
--

DROP TABLE IF EXISTS `pitch_team_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pitch_team_members`
--

LOCK TABLES `pitch_team_members` WRITE;
/*!40000 ALTER TABLE `pitch_team_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `pitch_team_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pitches`
--

DROP TABLE IF EXISTS `pitches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pitches_user_id_foreign` (`user_id`),
  KEY `pitches_sector_id_foreign` (`sector_id`),
  CONSTRAINT `pitches_sector_id_foreign` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pitches_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pitches`
--

LOCK TABLES `pitches` WRITE;
/*!40000 ALTER TABLE `pitches` DISABLE KEYS */;
INSERT INTO `pitches` VALUES (1,3,'AI-powered cold storage reducing post-harvest losses for 2,400+ farmers across Nepal.',NULL,NULL,NULL,NULL,'34% of Nepal perishable produce is lost before reaching market due to lack of reliable cold storage. Small farmers lose NPR 18,000-40,000 per season.','Low-cost, solar-hybrid smart cold rooms with IoT monitoring and AI demand forecasting. Farmers pay per use via mobile.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2,400 farmers onboarded (Q1 2026)\nNPR 9.2M revenue run-rate\n3 provinces live, 2 more in pipeline\nPartnership with Nepal Agricultural Research Council',NULL,NULL,NULL,28000000.00,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,12.00,NULL,NULL,NULL,NULL,NULL,'Early Revenue',1,1,0,0,0,0,NULL,NULL),(2,3,'Making quality education accessible in rural areas through AI-powered learning platforms',NULL,NULL,'EdTech for Rural Nepal - AI-powered learning platform',NULL,'Rural Nepal lacks access to quality education. 70% of students in rural areas have no access to digital learning.','AI-powered mobile learning platform that works offline. Adaptive curriculum in Nepali language.','NPR 500 Cr TAM in Nepal alone',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,5000000.00,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,15.00,NULL,33333333.00,NULL,NULL,NULL,'seed',4,1,0,1,0,1,'2026-05-29 04:34:33','2026-05-29 04:34:33');
/*!40000 ALTER TABLE `pitches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reports`
--

LOCK TABLES `reports` WRITE;
/*!40000 ALTER TABLE `reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `saved_listings`
--

DROP TABLE IF EXISTS `saved_listings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `saved_listings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `listing_type` varchar(50) NOT NULL,
  `listing_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_save` (`user_id`,`listing_type`,`listing_id`),
  CONSTRAINT `saved_listings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `saved_listings`
--

LOCK TABLES `saved_listings` WRITE;
/*!40000 ALTER TABLE `saved_listings` DISABLE KEYS */;
/*!40000 ALTER TABLE `saved_listings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sectors`
--

DROP TABLE IF EXISTS `sectors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sectors`
--

LOCK TABLES `sectors` WRITE;
/*!40000 ALTER TABLE `sectors` DISABLE KEYS */;
INSERT INTO `sectors` VALUES (1,'AgriTech','agritech',1,NULL,NULL),(2,'CleanTech','cleantech',1,NULL,NULL),(3,'HealthTech','healthtech',1,NULL,NULL),(4,'FinTech','fintech',1,NULL,NULL),(5,'EdTech','edtech',1,NULL,NULL),(6,'Logistics','logistics',1,NULL,NULL),(7,'Manufacturing','manufacturing',1,NULL,NULL),(8,'Retail','retail',1,NULL,NULL),(9,'Hospitality','hospitality',1,NULL,NULL),(10,'RealEstate','realestate',1,NULL,NULL),(11,'Technology','technology',1,NULL,NULL),(12,'Food & Beverage','food-beverage',1,NULL,NULL),(13,'E-commerce','ecommerce',1,NULL,NULL),(14,'Construction','construction',1,NULL,NULL),(15,'Education','education',1,NULL,NULL);
/*!40000 ALTER TABLE `sectors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('6q6OtoyEl1P5jrlat4m5S1iO2BisUJACAi07ck4z',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiJNMzljVklKWVZ1dldOQjYxVFVVUkxPSDVwT0w5NmoxU0JwZ0xVN2ZCIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1Iiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1779896805),('72L48Gpk2BdsEVLcxRj4Z9zCkSOxI0mj4mUYXWIU',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiJrVHM2VnFXemtRa1BwNlpKWmluQkhjSWlGRVRWYXFjYTY0bmhqSlhaIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9ob3ctaXQtd29ya3MiLCJyb3V0ZSI6Imhvdy1pdC13b3JrcyJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1779896830),('8aYu6R7JKl78dIhF6yk7n24VMEOIWTAUiMFbt8Gv',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiJHbGYzYTJTSUpmZ0ZzVVA2UWhJZ1dIYUFkaEJ0OW14dmdlWWtvTDlwIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9sZWdhbCIsInJvdXRlIjoibGVnYWwifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1779896830),('8RFFUWqR0wRoLjjJmqMnhT55TxV3ZE9473F1oYRy',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiJrU3VhWDFzUEE4bEdLZmE2V0tlT0dWZFlxZ3p4Um9pV29pcWMyTEhpIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9zdXBwb3J0Iiwicm91dGUiOiJzdXBwb3J0In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1779896831),('a3tQoMitJILr1E87fUiJdPgvN7mwnyAv1Aeg2e91',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiJZdmo4S3BVOVRTV01LT3BXREJiNVFSV0tJY1JYOWZGSVdQckJEQjhCIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9waXRjaGVzXC8xIiwicm91dGUiOiJwaXRjaC5zaG93In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1779897183),('ANO0mKUJRsK4lLtTOChQq4bbKynDq898AWuAdZzO',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiJYaVpSNlJveW1ZTmpYS2t2Zjg2eXp2RFdubzRWQXBRUmJjdmhyWWxnIiwidXJsIjp7ImludGVuZGVkIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjg3NjVcL2Rhc2hib2FyZCJ9LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjg3NjVcL2Rhc2hib2FyZCIsInJvdXRlIjoiZGFzaGJvYXJkIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1779896831),('Az2InZOMwc1o8CIC0yL4enajGdytWmawBTM7fFsb',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiJrbXJCaVM4YlZoU09LZHR5Y1EyQlJ1SjdTSWJVWkZXYURiWGpmbHd2IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1779896805),('DlHozGaDuzXCE6YRZzPpea6cJwgOhu3cV5Kr9mNB',2,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiIyM29kTDRvdU9KSWhUeDI5dWZUenZTTEw3aDhNalgxSjZPZklWUGd0IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9waXRjaGVzXC8xIiwicm91dGUiOiJwaXRjaC5zaG93In0sIl9mbGFzaCI6eyJvbGQiOlsic3VjY2VzcyJdLCJuZXciOltdfSwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjIsInN1Y2Nlc3MiOiJJbnRlcmVzdCByZXF1ZXN0IHNlbnQgc3VjY2Vzc2Z1bGx5LiJ9',1779897315),('ecRtfVjOWr8LQlOvzqCnsHOxvKhDEIuMvFcBZqz1',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiJNdEk0QkQ0VVpLc2ZEOWhiemd6cjcxVmZMUmh0MU9oNkxGNWpINWFnIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9mYXEiLCJyb3V0ZSI6ImZhcSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1779897184),('EMJUSmZMQWG144RYffRlLQNNtJwpzaPbZ27g9a3Y',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiJ0M0FBVHp0eWxoTmF1a3dyMnVya0pOVVhuVTBsVGE3SkNrTFE1TW5nIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1Iiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1779897182),('eNFnDwIGxWFZG4ZPG01w5YmBXKIVyDaqRnPpl9ja',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiJWWVk1c0JydWdjZGVvTW5GVUk1aE9td09Ja3pRVjZvZ0FYbjZZZzB6IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9pbnZlc3RvcnNcLzIiLCJyb3V0ZSI6ImludmVzdG9yLnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1779896830),('esGPA7UkON6LqgSbxPJ0Pol1HHSc7rQlsBwIpBcQ',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiJMdDU4Mll3ZTBVTjZMUm00bTg2S0oxVnRPY0VEdnN6d1FsU00zVnY1IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1Iiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1779897354),('fNiyC7GowNVE2ddTgbmIIUm2DitdMxAFdrVo3agJ',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiJnd1oxV1ZEVHZlcXo1aGdraXBxQWxGaDhFSnRhaWJ0Nk1wTWRmdkREIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9hYm91dCIsInJvdXRlIjoiYWJvdXQifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1779896830),('hpiDB9U3ynwbvyePeX8NTLvOxGIYy2WIxSXZPIgB',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiI4ZFZhZG1xSXNCV2NkWjlzeHN6cFN5SDJFdzByT3JrTWhhbWNMM0t3IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9hYm91dCIsInJvdXRlIjoiYWJvdXQifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1779897184),('J4ymTUcUqYaxccL2UBccFOMRFDuLlAyitF1GY6AH',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiJJUEJYRjRpU0EyUlI3R1pHY251Q0xtaGhGZUZoczNnUjhmcWpmc0NmIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1Iiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1779897364),('jQu0mRx4qeT67eJfIZVagcSg3907zNyEqY8I6TS4',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiI2WGx2eGtKY0h0WWM5ZHVtV2hmcEl6bVlPNnZVeHNoRWFmQXp4SjRNIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9waXRjaGVzXC8xIiwicm91dGUiOiJwaXRjaC5zaG93In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1779896830),('JUwxdZc56gJXK1Ji8eLFmCnmbSHgkcsigwm30wWb',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiJkYXdQWjRCU3pWRDh4RGNhYURDYnFRNjdVRmU1SDR4ZFliTEhMSm5kIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9mYXEiLCJyb3V0ZSI6ImZhcSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1779896830),('mJC4Q95hsgmlHjpfUCtLiXojxyE56Jv5BlnPKZyV',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiJvenlFaHo2TkJyRURQa09jM28yc0UyOGM4ME93Um5BMFlYNXl4eHJUIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9waXRjaGVzXC8xIiwicm91dGUiOiJwaXRjaC5zaG93In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1779896898),('mubIBCIaCzPM0rsiHgyhBtzTHrPkMx1Q4dhcOwdy',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiJhVlVMWUFORDI4UG1VWEZvQXZkT0pnUkd5SzZRbDFGZUk4b1NwVnM4IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9yZWdpc3RlciIsInJvdXRlIjoicmVnaXN0ZXIifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1779897182),('np8vmiLSeDtpuh3Qfio8RRBywmX0fEg82E3pJcMP',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiJmRmRkdHF3cDgyWG1RWVBOMDdGd2ZXVmlMbXBhSklhc0c1OFdycDB2IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9icm93c2VcL2VudHJlcHJlbmV1cnMiLCJyb3V0ZSI6ImJyb3dzZS5lbnRyZXByZW5ldXJzIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1779897183),('nqGVAewKZchJctAkHpqTc8LtiIqad4v9Cn2vReSm',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiJzTlI0SWlGUVRPRHVxYlhRQ0pKcTZkajJnU09pdGFXVU1qWGw0clp2IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9waXRjaGVzXC8xIiwicm91dGUiOiJwaXRjaC5zaG93In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1779896873),('p0Pzv0nNWtmhh79D2hGhwi3ZOLVWg4dswElFWNps',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiJtZzdLZUtLNUYwODU2S25RSzdBTVc0a2gwMVpYNzV0c2hOZEk3aWtoIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9yZWdpc3RlciIsInJvdXRlIjoicmVnaXN0ZXIifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1779896805),('rEK74FKOoNuE4O0LkgJmCpFnOxjSg8py91C3ukL5',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiJ3MlFSMDkxOTQ3YjNUbURQcUdlWW5xVDZaMTg5MXJ5WEFHalE2WkhrIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9zdXBwb3J0Iiwicm91dGUiOiJzdXBwb3J0In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1779897184),('RTWCas5tc5U0vDkohoz9pWqlopNbKahAakTtRvO3',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiJMQ205WXFXMkxQVFlBTU5pTFhqdzVPcWNldWd2alVQMzZkZEo1TmtCIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1Iiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1779896790),('s9ICfugWMW1rdRpZqLOhUB5rOg4zcOkJ3Hnmhk98',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiJ5d3FDaUhaMG5tTkRJWlY1WmJqZ3BucExJYUJsNWZwQ0xKMUZSb1l5IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1Iiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1779897369),('sWUqOLzh5fW4jiRgEccUOY4baWm0cnub1v3En3B7',1,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiJOSmlXbEVER29raVhyWHIzVWl0WDlBWUVYOWRBNEppOWpRVlRGM2ttIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9hZG1pblwvYnJvYWRjYXN0Iiwicm91dGUiOiJhZG1pbi5icm9hZGNhc3QifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MX0=',1779897185),('T2cRDu11oRPaUDFz32hdAj9nN1NmygD4Z1i7zbAJ',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiJuZE5vcW1yOTNhSGZjMHUxOEg1MVAwNG4zUXRickFieGFDN3liSTk0IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9pbnZlc3RvcnNcLzIiLCJyb3V0ZSI6ImludmVzdG9yLnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1779897183),('t5tzBB857SwLt4aR8nhcOEx2kehtaLjI13G7Kwcz',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiJlNDVmWVlYa20xamdJNGhJQ0FUMlFmbTI0a0RZTnF6ZmdWejdEeDhNIiwidXJsIjp7ImludGVuZGVkIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjg3NjVcL2FkbWluIn0sIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODc2NVwvYWRtaW4iLCJyb3V0ZSI6ImFkbWluLmRhc2hib2FyZCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1779896831),('uENvLWZEcpf9eHAkkRq8y9HSevkkyZ74bswAg27w',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiJIZ1FnbDV2TWN0aldZR3R1NE9uUFpDTzhQR0tVSXFhenhGY2JBYXdnIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9ob3ctaXQtd29ya3MiLCJyb3V0ZSI6Imhvdy1pdC13b3JrcyJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1779897184),('uvaoXu04mDvE8JJG4hGvAIPcKCONbQcgnY7wvpH4',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiJnemd6VDVndzBSQTJUUTRpMXZDTzJzOE9VODZBUFdDako1M001SEhGIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9icm93c2VcL2VudHJlcHJlbmV1cnMiLCJyb3V0ZSI6ImJyb3dzZS5lbnRyZXByZW5ldXJzIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1779896806),('Vc6NA4csKqrdUyK50JBL2jYGEdtZUGBZgUWfaiaB',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiJmQ0UxUEpoOVZGRk84MWVWcFFVbFpMdW1IbmtwR0pOTHhIZEtDZVJiIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9icm93c2VcL2ludmVzdG9ycyIsInJvdXRlIjoiYnJvd3NlLmludmVzdG9ycyJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1779896806),('VDf9vBjpo9lT0ImTZPCo6vWYfO4mWG655MauaXvs',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiJjcXpUeUpHQkJuVklabjk2aWJsWFlNMDYwZWtSVko3WHF3VFdmOXBwIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1779897182),('VpwyW73upWfJZbLAnVvvFwxuw5zs0bP7QL9jA46y',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiJSUkFqVDJ2UDhudXRycmFBN0UwS1BTeW5EaVk3MUlyR3NyYUVCenZyIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9icm93c2VcL2ludmVzdG9ycyIsInJvdXRlIjoiYnJvd3NlLmludmVzdG9ycyJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1779897183),('w3dNLvh5XKkkH5on1D0cnUcaPRZ8XI39CM28cki3',3,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiJMZGJndFBobGZJOXd6TG5MV0NaandsQjZrTGRuNDR1SnREYUJ1TW8yIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9teS1jb25uZWN0aW9ucyIsInJvdXRlIjoibXktY29ubmVjdGlvbnMifSwiX2ZsYXNoIjp7Im9sZCI6WyJzdWNjZXNzIl0sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6Mywic3VjY2VzcyI6IkludGVyZXN0IHJlcXVlc3QgYWNjZXB0ZWQuIn0=',1779897353),('WQvTi6hZrHcXJtfQ90VwKCEYMa3bAsz4g52OlfyM',NULL,'127.0.0.1','curl/8.18.0','eyJfdG9rZW4iOiIwcE9iQlM5VTYyVzdWb2dJQTdVYUxQelZxTjRhbll5NW50QmszOE1LIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9sZWdhbCIsInJvdXRlIjoibGVnYWwifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1779897184),('X0dwdDT7wvI6xkXCLiiQ4I2bRmkWLYChdXdCueqS',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJ6cVJNWElVdHo0OU5WYkw4SnlsU0NpQXROa2VwSXBIOHgyclh2aW15IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4NzY1XC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1779897404),('XQQknQ8HwXGHBejKZ4W5Pi05hs3D9gff4ogk0jA9',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJBa01qZ25xNlZWZzlEWTlUb0FlZnNhMDUwZ1FEaEh2dFFBSUJsT1dOIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdFwvaW52ZXN0LW1hdGNoLWxhcmF2ZWxcL3B1YmxpYyIsInJvdXRlIjoiaG9tZSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1779937605);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smart_suggestion_cache`
--

DROP TABLE IF EXISTS `smart_suggestion_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `smart_suggestion_cache` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `target_type` varchar(50) DEFAULT NULL,
  `target_id` bigint(20) unsigned DEFAULT NULL,
  `match_score` decimal(5,2) DEFAULT NULL,
  `score_breakdown` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`score_breakdown`)),
  `cached_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_until` (`user_id`,`cached_until`),
  CONSTRAINT `smart_suggestion_cache_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smart_suggestion_cache`
--

LOCK TABLES `smart_suggestion_cache` WRITE;
/*!40000 ALTER TABLE `smart_suggestion_cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `smart_suggestion_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin User','admin@investmatch.com','entrepreneur',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'verified','2026-05-29 03:56:21',1,0,0,NULL,'2026-05-29 04:34:10',NULL,0,NULL,'$2y$12$LJ3m4ys3Lk0TSwHnfcjRfeR6RbRgR5fK5fUb5v5fK5fUb5v5fK5fU',NULL,'2026-05-29 03:56:21',NULL,NULL),(2,'Ramesh Thapa','investor@nepal.com','investor','individual','+977 9841 234567','Bagmati','Kathmandu',NULL,'Thapa Capital','Angel investor focused on climate and agri-tech. Previously founded two Nepali SaaS companies.',NULL,NULL,'verified','2026-05-29 03:56:21',0,0,0,NULL,'2026-05-29 04:34:10','2026-05-29 04:40:24',0,NULL,'$2y$12$.6FuELd3UVwJnsESYU9aB.CiC11i9zEgUoVQFKgwWhwkAWJoD9e7C',NULL,'2026-05-29 03:56:21',NULL,NULL),(3,'Anjali K.C.','anjali@aarohan.com','entrepreneur','company','+977 9841 765432','Bagmati','Kathmandu',NULL,'Aarohan Kitchens','Founder of Aarohan Kitchens - AI-powered cold storage for Nepali farmers.',NULL,NULL,'verified','2026-05-29 03:56:21',0,0,0,NULL,'2026-05-29 04:34:10',NULL,0,NULL,'$2y$12$LJ3m4ys3Lk0TSwHnfcjRfeR6RbRgR5fK5fUb5v5fK5fUb5v5fK5fU',NULL,'2026-05-29 03:56:21',NULL,NULL),(4,'Sunita Sharma','sunita@vc.com','investor','company','+977 9841 345678','Gandaki','Pokhara',NULL,'Himalayan Seed Fund','VC firm investing in AgriTech and CleanTech startups across Nepal.',NULL,NULL,'verified','2026-05-29 03:56:21',0,0,0,NULL,'2026-05-29 04:34:10',NULL,0,NULL,'$2y$12$LJ3m4ys3Lk0TSwHnfcjRfeR6RbRgR5fK5fUb5v5fK5fUb5v5fK5fU',NULL,'2026-05-29 03:56:21',NULL,NULL),(6,'Jane Doe','e2e_4723@test.com','investor','individual','+977 9841123456',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'verified',NULL,0,0,0,NULL,'2026-05-29 04:39:43','2026-05-29 04:39:44',0,NULL,'$2y$12$o5rLAQETrcqw7hYKqh5sKu9W.fIbHESjiE5ropR8ark25C/WYtmYO',NULL,'2026-05-28 22:54:39','2026-05-28 22:54:39',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `verification_documents`
--

DROP TABLE IF EXISTS `verification_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `verification_documents`
--

LOCK TABLES `verification_documents` WRITE;
/*!40000 ALTER TABLE `verification_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `verification_documents` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-29 10:45:36

-- ============================================================
-- Demo user passwords (bcrypt hash of "Demo@2026")
-- ============================================================
UPDATE users SET password = '\\\.aWoTkJdnblXWH.40r.TMnkc517xBgqejDh6X66UtpSUvm' WHERE email IN ('admin@investmatch.com','investor@nepal.com','anjali@aarohan.com','sunita@vc.com');
UPDATE users SET ole = 'entrepreneur' WHERE id = 3;
UPDATE users SET erification_status = 'verified', email_verified_at = NOW() WHERE email IN ('admin@investmatch.com','investor@nepal.com','anjali@aarohan.com','sunita@vc.com');
