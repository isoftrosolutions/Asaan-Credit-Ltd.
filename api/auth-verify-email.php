<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed', 405);
}

$input = get_json_input();
$email = trim($input['email'] ?? '');
$otp   = trim($input['otp'] ?? '');

if ($email === '' || $otp === '') {
    json_error('Email and OTP are required.');
}

$stmt = db()->prepare('SELECT prt.token, prt.created_at, u.id as user_id FROM password_reset_tokens prt JOIN users u ON u.email = prt.email WHERE prt.email = ? AND prt.type = ? LIMIT 1');
$stmt->execute([$email, 'email']);
$row = $stmt->fetch();

if (!$row) {
    json_error('No verification request found. Please request a new code.', 400);
}

$OTP_EXPIRY = 300;
if (time() > strtotime($row['created_at']) + $OTP_EXPIRY) {
    db()->prepare("DELETE FROM password_reset_tokens WHERE email = ? AND type = ?")->execute([$email, 'email']);
    json_error('Code has expired. Please request a new one.', 400);
}

if (!hash_equals($row['token'], reset_token_hash($otp))) {
    json_error('Incorrect verification code.', 400);
}

db()->beginTransaction();

$stmt = db()->prepare('UPDATE users SET email_verified_at = NOW(), verification_status = ? WHERE id = ?');
$stmt->execute(['verified', $row['user_id']]);

db()->prepare("DELETE FROM password_reset_tokens WHERE email = ? AND type = ?")->execute([$email, 'email']);

db()->commit();

json_success(['message' => 'Email verified successfully.']);
