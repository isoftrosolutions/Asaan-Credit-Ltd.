<?php
require __DIR__ . '/../config/bootstrap.php';

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'contact') {
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if (filter_var($email, FILTER_VALIDATE_EMAIL) && $message !== '') {
        flash_set('success', 'Thank you! We have received your query and will respond within 2 business days.');
    } else {
        flash_set('error', 'Please enter a valid email address and your question.');
    }
    redirect('/support');
}

$faqs = db()->query("SELECT * FROM faqs WHERE is_active=1 ORDER BY sort_order")->fetchAll();

$pageTitle = 'FAQ & Support — ' . APP_NAME;
require __DIR__ . '/../includes/header.php';
?>
<main class="main-content" style="padding-top:0;">

<div class="breadcrumbs container" style="padding-top:1rem;padding-bottom:1rem;font-size:0.85rem;color:var(--secondary-text);">
  <a href="<?= APP_URL ?>">Home</a> <span style="margin:0 0.5rem;">/</span>
  <span>Q &amp; A</span>
</div>

<div class="container" style="max-width:800px; padding-bottom:4rem;">
  <div style="text-align:center;margin-bottom:2rem;">
    <h1>Frequently Asked Questions</h1>
    <p style="font-size:1.05rem;color:var(--secondary-text);">Find answers about using <?= APP_NAME ?> — whether you're a business owner, investor, franchisor, or advisor.</p>
  </div>

  <!-- Filter tabs -->
  <div class="persona-tabs" style="display:flex;gap:0.5rem;flex-wrap:wrap;justify-content:center;margin-bottom:1.5rem;">
    <button class="persona-tab active" onclick="filterFAQ('all',this)" style="padding:10px 20px;border-radius:999px;font-weight:600;cursor:pointer;font-size:0.9rem;border:1.5px solid var(--surface-container-high);background:var(--brand-red);color:#fff;font-family:inherit;transition:all 0.2s;">All</button>
    <button class="persona-tab" onclick="filterFAQ('owner',this)" style="padding:10px 20px;border-radius:999px;font-weight:600;cursor:pointer;font-size:0.9rem;border:1.5px solid var(--surface-container-high);background:var(--surface);color:var(--dark);font-family:inherit;transition:all 0.2s;">Business Owners</button>
    <button class="persona-tab" onclick="filterFAQ('investor',this)" style="padding:10px 20px;border-radius:999px;font-weight:600;cursor:pointer;font-size:0.9rem;border:1.5px solid var(--surface-container-high);background:var(--surface);color:var(--dark);font-family:inherit;transition:all 0.2s;">Investors &amp; Buyers</button>
    <button class="persona-tab" onclick="filterFAQ('advisor',this)" style="padding:10px 20px;border-radius:999px;font-weight:600;cursor:pointer;font-size:0.9rem;border:1.5px solid var(--surface-container-high);background:var(--surface);color:var(--dark);font-family:inherit;transition:all 0.2s;">Advisors</button>
    <button class="persona-tab" onclick="filterFAQ('franchisor',this)" style="padding:10px 20px;border-radius:999px;font-weight:600;cursor:pointer;font-size:0.9rem;border:1.5px solid var(--surface-container-high);background:var(--surface);color:var(--dark);font-family:inherit;transition:all 0.2s;">Franchisors</button>
  </div>

  <div class="faq-section">
    <?php if (empty($faqs)): ?>
    <div class="empty-state">
      <p>No FAQs available yet. Check back soon!</p>
    </div>
    <?php else: ?>
    <?php $first = true; ?>
    <?php foreach ($faqs as $faq): ?>
    <div class="faq-item<?= $first ? ' open' : '' ?>" data-category="all" style="background:var(--surface);border-radius:16px;padding:1rem 1.25rem;margin-bottom:0.5rem;border:1px solid var(--surface-container-high);">
      <div class="faq-question" onclick="this.parentElement.classList.toggle('open')" style="display:flex;justify-content:space-between;align-items:center;cursor:pointer;font-weight:600;">
        <span><?= e($faq['question']) ?></span>
        <span style="font-size:1.2rem;transition:transform 0.2s;flex-shrink:0;margin-left:1rem;">+</span>
      </div>
      <div class="faq-answer" style="display:<?= $first ? 'block' : 'none' ?>;margin-top:0.75rem;font-size:0.9rem;color:var(--secondary-text);line-height:1.7;">
        <?= e($faq['answer']) ?>
      </div>
    </div>
    <?php $first = false; ?>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Contact section -->
  <div style="margin-top:3rem; padding:2rem; background:var(--surface-container); border-radius:2rem; text-align:center;">
    <h3>Still have questions?</h3>
    <p style="color:var(--secondary-text);">Send your query and we'll get back to you within 2 business days.</p>
    <form method="post" action="<?= APP_URL ?>/support" style="max-width:480px; margin:1.5rem auto 0;">
      <input type="hidden" name="action" value="contact">
      <div class="input-group">
        <textarea name="message" class="input" placeholder="Type your question..." style="min-height:80px;" required></textarea>
      </div>
      <div style="display:flex; gap:0.75rem;">
        <input type="email" name="email" class="input" placeholder="Your email" style="flex:1;" required>
        <button type="submit" class="btn btn-primary">Send</button>
      </div>
    </form>
    <div style="margin-top:1rem; font-size:0.85rem; color:var(--secondary-text);">
      Or email us at <a href="mailto:support@investmatch.com.np" style="color:var(--brand-red);">support@investmatch.com.np</a>
    </div>
  </div>
</div>

<script>
function filterFAQ(category, btn) {
  document.querySelectorAll('.persona-tab').forEach(function(t) {
    t.style.background = 'var(--surface)';
    t.style.color = 'var(--dark)';
  });
  btn.style.background = 'var(--brand-red)';
  btn.style.color = '#fff';
  document.querySelectorAll('.faq-item').forEach(function(item) {
    var cats = item.getAttribute('data-category').split(' ');
    item.style.display = (category === 'all' || cats.indexOf(category) !== -1) ? 'block' : 'none';
  });
}
</script>

</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
