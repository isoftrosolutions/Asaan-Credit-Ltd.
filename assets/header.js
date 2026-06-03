const PUBLIC_LINKS = [
  { label: 'Home', url: '/', icon: 'home' },
  { label: 'How It Works', url: '/how-it-works', icon: 'document' },
  { label: 'Valuation', url: '/business-valuation', icon: 'chart' },
  { label: 'Support', url: '/support', icon: 'message' },
];

// SMERGERS-style public mega-menu (Phase 1)
const PUBLIC_NAV = [
  { label: 'Home', url: '/' },
  { label: 'Businesses for Sale', url: '/browse/businesses' },
  { label: 'Franchises', url: '/browse/franchises' },
  { label: 'Investors & Buyers', url: '/browse/investors' },
  { label: 'Invest in Startups', url: '/browse/entrepreneurs' },
  { label: 'How To', url: '/how-it-works' },
  { label: 'Q & A', url: '/support' },
];

const COMPANY_LINKS = [
  { label: 'Our Story', url: '/about' },
  { label: 'Contact Us', url: '/contact' },
  { label: 'Careers', url: '/careers' },
  { label: 'Press', url: '/press' },
  { label: 'Testimonials', url: '/testimonials' },
  { label: 'Blog', url: '/blog' },
  { label: 'Industry Watch', url: '/industry-watch' },
];

const ADD_PROFILE_LINKS = [
  { label: 'Add Business Profile', url: '/business/create' },
  { label: 'Add Investor Profile', url: '/investor/profile-create' },
  { label: 'Add Franchise Profile', url: '/franchise/create' },
  { label: 'Add Advisor Profile', url: '/advisor/create' },
];

const CARET = '<svg class="pub-caret" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>';

const DASHBOARD_LINKS = {
  investor: [
    { label: 'Dashboard', url: '/dashboard', icon: 'home' },
    { label: 'My Connections', url: '/connections', icon: 'matches' },
    { label: 'Notifications', url: '/notifications', icon: 'bell' },
    { label: 'My Profile', url: '/investor/profile-edit', icon: 'user' },
    { label: 'Preferences', url: '/investor/preferences-edit', icon: 'settings' },
    { label: 'Settings', url: '/investor/profile-edit', icon: 'settings' },
    { label: 'Documents', url: '/investor/documents-edit', icon: 'document' },
  ],
  owner: [
    { label: 'Dashboard', url: '/dashboard', icon: 'home' },
    { label: 'My Listing', url: '/business/dashboard.php', icon: 'briefcase' },
    { label: 'Connections', url: '/connections', icon: 'matches' },
    { label: 'Notifications', url: '/notifications', icon: 'bell' },
    { label: 'Settings', url: '/business/edit', icon: 'settings' },
  ],
  entrepreneur: [
    { label: 'Dashboard', url: '/dashboard', icon: 'home' },
    { label: 'My Pitch', url: '/entrepreneur/dashboard.php', icon: 'chart' },
    { label: 'Connections', url: '/connections', icon: 'matches' },
    { label: 'Notifications', url: '/notifications', icon: 'bell' },
    { label: 'Settings', url: '/entrepreneur/pitch-edit', icon: 'settings' },
  ],
  franchisor: [
    { label: 'Dashboard', url: '/dashboard', icon: 'home' },
    { label: 'My Franchise', url: '/franchise/dashboard.php', icon: 'briefcase' },
    { label: 'Connections', url: '/connections', icon: 'matches' },
    { label: 'Notifications', url: '/notifications', icon: 'bell' },
    { label: 'Settings', url: '/franchise/edit', icon: 'settings' },
  ],
  advisor: [
    { label: 'Dashboard', url: '/dashboard', icon: 'home' },
    { label: 'My Profile', url: '/advisor/edit', icon: 'user' },
    { label: 'Connections', url: '/connections', icon: 'matches' },
    { label: 'Notifications', url: '/notifications', icon: 'bell' },
    { label: 'Settings', url: '/advisor/edit', icon: 'settings' },
  ],
};

