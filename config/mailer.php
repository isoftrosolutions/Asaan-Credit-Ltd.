<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

function send_mail(string $to, string $subject, string $body): bool {
    // 'smtp' falls back to 'log' when no credentials are configured yet, so
    // local/dev environments keep working without an SMTP account.
    $driver = MAIL_DRIVER;
    if ($driver === 'smtp' && (SMTP_USER === '' || SMTP_PASS === '')) {
        $driver = 'log';
    }

    if ($driver === 'smtp') {
        return send_mail_smtp($to, $subject, $body);
    }

    if ($driver === 'log') {
        $logDir = STORAGE_PATH . '/mail';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        $filename = $logDir . '/' . date('Y-m-d_H-i-s') . '_' . bin2hex(random_bytes(4)) . '.html';
        $html  = '<h2>To: ' . e($to) . '</h2>';
        $html .= '<h3>Subject: ' . e($subject) . '</h3>';
        $html .= '<hr><div>' . $body . '</div>';
        file_put_contents($filename, $html);
        return true;
    }

    // 'mail' / fallback: PHP's built-in mail()
    $headers  = 'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM . '>' . "\r\n";
    $headers .= 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";
    return mail($to, $subject, $body, $headers);
}

function send_mail_smtp(string $to, string $subject, string $body): bool {
    require_once __DIR__ . '/../lib/PHPMailer/Exception.php';
    require_once __DIR__ . '/../lib/PHPMailer/PHPMailer.php';
    require_once __DIR__ . '/../lib/PHPMailer/SMTP.php';

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->Port       = SMTP_PORT;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = SMTP_ENCRYPTION === 'ssl'
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $body));

        $mail->send();
        return true;
    } catch (PHPMailerException $e) {
        error_log('SMTP send failed to ' . $to . ': ' . $mail->ErrorInfo);
        return false;
    }
}

function send_verification_email(string $to, string $token): void {
    $link = public_base_url() . '/verify-email?token=' . urlencode($token);
    $body  = '<p>Welcome to ' . e(APP_NAME) . '!</p>';
    $body .= '<p>Please confirm your email address by clicking the link below:</p>';
    $body .= '<p><a href="' . e($link) . '">' . e($link) . '</a></p>';
    $body .= '<p style="color:#888;font-size:13px;">If you did not create this account, you can ignore this email.</p>';
    send_mail($to, 'Verify your email address', $body);
}

function send_password_reset_email(string $to, string $token): void {
    $link = public_base_url() . '/reset-password?token=' . urlencode($token);
    $body  = '<p>We received a request to reset your password.</p>';
    $body .= '<p>Click the link below to choose a new one. This link expires in 24 hours:</p>';
    $body .= '<p><a href="' . e($link) . '">' . e($link) . '</a></p>';
    $body .= '<p style="color:#888;font-size:13px;">If you did not request this, you can safely ignore this email — your password will not change.</p>';
    send_mail($to, 'Reset your password', $body);
}
