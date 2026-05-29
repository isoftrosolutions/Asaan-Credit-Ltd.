<?php
require __DIR__ . '/../config/bootstrap.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = db()->prepare('SELECT f.*, s.name AS sector_name, u.full_name AS user_name
                        FROM franchises f
                        LEFT JOIN sectors s ON s.id = f.sector_id
                        LEFT JOIN users u ON u.id = f.user_id
                        WHERE f.id = ? AND f.is_published = 1');
$stmt->execute([$id]);
$franchise = $stmt->fetch();

if (!$franchise) {
    http_response_code(404);
    require __DIR__ . '/../pages/404.php';
    exit;
}

db()->prepare('UPDATE franchises SET views = views + 1 WHERE id = ?')->execute([$id]);

$pageTitle = $franchise['brand_name'] . ' — Franchise Opportunity';
$pageDescription = 'View this franchise opportunity on Asaan Capital Ltd. Details about investment requirements, training, and support.';

$breadcrumbSchema = '<script type="application/ld+json">{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem","position":1,"name":"Home","item":"'.APP_URL.'/"},
    {"@type": "ListItem","position":2,"name":"Franchises","item":"'.APP_URL.'/discover/franchises.php"},
    {"@type": "ListItem","position":3,"name":"'.e($franchise['brand_name']).'","item":"'.APP_URL.'/franchise/detail.php?id='.$id.'"}
  ]
}</script>';
require __DIR__ . '/../includes/layout-public.php';
?>

<?= $breadcrumbSchema ?>
<div class="breadcrumbs container">
  <a href="<?= APP_URL ?>">Home</a> <span>/</span>
  <a href="<?= APP_URL ?>/browse/franchises">Franchises</a> <span>/</span>
  <span><?= e($franchise['brand_name']) ?></span>
</div>

<div class="container" style="padding-bottom:3rem;">
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;margin-top:1.5rem;">
    <div>
      <?php if ($franchise['logo_path']): ?>
      <img src="<?= APP_URL ?>/public/uploads/franchise-logos/<?= e($franchise['logo_path']) ?>" alt="<?= e($franchise['brand_name']) ?> logo" style="max-width:100%;border-radius:1.5rem;max-height:300px;object-fit:contain;">
      <?php endif; ?>
      <h2 style="margin-top:1rem 0 0.25rem;"><?= e($franchise['brand_name']) ?></h2>
      <?php if ($franchise['sector_name']): ?>
      <span class="tx-badge tx-badge-franchise"><?= e($franchise['sector_name']) ?></span>
      <?php endif; ?>
      <?php if ($franchise['rating']): ?>
      <span class="rating-badge"><?= e($franchise['rating']) ?></span>
      <?php endif; ?>

      <div style="margin-top:1.5rem;">
        <h3>About the Brand</h3>
        <p><?= nl2br(e($franchise['description'])) ?></p>
      </div>

      <?php if ($franchise['ideal_partner_profile']): ?>
      <div style="margin-top:1.5rem;">
        <h3>Ideal Partner Profile</h3>
        <p><?= nl2br(e($franchise['ideal_partner_profile'])) ?></p>
      </div>
      <?php endif; ?>
    </div>

    <div>
      <div class="card" style="padding:1.5rem;">
        <h3 style="margin-bottom:1rem;">Financial Overview</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
          <div><span class="meta-label">Franchise Fee</span><div class="meta-value"><?= $franchise['franchise_fee'] ? money($franchise['franchise_fee']) : '—' ?></div></div>
          <div><span class="meta-label">Royalty</span><div class="meta-value"><?= $franchise['royalty_pct'] ? e($franchise['royalty_pct']) . '%' : '—' ?></div></div>
          <div><span class="meta-label">Marketing Fee</span><div class="meta-value"><?= $franchise['marketing_fee_pct'] ? e($franchise['marketing_fee_pct']) . '%' : '—' ?></div></div>
          <div><span class="meta-label">Expected Payback</span><div class="meta-value"><?= $franchise['expected_payback_months'] ? e($franchise['expected_payback_months']) . ' months' : '—' ?></div></div>
          <div><span class="meta-label">Investment Range</span><div class="meta-value"><?= $franchise['total_investment_min'] ? money($franchise['total_investment_min']) . ' – ' . money($franchise['total_investment_max']) : '—' ?></div></div>
          <div><span class="meta-label">Training</span><div class="meta-value"><?= $franchise['training_provided'] ? 'Yes' : 'No' ?></div></div>
          <div><span class="meta-label">Territory Protection</span><div class="meta-value"><?= $franchise['territory_protection'] ? 'Yes' : 'No' ?></div></div>
          <div><span class="meta-label">Established</span><div class="meta-value"><?= e($franchise['established_year']) ?: '—' ?></div></div>
          <div><span class="meta-label">Existing Units</span><div class="meta-value"><?= e($franchise['existing_units']) ?: '—' ?></div></div>
          <div><span class="meta-label">Countries Present</span><div class="meta-value"><?= e($franchise['countries_present']) ?: '—' ?></div></div>
        </div>
      </div>

      <div class="card" style="padding:1.5rem;margin-top:1rem;">
        <h3 style="margin-bottom:0.5rem;">Interest in this Franchise?</h3>
        <p style="font-size:0.85rem;color:var(--color-text-muted);">Connect with the franchisor to learn more about this opportunity.</p>
        <button class="btn btn-primary" style="width:100%;margin-top:0.75rem;" onclick="alert('Interest sent! (integration pending)')">Express Interest</button>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
