<?php
require __DIR__ . '/../../config/bootstrap.php';
require_admin();

$pageTitle = 'Investor Partners';
require __DIR__ . '/../../includes/layout-admin.php';

$uploadDir = upload_path('investor-partners');

function investor_partner_logo_upload(?array $file, string $uploadDir): ?string {
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $filename = handle_upload($file, ['image/jpeg', 'image/png', 'image/webp'], 2097152, $uploadDir);
    return $filename ? 'investor-partners/' . $filename : null;
}

function investor_partner_delete_logo(?string $path): void {
    if (!$path || !str_starts_with($path, 'investor-partners/')) {
        return;
    }
    $fullPath = PUBLIC_UPLOADS_PATH . '/' . $path;
    if (is_file($fullPath)) {
        @unlink($fullPath);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'create' || $action === 'update') {
        $name = trim($_POST['name'] ?? '');
        $partnerType = trim($_POST['partner_type'] ?? '');
        $initials = mb_strtoupper(trim($_POST['initials'] ?? ''));
        $accentColor = trim($_POST['accent_color'] ?? '#98202A');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $logoPath = investor_partner_logo_upload($_FILES['logo'] ?? null, $uploadDir);

        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $accentColor)) {
            $accentColor = '#98202A';
        }

        if ($name === '') {
            flash_set('error', 'Partner name is required.');
            redirect('/admin/investor-partners');
        }

        if ($action === 'create') {
            db()->prepare('INSERT INTO investor_partners (name, partner_type, logo_path, initials, accent_color, sort_order, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())')
                ->execute([$name, $partnerType, $logoPath, $initials, $accentColor, $sortOrder, $isActive]);
            admin_log('create_investor_partner', 'investor_partner', db()->lastInsertId(), ['name' => $name]);
            flash_set('success', 'Investor partner created.');
        } elseif ($id) {
            $existingLogo = db()->prepare('SELECT logo_path FROM investor_partners WHERE id = ?');
            $existingLogo->execute([$id]);
            $oldLogo = $existingLogo->fetchColumn();

            if ($logoPath) {
                db()->prepare('UPDATE investor_partners SET name = ?, partner_type = ?, logo_path = ?, initials = ?, accent_color = ?, sort_order = ?, is_active = ?, updated_at = NOW() WHERE id = ?')
                    ->execute([$name, $partnerType, $logoPath, $initials, $accentColor, $sortOrder, $isActive, $id]);
                investor_partner_delete_logo($oldLogo ?: null);
            } else {
                db()->prepare('UPDATE investor_partners SET name = ?, partner_type = ?, initials = ?, accent_color = ?, sort_order = ?, is_active = ?, updated_at = NOW() WHERE id = ?')
                    ->execute([$name, $partnerType, $initials, $accentColor, $sortOrder, $isActive, $id]);
            }

            admin_log('update_investor_partner', 'investor_partner', $id, ['name' => $name]);
            flash_set('success', 'Investor partner updated.');
        }
    } elseif ($action === 'toggle' && $id) {
        db()->prepare('UPDATE investor_partners SET is_active = NOT is_active, updated_at = NOW() WHERE id = ?')->execute([$id]);
        admin_log('toggle_investor_partner', 'investor_partner', $id);
        flash_set('success', 'Investor partner status updated.');
    } elseif ($action === 'remove_logo' && $id) {
        $stmt = db()->prepare('SELECT logo_path FROM investor_partners WHERE id = ?');
        $stmt->execute([$id]);
        $logoPath = $stmt->fetchColumn();
        db()->prepare('UPDATE investor_partners SET logo_path = NULL, updated_at = NOW() WHERE id = ?')->execute([$id]);
        investor_partner_delete_logo($logoPath ?: null);
        admin_log('remove_investor_partner_logo', 'investor_partner', $id);
        flash_set('success', 'Logo removed.');
    } elseif ($action === 'delete' && $id) {
        $stmt = db()->prepare('SELECT logo_path FROM investor_partners WHERE id = ?');
        $stmt->execute([$id]);
        $logoPath = $stmt->fetchColumn();
        db()->prepare('DELETE FROM investor_partners WHERE id = ?')->execute([$id]);
        investor_partner_delete_logo($logoPath ?: null);
        admin_log('delete_investor_partner', 'investor_partner', $id);
        flash_set('success', 'Investor partner deleted.');
    }

    redirect('/admin/investor-partners');
}

$partners = db()->query('SELECT * FROM investor_partners ORDER BY sort_order ASC, id ASC')->fetchAll();
?>
<style>
  .partner-logo-admin {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    border: 1px solid var(--dash-border);
    object-fit: contain;
    background: #fff;
    padding: 6px;
  }
  .partner-mark-admin {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 800;
    font-family: var(--font-heading);
  }
  .partner-edit-grid {
    display: grid;
    grid-template-columns: 1.2fr 1fr .7fr .7fr .6fr .8fr auto;
    gap: 10px;
    align-items: end;
  }
  @media (max-width: 1100px) {
    .partner-edit-grid { grid-template-columns: 1fr 1fr; }
  }
</style>

<div class="dash-pagehead">
  <div class="dash-pagehead-text">
    <h1 class="dash-pagehead-title">Investor Partners</h1>
    <p class="dash-pagehead-sub"><strong><?= count($partners) ?></strong> partner logos for the homepage marquee</p>
  </div>
</div>

