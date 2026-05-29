const PUBLIC_LINKS = [
  { label: 'Browse', url: '/browse/businesses' },
  { label: 'How It Works', url: '/how-it-works' },
  { label: 'Valuation', url: '/business-valuation' },
  { label: 'Support', url: '/support' },
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

  let navHtml = '';
  if (mode === 'public') {
    PUBLIC_LINKS.forEach(link => {
      navHtml += `<a href="${link.url}">${link.label}</a>`;
    });
  } else if (mode === 'admin') {
    navHtml = `<a href="/admin">Admin Panel</a>`;
  } else {
    navHtml = `<a href="/dashboard">Dashboard</a>`;
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
          <img src="/assan/logo.png" alt="Asaan" style="height:32px;width:auto;border-radius:4px;max-width:140px;object-fit:contain;">
        </a>
        <div class="header-nav" id="header-nav">
          ${navHtml}
        </div>
        <div class="header-actions">
          ${actionsHtml}
          <button class="header-mobile-toggle" onclick="toggleMobileMenu()" aria-label="Toggle menu">
            ${ICONS.menu}
          </button>
        </div>
      </div>
    </header>
  `;
}

function toggleMobileMenu() {
  const nav = document.getElementById('header-nav');
  if (nav) nav.classList.toggle('open');
}

function injectSidebar(role) {
  const root = document.getElementById('sidebar-root');
  if (!root) return;

  const links = role === 'admin' ? ADMIN_LINKS : (DASHBOARD_LINKS[role] || DASHBOARD_LINKS.investor);
  const currentPath = window.location.pathname;

  let html = `<div class="sidebar">`;
  html += `<nav class="sidebar-nav">`;
  links.forEach(link => {
    const active = currentPath === link.url || currentPath.startsWith(link.url + '/') ? ' active' : '';
    html += `<a href="${link.url}" class="sidebar-nav-item${active}">${ICONS[link.icon] || ''} ${link.label}</a>`;
  });
  html += `</nav>`;
  html += `<div style="margin-top:auto;padding-top:1rem;border-top:1px solid var(--surface-container-high);margin-top:1rem;">
    <a href="/logout" class="sidebar-nav-item">${ICONS.logout} Log out</a>
  </div>`;
  html += `</div>`;
  root.innerHTML = html;
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
