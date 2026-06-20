<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = min(50, max(1, (int)($_GET['per_page'] ?? 12)));

$sector       = $_GET['sector'] ?? '';
$investorType = $_GET['investor_type'] ?? '';
$location     = $_GET['location'] ?? '';
$userId       = $_GET['user_id'] ?? '';
$sort         = $_GET['sort'] ?? 'newest';

$where = ["u.role = 'investor'", "u.verification_status = 'verified'"];
$params = [];

if ($sector !== '') {
    $where[] = 'ip.preferred_sectors LIKE ?';
    $params[] = '%"' . $sector . '"%';
}
if ($investorType !== '') {
    $where[] = 'u.account_type = ?';
    $params[] = $investorType;
}
if ($location !== '') {
    $where[] = '(u.province LIKE ? OR u.district LIKE ?)';
    $loc = '%' . $location . '%';
    $params[] = $loc;
    $params[] = $loc;
}
if ($userId !== '') {
    $where[] = 'u.id = ?';
    $params[] = (int)$userId;
}

$whereClause = implode(' AND ', $where);

switch ($sort) {
    case 'ticket_desc': $orderBy = 'ip.ticket_max DESC'; break;
    case 'ticket_asc': $orderBy = 'ip.ticket_min ASC'; break;
    default: $orderBy = 'u.created_at DESC';
}

$countStmt = db()->prepare("SELECT COUNT(*) FROM users u JOIN investor_profiles ip ON u.id = ip.user_id WHERE $whereClause");
$countStmt->execute($params);
$meta = api_paginate($countStmt, $page, $perPage);

$sql = "SELECT u.id, u.name, u.email, u.role, u.account_type, u.phone, u.province, u.district, u.profile_photo, u.verification_status, u.created_at, ip.*
        FROM users u
        JOIN investor_profiles ip ON u.id = ip.user_id
        WHERE $whereClause
        ORDER BY $orderBy
        LIMIT {$meta['per_page']} OFFSET {$meta['offset']}";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$investors = $stmt->fetchAll();

json_success($investors, $meta);
