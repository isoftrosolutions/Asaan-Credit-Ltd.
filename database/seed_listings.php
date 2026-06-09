<?php
require __DIR__ . '/../config/bootstrap.php';

$db = db();

// Add thumbnail_url column if it doesn't exist
try {
    $db->exec("ALTER TABLE businesses ADD COLUMN thumbnail_url VARCHAR(500) DEFAULT NULL AFTER assets_included");
    echo "Added thumbnail_url column to businesses table\n";
} catch (PDOException $e) {
    echo "Column thumbnail_url may already exist: " . $e->getMessage() . "\n";
}

// Check existing businesses to see what IDs are taken
$existing = $db->query("SELECT id, business_name FROM businesses ORDER BY id")->fetchAll();
$takenIds = array_column($existing, 'id');
echo "Existing business IDs: " . implode(', ', $takenIds) . "\n";

// Get user IDs
$users = $db->query("SELECT id FROM users ORDER BY id")->fetchAll();
$userIds = array_column($users, 'id');
if (empty($userIds)) {
    echo "ERROR: No users found. Run the schema first.\n";
    exit(1);
}
$defaultUserId = $userIds[0];

// Sector mapping
$sectors = $db->query("SELECT id, name FROM sectors")->fetchAll();
$sectorMap = [];
foreach ($sectors as $s) {
    $sectorMap[$s['name']] = $s['id'];
}

