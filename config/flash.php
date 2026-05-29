<?php
function flash_set(string $key, string $value): void {
    $_SESSION['_flash'][$key] = $value;
}

function flash_get(string $key): ?string {
    $val = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $val;
}

function flash_has(string $key): bool {
    return isset($_SESSION['_flash'][$key]);
}

function flash_render(): void {
    foreach (['success', 'error', 'info', 'warning'] as $type) {
        $msg = flash_get($type);
        if ($msg) {
            echo '<div class="flash flash-' . $type . '">' . e($msg) . '</div>';
        }
    }
}
