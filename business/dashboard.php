<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();

$user = current_user();
$userId = (int)$user['id'];

// Handle owner delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_check();
    $deleteId = (int)($_POST['id'] ?? 0);
    if ($deleteId) {
        $stmt = db()->prepare('SELECT user_id FROM businesses WHERE id = ?');
        $stmt->execute([$deleteId]);
        $biz = $stmt->fetch();
        if ($biz && (int)$biz['user_id'] === $userId) {
            db()->prepare('DELETE FROM businesses WHERE id = ?')->execute([$deleteId]);
            flash_set('success', 'Business listing deleted.');
        }
    }
    redirect('/dashboard');
}

$stmt = db()->prepare('SELECT * FROM businesses WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$userId]);
$businesses = $stmt->fetchAll();

$totalListings = count($businesses);
$published = 0;
$totalViews = 0;
$ratings = [];
$askingPrices = [];
$statusLabels = ['draft' => 'Draft', 'pending' => 'Pending', 'approved' => 'Published', 'rejected' => 'Rejected', 'sold' => 'Sold'];
$statusClasses = ['draft' => 'draft', 'pending' => 'pending', 'approved' => 'published', 'rejected' => 'rejected', 'sold' => 'rejected'];
foreach ($businesses as $b) {
    if ($b['status'] === 'approved') $published++;
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
$drafts = $totalListings - $published;
$publishedLabel = $published . ' published';
if ($drafts > 0) {
    $publishedLabel .= ' / ' . $drafts . ' draft' . ($drafts === 1 ? '' : 's');
}
$pendingCount = count(array_filter($businesses, fn($b) => $b['status'] === 'pending'));
if ($pendingCount > 0) {
    $publishedLabel .= ' / ' . $pendingCount . ' pending';
}

$pageTitle = 'Business Owner Dashboard';
require __DIR__ . '/../includes/layout-dashboard.php';

ui_page_header(
    'Business Dashboard',
    'Manage your business listings and track performance.',
    ''
);
?>

<?php if (empty($businesses)): ?>
  <div class="dash-panel biz-empty-hero">
    <div class="biz-empty-copy">
      <span class="biz-eyebrow">Founder / Owner Portal</span>
      <h2>Launch your business listing in minutes</h2>
      <p>Create a clear investor-ready profile with your company overview, transaction goal, financial highlights, and supporting documents.</p>
      <div class="biz-empty-actions">
        <a href="<?= APP_URL ?>/business/create" class="btn btn-primary">Create business listing</a>
        <a href="<?= APP_URL ?>/business-valuation" class="btn btn-outline">Estimate valuation</a>
      </div>
      <div class="biz-empty-steps" aria-label="Listing setup steps">
        <span><strong>1</strong> Add business details</span>
        <span><strong>2</strong> Upload proof and media</span>
        <span><strong>3</strong> Connect with investors</span>
      </div>
    </div>
    <div class="biz-empty-visual">
      <img src="<?= APP_URL ?>/assets/business-founder-owner-empty.png" alt="Business founder preparing a listing dashboard" loading="lazy">
    </div>
  </div>
<?php else: ?>

<div class="dash-panel biz-dashboard-hero">
  <div class="biz-dashboard-hero-copy">
    <span class="biz-eyebrow">Business owner workspace</span>
    <h2><?= e($totalListings === 1 ? 'Your listing is ready to manage' : 'Manage your business portfolio') ?></h2>
    <p>Track listing visibility, respond to investor interest, and keep your business details current from one place.</p>
  </div>
  <div class="biz-dashboard-hero-meta">
    <div>
      <span class="biz-meta-label">Listings</span>
      <strong><?= number_format($totalListings) ?></strong>
    </div>
    <div>
      <span class="biz-meta-label">Status</span>
      <strong><?= e($publishedLabel) ?></strong>
    </div>
  </div>
</div>

<div class="dash-stats">
  <?php
    ui_stat_card(['label' => 'Profile views', 'value' => number_format($totalViews), 'icon' => 'eye', 'tone' => 'info']);
    ui_stat_card(['label' => 'Interest requests', 'value' => $interestCount, 'icon' => 'share', 'tone' => 'success']);
    ui_stat_card(['label' => 'Accepted matches', 'value' => $matchCount, 'icon' => 'matches', 'tone' => 'warning']);
    ui_stat_card(['label' => 'Top asking price', 'value' => $topAskingPrice ? money($topAskingPrice) : '—', 'icon' => 'tag', 'tone' => 'primary']);
  ?>
</div>

<?php ui_section_header('Quick actions'); ?>
<div class="dash-qa-grid">
  <?php
    ui_quick_action(['title' => 'Review connections', 'desc' => $interestCount . ' total interest request' . ($interestCount === 1 ? '' : 's'), 'icon' => 'matches', 'href' => APP_URL . '/connections', 'tone' => 'success']);
    ui_quick_action(['title' => 'Update listing details', 'desc' => 'Refresh pricing, media, and profile data', 'icon' => 'settings', 'href' => APP_URL . '/business/edit', 'tone' => 'info']);
  ?>
</div>

<?php ui_section_header('Your listings'); ?>
<div class="dash-panel biz-listings-panel">
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead><tr>
        <th>Image</th><th>Business</th><th>Transaction</th><th>Asking price</th>
        <th class="ta-center">Status</th><th class="ta-center">Views</th><th class="ta-right">Actions</th>
      </tr></thead>
      <tbody>
      <?php foreach ($businesses as $b): ?>
        <tr>
          <td>
            <div style="width:48px;height:36px;background:var(--color-bg-soft);border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:12px;color:var(--color-text-muted);"><i class="fas fa-building"></i></div>
          </td>
          <td>
            <span class="t-strong"><?= e($b['business_name']) ?></span>
            <?php if ($b['is_featured']): ?> <span class="dash-pill featured">Featured</span><?php endif; ?>
            <br><span class="t-muted">Updated <?= e(date_human($b['updated_at'] ?? $b['created_at'])) ?></span>
          </td>
          <td><?= e(ucfirst(str_replace('_', ' ', $b['listing_type']))) ?></td>
          <td><?= $b['asking_price'] ? money($b['asking_price']) : '—' ?></td>
          <td class="ta-center"><span class="dash-pill <?= $statusClasses[$b['status']] ?? 'draft' ?>"><?= $statusLabels[$b['status']] ?? e($b['status']) ?></span></td>
          <td class="ta-center"><?= (int)$b['views'] ?></td>
          <td class="ta-right">
            <span class="dash-table-actions">
              <a href="<?= APP_URL ?>/business/<?= $b['id'] ?>" class="btn btn-sm btn-outline">View</a>
              <a href="<?= APP_URL ?>/business/edit?id=<?= $b['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
              <form method="post" style="display:inline" onsubmit="return confirm('Delete this listing permanently?')">
                <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $b['id'] ?>">
                <button type="submit" class="btn btn-sm" style="background:var(--color-error);border-color:var(--color-error);color:#fff;">Delete</button>
              </form>
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
