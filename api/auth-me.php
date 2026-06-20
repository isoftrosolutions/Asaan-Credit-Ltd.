<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $user = _api_auth_user();
    if (!$user) {
        json_error('Authentication required.', 401);
    }
    $safeFields = ['id', 'name', 'email', 'role', 'account_type', 'phone', 'province', 'district', 'profile_photo', 'verification_status', 'is_premium', 'is_admin', 'usage_goal', 'email_verified_at', 'company_name', 'company_size', 'created_at'];
    $safeUser = array_intersect_key($user, array_flip($safeFields));
    json_success($safeUser);
}

if ($method === 'PUT' || $method === 'POST') {
    $user = require_api_auth();
    $input = get_json_input();
    $allowed = ['name', 'phone', 'province', 'district', 'company_name', 'company_size', 'usage_goal'];
    $updates = [];
    $params = [];
    foreach ($allowed as $field) {
        if (isset($input[$field]) && $input[$field] !== '') {
            $updates[] = "$field = ?";
            $params[] = trim((string)$input[$field]);
        }
    }
    if (!empty($updates)) {
        $params[] = (int)$user['id'];
        $stmt = db()->prepare('UPDATE users SET ' . implode(', ', $updates) . ', updated_at = NOW() WHERE id = ?');
        $stmt->execute($params);
    }
    $stmt = db()->prepare('SELECT id, name, email, role, account_type, phone, province, district, profile_photo, verification_status, is_premium, is_admin, usage_goal, email_verified_at, company_name, company_size, created_at FROM users WHERE id = ?');
    $stmt->execute([(int)$user['id']]);
    json_success($stmt->fetch());
}

json_error('Method not allowed', 405);
