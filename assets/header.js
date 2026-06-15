/* ============================================================
   HEADER — JS-injected (public & authenticated variants)
   Mobile-first with hamburger slide-out panel
   Mirrors ui_dashboard_links / ui_admin_links in includes/ui.php
   ============================================================ */

const DASHBOARD_LINKS = {
  investor: [
    ['Dashboard', '/dashboard', 'home'],
    ['My Connections', '/connections', 'matches'],
    ['Notifications', '/notifications', 'bell'],
    ['My Profile', '/investor/profile-edit', 'user'],
    ['Preferences', '/investor/preferences-edit', 'settings'],
    ['Documents', '/investor/documents-edit', 'document'],
  ],
  business_owner: [
    ['Dashboard', '/dashboard', 'home'],
    ['My Listing', '/business/dashboard.php', 'briefcase'],
    ['Connections', '/connections', 'matches'],
    ['Notifications', '/notifications', 'bell'],
    ['Settings', '/business/edit', 'settings'],
  ],
  entrepreneur: [
    ['Dashboard', '/dashboard', 'home'],
    ['My Pitch', '/entrepreneur/dashboard.php', 'chart'],
    ['Connections', '/connections', 'matches'],
    ['Notifications', '/notifications', 'bell'],
    ['Settings', '/entrepreneur/pitch-edit', 'settings'],
  ],
  franchisor: [
    ['Dashboard', '/dashboard', 'home'],
    ['My Franchise', '/franchise/dashboard.php', 'briefcase'],
    ['Connections', '/connections', 'matches'],
    ['Notifications', '/notifications', 'bell'],
    ['Settings', '/franchise/edit', 'settings'],
  ],
  advisor: [
    ['Dashboard', '/dashboard', 'home'],
    ['My Profile', '/advisor/edit', 'user'],
    ['Connections', '/connections', 'matches'],
    ['Notifications', '/notifications', 'bell'],
    ['Settings', '/advisor/edit', 'settings'],
  ],
};

const ADMIN_LINKS = [
  ['Verification Queue', '/admin/verification', 'document'],
  ['Business Verifications', '/admin/business-verifications', 'check'],
  ['Business Inquiries', '/admin/inquiries', 'mail'],
  ['NDA Requests', '/admin/nda-requests', 'lock'],
  ['Interest Log', '/admin/interest-log', 'share'],
  ['Users', '/admin/users', 'users'],
  ['Pitches', '/admin/pitches', 'tag'],
  ['Reports', '/admin/reports', 'lock'],
  ['Broadcast', '/admin/broadcast', 'share'],
  ['Sectors', '/admin/sectors', 'tag'],
  ['Email Settings', '/admin/email-settings', 'settings'],
  ['Email Templates', '/admin/email-templates', 'document'],
  ['Email Log', '/admin/email-log', 'mail'],
  ['FAQs', '/admin/faqs', 'bell'],
  ['Blog', '/admin/blog', 'document'],
  ['Homepage', '/admin/homepage', 'settings'],
];

const PUBLIC_NAV = [
  ['Home', '/'],
  ['Investment & Opportunities', '/browse/businesses'],
  ['About Us', '/about'],
  ['Blog', '/blog'],
  ['Contact', '/contact'],
];

/* ---- helpers ---- */

function _icon(name) {
  const svg = ICONS[name];
  return svg || '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4m0 4h.01"/></svg>';
}

function _closeIcon() {
  return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';
}

function _menuIcon() {
  return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>';
}

function _isActive(url) {
  var cur = window.location.pathname;
  if (cur.indexOf('/assan') === 0) cur = cur.replace(/^\/assan/, '') || '/';
  if (url === '/') return cur === '/' || cur === '/dashboard';
  return cur === url || cur.indexOf(url + '/') === 0;
}

function _navLinksHtml(links) {
  var html = '';
  for (var i = 0; i < links.length; i++) {
    var label = links[i][0];
    var url = links[i][1];
    var active = _isActive(url) ? ' class="active"' : '';
    html += '<a href="' + url + '"' + active + '>' + label + '</a>';
  }
  return html;
}

function _mobileNavHtml(links) {
  var html = '';
  for (var i = 0; i < links.length; i++) {
    var label = links[i][0];
    var url = links[i][1];
    var icon = links[i][2] || 'home';
    var active = _isActive(url) ? ' class="active"' : '';
    html += '<a href="' + url + '"' + active + '>' + _icon(icon) + label + '</a>';
  }
  return html;
}

function _assetUrl(path) {
  var scripts = document.getElementsByTagName('script');
  for (var i = 0; i < scripts.length; i++) {
    var src = scripts[i].getAttribute('src') || '';
    var marker = '/assets/header.js';
    var markerPos = src.indexOf(marker);
    if (markerPos !== -1) return src.slice(0, markerPos) + path;
  }
  return path;
}

