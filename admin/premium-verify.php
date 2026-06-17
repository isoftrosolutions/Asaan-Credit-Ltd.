<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $subId = (int)($_POST['sub_id'] ?? 0);
  $action = $_POST['action'] ?? '';

  $stmt = db()->prepare("
    SELECT ps.*, u.name, u.email, u.is_premium
    FROM premium_subscriptions ps
    JOIN users u ON u.id = ps.user_id
    WHERE ps.id = ?
  ");
  $stmt->execute([$subId]);
  $sub = $stmt->fetch();

  if (!$sub) {
    flash_set('error', 'Subscription not found.');
    redirect('/admin/premium-verify');
  }

  $userId = (int)$sub['user_id'];

  if ($action === 'approve') {
    $durationMonths = (int)$sub['duration_months'];
    $expiryDate = date('Y-m-d', strtotime("+$durationMonths months"));

    db()->beginTransaction();
    try {
      db()->prepare("UPDATE users SET is_premium = 1 WHERE id = ?")->execute([$userId]);
      db()->prepare("
        UPDATE premium_subscriptions
        SET status = 'active', activated_by = ?, activated_at = NOW(), expiry_date = ?
        WHERE id = ?
      ")->execute([(int)$_SESSION['user']['id'], $expiryDate, $subId]);

      admin_log('premium_verified', 'premium_subscription', $subId, [
        'user_id' => $userId,
        'plan' => $sub['plan_type'],
        'amount' => $sub['amount'],
      ]);

      db()->commit();
    } catch (\Throwable $e) {
      db()->rollBack();
      flash_set('error', 'Database error: ' . $e->getMessage());
      redirect('/admin/premium-verify');
    }

    $mailBody = '<div style="font-family:sans-serif;max-width:600px;margin:20px auto;padding:32px;border:1px solid #eef2f6;border-radius:16px;text-align:center;">
      <div style="font-size:48px;margin-bottom:16px;">👑</div>
      <h2 style="color:#6B1D22;margin:0 0 8px;">Your Premium Account is Activated!</h2>
      <p style="color:#555;margin:0 0 20px;font-size:15px;line-height:1.6;">
        Congratulations <strong>' . e($sub['name']) . '</strong>, your premium subscription has been verified and activated.
      </p>
      <table style="width:100%;max-width:360px;margin:0 auto 20px;border-collapse:collapse;font-size:14px;">
        <tr><td style="padding:8px 12px;border:1px solid #eef2f6;color:#888;">Plan</td><td style="padding:8px 12px;border:1px solid #eef2f6;font-weight:600;">' . e($sub['plan_label']) . ' — ' . $durationMonths . ' Months</td></tr>
        <tr><td style="padding:8px 12px;border:1px solid #eef2f6;color:#888;">Amount</td><td style="padding:8px 12px;border:1px solid #eef2f6;font-weight:600;">NPR ' . number_format((float)$sub['amount']) . '</td></tr>
        <tr><td style="padding:8px 12px;border:1px solid #eef2f6;color:#888;">Activated</td><td style="padding:8px 12px;border:1px solid #eef2f6;">' . date('M j, Y') . '</td></tr>
        <tr><td style="padding:8px 12px;border:1px solid #eef2f6;color:#888;">Expires</td><td style="padding:8px 12px;border:1px solid #eef2f6;">' . date('M j, Y', strtotime($expiryDate)) . '</td></tr>
      </table>
      <a href="' . APP_URL . '/dashboard" style="display:inline-block;padding:12px 28px;background:#6B1D22;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;">Go to Dashboard</a>
    </div>';
    EmailService::getInstance()->sendCustomEmail($sub['email'], 'Premium Account Activated 🎉', $mailBody);

    flash_set('success', $sub['name'] . '\'s premium subscription has been approved and activated.');

  } elseif ($action === 'reject') {
    db()->prepare("UPDATE premium_subscriptions SET status = 'rejected' WHERE id = ?")->execute([$subId]);
    admin_log('premium_rejected', 'premium_subscription', $subId, [
      'user_id' => $userId,
      'plan' => $sub['plan_type'],
    ]);

    $mailBody = '<div style="font-family:sans-serif;max-width:600px;margin:20px auto;padding:32px;border:1px solid #eef2f6;border-radius:16px;text-align:center;">
      <div style="font-size:48px;margin-bottom:16px;">😕</div>
      <h2 style="color:#6B1D22;margin:0 0 8px;">Premium Payment Not Verified</h2>
      <p style="color:#555;margin:0 0 20px;font-size:15px;line-height:1.6;">
        Hi <strong>' . e($sub['name']) . '</strong>, we were unable to verify your payment receipt. Please contact support or try submitting a new payment.
      </p>
      <a href="' . APP_URL . '/upgrade" style="display:inline-block;padding:12px 28px;background:#6B1D22;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;">Try Again</a>
    </div>';
    EmailService::getInstance()->sendCustomEmail($sub['email'], 'Premium Payment Update', $mailBody);

    flash_set('success', $sub['name'] . '\'s payment has been rejected. Email sent.');
  }

  redirect('/admin/premium-verify');
}

$tab = $_GET['tab'] ?? 'pending';
$search = trim($_GET['search'] ?? '');

$where = '1=1';
$params = [];
$statusFilter = '';

if ($tab === 'pending') {
  $where .= ' AND ps.status = ?';
  $params[] = 'pending';
} elseif ($tab === 'active') {
  $where .= ' AND ps.status = ?';
  $params[] = 'active';
} elseif ($tab === 'rejected') {
  $where .= ' AND ps.status = ?';
  $params[] = 'rejected';
}

if ($search) {
  $where .= ' AND (u.name LIKE ? OR u.email LIKE ?)';
  $params[] = "%$search%";
  $params[] = "%$search%";
}

$countStmt = db()->prepare("SELECT COUNT(*) FROM premium_subscriptions ps JOIN users u ON u.id = ps.user_id WHERE $where");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 25;
$lastPage = max(1, (int)ceil($total / $perPage));
$page = min($page, $lastPage);
$offset = ($page - 1) * $perPage;

$rows = db()->prepare("
  SELECT ps.*, u.name, u.email, u.role, u.verification_status, u.is_premium
  FROM premium_subscriptions ps
  JOIN users u ON u.id = ps.user_id
  WHERE $where
  ORDER BY ps.created_at DESC
  LIMIT $perPage OFFSET $offset
");
$rows->execute($params);
$subscriptions = $rows->fetchAll();

$pageTitle = 'Premium Payment Verification';
require __DIR__ . '/../includes/layout-admin.php';

ui_page_header('Premium Payment Verification', $total . ' total · ' . count($subscriptions) . ' shown');
?>

<div class="dash-panel">
  <div class="dash-tabs" style="display:flex;gap:4px;margin-bottom:20px;">
    <a href="?tab=pending" class="btn btn-sm <?= $tab === 'pending' ? 'btn-primary' : 'btn-outline' ?>">Pending</a>
    <a href="?tab=active" class="btn btn-sm <?= $tab === 'active' ? 'btn-primary' : 'btn-outline' ?>">Active</a>
    <a href="?tab=rejected" class="btn btn-sm <?= $tab === 'rejected' ? 'btn-primary' : 'btn-outline' ?>">Rejected</a>
    <a href="?" class="btn btn-sm <?= $tab === '' ? 'btn-primary' : 'btn-outline' ?>">All</a>
    <div style="flex:1;"></div>
    <form method="get" style="display:flex;gap:8px;">
      <?php if ($tab): ?><input type="hidden" name="tab" value="<?= e($tab) ?>"><?php endif; ?>
      <input type="text" name="search" class="input" placeholder="Search by user..." value="<?= e($search) ?>" style="width:200px;">
      <button type="submit" class="btn btn-sm btn-primary">Search</button>
      <?php if ($search): ?><a href="?tab=<?= e($tab) ?>" class="btn btn-sm btn-outline">Clear</a><?php endif; ?>
    </form>
  </div>

  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead>
        <tr>
          <th>User</th>
          <th>Plan</th>
          <th>Amount</th>
          <th>Transaction ID</th>
          <th>Receipt</th>
          <th>Date</th>
          <th>Status</th>
          <th class="ta-right">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($subscriptions as $sub): ?>
        <?php
          $statusLabel = ucfirst($sub['status']);
          $statusClass = match($sub['status']) {
            'pending' => 'open',
            'active'  => 'published',
            'rejected' => 'draft',
            'expired'  => 'draft',
            default   => 'draft',
          };
        ?>
        <tr>
          <td class="t-strong">
            <?= e($sub['name']) ?><br>
            <span class="t-muted" style="font-size:12px;"><?= e($sub['email']) ?></span>
          </td>
          <td>
            <?= e($sub['plan_label']) ?><br>
            <span class="t-muted" style="font-size:12px;"><?= $sub['duration_months'] ?> months</span>
          </td>
          <td class="t-strong">NPR <?= number_format((float)$sub['amount']) ?></td>
          <td class="t-muted" style="font-size:13px;"><?= e($sub['transaction_id'] ?? '—') ?></td>
          <td>
            <?php if ($sub['receipt_file']): ?>
              <?php $receiptUrl = upload_url('payment-receipts/' . $sub['receipt_file']); ?>
              <a href="<?= e($receiptUrl) ?>" target="_blank" class="btn btn-sm btn-outline">
                <i class="fas fa-eye"></i> View
              </a>
            <?php else: ?>
              <span class="t-muted">—</span>
            <?php endif; ?>
          </td>
          <td class="t-muted" style="font-size:13px;"><?= date('M j, Y', strtotime($sub['created_at'])) ?></td>
          <td><span class="dash-pill <?= $statusClass ?>"><?= $statusLabel ?></span></td>
          <td class="ta-right">
            <?php if ($sub['status'] === 'pending'): ?>
              <form method="post" style="display:inline;">
                <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
                <input type="hidden" name="sub_id" value="<?= $sub['id'] ?>">
                <input type="hidden" name="action" value="approve">
                <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Approve this payment and activate premium for <?= e($sub['name']) ?>?')">
                  <i class="fas fa-check"></i> Verify
                </button>
              </form>
              <form method="post" style="display:inline;">
                <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
                <input type="hidden" name="sub_id" value="<?= $sub['id'] ?>">
                <input type="hidden" name="action" value="reject">
                <button type="submit" class="btn btn-sm btn-outline" onclick="return confirm('Reject payment for <?= e($sub['name']) ?>?')">
                  <i class="fas fa-times"></i> Reject
                </button>
              </form>
            <?php elseif ($sub['status'] === 'active'): ?>
              <span class="t-muted" style="font-size:12px;">
                <i class="fas fa-check-circle" style="color:var(--dash-success);"></i>
                Verified by admin
              </span>
            <?php elseif ($sub['status'] === 'rejected'): ?>
              <span class="t-muted" style="font-size:12px;"><i class="fas fa-times-circle" style="color:var(--color-error);"></i> Rejected</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($subscriptions)): ?>
        <tr><td colspan="8"><div class="dash-empty"><?php ui_empty_state(['icon' => 'inbox', 'title' => 'No subscriptions', 'text' => 'No premium subscription records found.']); ?></div></td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if ($lastPage > 1): ?>
<div style="margin-top:16px;">
<?= render_pagination($page, $lastPage, '/admin/premium-verify?' . http_build_query(array_filter(['tab' => $tab, 'search' => $search]))) ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
