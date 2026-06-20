<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

$user = require_api_auth();
$userId = (int)$user['id'];

$input = get_json_input();

foreach (['brand_name'] as $field) {
    if (empty($input[$field])) {
        json_error("Field '$field' is required.", 400);
    }
}

$db = db();
$now = date('Y-m-d H:i:s');

$stmt = $db->prepare("INSERT INTO franchises (user_id, brand_name, sector_id, established_year, existing_units,
    description, ideal_partner_profile, franchise_fee, royalty_pct,
    total_investment_min, total_investment_max, expected_payback_months, training_provided,
    created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");

$stmt->execute([
    $userId,
    $input['brand_name'],
    $input['sector_id'] ?? null,
    $input['established_year'] ?? null,
    $input['existing_units'] ?? null,
    $input['description'] ?? null,
    $input['ideal_partner_profile'] ?? null,
    $input['franchise_fee'] ?? null,
    $input['royalty_pct'] ?? null,
    $input['total_investment_min'] ?? null,
    $input['total_investment_max'] ?? null,
    $input['expected_payback_months'] ?? null,
    $input['training_provided'] ?? null,
]);

json_success(['id' => (int)$db->lastInsertId()]);
