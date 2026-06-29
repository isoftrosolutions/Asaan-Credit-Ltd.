<?php
require __DIR__ . '/../config/bootstrap.php';
$pageTitle = 'Careers — ' . APP_NAME;
$pageDescription = 'Join the Asaan Capital Ltd team. Explore career opportunities in investment advisory, business valuation, and financial services in Nepal.';
$forcePublicHeader = true;

$jobs = [];
try {
    $stmt = db()->query("SELECT id, title, slug, location, type, department, description, requirements, created_at FROM job_openings WHERE status='published' ORDER BY created_at DESC");
    $jobs = $stmt->fetchAll();
} catch (\Throwable $e) {}

function job_type_label($type) {
    return [
        'full-time' => 'Full Time',
        'part-time' => 'Part Time',
        'contract' => 'Contract',
        'internship' => 'Internship',
        'remote' => 'Remote',
    ][$type] ?? ucfirst($type);
}

$breadcrumbSchema = '<script type="application/ld+json">{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem","position":1,"name":"Home","item":"'.APP_URL.'/"},
    {"@type": "ListItem","position":2,"name":"Careers","item":"'.APP_URL.'/careers"}
  ]
}</script>';
require __DIR__ . '/../includes/header.php';
?>
<?= $breadcrumbSchema ?>
<style>
.jobs-grid{ display:grid; gap:var(--space-4); }
.job-card{ background:var(--dash-card); border:1px solid var(--dash-border); border-radius:var(--dash-radius-card); padding:var(--space-5); text-decoration:none; color:inherit; transition:box-shadow var(--motion-base), transform var(--motion-base); display:flex; align-items:center; justify-content:space-between; gap:var(--space-4); }
.job-card:hover{ box-shadow:var(--dash-shadow-hover); transform:translateY(-2px); border-color:var(--dash-primary); }
.job-card-info{ flex:1; }
.job-card-title{ font-family:var(--font-heading); font-weight:700; font-size:1.1rem; color:var(--dash-ink); margin-bottom:6px; }
.job-card-meta{ display:flex; flex-wrap:wrap; gap:12px; font-size:.85rem; color:var(--dash-ink-soft); }
.job-type-badge{ display:inline-block; font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.03em; padding:4px 10px; border-radius:999px; background:rgba(107,29,34,.1); color:var(--color-primary); }
.job-type-badge.contract{ background:rgba(245,158,11,.12); color:var(--dash-warning); }
.job-type-badge.internship{ background:rgba(59,130,246,.1); color:var(--dash-info); }
.job-type-badge.part-time{ background:rgba(16,185,129,.1); color:var(--dash-success); }
.job-type-badge.remote{ background:#F3F4F6; color:var(--dash-ink-soft); }
.job-card-arrow{ color:var(--dash-primary); font-size:1.2rem; flex-shrink:0; }
@media (max-width:640px){ .job-card{ flex-direction:column; align-items:stretch; } }
</style>
<main class="pub-page">

<section class="pub-hero" style="padding:60px 0;background:linear-gradient(135deg, var(--color-primary) 0%, #4A1317 100%);color:#fff;">
  <div class="pub-wrap">
    <div style="max-width:680px;">
      <span style="display:inline-block;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.7);margin-bottom:8px;">Careers</span>
      <h1 class="pub-h1" style="color:#fff;margin:0 0 12px;">Join Our Team</h1>
      <p style="font-size:1.05rem;line-height:1.7;opacity:.88;">Be part of Nepal's leading financial advisory and investment company. Explore opportunities to grow your career with Asaan Capital Ltd.</p>
    </div>
  </div>
</section>

<section class="pub-section">
  <div class="pub-wrap" style="max-width:800px;margin:0 auto;">
    <?php if (empty($jobs)): ?>
      <div class="pub-card" style="padding:var(--space-8) var(--space-5);text-align:center;">
        <span class="material-symbols-outlined" style="font-size:48px;color:var(--dash-ink-soft);margin-bottom:var(--space-3);">work</span>
        <h3 class="pub-h3" style="margin-bottom:var(--space-2);">No Open Positions Right Now</h3>
        <p class="pub-text">We don't have any current openings, but we're always looking for talented individuals. Send your resume to <a href="mailto:careers@asaancapital.com" style="color:var(--dash-primary);font-weight:600;">careers@asaancapital.com</a> and we'll keep you in mind for future opportunities.</p>
      </div>
    <?php else: ?>
      <div class="jobs-grid">
        <?php foreach ($jobs as $j):
          $typeClass = $j['type'] === 'contract' ? 'contract' : ($j['type'] === 'internship' ? 'internship' : ($j['type'] === 'part-time' ? 'part-time' : ($j['type'] === 'remote' ? 'remote' : '')));
        ?>
        <a href="<?= APP_URL ?>/careers/<?= e($j['slug']) ?>" class="job-card">
          <div class="job-card-info">
            <div class="job-card-title"><?= e($j['title']) ?></div>
            <div class="job-card-meta">
              <?php if ($j['location']): ?><span><i class="fas fa-map-marker-alt" style="width:14px;"></i> <?= e($j['location']) ?></span><?php endif; ?>
              <?php if ($j['department']): ?><span><i class="fas fa-building" style="width:14px;"></i> <?= e($j['department']) ?></span><?php endif; ?>
              <span class="job-type-badge <?= $typeClass ?>"><?= job_type_label($j['type']) ?></span>
            </div>
          </div>
          <span class="job-card-arrow"><i class="fas fa-arrow-right"></i></span>
        </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
