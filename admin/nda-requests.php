<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();
flash_set('info', 'NDA requests have been removed. Documents are now premium-gated directly.');
redirect('/admin');
