<?php
require __DIR__ . '/../config/bootstrap.php';

$stmt = db()->prepare("SELECT * FROM pages WHERE slug = 'contact' AND is_active = 1 LIMIT 1");
$stmt->execute();
$page = $stmt->fetch();

if (!$page) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$pageTitle = $page['title'] . ' — ' . APP_NAME_LONG;
$pageDescription = $page['meta_description'] ?? $page['title'];
$forcePublicHeader = true;

$formSent = false;
$formError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $honeypot = trim($_POST['website'] ?? '');
    if ($honeypot !== '') {
        redirect('/contact');
    }

    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name && filter_var($email, FILTER_VALIDATE_EMAIL) && $subject && $message) {
        try {
            db()->prepare('INSERT INTO contact_messages (name, email, subject, message, status, ip_address, user_agent, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())')
                ->execute([
                    $name,
                    $email,
                    $subject,
                    $message,
                    'new',
                    $_SERVER['REMOTE_ADDR'] ?? null,
                    mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
                ]);
        } catch (Throwable $e) {
            // Keep email delivery as the fallback if the contact_messages table has not been migrated yet.
        }

        $body = "Name: $name\nEmail: $email\n\n$message";
        $sent = send_mail('info@asaancapital.com', "Contact Form: $subject", $body);
        if ($sent) {
            $formSent = true;
        } else {
            $formError = 'Failed to send. Please try again later.';
        }
    } else {
        $formError = 'All fields are required and email must be valid.';
    }
}

require __DIR__ . '/../includes/header.php';
?>
<main class="pub-page">
  <?php
  $html = $page['content_html'];

  if ($formSent) {
      $html = str_replace(
          '</form>',
          '<div style="padding:1rem;background:rgba(16,185,129,.1);color:var(--dash-success);border-radius:var(--radius-md);margin-bottom:1rem;font-weight:600;">Thank you! Your message has been sent.</div></form>',
          $html
      );
  } elseif ($formError) {
      $html = str_replace(
          '</form>',
          '<div style="padding:1rem;background:rgba(239,68,68,.1);color:var(--color-error);border-radius:var(--radius-md);margin-bottom:1rem;font-weight:600;">' . e($formError) . '</div></form>',
          $html
      );
  }

  $html = str_replace('{{CSRF_TOKEN}}', csrf_token(), $html);

  echo $html;
  ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
