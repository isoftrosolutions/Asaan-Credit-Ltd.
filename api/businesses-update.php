<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

$user = require_api_auth();
$userId = (int)$user['id'];

$input = get_json_input();
$id = (int)($input['id'] ?? 0);

if ($id < 1) {
    json_error('Business id is required.', 400);
}

$db = db();

$stmt = $db->prepare('SELECT user_id FROM businesses WHERE id = ?');
$stmt->execute([$id]);
$business = $stmt->fetch();

if (!$business) {
    json_error('Business not found.', 404);
}
if ((int)$business['user_id'] !== $userId) {
    json_error('Forbidden: you do not own this business.', 403);
}

$stmt = $db->prepare("UPDATE businesses SET
    business_name = ?, listing_type = ?, sector_id = ?, province = ?, district = ?,
    established_year = ?, employee_count = ?, legal_entity_type = ?,
    annual_revenue = ?, monthly_revenue = ?, ebitda_pct = ?,
    asking_price = ?, funding_required = ?, valuation = ?, stake_offered_pct = ?,
    description = ?, overview = ?, products_services = ?,
    reason_for_sale = ?, assets_included = ?, facilities = ?,
    updated_at = NOW()
    WHERE id = ?");

$stmt->execute([
    $input['business_name'] ?? '',
    $input['listing_type'] ?? '',
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
    $id,
]);

json_success(['updated' => true]);
