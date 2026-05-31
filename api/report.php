<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();
csrf_check();

$user = current_user();
$targetType = $_POST['target_type'] ?? '';
$targetId = (int)($_POST['target_id'] ?? 0);
$reason = mb_substr(trim($_POST['reason'] ?? ''), 0, 100);
$details = mb_substr(trim($_POST['details'] ?? ''), 0, 500);

if (!in_array($targetType, ['business', 'pitch', 'franchise', 'investor'], true) || $targetId < 1 || empty($reason)) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid request']);
    exit;
}

$db = db();
$insert = $db->prepare('INSERT INTO reports (reporter_id, target_type, target_id, reason, details, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, \'open\', NOW(), NOW())');
$insert->execute([$user['id'], $targetType, $targetId, $reason, $details]);

$notif = $db->prepare('INSERT INTO notifications (user_id, type, title, body, action_url, created_at) VALUES (?, \'report\', \'New Report Submitted\', ?, \'/admin/reports\', NOW())');
$notif->execute([1, $targetType . ' #' . $targetId . ' reported: ' . $reason]);

header('Content-Type: application/json');
echo json_encode(['ok' => true]);
