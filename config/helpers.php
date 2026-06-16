<?php
define('ROLE_INVESTOR', 'investor');
define('ROLE_BUSINESS_OWNER', 'business_owner');
define('ROLE_FRANCHISOR', 'franchisor');
define('ROLE_ADVISOR', 'advisor');
define('ROLE_ENTREPRENEUR', 'entrepreneur');

function e(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void {
    header('Location: ' . APP_URL . $path);
    exit;
}

// One-way hash for single-use email tokens (password reset / verification).
// We store only the hash so a leaked DB row cannot be used to reset an account.
function reset_token_hash(string $token): string {
    return hash('sha256', $token);
}

// Absolute base URL of the current request (scheme + host), used to build
// links inside emails so they resolve to wherever the app is actually served
// rather than always pointing at the hard-coded production APP_URL.
function public_base_url(): string {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($host === '') {
        return rtrim(APP_URL, '/');
    }
    $scheme = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
    return $scheme . '://' . $host;
}

function redirect_back(): void {
    $ref = $_SERVER['HTTP_REFERER'] ?? '/';
    header('Location: ' . $ref);
    exit;
}

function upload_url(?string $path): string {
    if (!$path) return '';
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) return $path;
    if (str_starts_with($path, 'business-thumbnails/') || str_starts_with($path, 'business-photos/')) {
        return APP_URL . '/public/uploads/' . $path;
    }
    if (str_starts_with($path, '/')) {
        return APP_URL . $path;
    }
    return APP_URL . '/public/uploads/' . $path;
}

function money($amount): string {
    return 'रू ' . number_format((float)$amount, 0);
}

function date_human(?string $datetime): string {
    if (!$datetime) return '—';
    $ts = strtotime($datetime);
    $diff = time() - $ts;
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M j', $ts);
}

function old(string $key, string $default = ''): string {
    return $_SESSION['_old'][$key] ?? $default;
}

function paginate(PDOStatement $countStmt, int $page, int $perPage = 12): array {
    $total = (int)$countStmt->fetchColumn();
    $lastPage = max(1, (int)ceil($total / $perPage));
    $page = max(1, min($page, $lastPage));
    $offset = ($page - 1) * $perPage;
    return ['page' => $page, 'perPage' => $perPage, 'offset' => $offset, 'total' => $total, 'lastPage' => $lastPage];
}

function render_pagination(int $page, int $lastPage, string $baseUrl): string {
    if ($lastPage <= 1) return '';
    $sep = str_contains($baseUrl, '?') ? '&' : '?';
    $html = '<nav class="pagination" role="navigation" aria-label="Pagination">';

    $prevUrl = $baseUrl . $sep . 'page=' . ($page - 1);
    $nextUrl = $baseUrl . $sep . 'page=' . ($page + 1);

    $prevDisabled = $page <= 1 ? ' disabled' : '';
    $nextDisabled = $page >= $lastPage ? ' disabled' : '';

    $html .= '<a href="' . ($prevDisabled ? '#' : $prevUrl) . '" class="page-link page-link-nav' . $prevDisabled . '" ' . ($prevDisabled ? 'tabindex="-1" aria-disabled="true"' : '') . ' aria-label="Previous page">
      <i class="fas fa-chevron-left" style="font-size:14px;"></i>
    </a>';

    $startPage = max(1, min($page - 2, $lastPage - 4));
    $endPage = min($lastPage, max($page + 2, 5));

    if ($lastPage > 7) {
        if ($startPage > 1) {
            $html .= '<a href="' . $baseUrl . $sep . 'page=1" class="page-link" aria-label="Page 1">1</a>';
            if ($startPage > 2) {
                $html .= '<span class="page-link page-link-dots" aria-hidden="true">&hellip;</span>';
            }
        }
        for ($i = $startPage; $i <= $endPage; $i++) {
            $active = $i === $page ? ' active' : '';
            $html .= '<a href="' . $baseUrl . $sep . 'page=' . $i . '" class="page-link' . $active . '" aria-label="Page ' . $i . '"' . ($active ? ' aria-current="page"' : '') . '>' . $i . '</a>';
        }
        if ($endPage < $lastPage) {
            if ($endPage < $lastPage - 1) {
                $html .= '<span class="page-link page-link-dots" aria-hidden="true">&hellip;</span>';
            }
            $html .= '<a href="' . $baseUrl . $sep . 'page=' . $lastPage . '" class="page-link" aria-label="Page ' . $lastPage . '">' . $lastPage . '</a>';
        }
    } else {
        for ($i = 1; $i <= $lastPage; $i++) {
            $active = $i === $page ? ' active' : '';
            $html .= '<a href="' . $baseUrl . $sep . 'page=' . $i . '" class="page-link' . $active . '" aria-label="Page ' . $i . '"' . ($active ? ' aria-current="page"' : '') . '>' . $i . '</a>';
        }
    }

    $html .= '<a href="' . ($nextDisabled ? '#' : $nextUrl) . '" class="page-link page-link-nav' . $nextDisabled . '" ' . ($nextDisabled ? 'tabindex="-1" aria-disabled="true"' : '') . ' aria-label="Next page">
      <i class="fas fa-chevron-right" style="font-size:14px;"></i>
    </a>';

    $html .= '</nav>';
    return $html;
}

function generate_slug(string $str): string {
    $str = preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($str)));
    $slug = trim($str, '-');
    if ($slug === '' || $slug === '-') {
        $slug = 'business-' . time();
    }
    return $slug;
}

function unique_slug(string $slug, string $table, string $column = 'slug'): string {
    $db = db();
    $original = $slug;
    $i = 1;
    $stmt = $db->prepare("SELECT COUNT(*) FROM {$table} WHERE {$column} = ?");
    while (true) {
        $stmt->execute([$slug]);
        if ((int)$stmt->fetchColumn() === 0) break;
        $slug = $original . '-' . $i++;
    }
    return $slug;
}

function remove_query_param(string $url, string $param): string {
    $parts = parse_url($url);
    parse_str($parts['query'] ?? '', $qs);
    unset($qs[$param]);
    $baseUrl = ($parts['path'] ?? '');
    if ($qs) {
        return $baseUrl . '?' . http_build_query($qs);
    }
    return $baseUrl;
}

function notifiable_roles(): array {
    return [ROLE_INVESTOR, ROLE_BUSINESS_OWNER, ROLE_FRANCHISOR, ROLE_ADVISOR, ROLE_ENTREPRENEUR];
}

function admin_log(string $action, ?string $targetType = null, ?int $targetId = null, ?array $details = null): void {
    $admin = current_user();
    if (!$admin) return;
    $stmt = db()->prepare('INSERT INTO admin_audit_log (admin_id, action, target_type, target_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $admin['id'],
        $action,
        $targetType,
        $targetId,
        $details ? json_encode($details) : null,
        $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
    ]);
}