<div class="dash-panel dash-panel-pad" style="margin-bottom:var(--space-5);">
  <details>
    <summary style="cursor:pointer;font-weight:600;font-size:0.95rem;color:var(--dash-primary);padding:4px 0;">+ Add investor partner</summary>
    <form method="post" enctype="multipart/form-data" style="margin-top:var(--space-4);">
      <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
      <input type="hidden" name="action" value="create">
      <div class="partner-edit-grid">
        <div class="input-group">
          <label>Name</label>
          <input type="text" name="name" class="input" required placeholder="e.g. Summit Growth Capital">
        </div>
        <div class="input-group">
          <label>Type</label>
          <input type="text" name="partner_type" class="input" placeholder="Investor Network">
        </div>
        <div class="input-group">
          <label>Initials</label>
          <input type="text" name="initials" class="input" maxlength="8" placeholder="SG">
        </div>
        <div class="input-group">
          <label>Accent</label>
          <input type="color" name="accent_color" class="input" value="#98202A" style="height:44px;">
        </div>
        <div class="input-group">
          <label>Order</label>
          <input type="number" name="sort_order" class="input" value="0">
        </div>
        <div class="input-group">
          <label>Logo image</label>
          <input type="file" name="logo" class="input" accept="image/jpeg,image/png,image/webp">
        </div>
        <label style="display:flex;align-items:center;gap:8px;min-height:44px;">
          <input type="checkbox" name="is_active" value="1" checked style="width:18px;height:18px;accent-color:#98202A;"> Active
        </label>
      </div>
      <button type="submit" class="btn btn-sm btn-primary" style="margin-top:var(--space-4);">Create Partner</button>
    </form>
  </details>
</div>

<?php if (empty($partners)): ?>
<div class="dash-panel">
  <?php ui_empty_state(['icon' => 'users', 'title' => 'No investor partners yet', 'text' => 'Add partner logos to replace the homepage fallback logo strip.']); ?>
</div>
<?php else: ?>
<div class="dash-panel">
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead>
        <tr>
          <th style="width:80px;">Logo</th>
          <th>Partner</th>
          <th style="width:90px;">Order</th>
          <th style="width:90px;" class="ta-center">Active</th>
          <th style="width:260px;" class="ta-right">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($partners as $partner): ?>
        <tr>
          <td>
            <?php if (!empty($partner['logo_path'])): ?>
              <img src="<?= e(upload_url($partner['logo_path'])) ?>" alt="<?= e($partner['name']) ?> logo" class="partner-logo-admin">
            <?php else: ?>
              <span class="partner-mark-admin" style="background:<?= e($partner['accent_color']) ?>;"><?= e($partner['initials'] ?: mb_strtoupper(mb_substr($partner['name'], 0, 2))) ?></span>
            <?php endif; ?>
          </td>
          <td>
            <details>
              <summary style="cursor:pointer;">
                <span class="t-strong"><?= e($partner['name']) ?></span>
                <?php if (!empty($partner['partner_type'])): ?><span class="t-muted"> - <?= e($partner['partner_type']) ?></span><?php endif; ?>
              </summary>
              <form method="post" enctype="multipart/form-data" style="margin-top:var(--space-4);">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= (int)$partner['id'] ?>">
                <div class="partner-edit-grid">
                  <div class="input-group">
                    <label>Name</label>
                    <input type="text" name="name" class="input" required value="<?= e($partner['name']) ?>">
                  </div>
                  <div class="input-group">
                    <label>Type</label>
                    <input type="text" name="partner_type" class="input" value="<?= e($partner['partner_type']) ?>">
                  </div>
                  <div class="input-group">
                    <label>Initials</label>
                    <input type="text" name="initials" class="input" maxlength="8" value="<?= e($partner['initials']) ?>">
                  </div>
                  <div class="input-group">
                    <label>Accent</label>
                    <input type="color" name="accent_color" class="input" value="<?= e($partner['accent_color'] ?: '#98202A') ?>" style="height:44px;">
                  </div>
                  <div class="input-group">
                    <label>Order</label>
                    <input type="number" name="sort_order" class="input" value="<?= (int)$partner['sort_order'] ?>">
                  </div>
                  <div class="input-group">
                    <label>Replace logo</label>
                    <input type="file" name="logo" class="input" accept="image/jpeg,image/png,image/webp">
                  </div>
                  <label style="display:flex;align-items:center;gap:8px;min-height:44px;">
                    <input type="checkbox" name="is_active" value="1" <?= $partner['is_active'] ? 'checked' : '' ?> style="width:18px;height:18px;accent-color:#98202A;"> Active
                  </label>
                </div>
                <button type="submit" class="btn btn-sm btn-primary" style="margin-top:var(--space-4);">Save Changes</button>
              </form>
            </details>
          </td>
          <td class="t-muted"><?= (int)$partner['sort_order'] ?></td>
          <td class="ta-center">
            <span class="dash-pill <?= $partner['is_active'] ? 'published' : 'draft' ?>"><?= $partner['is_active'] ? 'Yes' : 'No' ?></span>
          </td>
          <td class="ta-right">
            <span class="dash-table-actions">
              <form method="post" style="display:inline;">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="id" value="<?= (int)$partner['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline"><?= $partner['is_active'] ? 'Deactivate' : 'Activate' ?></button>
              </form>
              <?php if (!empty($partner['logo_path'])): ?>
              <form method="post" style="display:inline;">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="remove_logo">
                <input type="hidden" name="id" value="<?= (int)$partner['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline">Remove Logo</button>
              </form>
              <?php endif; ?>
              <form method="post" style="display:inline;" onsubmit="return confirm('Delete this investor partner?');">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$partner['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline">Delete</button>
              </form>
            </span>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

