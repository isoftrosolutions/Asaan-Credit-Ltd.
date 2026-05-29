<?php
require __DIR__ . '/../config/bootstrap.php';
$pageTitle = 'Terms & Privacy — ' . APP_NAME;
$pageDescription = 'Terms of Service and Privacy Policy for Asaan Capital Ltd - Financial & Investment Services.';

$breadcrumbSchema = '<script type="application/ld+json">{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem","position":1,"name":"Home","item":"'.APP_URL.'/"},
    {"@type": "ListItem","position":2,"name":"Terms & Privacy","item":"'.APP_URL.'/legal"}
  ]
}</script>';
require __DIR__ . '/../includes/header.php';
?>
<main class="main-content" style="padding-top:0;">

<?= $breadcrumbSchema ?>
<div class="breadcrumbs container" style="padding-top:1rem;padding-bottom:1rem;font-size:0.85rem;color:var(--secondary-text);">
  <a href="<?= APP_URL ?>">Home</a> <span style="margin:0 0.5rem;">/</span>
  <span>Terms &amp; Privacy</span>
</div>

<div class="container" style="max-width:780px; padding-bottom:4rem;">
  <h1>Terms of Service</h1>
  <p style="color:var(--secondary-text);">Last updated: May 26, 2026</p>

  <h3>1. Platform Role</h3>
  <p style="color:var(--secondary-text);"><?= APP_NAME ?> is a discovery and matching platform. We connect business owners, investors, buyers, lenders, franchisors, and advisors. We do not facilitate payments, execute transactions, or provide investment advice. All deals are conducted directly between parties.</p>

  <h3>2. Eligibility</h3>
  <p style="color:var(--secondary-text);">Users must be at least 18 years old. Businesses must be legally registered entities. All users must pass manual verification before their profiles go live. We reserve the right to reject any registration.</p>

  <h3>3. Verification &amp; Data</h3>
  <p style="color:var(--secondary-text);">Verification documents (citizenship, PAN, company registration, GST) are stored securely and only accessible to platform administrators. They are never shared publicly. Standard security measures including SSL encryption are employed.</p>

  <h3>4. Profile Rules</h3>
  <p style="color:var(--secondary-text);">Each user may maintain one business profile at a time. Profiles must be accurate and not misleading. We prohibit scraping, automated data collection, and any form of spam or solicitation outside the platform's intended use.</p>

  <h3>5. Confidentiality</h3>
  <p style="color:var(--secondary-text);">Contact information is revealed only after mutual interest is established. Users may not share or misuse contact details obtained through the platform. Violation may result in permanent account suspension.</p>

  <h3>6. Privacy Policy</h3>
  <p style="color:var(--secondary-text);">We collect: name, email, phone, verification documents, profile information, and usage data. This data is used for: platform operation, matching algorithms, verification, communication, and analytics. We do not sell user data. Users may request data deletion by contacting support.</p>

  <h3>7. Refund Policy</h3>
  <p style="color:var(--secondary-text);">Profile activation typically completes within 2 business days. CIM and Valuation Model services have a 25 business day SLA. A 5% deduction applies in eligible refund cases. No refund for change-of-mind or non-use. Refund requests must be submitted within 3 months via email. Credits process within 5-15 days.</p>

  <h3>8. Finder's Fee</h3>
  <p style="color:var(--secondary-text);">Paid subscription plans include a 1% finder's fee post deal closure. This fee is payable upon successful transaction completion between matched parties.</p>

  <h3>9. Governing Law</h3>
  <p style="color:var(--secondary-text);">These terms are governed by the laws of Nepal. Disputes shall be subject to the exclusive jurisdiction of courts in Kathmandu, Nepal.</p>

  <h3>10. Indemnification</h3>
  <p style="color:var(--secondary-text);">Users agree to indemnify and hold <?= APP_NAME ?> harmless from any claims arising from their use of the platform, including but not limited to transaction disputes, misrepresentation, or breach of terms.</p>

  <div style="margin-top:2.5rem; padding:1.5rem; background:var(--surface-container); border-radius:1.5rem; font-size:0.9rem;">
    <strong>Contact:</strong><br>
    Asaan Credit Ltd<br>
    Kathmandu, Nepal<br>
    Email: <a href="mailto:hello@asaancapital.com" style="color:var(--brand-red);">hello@asaancapital.com</a>
  </div>
</div>

</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
