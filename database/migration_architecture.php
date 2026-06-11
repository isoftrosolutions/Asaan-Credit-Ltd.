<?php
require __DIR__ . '/../config/bootstrap.php';

$db = db();
echo "=== Architecture Migration ===\n\n";

// ── 1. Add columns to businesses ──────────────────────────────────
$businessCols = [
    "ADD COLUMN `slug` VARCHAR(255) DEFAULT NULL AFTER `business_name`",
    "ADD COLUMN `legal_entity_type` VARCHAR(100) DEFAULT NULL AFTER `employee_count`",
    "ADD COLUMN `monthly_revenue` DECIMAL(15,2) DEFAULT NULL AFTER `annual_revenue`",
    "ADD COLUMN `funding_required` DECIMAL(15,2) DEFAULT NULL AFTER `asking_price`",
    "ADD COLUMN `valuation` DECIMAL(15,2) DEFAULT NULL AFTER `funding_required`",
    "ADD COLUMN `status` ENUM('draft','pending','approved','rejected','sold') NOT NULL DEFAULT 'approved' AFTER `is_hidden`",
    "ADD COLUMN `overview` LONGTEXT DEFAULT NULL AFTER `description`",
    "ADD COLUMN `products_services` LONGTEXT DEFAULT NULL AFTER `overview`",
    "ADD COLUMN `facilities` LONGTEXT DEFAULT NULL AFTER `assets_included`",
    "ADD COLUMN `capitalization` LONGTEXT DEFAULT NULL AFTER `facilities`",
];

foreach ($businessCols as $col) {
    try {
        $db->exec("ALTER TABLE businesses $col");
        echo "OK: businesses $col\n";
    } catch (PDOException $e) {
        if (str_contains($e->getMessage(), 'Duplicate column')) {
            echo "SKIP (exists): businesses $col\n";
        } else {
            echo "ERR: " . $e->getMessage() . "\n";
        }
    }
}

// Generate slugs for existing businesses that don't have one
$stmt = $db->query("SELECT id, business_name FROM businesses WHERE slug IS NULL OR slug = ''");
while ($row = $stmt->fetch()) {
    $slug = generate_slug($row['business_name']) . '-' . $row['id'];
    $db->prepare("UPDATE businesses SET slug = ? WHERE id = ?")->execute([$slug, $row['id']]);
    echo "Generated slug for {$row['business_name']}: $slug\n";
}

