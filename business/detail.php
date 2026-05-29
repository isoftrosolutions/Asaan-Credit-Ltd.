<?php
require __DIR__ . '/../config/bootstrap.php';

$businessId = (int)($_GET['id'] ?? 0);
if ($businessId < 1) {
    http_response_code(404);
    require __DIR__ . '/../pages/404.php';
    exit;
}

$db = db();

$stmt = $db->prepare('SELECT b.*, s.name AS sector_name, u.name AS owner_name, u.company_name, u.verification_status, u.id AS owner_id FROM businesses b LEFT JOIN sectors s ON s.id = b.sector_id JOIN users u ON u.id = b.user_id WHERE b.id = ? AND b.is_published = 1');
$stmt->execute([$businessId]);
$business = $stmt->fetch();

if (!$business) {
    http_response_code(404);
    require __DIR__ . '/../pages/404.php';
    exit;
}

$db->prepare('UPDATE businesses SET views = views + 1 WHERE id = ?')->execute([$businessId]);

$photoStmt = $db->prepare('SELECT * FROM business_photos WHERE business_id = ? ORDER BY sort_order');
$photoStmt->execute([$businessId]);
$photos = $photoStmt->fetchAll();

$user = current_user();
$hasMatch = false;
$ownerUserId = (int)$business['owner_id'];

if ($user) {
    $userId = (int)$user['id'];
    $matchStmt = $db->prepare("SELECT id FROM matches WHERE context_type = 'business' AND context_id = ? AND ((user_a_id = ? AND user_b_id = ?) OR (user_a_id = ? AND user_b_id = ?)) AND closed_status = 'open' LIMIT 1");
    $matchStmt->execute([$businessId, $userId, $ownerUserId, $ownerUserId, $userId]);
    $hasMatch = (bool)$matchStmt->fetch();
}

$listingTypeLabels = [
    'sale' => 'Business for Sale',
    'partial_stake' => 'Partial Stake Sale',
    'investment' => 'Investment Opportunity',
    'loan' => 'Business Loan',
    'asset_sale' => 'Asset Sale',
];
$listingTypeLabel = $listingTypeLabels[$business['listing_type']] ?? ucfirst($business['listing_type']);

$pageTitle = e($business['business_name']) . ' — ' . APP_NAME;
$pageDescription = 'View details about this business listing on Asaan Capital Ltd. Includes financials, sector, location, and contact information.';

$breadcrumbSchema = '<script type="application/ld+json">{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem","position":1,"name":"Home","item":"'.APP_URL.'/"},
    {"@type": "ListItem","position":2,"name":"Businesses","item":"'.APP_URL.'/discover/businesses.php"},
    {"@type": "ListItem","position":3,"name":"'.e($business['business_name']).'","item":"'.APP_URL.'/business/detail.php?id='.$businessId.'"}
  ]
}</script>';
require __DIR__ . '/../includes/layout-public.php';
?>
<?= $breadcrumbSchema ?>
<div class="breadcrumbs container">
    <a href="<?= APP_URL ?>/">Home</a> <span>/</span>
    <a href="<?= APP_URL ?>/browse/businesses">Businesses</a> <span>/</span>
    <span><?= e($business['business_name']) ?></span>
</div>

