<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

$action = $_GET['action'] ?? '';
$pitchId = (int)($_GET['id'] ?? 0);

if ($action === 'hide' && $pitchId) {
    db()->prepare('UPDATE pitches SET is_hidden = 1 WHERE id = ?')->execute([$pitchId]);
    admin_log('hide_pitch', 'pitch', $pitchId);
    flash_set('success', 'Pitch hidden from public.');
    redirect('/admin/pitches');
}
if ($action === 'unhide' && $pitchId) {
    db()->prepare('UPDATE pitches SET is_hidden = 0 WHERE id = ?')->execute([$pitchId]);
    admin_log('unhide_pitch', 'pitch', $pitchId);
    flash_set('success', 'Pitch is now visible.');
    redirect('/admin/pitches');
}
if ($action === 'flag' && $pitchId) {
    db()->prepare('UPDATE pitches SET is_hidden = 1 WHERE id = ?')->execute([$pitchId]);
    admin_log('flag_pitch', 'pitch', $pitchId, ['reason' => 'Flagged for review']);
    flash_set('warning', 'Pitch flagged and hidden for review.');
    redirect('/admin/pitches');
}

$search = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

$where = ['1=1'];
$params = [];
if ($search) { $where[] = '(p.tagline LIKE ? OR u.name LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }

$whereClause = implode(' AND ', $where);
$countStmt = db()->prepare("SELECT COUNT(*) FROM pitches p JOIN users u ON u.id = p.user_id WHERE $whereClause");
$countStmt->execute($params);
$pagi = paginate($countStmt, $page, $perPage);

$stmt = db()->prepare("SELECT p.*, u.name, u.email, u.is_suspended FROM pitches p JOIN users u ON u.id = p.user_id WHERE $whereClause ORDER BY p.created_at DESC LIMIT {$pagi['perPage']} OFFSET {$pagi['offset']}");
$stmt->execute($params);
$pitches = $stmt->fetchAll();
$pageTitle = 'Pitch Moderation';
require __DIR__ . '/../includes/layout-admin.php';

ui_page_header('Pitch Moderation', 'Review and moderate entrepreneur pitches.');
?>
<form method="get" class="dash-filterbar">
  <div class="input-group grow">
    <label>Search</label>
    <input type="text" name="search" class="input" value="<?= e($search) ?>" placeholder="Search pitch or user...">
  </div>
  <button type="submit" class="btn btn-sm btn-primary">Search</button>
  <a href="<?= APP_URL ?>/admin/pitches" class="btn btn-sm btn-outline">Clear</a>
</form>

<div class="dash-panel">
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead><tr>
        <th>User</th><th>Tagline</th><th>Stage</th><th>Amount</th>
        <th class="ta-center">Status</th><th>Created</th><th class="ta-right">Actions</th>
      </tr></thead>
      <tbody>
      <?php foreach ($pitches as $p): ?>
        <tr>
          <td><span class="t-strong"><?= e($p['name']) ?></span><br><span class="t-muted"><?= e($p['email']) ?></span></td>
          <td style="max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($p['tagline']) ?></td>
          <td><span class="dash-pill open"><?= e(ucfirst(str_replace('_', ' ', $p['stage']))) ?></span></td>
          <td><?= $p['funding_amount'] ? money($p['funding_amount']) : '—' ?></td>
          <td class="ta-center">
            <span class="dash-pill <?= $p['is_hidden'] ? 'draft' : 'published' ?>"><?= $p['is_hidden'] ? 'Hidden' : 'Visible' ?></span>
            <?php if (!$p['is_published']): ?> <span class="dash-pill draft">Draft</span><?php endif; ?>
          </td>
          <td class="t-muted"><?= date_human($p['created_at']) ?></td>
          <td class="ta-right">
            <span class="dash-table-actions">
              <a href="<?= APP_URL ?>/pitch/<?= $p['id'] ?>" target="_blank" class="btn btn-sm btn-outline">View</a>
              <?php if ($p['is_hidden']): ?>
                <a href="?action=unhide&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline">Unhide</a>
              <?php else: ?>
                <a href="?action=hide&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline" onclick="return confirm('Hide this pitch?')">Hide</a>
              <?php endif; ?>
              <a href="?action=flag&id=<?= $p['id'] ?>" class="btn btn-sm btn-primary" onclick="return confirm('Flag for review?')">Flag</a>
            </span>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($pitches)): ?>
        <tr><td colspan="7"><?php ui_empty_state(['icon' => 'chart', 'title' => 'No pitches found', 'text' => 'Try a different search.']); ?></td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?= render_pagination($pagi['page'], $pagi['lastPage'], '/admin/pitches?' . http_build_query(array_filter(['search' => $search]))) ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
