<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class EmailService {

    private static $instance = null;
    private $templates = null;
    private $smtpConfig = null;

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $templatePath = __DIR__ . '/../config/email_templates.php';
        if (file_exists($templatePath)) {
            $this->templates = include $templatePath;
        }
    }

    public function sendVerificationEmail(string $to, string $token): bool {
        if (!self::validateEmail($to)) return false;
        try {
            $stmt = db()->prepare("SELECT name FROM users WHERE email = ?");
            $stmt->execute([$to]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            $userName = $userData['name'] ?? 'User';
        } catch (\Throwable $e) {
        }

        $link = public_base_url() . '/verify-email?token=' . urlencode($token);
        return $this->processAndSend('email_verification', $to, [
            'user_name'         => $userName,
            'verification_link' => $link,
        ]);
    }

    public function sendPasswordResetEmail(string $to, string $token): bool {
        if (!self::validateEmail($to)) return false;
        try {
            $stmt = db()->prepare("SELECT name FROM users WHERE email = ?");
            $stmt->execute([$to]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            $userName = $userData['name'] ?? 'User';
        } catch (\Throwable $e) {
        }

        $link = public_base_url() . '/reset-password?token=' . urlencode($token);
        return $this->processAndSend('password_reset', $to, [
            'user_name' => $userName,
            'reset_link' => $link,
        ]);
    }

    public function sendWelcomeEmail(string $to, string $userName, string $role): bool {
        if (!self::validateEmail($to)) return false;
        return $this->processAndSend('welcome', $to, [
            'user_name'  => $userName,
            'login_url'  => public_base_url(),
            'role'       => ucfirst(str_replace('_', ' ', $role)),
            'user_email' => $to,
        ]);
    }

    public function sendInterestReceivedEmail(string $to, string $userName, string $senderName, string $senderRole, string $listingType, string $listingName, string $message): bool {
        if (!self::validateEmail($to)) return false;
        return $this->processAndSend('interest_received', $to, [
            'user_name'    => $userName,
            'sender_name'  => $senderName,
            'sender_role'  => $senderRole,
            'listing_type' => $listingType,
            'listing_name' => $listingName,
            'message'      => e($message),
            'login_url'    => public_base_url() . '/connections',
        ]);
    }

    public function sendMatchConfirmedEmail(string $to, string $userName, string $matchedUserName, string $matchedUserRole, string $contextType, string $contextName): bool {
        if (!self::validateEmail($to)) return false;
        return $this->processAndSend('match_confirmed', $to, [
            'user_name'         => $userName,
            'matched_user_name' => $matchedUserName,
            'matched_user_role' => $matchedUserRole,
            'context_type'      => $contextType,
            'context_name'      => $contextName,
            'login_url'         => public_base_url() . '/connections',
        ]);
    }

    public function sendVerificationApprovedEmail(string $to, string $userName): bool {
        if (!self::validateEmail($to)) return false;
        return $this->processAndSend('verification_approved', $to, [
            'user_name' => $userName,
            'login_url'  => public_base_url() . '/dashboard',
        ]);
    }

    public function sendVerificationRejectedEmail(string $to, string $userName, string $reason): bool {
        if (!self::validateEmail($to)) return false;
        return $this->processAndSend('verification_rejected', $to, [
            'user_name'       => $userName,
            'rejection_reason' => e($reason),
            'login_url'       => public_base_url() . '/dashboard',
        ]);
    }

    public function sendCustomEmail(string $to, string $subject, string $body, ?string $templateKey = null, ?int $sentBy = null): bool {
        if (!self::validateEmail($to)) return false;
        return $this->sendEmail($to, $subject, $body, $templateKey, $sentBy);
    }

    private function processAndSend(string $templateKey, string $to, array $replacements, ?int $sentBy = null): bool {
        $template = $this->resolveTemplate($templateKey);
        if (!$template) {
            error_log("EmailService: template '$templateKey' not found");
            return false;
        }

        $subject = $template['subject'];
        $body = $template['body'];

        $subject = self::applyPlaceholders($subject, $replacements);
        $body = self::applyPlaceholders($body, $replacements);

        return $this->sendEmail($to, $subject, $body, $templateKey, $sentBy);
    }

    private static function applyPlaceholders(string $str, array $vars): string {
        $search = [];
        $replace = [];
        foreach ($vars as $key => $val) {
            $search[] = '{{' . $key . '}}';
            $replace[] = $val;
        }
        return str_replace($search, $replace, $str);
    }

    private function resolveTemplate(string $key): ?array {
        try {
            $stmt = db()->prepare("SELECT subject, body FROM email_templates WHERE template_key = ? AND is_active = 1 LIMIT 1");
            $stmt->execute([$key]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && isset($row['body'])) return $row;
        } catch (\Throwable $e) {
        }

        if ($this->templates && isset($this->templates[$key])) {
            $tpl = $this->templates[$key];
            $body = $tpl['body'] ?? $tpl['content_html'] ?? null;
            if ($body === null) return null;
            return [
                'subject' => $tpl['subject'] ?? '',
                'body'    => $body,
            ];
        }

        return null;
    }

    private function sendEmail(string $to, string $subject, string $body, ?string $templateKey = null, ?int $sentBy = null): bool {
        $cfg = $this->loadSmtpConfig();

        if (!$cfg['active']) {
            $logDir = STORAGE_PATH . '/mail';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0777, true);
            }
            $filename = $logDir . '/' . date('Y-m-d_H-i-s') . '_' . bin2hex(random_bytes(4)) . '.html';
            $html  = '<h2>To: ' . e($to) . '</h2>';
            $html .= '<h3>Subject: ' . e($subject) . '</h3>';
            $html .= '<hr><div>' . $body . '</div>';
            file_put_contents($filename, $html);
            $this->logEmail($to, $subject, $templateKey, 'sent', null, $sentBy);
            return true;
        }

        $result = $this->sendMailSmtp($to, $subject, $body, $cfg);
        $this->logEmail($to, $subject, $templateKey, $result['success'] ? 'sent' : 'failed', $result['success'] ? null : $result['error'], $sentBy);
        return $result['success'];
    }

    public function sendMailSmtpWithConfig(string $to, string $subject, string $body, array $cfg): bool {
        if (!self::validateEmail($to)) return false;
        $result = $this->sendMailSmtp($to, $subject, $body, $cfg);
        $this->logEmail($to, $subject, null, $result['success'] ? 'sent' : 'failed', $result['success'] ? null : $result['error'], null);
        return $result['success'];
    }

    private function sendMailSmtp(string $to, string $subject, string $body, array $cfg): array {
        require_once __DIR__ . '/../lib/PHPMailer/Exception.php';
        require_once __DIR__ . '/../lib/PHPMailer/PHPMailer.php';
        require_once __DIR__ . '/../lib/PHPMailer/SMTP.php';

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $cfg['host'];
            $mail->Port       = $cfg['port'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $cfg['username'];
            $mail->Password   = $cfg['password'];
            $mail->SMTPSecure = $cfg['encryption'] === 'ssl'
                ? PHPMailer::ENCRYPTION_SMTPS
                : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ];
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($cfg['from_email'], $cfg['from_name']);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = self::htmlToText($body);

            $mail->send();
            return ['success' => true, 'error' => null];
        } catch (PHPMailerException $e) {
            $err = $mail->ErrorInfo;
            error_log('SMTP send failed to ' . $to . ': ' . $err);
            return ['success' => false, 'error' => $err];
        }
    }

    private static function htmlToText(string $html): string {
        $text = str_replace(['</p>', '</div>', '</li>', '</h1>', '</h2>', '</h3>', '</h4>', '</h5>', '</h6>', '<br>', '<br/>', '<br />', '</tr>'], "\n", $html);
        $text = str_replace(['</td>', '</th>'], ' ', $text);
        return trim(strip_tags($text));
    }

    private static function validateEmail(string $email): bool {
        $email = trim($email);
        if ($email === '') return false;
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function logEmail(string $to, string $subject, ?string $templateKey, string $status, ?string $error, ?int $sentBy): void {
        try {
            $stmt = db()->prepare(
                'INSERT INTO email_log (recipient, subject, template_key, status, error, sent_by, sent_at) VALUES (?, ?, ?, ?, ?, ?, NOW())'
            );
            $stmt->execute([$to, $subject, $templateKey, $status, $error, $sentBy]);
        } catch (\Throwable $e) {
            error_log('EmailService: failed to log email: ' . $e->getMessage());
        }
    }

    public function smtpConfig(): array {
        return $this->loadSmtpConfig();
    }

    public function resetSmtpConfig(): void {
        $this->smtpConfig = null;
    }

    private function loadSmtpConfig(): array {
        if ($this->smtpConfig !== null) return $this->smtpConfig;

        $cfg = [
            'host'       => defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com',
            'port'       => defined('SMTP_PORT') ? SMTP_PORT : 587,
            'encryption' => defined('SMTP_ENCRYPTION') ? SMTP_ENCRYPTION : 'tls',
            'username'   => defined('SMTP_USER') ? SMTP_USER : '',
            'password'   => defined('SMTP_PASS') ? SMTP_PASS : '',
            'from_email' => defined('MAIL_FROM') ? MAIL_FROM : 'noreply@asaancapital.com',
            'from_name'  => defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'Asaan Capital Ltd',
            'active'     => (defined('SMTP_USER') && SMTP_USER !== '' && defined('SMTP_PASS') && SMTP_PASS !== ''),
        ];

        try {
            $row = db()->query("SELECT smtp_host, smtp_port, smtp_encryption, smtp_username, smtp_password, from_email, from_name, is_active FROM email_settings WHERE is_active = 1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            if ($row && $row['is_active'] && ($row['smtp_username'] ?? '') !== '' && ($row['smtp_password'] ?? '') !== '') {
                $cfg['host']       = $row['smtp_host'];
                $cfg['port']       = (int)$row['smtp_port'];
                $cfg['encryption'] = $row['smtp_encryption'];
                $cfg['username']   = $row['smtp_username'];
                $cfg['password']   = $row['smtp_password'];
                $cfg['from_email'] = $row['from_email'] ?: $cfg['from_email'];
                $cfg['from_name']  = $row['from_name'] ?: $cfg['from_name'];
                $cfg['active']     = true;
            }
        } catch (\Throwable $e) {
        }

        $this->smtpConfig = $cfg;
        return $cfg;
    }
}

function email_service(): EmailService {
    return EmailService::getInstance();
}