const ADMIN_LINKS = [
  { label: 'Analytics', url: '/admin/analytics', icon: 'chart' },
  { label: 'Verification Queue', url: '/admin/verification', icon: 'document' },
  { label: 'Users', url: '/admin/users', icon: 'users' },
  { label: 'Pitches', url: '/admin/pitches', icon: 'tag' },
  { label: 'Reports', url: '/admin/reports', icon: 'lock' },
  { label: 'Broadcast', url: '/admin/broadcast', icon: 'share' },
  { label: 'Sectors', url: '/admin/sectors', icon: 'tag' },
  { label: 'FAQs', url: '/admin/faqs', icon: 'message' },
  { label: 'Blog', url: '/admin/blog', icon: 'document' },
  { label: 'Homepage', url: '/admin/homepage', icon: 'settings' },
];

function injectHeader(mode, actionsHtml) {
  const root = document.getElementById('header-root');
  if (!root) return;

  if (!actionsHtml) {
    actionsHtml = window.CURRENT_USER
      ? '<a href="/dashboard" class="header-user" aria-label="User — go to dashboard"><div class="avatar avatar-sm">U</div><span class="header-user-name">User</span></a>'
      : '<a href="/login" class="btn btn-sm btn-outline">Log in</a><a href="/onboarding" class="btn btn-sm btn-primary">Sign up</a>';
  }

  const user = window.CURRENT_USER;
  // Auth state, not page mode — so the public/home header also reflects login.
  const isLoggedIn = !!user;
  const unread = window.UNREAD_COUNT || 0;

  // Public marketing header uses the two-row mega-menu (separate markup).
  if (mode === 'public') {
    renderPublicHeader(root, user, isLoggedIn, unread);
    return;
  }

  const mobileLinks = mode === 'public' ? PUBLIC_LINKS
    : mode === 'admin' ? ADMIN_LINKS
    : (DASHBOARD_LINKS[user?.role] || DASHBOARD_LINKS.investor);

  const currentPath = window.location.pathname;

  function isActive(url) {
    if (url === '/') return currentPath === '/';
    return currentPath === url || currentPath.startsWith(url + '/') || currentPath.startsWith(url + '?');
  }

  let navHtml = '';
  if (mode === 'public') {
    PUBLIC_LINKS.forEach(link => {
      const active = isActive(link.url) ? ' active' : '';
      navHtml += `<a href="${link.url}" class="header-nav-link${active}"${active ? ' aria-current="page"' : ''} onclick="closeMobileMenu()">${ICONS[link.icon] || ''} ${link.label}</a>`;
    });
    if (!isLoggedIn) {
      navHtml += `<div class="mobile-nav-divider"></div>`;
      navHtml += `<div class="mobile-nav-actions">`;
      navHtml += `<a href="/login" class="btn btn-outline" onclick="closeMobileMenu()">Log in</a>`;
      navHtml += `<a href="/onboarding" class="btn btn-primary" onclick="closeMobileMenu()">Sign up</a>`;
      navHtml += `</div>`;
    } else {
      const initials = (user.name || 'U').charAt(0).toUpperCase();
      navHtml += `<div class="mobile-nav-divider"></div>`;
      navHtml += `<div class="mobile-user-info" style="padding:12px 16px;display:flex;align-items:center;gap:10px;border-bottom:1px solid var(--surface-container-high);">`;
      navHtml += `<div class="avatar avatar-sm">${initials}</div>`;
      navHtml += `<span style="font-weight:600;color:var(--ink);">${user.name || 'User'}</span>`;
      navHtml += `</div>`;
      navHtml += `<a href="/dashboard" class="header-nav-link" onclick="closeMobileMenu()">${ICONS.home || ''} Dashboard</a>`;
      navHtml += `<a href="/logout" onclick="closeMobileMenu()">${ICONS.logout} Log out</a>`;
    }
  } else if (mode === 'admin') {
    ADMIN_LINKS.forEach(link => {
      const active = isActive(link.url) ? ' active' : '';
      navHtml += `<a href="${link.url}" class="header-nav-link${active}"${active ? ' aria-current="page"' : ''} onclick="closeMobileMenu()">${ICONS[link.icon] || ''} ${link.label}</a>`;
    });
    navHtml += `<div class="mobile-nav-divider"></div>`;
    navHtml += `<a href="/logout" onclick="closeMobileMenu()">${ICONS.logout} Log out</a>`;
  } else {
    const links = DASHBOARD_LINKS[user?.role] || DASHBOARD_LINKS.investor;
    links.forEach(link => {
      const active = isActive(link.url) ? ' active' : '';
      navHtml += `<a href="${link.url}" class="header-nav-link${active}"${active ? ' aria-current="page"' : ''} onclick="closeMobileMenu()">${ICONS[link.icon] || ''} ${link.label}</a>`;
    });
    navHtml += `<div class="mobile-nav-divider"></div>`;
    navHtml += `<a href="/logout" onclick="closeMobileMenu()">${ICONS.logout} Log out</a>`;
  }

  // actionsHtml is pre-rendered by PHP and passed as a parameter

  root.innerHTML = `
    <header class="site-header">
      <div class="header-inner">
        <a href="/" class="header-logo">
          <img src="/logo.png" width="160" height="40" alt="Asaan Capital Ltd - Financial &amp; Investment Services">
        </a>
        <nav class="header-nav" id="header-nav">
          <div class="header-nav-header">
            <a href="/" class="header-logo-mobile">
              <img src="/logo.png" width="120" height="28" alt="Asaan Capital Ltd" style="height:28px;width:auto;">
            </a>
            <button class="header-nav-close" onclick="closeMobileMenu()" aria-label="Close menu">
              ${ICONS.close}
            </button>
          </div>
          <div class="header-nav-links">
            ${navHtml}
          </div>
        </nav>
        <div class="header-actions">
          ${actionsHtml}
          <button class="header-mobile-toggle" id="header-mobile-toggle" onclick="toggleMobileMenu()" aria-label="Toggle menu" aria-expanded="false" aria-controls="header-nav">
            ${ICONS.menu}
          </button>
        </div>
      </div>
      <div class="header-backdrop" id="header-backdrop" onclick="closeMobileMenu()"></div>
    </header>
  `;
}

