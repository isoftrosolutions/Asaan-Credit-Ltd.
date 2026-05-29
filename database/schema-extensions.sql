-- Asaan Marketplace — Schema Extensions (Phase 1)
-- Additive only. No drops. Run via: mysql -u root invest_match < database/schema-extensions.sql

ALTER TABLE users
  ADD COLUMN last_login_at TIMESTAMP NULL AFTER email_verified_at,
  ADD COLUMN failed_login_attempts INT DEFAULT 0 AFTER last_login_at,
  ADD COLUMN locked_until TIMESTAMP NULL AFTER failed_login_attempts;

CREATE TABLE IF NOT EXISTS businesses (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  business_name VARCHAR(255) NOT NULL,
  listing_type VARCHAR(50) NOT NULL,
  sector_id BIGINT UNSIGNED NULL,
  province VARCHAR(100), district VARCHAR(100),
  established_year SMALLINT,
  employee_count INT,
  annual_revenue DECIMAL(15,2),
  ebitda_pct DECIMAL(5,2),
  asking_price DECIMAL(15,2),
  stake_offered_pct DECIMAL(5,2),
  loan_amount DECIMAL(15,2), loan_interest_pct DECIMAL(5,2),
  description TEXT,
  reason_for_sale TEXT,
  assets_included TEXT,
  is_published TINYINT(1) DEFAULT 0,
  is_hidden TINYINT(1) DEFAULT 0,
  is_featured TINYINT(1) DEFAULT 0,
  views INT DEFAULT 0,
  rating DECIMAL(3,1),
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (sector_id) REFERENCES sectors(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS business_photos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id BIGINT UNSIGNED NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP NULL,
  FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS franchises (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  brand_name VARCHAR(255) NOT NULL,
  sector_id BIGINT UNSIGNED NULL,
  established_year SMALLINT,
  existing_units INT,
  countries_present VARCHAR(255),
  description TEXT,
  ideal_partner_profile TEXT,
  franchise_fee DECIMAL(15,2),
  royalty_pct DECIMAL(5,2),
  marketing_fee_pct DECIMAL(5,2),
  total_investment_min DECIMAL(15,2),
  total_investment_max DECIMAL(15,2),
  expected_payback_months INT,
  training_provided TINYINT(1) DEFAULT 1,
  territory_protection TINYINT(1) DEFAULT 0,
  logo_path VARCHAR(255),
  is_published TINYINT(1) DEFAULT 0,
  is_hidden TINYINT(1) DEFAULT 0,
  is_featured TINYINT(1) DEFAULT 0,
  views INT DEFAULT 0,
  rating DECIMAL(3,1),
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (sector_id) REFERENCES sectors(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS advisors (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  firm_name VARCHAR(255),
  specialties JSON,
  years_experience INT,
  past_deals_count INT,
  total_deal_value DECIMAL(15,2),
  credentials TEXT,
  bar_council_id VARCHAR(100),
  service_fee_structure VARCHAR(100),
  fee_min DECIMAL(15,2), fee_max DECIMAL(15,2),
  description TEXT,
  is_published TINYINT(1) DEFAULT 0,
  is_hidden TINYINT(1) DEFAULT 0,
  rating DECIMAL(3,1),
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS matches (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  interest_request_id BIGINT UNSIGNED NOT NULL,
  user_a_id BIGINT UNSIGNED NOT NULL,
  user_b_id BIGINT UNSIGNED NOT NULL,
  context_type VARCHAR(50),
  context_id BIGINT UNSIGNED NULL,
  matched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  closed_status VARCHAR(50) DEFAULT 'open',
  closed_at TIMESTAMP NULL,
  FOREIGN KEY (interest_request_id) REFERENCES interest_requests(id) ON DELETE CASCADE,
  FOREIGN KEY (user_a_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (user_b_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reports (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reporter_id BIGINT UNSIGNED NOT NULL,
  target_type VARCHAR(50) NOT NULL,
  target_id BIGINT UNSIGNED NOT NULL,
  reason VARCHAR(100) NOT NULL,
  details TEXT,
  status VARCHAR(50) DEFAULT 'open',
  resolved_by BIGINT UNSIGNED NULL,
  resolved_at TIMESTAMP NULL,
  action_taken VARCHAR(100),
  created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
  FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS broadcasts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sent_by BIGINT UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  body TEXT NOT NULL,
  audience VARCHAR(50) NOT NULL,
  delivery VARCHAR(50) NOT NULL,
  recipients_count INT DEFAULT 0,
  sent_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  FOREIGN KEY (sent_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS saved_listings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  listing_type VARCHAR(50) NOT NULL,
  listing_id BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NULL,
  UNIQUE KEY uq_save (user_id, listing_type, listing_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS admin_audit_log (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id BIGINT UNSIGNED NOT NULL,
  action VARCHAR(100) NOT NULL,
  target_type VARCHAR(50),
  target_id BIGINT UNSIGNED,
  details JSON,
  ip_address VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS smart_suggestion_cache (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  target_type VARCHAR(50),
  target_id BIGINT UNSIGNED,
  match_score DECIMAL(5,2),
  score_breakdown JSON,
  cached_until TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user_until (user_id, cached_until),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
