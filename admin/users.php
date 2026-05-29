<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

$pageTitle = 'User Management';
require __DIR__ . '/../includes/layout-admin.php';

$action = $_GET['action'] ?? '';
$userId = (int)($_GET['id'] ?? 0);

if ($action === 'suspend' && $userId) {
    db()->prepare('UPDATE users SET is_suspended = 1 WHERE id = ?')->execute([$userId]);
    admin_log('suspend_user', 'user', $userId);
    flash_set('success', 'User suspended.');
    redirect('/admin/users');
}
if ($action === 'unsuspend' && $userId) {
    db()->prepare('UPDATE users SET is_suspended = 0 WHERE id = ?')->execute([$userId]);
    admin_log('unsuspend_user', 'user', $userId);
    flash_set('success', 'User unsuspended.');
    redirect('/admin/users');
}
if ($action === 'edit_role' && $userId && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $role = $_POST['role'] ?? '';
    $allowedRoles = ['investor', 'business_owner', 'entrepreneur', 'franchisor', 'advisor'];
    if (in_array($role, $allowedRoles, true)) {
        db()->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$role, $userId]);
        admin_log('edit_user_role', 'user', $userId, ['role' => $role]);
        flash_set('success', 'User role updated.');
    }
    redirect('/admin/users');
}

$roleFilter = $_GET['role'] ?? '';
$vStatusFilter = $_GET['verification_status'] ?? '';
$search = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

$where = ['1=1'];
$params = [];
if ($roleFilter) { $where[] = 'u.role = ?'; $params[] = $roleFilter; }
if ($vStatusFilter) { $where[] = 'u.verification_status = ?'; $params[] = $vStatusFilter; }
if ($search) { $where[] = '(u.name LIKE ? OR u.email LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }

$whereClause = implode(' AND ', $where);
$countStmt = db()->prepare("SELECT COUNT(*) FROM users u WHERE $whereClause");
$countStmt->execute($params);
$pagi = paginate($countStmt, $page, $perPage);

$stmt = db()->prepare("SELECT u.* FROM users u WHERE $whereClause ORDER BY u.created_at DESC LIMIT {$pagi['perPage']} OFFSET {$pagi['offset']}");
$stmt->execute($params);
$users = $stmt->fetchAll();

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="users-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Name', 'Email', 'Role', 'Verification Status', 'Suspended', 'Created']);
    foreach ($users as $u) {
        fputcsv($out, [$u['id'], $u['name'], $u['email'], $u['role'], $u['verification_status'], $u['is_suspended'] ? 'Yes' : 'No', $u['created_at']]);
    }
    fclose($out);
    exit;
}
?>
<h2>User Management</h2>
<div class="card" style="margin-bottom:1rem;">
  <form method="get" style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:flex-end;">
    <div class="input-group" style="flex:1;min-width:180px;">
      <label>Search</label>
      <input type="text" name="search" class="input" value="<?= e($search) ?>" placeholder="Name or email...">
    </div>
    <div class="input-group">
      <label>Role</label>
      <select name="role" class="select">
        <option value="">All</option>
        <option value="investor" <?= $roleFilter === 'investor' ? 'selected' : '' ?>>Investor</option>
        <option value="business_owner" <?= $roleFilter === 'business_owner' ? 'selected' : '' ?>>Business Owner</option>
        <option value="entrepreneur" <?= $roleFilter === 'entrepreneur' ? 'selected' : '' ?>>Entrepreneur</option>
        <option value="franchisor" <?= $roleFilter === 'franchisor' ? 'selected' : '' ?>>Franchisor</option>
        <option value="advisor" <?= $roleFilter === 'advisor' ? 'selected' : '' ?>>Advisor</option>
      </select>
    </div>
    <div class="input-group">
      <label>Verification</label>
      <select name="verification_status" class="select">
        <option value="">All</option>
        <option value="unverified" <?= $vStatusFilter === 'unverified' ? 'selected' : '' ?>>Unverified</option>
        <option value="pending" <?= $vStatusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
        <option value="verified" <?= $vStatusFilter === 'verified' ? 'selected' : '' ?>>Verified</option>
        <option value="rejected" <?= $vStatusFilter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
      </select>
    </div>
    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
    <a href="<?= APP_URL ?>/admin/users" class="btn btn-sm btn-secondary">Clear</a>
    <a href="<?= APP_URL ?>/admin/users?export=csv&<?= http_build_query($_GET) ?>" class="btn btn-sm btn-outline">CSV Export</a>
  </form>
</div>
<div class="card">
  <table style="width:100%;">
    <tr style="border-bottom:1px solid #eae8e6;">
      <th style="text-align:left;padding:8px;">ID</th>
      <th style="text-align:left;padding:8px;">Name</th>
      <th style="text-align:left;padding:8px;">Email</th>
      <th style="padding:8px;">Role</th>
      <th style="padding:8px;">Verification</th>
      <th style="padding:8px;">Status</th>
      <th style="padding:8px;">Created</th>
      <th style="padding:8px;">Actions</th>
    </tr>
    <?php foreach ($users as $u): ?>
    <tr style="border-bottom:1px solid #eee;">
      <td style="padding:10px 8px;"><?= $u['id'] ?></td>
      <td style="padding:10px 8px;font-weight:600;"><?= e($u['name']) ?></td>
      <td style="padding:10px 8px;"><?= e($u['email']) ?></td>
      <td style="padding:10px 8px;"><span class="badge"><?= e($u['role']) ?></span></td>
      <td style="padding:10px 8px;"><?= e($u['verification_status']) ?></td>
      <td style="padding:10px 8px;"><?= $u['is_suspended'] ? '<span style="color:#b91c1c;">Suspended</span>' : '<span style="color:#166534;">Active</span>' ?></td>
      <td style="padding:10px 8px;font-size:0.85rem;"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
      <td style="padding:10px 8px;">
        <?php if ($u['is_suspended']): ?>
          <a href="?action=unsuspend&id=<?= $u['id'] ?>" class="btn btn-sm btn-outline" style="color:#166534;">Unsuspend</a>
        <?php else: ?>
          <a href="?action=suspend&id=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Suspend this user?')">Suspend</a>
        <?php endif; ?>
        <form method="post" action="?action=edit_role&id=<?= $u['id'] ?>" style="display:inline;margin-left:0.25rem;">
          <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
          <select name="role" class="select" style="font-size:0.8rem;padding:4px 8px;" onchange="this.form.submit()">
            <option value="investor" <?= $u['role'] === 'investor' ? 'selected' : '' ?>>Investor</option>
            <option value="business_owner" <?= $u['role'] === 'business_owner' ? 'selected' : '' ?>>Biz Owner</option>
            <option value="entrepreneur" <?= $u['role'] === 'entrepreneur' ? 'selected' : '' ?>>Entrepreneur</option>
            <option value="franchisor" <?= $u['role'] === 'franchisor' ? 'selected' : '' ?>>Franchisor</option>
            <option value="advisor" <?= $u['role'] === 'advisor' ? 'selected' : '' ?>>Advisor</option>
          </select>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($users)): ?>
    <tr><td colspan="8" style="padding:20px;text-align:center;color:#888;">No users found.</td></tr>
    <?php endif; ?>
  </table>
</div>
<?= render_pagination($pagi['page'], $pagi['lastPage'], '/admin/users?' . http_build_query(array_filter(['role' => $roleFilter, 'verification_status' => $vStatusFilter, 'search' => $search]))) ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
