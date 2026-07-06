<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

$user = current_user();
$backupDir = __DIR__ . '/../storage/backups';

$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        require_once __DIR__ . '/../scripts/backup.php';
        $result = run_backup();
        if ($result['success']) {
            $mb = number_format($result['size'] / 1048576, 2);
            $message = "Backup created: {$result['file']} ({$mb} MB).";
            $messageType = 'success';
            admin_log('create_backup', 'backup', 0, ['file' => $result['file'], 'size' => $result['size']]);
        } else {
            $message = 'Backup failed: ' . $result['error'];
            $messageType = 'error';
        }
    } elseif ($action === 'delete') {
        $filename = basename($_POST['file'] ?? '');
        $path = $backupDir . '/' . $filename;
        if ($filename && str_starts_with($filename, 'backup_') && str_ends_with($filename, '.zip') && file_exists($path)) {
            unlink($path);
            $message = "Deleted: {$filename}";
            $messageType = 'success';
            admin_log('delete_backup', 'backup', 0, ['file' => $filename]);
        } else {
            $message = 'Invalid backup file.';
            $messageType = 'error';
        }
    }
}

// Handle download
$downloadFile = $_GET['download'] ?? '';
if ($downloadFile) {
    $safe = basename($downloadFile);
    $path = $backupDir . '/' . $safe;
    if ($safe && str_starts_with($safe, 'backup_') && str_ends_with($safe, '.zip') && file_exists($path)) {
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $safe . '"');
        header('Content-Length: ' . filesize($path));
        header('X-Accel-Buffering: no');
        readfile($path);
        exit;
    }
    $message = 'Backup file not found.';
    $messageType = 'error';
}

// List backups
$backups = [];
foreach (glob($backupDir . '/backup_*.zip') as $path) {
    $backups[] = [
        'name' => basename($path),
        'size' => filesize($path),
        'modified' => filemtime($path),
    ];
}
usort($backups, fn($a, $b) => $b['modified'] - $a['modified']);

$pageTitle = 'Backups — ' . APP_NAME;
require __DIR__ . '/../includes/layout-dashboard.php';
?>

<style>
.bu-wrap { max-width:960px; margin:0 auto; padding:24px 16px 48px; }
.bu-head { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:20px; flex-wrap:wrap; }
.bu-head h1 { margin:0; font-size:24px; letter-spacing:-.03em; }
.bu-stat-row { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:20px; }
.bu-stat { background:#fff; border-radius:14px; border:1px solid var(--dash-border); box-shadow:var(--dash-shadow); padding:18px 20px; }
.bu-stat small { display:block; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color:var(--dash-ink-soft); margin-bottom:4px; }
.bu-stat strong { font-size:28px; letter-spacing:-.04em; }
.bu-stat p { margin:4px 0 0; font-size:12px; color:var(--dash-ink-soft); }
.bu-table { width:100%; border-collapse:collapse; background:#fff; border-radius:14px; border:1px solid var(--dash-border); box-shadow:var(--dash-shadow); overflow:hidden; }
.bu-table th { text-align:left; padding:12px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:var(--dash-ink-soft); background:var(--color-bg-soft); border-bottom:1px solid var(--dash-border); }
.bu-table td { padding:12px 16px; font-size:13px; border-top:1px solid var(--dash-border); }
.bu-table td.actions { text-align:right; white-space:nowrap; }
.bu-empty { text-align:center; padding:48px 20px; color:var(--dash-ink-soft); }
.bu-empty .ico { font-size:48px; opacity:.3; margin-bottom:8px; }
.bu-empty p { margin:0 0 16px; font-size:14px; }
@media (max-width:700px) {
  .bu-stat-row { grid-template-columns:1fr; }
}
</style>

<div class="bu-wrap">
  <div class="bu-head">
    <h1>Backups</h1>
    <form method="post" style="display:inline;">
      <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
      <input type="hidden" name="action" value="create">
      <button type="submit" class="btn btn-primary" id="buCreateBtn">Create backup</button>
    </form>
  </div>

  <?php if ($message): ?>
  <div class="kyc-alert <?= $messageType ?>"><?= e($message) ?></div>
  <?php endif; ?>

  <?php
  $latestSize = $backups[0]['size'] ?? 0;
  $totalSize = array_sum(array_column($backups, 'size'));
  $count = count($backups);
  ?>
  <div class="bu-stat-row">
    <div class="bu-stat">
      <small>Latest backup</small>
      <strong><?= $count ? date('M j, g:ia', $backups[0]['modified']) : '—' ?></strong>
      <p><?= $latestSize ? number_format($latestSize / 1048576, 2) . ' MB' : '' ?></p>
    </div>
    <div class="bu-stat">
      <small>Total backups</small>
      <strong><?= $count ?></strong>
      <p>Stored in storage/backups/</p>
    </div>
    <div class="bu-stat">
      <small>Total size</small>
      <strong><?= $totalSize ? number_format($totalSize / 1048576, 2) . ' MB' : '0 MB' ?></strong>
      <p>Auto-prunes after 30 days</p>
    </div>
  </div>

  <?php if (empty($backups)): ?>
  <div class="bu-empty">
    <div class="ico"><?php ui_icon('document'); ?></div>
    <p>No backups yet. Click "Create backup" to generate your first one.</p>
  </div>
  <?php else: ?>
  <table class="bu-table">
    <thead>
      <tr>
        <th>Backup file</th>
        <th>Date</th>
        <th>Size</th>
        <th class="actions">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($backups as $b): ?>
      <tr>
        <td><code><?= e($b['name']) ?></code></td>
        <td><?= date('M j, Y g:ia', $b['modified']) ?></td>
        <td><?= number_format($b['size'] / 1048576, 2) ?> MB</td>
        <td class="actions">
          <a href="?download=<?= urlencode($b['name']) ?>" class="btn btn-sm btn-outline">Download</a>
          <form method="post" style="display:inline;" onsubmit="return confirm('Delete <?= e($b['name']) ?>?')">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="file" value="<?= e($b['name']) ?>">
            <button type="submit" class="btn btn-sm btn-outline" style="color:var(--color-error);border-color:var(--color-error);">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<script>
document.getElementById('buCreateBtn')?.addEventListener('click', function() {
  this.disabled = true;
  this.textContent = 'Creating…';
});
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
