<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send') {
    csrf_check();
    $title = trim($_POST['title'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $audience = $_POST['audience'] ?? '';
    $delivery = $_POST['delivery'] ?? '';

    if (!$title || !$body || !$audience || !$delivery) {
        flash_set('error', 'All fields are required.');
        redirect('/admin/broadcast');
    }

    $allowedAudiences = ['all', 'investors', 'business_owners', 'entrepreneurs', 'franchisors', 'advisors'];
    $allowedDelivery = ['in_app', 'email', 'both'];
    if (!in_array($audience, $allowedAudiences, true) || !in_array($delivery, $allowedDelivery, true)) {
        flash_set('error', 'Invalid selection.');
        redirect('/admin/broadcast');
    }

    $audienceWhere = match ($audience) {
        'all' => '1=1',
        'investors' => "role = 'investor'",
        'business_owners' => "role = 'business_owner'",
        'entrepreneurs' => "role = 'entrepreneur'",
        'franchisors' => "role = 'franchisor'",
        'advisors' => "role = 'advisor'",
        default => '1=1',
    };

    $targetStmt = db()->query("SELECT id, name, email FROM users WHERE $audienceWhere AND is_suspended = 0");
    $targetUsers = $targetStmt->fetchAll();
    $recipientsCount = count($targetUsers);

    $deliverInApp = ($delivery === 'in_app' || $delivery === 'both');
    $deliverEmail = ($delivery === 'email' || $delivery === 'both');

    if ($deliverInApp) {
        $notifStmt = db()->prepare('INSERT INTO notifications (user_id, type, title, body, created_at) VALUES (?, ?, ?, ?, NOW())');
        foreach ($targetUsers as $t) {
            $notifStmt->execute([$t['id'], 'broadcast', $title, $body]);
        }
    }

    if ($deliverEmail) {
        foreach ($targetUsers as $t) {
            send_mail($t['email'], $title, '<p>' . e($body) . '</p>');
        }
    }

    db()->prepare('INSERT INTO broadcasts (sent_by, title, body, audience, delivery, recipients_count, sent_at, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())')->execute([$user['id'], $title, $body, $audience, $delivery, $recipientsCount]);

    admin_log('send_broadcast', 'broadcast', null, ['audience' => $audience, 'delivery' => $delivery, 'recipients' => $recipientsCount]);
    flash_set('success', "Broadcast sent to $recipientsCount users via " . str_replace('_', ' ', $delivery) . '.');
    redirect('/admin/broadcast');
}

$historyStmt = db()->query('SELECT b.*, u.name AS admin_name FROM broadcasts b JOIN users u ON u.id = b.sent_by ORDER BY b.created_at DESC LIMIT 20');
$history = $historyStmt->fetchAll();
$pageTitle = 'Broadcast';
require __DIR__ . '/../includes/layout-admin.php';

ui_page_header('Send Platform Broadcast', 'Message members in-app, by email, or both.');
?>
<div class="dash-panel dash-panel-pad dash-form" style="max-width:620px;">
  <form method="post">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= csrf_token() ?>">
    <input type="hidden" name="action" value="send">
    <div class="input-group">
      <label>Audience</label>
      <select name="audience" class="select" required>
        <option value="all">All Users</option>
        <option value="investors">Investors</option>
        <option value="business_owners">Business Owners</option>
        <option value="entrepreneurs">Entrepreneurs</option>
        <option value="franchisors">Franchisors</option>
        <option value="advisors">Advisors</option>
      </select>
    </div>
    <div class="input-group">
      <label>Delivery</label>
      <select name="delivery" class="select" required>
        <option value="in_app">In-App Notification</option>
        <option value="email">Email</option>
        <option value="both">Both</option>
      </select>
    </div>
    <div class="input-group">
      <label>Title</label>
      <input type="text" name="title" class="input" required maxlength="255">
    </div>
    <div class="input-group">
      <label>Message</label>
      <textarea name="body" class="input" rows="5" required style="resize:vertical;"></textarea>
    </div>
    <div class="dash-form-actions" style="border-top:none;padding-top:0;margin-top:var(--space-4);">
      <button type="submit" class="btn btn-primary">Send broadcast</button>
    </div>
  </form>
</div>

<?php if (!empty($history)): ?>
<?php ui_section_header('Sent broadcasts'); ?>
<div class="dash-panel">
  <div class="dash-table-wrap">
    <table class="dash-table">
      <thead><tr>
        <th>Title</th><th>Audience</th><th>Delivery</th><th class="ta-center">Recipients</th><th>Sent by</th><th>Date</th>
      </tr></thead>
      <tbody>
    <?php foreach ($history as $b): ?>
    <tr>
      <td class="t-strong"><?= e($b['title']) ?></td>
      <td><span class="dash-pill open"><?= e($b['audience']) ?></span></td>
      <td><?= e($b['delivery']) ?></td>
      <td class="ta-center"><?= $b['recipients_count'] ?></td>
      <td><?= e($b['admin_name']) ?></td>
      <td class="t-muted"><?= date_human($b['sent_at']) ?></td>
    </tr>
    <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