// ── 2. Location tables ────────────────────────────────────────────
$db->exec("CREATE TABLE IF NOT EXISTS `countries` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `iso_code` varchar(2) DEFAULT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
echo "OK: countries table\n";

$db->exec("CREATE TABLE IF NOT EXISTS `states` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `country_id` bigint(20) unsigned NOT NULL,
    `name` varchar(255) NOT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    KEY `states_country_id_foreign` (`country_id`),
    CONSTRAINT `states_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
echo "OK: states table\n";

$db->exec("CREATE TABLE IF NOT EXISTS `cities` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `state_id` bigint(20) unsigned NOT NULL,
    `name` varchar(255) NOT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    KEY `cities_state_id_foreign` (`state_id`),
    CONSTRAINT `cities_state_id_foreign` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
echo "OK: cities table\n";

// Add location FK columns to businesses
$locCols = [
    "ADD COLUMN `country_id` bigint(20) unsigned DEFAULT NULL AFTER `district`",
    "ADD COLUMN `state_id` bigint(20) unsigned DEFAULT NULL AFTER `country_id`",
    "ADD COLUMN `city_id` bigint(20) unsigned DEFAULT NULL AFTER `state_id`",
];
foreach ($locCols as $col) {
    try {
        $db->exec("ALTER TABLE businesses $col");
        echo "OK: businesses $col\n";
    } catch (PDOException $e) {
        if (str_contains($e->getMessage(), 'Duplicate column')) {
            echo "SKIP (exists): businesses $col\n";
        } else {
            echo "ERR: " . $e->getMessage() . "\n";
        }
    }
}

// Seed Nepal locations
$db->exec("INSERT IGNORE INTO countries (id, name, iso_code) VALUES (1, 'Nepal', 'NP')");

$nepalStates = [
    ['name' => 'Koshi', 'districts' => ['Bhojpur', 'Dhankuta', 'Ilam', 'Jhapa', 'Khotang', 'Morang', 'Okhaldhunga', 'Panchthar', 'Sankhuwasabha', 'Solukhumbu', 'Sunsari', 'Taplejung', 'Terhathum', 'Udayapur']],
    ['name' => 'Madhesh', 'districts' => ['Bara', 'Dhanusha', 'Mahottari', 'Parsa', 'Rautahat', 'Saptari', 'Sarlahi', 'Siraha']],
    ['name' => 'Bagmati', 'districts' => ['Bhaktapur', 'Chitwan', 'Dhading', 'Dolakha', 'Kathmandu', 'Kavrepalanchok', 'Lalitpur', 'Makwanpur', 'Nuwakot', 'Ramechhap', 'Rasuwa', 'Sindhuli', 'Sindhupalchok']],
    ['name' => 'Gandaki', 'districts' => ['Baglung', 'Gorkha', 'Kaski', 'Lamjung', 'Manang', 'Mustang', 'Myagdi', 'Nawalpur', 'Parbat', 'Syangja', 'Tanahun']],
    ['name' => 'Lumbini', 'districts' => ['Arghakhanchi', 'Banke', 'Bardiya', 'Dang', 'Gulmi', 'Kapilvastu', 'Palpa', 'Pyuthan', 'Rolpa', 'Rukum East', 'Rupandehi']],
    ['name' => 'Karnali', 'districts' => ['Dailekh', 'Dolpa', 'Humla', 'Jajarkot', 'Jumla', 'Kalikot', 'Mugu', 'Salyan', 'Surkhet', 'Western Rukum']],
    ['name' => 'Sudurpashchim', 'districts' => ['Achham', 'Baitadi', 'Bajhang', 'Bajura', 'Dadeldhura', 'Darchula', 'Doti', 'Kailali', 'Kanchanpur']],
];

$stateStmt = $db->prepare("INSERT IGNORE INTO states (country_id, name) VALUES (1, ?)");
$cityStmt = $db->prepare("INSERT IGNORE INTO cities (state_id, name) VALUES (?, ?)");
$findStateStmt = $db->prepare("SELECT id FROM states WHERE country_id = 1 AND name = ?");

foreach ($nepalStates as $ns) {
    $stateStmt->execute([$ns['name']]);
    $findStateStmt->execute([$ns['name']]);
    $stateId = (int)$findStateStmt->fetchColumn();
    foreach ($ns['districts'] as $district) {
        $cityStmt->execute([$stateId, $district]);
    }
}
echo "OK: Seeded Nepal locations\n";

// Map existing province/district to new FK columns
$biz = $db->query("SELECT id, province, district FROM businesses WHERE province IS NOT NULL")->fetchAll();
$mapState = $db->prepare("SELECT id FROM states WHERE country_id = 1 AND name = ?");
$mapCity = $db->prepare("SELECT id FROM cities WHERE state_id = ? AND name = ?");
$updBiz = $db->prepare("UPDATE businesses SET country_id = 1, state_id = ?, city_id = ? WHERE id = ?");

foreach ($biz as $b) {
    $mapState->execute([$b['province']]);
    $stateId = $mapState->fetchColumn();
    if ($stateId && $b['district']) {
        $mapCity->execute([$stateId, $b['district']]);
        $cityId = $mapCity->fetchColumn();
        $updBiz->execute([$stateId, $cityId ?: null, $b['id']]);
    } elseif ($stateId) {
        $updBiz->execute([$stateId, null, $b['id']]);
    }
}
echo "OK: Mapped existing locations\n";

// ── 3. Business Financials (multiple fiscal years) ─────────────────
$db->exec("CREATE TABLE IF NOT EXISTS `business_financials` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `business_id` bigint(20) unsigned NOT NULL,
    `fiscal_year` int(11) NOT NULL,
    `revenue` decimal(15,2) DEFAULT NULL,
    `expenses` decimal(15,2) DEFAULT NULL,
    `profit` decimal(15,2) DEFAULT NULL,
    `ebitda` decimal(15,2) DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `business_financials_business_id_foreign` (`business_id`),
    CONSTRAINT `business_financials_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
echo "OK: business_financials table\n";

// ── 4. Business Inquiries (contact requests) ──────────────────────
$db->exec("CREATE TABLE IF NOT EXISTS `business_inquiries` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `business_id` bigint(20) unsigned NOT NULL,
    `user_id` bigint(20) unsigned NOT NULL,
    `message` text DEFAULT NULL,
    `status` enum('new','contacted','qualified','rejected') NOT NULL DEFAULT 'new',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `business_inquiries_business_id_foreign` (`business_id`),
    KEY `business_inquiries_user_id_foreign` (`user_id`),
    CONSTRAINT `business_inquiries_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
    CONSTRAINT `business_inquiries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
echo "OK: business_inquiries table\n";

// ── 5. Business Assets ────────────────────────────────────────────
$db->exec("CREATE TABLE IF NOT EXISTS `business_assets` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `business_id` bigint(20) unsigned NOT NULL,
    `asset_name` varchar(255) NOT NULL,
    `asset_type` enum('land','building','equipment','inventory','vehicle','intellectual_property','other') DEFAULT NULL,
    `estimated_value` decimal(15,2) DEFAULT NULL,
    `description` text DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `business_assets_business_id_foreign` (`business_id`),
    CONSTRAINT `business_assets_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
echo "OK: business_assets table\n";

// ── 6. Business Media (extends business_photos) ───────────────────
$db->exec("CREATE TABLE IF NOT EXISTS `business_media` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `business_id` bigint(20) unsigned NOT NULL,
    `file_url` text DEFAULT NULL,
    `media_type` enum('image','video','document') NOT NULL DEFAULT 'image',
    `sort_order` int(11) NOT NULL DEFAULT 0,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `business_media_business_id_foreign` (`business_id`),
    CONSTRAINT `business_media_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
echo "OK: business_media table\n";

// Migrate existing business_photos to business_media
$db->exec("INSERT IGNORE INTO business_media (business_id, file_url, media_type, sort_order, created_at)
    SELECT business_id, CONCAT('/public/uploads/business-photos/', file_path), 'image', sort_order, created_at
    FROM business_photos");
echo "OK: Migrated existing photos to business_media\n";

// ── 7. NDA Requests ───────────────────────────────────────────────
$db->exec("CREATE TABLE IF NOT EXISTS `nda_requests` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `business_id` bigint(20) unsigned NOT NULL,
    `investor_id` bigint(20) unsigned NOT NULL,
    `signed` tinyint(1) NOT NULL DEFAULT 0,
    `signed_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `nda_requests_business_id_foreign` (`business_id`),
    KEY `nda_requests_investor_id_foreign` (`investor_id`),
    CONSTRAINT `nda_requests_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
    CONSTRAINT `nda_requests_investor_id_foreign` FOREIGN KEY (`investor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
echo "OK: nda_requests table\n";

// ── 8. Business Verifications ─────────────────────────────────────
$db->exec("CREATE TABLE IF NOT EXISTS `business_verifications` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `business_id` bigint(20) unsigned NOT NULL,
    `email_verified` tinyint(1) NOT NULL DEFAULT 0,
    `phone_verified` tinyint(1) NOT NULL DEFAULT 0,
    `identity_verified` tinyint(1) NOT NULL DEFAULT 0,
    `company_verified` tinyint(1) NOT NULL DEFAULT 0,
    `verified_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `business_verifications_business_id_unique` (`business_id`),
    CONSTRAINT `business_verifications_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
echo "OK: business_verifications table\n";

echo "\n=== Migration complete ===\n";
