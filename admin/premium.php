<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $userId = (int)($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    $stmt = db()->prepare('SELECT id, name, email, is_premium FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $target = $stmt->fetch();

    if (!$target) {
        flash_set('error', 'User not found.');
        redirect('/admin/premium');
    }

    if ($action === 'set_premium') {
        db()->prepare('UPDATE users SET is_premium = 1 WHERE id = ?')->execute([$userId]);
        admin_log('set_premium', 'user', $userId, ['email' => $target['email']]);
        $mailBody = '<div style="font-family:sans-serif;max-width:600px;margin:20px auto;padding:32px;border:1px solid #eef2f6;border-radius:16px;text-align:center;">
            <div style="font-size:48px;margin-bottom:16px;">👑</div>
            <h2 style="color:#6B1D22;margin:0 0 8px;">Welcome to Premium!</h2>
            <p style="color:#555;margin:0 0 20px;font-size:15px;line-height:1.6;">Congratulations <strong>' . e($target['name']) . '</strong>, your account has been upgraded to Premium. You now have access to owner contact details, financial documents, and more.</p>
            <a href="' . APP_URL . '/dashboard" style="display:inline-block;padding:12px 28px;background:#6B1D22;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;">Go to Dashboard</a>
        </div>';
        EmailService::getInstance()->sendCustomEmail($target['email'], 'You\'re Now Premium — ' . APP_NAME, $mailBody);
        flash_set('success', $target['name'] . ' is now premium. Email sent.');
    } elseif ($action === 'remove_premium') {
        db()->prepare('UPDATE users SET is_premium = 0 WHERE id = ?')->execute([$userId]);
        admin_log('remove_premium', 'user', $userId, ['email' => $target['email']]);
        flash_set('success', 'Premium removed from ' . $target['name'] . '.');
    }

    redirect('/admin/premium');
}

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$search = trim($_GET['search'] ?? '');

$where = ['is_premium = 1'];
$params = [];
if ($search) {
    $where[] = '(name LIKE ? OR email LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$whereClause = implode(' AND ', $where);

$countStmt = db()->prepare("SELECT COUNT(*) FROM users WHERE $whereClause");
$countStmt->execute($params);
$pagi = paginate($countStmt, $page, $perPage);

$userStmt = db()->prepare("SELECT id, name, email, role, verification_status, created_at FROM users WHERE $whereClause ORDER BY created_at DESC LIMIT {$pagi['perPage']} OFFSET {$pagi['offset']}");
$userStmt->execute($params);
$premiumUsers = $userStmt->fetchAll();

$requests = db()->query("
    SELECT n.id, n.title, n.body, n.created_at, n.action_url
    FROM notifications n
    WHERE n.type = 'upgrade'
    ORDER BY n.created_at DESC
    LIMIT 50
")->fetchAll();

$highlightId = (int)($_GET['requester_id'] ?? 0);

$pageTitle = 'Premium Management';
require __DIR__ . '/../includes/layout-admin.php';

ui_page_header('Premium Management', count($premiumUsers) . ' premium users · ' . count($requests) . ' upgrade requests');
?>

<?php ui_section_header('Upgrade Requests'); ?>
<div class="dash-panel">
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead><tr>
        <th>Requester</th><th>Details</th><th>Date</th><th class="ta-right">Actions</th>
      </tr></thead>
      <tbody>
      <?php foreach ($requests as $req):
        preg_match('/^([^(]+) \(([^)]+)\)/', $req['body'], $m);
        $reqName = $m[1] ?? $req['body'];
        $reqEmail = $m[2] ?? '';
        parse_str(parse_url($req['action_url'] ?? '', PHP_URL_QUERY) ?: '', $q);
        $reqUserId = (int)($q['requester_id'] ?? 0);
        $isHighlighted = $highlightId && $reqUserId === $highlightId;
      ?>
        <tr<?= $isHighlighted ? ' style="background:rgba(16,185,129,.08);"' : '' ?>>
          <td class="t-strong"><?= e($reqName) ?><br><span class="t-muted"><?= e($reqEmail) ?></span></td>
          <td><?= e($req['body']) ?></td>
          <td class="t-muted"><?= date_human($req['created_at']) ?></td>
          <td class="ta-right">
            <?php if ($reqUserId): ?>
            <form method="post" style="display:inline;">
              <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
              <input type="hidden" name="user_id" value="<?= $reqUserId ?>">
              <input type="hidden" name="action" value="set_premium">
              <button type="submit" class="btn btn-sm btn-primary">Approve</button>
            </form>
            <?php else: ?>
            <span class="t-muted" style="font-size:12px;">Cannot identify user</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($requests)): ?>
        <tr><td colspan="4"><div class="dash-empty"><?php ui_empty_state(['icon' => 'inbox', 'title' => 'No requests', 'text' => 'No premium upgrade requests yet.']); ?></div></td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php ui_section_header('Premium Users'); ?>
<div class="dash-panel">
  <form method="get" class="dash-filterbar" style="margin-bottom:16px;">
    <input type="text" name="search" class="input" placeholder="Search premium users..." value="<?= e($search) ?>" style="flex:1;min-width:200px;">
    <button type="submit" class="btn btn-primary btn-sm">Search</button>
    <?php if ($search): ?>
    <a href="/admin/premium" class="btn btn-outline btn-sm">Clear</a>
    <?php endif; ?>
  </form>
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead><tr>
        <th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Since</th><th class="ta-right">Actions</th>
      </tr></thead>
      <tbody>
      <?php foreach ($premiumUsers as $u): ?>
        <tr>
          <td class="t-strong"><?= e($u['name']) ?></td>
          <td class="t-muted"><?= e($u['email']) ?></td>
          <td><span class="dash-pill open"><?= e(ucfirst(str_replace('_', ' ', $u['role']))) ?></span></td>
          <td><span class="dash-pill published"><?= e(ucfirst($u['verification_status'])) ?></span></td>
          <td class="t-muted"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
          <td class="ta-right">
            <form method="post" style="display:inline;">
              <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
              <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
              <input type="hidden" name="action" value="remove_premium">
              <button type="submit" class="btn btn-sm btn-outline" onclick="return confirm('Remove premium from <?= e($u['name']) ?>?')">Remove</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($premiumUsers)): ?>
        <tr><td colspan="6"><div class="dash-empty"><?php ui_empty_state(['icon' => 'star', 'title' => 'No premium users', 'text' => 'No users have been upgraded yet.']); ?></div></td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?= render_pagination($pagi['page'], $pagi['lastPage'], '/admin/premium?' . http_build_query(array_filter(['search' => $search]))) ?>

<div style="margin-top:24px;">
<?php ui_section_header('Add Premium to User'); ?>
<div class="dash-panel" style="padding:20px;">
  <form method="get" class="dash-filterbar" action="/admin/premium" style="margin-bottom:12px;">
    <input type="text" name="search_non" class="input" placeholder="Search non-premium users..." value="<?= e($_GET['search_non'] ?? '') ?>">
    <button type="submit" class="btn btn-sm btn-primary">Find</button>
  </form>
  <?php
    $nonWhere = ['is_premium = 0'];
    $nonParams = [];
    $searchNon = trim($_GET['search_non'] ?? '');
    if ($searchNon) {
        $nonWhere[] = '(name LIKE ? OR email LIKE ?)';
        $nonParams[] = "%$searchNon%";
        $nonParams[] = "%$searchNon%";
    }
    $nonStmt = db()->prepare("SELECT id, name, email, role FROM users WHERE " . implode(' AND ', $nonWhere) . " ORDER BY name LIMIT 50");
    $nonStmt->execute($nonParams);
    $nonPremium = $nonStmt->fetchAll();
  ?>
  <?php if (!empty($nonPremium)): ?>
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead><tr><th>Name</th><th>Email</th><th>Role</th><th class="ta-right">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($nonPremium as $u): ?>
        <tr>
          <td class="t-strong"><?= e($u['name']) ?></td>
          <td class="t-muted"><?= e($u['email']) ?></td>
          <td><span class="dash-pill open"><?= e(ucfirst(str_replace('_', ' ', $u['role']))) ?></span></td>
          <td class="ta-right">
            <form method="post" style="display:inline;">
              <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
              <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
              <input type="hidden" name="action" value="set_premium">
              <button type="submit" class="btn btn-sm btn-primary">Upgrade</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
  <p class="t-muted" style="font-size:13px;">All users are premium. <a href="/admin/premium">Clear search</a></p>
  <?php endif; ?>
</div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
