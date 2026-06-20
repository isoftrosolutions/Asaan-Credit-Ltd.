<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed', 405);
}

$input = get_json_input();
$email = trim($input['email'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_error('A valid email is required.');
}

$stmt = db()->prepare('SELECT id, name FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    json_success(['message' => 'If that email is registered, a reset code has been sent.']);
}

$stmt = db()->prepare("SELECT created_at FROM password_reset_tokens WHERE email = ? AND type = 'password' ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$email]);
$last = $stmt->fetchColumn();
$throttled = $last && (time() - strtotime($last)) < 60;

if ($throttled) {
    json_success(['message' => 'If that email is registered, a reset code has been sent.']);
}

$otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

$stmt = db()->prepare("DELETE FROM password_reset_tokens WHERE email = ? AND type = 'password'");
$stmt->execute([$email]);

$stmt = db()->prepare("INSERT INTO password_reset_tokens (email, token, type, created_at) VALUES (?, ?, 'password', ?)");
$stmt->execute([$email, reset_token_hash($otp), date('Y-m-d H:i:s')]);

send_password_reset_email($email, $otp);

json_success(['message' => 'If that email is registered, a reset code has been sent.']);