<div class="container" style="padding-bottom:3rem;">
    <div class="detail-grid">
        <div>
            <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1rem;flex-wrap:wrap;">
                <span class="tx-badge tx-badge-sale" style="font-size:0.85rem;padding:4px 14px;"><?= e($listingTypeLabel) ?></span>
                <?php if ($business['is_featured']): ?><span class="premium-ribbon" style="padding:3px 12px;">PREMIUM</span><?php endif; ?>
            </div>

            <h1 style="margin-bottom:0.25rem;"><?= e($business['business_name']) ?></h1>
            <?php if ($business['sector_name']): ?>
            <p style="font-size:1rem;color:var(--on-surface-variant);margin-bottom:1.5rem;"><?= e($business['sector_name']) ?> &middot; <?= e($business['province']) ?><?= $business['district'] ? ', ' . e($business['district']) : '' ?></p>
            <?php endif; ?>

            <?php if ($hasMatch): ?>
            <div style="display:flex;gap:1rem;flex-wrap:wrap;padding:1rem 0;border-top:1px solid var(--surface-container-high);border-bottom:1px solid var(--surface-container-high);margin-bottom:1.5rem;">
                <div><span class="meta-label">Owner</span><div class="meta-value"><?= e($business['owner_name']) ?></div></div>
                <div><span class="meta-label">Company</span><div class="meta-value"><?= e($business['company_name'] ?? '—') ?></div></div>
            </div>
            <?php endif; ?>

            <div style="display:flex;gap:2rem;flex-wrap:wrap;padding:1rem 0;border-top:1px solid var(--surface-container-high);border-bottom:1px solid var(--surface-container-high);margin-bottom:1.5rem;">
                <?php if ($business['established_year']): ?>
                <div><span class="meta-label">Established</span><div class="meta-value"><?= e($business['established_year']) ?></div></div>
                <?php endif; ?>
                <?php if ($business['employee_count']): ?>
                <div><span class="meta-label">Employees</span><div class="meta-value"><?= e($business['employee_count']) ?></div></div>
                <?php endif; ?>
                <?php if ($business['annual_revenue']): ?>
                <div><span class="meta-label">Annual Revenue</span><div class="meta-value"><?= money($business['annual_revenue']) ?></div></div>
                <?php endif; ?>
                <?php if ($business['ebitda_pct']): ?>
                <div><span class="meta-label">EBITDA Margin</span><div class="meta-value"><?= e($business['ebitda_pct']) ?>%</div></div>
                <?php endif; ?>
                <div><span class="meta-label">Listing ID</span><div class="meta-value">#<?= $business['id'] ?></div></div>
            </div>

            <?php if (!empty($photos)): ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:0.75rem;margin-bottom:1.5rem;">
                <?php foreach ($photos as $photo): ?>
                <img src="<?= APP_URL ?>/public/uploads/business-photos/<?= e($photo['file_path']) ?>" alt="<?= e($business['business_name']) ?>" style="width:100%;height:150px;object-fit:cover;border-radius:0.75rem;">
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ($business['description']): ?>
            <h3>Business Overview</h3>
            <p><?= nl2br(e($business['description'])) ?></p>
            <?php endif; ?>

            <?php if ($business['reason_for_sale']): ?>
            <h3>Reason for Sale</h3>
            <p><?= nl2br(e($business['reason_for_sale'])) ?></p>
            <?php endif; ?>

            <?php if ($business['assets_included']): ?>
            <h3>Assets Included</h3>
            <p><?= nl2br(e($business['assets_included'])) ?></p>
            <?php endif; ?>
        </div>

        <div class="detail-sidebar-card">
            <div class="card">
                <div style="margin-bottom:1rem;">
                    <div class="meta-label" style="margin-bottom:0.25rem;">TRANSACTION</div>
                    <div style="font-size:1.5rem;font-weight:700;"><?= $business['asking_price'] ? money($business['asking_price']) : 'Negotiable' ?></div>
                    <div style="font-size:0.85rem;color:var(--on-surface-variant);"><?= e($listingTypeLabel) ?></div>
                </div>

                <div class="info-row"><span class="label">Location</span><span class="value"><?= e($business['province']) ?><?= $business['district'] ? ', ' . e($business['district']) : '' ?></span></div>
                <?php if ($business['sector_name']): ?>
                <div class="info-row"><span class="label">Industry</span><span class="value"><?= e($business['sector_name']) ?></span></div>
                <?php endif; ?>
                <div class="info-row"><span class="label">Listed by</span><span class="value">Business Owner</span></div>
                <div class="info-row"><span class="label">Last Updated</span><span class="value"><?= date_human($business['updated_at']) ?></span></div>
                <?php if ($business['stake_offered_pct']): ?>
                <div class="info-row"><span class="label">Stake Offered</span><span class="value"><?= e($business['stake_offered_pct']) ?>%</span></div>
                <?php endif; ?>

                <?php if ($hasMatch): ?>
                <button class="btn btn-secondary" style="width:100%;margin-top:1rem;" onclick="alert('Contact: <?= e($business['owner_name']) ?>')">View Contact Details</button>
                <?php else: ?>
                <button class="btn btn-accent" style="width:100%;margin-top:1rem;" onclick="document.getElementById('interest-modal').classList.add('open')">Express Interest</button>
                <button class="btn btn-ghost btn-sm" style="width:100%;margin-top:0.5rem;" onclick="alert('Saved to bookmarks (demo)')">&#9734; Save for later</button>
                <?php endif; ?>

                    <div style="margin-top:1rem;font-size:0.75rem;color:var(--color-text-muted);border-top:1px solid var(--surface-container-high);padding-top:0.75rem;">
                    <div style="display:flex;align-items:center;gap:4px;margin-bottom:0.25rem;">
                        <span class="social-proof" style="background:transparent;padding:0;font-size:0.75rem;">&#128065; <?= (int)$business['views'] ?> views</span>
                    </div>
                </div>

                <div style="margin-top:0.75rem;padding:0.75rem;background:rgba(199,122,18,0.1);border-radius:0.75rem;font-size:0.75rem;color:var(--color-warning);">
                    <strong>&#8505;&#65039; Disclaimer:</strong> Asaan Capital Ltd is a discovery platform. We do not guarantee accuracy of financial data. Conduct your own due diligence.
                </div>
            </div>
        </div>
    </div>
</div>

<div id="interest-modal" class="modal" onclick="if (event.target === this) this.classList.remove('open')">
    <div class="modal-content" onclick="event.stopImmediatePropagation()">
        <div class="modal-header">
            <h3>Express Interest</h3>
            <button class="close-btn" onclick="document.getElementById('interest-modal').classList.remove('open')">&times;</button>
        </div>
        <form method="POST" action="<?= APP_URL ?>/connections/send-interest">
            <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="listing_type" value="business">
            <input type="hidden" name="listing_id" value="<?= $businessId ?>">
            <div class="input-group">
                <textarea name="message" class="input" style="width:100%;height:110px;margin-bottom:1rem;" placeholder="Short note to the business owner (optional)"></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Send Request</button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
