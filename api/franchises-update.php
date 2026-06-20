<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

$user = require_api_auth();
$userId = (int)$user['id'];

$input = get_json_input();
$id = (int)($input['id'] ?? 0);

if ($id < 1) {
    json_error('Franchise id is required.', 400);
}

$db = db();

$stmt = $db->prepare('SELECT user_id FROM franchises WHERE id = ?');
$stmt->execute([$id]);
$franchise = $stmt->fetch();

if (!$franchise) {
    json_error('Franchise not found.', 404);
}
if ((int)$franchise['user_id'] !== $userId) {
    json_error('Forbidden: you do not own this franchise.', 403);
}

$stmt = $db->prepare("UPDATE franchises SET
    brand_name = ?, sector_id = ?, established_year = ?, existing_units = ?,
    description = ?, ideal_partner_profile = ?, franchise_fee = ?, royalty_pct = ?,
    total_investment_min = ?, total_investment_max = ?, expected_payback_months = ?, training_provided = ?,
    updated_at = NOW()
    WHERE id = ?");

$stmt->execute([
    $input['brand_name'] ?? '',
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
    $id,
]);

json_success(['updated' => true]);