function renderPublicHeader(root, user, isLoggedIn, unread) {
  const path = window.location.pathname;
  const isActive = (url) => url === '/' ? path === '/' : (path === url || path.startsWith(url + '/'));

  const mainNav = PUBLIC_NAV.map(l =>
    `<a href="${l.url}" class="pub-nav-link${isActive(l.url) ? ' active' : ''}"${isActive(l.url) ? ' aria-current="page"' : ''}>${l.label}</a>`
  ).join('');

  const companyDD = `
    <div class="pub-dd pub-dd-company">
      <button type="button" class="pub-nav-link pub-dd-toggle" onclick="pubToggleDD(this)" aria-haspopup="true" aria-expanded="false">Company ${CARET}</button>
      <div class="pub-dd-menu" role="menu">${COMPANY_LINKS.map(l => `<a href="${l.url}" role="menuitem">${l.label}</a>`).join('')}</div>
    </div>`;

  const addProfileDD = `
    <div class="pub-dd pub-dd-add">
      <button type="button" class="pub-add-btn pub-dd-toggle" onclick="pubToggleDD(this)" aria-haspopup="true" aria-expanded="false">${ICONS.plus}<span>Add Profile</span>${CARET}</button>
      <div class="pub-dd-menu pub-dd-menu-right" role="menu">${ADD_PROFILE_LINKS.map(l => `<a href="${l.url}" role="menuitem">${ICONS.plus}<span>${l.label}</span></a>`).join('')}</div>
    </div>`;

  let right = '';
  if (isLoggedIn && user) {
    const initials = (user.name || 'U').charAt(0).toUpperCase();
    const bellLabel = unread > 0 ? `Notifications (${unread} unread)` : 'Notifications';
    right = `
      <a href="/notifications" class="notification-bell" aria-label="${bellLabel}">
        ${ICONS.bell}
        <span class="notification-badge" aria-hidden="true"${unread > 0 ? '' : ' style="display:none;"'}>${unread > 9 ? '9+' : unread}</span>
      </a>
      <div class="pub-dd pub-dd-avatar">
        <button type="button" class="pub-avatar-toggle pub-dd-toggle" onclick="pubToggleDD(this)" aria-haspopup="true" aria-expanded="false" aria-label="Account menu">
          <div class="avatar avatar-sm">${initials}</div>
        </button>
        <div class="pub-dd-menu pub-dd-menu-right" role="menu">
          <div class="pub-dd-userinfo">
            <div class="pub-dd-username">${user.name || 'User'}</div>
            ${user.email ? `<div class="pub-dd-useremail">${user.email}</div>` : ''}
          </div>
          <a href="/dashboard" role="menuitem">${ICONS.home}<span>Dashboard</span></a>
          <a href="/my-saved" role="menuitem">${ICONS.heart}<span>Bookmarks</span></a>
          <a href="/notifications" role="menuitem">${ICONS.bell}<span>Notifications</span></a>
          <a href="/notifications/settings" role="menuitem">${ICONS.settings}<span>Settings</span></a>
          <a href="/logout" role="menuitem">${ICONS.logout}<span>Log out</span></a>
        </div>
      </div>`;
  } else {
    right = `
      <a href="/login" class="btn btn-sm btn-outline pub-auth-btn">Log in</a>` +
      (window.location.pathname === '/onboarding' ? '' : `
      <a href="/onboarding" class="btn btn-sm btn-primary pub-auth-btn">Sign up</a>`);
  }

  const searchForm = `
    <form class="pub-search" action="/search" method="get" role="search">
      <select name="type" class="pub-search-type" aria-label="Search category">
        <option value="businesses">Businesses</option>
        <option value="investors">Investors</option>
      </select>
      <input type="text" name="q" class="pub-search-input" placeholder="Try: Businesses for sale in Kathmandu" aria-label="Search">
      <button type="submit" class="pub-search-btn" aria-label="Search">${ICONS.search}</button>
    </form>`;

  // Mobile drawer
  let drawer = '';
  drawer += PUBLIC_NAV.map(l => `<a href="${l.url}" class="${isActive(l.url) ? 'active' : ''}" onclick="closeMobileMenu()">${l.label}</a>`).join('');
  drawer += `<div class="mobile-nav-divider"></div><div class="pub-drawer-label">Company</div>`;
  drawer += COMPANY_LINKS.map(l => `<a href="${l.url}" onclick="closeMobileMenu()">${l.label}</a>`).join('');
  drawer += `<div class="mobile-nav-divider"></div><div class="pub-drawer-label">Add a Profile</div>`;
  drawer += ADD_PROFILE_LINKS.map(l => `<a href="${l.url}" onclick="closeMobileMenu()">${ICONS.plus} ${l.label}</a>`).join('');
  drawer += `<div class="mobile-nav-divider"></div>`;
  if (isLoggedIn && user) {
    drawer += `<a href="/dashboard" onclick="closeMobileMenu()">${ICONS.home} Dashboard</a>`;
    drawer += `<a href="/my-saved" onclick="closeMobileMenu()">${ICONS.heart} Bookmarks</a>`;
    drawer += `<a href="/notifications" onclick="closeMobileMenu()">${ICONS.bell} Notifications</a>`;
    drawer += `<a href="/notifications/settings" onclick="closeMobileMenu()">${ICONS.settings} Settings</a>`;
    drawer += `<a href="/logout" onclick="closeMobileMenu()">${ICONS.logout} Log out</a>`;
  } else {
    drawer += `<div class="mobile-nav-actions"><a href="/login" class="btn btn-outline" onclick="closeMobileMenu()">Log in</a>${window.location.pathname === '/onboarding' ? '' : `<a href="/onboarding" class="btn btn-primary" onclick="closeMobileMenu()">Sign up</a>`}</div>`;
  }

  root.innerHTML = `
    <header class="site-header pub-header">
      <div class="pub-topbar">
        <div class="pub-topbar-inner">
          <a href="/" class="header-logo"><img src="/logo.png" width="160" height="40" alt="Asaan Capital Ltd"></a>
          ${searchForm}
          <div class="pub-topbar-actions">
            ${addProfileDD}
            ${right}
            <button class="header-mobile-toggle" id="header-mobile-toggle" onclick="toggleMobileMenu()" aria-label="Toggle menu" aria-expanded="false" aria-controls="header-nav">${ICONS.menu}</button>
          </div>
        </div>
      </div>
      <nav class="pub-mainnav">
        <div class="pub-mainnav-inner">
          ${mainNav}
          ${companyDD}
        </div>
      </nav>
      <nav class="header-nav" id="header-nav">
        <div class="header-nav-header">
          <a href="/" class="header-logo-mobile"><img src="/logo.png" width="120" height="28" alt="Asaan Capital Ltd" style="height:28px;width:auto;"></a>
          <button class="header-nav-close" onclick="closeMobileMenu()" aria-label="Close menu">${ICONS.close}</button>
        </div>
        <div class="header-nav-links">
          <div class="pub-drawer-search">${searchForm}</div>
          ${drawer}
        </div>
      </nav>
      <div class="header-backdrop" id="header-backdrop" onclick="closeMobileMenu()"></div>
    </header>`;
}

