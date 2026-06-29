<?php
require __DIR__ . '/../../config/bootstrap.php';
require_admin();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$member = null;
if ($id) {
    $stmt = db()->prepare("SELECT * FROM team_members WHERE id = ?");
    $stmt->execute([$id]);
    $member = $stmt->fetch();
    if (!$member) { flash_error('Member not found.'); redirect('/admin/team'); }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim($_POST['name'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    $isActive = !empty($_POST['is_active']) ? 1 : 0;

    if (!$name || !$position) { flash_error('Name and position are required.'); redirect('/admin/team/edit' . ($id ? '?id=' . $id : '')); }

    $photo = $member['photo'] ?? null;
    if (!empty($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $uploaded = handle_upload($_FILES['photo'], $allowed, 3 * 1024 * 1024, upload_path('team'));
        if ($uploaded) {
            if ($photo) { $old = __DIR__ . '/../../public/uploads/team/' . $photo; if (file_exists($old)) @unlink($old); }
            $photo = $uploaded;
        }
    }

    if ($id) {
        db()->prepare("UPDATE team_members SET name=?, position=?, bio=?, phone=?, photo=COALESCE(?, photo), sort_order=?, is_active=? WHERE id=?")
            ->execute([$name, $position, $bio, $phone, $photo, $sortOrder, $isActive, $id]);
        admin_log('update_team', 'team_members', $id);
        flash_success('Team member updated.');
    } else {
        db()->prepare("INSERT INTO team_members (name, position, bio, phone, photo, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)")
            ->execute([$name, $position, $bio, $phone, $photo, $sortOrder, $isActive]);
        admin_log('create_team', 'team_members', db()->lastInsertId());
        flash_success('Team member created.');
    }
    redirect('/admin/team');
}

$pageTitle = ($id ? 'Edit' : 'Add') . ' Team Member — Admin';
require __DIR__ . '/../../includes/layout-admin.php';
?>
<div class="dash-pagehead">
  <h1 class="dash-pagehead-title"><?= $id ? 'Edit' : 'Add' ?> Team Member</h1>
  <a href="/admin/team" class="btn btn-sm btn-outline">&larr; Back</a>
</div>

<div class="dash-panel" style="max-width:700px;">
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
    <?php if ($id): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>

    <div class="input-group">
      <label>Name</label>
      <input type="text" name="name" class="input" required value="<?= e($member['name'] ?? '') ?>">
    </div>
    <div class="input-group">
      <label>Position</label>
      <input type="text" name="position" class="input" required value="<?= e($member['position'] ?? '') ?>">
    </div>
    <div class="input-group">
      <label>Bio</label>
      <textarea name="bio" rows="4" class="input"><?= e($member['bio'] ?? '') ?></textarea>
    </div>
    <div class="input-group">
      <label>Phone</label>
      <input type="text" name="phone" class="input" value="<?= e($member['phone'] ?? '') ?>">
    </div>
    <div class="input-group">
      <label>Photo</label>
      <input type="file" name="photo" class="input" accept="image/jpeg,image/png,image/webp">
      <?php if ($member && $member['photo']): ?>
        <div style="margin-top:8px;"><img src="<?= APP_URL ?>/public/uploads/team/<?= e($member['photo']) ?>" alt="" style="width:80px;height:80px;border-radius:50%;object-fit:cover;"></div>
      <?php endif; ?>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
      <div class="input-group">
        <label>Sort Order</label>
        <input type="number" name="sort_order" class="input" value="<?= e($member['sort_order'] ?? '0') ?>">
      </div>
      <div class="input-group">
        <label style="display:flex;align-items:center;gap:8px;margin-top:24px;">
          <input type="checkbox" name="is_active" value="1" <?= !$member || $member['is_active'] ? 'checked' : '' ?>> Active
        </label>
      </div>
    </div>
    <div style="margin-top:var(--space-5);">
      <button type="submit" class="btn btn-primary"><?= $id ? 'Update' : 'Create' ?> Member</button>
      <a href="/admin/team" class="btn btn-outline" style="margin-left:8px;">Cancel</a>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
