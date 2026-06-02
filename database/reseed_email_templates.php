<?php
/**
 * Re-seed email_templates table from config/email_templates.php
 * Run: php database/reseed_email_templates.php
 * Fixes encoding corruption from the UTF-16LE BOM migration file.
 */
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../config/db.php';

$templates = require __DIR__ . '/../config/email_templates.php';

try {
    $pdo = db();
    $pdo->beginTransaction();

    $pdo->exec('DELETE FROM email_templates');

    $stmt = $pdo->prepare(
        'INSERT INTO email_templates (template_key, name, subject, body, variables, is_active, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, 1, NOW(), NOW())'
    );

    $count = 0;
    foreach ($templates as $key => $tpl) {
        $variables = isset($tpl['variables']) ? json_encode($tpl['variables']) : '[]';
        $stmt->execute([
            $key,
            $tpl['name'] ?? $key,
            $tpl['subject'] ?? '',
            $tpl['body'] ?? ($tpl['content_html'] ?? ''),
            $variables,
        ]);
        echo "  Inserted: $key\n";
        $count++;
    }

    $pdo->commit();
    echo "\nDone. $count templates re-seeded with clean UTF-8 data.\n";
} catch (\Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
