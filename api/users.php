<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();

header('Content-Type: application/json');

$userId = (int)($_GET['id'] ?? 0);
if ($userId < 1) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'id required']);
    exit;
}

$stmt = db()->prepare('SELECT id, name, role, profile_photo FROM users WHERE id = ? AND deleted_at IS NULL');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'User not found']);
    exit;
}

echo json_encode(['success' => true, 'user' => $user]);
