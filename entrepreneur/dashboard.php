<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();
require_role(ROLE_ENTREPRENEUR);

$user = current_user();
$userId = (int)$user['id'];
$db = db();

$stmt = $db->prepare('SELECT * FROM pitches WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$userId]);
$pitches = $stmt->fetchAll();

$totalPitches = count($pitches);
$published = 0;
$totalViews = 0;
$totalInterest = 0;
$fundingAmounts = [];
$pitchStatusLabels = [];
foreach ($pitches as $p) {
    if ($p['is_published']) {
        $published++;
        $pitchStatusLabels[$p['id']] = $p['is_hidden'] ? 'Hidden' : 'Published';
    } else {
        $pitchStatusLabels[$p['id']] = 'Draft';
    }
    if ($p['funding_amount'] > 0) $fundingAmounts[] = (float)$p['funding_amount'];
}
$topFunding = !empty($fundingAmounts) ? max($fundingAmounts) : 0;

$pitchIds = array_column($pitches, 'id');
$interestRequests = [];
if (!empty($pitchIds)) {
    $placeholders = implode(',', array_fill(0, count($pitchIds), '?'));

    $viewStmt = $db->prepare("SELECT SUM(views) FROM pitches WHERE id IN ($placeholders)");
    $viewStmt->execute($pitchIds);
    $totalViews = (int)$viewStmt->fetchColumn();

    $irStmt = $db->prepare("SELECT COUNT(*) FROM interest_requests WHERE pitch_id IN ($placeholders)");
    $irStmt->execute($pitchIds);
    $totalInterest = (int)$irStmt->fetchColumn();

    $irDetailStmt = $db->prepare("
        SELECT ir.*, u.name AS sender_name, u.role AS sender_role, p.tagline AS pitch_tagline
        FROM interest_requests ir
        JOIN users u ON u.id = ir.sender_id
        JOIN pitches p ON p.id = ir.pitch_id
        WHERE ir.pitch_id IN ($placeholders) AND ir.status = 'pending'
        ORDER BY ir.created_at DESC
    ");
    $irDetailStmt->execute($pitchIds);
    $interestRequests = $irDetailStmt->fetchAll();
}

$pageTitle = 'Entrepreneur Dashboard';
require __DIR__ . '/../includes/layout-dashboard.php';

ui_page_header(
    'Entrepreneur Dashboard',
    'Manage your pitches and track investor interest.',
    empty($pitches) ? '' : '<a href="' . APP_URL . '/entrepreneur/pitch-create" class="btn btn-primary btn-sm">' . ui_icon_str('plus') . ' New pitch</a>'
);
?>

<?php if (empty($pitches)): ?>
  <div class="dash-panel">
    <?php ui_empty_state(['icon' => 'chart', 'title' => 'No pitches yet', 'text' => 'Create your first pitch to start connecting with investors.', 'ctaHref' => APP_URL . '/entrepreneur/pitch-create', 'ctaLabel' => 'Create pitch']); ?>
  </div>
<?php else: ?>

<div class="dash-stats">
  <?php
    ui_stat_card(['label' => 'Profile views', 'value' => number_format($totalViews), 'icon' => 'eye', 'tone' => 'info']);
    ui_stat_card(['label' => 'Interest requests', 'value' => $totalInterest, 'icon' => 'share', 'tone' => 'success']);
    ui_stat_card(['label' => 'Published', 'value' => $published . '/' . $totalPitches, 'icon' => 'check', 'tone' => 'warning']);
    ui_stat_card(['label' => 'Current ask', 'value' => $topFunding ? money($topFunding) : '—', 'icon' => 'tag', 'tone' => 'primary']);
  ?>
</div>

<?php ui_section_header('Your pitches'); ?>
<div class="dash-panel">
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead><tr>
        <th>Image</th><th>Tagline</th><th>Stage</th><th>Funding ask</th>
        <th class="ta-center">Status</th><th class="ta-right">Actions</th>
      </tr></thead>
      <tbody>
      <?php foreach ($pitches as $p): ?>
        <tr>
          <td>
            <?php if (!empty($p['pitch_image'])): ?>
              <img src="<?= APP_URL . $p['pitch_image'] ?>" alt="" style="width:48px;height:36px;object-fit:cover;border-radius:4px;">
            <?php else: ?>
              <div style="width:48px;height:36px;background:var(--color-bg-soft);border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:12px;color:var(--color-text-muted);"><i class="fas fa-chart"></i></div>
            <?php endif; ?>
          </td>
          <td>
            <span class="t-strong"><?= e($p['tagline']) ?></span>
            <?php if ($p['is_featured']): ?> <span class="dash-pill featured">Featured</span><?php endif; ?>
          </td>
          <td><?= e(ucfirst($p['stage'] ?? '—')) ?></td>
          <td><?= $p['funding_amount'] ? money($p['funding_amount']) : '—' ?></td>
          <td class="ta-center"><span class="dash-pill <?= $p['is_published'] ? ($p['is_hidden'] ? 'draft' : 'published') : 'draft' ?>"><?= $pitchStatusLabels[$p['id']] ?? ($p['is_published'] ? 'Published' : 'Draft') ?></span></td>
          <td class="ta-right">
            <span class="dash-table-actions">
              <a href="<?= APP_URL ?>/pitch/<?= $p['id'] ?>" class="btn btn-sm btn-outline">View</a>
              <a href="<?= APP_URL ?>/entrepreneur/pitch-edit?id=<?= $p['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
            </span>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if (!empty($interestRequests)): ?>
  <?php ui_section_header('Recent interest requests'); ?>
  <div class="dash-panel">
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead><tr>
          <th>Investor</th><th>Pitch</th><th>Message</th><th>Date</th><th class="ta-right">Respond</th>
        </tr></thead>
        <tbody>
        <?php foreach ($interestRequests as $ir): ?>
          <tr>
            <td>
              <span class="t-strong"><?= e($ir['sender_name']) ?></span><br>
              <span class="t-muted"><?= e(ucfirst(str_replace('_', ' ', $ir['sender_role'] ?? ''))) ?></span>
            </td>
            <td><?= e(mb_substr($ir['pitch_tagline'], 0, 40)) ?>&hellip;</td>
            <td class="t-muted"><?= e($ir['message'] ?? '—') ?></td>
            <td class="t-muted"><?= date_human($ir['created_at']) ?></td>
            <td class="ta-right">
              <form method="POST" action="<?= APP_URL ?>/connections/respond" class="dash-table-actions">
                <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
                <input type="hidden" name="request_id" value="<?= $ir['id'] ?>">
                <button type="submit" name="action" value="accept" class="btn btn-primary btn-sm">Accept</button>
                <button type="submit" name="action" value="reject" class="btn btn-outline btn-sm" onclick="return confirm('Decline this interest request?')">Decline</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
