<?php
require __DIR__ . '/../config/bootstrap.php';

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'contact') {
    csrf_check();
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
$pageDescription = 'Frequently asked questions about ' . APP_NAME_LONG . '. Learn how our platform works for business owners, investors, and advisors.';

$breadcrumbSchema = '<script type="application/ld+json">{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem","position":1,"name":"Home","item":"'.APP_URL.'/"},
    {"@type": "ListItem","position":2,"name":"Q & A","item":"'.APP_URL.'/support"}
  ]
}</script>';

$faqSchema = '<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "How does the platform ensure profiles are genuine?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Every profile undergoes a manual review process before being approved. We verify key details such as business ownership, financial standing, and identity to maintain a trusted marketplace. Profiles that do not meet our verification standards are rejected or flagged for additional review."
      }
    },
    {
      "@type": "Question",
      "name": "When do contact details get shared?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Contact details are only shared when there is mutual interest between the parties. This means both the business owner and the investor (or relevant counterparty) must express interest before any contact information is exchanged, ensuring privacy and reducing unwanted communication."
      }
    },
    {
      "@type": "Question",
      "name": "What types of transactions are supported?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Asaan Capital supports a wide range of transaction types including business sale, stake acquisition, investment partnership, loan-based arrangements, and franchise opportunities. Each listing specifies the transaction type so you can filter and find opportunities that match your goals."
      }
    },
    {
      "@type": "Question",
      "name": "Is there a fee to use InvestMatch?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Asaan Capital offers a free basic plan that allows you to browse listings and create a profile. Premium plans with additional features such as advanced analytics, priority support, and enhanced visibility are available for a subscription fee. A finder\'s fee may apply for successfully facilitated transactions."
      }
    },
    {
      "@type": "Question",
      "name": "How long does verification take?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Most profiles are verified within 24 to 48 hours after submission. In some cases where additional documentation is required, the process may take slightly longer. You will be notified via email once your profile has been reviewed."
      }
    }
  ]
}
</script>';
$forcePublicHeader = true;
require __DIR__ . '/../includes/header.php';
?>
<main class="pub-page" style="padding-top:0;">

<?= $breadcrumbSchema ?>
<div class="pub-wrap" style="padding-top:var(--space-4);padding-bottom:var(--space-4);">
  <div class="breadcrumbs pub-text">
    <a href="<?= APP_URL ?>" style="color:var(--dash-ink-soft);text-decoration:none;">Home</a> <span style="margin:0 0.5rem;">/</span>
    <span>Q &amp; A</span>
  </div>
</div>

<div class="pub-wrap-narrow" style="padding-bottom:4rem;">
  <div class="pub-section-head" style="margin-bottom:var(--space-6);">
    <h1 class="pub-h1">Frequently Asked Questions</h1>
    <p class="pub-lead">Find answers about using <?= APP_NAME_LONG ?> — whether you're a business owner, investor, franchisor, or advisor.</p>
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
    <div class="pub-faq faq-item<?= $first ? ' open' : '' ?>" data-category="all">
      <div class="pub-faq-q faq-question" onclick="this.parentElement.classList.toggle('open')">
        <span><?= e($faq['question']) ?></span>
        <span class="pub-faq-icon">+</span>
      </div>
      <div class="pub-faq-a faq-answer trix-content" style="display:<?= $first ? 'block' : 'none' ?>;">
        <?php
        $answer = $faq['answer'];
        if ($answer !== strip_tags($answer)) {
            echo $answer;
        } else {
            echo '<p>' . nl2br(e($answer)) . '</p>';
        }
        ?>
      </div>
    </div>
    <?php $first = false; ?>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Contact section -->
  <div class="pub-card" style="margin-top:3rem;text-align:center;">
    <h3 class="pub-h3" style="margin-bottom:var(--space-2);">Still have questions?</h3>
    <p class="pub-text">Send your query and we'll get back to you within 2 business days.</p>
    <form method="post" action="<?= APP_URL ?>/support" style="max-width:480px; margin:1.5rem auto 0;">
      <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
      <input type="hidden" name="action" value="contact">
      <div class="input-group">
        <textarea name="message" class="input" placeholder="Type your question..." style="min-height:80px;" required></textarea>
      </div>
      <div style="display:flex; gap:0.75rem;">
        <input type="email" name="email" class="input" placeholder="Your email" style="flex:1;" required>
        <button type="submit" class="btn btn-primary">Send</button>
      </div>
    </form>
    <div style="margin-top:1rem;" class="pub-text">
      Or email us at <a href="mailto:info@asaancapital.com" style="color:var(--dash-primary);">info@asaancapital.com</a>
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

<?php if (isset($faqSchema)) echo $faqSchema; ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
