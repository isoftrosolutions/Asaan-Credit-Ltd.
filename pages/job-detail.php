<?php
require __DIR__ . '/../config/bootstrap.php';

$slug = $_GET['slug'] ?? '';
$job = null;
try {
    $stmt = db()->prepare("SELECT * FROM job_openings WHERE slug = ? AND status='published' LIMIT 1");
    $stmt->execute([$slug]);
    $job = $stmt->fetch();
} catch (\Throwable $e) {}

if (!$job) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    return;
}

$applied = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try { csrf_check(); } catch (\Throwable $e) { http_response_code(419); exit; }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $coverLetter = trim($_POST['cover_letter'] ?? '');

    if (!$name || !$email || !$coverLetter) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $resumePath = null;
        if (!empty($_FILES['resume']) && $_FILES['resume']['error'] !== UPLOAD_ERR_NO_FILE) {
            $allowedMime = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];
            $resumePath = handle_upload($_FILES['resume'], $allowedMime, 5 * 1024 * 1024, upload_path('resumes'));
        } elseif ($_FILES['resume']['error'] ?? UPLOAD_ERR_NO_FILE !== UPLOAD_ERR_NO_FILE) {
            $error = 'Resume upload failed. Please ensure the file is under 5MB and is a PDF, DOC, or image.';
        }

        if (!$error) {
            try {
                $stmt = db()->prepare("INSERT INTO job_applications (job_id, name, email, phone, cover_letter, resume_path, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
                $stmt->execute([$job['id'], $name, $email, $phone, $coverLetter, $resumePath]);
                $applied = true;
            } catch (\Throwable $e) {
                $error = 'Something went wrong. Please try again later.';
            }
        }
    }
}

function job_type_label($type) {
    return ['full-time'=>'Full Time','part-time'=>'Part Time','contract'=>'Contract','internship'=>'Internship','remote'=>'Remote'][$type] ?? ucfirst($type);
}

$pageTitle = $job['title'] . ' — Careers — ' . APP_NAME;
$pageDescription = 'Apply for ' . $job['title'] . ' at Asaan Capital Ltd. ' . ($job['location'] ? 'Location: ' . $job['location'] . '. ' : '') . ($job['department'] ? 'Department: ' . $job['department'] . '.' : '');
$forcePublicHeader = true;
require __DIR__ . '/../includes/header.php';
?>
<style>
.job-applied{ text-align:center; padding:var(--space-8); }
.job-applied .material-symbols-outlined{ font-size:56px; color:var(--dash-success); margin-bottom:var(--space-3); }
.job-form{ display:grid; gap:var(--space-4); }
.job-form .input-group{ display:flex; flex-direction:column; gap:6px; }
.job-form label{ font-size:.88rem; font-weight:600; color:var(--dash-ink); }
.job-form .input,.job-form textarea{ width:100%; padding:10px 14px; border:1px solid var(--dash-border); border-radius:10px; font-size:.95rem; font-family:var(--font-body); background:var(--dash-card); color:var(--dash-ink); }
.job-form .input:focus,.job-form textarea:focus{ outline:none; border-color:var(--dash-primary); box-shadow:0 0 0 3px rgba(107,29,34,.1); }
.job-form .required::after{ content:' *'; color:var(--color-primary); }
</style>
<main class="pub-page">

