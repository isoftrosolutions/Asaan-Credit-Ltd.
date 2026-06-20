<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

$id = (int)($_GET['id'] ?? 0);
if ($id < 1) {
    json_error('Franchise id is required.', 400);
}

$user = _api_auth_user();
$userId = $user ? (int)$user['id'] : 0;

$stmt = db()->prepare('SELECT f.*, s.name AS sector_name, u.name AS user_name FROM franchises f LEFT JOIN sectors s ON s.id = f.sector_id LEFT JOIN users u ON u.id = f.user_id WHERE f.id = ?');
$stmt->execute([$id]);
$franchise = $stmt->fetch();

if (!$franchise) {
    json_error('Franchise not found.', 404);
}

if (!$franchise['is_published'] && (!$user || $userId !== (int)$franchise['user_id'])) {
    json_error('Franchise not found.', 404);
}

db()->prepare('UPDATE franchises SET views = views + 1 WHERE id = ?')->execute([$id]);

$ownerUserId = (int)$franchise['user_id'];
$hasMatch = false;
if ($userId) {
    $matchStmt = db()->prepare("SELECT id FROM matches WHERE context_type = 'franchise' AND context_id = ? AND ((user_a_id = ? AND user_b_id = ?) OR (user_a_id = ? AND user_b_id = ?)) AND closed_status = 'open' LIMIT 1");
    $matchStmt->execute([$id, $userId, $ownerUserId, $ownerUserId, $userId]);
    $hasMatch = (bool)$matchStmt->fetch();
}

json_success([
    'franchise' => $franchise,
    'has_match' => $hasMatch,
]);
