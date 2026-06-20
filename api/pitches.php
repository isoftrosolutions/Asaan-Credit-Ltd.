<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = min(50, max(1, (int)($_GET['per_page'] ?? 12)));

$sectorId = $_GET['sector_id'] ?? '';
$stage    = $_GET['stage'] ?? '';
$fundMin  = $_GET['fund_min'] ?? '';
$fundMax  = $_GET['fund_max'] ?? '';
$userId   = $_GET['user_id'] ?? '';
$sort     = $_GET['sort'] ?? 'newest';

$where = ['p.is_published = 1', 'p.is_hidden = 0'];
$params = [];

if ($sectorId !== '') {
    $where[] = 'p.sector_id = ?';
    $params[] = (int)$sectorId;
}
if ($stage !== '') {
    $where[] = 'p.stage = ?';
    $params[] = $stage;
}
if ($fundMin !== '') {
    $where[] = 'p.funding_amount >= ?';
    $params[] = (float)$fundMin;
}
if ($fundMax !== '') {
    $where[] = 'p.funding_amount <= ?';
    $params[] = (float)$fundMax;
}
if ($userId !== '') {
    $where[] = 'p.user_id = ?';
    $params[] = (int)$userId;
}

$whereClause = implode(' AND ', $where);

switch ($sort) {
    case 'fund_asc': $orderBy = 'p.funding_amount ASC'; break;
    case 'fund_desc': $orderBy = 'p.funding_amount DESC'; break;
    default: $orderBy = 'p.created_at DESC';
}

$countStmt = db()->prepare("SELECT COUNT(*) FROM pitches p WHERE $whereClause");
$countStmt->execute($params);
$meta = api_paginate($countStmt, $page, $perPage);

$sql = "SELECT p.*, s.name as sector_name, u.name as user_name, u.profile_photo
        FROM pitches p
        LEFT JOIN sectors s ON p.sector_id = s.id
        JOIN users u ON p.user_id = u.id
        WHERE $whereClause
        ORDER BY $orderBy
        LIMIT {$meta['per_page']} OFFSET {$meta['offset']}";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$pitches = $stmt->fetchAll();

json_success($pitches, $meta);