function pubCloseAllDD() {
  document.querySelectorAll('.pub-dd.open').forEach(d => {
    d.classList.remove('open');
    const t = d.querySelector('.pub-dd-toggle');
    if (t) t.setAttribute('aria-expanded', 'false');
  });
}

function pubToggleDD(btn) {
  const dd = btn.closest('.pub-dd');
  if (!dd) return;
  const isOpen = dd.classList.contains('open');
  pubCloseAllDD();
  if (!isOpen) {
    dd.classList.add('open');
    btn.setAttribute('aria-expanded', 'true');
  }
}

document.addEventListener('click', (e) => {
  if (!e.target.closest('.pub-dd')) pubCloseAllDD();
});
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') pubCloseAllDD();
});

let lastFocusedBeforeMenu = null;

function toggleMobileMenu() {
  const nav = document.getElementById('header-nav');
  if (nav?.classList.contains('open')) {
    closeMobileMenu();
  } else {
    openMobileMenu();
  }
}

function openMobileMenu() {
  const nav = document.getElementById('header-nav');
  const backdrop = document.getElementById('header-backdrop');
  const toggle = document.getElementById('header-mobile-toggle');
  lastFocusedBeforeMenu = document.activeElement;
  if (nav) nav.classList.add('open');
  if (backdrop) backdrop.classList.add('open');
  document.body.classList.add('menu-open');
  if (toggle) toggle.setAttribute('aria-expanded', 'true');
  // Move focus into the drawer for keyboard/screen-reader users
  nav?.querySelector('.header-nav-close')?.focus();
  document.addEventListener('keydown', handleMenuKeydown);
}