<section class="pub-section">
  <div class="pub-wrap-narrow">
    <div class="breadcrumbs pub-text" style="margin-bottom:var(--space-4);">
      <a href="<?= APP_URL ?>/" style="color:var(--dash-ink-soft);text-decoration:none;">Home</a> <span style="margin:0 6px;">/</span>
      <a href="<?= APP_URL ?>/careers" style="color:var(--dash-ink-soft);text-decoration:none;">Careers</a> <span style="margin:0 6px;">/</span>
      <span><?= e($job['title']) ?></span>
    </div>

    <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;margin-bottom:var(--space-2);">
      <?php
        $typeClass = $job['type'] === 'contract' ? 'contract' : ($job['type'] === 'internship' ? 'internship' : ($job['type'] === 'part-time' ? 'part-time' : ($job['type'] === 'remote' ? 'remote' : '')));
      ?>
      <span class="job-type-badge <?= $typeClass ?>" style="display:inline-block;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.03em;padding:4px 10px;border-radius:999px;background:rgba(107,29,34,.1);color:var(--color-primary);"><?= job_type_label($job['type']) ?></span>
      <?php if ($job['location']): ?>
        <span class="pub-text" style="font-size:.85rem;"><i class="fas fa-map-marker-alt"></i> <?= e($job['location']) ?></span>
      <?php endif; ?>
      <?php if ($job['department']): ?>
        <span class="pub-text" style="font-size:.85rem;"><i class="fas fa-building"></i> <?= e($job['department']) ?></span>
      <?php endif; ?>
    </div>

    <h1 class="pub-h1" style="margin-bottom:var(--space-4);"><?= e($job['title']) ?></h1>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-8);">
      <div>
        <div class="pub-prose trix-content">
          <h3>About This Role</h3>
          <?php
          $desc = $job['description'];
          if ($desc !== strip_tags($desc)) { echo $desc; }
          else { echo '<p>' . nl2br(e($desc)) . '</p>'; }
          ?>
        </div>

        <?php if ($job['requirements']): ?>
        <div class="pub-prose trix-content" style="margin-top:var(--space-5);">
          <h3>Requirements</h3>
          <?php
          $req = $job['requirements'];
          if ($req !== strip_tags($req)) { echo $req; }
          else { echo '<p>' . nl2br(e($req)) . '</p>'; }
          ?>
        </div>
        <?php endif; ?>
      </div>

      <div>
        <?php if ($applied): ?>
          <div class="pub-card job-applied">
            <span class="material-symbols-outlined">check_circle</span>
            <h3 class="pub-h3" style="margin-bottom:var(--space-1);">Application Submitted!</h3>
            <p class="pub-text">Thank you for applying to <?= e($job['title']) ?>. Our team will review your application and get back to you shortly.</p>
            <a href="<?= APP_URL ?>/careers" class="btn btn-primary" style="display:inline-block;margin-top:var(--space-3);padding:10px 24px;background:var(--color-primary);color:#fff;border-radius:10px;text-decoration:none;font-weight:600;">Browse More Positions</a>
          </div>
        <?php else: ?>
          <div class="pub-card" style="padding:var(--space-5);">
            <h3 class="pub-h3" style="margin-bottom:var(--space-4);">Apply for This Position</h3>
            <?php if ($error): ?>
              <div style="background:rgba(239,68,68,.1);color:var(--dash-danger);padding:12px 16px;border-radius:10px;font-size:.88rem;margin-bottom:var(--space-4);"><?= e($error) ?></div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data" class="job-form">
              <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
              <div class="input-group">
                <label class="required">Full Name</label>
                <input type="text" name="name" class="input" required value="<?= e($_POST['name'] ?? '') ?>">
              </div>
              <div class="input-group">
                <label class="required">Email</label>
                <input type="email" name="email" class="input" required value="<?= e($_POST['email'] ?? '') ?>">
              </div>
              <div class="input-group">
                <label>Phone</label>
                <input type="tel" name="phone" class="input" value="<?= e($_POST['phone'] ?? '') ?>">
              </div>
              <div class="input-group">
                <label class="required">Cover Letter</label>
                <textarea name="cover_letter" rows="6" class="input" required><?= e($_POST['cover_letter'] ?? '') ?></textarea>
              </div>
              <div class="input-group">
                <label>Resume (PDF, DOC, or image — max 5MB)</label>
                <input type="file" name="resume" class="input" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
              </div>
              <button type="submit" class="btn btn-primary" style="padding:12px 24px;background:var(--color-primary);color:#fff;border:none;border-radius:10px;font-size:1rem;font-weight:600;cursor:pointer;">Submit Application</button>
            </form>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
