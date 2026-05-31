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

function redirect_back(): void {
    $ref = $_SERVER['HTTP_REFERER'] ?? '/';
    header('Location: ' . $ref);
    exit;
}

function money($amount): string {
    return 'NPR ' . number_format((float)$amount, 0);
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
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
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
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
    </a>';

    $html .= '</nav>';
    return $html;
}

function generate_slug(string $str): string {
    $str = preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($str)));
    return trim($str, '-');
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