function closeMobileMenu() {
  const nav = document.getElementById('header-nav');
  const backdrop = document.getElementById('header-backdrop');
  const toggle = document.getElementById('header-mobile-toggle');
  const wasOpen = nav?.classList.contains('open');
  if (nav) nav.classList.remove('open');
  if (backdrop) backdrop.classList.remove('open');
  document.body.classList.remove('menu-open');
  if (toggle) toggle.setAttribute('aria-expanded', 'false');
  document.removeEventListener('keydown', handleMenuKeydown);
  // Restore focus only if the drawer was actually open (avoids stealing focus on desktop link clicks)
  if (wasOpen && lastFocusedBeforeMenu) lastFocusedBeforeMenu.focus();
  lastFocusedBeforeMenu = null;
}

function handleMenuKeydown(e) {
  const nav = document.getElementById('header-nav');
  if (!nav || !nav.classList.contains('open')) return;

  if (e.key === 'Escape') {
    closeMobileMenu();
    return;
  }
  if (e.key !== 'Tab') return;

  const focusable = nav.querySelectorAll('a[href], button:not([disabled])');
  if (!focusable.length) return;
  const first = focusable[0];
  const last = focusable[focusable.length - 1];

  if (e.shiftKey && document.activeElement === first) {
    e.preventDefault();
    last.focus();
  } else if (!e.shiftKey && document.activeElement === last) {
    e.preventDefault();
    first.focus();
  }
}

