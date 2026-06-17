<?php
/**
 * Dashboard UI kit — reusable, role-agnostic render helpers for the redesigned
 * dashboard (Phase 1). Pure presentation: no queries, no business logic. Every
 * helper echoes HTML. Used by includes/layout-dashboard.php and the per-role
 * dashboard pages. UI icons use Font Awesome (fas) classes.
 */

if (!defined('UI_PHP_LOADED')) {
    define('UI_PHP_LOADED', 1);

    /** Inline SVG icon library (stroke-based, inherit currentColor). */
    $GLOBALS['UI_ICONS'] = [
        'home'       => '<path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
        'matches'    => '<path d="M5 5a2 2 0 012-2 3 3 0 003 3 3 3 0 003-3 2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V5z"/>',
        'bell'       => '<path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>',
        'user'       => '<path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
        'users'      => '<path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>',
        'settings'   => '<path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
        'document'   => '<path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
        'briefcase'  => '<path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>',
        'chart'      => '<path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>',
        'logout'     => '<path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>',
        'plus'       => '<path d="M12 4v16m8-8H4"/>',
        'arrowRight' => '<path d="M5 12h14M12 5l7 7-7 7"/>',
        'search'     => '<path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>',
        'heart'      => '<path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>',
        'tag'        => '<path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>',
        'lock'       => '<path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>',
        'share'      => '<path d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>',
        'check'      => '<path d="M5 13l4 4L19 7"/>',
        'clock'      => '<path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'mapPin'     => '<path d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>',
        'trending'   => '<path d="M23 6l-9.5 9.5-5-5L1 18"/><path d="M17 6h6v6"/>',
        'sparkles'   => '<path d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9L12 3z"/><path d="M5 19l.7 1.9L7.6 21l-1.9.7L5 23.6 4.3 21.7 2.4 21l1.9-.1L5 19z"/>',
        'bulb'       => '<path d="M9 18h6M10 21h4M12 3a6 6 0 00-3.6 10.8c.4.3.6.8.6 1.2v.5h6v-.5c0-.4.2-.9.6-1.2A6 6 0 0012 3z"/>',
        'menu'       => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'close'      => '<path d="M6 18L18 6M6 6l12 12"/>',
        'eye'        => '<path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>',
        'inbox'      => '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.45 5.11L2 12v6a2 2 0 002 2h16a2 2 0 002-2v-6l-3.45-6.89A2 2 0 0016.76 4H7.24a2 2 0 00-1.79 1.11z"/>',
    ];

    /** Font Awesome icon name lookup. */
    $GLOBALS['UI_ICONS_FA'] = [
        'home'       => 'fa-home',
        'search'     => 'fa-search',
        'bell'       => 'fa-bell',
        'user'       => 'fa-user',
        'users'      => 'fa-users',
        'settings'   => 'fa-cog',
        'document'   => 'fa-file-alt',
        'briefcase'  => 'fa-briefcase',
        'matches'    => 'fa-handshake',
        'plus'       => 'fa-plus',
        'close'      => 'fa-times',
        'menu'       => 'fa-bars',
        'upload'     => 'fa-upload',
        'filter'     => 'fa-filter',
        'logout'     => 'fa-sign-out-alt',
        'tag'        => 'fa-tag',
        'share'      => 'fa-share-alt',
        'clock'      => 'fa-clock',
        'inbox'      => 'fa-inbox',
        'eye'        => 'fa-eye',
        'heart'      => 'fa-heart',
        'chart'      => 'fa-chart-line',
        'arrowRight' => 'fa-arrow-right',
        'check'      => 'fa-check',
        'star'       => 'fa-star',
        'mapPin'     => 'fa-map-marker-alt',
        'lock'       => 'fa-lock',
        'trending'   => 'fa-chart-line',
        'sparkles'   => 'fa-sparkles',
        'bulb'       => 'fa-lightbulb',
        'mail'       => 'fa-envelope',
    ];

    /** Echo a Font Awesome icon by name. */
    function ui_icon(string $name, string $class = 'ui-ico'): void {
        $fa = $GLOBALS['UI_ICONS_FA'][$name] ?? 'fa-circle';
        $cls = $class !== '' ? ' ' . e($class) : '';
        echo '<i class="fas ' . $fa . $cls . '" aria-hidden="true"></i>';
    }

    /** Return a Font Awesome icon string (when you need it inline, not echoed). */
    function ui_icon_str(string $name, string $class = 'ui-ico'): string {
        $fa = $GLOBALS['UI_ICONS_FA'][$name] ?? 'fa-circle';
        $cls = $class !== '' ? ' ' . e($class) : '';
        return '<i class="fas ' . $fa . $cls . '" aria-hidden="true"></i>';
    }

    /** Role → sidebar links. Mirrors DASHBOARD_LINKS in assets/header.js. */
    function ui_dashboard_links(string $role): array {
        $role = [
            'owner' => 'business_owner',
            'ceo' => 'business_owner',
            'cfo' => 'business_owner',
            'individual_investor' => 'investor',
            'investment_manager' => 'investor',
            'broker' => 'advisor',
        ][$role] ?? $role;
        $map = [
            'investor' => [
                ['Dashboard', '/dashboard', 'home'],
                ['My Connections', '/connections', 'matches'],
                ['Notifications', '/notifications', 'bell'],
                ['My Profile', '/investor/profile-edit', 'user'],
                ['Preferences', '/investor/preferences-edit', 'settings'],
                ['Documents', '/investor/documents-edit', 'document'],
            ],
            'business_owner' => [
                ['Dashboard', '/dashboard', 'home'],
                ['My Listing', '/business/dashboard.php', 'briefcase'],
                ['Connections', '/connections', 'matches'],
                ['Notifications', '/notifications', 'bell'],
                ['Settings', '/business/edit', 'settings'],
            ],
            'entrepreneur' => [
                ['Dashboard', '/dashboard', 'home'],
                ['My Pitch', '/entrepreneur/dashboard.php', 'chart'],
                ['Connections', '/connections', 'matches'],
                ['Notifications', '/notifications', 'bell'],
                ['Settings', '/entrepreneur/pitch-edit', 'settings'],
            ],
            'franchisor' => [
                ['Dashboard', '/dashboard', 'home'],
                ['My Franchise', '/franchise/dashboard.php', 'briefcase'],
                ['Connections', '/connections', 'matches'],
                ['Notifications', '/notifications', 'bell'],
                ['Settings', '/franchise/edit', 'settings'],
            ],
            'advisor' => [
                ['Dashboard', '/dashboard', 'home'],
                ['My Profile', '/advisor/edit', 'user'],
                ['Connections', '/connections', 'matches'],
                ['Notifications', '/notifications', 'bell'],
                ['Settings', '/advisor/edit', 'settings'],
            ],
        ];
        return $map[$role] ?? $map['investor'];
    }

    function ui_admin_links(): array {
        return [
            ['Verification Queue', '/admin/verification', 'document'],
            ['Businesses', '/admin/businesses', 'briefcase'],
            ['Business Verifications', '/admin/business-verifications', 'check'],
            ['Business Inquiries', '/admin/inquiries', 'mail'],
            ['NDA Requests', '/admin/nda-requests', 'lock'],
            ['Interest Log', '/admin/interest-log', 'share'],
            ['Users', '/admin/users', 'users'],
            ['Pitches', '/admin/pitches', 'tag'],
            ['Reports', '/admin/reports', 'lock'],
            ['Premium', '/admin/premium', 'star'],
            ['Broadcast', '/admin/broadcast', 'share'],
            ['Sectors', '/admin/sectors', 'tag'],
            ['Email Settings', '/admin/email-settings', 'settings'],
            ['Pages', '/admin/pages', 'document'],
            ['Email Templates', '/admin/email-templates', 'document'],
            ['Email Log', '/admin/email-log', 'mail'],
            ['FAQs', '/admin/faqs', 'bell'],
            ['Blog', '/admin/blog', 'document'],
            ['Homepage', '/admin/homepage', 'settings'],
        ];
    }

    /** Human label for a role code. */
    function ui_role_label(string $role): string {
        return [
            'investor' => 'Investor',
            'business_owner' => 'Business Owner',
            'entrepreneur' => 'Entrepreneur',
            'franchisor' => 'Franchisor',
            'advisor' => 'Advisor',
        ][$role] ?? ucfirst(str_replace('_', ' ', $role));
    }

    /** Is the given app-relative path the current page? */
    function ui_is_active(string $url): bool {
        $cur = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        // Normalise the dev "/assan" prefix away for comparison.
        $cur = preg_replace('#^/assan#', '', $cur) ?: '/';
        if ($url === '/dashboard') return $cur === '/dashboard' || $cur === '/' ;
        return $cur === $url || str_starts_with($cur, $url . '/');
    }

    /* ---------------------------------------------------------------------
     * Chrome: sidebar + topbar (rendered by layout-dashboard.php)
     * ------------------------------------------------------------------- */

    function ui_sidebar(array $user, int $unreadCount, bool $isAdmin): void {
        $links = $isAdmin ? ui_admin_links() : ui_dashboard_links($user['role'] ?? 'investor');
        ?>
        <aside class="dash-sidebar" id="dashSidebar" aria-label="Dashboard navigation">
          <div class="dash-sidebar-brand">
            <a href="<?= APP_URL ?>/" class="dash-brand-link" aria-label="Asaan Capital home">
              <img src="<?= APP_URL ?>/assets/asaan-capital-logo-header.png" alt="Asaan Capital Ltd" class="dash-brand-logo">
            </a>
            <button class="dash-sidebar-close" type="button" onclick="dashCloseSidebar()" aria-label="Close menu"><?php ui_icon('close'); ?></button>
          </div>

          <nav class="dash-nav">
            <?php foreach ($links as [$label, $url, $icon]):
              $active = ui_is_active($url);
              $badge = ($label === 'Notifications' && $unreadCount > 0)
                  ? '<span class="dash-nav-badge">' . ($unreadCount > 9 ? '9+' : $unreadCount) . '</span>' : '';
            ?>
            <a href="<?= APP_URL . $url ?>" class="dash-nav-item<?= $active ? ' active' : '' ?>"<?= $active ? ' aria-current="page"' : '' ?>>
              <span class="dash-nav-ico"><?php ui_icon($icon); ?></span>
              <span class="dash-nav-label"><?= e($label) ?></span>
              <?= $badge ?>
            </a>
            <?php endforeach; ?>
          </nav>

          <div class="dash-sidebar-footer">
            <a href="<?= APP_URL ?>/logout" class="dash-nav-item dash-nav-logout">
              <span class="dash-nav-ico"><?php ui_icon('logout'); ?></span>
              <span class="dash-nav-label">Log out</span>
            </a>
          </div>
        </aside>
        <div class="dash-sidebar-backdrop" id="dashSidebarBackdrop" onclick="dashCloseSidebar()"></div>
        <?php
    }

    function ui_topbar(array $user, int $unreadCount): void {
        $initial = mb_strtoupper(mb_substr($user['name'] ?? 'U', 0, 1));
        $bellLabel = $unreadCount > 0 ? "Notifications ({$unreadCount} unread)" : 'Notifications';
        ?>
        <header class="dash-topbar">
          <button class="dash-menu-toggle" type="button" onclick="dashOpenSidebar()" aria-label="Open menu"><?php ui_icon('menu'); ?></button>
          <form class="dash-topbar-search" action="<?= APP_URL ?>/search" method="get" role="search">
            <span class="dash-search-ico"><?php ui_icon('search'); ?></span>
            <input type="text" name="q" placeholder="Search businesses, investors, sectors…" aria-label="Search">
          </form>
          <div class="dash-topbar-actions">
            <button type="button" class="dash-iconbtn" onclick="openSavedModal()" aria-label="Saved listings" title="Saved listings">
              <i class="fas fa-heart"></i>
              <span class="saved-count dash-iconbtn-badge" style="display:none;">0</span>
            </button>
            <a href="<?= APP_URL ?>/notifications" class="dash-iconbtn" aria-label="<?= e($bellLabel) ?>">
              <?php ui_icon('bell'); ?>
              <?php if ($unreadCount > 0): ?><span class="dash-iconbtn-badge"><?= $unreadCount > 9 ? '9+' : $unreadCount ?></span><?php endif; ?>
            </a>
            <a href="<?= APP_URL ?>/dashboard" class="dash-topbar-user" aria-label="<?= e($user['name'] ?? 'User') ?>">
              <div class="dash-avatar dash-avatar-sm"><?= e($initial) ?></div>
              <span class="dash-topbar-username"><?= e($user['name'] ?? 'User') ?></span>
            </a>
          </div>
        </header>
        <?php
    }

    /* ---------------------------------------------------------------------
     * Content partials
     * ------------------------------------------------------------------- */

    /** Page hero: title, subtitle, optional right-aligned action HTML. */
    function ui_page_header(string $title, string $subtitle = '', string $actionsHtml = ''): void {
        ?>
        <div class="dash-pagehead">
          <div class="dash-pagehead-text">
            <h1 class="dash-pagehead-title"><?= e($title) ?></h1>
            <?php if ($subtitle !== ''): ?><p class="dash-pagehead-sub"><?= $subtitle ?></p><?php endif; ?>
          </div>
          <?php if ($actionsHtml !== ''): ?><div class="dash-pagehead-actions"><?= $actionsHtml ?></div><?php endif; ?>
        </div>
        <?php
    }

    /** Section header inside the content area. */
    function ui_section_header(string $title, string $linkHref = '', string $linkLabel = ''): void {
        ?>
        <div class="dash-section-head">
          <h2 class="dash-section-title"><?= e($title) ?></h2>
          <?php if ($linkHref !== ''): ?>
            <a href="<?= e($linkHref) ?>" class="dash-section-link"><?= e($linkLabel ?: 'View all') ?> <?php ui_icon('arrowRight'); ?></a>
          <?php endif; ?>
        </div>
        <?php
    }

    /**
     * KPI stat card.
     * $o: label, value, icon, tone(success|info|warning|primary), delta?, deltaUp?(bool), spark?(int[])
     */
    function ui_stat_card(array $o): void {
        $tone = $o['tone'] ?? 'primary';
        $delta = $o['delta'] ?? '';
        $deltaUp = $o['deltaUp'] ?? true;
        ?>
        <div class="dash-stat tone-<?= e($tone) ?>">
          <div class="dash-stat-top">
            <span class="dash-stat-ico"><?php ui_icon($o['icon'] ?? 'chart'); ?></span>
            <?php if ($delta !== ''): ?>
              <span class="dash-stat-delta <?= $deltaUp ? 'up' : 'down' ?>"><?= e($delta) ?></span>
            <?php endif; ?>
          </div>
          <div class="dash-stat-value"><?= e((string)($o['value'] ?? '0')) ?></div>
          <div class="dash-stat-label"><?= e($o['label'] ?? '') ?></div>
          <?php if (!empty($o['spark']) && is_array($o['spark'])): ?>
            <div class="dash-stat-spark"><?= ui_sparkline($o['spark'], $tone) ?></div>
          <?php endif; ?>
        </div>
        <?php
    }

    /** Quick-action tile. $o: title, desc, icon, href, tone? */
    function ui_quick_action(array $o): void {
        $tone = $o['tone'] ?? 'primary';
        ?>
        <a href="<?= e($o['href'] ?? '#') ?>" class="dash-qa tone-<?= e($tone) ?>">
          <span class="dash-qa-ico"><?php ui_icon($o['icon'] ?? 'arrowRight'); ?></span>
          <span class="dash-qa-text">
            <span class="dash-qa-title"><?= e($o['title'] ?? '') ?></span>
            <?php if (!empty($o['desc'])): ?><span class="dash-qa-desc"><?= e($o['desc']) ?></span><?php endif; ?>
          </span>
          <span class="dash-qa-arrow"><?php ui_icon('arrowRight'); ?></span>
        </a>
        <?php
    }

    /**
     * Inline SVG sparkline from an array of numbers. Returns an HTML string.
     * Flat baseline when all values equal. Width/height fixed; scales to box.
     */
    function ui_sparkline(array $values, string $tone = 'primary', int $w = 120, int $h = 36): string {
        $values = array_values(array_map('floatval', $values));
        $n = count($values);
        if ($n === 0) return '';
        if ($n === 1) { $values = [$values[0], $values[0]]; $n = 2; }
        $min = min($values); $max = max($values);
        $range = ($max - $min) ?: 1;
        $stepX = $w / ($n - 1);
        $pts = [];
        foreach ($values as $i => $v) {
            $x = round($i * $stepX, 2);
            $y = round($h - (($v - $min) / $range) * ($h - 6) - 3, 2);
            $pts[] = "$x,$y";
        }
        $line = implode(' ', $pts);
        $area = "0,$h " . $line . "," . $w . ",$h";
        $colorMap = [
            'success' => 'var(--color-success)',
            'info'    => 'var(--color-info)',
            'warning' => 'var(--color-warning)',
            'primary' => 'var(--color-primary-vivid)',
        ];
        $c = $colorMap[$tone] ?? $colorMap['primary'];
        $gid = 'spk' . substr(md5($line . $tone), 0, 6);
        return '<svg class="dash-spark" viewBox="0 0 ' . $w . ' ' . $h . '" preserveAspectRatio="none" aria-hidden="true">'
            . '<defs><linearGradient id="' . $gid . '" x1="0" y1="0" x2="0" y2="1">'
            . '<stop offset="0%" stop-color="' . $c . '" stop-opacity="0.22"/>'
            . '<stop offset="100%" stop-color="' . $c . '" stop-opacity="0"/></linearGradient></defs>'
            . '<polygon points="' . $area . '" fill="url(#' . $gid . ')"/>'
            . '<polyline points="' . $line . '" fill="none" stroke="' . $c . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>'
            . '</svg>';
    }

    /** Pro-tip / callout card. $o: title, body, icon?, tone? */
    function ui_pro_tip(array $o): void {
        ?>
        <div class="dash-protip tone-<?= e($o['tone'] ?? 'info') ?>">
          <span class="dash-protip-ico"><?php ui_icon($o['icon'] ?? 'bulb'); ?></span>
          <div class="dash-protip-body">
            <div class="dash-protip-title"><?= e($o['title'] ?? 'Pro tip') ?></div>
            <p class="dash-protip-text"><?= $o['body'] ?? '' ?></p>
          </div>
        </div>
        <?php
    }

    /** Empty-state block. $o: icon?, imageSrc?, imageAlt?, title, text?, ctaHref?, ctaLabel? */
    function ui_empty_state(array $o): void {
        ?>
        <div class="dash-empty">
          <?php if (!empty($o['imageSrc'])): ?>
            <img class="dash-empty-image" src="<?= e($o['imageSrc']) ?>" alt="<?= e($o['imageAlt'] ?? '') ?>" loading="lazy">
          <?php else: ?>
            <span class="dash-empty-ico"><?php ui_icon($o['icon'] ?? 'inbox'); ?></span>
          <?php endif; ?>
          <div class="dash-empty-title"><?= e($o['title'] ?? 'Nothing here yet') ?></div>
          <?php if (!empty($o['text'])): ?><p class="dash-empty-text"><?= e($o['text']) ?></p><?php endif; ?>
          <?php if (!empty($o['ctaHref'])): ?>
            <a href="<?= e($o['ctaHref']) ?>" class="btn btn-sm btn-primary"><?= e($o['ctaLabel'] ?? 'Get started') ?></a>
          <?php endif; ?>
        </div>
        <?php
    }
}
