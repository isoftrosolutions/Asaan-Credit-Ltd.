<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed', 405);
}

$input = get_json_input();
$email = trim($input['email'] ?? '');
$type  = $input['type'] ?? 'email';

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_error('A valid email is required.');
}

$validTypes = ['email', 'password'];
if (!in_array($type, $validTypes, true)) {
    $type = 'email';
}

$stmt = db()->prepare("SELECT created_at FROM password_reset_tokens WHERE email = ? AND type = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$email, $type]);
$last = $stmt->fetchColumn();
$throttled = $last && (time() - strtotime($last)) < 60;

if ($throttled) {
    json_error('Please wait at least 60 seconds before requesting a new code.', 429);
}

$otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

$stmt = db()->prepare('DELETE FROM password_reset_tokens WHERE email = ? AND type = ?');
$stmt->execute([$email, $type]);

$stmt = db()->prepare("INSERT INTO password_reset_tokens (email, token, type, created_at) VALUES (?, ?, ?, NOW())");
$stmt->execute([$email, reset_token_hash($otp), $type]);

if ($type === 'email') {
    send_email_otp_email($email, $otp);
} else {
    send_password_reset_email($email, $otp);
}

json_success(['message' => 'OTP sent successfully.']);