function injectSidebar(role) {
  const root = document.getElementById('sidebar-root');
  if (!root) return;

  // Mark the dashboard shell so the header can drop its redundant nav links
  // (the sidebar is the primary navigation here).
  document.body.classList.add('has-dashboard-sidebar');

  const links = role === 'admin' ? ADMIN_LINKS : (DASHBOARD_LINKS[role] || DASHBOARD_LINKS.investor);
  const currentPath = window.location.pathname;
  const user = window.CURRENT_USER;
  const unread = window.UNREAD_COUNT || 0;

  let html = `<button class="sidebar-mobile-toggle" onclick="toggleMobileSidebar()" aria-label="Toggle sidebar">
    ${ICONS.menu} Menu
  </button>`;
  html += `<div class="sidebar" id="sidebar">`;
  html += `<div class="sidebar-header-mobile">
    <strong>Navigation</strong>
    <button class="sidebar-close-btn" onclick="toggleMobileSidebar()" aria-label="Close sidebar">${ICONS.close}</button>
  </div>`;

  if (user) {
    const initials = (user.name || 'U').charAt(0).toUpperCase();
    const roleLabels = { investor: 'Investor', business_owner: 'Business Owner', entrepreneur: 'Entrepreneur', franchisor: 'Franchisor', advisor: 'Advisor' };
    html += `<div class="sidebar-user">
      <div style="display:flex;align-items:center;gap:10px;">
        <div class="avatar avatar-sm">${initials}</div>
        <div>
          <div class="sidebar-user-name">${user.name || 'User'}</div>
          <div class="sidebar-user-role">${roleLabels[user.role] || user.role || ''}</div>
        </div>
      </div>
    </div>`;
  }

  html += `<nav class="sidebar-nav">`;
  links.forEach(link => {
    const active = currentPath === link.url || currentPath.startsWith(link.url + '/') ? ' active' : '';
    const badge = (link.label === 'Notifications' && unread > 0) ? `<span class="sidebar-notif-badge">${unread > 9 ? '9+' : unread}</span>` : '';
    html += `<a href="${link.url}" class="sidebar-nav-item${active}">${ICONS[link.icon] || ''} ${link.label}${badge}</a>`;
  });
  html += `</nav>`;

  html += `<div class="sidebar-footer">
    <a href="/logout" class="sidebar-nav-item" onclick="closeMobileSidebar()">${ICONS.logout} Log out</a>
  </div>`;
  html += `</div>`;
  html += `<div class="sidebar-backdrop" id="sidebar-backdrop" onclick="closeMobileSidebar()"></div>`;
  root.innerHTML = html;
}

function toggleMobileSidebar() {
  const sidebar = document.getElementById('sidebar');
  const backdrop = document.getElementById('sidebar-backdrop');
  const isOpen = sidebar?.classList.contains('open');

  if (isOpen) {
    closeMobileSidebar();
  } else {
    if (sidebar) sidebar.classList.add('open');
    if (backdrop) backdrop.classList.add('open');
    document.body.classList.add('menu-open');
  }
}

function closeMobileSidebar() {
  const sidebar = document.getElementById('sidebar');
  const backdrop = document.getElementById('sidebar-backdrop');
  if (sidebar) sidebar.classList.remove('open');
  if (backdrop) backdrop.classList.remove('open');
  document.body.classList.remove('menu-open');
}

function initNotificationPoller() {
  if (!window.CURRENT_USER) return;
  setInterval(() => {
    fetch('/api/notifications-unread')
      .then(r => r.json())
      .then(data => {
        const badge = document.querySelector('.notification-badge');
        const bell = document.querySelector('.notification-bell');
        const count = data.count || 0;
        if (badge) {
          badge.textContent = count > 9 ? '9+' : count;
          badge.style.display = count > 0 ? 'flex' : 'none';
        }
        if (bell) {
          bell.setAttribute('aria-label', count > 0 ? `Notifications (${count} unread)` : 'Notifications');
        }
      })
      .catch(() => {});
  }, 30000);
}

document.addEventListener('DOMContentLoaded', () => {
  initNotificationPoller();
});
