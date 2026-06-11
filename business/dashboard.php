<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();
require_role([ROLE_BUSINESS_OWNER, 'owner', 'ceo', 'cfo']);

$user = current_user();
$userId = (int)$user['id'];

$stmt = db()->prepare('SELECT * FROM businesses WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$userId]);
$businesses = $stmt->fetchAll();

$totalListings = count($businesses);
$published = 0;
$totalViews = 0;
$ratings = [];
$askingPrices = [];
foreach ($businesses as $b) {
    if ($b['is_published']) $published++;
    $totalViews += (int)$b['views'];
    if ($b['rating'] > 0) $ratings[] = (float)$b['rating'];
    if ($b['asking_price'] > 0) $askingPrices[] = (float)$b['asking_price'];
}
$avgRating = count($ratings) ? round(array_sum($ratings) / count($ratings), 1) : '—';

$interestRequests = [];
$interestCount = 0;
$matchCount = 0;
if (!empty($businesses)) {
    $ids = array_column($businesses, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $irStmt = db()->prepare("SELECT COUNT(*) FROM interest_requests WHERE business_id IN ($placeholders)");
    $irStmt->execute($ids);
    $interestCount = (int)$irStmt->fetchColumn();

    $mStmt = db()->prepare("SELECT COUNT(*) FROM matches WHERE context_type = 'business' AND context_id IN ($placeholders) AND closed_status = 'open'");
    $mStmt->execute($ids);
    $matchCount = (int)$mStmt->fetchColumn();

    $irDetailStmt = db()->prepare("
        SELECT ir.*, u.name AS sender_name, u.role AS sender_role, b.business_name,
               b.rating AS business_rating
        FROM interest_requests ir
        JOIN users u ON u.id = ir.sender_id
        JOIN businesses b ON b.id = ir.business_id
        WHERE ir.business_id IN ($placeholders) AND ir.status = 'pending'
        ORDER BY ir.created_at DESC
    ");
    $irDetailStmt->execute($ids);
    $interestRequests = $irDetailStmt->fetchAll();
}

$topAskingPrice = !empty($askingPrices) ? max($askingPrices) : 0;

$pageTitle = 'Business Owner Dashboard';
require __DIR__ . '/../includes/layout-dashboard.php';

ui_page_header(
    'Business Dashboard',
    'Manage your business listings and track performance.',
    empty($businesses) ? '' : '<a href="' . APP_URL . '/business/create" class="btn btn-primary btn-sm">' . ui_icon_str('plus') . ' New business</a>'
);
?>

<?php if (empty($businesses)): ?>
  <div class="dash-panel">
    <?php ui_empty_state([
        'imageSrc' => APP_URL . '/assets/business-founder-owner-empty.png',
        'imageAlt' => 'Business founder preparing a listing dashboard',
        'title' => 'No business listings yet',
        'text' => 'Create your first business listing to start connecting with investors and buyers.',
        'ctaHref' => APP_URL . '/business/create',
        'ctaLabel' => 'Create business listing',
    ]); ?>
  </div>
<?php else: ?>

<div class="dash-stats">
  <?php
    ui_stat_card(['label' => 'Profile views', 'value' => number_format($totalViews), 'icon' => 'eye', 'tone' => 'info']);
    ui_stat_card(['label' => 'Interest requests', 'value' => $interestCount, 'icon' => 'share', 'tone' => 'success']);
    ui_stat_card(['label' => 'Accepted matches', 'value' => $matchCount, 'icon' => 'matches', 'tone' => 'warning']);
    ui_stat_card(['label' => 'Top asking price', 'value' => $topAskingPrice ? money($topAskingPrice) : '—', 'icon' => 'tag', 'tone' => 'primary']);
  ?>
</div>

<?php ui_section_header('Your listings'); ?>
<div class="dash-panel">
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead><tr>
        <th>Business</th><th>Type</th><th>Asking price</th>
        <th class="ta-center">Status</th><th class="ta-center">Views</th><th class="ta-right">Actions</th>
      </tr></thead>
      <tbody>
      <?php foreach ($businesses as $b): ?>
        <tr>
          <td>
            <span class="t-strong"><?= e($b['business_name']) ?></span>
            <?php if ($b['is_featured']): ?> <span class="dash-pill featured">Featured</span><?php endif; ?>
          </td>
          <td><?= e(ucfirst(str_replace('_', ' ', $b['listing_type']))) ?></td>
          <td><?= $b['asking_price'] ? money($b['asking_price']) : '—' ?></td>
          <td class="ta-center"><span class="dash-pill <?= $b['is_published'] ? 'published' : 'draft' ?>"><?= $b['is_published'] ? 'Published' : 'Draft' ?></span></td>
          <td class="ta-center"><?= (int)$b['views'] ?></td>
          <td class="ta-right">
            <span class="dash-table-actions">
              <a href="<?= APP_URL ?>/business/<?= $b['id'] ?>" class="btn btn-sm btn-outline">View</a>
              <a href="<?= APP_URL ?>/business/edit?id=<?= $b['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
            </span>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if (!empty($interestRequests)): ?>
  <?php ui_section_header('Pending interest requests'); ?>
  <div class="dash-panel">
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead><tr>
          <th>Investor / Buyer</th><th>Business</th><th>Message</th><th>Date</th><th class="ta-right">Respond</th>
        </tr></thead>
        <tbody>
        <?php foreach ($interestRequests as $ir): ?>
          <tr>
            <td>
              <span class="t-strong"><?= e($ir['sender_name']) ?></span><br>
              <span class="t-muted"><?= e(ucfirst(str_replace('_', ' ', $ir['sender_role'] ?? ''))) ?></span>
            </td>
            <td><?= e($ir['business_name']) ?></td>
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
