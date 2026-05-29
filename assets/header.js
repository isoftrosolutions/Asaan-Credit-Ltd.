const PUBLIC_LINKS = [
  { label: 'Browse', url: '/browse/businesses', icon: 'search' },
  { label: 'How It Works', url: '/how-it-works', icon: 'document' },
  { label: 'Valuation', url: '/business-valuation', icon: 'chart' },
  { label: 'Support', url: '/support', icon: 'message' },
];

const DASHBOARD_LINKS = {
  investor: [
    { label: 'Dashboard', url: '/dashboard', icon: 'home' },
    { label: 'Discover', url: '/browse/businesses', icon: 'search' },
    { label: 'My Connections', url: '/connections', icon: 'matches' },
    { label: 'Notifications', url: '/notifications', icon: 'bell' },
    { label: 'My Profile', url: '/investor/profile-create', icon: 'user' },
    { label: 'Preferences', url: '/investor/preferences-edit', icon: 'settings' },
    { label: 'Documents', url: '/investor/documents-edit', icon: 'document' },
  ],
  owner: [
    { label: 'Dashboard', url: '/dashboard', icon: 'home' },
    { label: 'My Listing', url: '/business/create', icon: 'briefcase' },
    { label: 'Connections', url: '/connections', icon: 'matches' },
    { label: 'Notifications', url: '/notifications', icon: 'bell' },
    { label: 'Settings', url: '/business/edit', icon: 'settings' },
  ],
  entrepreneur: [
    { label: 'Dashboard', url: '/dashboard', icon: 'home' },
    { label: 'My Pitch', url: '/entrepreneur/pitch-create', icon: 'chart' },
    { label: 'Connections', url: '/connections', icon: 'matches' },
    { label: 'Notifications', url: '/notifications', icon: 'bell' },
    { label: 'Settings', url: '/entrepreneur/pitch-edit', icon: 'settings' },
  ],
  franchisor: [
    { label: 'Dashboard', url: '/dashboard', icon: 'home' },
    { label: 'My Franchise', url: '/franchise/create', icon: 'briefcase' },
    { label: 'Connections', url: '/connections', icon: 'matches' },
    { label: 'Notifications', url: '/notifications', icon: 'bell' },
    { label: 'Settings', url: '/franchise/edit', icon: 'settings' },
  ],
  advisor: [
    { label: 'Dashboard', url: '/dashboard', icon: 'home' },
    { label: 'My Profile', url: '/advisor/create', icon: 'user' },
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
  { label: 'Homepage', url: '/admin/homepage', icon: 'settings' },
];

function injectHeader(mode) {
  const root = document.getElementById('header-root');
  if (!root) return;

  const isLoggedIn = mode === 'dashboard' || mode === 'admin';
  const user = window.CURRENT_USER;
  const unread = window.UNREAD_COUNT || 0;

  const mobileLinks = mode === 'public' ? PUBLIC_LINKS
    : mode === 'admin' ? ADMIN_LINKS
    : (DASHBOARD_LINKS[user?.role] || DASHBOARD_LINKS.investor);

  let navHtml = '';
  if (mode === 'public') {
    PUBLIC_LINKS.forEach(link => {
      navHtml += `<a href="${link.url}" onclick="closeMobileMenu()">${ICONS[link.icon] || ''} ${link.label}</a>`;
    });
    if (!isLoggedIn) {
      navHtml += `<div class="mobile-nav-divider"></div>`;
      navHtml += `<div class="mobile-nav-actions">`;
      navHtml += `<a href="/login" class="btn btn-outline" onclick="closeMobileMenu()">Log in</a>`;
      navHtml += `<a href="/signup" class="btn btn-primary" onclick="closeMobileMenu()">Sign up</a>`;
      navHtml += `</div>`;
    }
  } else if (mode === 'admin') {
    ADMIN_LINKS.forEach(link => {
      navHtml += `<a href="${link.url}" onclick="closeMobileMenu()">${ICONS[link.icon] || ''} ${link.label}</a>`;
    });
    navHtml += `<div class="mobile-nav-divider"></div>`;
    navHtml += `<a href="/logout" onclick="closeMobileMenu()">${ICONS.logout} Log out</a>`;
  } else {
    const links = DASHBOARD_LINKS[user?.role] || DASHBOARD_LINKS.investor;
    links.forEach(link => {
      navHtml += `<a href="${link.url}" onclick="closeMobileMenu()">${ICONS[link.icon] || ''} ${link.label}</a>`;
    });
    navHtml += `<div class="mobile-nav-divider"></div>`;
    navHtml += `<a href="/logout" onclick="closeMobileMenu()">${ICONS.logout} Log out</a>`;
  }

  let actionsHtml = '';
  if (isLoggedIn && user) {
    const initials = (user.name || 'U').charAt(0).toUpperCase();
    actionsHtml = `
      <span class="notification-bell" onclick="location.href='/notifications'">
        ${ICONS.bell}
        ${unread > 0 ? `<span class="notification-badge">${unread > 9 ? '9+' : unread}</span>` : ''}
      </span>
      <div class="header-user" onclick="location.href='/dashboard'">
        <div class="avatar avatar-sm">${initials}</div>
        <span class="header-user-name">${user.name || 'User'}</span>
      </div>
    `;
  } else {
    actionsHtml = `
      <a href="/login" class="btn btn-sm btn-outline">Log in</a>
      <a href="/signup" class="btn btn-sm btn-primary">Sign up</a>
    `;
  }

  root.innerHTML = `
    <header class="site-header">
      <div class="header-inner">
        <a href="/" class="header-logo">
          <img src="/logo.png" alt="Asaan">
        </a>
        <nav class="header-nav" id="header-nav">
          ${navHtml}
        </nav>
        <div class="header-actions">
          ${actionsHtml}
          <button class="header-mobile-toggle" id="header-mobile-toggle" onclick="toggleMobileMenu()" aria-label="Toggle menu">
            ${ICONS.menu}
          </button>
        </div>
      </div>
      <div class="header-backdrop" id="header-backdrop" onclick="closeMobileMenu()"></div>
    </header>
  `;
}

function toggleMobileMenu() {
  const nav = document.getElementById('header-nav');
  const backdrop = document.getElementById('header-backdrop');
  const isOpen = nav?.classList.contains('open');

  if (isOpen) {
    closeMobileMenu();
  } else {
    if (nav) nav.classList.add('open');
    if (backdrop) backdrop.classList.add('open');
    document.body.classList.add('menu-open');
  }
}

function closeMobileMenu() {
  const nav = document.getElementById('header-nav');
  const backdrop = document.getElementById('header-backdrop');
  if (nav) nav.classList.remove('open');
  if (backdrop) backdrop.classList.remove('open');
  document.body.classList.remove('menu-open');
}

function injectSidebar(role) {
  const root = document.getElementById('sidebar-root');
  if (!root) return;

  const links = role === 'admin' ? ADMIN_LINKS : (DASHBOARD_LINKS[role] || DASHBOARD_LINKS.investor);
  const currentPath = window.location.pathname;

  let html = `<button class="sidebar-mobile-toggle" onclick="toggleMobileSidebar()" aria-label="Toggle sidebar">
    ${ICONS.menu} Menu
  </button>`;
  html += `<div class="sidebar" id="sidebar">`;
  html += `<div class="sidebar-header-mobile">
    <strong>Navigation</strong>
    <button class="sidebar-close-btn" onclick="toggleMobileSidebar()" aria-label="Close sidebar">${ICONS.close}</button>
  </div>`;
  html += `<nav class="sidebar-nav">`;
  links.forEach(link => {
    const active = currentPath === link.url || currentPath.startsWith(link.url + '/') ? ' active' : '';
    html += `<a href="${link.url}" class="sidebar-nav-item${active}">${ICONS[link.icon] || ''} ${link.label}</a>`;
  });
  html += `</nav>`;
  html += `<div style="margin-top:auto;padding-top:1rem;border-top:1px solid var(--surface-container-high);margin-top:1rem;">
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
        const count = data.count || 0;
        if (badge) {
          badge.textContent = count > 9 ? '9+' : count;
          badge.style.display = count > 0 ? 'flex' : 'none';
        }
      })
      .catch(() => {});
  }, 30000);
}

document.addEventListener('DOMContentLoaded', () => {
  initNotificationPoller();
});
