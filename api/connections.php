<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

$user = require_api_auth();
$userId = (int)$user['id'];
$db = db();

$matches = $db->prepare('
    SELECT m.*,
           CASE WHEN m.user_a_id = ? THEN mu.name ELSE mu2.name END AS connected_name,
           CASE WHEN m.user_a_id = ? THEN mu.role ELSE mu2.role END AS connected_role,
           CASE WHEN m.user_a_id = ? THEN mu.email ELSE mu2.email END AS connected_email,
           CASE WHEN m.user_a_id = ? THEN mu.phone ELSE mu2.phone END AS connected_phone,
           CASE
               WHEN m.context_type = \'pitch\' THEN (SELECT tagline FROM pitches WHERE id = m.context_id)
               WHEN m.context_type = \'business\' THEN (SELECT business_name FROM businesses WHERE id = m.context_id)
               WHEN m.context_type = \'franchise\' THEN (SELECT brand_name FROM franchises WHERE id = m.context_id)
               WHEN m.context_type = \'advisor\' THEN (SELECT firm_name FROM advisors WHERE id = m.context_id)
               ELSE NULL
           END AS context_name,
           ir.message AS interest_message
    FROM matches m
    LEFT JOIN interest_requests ir ON ir.id = m.interest_request_id
    JOIN users mu ON mu.id = m.user_a_id
    JOIN users mu2 ON mu2.id = m.user_b_id
    WHERE m.user_a_id = ? OR m.user_b_id = ?
    ORDER BY m.matched_at DESC
');
$matches->execute([$userId, $userId, $userId, $userId, $userId, $userId]);
$allMatches = $matches->fetchAll();

$pendingSent = $db->prepare('
    SELECT ir.*, u.name AS receiver_name, u.role AS receiver_role,
           CASE
               WHEN ir.business_id IS NOT NULL THEN (SELECT business_name FROM businesses WHERE id = ir.business_id)
               WHEN ir.pitch_id IS NOT NULL THEN (SELECT tagline FROM pitches WHERE id = ir.pitch_id)
               ELSE NULL
           END AS context_name
    FROM interest_requests ir
    JOIN users u ON u.id = ir.receiver_id
    WHERE ir.sender_id = ? AND ir.status = \'pending\'
    ORDER BY ir.created_at DESC
');
$pendingSent->execute([$userId]);
$sentRequests = $pendingSent->fetchAll();

$pendingReceived = $db->prepare('
    SELECT ir.*, u.name AS sender_name, u.role AS sender_role,
           CASE
               WHEN ir.business_id IS NOT NULL THEN (SELECT business_name FROM businesses WHERE id = ir.business_id)
               WHEN ir.pitch_id IS NOT NULL THEN (SELECT tagline FROM pitches WHERE id = ir.pitch_id)
               ELSE NULL
           END AS context_name
    FROM interest_requests ir
    JOIN users u ON u.id = ir.sender_id
    WHERE ir.receiver_id = ? AND ir.status = \'pending\'
    ORDER BY ir.created_at DESC
');
$pendingReceived->execute([$userId]);
$receivedRequests = $pendingReceived->fetchAll();

json_success([
    'matches' => $allMatches,
    'sent' => $sentRequests,
    'received' => $receivedRequests,
]);
