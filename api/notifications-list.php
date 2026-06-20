<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

$user = require_api_auth();
$userId = (int)$user['id'];

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = min(50, max(1, (int)($_GET['per_page'] ?? 12)));

$countStmt = db()->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ?');
$countStmt->execute([$userId]);
$meta = api_paginate($countStmt, $page, $perPage);

$stmt = db()->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?');
$stmt->execute([$userId, $meta['per_page'], $meta['offset']]);
$notifications = $stmt->fetchAll();

json_success($notifications, $meta);
