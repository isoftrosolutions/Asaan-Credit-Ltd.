<?php
require __DIR__ . '/../config/bootstrap.php';

$token = $_GET['token'] ?? '';

if ($token === '') {
    flash_set('error', 'Invalid verification link.');
    redirect('/login');
}

$stmt = db()->prepare('
    SELECT prt.email, u.id 
    FROM password_reset_tokens prt
    JOIN users u ON u.email = prt.email
    WHERE prt.token = ? AND prt.type = ?
    LIMIT 1
');
$stmt->execute([$token, 'email']);
$row = $stmt->fetch();

if (!$row) {
    flash_set('error', 'Invalid or expired verification link.');
    redirect('/login');
}

db()->beginTransaction();

$stmt = db()->prepare('UPDATE users SET email_verified_at = NOW(), verification_status = ? WHERE id = ?');
$stmt->execute(['verified', $row['id']]);

$stmt = db()->prepare('DELETE FROM password_reset_tokens WHERE token = ? AND type = ?');
$stmt->execute([$token, 'email']);

db()->commit();

flash_set('success', 'Email verified successfully! You can now log in.');
redirect('/login');
