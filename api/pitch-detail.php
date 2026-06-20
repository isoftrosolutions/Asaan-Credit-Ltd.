<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

$pitchId = (int)($_GET['id'] ?? 0);
if ($pitchId < 1) {
    json_error('Pitch id is required.', 400);
}

$db = db();
$user = _api_auth_user();
$userId = $user ? (int)$user['id'] : 0;

$stmt = $db->prepare('SELECT p.*, s.name AS sector_name, u.name AS entrepreneur_name, u.company_name, u.province, u.district, u.verification_status, u.id AS owner_id, u.profile_photo FROM pitches p LEFT JOIN sectors s ON s.id = p.sector_id JOIN users u ON u.id = p.user_id WHERE p.id = ? AND (p.is_published = 1 OR p.user_id = ?)');
$stmt->execute([$pitchId, $userId]);
$pitch = $stmt->fetch();

if (!$pitch) {
    json_error('Pitch not found.', 404);
}

$db->prepare('UPDATE pitches SET views = views + 1 WHERE id = ?')->execute([$pitchId]);

$ownerUserId = (int)$pitch['owner_id'];

$mediaStmt = $db->prepare('SELECT * FROM pitch_media WHERE pitch_id = ? ORDER BY sort_order');
$mediaStmt->execute([$pitchId]);
$media = $mediaStmt->fetchAll();

$teamStmt = $db->prepare('SELECT * FROM pitch_team_members WHERE pitch_id = ? ORDER BY id');
$teamStmt->execute([$pitchId]);
$teamMembers = $teamStmt->fetchAll();

$hasMatch = false;
if ($userId) {
    $matchStmt = $db->prepare("SELECT id FROM matches WHERE context_type = 'pitch' AND context_id = ? AND ((user_a_id = ? AND user_b_id = ?) OR (user_a_id = ? AND user_b_id = ?)) AND closed_status = 'open' LIMIT 1");
    $matchStmt->execute([$pitchId, $userId, $ownerUserId, $ownerUserId, $userId]);
    $hasMatch = (bool)$matchStmt->fetch();
}

json_success([
    'pitch' => $pitch,
    'media' => $media,
    'team_members' => $teamMembers,
    'has_match' => $hasMatch,
]);
