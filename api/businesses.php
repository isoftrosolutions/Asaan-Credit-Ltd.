<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = min(50, max(1, (int)($_GET['per_page'] ?? 12)));

$sectorId   = $_GET['sector_id'] ?? '';
$province   = $_GET['province'] ?? '';
$listingType = $_GET['listing_type'] ?? '';
$priceMin   = $_GET['price_min'] ?? '';
$priceMax   = $_GET['price_max'] ?? '';
$keyword    = $_GET['keyword'] ?? '';
$userId     = $_GET['user_id'] ?? '';
$sort       = $_GET['sort'] ?? 'newest';

$where = ["b.status = 'approved'", 'b.is_hidden = 0'];
$params = [];

if ($sectorId !== '') {
    $where[] = 'b.sector_id = ?';
    $params[] = (int)$sectorId;
}
if ($province !== '') {
    $where[] = 'b.province = ?';
    $params[] = $province;
}
if ($listingType !== '') {
    $where[] = 'b.listing_type = ?';
    $params[] = $listingType;
}
if ($priceMin !== '') {
    $where[] = 'b.asking_price >= ?';
    $params[] = (float)$priceMin;
}
if ($priceMax !== '') {
    $where[] = 'b.asking_price <= ?';
    $params[] = (float)$priceMax;
}
if ($keyword !== '') {
    $where[] = '(b.business_name LIKE ? OR b.description LIKE ? OR b.province LIKE ? OR b.district LIKE ?)';
    $kw = '%' . $keyword . '%';
    $params[] = $kw;
    $params[] = $kw;
    $params[] = $kw;
    $params[] = $kw;
}
if ($userId !== '') {
    $where[] = 'b.user_id = ?';
    $params[] = (int)$userId;
}

$whereClause = implode(' AND ', $where);

switch ($sort) {
    case 'rating': $orderBy = 'b.rating DESC'; break;
    case 'price_low': $orderBy = 'b.asking_price ASC'; break;
    case 'price_high': $orderBy = 'b.asking_price DESC'; break;
    default: $orderBy = 'b.created_at DESC';
}

$countStmt = db()->prepare("SELECT COUNT(*) FROM businesses b WHERE $whereClause");
$countStmt->execute($params);
$meta = api_paginate($countStmt, $page, $perPage);

$sql = "SELECT b.*, s.name as sector_name
        FROM businesses b
        LEFT JOIN sectors s ON b.sector_id = s.id
        WHERE $whereClause
        ORDER BY $orderBy
        LIMIT {$meta['per_page']} OFFSET {$meta['offset']}";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$businesses = $stmt->fetchAll();

json_success($businesses, $meta);
