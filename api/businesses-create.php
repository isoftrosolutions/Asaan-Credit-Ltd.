<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

$user = require_api_auth();
$userId = (int)$user['id'];

$input = get_json_input();

foreach (['business_name', 'listing_type'] as $field) {
    if (empty($input[$field])) {
        json_error("Field '$field' is required.", 400);
    }
}

$db = db();
$slug = unique_slug(generate_slug($input['business_name']), 'businesses');
$now = date('Y-m-d H:i:s');

$stmt = $db->prepare("INSERT INTO businesses (user_id, business_name, slug, listing_type, sector_id, province,
    district, established_year, employee_count, legal_entity_type, annual_revenue, monthly_revenue, ebitda_pct,
    asking_price, funding_required, valuation, stake_offered_pct, description, overview, products_services,
    reason_for_sale, assets_included, facilities, status, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");

$stmt->execute([
    $userId,
    $input['business_name'],
    $slug,
    $input['listing_type'],
    $input['sector_id'] ?? null,
    $input['province'] ?? null,
    $input['district'] ?? null,
    $input['established_year'] ?? null,
    $input['employee_count'] ?? null,
    $input['legal_entity_type'] ?? null,
    $input['annual_revenue'] ?? null,
    $input['monthly_revenue'] ?? null,
    $input['ebitda_pct'] ?? null,
    $input['asking_price'] ?? null,
    $input['funding_required'] ?? null,
    $input['valuation'] ?? null,
    $input['stake_offered_pct'] ?? null,
    $input['description'] ?? null,
    $input['overview'] ?? null,
    $input['products_services'] ?? null,
    $input['reason_for_sale'] ?? null,
    $input['assets_included'] ?? null,
    $input['facilities'] ?? null,
    'pending',
]);

json_success(['id' => (int)$db->lastInsertId()]);
