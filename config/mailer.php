<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * Load SMTP credentials from the database.
 * Falls back to config.php constants if the DB row has empty credentials.
 */
function smtp_config(): array {
    static $cfg = null;
    if ($cfg !== null) return $cfg;

    $cfg = [
        'host'       => SMTP_HOST,
        'port'       => SMTP_PORT,
        'encryption' => SMTP_ENCRYPTION,
        'username'   => SMTP_USER,
        'password'   => SMTP_PASS,
        'from_email' => MAIL_FROM,
        'from_name'  => MAIL_FROM_NAME,
        'active'     => SMTP_USER !== '' && SMTP_PASS !== '',
    ];

    try {
        $row = db()->query("SELECT smtp_host, smtp_port, smtp_encryption, smtp_username, smtp_password, from_email, from_name, is_active FROM email_settings WHERE is_active = 1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        if ($row && $row['is_active'] && $row['smtp_username'] !== '' && $row['smtp_password'] !== '') {
            $cfg['host']       = $row['smtp_host'];
            $cfg['port']       = (int)$row['smtp_port'];
            $cfg['encryption'] = $row['smtp_encryption'];
            $cfg['username']   = $row['smtp_username'];
            $cfg['password']   = $row['smtp_password'];
            $cfg['from_email'] = $row['from_email'] ?: $cfg['from_email'];
            $cfg['from_name']  = $row['from_name'] ?: $cfg['from_name'];
            $cfg['active']     = true;
        }
    } catch (\PDOException $e) {
        // Table may not exist yet — fall back to config constants
    }

    return $cfg;
}

function send_mail(string $to, string $subject, string $body): bool {
    $smtp = smtp_config();

    if (!$smtp['active']) {
        // Fall back to log driver when no SMTP credentials are configured
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

    return send_mail_smtp($to, $subject, $body, $smtp);
}

function send_mail_smtp(string $to, string $subject, string $body, ?array $smtp = null): bool {
    if ($smtp === null) $smtp = smtp_config();

    require_once __DIR__ . '/../lib/PHPMailer/Exception.php';
    require_once __DIR__ . '/../lib/PHPMailer/PHPMailer.php';
    require_once __DIR__ . '/../lib/PHPMailer/SMTP.php';

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $smtp['host'];
        $mail->Port       = $smtp['port'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp['username'];
        $mail->Password   = $smtp['password'];
        $mail->SMTPSecure = $smtp['encryption'] === 'ssl'
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($smtp['from_email'], $smtp['from_name']);
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
    $stmt = db()->prepare("SELECT name FROM users WHERE email = ?");
    $stmt->execute([$to]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    $userName = $userData['name'] ?? 'User';

    $tpl = get_email_template('email_verification');
    $link = public_base_url() . '/verify-email?token=' . urlencode($token);

    $subject = replace_placeholders($tpl['subject'], ['user_name' => $userName]);
    $body = replace_placeholders($tpl['body'], [
        'user_name'         => $userName,
        'verification_link' => $link,
    ]);

    send_mail($to, $subject, $body);
}

function send_password_reset_email(string $to, string $token): void {
    $stmt = db()->prepare("SELECT name FROM users WHERE email = ?");
    $stmt->execute([$to]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    $userName = $userData['name'] ?? 'User';

    $tpl = get_email_template('password_reset');
    $link = public_base_url() . '/reset-password?token=' . urlencode($token);

    $subject = replace_placeholders($tpl['subject'], ['user_name' => $userName]);
    $body = replace_placeholders($tpl['body'], [
        'user_name' => $userName,
        'reset_link' => $link,
    ]);

    send_mail($to, $subject, $body);
}

function send_welcome_email(string $to, string $userName, string $role): void {
    $tpl = get_email_template('welcome');
    $subject = replace_placeholders($tpl['subject'], ['user_name' => $userName]);
    $body = replace_placeholders($tpl['body'], [
        'user_name' => $userName,
        'login_url'  => public_base_url(),
        'role'       => ucfirst(str_replace('_', ' ', $role)),
        'user_email' => $to,
    ]);
    send_mail($to, $subject, $body);
}

function send_interest_received_email(string $to, string $userName, string $senderName, string $senderRole, string $listingType, string $listingName, string $message): void {
    $tpl = get_email_template('interest_received');
    $subject = replace_placeholders($tpl['subject'], ['listing_type' => $listingType]);
    $body = replace_placeholders($tpl['body'], [
        'user_name'    => $userName,
        'sender_name'  => $senderName,
        'sender_role'  => $senderRole,
        'listing_type' => $listingType,
        'listing_name' => $listingName,
        'message'      => e($message),
        'login_url'    => public_base_url() . '/connections',
    ]);
    send_mail($to, $subject, $body);
}

function send_match_confirmed_email(string $to, string $userName, string $matchedUserName, string $matchedUserRole, string $contextType, string $contextName): void {
    $tpl = get_email_template('match_confirmed');
    $subject = replace_placeholders($tpl['subject'], []);
    $body = replace_placeholders($tpl['body'], [
        'user_name'         => $userName,
        'matched_user_name' => $matchedUserName,
        'matched_user_role' => $matchedUserRole,
        'context_type'      => $contextType,
        'context_name'      => $contextName,
        'login_url'         => public_base_url() . '/connections',
    ]);
    send_mail($to, $subject, $body);
}

function send_verification_approved_email(string $to, string $userName): void {
    $tpl = get_email_template('verification_approved');
    $subject = replace_placeholders($tpl['subject'], []);
    $body = replace_placeholders($tpl['body'], [
        'user_name' => $userName,
        'login_url'  => public_base_url() . '/dashboard',
    ]);
    send_mail($to, $subject, $body);
}

function send_verification_rejected_email(string $to, string $userName, string $reason): void {
    $tpl = get_email_template('verification_rejected');
    $subject = replace_placeholders($tpl['subject'], []);
    $body = replace_placeholders($tpl['body'], [
        'user_name'       => $userName,
        'rejection_reason' => e($reason),
        'login_url'       => public_base_url() . '/dashboard',
    ]);
    send_mail($to, $subject, $body);
}

/**
 * Get an email template by key — first from DB, fallback to file.
 */
function get_email_template(string $key): ?array {
    try {
        $stmt = db()->prepare("SELECT subject, body FROM email_templates WHERE template_key = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) return $row;
    } catch (\PDOException $e) {
        // Table may not exist yet
    }

    $templates = include __DIR__ . '/email_templates.php';
    return $templates[$key] ?? null;
}

/**
 * Replace {{placeholders}} in a string with provided values.
 */
function replace_placeholders(string $str, array $vars): string {
    $search = [];
    $replace = [];
    foreach ($vars as $key => $val) {
        $search[] = '{{' . $key . '}}';
        $replace[] = $val;
    }
    return str_replace($search, $replace, $str);
}
