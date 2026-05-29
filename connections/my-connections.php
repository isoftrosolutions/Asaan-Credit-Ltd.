<?php
require __DIR__ . '/../config/bootstrap.php';
require_login();

$user = current_user();
$userId = (int)$user['id'];

$db = db();

$matches = $db->prepare('
    SELECT m.*,
           CASE WHEN m.user_a_id = ? THEN mu.name ELSE mu2.name END AS connected_name,
           CASE WHEN m.user_a_id = ? THEN mu.role ELSE mu2.role END AS connected_role,
           CASE WHEN m.user_a_id = ? THEN mu.email ELSE mu2.email END AS connected_email,
           CASE WHEN m.user_a_id = ? THEN mu.phone ELSE mu2.phone END AS connected_phone,
           CASE
               WHEN m.context_type = \'pitch\' THEN (SELECT tagline FROM pitches WHERE id = m.context_id)
               WHEN m.context_type = \'business\' THEN (SELECT business_name FROM businesses WHERE id = m.context_id)
               WHEN m.context_type = \'franchise\' THEN (SELECT brand_name FROM franchises WHERE id = m.context_id)
               WHEN m.context_type = \'advisor\' THEN (SELECT firm_name FROM advisors WHERE id = m.context_id)
               ELSE NULL
           END AS context_name,
           ir.message AS interest_message
    FROM matches m
    LEFT JOIN interest_requests ir ON ir.id = m.interest_request_id
    JOIN users mu ON mu.id = m.user_a_id
    JOIN users mu2 ON mu2.id = m.user_b_id
    WHERE m.user_a_id = ? OR m.user_b_id = ?
    ORDER BY m.matched_at DESC
');

$matches->execute([$userId, $userId, $userId, $userId, $userId, $userId]);
$allMatches = $matches->fetchAll();

$recentActivity = $db->prepare('
    SELECT \'match\' AS event_type, m.matched_at AS event_at,
           CASE WHEN m.user_a_id = ? THEN mu.name ELSE mu2.name END AS other_name
    FROM matches m
    JOIN users mu ON mu.id = m.user_a_id
    JOIN users mu2 ON mu2.id = m.user_b_id
    WHERE m.user_a_id = ? OR m.user_b_id = ?
    UNION ALL
    SELECT \'interest\' AS event_type, ir.responded_at AS event_at, u.name AS other_name
    FROM interest_requests ir
    JOIN users u ON u.id = ir.sender_id
    WHERE ir.receiver_id = ? AND ir.responded_at IS NOT NULL
    ORDER BY event_at DESC
    LIMIT 10
');
$recentActivity->execute([$userId, $userId, $userId, $userId]);
$activities = $recentActivity->fetchAll();

$pageTitle = 'My Connections';
require __DIR__ . '/../includes/layout-dashboard.php';
?>

<div class="connections-page">
    <h2>My Connections (<?= count($allMatches) ?>)</h2>
    <p style="color:var(--color-text-muted); margin-bottom:1.5rem;">These are mutual matches where contact details have been revealed.</p>

    <?php if (empty($allMatches)): ?>
        <div class="card">
            <p style="text-align:center; padding:2rem; color:var(--color-text-muted);">No connections yet. Browse listings and send interest requests to get started.</p>
        </div>
    <?php else: ?>
        <?php foreach ($allMatches as $m): ?>
            <div class="card" style="margin-bottom:1rem;">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.75rem;">
                    <div style="display:flex; align-items:center; gap:1rem;">
                        <div class="avatar avatar-sm" style="background:var(--color-accent-blue);"><?= e(strtoupper(substr($m['connected_name'], 0, 2))) ?></div>
                        <div>
                            <strong><?= e($m['connected_name']) ?></strong><br>
                            <span style="font-size:0.8rem; color:var(--color-text-muted);"><?= e(ucfirst(str_replace('_', ' ', $m['connected_role'] ?? ''))) ?>
                                <?php if ($m['context_name']): ?> &middot; <?= e($m['context_name']) ?><?php endif; ?>
                            </span>
                        </div>
                    </div>
                    <div style="text-align:right; display:flex; gap:0.5rem; align-items:center;">
                        <a href="#" class="btn btn-sm btn-accent" onclick="alert('Email: <?= e($m['connected_email']) ?>\nPhone: <?= e($m['connected_phone'] ?? '—') ?>')">View Contact</a>
                    </div>
                </div>
                <?php if ($m['interest_message']): ?>
                    <div style="margin-top:0.75rem; font-size:0.85rem; color:var(--color-text-muted); border-top:1px solid var(--color-border); padding-top:0.75rem;">
                        <em>"<?= e($m['interest_message']) ?>"</em>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!empty($activities)): ?>
        <div class="card" style="margin-top:2rem;">
            <h4>Recent Activity</h4>
            <ul style="list-style:none; padding:0; margin-top:0.5rem;">
                <?php foreach ($activities as $act): ?>
                    <li style="padding:0.5rem 0; border-bottom:1px solid var(--color-border); font-size:0.9rem;">
                        <?php if ($act['event_type'] === 'match'): ?>
                            &#x1F91D; Connected with <strong><?= e($act['other_name']) ?></strong>
                        <?php else: ?>
                            &#x1F4E8; Interest request from <strong><?= e($act['other_name']) ?></strong>
                        <?php endif; ?>
                        <span style="color:var(--color-text-muted); font-size:0.8rem; margin-left:0.5rem;"><?= date_human($act['event_at']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div style="margin-top:2rem; font-size:0.85rem; color:var(--color-text-muted);">
        Connections are permanent. You can use the revealed contact details to communicate directly.
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
