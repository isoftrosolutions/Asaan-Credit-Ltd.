<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

$user = require_api_auth();
$userId = (int)$user['id'];

$input = get_json_input();
$id = (int)($input['id'] ?? 0);

if ($id < 1) {
    json_error('Pitch id is required.', 400);
}

$db = db();

$stmt = $db->prepare('SELECT user_id FROM pitches WHERE id = ?');
$stmt->execute([$id]);
$pitch = $stmt->fetch();

if (!$pitch) {
    json_error('Pitch not found.', 404);
}
if ((int)$pitch['user_id'] !== $userId) {
    json_error('Forbidden: you do not own this pitch.', 403);
}

$stmt = $db->prepare("UPDATE pitches SET
    tagline = ?, short_summary = ?, problem_statement = ?, solution = ?,
    market_size = ?, business_model = ?, funding_amount = ?, equity_offered = ?,
    valuation = ?, stage = ?, sector_id = ?, traction = ?,
    target_customers = ?, competitors = ?, competitive_advantage = ?,
    updated_at = NOW()
    WHERE id = ?");

$stmt->execute([
    $input['tagline'] ?? null,
    $input['short_summary'] ?? null,
    $input['problem_statement'] ?? null,
    $input['solution'] ?? null,
    $input['market_size'] ?? null,
    $input['business_model'] ?? null,
    $input['funding_amount'] ?? null,
    $input['equity_offered'] ?? null,
    $input['valuation'] ?? null,
    $input['stage'] ?? null,
    $input['sector_id'] ?? null,
    $input['traction'] ?? null,
    $input['target_customers'] ?? null,
    $input['competitors'] ?? null,
    $input['competitive_advantage'] ?? null,
    $id,
]);

json_success(['updated' => true]);
