<?php
require __DIR__ . '/../config/bootstrap.php';

$pageTitle = 'Contact Us — ' . APP_NAME_LONG;
$pageDescription = 'Get in touch with Asaan Capital Ltd. Send us a message and we\'ll get back to you.';
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
  <div class="pub-wrap-narrow" style="padding-top:var(--space-6);padding-bottom:var(--space-8);">

    <div class="breadcrumbs pub-text" style="margin-bottom:var(--space-5);">
      <a href="/" style="color:var(--dash-ink-soft);text-decoration:none;">Home</a>
      <span style="margin:0 0.5rem;">/</span>
      <span>Contact Us</span>
    </div>

    <h1 class="pub-h1" style="margin-bottom:var(--space-6);">Contact Us</h1>

    <div class="pub-grid cols-2">
      <div>
        <?php if ($formSent): ?>
          <div class="contact-alert success">Thank you! Your message has been sent.</div>
        <?php elseif ($formError): ?>
          <div class="contact-alert error"><?= e($formError) ?></div>
        <?php endif; ?>

        <form method="post" action="/contact">
          <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
          <div style="display:none;"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
          <div class="input-group" style="margin-bottom:var(--space-4);">
            <label>Your Name</label>
            <input type="text" name="name" class="input" required>
          </div>
          <div class="input-group" style="margin-bottom:var(--space-4);">
            <label>Email</label>
            <input type="email" name="email" class="input" required>
          </div>
          <div class="input-group" style="margin-bottom:var(--space-4);">
            <label>Subject</label>
            <input type="text" name="subject" class="input" required>
          </div>
          <div class="input-group" style="margin-bottom:var(--space-4);">
            <label>Message</label>
            <textarea name="message" class="input" rows="5" required></textarea>
          </div>
          <button type="submit" class="btn btn-primary" style="font-size:1rem;padding:0.75rem 2rem;">Send Message</button>
        </form>
      </div>

      <div class="contact-info-stack">
        <div class="contact-info-card">
          <h3>Office</h3>
          <p>Madhyapur Thimi Municipality-9<br>Bhaktapur, Nepal</p>
        </div>
        <div class="contact-info-card">
          <h3>Phone</h3>
          <p>+977-9848714990<br>+977-982000470</p>
        </div>
        <div class="contact-info-card">
          <h3>Email</h3>
          <p><a href="mailto:info@asaancapital.com">info@asaancapital.com</a></p>
        </div>
        <div class="contact-info-card">
          <h3>Hours</h3>
          <p>Sunday – Friday<br>9:00 AM – 5:00 PM NPT</p>
        </div>
      </div>
    </div>

  </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
