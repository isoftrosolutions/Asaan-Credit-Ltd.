<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

$sectors = db()->query("SELECT id, name, slug FROM sectors WHERE is_active = 1 ORDER BY name")->fetchAll();
json_success($sectors);
