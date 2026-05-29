<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();

$user = current_user();
$role = $user['role'];

$roleDashboards = [
    'investor' => __DIR__ . '/../investor/dashboard.php',
    'business_owner' => __DIR__ . '/../business/dashboard.php',
    'entrepreneur' => __DIR__ . '/../entrepreneur/dashboard.php',
    'franchisor' => __DIR__ . '/../franchise/dashboard.php',
    'advisor' => __DIR__ . '/../advisor/dashboard.php',
];

if (!empty($user['is_admin'])) {
    require __DIR__ . '/../admin/dashboard.php';
    exit;
}

$dashboard = $roleDashboards[$role] ?? null;
if ($dashboard && file_exists($dashboard)) {
    require $dashboard;
    exit;
}

http_response_code(404);
echo '<h1>Dashboard not found for role: ' . e($role) . '</h1>';
