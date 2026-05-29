<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

$pageTitle = 'Reports';
require __DIR__ . '/../includes/layout-admin.php';

$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $reportId = (int)($_POST['report_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    $stmt = db()->prepare('SELECT * FROM reports WHERE id = ?');
    $stmt->execute([$reportId]);
    $report = $stmt->fetch();

    if (!$report) {
        flash_set('error', 'Report not found.');
        redirect('/admin/reports');
    }

    if ($action === 'resolve') {
        $actionTaken = trim($_POST['action_taken'] ?? 'Reviewed and resolved');
        db()->prepare("UPDATE reports SET status = 'resolved', action_taken = ?, resolved_by = ?, resolved_at = NOW() WHERE id = ?")->execute([$actionTaken, $user['id'], $reportId]);
        admin_log('resolve_report', 'report', $reportId, ['action_taken' => $actionTaken]);
        flash_set('success', 'Report resolved.');
    } elseif ($action === 'dismiss') {
        db()->prepare("UPDATE reports SET status = 'dismissed', resolved_by = ?, resolved_at = NOW() WHERE id = ?")->execute([$user['id'], $reportId]);
        admin_log('dismiss_report', 'report', $reportId);
        flash_set('success', 'Report dismissed.');
    } elseif ($action === 'suspend_target') {
        db()->prepare('UPDATE users SET is_suspended = 1 WHERE id = ?')->execute([$report['target_id']]);
        db()->prepare("UPDATE reports SET status = 'resolved', action_taken = 'User suspended', resolved_by = ?, resolved_at = NOW() WHERE id = ?")->execute([$user['id'], $reportId]);
        admin_log('suspend_target_from_report', 'report', $reportId, ['target_id' => $report['target_id']]);
        flash_set('success', 'Target user suspended and report resolved.');
    } elseif ($action === 'hide_content') {
        $targetType = $report['target_type'];
        $targetId = $report['target_id'];
        $hideSql = match ($targetType) {
            'pitch' => 'UPDATE pitches SET is_hidden = 1 WHERE id = ?',
            'business' => 'UPDATE businesses SET is_hidden = 1 WHERE id = ?',
            'franchise' => 'UPDATE franchises SET is_hidden = 1 WHERE id = ?',
            default => null
        };
        if ($hideSql) {
            db()->prepare($hideSql)->execute([$targetId]);
        }
        db()->prepare("UPDATE reports SET status = 'resolved', action_taken = 'Content hidden', resolved_by = ?, resolved_at = NOW() WHERE id = ?")->execute([$user['id'], $reportId]);
        admin_log('hide_content_from_report', 'report', $reportId, ['target_type' => $targetType, 'target_id' => $targetId]);
        flash_set('success', 'Content hidden and report resolved.');
    }
    redirect('/admin/reports');
}

$stmt = db()->query("SELECT r.*, u.name AS reporter_name, u.email AS reporter_email FROM reports r JOIN users u ON u.id = r.reporter_id WHERE r.status = 'open' ORDER BY r.created_at DESC");
$openReports = $stmt->fetchAll();

$closedStmt = db()->query("SELECT r.*, u.name AS reporter_name FROM reports r JOIN users u ON u.id = r.reporter_id WHERE r.status IN ('resolved','dismissed') ORDER BY r.resolved_at DESC LIMIT 20");
$closedReports = $closedStmt->fetchAll();
?>
<h2>Reports — <?= count($openReports) ?> Open</h2>
<?php if (!empty($openReports)): ?>
<div class="card" style="margin-top:1rem;">
  <table style="width:100%;">
    <tr style="border-bottom:1px solid #eae8e6;">
      <th style="text-align:left;padding:8px;">Reporter</th>
      <th style="text-align:left;padding:8px;">Target</th>
      <th style="padding:8px;">Reason</th>
      <th style="padding:8px;">Details</th>
      <th style="padding:8px;">Date</th>
      <th style="padding:8px;">Actions</th>
    </tr>
    <?php foreach ($openReports as $r): ?>
    <tr style="border-bottom:1px solid #eee;">
      <td style="padding:10px 8px;font-weight:600;"><?= e($r['reporter_name']) ?></td>
      <td style="padding:10px 8px;"><?= e($r['target_type']) ?> #<?= $r['target_id'] ?></td>
      <td style="padding:10px 8px;"><?= e($r['reason']) ?></td>
      <td style="padding:10px 8px;max-width:200px;font-size:0.85rem;color:#666;"><?= e($r['details'] ?? '—') ?></td>
      <td style="padding:10px 8px;font-size:0.85rem;"><?= date_human($r['created_at']) ?></td>
      <td style="padding:10px 8px;white-space:nowrap;">
        <form method="post" style="display:inline;">
          <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
          <input type="hidden" name="report_id" value="<?= $r['id'] ?>">
          <input type="hidden" name="action" value="dismiss">
          <button type="submit" class="btn btn-sm btn-outline" onclick="return confirm('Dismiss this report?')">Dismiss</button>
        </form>
        <form method="post" style="display:inline;">
          <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
          <input type="hidden" name="report_id" value="<?= $r['id'] ?>">
          <input type="hidden" name="action" value="hide_content">
          <button type="submit" class="btn btn-sm btn-secondary" onclick="return confirm('Hide content?')">Hide</button>
        </form>
        <form method="post" style="display:inline;">
          <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
          <input type="hidden" name="report_id" value="<?= $r['id'] ?>">
          <input type="hidden" name="action" value="suspend_target">
          <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Suspend user?')">Suspend</button>
        </form>
        <details style="display:inline;vertical-align:middle;">
          <summary style="font-size:0.8rem;cursor:pointer;color:#C41E3A;display:inline;margin-left:0.25rem;">Resolve</summary>
          <form method="post" style="margin-top:0.25rem;">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
            <input type="hidden" name="report_id" value="<?= $r['id'] ?>">
            <input type="hidden" name="action" value="resolve">
            <input type="text" name="action_taken" class="input" placeholder="Action taken..." style="font-size:0.8rem;padding:4px 8px;width:180px;" required>
            <button type="submit" class="btn btn-sm btn-primary">Save</button>
          </form>
        </details>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php else: ?>
<div class="card" style="margin-top:1rem;padding:2rem;text-align:center;color:#888;">No open reports.</div>
<?php endif; ?>

<?php if (!empty($closedReports)): ?>
<h3 style="margin-top:2rem;">Recently Processed</h3>
<div class="card" style="margin-top:0.5rem;">
  <table style="width:100%;">
    <tr style="border-bottom:1px solid #eae8e6;">
      <th style="text-align:left;padding:8px;">Reporter</th>
      <th style="padding:8px;">Status</th>
      <th style="padding:8px;">Action Taken</th>
      <th style="padding:8px;">Resolved</th>
    </tr>
    <?php foreach ($closedReports as $r): ?>
    <tr style="border-bottom:1px solid #eee;">
      <td style="padding:10px 8px;"><?= e($r['reporter_name']) ?></td>
      <td style="padding:10px 8px;"><span style="color:#166534;"><?= e($r['status']) ?></span></td>
      <td style="padding:10px 8px;font-size:0.85rem;"><?= e($r['action_taken'] ?? '—') ?></td>
      <td style="padding:10px 8px;font-size:0.85rem;"><?= date_human($r['resolved_at']) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
