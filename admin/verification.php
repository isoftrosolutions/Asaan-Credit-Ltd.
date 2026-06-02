<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $docId = (int)($_POST['document_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    $stmt = db()->prepare('SELECT vd.*, u.id as uid FROM verification_documents vd JOIN users u ON u.id = vd.user_id WHERE vd.id = ?');
    $stmt->execute([$docId]);
    $doc = $stmt->fetch();

    if (!$doc) {
        flash_set('error', 'Document not found.');
        redirect('/admin/verification');
    }

    if ($action === 'approve') {
        db()->prepare("UPDATE verification_documents SET status = 'approved', reviewed_by = ?, reviewed_at = NOW() WHERE id = ?")->execute([$user['id'], $docId]);
        db()->prepare("UPDATE users SET verification_status = 'verified', verified_at = NOW() WHERE id = ?")->execute([$doc['uid']]);

        $notifStmt = db()->prepare('INSERT INTO notifications (user_id, type, title, body, action_url, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $notifStmt->execute([$doc['uid'], 'verification', 'Verification Approved', 'Your documents have been verified successfully. You now have full access to the platform.', '/dashboard']);

        admin_log('approve_verification', 'verification_document', $docId, ['user_id' => $doc['uid']]);
        flash_set('success', 'Verification approved and user verified.');
    } elseif ($action === 'reject') {
        $reason = trim($_POST['rejection_reason'] ?? '');
        if (!$reason) {
            flash_set('error', 'Please provide a rejection reason.');
            redirect('/admin/verification');
        }
        db()->prepare("UPDATE verification_documents SET status = 'rejected', rejection_reason = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?")->execute([$reason, $user['id'], $docId]);

        $notifStmt = db()->prepare('INSERT INTO notifications (user_id, type, title, body, action_url, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $notifStmt->execute([$doc['uid'], 'verification', 'Verification Rejected', 'Your verification documents were rejected: ' . $reason, '/profile']);

        admin_log('reject_verification', 'verification_document', $docId, ['user_id' => $doc['uid'], 'reason' => $reason]);
        flash_set('success', 'Verification rejected.');
    }
    redirect('/admin/verification');
}

$stmt = db()->query('SELECT vd.*, u.name, u.email, u.role FROM verification_documents vd JOIN users u ON u.id = vd.user_id WHERE vd.status = "pending" ORDER BY vd.created_at DESC');
$pendingDocs = $stmt->fetchAll();
$count = count($pendingDocs);

$approvedStmt = db()->query('SELECT vd.*, u.name, u.email FROM verification_documents vd JOIN users u ON u.id = vd.user_id WHERE vd.status IN ("approved","rejected") ORDER BY vd.reviewed_at DESC LIMIT 20');
$processedDocs = $approvedStmt->fetchAll();
$pageTitle = 'Verification Queue';
require __DIR__ . '/../includes/layout-admin.php';

ui_page_header('Verification Queue', '<strong>' . $count . '</strong> document' . ($count !== 1 ? 's' : '') . ' pending review.');
?>
<?php if ($count > 0): ?>
<div class="dash-panel">
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead><tr>
        <th>User</th><th>Role</th><th>Document type</th><th>File</th><th>Submitted</th><th class="ta-right">Actions</th>
      </tr></thead>
      <tbody>
    <?php foreach ($pendingDocs as $doc): ?>
    <tr>
      <td><span class="t-strong"><?= e($doc['name']) ?></span><br><span class="t-muted"><?= e($doc['email']) ?></span></td>
      <td><span class="dash-pill open"><?= e(ucfirst(str_replace('_', ' ', $doc['role']))) ?></span></td>
      <td><?= e($doc['document_type']) ?></td>
      <td><a href="<?= APP_URL ?>/public/uploads/<?= e($doc['file_path']) ?>" target="_blank" class="dash-section-link">View file</a></td>
      <td class="t-muted"><?= date_human($doc['created_at']) ?></td>
      <td class="ta-right">
        <span class="dash-table-actions">
          <form method="post" style="display:inline;">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
            <input type="hidden" name="document_id" value="<?= $doc['id'] ?>">
            <input type="hidden" name="action" value="approve">
            <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Approve this verification?')">Approve</button>
          </form>
          <form method="post" style="display:inline;" onsubmit="var r=prompt('Enter rejection reason:');if(!r||!r.trim()){alert('Reason required');return false;}this.rejection_reason.value=r;">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
            <input type="hidden" name="document_id" value="<?= $doc['id'] ?>">
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
  <?php ui_empty_state(['icon' => 'check', 'title' => 'Queue is clear', 'text' => 'No pending verification documents.']); ?>
</div>
<?php endif; ?>

<?php if (!empty($processedDocs)): ?>
<?php ui_section_header('Recently processed'); ?>
<div class="dash-panel">
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead><tr>
        <th>User</th><th class="ta-center">Status</th><th>Reason</th><th>Reviewed</th>
      </tr></thead>
      <tbody>
    <?php foreach ($processedDocs as $doc): ?>
    <tr>
      <td class="t-strong"><?= e($doc['name']) ?></td>
      <td class="ta-center"><span class="dash-pill <?= $doc['status'] === 'approved' ? 'published' : 'draft' ?>"><?= ucfirst($doc['status']) ?></span></td>
      <td class="t-muted"><?= e($doc['rejection_reason'] ?? '—') ?></td>
      <td class="t-muted"><?= date_human($doc['reviewed_at']) ?></td>
    </tr>
    <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