$listings = [
    // --- Hotels & Hospitality ---
    [
        'name' => 'Mountain Vista Resort & Spa',
        'type' => 'sale',
        'sector' => 'Hospitality',
        'province' => 'Gandaki',
        'district' => 'Pokhara',
        'year' => 2012,
        'employees' => 48,
        'revenue' => 65000000,
        'ebitda' => 22.00,
        'price' => 120000000,
        'desc' => 'A 30-room boutique resort with panoramic Himalayan views, full-service spa, and multi-cuisine restaurant. Located on the scenic Pokhara lakeside. 85% average occupancy rate with 4.7-star guest rating.',
        'reason' => 'Owner retiring after 12 successful years in hospitality',
        'featured' => true,
        'rating' => 9.1,
        'thumb' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=400&h=300&fit=crop',
    ],
    [
        'name' => 'Downtown Kathmandu Business Hotel',
        'type' => 'sale',
        'sector' => 'Hospitality',
        'province' => 'Bagmati',
        'district' => 'Kathmandu',
        'year' => 2015,
        'employees' => 35,
        'revenue' => 42000000,
        'ebitda' => 18.50,
        'price' => 75000000,
        'desc' => 'Well-established 20-room business hotel in central Kathmandu with conference facilities, restaurant, and bar. Strong corporate client base and consistent year-round revenue.',
        'reason' => 'Seeking to divest for larger development project',
        'featured' => false,
        'rating' => 8.3,
        'thumb' => 'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=400&h=300&fit=crop',
    ],
    [
        'name' => 'Lakeside Restaurant & Bar',
        'type' => 'sale',
        'sector' => 'Hospitality',
        'province' => 'Gandaki',
        'district' => 'Pokhara',
        'year' => 2018,
        'employees' => 18,
        'revenue' => 18000000,
        'ebitda' => 25.00,
        'price' => 28000000,
        'desc' => 'Popular multi-cuisine restaurant and bar on Pokhara lakefront with 80-seat capacity, outdoor terrace, and live music license. Strong tourist and expat clientele.',
        'reason' => 'Owner relocating abroad',
        'featured' => false,
        'rating' => 8.7,
        'thumb' => 'https://images.unsplash.com/photo-1552566626-52f8b828add9?w=400&h=300&fit=crop',
    ],

    // --- Retail & Pharmacy ---
    [
        'name' => 'Everest Wellness Pharmacy Chain',
        'type' => 'sale',
        'sector' => 'Retail',
        'province' => 'Bagmati',
        'district' => 'Kathmandu',
        'year' => 2008,
        'employees' => 42,
        'revenue' => 72000000,
        'ebitda' => 14.00,
        'price' => 85000000,
        'desc' => 'Chain of 7 pharmacies across Kathmandu Valley with wholesale distribution license. Established supplier relationships with major pharmaceutical companies. Loyal customer base of 15,000+.',
        'reason' => 'Founder expanding into hospital management',
        'featured' => false,
        'rating' => 8.0,
        'thumb' => 'https://images.unsplash.com/photo-1585435557343-3b092031a831?w=400&h=300&fit=crop',
    ],
    [
        'name' => 'Himalayan General Store & Provisions',
        'type' => 'sale',
        'sector' => 'Retail',
        'province' => 'Bagmati',
        'district' => 'Lalitpur',
        'year' => 2010,
        'employees' => 22,
        'revenue' => 35000000,
        'ebitda' => 12.00,
        'price' => 40000000,
        'desc' => 'Full-service general store in a high-traffic residential area of Lalitpur. Stocking groceries, household items, and local specialties. Stable annual growth of 8-10%.',
        'reason' => 'Owner pursuing franchise opportunity',
        'featured' => false,
        'rating' => 7.8,
        'thumb' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=400&h=300&fit=crop',
    ],
    [
        'name' => 'Organic & Natural Products Boutique',
        'type' => 'partial_stake',
        'sector' => 'Retail',
        'province' => 'Bagmati',
        'district' => 'Kathmandu',
        'year' => 2019,
        'employees' => 12,
        'revenue' => 15000000,
        'ebitda' => 20.00,
        'price' => 12000000,
        'desc' => 'Specialty retail store offering organic foods, natural cosmetics, and eco-friendly home products. Growing health-conscious customer segment. Seeking partner for 40% equity to fund expansion.',
        'reason' => 'Expanding to second location, need growth capital',
        'featured' => false,
        'rating' => 8.5,
        'thumb' => 'https://images.unsplash.com/photo-1558618666-fcd25c85f82e?w=400&h=300&fit=crop',
    ],

    // --- EdTech ---
    [
        'name' => 'LearnNepal Online Academy',
        'type' => 'partial_stake',
        'sector' => 'EdTech',
        'province' => 'Bagmati',
        'district' => 'Kathmandu',
        'year' => 2020,
        'employees' => 28,
        'revenue' => 22000000,
        'ebitda' => 28.00,
        'price' => 35000000,
        'desc' => 'Online learning platform offering STEM courses for grades 8-12. 8,500+ active students across 35 districts. Offline-capable mobile app. Partnerships with 50+ schools.',
        'reason' => 'Scaling to include vocational training vertical',
        'featured' => true,
        'rating' => 9.0,
        'thumb' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?w=400&h=300&fit=crop',
    ],
    [
        'name' => 'SkillBridge Vocational Training Center',
        'type' => 'sale',
        'sector' => 'EdTech',
        'province' => 'Province 1',
        'district' => 'Biratnagar',
        'year' => 2016,
        'employees' => 25,
        'revenue' => 28000000,
        'ebitda' => 16.00,
        'price' => 45000000,
        'desc' => 'CTEVT-affiliated vocational training center offering IT, hospitality, and healthcare assistant courses. 500+ graduates annually with 85% placement rate. Government-recognized certification.',
        'reason' => 'Owner retiring, looking for successor',
        'featured' => false,
        'rating' => 8.1,
        'thumb' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=400&h=300&fit=crop',
    ],

    // --- AgriTech ---
    [
        'name' => 'GreenFarm Hydroponics',
        'type' => 'partial_stake',
        'sector' => 'AgriTech',
        'province' => 'Bagmati',
        'district' => 'Kathmandu',
        'year' => 2021,
        'employees' => 15,
        'revenue' => 12000000,
        'ebitda' => 30.00,
        'price' => 18000000,
        'desc' => 'Commercial hydroponic farm supplying premium lettuce, herbs, and microgreens to 40+ hotels and restaurants in Kathmandu. Year-round production with 3 greenhouse facilities.',
        'reason' => 'Seeking capital to build 5 more greenhouse units',
        'featured' => false,
        'rating' => 8.8,
        'thumb' => 'https://images.unsplash.com/photo-1585515321484-4e8e8e6b8f7b?w=400&h=300&fit=crop',
    ],
    [
        'name' => 'Nepal Organic Tea Plantation',
        'type' => 'sale',
        'sector' => 'AgriTech',
        'province' => 'Province 1',
        'district' => 'Ilam',
        'year' => 2005,
        'employees' => 60,
        'revenue' => 38000000,
        'ebitda' => 20.00,
        'price' => 95000000,
        'desc' => 'Established 25-acre organic tea estate in Ilam producing premium orthodox teas. Exports to 8 countries. Certified organic and Fair Trade. On-site processing facility.',
        'reason' => 'Founder looking for strategic acquisition partner',
        'featured' => false,
        'rating' => 9.2,
        'thumb' => 'https://images.unsplash.com/photo-1563822249366-3efb23b8e0c9?w=400&h=300&fit=crop',
    ],

    // --- Manufacturing ---
    [
        'name' => 'Annapurna Steel Fabrication',
        'type' => 'sale',
        'sector' => 'Manufacturing',
        'province' => 'Bagmati',
        'district' => 'Hetauda',
        'year' => 2014,
        'employees' => 85,
        'revenue' => 95000000,
        'ebitda' => 15.00,
        'price' => 110000000,
        'desc' => 'Steel fabrication and structural manufacturing unit with 30,000 sq ft factory. Supplies construction companies across central Nepal. ISO 9001 certified. Modern machinery.',
        'reason' => 'Owner pursuing import business opportunities',
        'featured' => false,
        'rating' => 8.2,
        'thumb' => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=400&h=300&fit=crop',
    ],
    [
        'name' => 'Himalayan Pashmina Weaving Mill',
        'type' => 'partial_stake',
        'sector' => 'Manufacturing',
        'province' => 'Bagmati',
        'district' => 'Kathmandu',
        'year' => 2009,
        'employees' => 55,
        'revenue' => 52000000,
        'ebitda' => 22.00,
        'price' => 65000000,
        'desc' => 'Traditional pashmina shawl and scarf manufacturer with 40 handlooms. Exports to luxury retailers in Europe, Japan, and North America. Ethical production certified.',
        'reason' => 'Seeking investment for digital marketing and US market entry',
        'featured' => false,
        'rating' => 8.6,
        'thumb' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=400&h=300&fit=crop',
    ],
    [
        'name' => 'Everest Bottled Water Co.',
        'type' => 'sale',
        'sector' => 'Manufacturing',
        'province' => 'Bagmati',
        'district' => 'Dhulikhel',
        'year' => 2011,
        'employees' => 38,
        'revenue' => 45000000,
        'ebitda' => 18.00,
        'price' => 55000000,
        'desc' => 'Premium spring water bottling plant with source at 1,600m elevation. 5-stage purification system. Supplies 20,000+ bottles daily to hotels, offices, and retail outlets.',
        'reason' => 'Competitive market prompting owner to exit',
        'featured' => false,
        'rating' => 7.9,
        'thumb' => 'https://images.unsplash.com/photo-1616118132534-3815a88f96b6?w=400&h=300&fit=crop',
    ],

    // --- Food & Beverage ---
    [
        'name' => 'Newa Momo Kitchen Franchise',
        'type' => 'loan',
        'sector' => 'Food & Beverage',
        'province' => 'Bagmati',
        'district' => 'Kathmandu',
        'year' => 2017,
        'employees' => 20,
        'revenue' => 25000000,
        'ebitda' => 24.00,
        'price' => 15000000,
        'desc' => 'Popular momo chain with 3 outlets in Kathmandu. Famous for authentic Newari-style momos. Strong brand recognition. Looking for loan to open 2 more outlets in Lalitpur.',
        'reason' => 'Expansion capital for new outlets',
        'featured' => false,
        'rating' => 8.4,
        'thumb' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=400&h=300&fit=crop',
    ],
    [
        'name' => 'Himalayan Coffee Roasters',
        'type' => 'partial_stake',
        'sector' => 'Food & Beverage',
        'province' => 'Bagmati',
        'district' => 'Lalitpur',
        'year' => 2018,
        'employees' => 14,
        'revenue' => 18000000,
        'ebitda' => 26.00,
        'price' => 25000000,
        'desc' => 'Specialty coffee roastery sourcing beans from 200+ small farmers in Palpa and Gulmi. Supplies 60+ cafes across Nepal. Single-origin and blend offerings. 35% YoY growth.',
        'reason' => 'Scaling production capacity and retail presence',
        'featured' => true,
        'rating' => 9.3,
        'thumb' => 'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?w=400&h=300&fit=crop',
    ],
    [
        'name' => 'Bakery & Patisserie Shop',
        'type' => 'sale',
        'sector' => 'Food & Beverage',
        'province' => 'Bagmati',
        'district' => 'Bhaktapur',
        'year' => 2019,
        'employees' => 10,
        'revenue' => 9500000,
        'ebitda' => 32.00,
        'price' => 15000000,
        'desc' => 'Artisan bakery in heritage area of Bhaktapur producing sourdough, pastries, and celebration cakes. Strong repeat customer base. Equipment and recipes included.',
        'reason' => 'Owner focusing on food consultancy',
        'featured' => false,
        'rating' => 8.9,
        'thumb' => 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=400&h=300&fit=crop',
    ],

    // --- E-commerce ---
    [
        'name' => 'NepalCraft Online Marketplace',
        'type' => 'partial_stake',
        'sector' => 'E-commerce',
        'province' => 'Bagmati',
        'district' => 'Kathmandu',
        'year' => 2020,
        'employees' => 18,
        'revenue' => 28000000,
        'ebitda' => 16.00,
        'price' => 40000000,
        'desc' => 'E-commerce platform connecting Nepali artisans with global buyers. 1,200+ registered sellers, 15,000+ products. Monthly orders: 2,500+. Shipping to 25+ countries.',
        'reason' => 'Need capital for logistics infrastructure and marketing',
        'featured' => false,
        'rating' => 8.0,
        'thumb' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=400&h=300&fit=crop',
    ],
    [
        'name' => 'FreshGrocery Nepal',
        'type' => 'sale',
        'sector' => 'E-commerce',
        'province' => 'Bagmati',
        'district' => 'Kathmandu',
        'year' => 2021,
        'employees' => 25,
        'revenue' => 32000000,
        'ebitda' => 10.00,
        'price' => 22000000,
        'desc' => 'Online grocery delivery service covering Kathmandu Valley. 4,000+ SKUs, own warehouse and delivery fleet. 300+ daily orders. 2-hour delivery window.',
        'reason' => 'Competitive pressure prompting sale to larger player',
        'featured' => false,
        'rating' => 7.5,
        'thumb' => 'https://images.unsplash.com/photo-1594398901394-4e34939a4e17?w=400&h=300&fit=crop',
    ],

    // --- Construction ---
    [
        'name' => 'Shiva Construction & Engineering',
        'type' => 'sale',
        'sector' => 'Construction',
        'province' => 'Bagmati',
        'district' => 'Kathmandu',
        'year' => 2007,
        'employees' => 120,
        'revenue' => 150000000,
        'ebitda' => 12.00,
        'price' => 180000000,
        'desc' => 'Class A construction company with 17 years of project delivery. Completed 35+ commercial and residential projects. Owns heavy equipment fleet. Government-approved contractor.',
        'reason' => 'Founders approaching retirement age',
        'featured' => false,
        'rating' => 8.2,
        'thumb' => 'https://images.unsplash.com/photo-1504917595217-d4dc5ebe6122?w=400&h=300&fit=crop',
    ],
    [
        'name' => 'GreenBuild Materials Supply',
        'type' => 'sale',
        'sector' => 'Construction',
        'province' => 'Bagmati',
        'district' => 'Lalitpur',
        'year' => 2016,
        'employees' => 28,
        'revenue' => 42000000,
        'ebitda' => 14.00,
        'price' => 35000000,
        'desc' => 'Eco-friendly construction materials supplier offering bamboo panels, recycled aggregates, and energy-efficient blocks. Exclusive distributor for 5 international brands in Nepal.',
        'reason' => 'Owner shifting focus to architectural consultancy',
        'featured' => false,
        'rating' => 8.0,
        'thumb' => 'https://images.unsplash.com/photo-1579003106855-42f65416a5e5?w=400&h=300&fit=crop',
    ],
];

$insertSql = "INSERT INTO businesses (user_id, business_name, listing_type, sector_id, province, district, established_year, employee_count, annual_revenue, ebitda_pct, asking_price, description, reason_for_sale, thumbnail_url, is_published, is_featured, views, rating, created_at, updated_at)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, FLOOR(RAND() * 800) + 200, ?, NOW(), NOW())";

$stmt = $db->prepare($insertSql);
$count = 0;

foreach ($listings as $l) {
    $sid = $sectorMap[$l['sector']] ?? null;
    if (!$sid) {
        echo "SKIP: Sector '{$l['sector']}' not found for '{$l['name']}'\n";
        continue;
    }
    $stmt->execute([
        $defaultUserId,
        $l['name'],
        $l['type'],
        $sid,
        $l['province'],
        $l['district'],
        $l['year'],
        $l['employees'],
        $l['revenue'],
        $l['ebitda'],
        $l['price'],
        $l['desc'],
        $l['reason'],
        $l['thumb'],
        $l['featured'] ? 1 : 0,
        $l['rating'],
    ]);
    $count++;
    echo "Inserted: {$l['name']} ({$l['sector']}) - NPR " . number_format($l['price']) . "\n";
}

echo "\nTotal listings inserted: $count\n";
