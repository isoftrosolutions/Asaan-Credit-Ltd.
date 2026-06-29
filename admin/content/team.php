<?php
require __DIR__ . '/../../config/bootstrap.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        db()->prepare("UPDATE team_members SET is_active = NOT is_active WHERE id = ?")->execute([$id]);
        admin_log('toggle_team', 'team_members', $id);
        flash_success('Team member status updated.');
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = db()->prepare("SELECT photo FROM team_members WHERE id = ?");
        $stmt->execute([$id]);
        $m = $stmt->fetch();
        if ($m && $m['photo']) {
            $path = __DIR__ . '/../../public/uploads/team/' . $m['photo'];
            if (file_exists($path)) @unlink($path);
        }
        db()->prepare("DELETE FROM team_members WHERE id = ?")->execute([$id]);
        admin_log('delete_team', 'team_members', $id);
        flash_success('Team member deleted.');
    }

    redirect('/admin/team');
}

$pageTitle = 'Team — Admin';
require __DIR__ . '/../../includes/layout-admin.php';

$members = db()->query("SELECT * FROM team_members ORDER BY sort_order ASC, id ASC")->fetchAll();
?>
<div class="dash-pagehead">
  <h1 class="dash-pagehead-title">Team Members</h1>
  <a href="/admin/team/edit" class="btn btn-primary btn-sm">+ Add Member</a>
</div>

<div class="dash-panel" style="margin-top:var(--space-5);">
  <?php if (empty($members)): ?>
    <div style="padding:var(--space-5);text-align:center;color:var(--dash-ink-soft);">No team members yet.</div>
  <?php else: ?>
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead>
        <tr><th>Photo</th><th>Name</th><th>Position</th><th>Phone</th><th>Order</th><th>Active</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($members as $m): ?>
        <tr>
          <td>
            <?php if ($m['photo']): ?>
              <img src="<?= APP_URL ?>/public/uploads/team/<?= e($m['photo']) ?>" alt="" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
            <?php else: ?>
              <div style="width:40px;height:40px;border-radius:50%;background:var(--dash-bg);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;color:var(--dash-ink-soft);"><?= mb_strtoupper(mb_substr($m['name'], 0, 1)) ?></div>
            <?php endif; ?>
          </td>
          <td><strong><?= e($m['name']) ?></strong></td>
          <td><?= e($m['position']) ?></td>
          <td><?= e($m['phone'] ?: '—') ?></td>
          <td><?= $m['sort_order'] ?></td>
          <td><span class="dash-pill <?= $m['is_active'] ? 'published' : 'draft' ?>"><?= $m['is_active'] ? 'Yes' : 'No' ?></span></td>
          <td>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
              <a href="/admin/team/edit?id=<?= $m['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
              <form method="post" style="display:inline;">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
                <input type="hidden" name="id" value="<?= $m['id'] ?>">
                <button type="submit" name="action" value="toggle" class="btn btn-sm btn-outline"><?= $m['is_active'] ? 'Deactivate' : 'Activate' ?></button>
              </form>
              <form method="post" style="display:inline;" onsubmit="return confirm('Delete this team member?');">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
                <input type="hidden" name="id" value="<?= $m['id'] ?>">
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
