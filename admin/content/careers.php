<?php
require __DIR__ . '/../../config/bootstrap.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = db()->prepare("SELECT status FROM job_openings WHERE id = ?");
        $stmt->execute([$id]);
        $job = $stmt->fetch();
        if ($job) {
            $newStatus = $job['status'] === 'published' ? 'draft' : 'published';
            db()->prepare("UPDATE job_openings SET status = ? WHERE id = ?")->execute([$newStatus, $id]);
            admin_log('toggle_job', 'job_openings', $id, ['new_status' => $newStatus]);
            flash_success('Job status updated.');
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        db()->prepare("DELETE FROM job_openings WHERE id = ?")->execute([$id]);
        admin_log('delete_job', 'job_openings', $id);
        flash_success('Job deleted.');
    }

    redirect('/admin/careers');
}

$pageTitle = 'Careers — Admin';
require __DIR__ . '/../../includes/layout-admin.php';

$jobs = db()->query("SELECT j.*, (SELECT COUNT(*) FROM job_applications WHERE job_id = j.id) AS app_count FROM job_openings j ORDER BY j.created_at DESC")->fetchAll();
?>
<div class="dash-pagehead">
  <h1 class="dash-pagehead-title">Careers</h1>
  <a href="/admin/careers/edit" class="btn btn-primary btn-sm">+ New Job</a>
</div>

<div class="dash-panel" style="margin-top:var(--space-5);">
  <?php if (empty($jobs)): ?>
    <div style="padding:var(--space-5);text-align:center;color:var(--dash-ink-soft);">No job openings yet.</div>
  <?php else: ?>
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead>
        <tr>
          <th>Title</th>
          <th>Department</th>
          <th>Location</th>
          <th>Type</th>
          <th>Applications</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($jobs as $j): ?>
        <tr>
          <td><strong><?= e($j['title']) ?></strong></td>
          <td><?= e($j['department'] ?: '—') ?></td>
          <td><?= e($j['location'] ?: '—') ?></td>
          <td><?= e(ucfirst($j['type'])) ?></td>
          <td>
            <?php if ($j['app_count'] > 0): ?>
              <a href="/admin/careers/edit?id=<?= $j['id'] ?>&tab=applications" style="color:var(--dash-primary);font-weight:600;"><?= $j['app_count'] ?></a>
            <?php else: ?>
              0
            <?php endif; ?>
          </td>
          <td><span class="dash-pill <?= $j['status'] === 'published' ? 'published' : 'draft' ?>"><?= $j['status'] ?></span></td>
          <td>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
              <a href="/admin/careers/edit?id=<?= $j['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
              <form method="post" style="display:inline;">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
                <input type="hidden" name="id" value="<?= $j['id'] ?>">
                <button type="submit" name="action" value="toggle" class="btn btn-sm btn-outline"><?= $j['status'] === 'published' ? 'Draft' : 'Publish' ?></button>
              </form>
              <form method="post" style="display:inline;" onsubmit="return confirm('Delete this job?');">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
                <input type="hidden" name="id" value="<?= $j['id'] ?>">
                <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger">Delete</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
