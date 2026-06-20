<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

$user = require_api_auth();
$userId = (int)$user['id'];

$input = get_json_input();

$db = db();
$now = date('Y-m-d H:i:s');

$stmt = $db->prepare("INSERT INTO pitches (user_id, tagline, short_summary, problem_statement, solution,
    market_size, business_model, funding_amount, equity_offered, valuation, stage, sector_id,
    traction, target_customers, competitors, competitive_advantage, is_published, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");

$stmt->execute([
    $userId,
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
    0,
]);

json_success(['id' => (int)$db->lastInsertId()]);