/* ---- injectHeader(type, actionsHtml) ---- */
function injectHeader(type, actionsHtml) {
  var root = document.getElementById('header-root');
  if (!root) return;

  var isAuth = type === 'dashboard' || type === 'admin';

  var desktopNavHtml = _navLinksHtml(PUBLIC_NAV);

  var mobileNavHtml = _mobileNavHtml(PUBLIC_NAV);
  var logoSrc = _assetUrl('/assets/asaan-capital-logo-header.png');

  var ROLE_ALIAS = { owner:'business_owner', ceo:'business_owner', cfo:'business_owner', individual_investor:'investor', investment_manager:'investor', broker:'advisor' };
  var dashboardLinksHtml = '';
  if (isAuth && typeof CURRENT_USER !== 'undefined' && CURRENT_USER) {
    var roleKey = ROLE_ALIAS[CURRENT_USER.role] || CURRENT_USER.role;
    var links = type === 'admin' ? ADMIN_LINKS : (DASHBOARD_LINKS[roleKey] || DASHBOARD_LINKS.investor);
    dashboardLinksHtml = _mobileNavHtml(links);
  }

  root.innerHTML =
    '<header class="site-header' + (type === 'admin' ? ' stitch-header' : '') + '" id="mainHeader">' +
      '<div class="header-inner">' +
        '<div class="header-logo-section" style="display:flex;align-items:center;gap:8px;">' +
          '<button class="hamburger" id="hamburgerBtn" type="button" aria-label="Open menu">' + _menuIcon() + '</button>' +
          '<a href="/" class="header-logo" aria-label="Asaan Capital Ltd">' +
            '<img src="' + logoSrc + '" alt="Asaan Capital Ltd" class="header-logo-img">' +
          '</a>' +
        '</div>' +
        '<nav class="desktop-nav" aria-label="Main navigation">' + desktopNavHtml + '</nav>' +
        '<div class="header-actions">' + (actionsHtml || '') + '</div>' +
      '</div>' +
    '</header>' +
    '<div class="mobile-backdrop" id="mobileBackdrop"></div>' +
    '<div class="mobile-panel" id="mobilePanel">' +
      '<div class="mobile-panel-head">' +
        '<img src="' + logoSrc + '" alt="Asaan Capital Ltd" class="mobile-panel-logo">' +
        '<button class="mobile-panel-close" id="mobilePanelClose" type="button" aria-label="Close menu">' + _closeIcon() + '</button>' +
      '</div>' +
      '<div class="mobile-panel-body">' +
        '<ul class="mobile-panel-nav">' + mobileNavHtml + '</ul>' +
        (dashboardLinksHtml ? '<hr style="border:none;border-top:1px solid var(--color-border);margin:12px 0;"><ul class="mobile-panel-nav">' + dashboardLinksHtml + '</ul>' : '') +
      '</div>' +
      (actionsHtml ? '<div class="mobile-panel-footer">' + actionsHtml + '</div>' : '') +
    '</div>';

  /* ---- Mobile menu toggle ---- */
  var hamburger = document.getElementById('hamburgerBtn');
  var panel = document.getElementById('mobilePanel');
  var backdrop = document.getElementById('mobileBackdrop');
  var closeBtn = document.getElementById('mobilePanelClose');

  function openMenu() {
    panel.classList.add('open');
    backdrop.classList.add('open');
    document.body.classList.add('menu-open');
  }

  function closeMenu() {
    panel.classList.remove('open');
    backdrop.classList.remove('open');
    document.body.classList.remove('menu-open');
  }

  if (hamburger) hamburger.addEventListener('click', openMenu);
  if (closeBtn) closeBtn.addEventListener('click', closeMenu);
  if (backdrop) backdrop.addEventListener('click', closeMenu);

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeMenu();
  });

  /* ---- Scroll shadow (IntersectionObserver, no scroll listener) ---- */
  var header = document.getElementById('mainHeader');
  if (header) {
    var sentinel = document.createElement('div');
    sentinel.style.position = 'absolute';
    sentinel.style.top = '0';
    sentinel.style.left = '0';
    sentinel.style.width = '1px';
    sentinel.style.height = '1px';
    sentinel.style.pointerEvents = 'none';
    document.body.prepend(sentinel);

    var scrollObserver = new IntersectionObserver(function (entries) {
      header.classList.toggle('scrolled', !entries[0].isIntersecting);
    }, { threshold: [0], rootMargin: '-1px 0px 0px 0px' });
    scrollObserver.observe(sentinel);
  }
}
