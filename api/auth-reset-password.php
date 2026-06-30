<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed', 405);
}

$input = get_json_input();
$email    = trim($input['email'] ?? '');
$otp      = trim($input['otp'] ?? '');
$password = $input['password'] ?? '';

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_error('A valid email is required.');
}
if ($otp === '') {
    json_error('OTP code is required.');
}
if (strlen($password) < 8) {
    json_error('Password must be at least 8 characters.');
}
if (!preg_match('/[A-Z]/', $password)) {
    json_error('Password must include at least one uppercase letter.');
}
if (!preg_match('/[0-9]/', $password)) {
    json_error('Password must include at least one number.');
}
if (!preg_match('/[^A-Za-z0-9]/', $password)) {
    json_error('Password must include at least one special character.');
}

$stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if (!$stmt->fetch()) {
    json_error('No account found with that email.', 404);
}

$stmt = db()->prepare("SELECT token, created_at FROM password_reset_tokens WHERE email = ? AND type = ? LIMIT 1");
$stmt->execute([$email, 'password']);
$row = $stmt->fetch();

if (!$row) {
    json_error('No reset request found. Please request a new code.', 400);
}

$OTP_EXPIRY = 300;
if (time() > strtotime($row['created_at']) + $OTP_EXPIRY) {
    db()->prepare("DELETE FROM password_reset_tokens WHERE email = ? AND type = ?")->execute([$email, 'password']);
    json_error('Code has expired. Please request a new one.', 400);
}

if (!hash_equals($row['token'], reset_token_hash($otp))) {
    json_error('Incorrect verification code.', 400);
}

$hash = password_hash($password, PASSWORD_BCRYPT);

db()->beginTransaction();
$stmt = db()->prepare('UPDATE users SET password = ? WHERE email = ?');
$stmt->execute([$hash, $email]);
db()->prepare("DELETE FROM password_reset_tokens WHERE email = ? AND type = ?")->execute([$email, 'password']);
db()->prepare('UPDATE users SET failed_login_attempts = 0, locked_until = NULL WHERE email = ?')->execute([$email]);
db()->commit();

$stmt = db()->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$userData = $stmt->fetch();
if ($userData) {
    db()->prepare("DELETE FROM user_api_tokens WHERE user_id = ?")->execute([(int)$userData['id']]);
}

json_success(['message' => 'Password reset successfully.']);
