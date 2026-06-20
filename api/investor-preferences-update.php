<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

$user = require_api_role('investor');
$userId = (int)$user['id'];

$input = get_json_input();

$preferredSectors = isset($input['preferred_sectors']) ? json_encode($input['preferred_sectors'], JSON_UNESCAPED_UNICODE) : null;
$preferredStages = isset($input['preferred_stages']) ? json_encode($input['preferred_stages'], JSON_UNESCAPED_UNICODE) : null;
$preferredGeography = isset($input['preferred_geography']) ? json_encode($input['preferred_geography'], JSON_UNESCAPED_UNICODE) : null;

$stmt = db()->prepare('UPDATE investor_profiles SET
    preferred_sectors = ?, preferred_stages = ?, preferred_geography = ?,
    ticket_min = ?, ticket_max = ?, updated_at = NOW()
    WHERE user_id = ?');
$stmt->execute([
    $preferredSectors,
    $preferredStages,
    $preferredGeography,
    $input['ticket_min'] ?? null,
    $input['ticket_max'] ?? null,
    $userId,
]);

json_success(['updated' => true]);
