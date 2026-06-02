<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

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
$pageTitle = 'Reports';
require __DIR__ . '/../includes/layout-admin.php';

ui_page_header('Reports', '<strong>' . count($openReports) . '</strong> open report' . (count($openReports) !== 1 ? 's' : '') . ' awaiting action.');
?>
<?php if (!empty($openReports)): ?>
<div class="dash-panel">
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead><tr>
        <th>Reporter</th><th>Target</th><th>Reason</th><th>Details</th><th>Date</th><th class="ta-right">Actions</th>
      </tr></thead>
      <tbody>
    <?php foreach ($openReports as $r): ?>
    <tr>
      <td class="t-strong"><?= e($r['reporter_name']) ?></td>
      <td><span class="dash-pill open"><?= e($r['target_type']) ?> #<?= $r['target_id'] ?></span></td>
      <td><?= e($r['reason']) ?></td>
      <td class="t-muted" style="max-width:200px;"><?= e($r['details'] ?? '—') ?></td>
      <td class="t-muted"><?= date_human($r['created_at']) ?></td>
      <td class="ta-right" style="white-space:nowrap;">
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
          <summary style="font-size:0.8rem;cursor:pointer;color:var(--color-primary-vivid);display:inline;margin-left:0.25rem;">Resolve</summary>
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
      </tbody>
    </table>
  </div>
</div>
<?php else: ?>
<div class="dash-panel">
  <?php ui_empty_state(['icon' => 'check', 'title' => 'No open reports', 'text' => 'Everything is clear right now.']); ?>
</div>
<?php endif; ?>

<?php if (!empty($closedReports)): ?>
<?php ui_section_header('Recently processed'); ?>
<div class="dash-panel">
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead><tr>
        <th>Reporter</th><th class="ta-center">Status</th><th>Action taken</th><th>Resolved</th>
      </tr></thead>
      <tbody>
    <?php foreach ($closedReports as $r): ?>
    <tr>
      <td><?= e($r['reporter_name']) ?></td>
      <td class="ta-center"><span class="dash-pill <?= $r['status'] === 'resolved' ? 'published' : 'draft' ?>"><?= e($r['status']) ?></span></td>
      <td class="t-muted"><?= e($r['action_taken'] ?? '—') ?></td>
      <td class="t-muted"><?= date_human($r['resolved_at']) ?></td>
    </tr>
    <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
