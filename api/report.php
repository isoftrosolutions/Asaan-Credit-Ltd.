<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

$user = require_api_auth();
$input = get_json_input();

$targetType = $input['target_type'] ?? '';
$targetId = (int)($input['target_id'] ?? 0);
$reason = mb_substr(trim($input['reason'] ?? ''), 0, 100);
$details = mb_substr(trim($input['details'] ?? ''), 0, 500);

if (!in_array($targetType, ['business', 'pitch', 'franchise', 'investor'], true) || $targetId < 1 || empty($reason)) {
    json_error('Invalid request');
}

$db = db();
$insert = $db->prepare('INSERT INTO reports (reporter_id, target_type, target_id, reason, details, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, \'open\', NOW(), NOW())');
$insert->execute([$user['id'], $targetType, $targetId, $reason, $details]);

$notif = $db->prepare('INSERT INTO notifications (user_id, type, title, body, action_url, created_at) VALUES (?, \'report\', \'New Report Submitted\', ?, \'/admin/reports\', NOW())');
$notif->execute([1, $targetType . ' #' . $targetId . ' reported: ' . $reason]);

json_success(['ok' => true]);
