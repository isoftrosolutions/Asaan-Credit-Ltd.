<?php
require __DIR__ . '/../config/bootstrap.php';
try {
    $count = db()->query("SELECT COUNT(*) FROM pitches")->fetchColumn();
    echo "Total pitches: " . $count . "\n";
    $latest = db()->query("SELECT id, tagline, is_published FROM pitches ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($latest as $p) {
        echo "  ID: {$p['id']}, Tagline: {$p['tagline']}, Published: {$p['is_published']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
