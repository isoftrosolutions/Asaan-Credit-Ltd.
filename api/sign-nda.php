<?php
require __DIR__ . '/../config/bootstrap.php';

$user = current_user();
if (!$user) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Please login first.']);
    exit;
}

csrf_check();

$businessId = (int)($_POST['business_id'] ?? 0);
$userId = (int)$user['id'];

if ($businessId < 1) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid business ID.']);
    exit;
}

$db = db();

// Check business exists
$stmt = $db->prepare('SELECT id FROM businesses WHERE id = ?');
$stmt->execute([$businessId]);
if (!$stmt->fetch()) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Business not found.']);
    exit;
}

// Upsert NDA
$stmt = $db->prepare('INSERT INTO nda_requests (business_id, investor_id, signed, signed_at, created_at, updated_at) VALUES (?, ?, 1, NOW(), NOW(), NOW()) ON DUPLICATE KEY UPDATE signed = 1, signed_at = NOW(), updated_at = NOW()');
$stmt->execute([$businessId, $userId]);

// Also create verification record if it doesn't exist
$db->prepare('INSERT IGNORE INTO business_verifications (business_id, created_at, updated_at) VALUES (?, NOW(), NOW())')->execute([$businessId]);

echo json_encode(['ok' => true]);
