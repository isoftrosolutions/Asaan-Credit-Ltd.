<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

$user = require_api_auth();
if (empty($user['is_admin'])) {
    json_error('Forbidden.', 403);
}

$queries = [
    'total_users' => 'SELECT COUNT(*) FROM users',
    'total_verified' => "SELECT COUNT(*) FROM users WHERE verification_status = 'verified'",
    'total_businesses' => 'SELECT COUNT(*) FROM businesses',
    'total_pitches' => 'SELECT COUNT(*) FROM pitches',
    'total_franchises' => 'SELECT COUNT(*) FROM franchises',
    'total_interest_requests' => 'SELECT COUNT(*) FROM interest_requests',
    'total_matches' => 'SELECT COUNT(*) FROM matches',
    'total_reports' => "SELECT COUNT(*) FROM reports WHERE status = 'open'",
];

$stats = [];
foreach ($queries as $key => $sql) {
    $stats[$key] = (int)db()->query($sql)->fetchColumn();
}

$stats['recent_signups'] = (int)db()->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
$stats['pending_verifications'] = (int)db()->query("SELECT COUNT(*) FROM verification_documents WHERE status = 'pending'")->fetchColumn();
$stats['pending_businesses'] = (int)db()->query("SELECT COUNT(*) FROM businesses WHERE status = 'pending'")->fetchColumn();
$stats['pending_pitches'] = (int)db()->query("SELECT COUNT(*) FROM pitches WHERE is_published = 0")->fetchColumn();

json_success($stats);
