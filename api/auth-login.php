<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed', 405);
}

$input = get_json_input();
$email    = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if ($email === '' || $password === '') {
    json_error('Email and password are required.');
}

$stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    json_error('Invalid email or password.', 401);
}

if (!empty($user['is_suspended'])) {
    json_error('Your account has been suspended. Contact support.', 403);
}

if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
    $minutes = ceil((strtotime($user['locked_until']) - time()) / 60);
    json_error('Account temporarily locked. Try again in ' . $minutes . ' minute(s).', 423);
}

if (!password_verify($password, $user['password'])) {
    $attempts = (int)$user['failed_login_attempts'] + 1;
    $lockUntil = null;
    if ($attempts >= 5) {
        $lockUntil = date('Y-m-d H:i:s', time() + 900);
    }
    $stmt = db()->prepare('UPDATE users SET failed_login_attempts = ?, locked_until = ? WHERE id = ?');
    $stmt->execute([$attempts, $lockUntil, $user['id']]);
    json_error('Invalid email or password.', 401);
}

$stmt = db()->prepare('UPDATE users SET failed_login_attempts = 0, locked_until = NULL, last_login_at = NOW() WHERE id = ?');
$stmt->execute([$user['id']]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
session_regenerate_id(true);
unset($user['password']);
$_SESSION['user'] = $user;

$token = generate_api_token();
$hashedToken = hash('sha256', $token);
$stmt = db()->prepare('INSERT INTO user_api_tokens (user_id, token, name, created_at) VALUES (?, ?, ?, NOW())');
$stmt->execute([$user['id'], $hashedToken, 'mobile']);

$safeFields = ['id', 'name', 'email', 'role', 'account_type', 'phone', 'province', 'district', 'profile_photo', 'verification_status', 'is_premium', 'is_admin', 'usage_goal', 'email_verified_at', 'created_at'];
$safeUser = array_intersect_key($user, array_flip($safeFields));

json_success([
    'user' => $safeUser,
    'token' => $token,
]);
