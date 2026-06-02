<?php
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../config/db.php';
try {
    $pdo = db();
    $pdo->prepare("UPDATE email_settings SET smtp_port = 465, smtp_encryption = 'ssl' WHERE id = 1")->execute();
    echo "Updated email_settings to port 465/SSL\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
