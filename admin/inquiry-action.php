<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

csrf_check();

$id = (int)($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($id < 1 || !in_array($action, ['mark_read', 'archive'], true)) {
    flash_set('error', 'Invalid request.');
    redirect('/admin/inquiries');
}

$db = db();

if ($action === 'mark_read') {
    $db->prepare("UPDATE business_inquiries SET status = 'read', updated_at = NOW() WHERE id = ?")->execute([$id]);
    flash_set('success', 'Inquiry marked as read.');
} elseif ($action === 'archive') {
    $db->prepare("UPDATE business_inquiries SET status = 'archived', updated_at = NOW() WHERE id = ?")->execute([$id]);
    flash_set('success', 'Inquiry archived.');
}

redirect('/admin/inquiries');
