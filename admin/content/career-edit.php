<?php
require __DIR__ . '/../../config/bootstrap.php';
require_admin();

function generate_slug_career($title, $id = null) {
    $slug = mb_strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $title), '-'));
    $base = $slug;
    $i = 1;
    while (true) {
        $stmt = db()->prepare("SELECT COUNT(*) FROM job_openings WHERE slug = ?" . ($id ? " AND id != ?" : ""));
        $params = [$slug];
        if ($id) $params[] = $id;
        $stmt->execute($params);
        if (!$stmt->fetchColumn()) break;
        $slug = $base . '-' . ($i++);
    }
    return $slug;
}

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$tab = $_GET['tab'] ?? 'details';
$job = null;

if ($id) {
    $stmt = db()->prepare("SELECT * FROM job_openings WHERE id = ?");
    $stmt->execute([$id]);
    $job = $stmt->fetch();
    if (!$job) { flash_error('Job not found.'); redirect('/admin/careers'); }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? 'save';

    if ($action === 'save') {
        $title = trim($_POST['title'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $type = $_POST['type'] ?? 'full-time';
        $department = trim($_POST['department'] ?? '');
        $description = $_POST['description'] ?? '';
        $requirements = $_POST['requirements'] ?? '';
        $status = $_POST['status'] ?? 'draft';

        if (!$title) { flash_error('Title is required.'); redirect('/admin/careers/edit' . ($id ? '?id=' . $id : '')); }

        if ($id) {
            $slug = generate_slug_career($title, $id);
            db()->prepare("UPDATE job_openings SET title=?, slug=?, location=?, type=?, department=?, description=?, requirements=?, status=? WHERE id=?")
                ->execute([$title, $slug, $location, $type, $department, $description, $requirements, $status, $id]);
            admin_log('update_job', 'job_openings', $id, ['title' => $title]);
            flash_success('Job updated.');
        } else {
            $slug = generate_slug_career($title);
            db()->prepare("INSERT INTO job_openings (title, slug, location, type, department, description, requirements, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
                ->execute([$title, $slug, $location, $type, $department, $description, $requirements, $status]);
            admin_log('create_job', 'job_openings', db()->lastInsertId(), ['title' => $title]);
            flash_success('Job created.');
        }
        redirect('/admin/careers');
    }

    if ($action === 'update_status') {
        $appId = (int)($_POST['app_id'] ?? 0);
        $newStatus = $_POST['app_status'] ?? 'pending';
        db()->prepare("UPDATE job_applications SET status = ? WHERE id = ?")->execute([$newStatus, $appId]);
        flash_success('Application status updated.');
        redirect('/admin/careers/edit?id=' . $id . '&tab=applications');
    }
}

$pageTitle = ($id ? 'Edit' : 'New') . ' Job — Admin';
require __DIR__ . '/../../includes/layout-admin.php';

$applications = [];
if ($id) {
    $stmt = db()->prepare("SELECT * FROM job_applications WHERE job_id = ? ORDER BY created_at DESC");
    $stmt->execute([$id]);
    $applications = $stmt->fetchAll();
}
?>
<link rel="stylesheet" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
<style>
  trix-toolbar .trix-button-row{ flex-wrap:wrap; }
  trix-editor{ min-height:250px; }
</style>
<div class="dash-pagehead">
  <h1 class="dash-pagehead-title"><?= $id ? 'Edit Job' : 'New Job' ?></h1>
  <a href="/admin/careers" class="btn btn-sm btn-outline">&larr; Back</a>
</div>

<div style="display:flex;gap:4px;margin-bottom:var(--space-5);border-bottom:1px solid var(--dash-border);">
  <a href="/admin/careers/edit?id=<?= $id ?>" style="padding:10px 20px;text-decoration:none;font-weight:600;font-size:.9rem;color:<?= $tab === 'details' || !$id ? 'var(--dash-primary)' : 'var(--dash-ink-soft)' ?>;border-bottom:2px solid <?= $tab === 'details' || !$id ? 'var(--dash-primary)' : 'transparent' ?>;">Job Details</a>
  <?php if ($id): ?>
  <a href="/admin/careers/edit?id=<?= $id ?>&tab=applications" style="padding:10px 20px;text-decoration:none;font-weight:600;font-size:.9rem;color:<?= $tab === 'applications' ? 'var(--dash-primary)' : 'var(--dash-ink-soft)' ?>;border-bottom:2px solid <?= $tab === 'applications' ? 'var(--dash-primary)' : 'transparent' ?>;">Applications (<?= count($applications) ?>)</a>
  <?php endif; ?>
</div>

<?php if ($tab === 'applications' && $id): ?>
<div class="dash-panel">
  <?php if (empty($applications)): ?>
    <div style="padding:var(--space-5);text-align:center;color:var(--dash-ink-soft);">No applications yet.</div>
  <?php else: ?>
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead>
        <tr><th>Name</th><th>Email</th><th>Phone</th><th>Resume</th><th>Status</th><th>Date</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($applications as $a): ?>
        <tr>
          <td><strong><?= e($a['name']) ?></strong></td>
          <td><a href="mailto:<?= e($a['email']) ?>"><?= e($a['email']) ?></a></td>
          <td><?= e($a['phone'] ?: '—') ?></td>
          <td><?php if ($a['resume_path']): ?><a href="<?= APP_URL . '/public/uploads/resumes/' . e($a['resume_path']) ?>" target="_blank" class="btn btn-sm btn-outline">View</a><?php else: ?>—<?php endif; ?></td>
          <td><span class="dash-pill <?= $a['status'] ?>"><?= $a['status'] ?></span></td>
          <td><?= date('M j, Y', strtotime($a['created_at'])) ?></td>
          <td>
            <form method="post" style="display:flex;gap:4px;">
              <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
              <input type="hidden" name="app_id" value="<?= $a['id'] ?>">
              <input type="hidden" name="action" value="update_status">
              <select name="app_status" class="input" style="padding:4px 8px;font-size:.82rem;width:auto;" onchange="this.form.submit()">
                <option value="pending" <?= $a['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="reviewed" <?= $a['status'] === 'reviewed' ? 'selected' : '' ?>>Reviewed</option>
                <option value="shortlisted" <?= $a['status'] === 'shortlisted' ? 'selected' : '' ?>>Shortlisted</option>
                <option value="rejected" <?= $a['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                <option value="accepted" <?= $a['status'] === 'accepted' ? 'selected' : '' ?>>Accepted</option>
              </select>
            </form>
          </td>
        </tr>
        <?php if ($a['cover_letter']): ?>
        <tr style="background:var(--dash-bg);">
          <td colspan="7" style="font-size:.85rem;color:var(--dash-ink-soft);padding:8px 16px 16px;"><strong>Cover Letter:</strong> <?= nl2br(e($a['cover_letter'])) ?></td>
        </tr>
        <?php endif; ?>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php else: ?>
<div class="dash-panel">
  <form method="post" style="max-width:800px;">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
    <input type="hidden" name="action" value="save">
    <?php if ($id): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
      <div class="input-group" style="grid-column:1/-1;">
        <label>Job Title</label>
        <input type="text" name="title" class="input" required value="<?= e($job['title'] ?? '') ?>">
      </div>
      <div class="input-group">
        <label>Location</label>
        <input type="text" name="location" class="input" value="<?= e($job['location'] ?? '') ?>" placeholder="e.g. Bhaktapur, Nepal">
      </div>
      <div class="input-group">
        <label>Department</label>
        <input type="text" name="department" class="input" value="<?= e($job['department'] ?? '') ?>" placeholder="e.g. Advisory">
      </div>
      <div class="input-group">
        <label>Type</label>
        <select name="type" class="input">
          <?php foreach (['full-time'=>'Full Time','part-time'=>'Part Time','contract'=>'Contract','internship'=>'Internship','remote'=>'Remote'] as $k=>$v): ?>
          <option value="<?= $k ?>" <?= ($job['type'] ?? 'full-time') === $k ? 'selected' : '' ?>><?= $v ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="input-group">
        <label>Status</label>
        <select name="status" class="input">
          <option value="draft" <?= ($job['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
          <option value="published" <?= ($job['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
        </select>
      </div>
    </div>

    <div class="input-group" style="margin-top:var(--space-4);">
      <label>Description</label>
      <input type="hidden" id="description" name="description" value="<?= e($job['description'] ?? '') ?>">
      <trix-editor input="description" class="trix-content"></trix-editor>
    </div>

    <div class="input-group" style="margin-top:var(--space-4);">
      <label>Requirements</label>
      <input type="hidden" id="requirements" name="requirements" value="<?= e($job['requirements'] ?? '') ?>">
      <trix-editor input="requirements" class="trix-content"></trix-editor>
    </div>

    <div style="margin-top:var(--space-5);">
      <button type="submit" class="btn btn-primary"><?= $id ? 'Update Job' : 'Create Job' ?></button>
      <a href="/admin/careers" class="btn btn-outline" style="margin-left:8px;">Cancel</a>
    </div>
  </form>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
