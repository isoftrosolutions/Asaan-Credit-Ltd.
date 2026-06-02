<?php

require_once __DIR__ . '/../includes/email-service.php';

function smtp_config(): array {
    return email_service()->smtpConfig();
}

function send_mail(string $to, string $subject, string $body): bool {
    return email_service()->sendCustomEmail($to, $subject, $body);
}

function send_mail_smtp(string $to, string $subject, string $body, ?array $smtp = null): bool {
    if ($smtp !== null && isset($smtp['host'])) {
        return email_service()->sendMailSmtpWithConfig($to, $subject, $body, $smtp);
    }
    return email_service()->sendCustomEmail($to, $subject, $body);
}

function send_verification_email(string $to, string $token): bool {
    return email_service()->sendVerificationEmail($to, $token);
}

function send_password_reset_email(string $to, string $otpCode): bool {
    return email_service()->sendPasswordResetEmail($to, $otpCode);
}

function send_welcome_email(string $to, string $userName, string $role): bool {
    return email_service()->sendWelcomeEmail($to, $userName, $role);
}

function send_interest_received_email(string $to, string $userName, string $senderName, string $senderRole, string $listingType, string $listingName, string $message): bool {
    return email_service()->sendInterestReceivedEmail($to, $userName, $senderName, $senderRole, $listingType, $listingName, $message);
}

function send_match_confirmed_email(string $to, string $userName, string $matchedUserName, string $matchedUserRole, string $contextType, string $contextName): bool {
    return email_service()->sendMatchConfirmedEmail($to, $userName, $matchedUserName, $matchedUserRole, $contextType, $contextName);
}

function send_verification_approved_email(string $to, string $userName): bool {
    return email_service()->sendVerificationApprovedEmail($to, $userName);
}

function send_verification_rejected_email(string $to, string $userName, string $reason): bool {
    return email_service()->sendVerificationRejectedEmail($to, $userName, $reason);
}

function get_email_template(string $key): ?array {
    try {
        $stmt = db()->prepare("SELECT subject, body FROM email_templates WHERE template_key = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) return $row;
    } catch (\PDOException $e) {
    }

    $templates = include __DIR__ . '/email_templates.php';
    if (isset($templates[$key])) {
        $tpl = $templates[$key];
        return [
            'subject' => $tpl['subject'] ?? '',
            'body'    => $tpl['body'] ?? $tpl['content_html'] ?? '',
        ];
    }
    return null;
}

function replace_placeholders(string $str, array $vars): string {
    $search = [];
    $replace = [];
    foreach ($vars as $key => $val) {
        $search[] = '{{' . $key . '}}';
        $replace[] = $val;
    }
    return str_replace($search, $replace, $str);
}
