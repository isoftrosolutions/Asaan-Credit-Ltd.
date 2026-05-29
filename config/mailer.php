<?php
function send_mail(string $to, string $subject, string $body): bool {
    if (MAIL_DRIVER === 'log') {
        $logDir = STORAGE_PATH . '/mail';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        $filename = $logDir . '/' . date('Y-m-d_H-i-s') . '_' . bin2hex(random_bytes(4)) . '.html';
        $html = '<h2>To: ' . e($to) . '</h2>';
        $html .= '<h3>Subject: ' . e($subject) . '</h3>';
        $html .= '<hr><div>' . $body . '</div>';
        file_put_contents($filename, $html);
        return true;
    }
    $headers = 'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM . '>' . "\r\n";
    $headers .= 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";
    return mail($to, $subject, $body, $headers);
}

function send_verification_email(string $to, string $token): void {
    $link = APP_URL . '/verify-email?token=' . urlencode($token);
    $body = '<p>Welcome to ' . APP_NAME . '!</p><p>Click the link below to verify your email:</p>';
    $body .= '<p><a href="' . $link . '">' . $link . '</a></p>';
    send_mail($to, 'Verify your email address', $body);
}

function send_password_reset_email(string $to, string $token): void {
    $link = APP_URL . '/reset-password?token=' . urlencode($token);
    $body = '<p>Click the link below to reset your password:</p>';
    $body .= '<p><a href="' . $link . '">' . $link . '</a></p>';
    send_mail($to, 'Reset your password', $body);
}
