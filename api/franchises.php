<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = min(50, max(1, (int)($_GET['per_page'] ?? 12)));

$sectorId = $_GET['sector_id'] ?? '';
$invMin   = $_GET['inv_min'] ?? '';
$invMax   = $_GET['inv_max'] ?? '';
$userId   = $_GET['user_id'] ?? '';
$sort     = $_GET['sort'] ?? 'newest';

$where = ['f.is_published = 1', 'f.is_hidden = 0'];
$params = [];

if ($sectorId !== '') {
    $where[] = 'f.sector_id = ?';
    $params[] = (int)$sectorId;
}
if ($invMin !== '') {
    $where[] = 'f.total_investment_max >= ?';
    $params[] = (float)$invMin;
}
if ($invMax !== '') {
    $where[] = 'f.total_investment_min <= ?';
    $params[] = (float)$invMax;
}
if ($userId !== '') {
    $where[] = 'f.user_id = ?';
    $params[] = (int)$userId;
}

$whereClause = implode(' AND ', $where);

switch ($sort) {
    case 'rating': $orderBy = 'f.rating DESC'; break;
    case 'inv_asc': $orderBy = 'f.total_investment_min ASC'; break;
    case 'inv_desc': $orderBy = 'f.total_investment_max DESC'; break;
    default: $orderBy = 'f.created_at DESC';
}

$countStmt = db()->prepare("SELECT COUNT(*) FROM franchises f WHERE $whereClause");
$countStmt->execute($params);
$meta = api_paginate($countStmt, $page, $perPage);

$sql = "SELECT f.*, s.name as sector_name
        FROM franchises f
        LEFT JOIN sectors s ON f.sector_id = s.id
        WHERE $whereClause
        ORDER BY $orderBy
        LIMIT {$meta['per_page']} OFFSET {$meta['offset']}";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$franchises = $stmt->fetchAll();

json_success($franchises, $meta);
