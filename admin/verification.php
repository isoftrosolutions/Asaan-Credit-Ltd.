<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $userId = (int)($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    $stmt = db()->prepare('SELECT id, name, email, verification_status FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $target = $stmt->fetch();

    if (!$target) {
        flash_set('error', 'User not found.');
        redirect('/admin/verification');
    }

    if ($action === 'approve') {
        db()->prepare("UPDATE users SET verification_status = 'verified', verified_at = NOW() WHERE id = ?")->execute([$userId]);

        $notifStmt = db()->prepare('INSERT INTO notifications (user_id, type, title, body, action_url, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $notifStmt->execute([$userId, 'verification', 'Verification Approved', 'Your account has been verified. You now have full access to the platform.', '/dashboard']);

        send_verification_approved_email($target['email'], $target['name']);

        admin_log('approve_verification', 'user', $userId, ['email' => $target['email']]);
        flash_set('success', 'User verified successfully.');
    } elseif ($action === 'reject') {
        $reason = trim($_POST['rejection_reason'] ?? '');
        if (!$reason) {
            flash_set('error', 'Please provide a rejection reason.');
            redirect('/admin/verification');
        }
        db()->prepare("UPDATE users SET verification_status = 'rejected', updated_at = NOW() WHERE id = ?")->execute([$userId]);

        $notifStmt = db()->prepare('INSERT INTO notifications (user_id, type, title, body, action_url, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $notifStmt->execute([$userId, 'verification', 'Verification Rejected', 'Your account verification was rejected: ' . $reason, '/profile']);

        send_verification_rejected_email($target['email'], $target['name'], $reason);

        admin_log('reject_verification', 'user', $userId, ['email' => $target['email'], 'reason' => $reason]);
        flash_set('success', 'User verification rejected.');
    }
    redirect('/admin/verification');
}

$stmt = db()->query("SELECT id, name, email, role, account_type, company_name, created_at FROM users WHERE verification_status IN ('pending','unverified') ORDER BY created_at DESC");
$pendingUsers = $stmt->fetchAll();
$count = count($pendingUsers);

$recentStmt = db()->query("SELECT id, name, email, verification_status, verified_at, created_at FROM users WHERE verification_status IN ('verified','rejected') ORDER BY COALESCE(verified_at, updated_at) DESC LIMIT 20");
$processedUsers = $recentStmt->fetchAll();
$pageTitle = 'Verification Queue';
require __DIR__ . '/../includes/layout-admin.php';

ui_page_header('Verification Queue', '<strong>' . $count . '</strong> user' . ($count !== 1 ? 's' : '') . ' pending review.');
?>
<?php if ($count > 0): ?>
<div class="dash-panel">
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead><tr>
        <th>User</th><th>Role</th><th>Type</th><th>Registered</th><th class="ta-right">Actions</th>
      </tr></thead>
      <tbody>
    <?php foreach ($pendingUsers as $u): ?>
    <tr>
      <td>
        <span class="t-strong"><?= e($u['name']) ?></span><br>
        <span class="t-muted"><?= e($u['email']) ?></span>
        <?php if ($u['company_name']): ?><br><span class="t-muted"><?= e($u['company_name']) ?></span><?php endif; ?>
      </td>
      <td><span class="dash-pill open"><?= e(ucfirst(str_replace('_', ' ', $u['role']))) ?></span></td>
      <td class="t-muted"><?= e(ucfirst($u['account_type'])) ?></td>
      <td class="t-muted"><?= date_human($u['created_at']) ?></td>
      <td class="ta-right">
        <span class="dash-table-actions">
          <form method="post" style="display:inline;">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
            <input type="hidden" name="action" value="approve">
            <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Verify this user?')">Approve</button>
          </form>
          <form method="post" style="display:inline;" onsubmit="var r=prompt('Enter rejection reason:');if(!r||!r.trim()){alert('Reason required');return false;}this.rejection_reason.value=r;">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
            <input type="hidden" name="action" value="reject">
            <input type="hidden" name="rejection_reason" value="">
            <button type="submit" class="btn btn-sm btn-outline">Reject</button>
          </form>
        </span>
      </td>
    </tr>
    <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php else: ?>
<div class="dash-panel">
  <?php ui_empty_state(['icon' => 'check', 'title' => 'Queue is clear', 'text' => 'No pending users.']); ?>
</div>
<?php endif; ?>

<?php if (!empty($processedUsers)): ?>
<?php ui_section_header('Recently processed'); ?>
<div class="dash-panel">
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead><tr>
        <th>User</th><th class="ta-center">Status</th><th>Processed</th>
      </tr></thead>
      <tbody>
    <?php foreach ($processedUsers as $u): ?>
    <tr>
      <td>
        <span class="t-strong"><?= e($u['name']) ?></span><br>
        <span class="t-muted"><?= e($u['email']) ?></span>
      </td>
      <td class="ta-center"><span class="dash-pill <?= $u['verification_status'] === 'verified' ? 'published' : 'draft' ?>"><?= ucfirst($u['verification_status']) ?></span></td>
      <td class="t-muted"><?= $u['verified_at'] ? date_human($u['verified_at']) : '—' ?></td>
    </tr>
    <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
