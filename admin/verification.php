<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

$pageTitle = 'Verification Queue';
require __DIR__ . '/../includes/layout-admin.php';

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
?>
<h2>Verification Queue — <?= $count ?> Pending</h2>
<?php if ($count > 0): ?>
<div class="card" style="margin-top:1rem;">
  <table style="width:100%;">
    <tr style="border-bottom:1px solid #eae8e6;">
      <th style="text-align:left;padding:8px;">User</th>
      <th style="text-align:left;padding:8px;">Role</th>
      <th style="padding:8px;">Document Type</th>
      <th style="padding:8px;">File</th>
      <th style="padding:8px;">Submitted</th>
      <th style="padding:8px;">Actions</th>
    </tr>
    <?php foreach ($pendingDocs as $doc): ?>
    <tr style="border-bottom:1px solid #eee;">
      <td style="padding:10px 8px;font-weight:600;"><?= e($doc['name']) ?><br><small style="color:#666;"><?= e($doc['email']) ?></small></td>
      <td style="padding:10px 8px;"><?= e($doc['role']) ?></td>
      <td style="padding:10px 8px;"><?= e($doc['document_type']) ?></td>
      <td style="padding:10px 8px;"><a href="<?= APP_URL ?>/public/uploads/<?= e($doc['file_path']) ?>" target="_blank" style="color:#C41E3A;">View</a></td>
      <td style="padding:10px 8px;font-size:0.85rem;"><?= date_human($doc['created_at']) ?></td>
      <td style="padding:10px 8px;">
        <form method="post" style="display:inline;">
          <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
          <input type="hidden" name="document_id" value="<?= $doc['id'] ?>">
          <input type="hidden" name="action" value="approve">
          <button type="submit" class="btn btn-sm" style="background:#166534;color:white;" onclick="return confirm('Approve this verification?')">Approve</button>
        </form>
        <form method="post" style="display:inline;margin-left:0.25rem;" onsubmit="var r=prompt('Enter rejection reason:');if(!r||!r.trim()){alert('Reason required');return false;}this.reason.value=r;">
          <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
          <input type="hidden" name="document_id" value="<?= $doc['id'] ?>">
          <input type="hidden" name="action" value="reject">
          <input type="hidden" name="rejection_reason" value="">
          <button type="submit" class="btn btn-sm" style="background:#b91c1c;color:white;">Reject</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php else: ?>
<div class="card" style="margin-top:1rem;padding:2rem;text-align:center;color:#888;">
  No pending verification documents.
</div>
<?php endif; ?>

<?php if (!empty($processedDocs)): ?>
<h3 style="margin-top:2rem;">Recently Processed</h3>
<div class="card" style="margin-top:0.5rem;">
  <table style="width:100%;">
    <tr style="border-bottom:1px solid #eae8e6;">
      <th style="text-align:left;padding:8px;">User</th>
      <th style="padding:8px;">Status</th>
      <th style="padding:8px;">Reason</th>
      <th style="padding:8px;">Reviewed</th>
    </tr>
    <?php foreach ($processedDocs as $doc): ?>
    <tr style="border-bottom:1px solid #eee;">
      <td style="padding:10px 8px;font-weight:600;"><?= e($doc['name']) ?></td>
      <td style="padding:10px 8px;"><?php if ($doc['status'] === 'approved'): ?><span style="color:#166534;font-weight:600;">Approved</span><?php else: ?><span style="color:#b91c1c;font-weight:600;">Rejected</span><?php endif; ?></td>
      <td style="padding:10px 8px;font-size:0.85rem;"><?= e($doc['rejection_reason'] ?? '—') ?></td>
      <td style="padding:10px 8px;font-size:0.85rem;"><?= date_human($doc['reviewed_at']) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
