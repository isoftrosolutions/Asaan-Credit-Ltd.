<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

$q = trim($_GET['q'] ?? '');
if ($q === '' || strlen($q) < 2) {
    json_error('Search query must be at least 2 characters.');
}

$type = $_GET['type'] ?? 'all';
$limit = min(20, max(1, (int)($_GET['limit'] ?? 10)));
$kw = '%' . $q . '%';

$results = [];
$meta = ['query' => $q, 'type' => $type];

if ($type === 'all' || $type === 'businesses') {
    $sql = "SELECT b.id, b.business_name as name, b.slug, b.province, b.district, b.asking_price, b.listing_type, 'business' as listing_type_label, b.created_at
            FROM businesses b WHERE b.status = 'approved' AND b.is_hidden = 0 AND (b.business_name LIKE ? OR b.description LIKE ? OR b.province LIKE ?)
            LIMIT ?";
    $stmt = db()->prepare($sql);
    $stmt->execute([$kw, $kw, $kw, $limit]);
    $results['businesses'] = $stmt->fetchAll();
}

if ($type === 'all' || $type === 'investors') {
    $sql = "SELECT u.id, u.name, u.province, u.district, u.profile_photo, u.account_type as investor_type, u.created_at
            FROM users u JOIN investor_profiles ip ON u.id = ip.user_id
            WHERE u.role = 'investor' AND u.verification_status = 'verified' AND (u.name LIKE ? OR u.province LIKE ? OR u.district LIKE ? OR ip.preferred_sectors LIKE ?)
            LIMIT ?";
    $stmt = db()->prepare($sql);
    $stmt->execute([$kw, $kw, $kw, $kw, $limit]);
    $results['investors'] = $stmt->fetchAll();
}

if ($type === 'all' || $type === 'pitches') {
    $sql = "SELECT p.id, p.tagline as title, p.funding_amount, p.stage, p.sector_id, s.name as sector_name, p.created_at
            FROM pitches p LEFT JOIN sectors s ON p.sector_id = s.id
            WHERE p.is_published = 1 AND p.is_hidden = 0 AND (p.tagline LIKE ? OR p.short_summary LIKE ? OR p.problem_statement LIKE ? OR p.solution LIKE ?)
            LIMIT ?";
    $stmt = db()->prepare($sql);
    $stmt->execute([$kw, $kw, $kw, $kw, $limit]);
    $results['pitches'] = $stmt->fetchAll();
}

if ($type === 'all' || $type === 'franchises') {
    $sql = "SELECT f.id, f.brand_name as name, f.logo_path as image, s.name as sector_name, f.total_investment_min, f.total_investment_max, f.created_at
            FROM franchises f LEFT JOIN sectors s ON f.sector_id = s.id
            WHERE f.is_published = 1 AND f.is_hidden = 0 AND (f.brand_name LIKE ? OR f.description LIKE ?)
            LIMIT ?";
    $stmt = db()->prepare($sql);
    $stmt->execute([$kw, $kw, $limit]);
    $results['franchises'] = $stmt->fetchAll();
}

json_success($results, $meta);
