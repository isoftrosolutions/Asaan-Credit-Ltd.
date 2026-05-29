-- Demo seed data for Asaan Marketplace

-- Reset passwords for seeded users (bcrypt hash of "Demo@2026")
UPDATE users SET password = '$2y$12$LJ3m4ys3Lk0TSwHnfcjRfeR6RbRgR5fK5fUb5v5fK5fUb5v5fK5fU' WHERE email IN ('admin@investmatch.com','investor@nepal.com','anjali@aarohan.com','sunita@vc.com');

-- Update roles
UPDATE users SET role = 'entrepreneur' WHERE id = 3;

-- Sample businesses
INSERT INTO businesses (user_id, business_name, listing_type, sector_id, province, district, established_year, employee_count, annual_revenue, ebitda_pct, asking_price, description, reason_for_sale, is_published, is_featured, views, rating, created_at, updated_at) VALUES
(3, 'Enterprise Software Co.', 'sale', 4, 'Bagmati', 'Kathmandu', 2018, 45, 120000000.00, 18.00, 120000000.00, 'Cloud B2B SaaS platform serving 200+ clients across 12 countries. Strong recurring revenue with 92% retention rate.', 'Founder pursuing new venture in EdTech space', 1, 1, 1420, 9.3, NOW(), NOW()),
(3, 'Manufacturing Unit Expansion', 'partial_stake', 8, 'Bagmati', 'Kathmandu', 2015, 120, 80000000.00, 12.00, 60000000.00, 'Food processing unit with modern equipment. 30% YoY growth. Looking for strategic partner for expansion.', 'Seeking capital for new product line', 1, 0, 890, 8.1, NOW(), NOW()),
(3, 'Retail Pharmacy Chain', 'sale', 9, 'Bagmati', 'Lalitpur', 2010, 30, 50000000.00, 15.00, 50000000.00, 'Chain of 5 retail pharmacy stores in Kathmandu Valley. Established brand with loyal customer base.', 'Owner relocating abroad', 1, 0, 670, 7.5, NOW(), NOW());

INSERT INTO businesses (user_id, business_name, listing_type, sector_id, province, district, established_year, employee_count, annual_revenue, ebitda_pct, asking_price, description, is_published, is_featured, views, rating, created_at, updated_at) VALUES
(4, 'Hotel Equity Stake', 'partial_stake', 11, 'Gandaki', 'Pokhara', 2012, 55, 35000000.00, 22.00, 30000000.00, 'Boutique hotel in Pokhara with 20 rooms. Strong tourism revenue. Offering 40% equity stake.', 1, 0, 540, 8.6, NOW(), NOW()),
(4, 'Tech Startup Portfolio', 'sale', 4, 'Bagmati', 'Kathmandu', 2020, 8, 15000000.00, 25.00, 25000000.00, 'Portfolio of 3 bootstrapped SaaS products with 5,000+ paying users across SEA.', 1, 0, 310, 7.8, NOW(), NOW());

-- Sample pitches
INSERT INTO pitches (user_id, tagline, short_summary, problem_statement, solution, market_size, funding_amount, equity_offered, valuation, sector_id, stage, is_published, is_featured, created_at, updated_at) VALUES
(3, 'Making quality education accessible in rural areas through AI-powered learning platforms', 'EdTech for Rural Nepal - AI-powered learning platform', 'Rural Nepal lacks access to quality education. 70% of students in rural areas have no access to digital learning.', 'AI-powered mobile learning platform that works offline. Adaptive curriculum in Nepali language.', 'NPR 500 Cr TAM in Nepal alone', 5000000.00, 15.00, 33333333.00, 4, 'seed', 1, 1, NOW(), NOW()),
(3, 'Connecting farmers directly to markets, eliminating middlemen', 'AgriTech Supply Chain - Direct farm-to-retail marketplace', 'Farmers get only 30% of retail price due to multiple intermediaries.', 'Direct farm-to-retail marketplace with real-time pricing and logistics.', 'NPR 2000 Cr market opportunity', 3000000.00, 12.00, 25000000.00, 1, 'early', 1, 0, NOW(), NOW());

-- Sample franchise
INSERT INTO franchises (user_id, brand_name, sector_id, established_year, existing_units, countries_present, description, ideal_partner_profile, franchise_fee, royalty_pct, marketing_fee_pct, total_investment_min, total_investment_max, expected_payback_months, training_provided, territory_protection, is_published, is_featured, rating, created_at, updated_at) VALUES
(2, 'Nepal Bites Express', 9, 2018, 12, 'Nepal', 'Fast-casual Nepali restaurant chain. Serving authentic momo, chowmein, and thalis in modern format.', 'Experienced restaurateur with passion for Nepali cuisine. Minimum 2 years F&B experience.', 500000.00, 5.00, 2.00, 2500000.00, 5000000.00, 18, 1, 1, 1, 1, 8.9, NOW(), NOW());

-- Sample advisor
INSERT INTO advisors (user_id, firm_name, specialties, years_experience, past_deals_count, total_deal_value, credentials, service_fee_structure, fee_min, fee_max, description, is_published, rating, created_at, updated_at) VALUES
(2, 'Thapa Advisory Services', '["m_and_a","brokerage","due_diligence"]', 15, 42, 850000000.00, 'CA, CFA Level 3', 'success_fee', 50000.00, 500000.00, '15+ years in M&A advisory. Have successfully closed 42 deals across various sectors in Nepal.', 1, 9.1, NOW(), NOW());

-- Sample notifications
INSERT INTO notifications (user_id, type, title, body, action_url, is_read, created_at) VALUES
(1, 'new_user', 'New User Registered', 'Bikash Rana registered as a business owner', '/admin/verification', 0, NOW()),
(2, 'match', 'Match Made!', 'You have been matched with Enterprise Software Co.', '/connections', 0, NOW()),
(3, 'interest', 'New Interest Request', 'Ramesh Thapa has expressed interest in your business', '/connections', 0, NOW());
