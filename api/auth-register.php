<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed', 405);
}

$input = get_json_input();

$name         = trim($input['name'] ?? '');
$email        = trim($input['email'] ?? '');
$password     = $input['password'] ?? '';
$phone        = trim($input['phone'] ?? '');
$role         = $input['role'] ?? '';
$accountType  = $input['account_type'] ?? 'individual';
$company      = trim($input['company'] ?? '');
$province     = trim($input['province'] ?? '');
$district     = trim($input['district'] ?? '');
$goal         = $input['goal'] ?? '';
$size         = $input['size'] ?? '';

if ($name === '') {
    json_error('Full name is required.');
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_error('A valid email is required.');
}
if (strlen($password) < 8) {
    json_error('Password must be at least 8 characters.');
}

$validRoles = ['investor', 'business_owner', 'entrepreneur', 'franchisor', 'advisor'];
$role = in_array($role, $validRoles, true) ? $role : 'investor';

$validGoals = ['buy', 'sell', 'raise', 'invest', 'franchise', 'advisory'];
if ($goal !== '' && !in_array($goal, $validGoals, true)) {
    $goal = '';
}

try {
    $db = db();
    $db->beginTransaction();

    $stmt = $db->prepare("INSERT INTO users (name, email, password, account_type, role, company_name, phone, province, district, verification_status, email_verified_at, company_size, usage_goal, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'unverified', NULL, ?, ?, NOW(), NOW())");
    $stmt->execute([
        $name, $email, password_hash($password, PASSWORD_BCRYPT),
        $accountType, $role, $company ?: null, $phone ?: null,
        $province ?: null, $district ?: null,
        $size ?: null, $goal ?: null
    ]);
    $userId = (int)$db->lastInsertId();

    $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $db->prepare('DELETE FROM password_reset_tokens WHERE email = ? AND type = ?')->execute([$email, 'email']);
    $db->prepare('INSERT INTO password_reset_tokens (email, token, type, created_at) VALUES (?, ?, ?, NOW())')->execute([$email, reset_token_hash($otp), 'email']);

    $token = generate_api_token();
    $db->prepare('INSERT INTO user_api_tokens (user_id, token, name, created_at) VALUES (?, ?, ?, NOW())')->execute([$userId, hash('sha256', $token), 'mobile']);

    $db->commit();

    send_email_otp_email($email, $otp);

    $stmt = $db->prepare('SELECT id, name, email, role, account_type, phone, province, district, profile_photo, verification_status, is_premium, is_admin, usage_goal, created_at FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    json_success([
        'user' => $user,
        'token' => $token,
        'email_otp_sent' => true,
    ]);
} catch (\PDOException $e) {
    if ($db->inTransaction()) $db->rollBack();
    if ($e->getCode() == 23000) {
        json_error('This email is already registered.', 409);
    }
    json_error('Registration failed. Please try again.', 500);
}
