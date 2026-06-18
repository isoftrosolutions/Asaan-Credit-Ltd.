<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

csrf_check();

$id = (int)($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($id < 1 || !in_array($action, ['mark_read', 'archive', 'delete'], true)) {
    flash_set('error', 'Invalid request.');
    redirect('/admin/contact-messages');
}

$db = db();

if ($action === 'mark_read') {
    $db->prepare("UPDATE contact_messages SET status = 'read', updated_at = NOW() WHERE id = ?")->execute([$id]);
    admin_log('mark_contact_message_read', 'contact_message', $id);
    flash_set('success', 'Message marked as read.');
} elseif ($action === 'archive') {
    $db->prepare("UPDATE contact_messages SET status = 'archived', updated_at = NOW() WHERE id = ?")->execute([$id]);
    admin_log('archive_contact_message', 'contact_message', $id);
    flash_set('success', 'Message archived.');
} elseif ($action === 'delete') {
    $db->prepare('DELETE FROM contact_messages WHERE id = ?')->execute([$id]);
    admin_log('delete_contact_message', 'contact_message', $id);
    flash_set('success', 'Message deleted.');
}

redirect('/admin/contact-messages');

