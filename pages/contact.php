<?php
require __DIR__ . '/../config/bootstrap.php';

$old = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $honey   = trim($_POST['website'] ?? ''); // spam honeypot

    if ($honey !== '') {
        // Silently drop bot submissions.
        flash_set('success', 'Thanks! Your message has been sent.');
        redirect('/contact');
    }

    $errors = [];
    if ($name === '') $errors[] = 'Please enter your name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (mb_strlen($message) < 10) $errors[] = 'Please enter a message (at least 10 characters).';

    if (!$errors) {
        $body  = '<p><strong>Name:</strong> ' . e($name) . '</p>';
        $body .= '<p><strong>Email:</strong> ' . e($email) . '</p>';
        $body .= '<p><strong>Subject:</strong> ' . e($subject !== '' ? $subject : '(none)') . '</p>';
        $body .= '<hr><p>' . nl2br(e($message)) . '</p>';
        send_mail('hello@asaancapital.com', 'Contact form: ' . ($subject !== '' ? $subject : 'New message'), $body);
        flash_set('success', "Thanks! Your message has been sent — we'll get back to you soon.");
        redirect('/contact');
    }

    flash_set('error', implode(' ', $errors));
    $old = compact('name', 'email', 'subject', 'message');
}

$pageTitle = 'Contact Us — ' . APP_NAME;
$pageDescription = 'Get in touch with ' . APP_NAME . '. Reach our team for support, partnerships, and media enquiries.';
$forcePublicHeader = true;
$breadcrumbSchema = '<script type="application/ld+json">{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem","position":1,"name":"Home","item":"'.APP_URL.'/"},
    {"@type": "ListItem","position":2,"name":"Contact Us","item":"'.APP_URL.'/contact"}
  ]
}</script>';
require __DIR__ . '/../includes/header.php';
?>
<?= $breadcrumbSchema ?>
<style>
@media (min-width:768px){ .ct-grid{ grid-template-columns:1.3fr 1fr; } }
@media (max-width:767px){ .ct-grid{ grid-template-columns:1fr; } }
.ct-input{ width:100%; padding:11px 13px; border:1px solid #dbc0bf; border-radius:8px; font-size:15px; font-family:Inter,sans-serif; color:#1c1b1b; background:#fff; box-sizing:border-box; }
.ct-input:focus{ outline:none; border-color:#6B1D22; box-shadow:0 0 0 3px rgba(107,29,34,0.1); }
.ct-label{ display:block; font-size:13px; font-weight:600; color:#554242; margin-bottom:6px; font-family:Inter,sans-serif; }
</style>
<main>
<section style="padding:48px 0 24px;background:#ffffff;">
  <div style="max-width:1000px;margin:0 auto;padding:0 24px;">
    <div class="breadcrumbs" style="font-size:13px;color:#887271;font-family:Inter,sans-serif;margin-bottom:16px;">
      <a href="<?= APP_URL ?>/" style="color:#887271;text-decoration:none;">Home</a> <span style="margin:0 6px;">/</span>
      <span style="color:#554242;">Contact Us</span>
    </div>
    <h1 style="font-size:36px;line-height:44px;font-weight:800;letter-spacing:-0.02em;color:#6B1D22;font-family:Montserrat,sans-serif;margin:0 0 12px;">Contact Us</h1>
    <p style="font-size:16px;line-height:26px;color:#554242;font-family:Inter,sans-serif;margin:0;max-width:640px;">Have a question, partnership idea, or need a hand getting started? Send us a message and our team will get back to you.</p>
  </div>
</section>

<section style="padding:8px 0 56px;background:#ffffff;">
  <div style="max-width:1000px;margin:0 auto;padding:0 24px;">
    <div class="ct-grid" style="display:grid;gap:32px;align-items:start;">
      <!-- Form -->
      <div style="background:#fff;border-radius:16px;border:1px solid #dbc0bf4d;border-top:4px solid #6B1D22;padding:28px;box-shadow:0 4px 16px rgba(0,0,0,0.04);">
        <form method="post" action="<?= APP_URL ?>/contact">
          <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
          <div style="position:absolute;left:-9999px;" aria-hidden="true">
            <label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div>
              <label class="ct-label" for="ct-name">Name</label>
              <input class="ct-input" id="ct-name" type="text" name="name" value="<?= e($old['name']) ?>" required>
            </div>
            <div>
              <label class="ct-label" for="ct-email">Email</label>
              <input class="ct-input" id="ct-email" type="email" name="email" value="<?= e($old['email']) ?>" required>
            </div>
          </div>
          <div style="margin-top:16px;">
            <label class="ct-label" for="ct-subject">Subject</label>
            <input class="ct-input" id="ct-subject" type="text" name="subject" value="<?= e($old['subject']) ?>" placeholder="How can we help?">
          </div>
          <div style="margin-top:16px;">
            <label class="ct-label" for="ct-message">Message</label>
            <textarea class="ct-input" id="ct-message" name="message" rows="6" required style="resize:vertical;"><?= e($old['message']) ?></textarea>
          </div>
          <button type="submit" style="margin-top:20px;display:inline-flex;align-items:center;gap:8px;padding:12px 32px;border-radius:8px;font-weight:600;font-size:16px;color:#fff;background:#6B1D22;font-family:Inter,sans-serif;border:none;cursor:pointer;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);transition:filter 0.2s;" onmouseover="this.style.filter='brightness(1.1)'" onmouseout="this.style.filter='none'">
            <span style="font-family:'Material Symbols Outlined';font-size:20px;">send</span>
            Send Message
          </button>
        </form>
      </div>

      <!-- Info -->
      <div style="display:grid;gap:16px;">
        <div style="background:#fcf9f8;border-radius:12px;border:1px solid #dbc0bf4d;padding:20px;">
          <span style="color:#6B1D22;font-family:'Material Symbols Outlined';font-size:26px;font-variation-settings:'FILL' 1;">support_agent</span>
          <h3 style="font-size:16px;font-weight:700;margin:8px 0 4px;color:#1c1b1b;font-family:Montserrat,sans-serif;">General &amp; Support</h3>
          <p style="font-size:14px;line-height:20px;color:#554242;font-family:Inter,sans-serif;margin:0 0 6px;">Help with your account, listings, or connections.</p>
          <a href="mailto:hello@asaancapital.com" style="font-size:14px;font-weight:600;color:#6B1D22;text-decoration:none;">hello@asaancapital.com</a>
        </div>
        <div style="background:#fcf9f8;border-radius:12px;border:1px solid #dbc0bf4d;padding:20px;">
          <span style="color:#3b6281;font-family:'Material Symbols Outlined';font-size:26px;font-variation-settings:'FILL' 1;">handshake</span>
          <h3 style="font-size:16px;font-weight:700;margin:8px 0 4px;color:#1c1b1b;font-family:Montserrat,sans-serif;">Partnerships &amp; Media</h3>
          <p style="font-size:14px;line-height:20px;color:#554242;font-family:Inter,sans-serif;margin:0 0 6px;">Press, advisors, and business development.</p>
          <a href="mailto:partnerships@asaancapital.com" style="font-size:14px;font-weight:600;color:#3b6281;text-decoration:none;">partnerships@asaancapital.com</a>
        </div>
        <div style="background:#fcf9f8;border-radius:12px;border:1px solid #dbc0bf4d;padding:20px;">
          <span style="color:#6B1D22;font-family:'Material Symbols Outlined';font-size:26px;font-variation-settings:'FILL' 1;">location_on</span>
          <h3 style="font-size:16px;font-weight:700;margin:8px 0 4px;color:#1c1b1b;font-family:Montserrat,sans-serif;">Office</h3>
          <p style="font-size:14px;line-height:20px;color:#554242;font-family:Inter,sans-serif;margin:0;">Asaan Credit Ltd<br>Kathmandu, Nepal</p>
        </div>
      </div>
    </div>
  </div>
</section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
