function injectFooter() {
  const root = document.getElementById('footer-root');
  if (!root) return;

  const year = new Date().getFullYear();
  root.innerHTML = `
    <footer style="background:#0f0f0f;color:rgba(255,255,255,0.55);padding:56px 24px 32px;margin-top:80px;">
      <div style="max-width:1200px;margin:0 auto;">
        <div style="display:grid;grid-template-columns:1.6fr 1fr 1fr;gap:48px;margin-bottom:40px;">
          <div>
            <h4 style="color:#fff;margin:0 0 12px;font-size:15px;font-weight:600;letter-spacing:-0.01em;">Asaan Capital Ltd</h4>
            <p style="font-size:13px;line-height:1.7;margin:0 0 20px;color:rgba(255,255,255,0.45);">
              Nepal's marketplace connecting verified business owners with investors, buyers, and advisors.
            </p>
            <div style="display:flex;gap:10px;">
              <a href="https://facebook.com/asaancapital" target="_blank" rel="noopener" aria-label="Facebook" style="color:rgba(255,255,255,0.4);transition:color 160ms ease-out;">${'<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.69 4.53-4.69 1.31 0 2.68.24 2.68.24v2.97h-1.51c-1.49 0-1.96.93-1.96 1.89v2.25h3.33l-.53 3.49h-2.8V24C19.61 23.1 24 18.1 24 12.07z"/></svg>'}</a>
              <a href="https://instagram.com/asaancapital" target="_blank" rel="noopener" aria-label="Instagram" style="color:rgba(255,255,255,0.4);transition:color 160ms ease-out;">${'<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.16c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.41a3.72 3.72 0 01-1.38-.9 3.72 3.72 0 01-.9-1.38c-.16-.42-.36-1.06-.41-2.23C2.17 15.58 2.16 15.2 2.16 12s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.43-.16 1.06-.36 2.23-.41C8.42 2.17 8.8 2.16 12 2.16M12 0C8.74 0 8.33.01 7.05.07 5.78.13 4.9.33 4.14.63c-.79.31-1.46.72-2.13 1.38C1.35 2.68.94 3.35.63 4.14.33 4.9.13 5.78.07 7.05.01 8.33 0 8.74 0 12s.01 3.67.07 4.95c.06 1.27.26 2.15.56 2.91.31.79.72 1.46 1.38 2.13.67.66 1.34 1.07 2.13 1.38.76.3 1.64.5 2.91.56C8.33 23.99 8.74 24 12 24s3.67-.01 4.95-.07c1.27-.06 2.15-.26 2.91-.56.79-.31 1.46-.72 2.13-1.38.66-.67 1.07-1.34 1.38-2.13.3-.76.5-1.64.56-2.91.06-1.28.07-1.69.07-4.95s-.01-3.67-.07-4.95c-.06-1.27-.26-2.15-.56-2.91a5.9 5.9 0 00-1.38-2.13A5.9 5.9 0 0019.86.63c-.76-.3-1.64-.5-2.91-.56C15.67.01 15.26 0 12 0zm0 5.84A6.16 6.16 0 1018.16 12 6.16 6.16 0 0012 5.84z"/></svg>'}</a>
              <a href="https://x.com/asaancapital" target="_blank" rel="noopener" aria-label="X" style="color:rgba(255,255,255,0.4);transition:color 160ms ease-out;">${'<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18.9 1.15h3.68l-8.04 9.19L24 22.85h-7.41l-5.8-7.58-6.64 7.58H.46l8.6-9.83L0 1.15h7.6l5.24 6.93zM17.61 20.64h2.04L6.49 3.24H4.3z"/></svg>'}</a>
              <a href="https://linkedin.com/company/asaancapital" target="_blank" rel="noopener" aria-label="LinkedIn" style="color:rgba(255,255,255,0.4);transition:color 160ms ease-out;">${'<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M20.45 20.45h-3.56v-5.57c0-1.33-.02-3.04-1.85-3.04-1.85 0-2.13 1.45-2.13 2.94v5.67H9.35V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46zM5.34 7.43a2.07 2.07 0 110-4.14 2.07 2.07 0 010 4.14zM7.12 20.45H3.56V9h3.56zM22.22 0H1.77C.79 0 0 .77 0 1.73v20.54C0 23.23.79 24 1.77 24h20.45c.98 0 1.78-.77 1.78-1.73V1.73C24 .77 23.2 0 22.22 0z"/></svg>'}</a>
            </div>
          </div>
          <div>
            <h4 style="color:#fff;margin:0 0 12px;font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Explore</h4>
            <div style="display:flex;flex-direction:column;gap:6px;font-size:13px;">
              <a href="/browse/businesses" style="color:rgba(255,255,255,0.55);text-decoration:none;transition:color 160ms ease-out;">Browse Businesses</a>
              <a href="/about" style="color:rgba(255,255,255,0.55);text-decoration:none;transition:color 160ms ease-out;">About Us</a>
              <a href="/blog" style="color:rgba(255,255,255,0.55);text-decoration:none;transition:color 160ms ease-out;">Blog</a>
              <a href="/support" style="color:rgba(255,255,255,0.55);text-decoration:none;transition:color 160ms ease-out;">FAQs</a>
            </div>
          </div>
          <div>
            <h4 style="color:#fff;margin:0 0 12px;font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Contact</h4>
            <div style="display:flex;flex-direction:column;gap:6px;font-size:13px;">
              <span>Madhyapur Thimi-9, Bhaktapur</span>
              <a href="tel:+9779848714990" style="color:rgba(255,255,255,0.55);text-decoration:none;transition:color 160ms ease-out;">+977-9848714990</a>
              <a href="mailto:info@asaancapital.com" style="color:rgba(255,255,255,0.55);text-decoration:none;transition:color 160ms ease-out;">info@asaancapital.com</a>
            </div>
          </div>
        </div>
        <div style="border-top:1px solid rgba(255,255,255,0.08);padding-top:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
          <p style="margin:0;font-size:12px;color:rgba(255,255,255,0.35);">&copy; ${year} Asaan Capital Ltd. All rights reserved.</p>
          <div style="display:flex;gap:16px;font-size:12px;">
            <a href="/legal" style="color:rgba(255,255,255,0.35);text-decoration:none;transition:color 160ms ease-out;">Privacy</a>
            <a href="/legal" style="color:rgba(255,255,255,0.35);text-decoration:none;transition:color 160ms ease-out;">Terms</a>
          </div>
        </div>
      </div>
    </footer>
  `;
}

function showInterestModal(businessId, businessName) {
  const overlay = document.createElement('div');
  overlay.className = 'modal-overlay';
  overlay.onclick = (e) => { if (e.target === overlay) overlay.remove(); };
  overlay.innerHTML = `
    <div class="modal-content">
      <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">&times;</button>
      <h3 style="margin-bottom:0.5rem;">Express Interest</h3>
      <p style="color:var(--secondary-text);font-size:0.9rem;margin-bottom:1.5rem;">
        Send an interest request to <strong>${businessName || 'this listing'}</strong>
      </p>
      <form id="interest-form" action="/connections/send-interest" method="POST">
        <input type="hidden" name="_csrf" value="${CSRF_TOKEN}">
        <input type="hidden" name="listing_type" value="business">
        <input type="hidden" name="listing_id" value="${businessId || ''}">
        <div class="input-group">
          <label>Your Message</label>
          <textarea name="message" rows="4" placeholder="Introduce yourself and explain your interest..." required></textarea>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;">Send Interest Request</button>
      </form>
    </div>
  `;
  document.body.appendChild(overlay);
}

function showToast(message, type) {
  const colors = { success: 'var(--color-success)', error: 'var(--color-error)', info: 'var(--color-secondary)', warning: 'var(--color-warning)' };
  const toast = document.createElement('div');
  toast.style.cssText = `position:fixed;bottom:24px;right:24px;background:${colors[type] || colors.info};color:#fff;padding:14px 24px;border-radius:12px;font-weight:600;font-size:0.9rem;z-index:9999;box-shadow:0 8px 24px rgba(0,0,0,0.15);max-width:400px;animation:fadeUp 0.3s ease;`;
  toast.textContent = message;
  document.body.appendChild(toast);
  setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.3s'; setTimeout(() => toast.remove(), 300); }, 4000);
}

function confirmAction(message, callback) {
  const overlay = document.createElement('div');
  overlay.className = 'modal-overlay';
  overlay.onclick = (e) => { if (e.target === overlay) overlay.remove(); };
  overlay.innerHTML = `
    <div class="modal-content" style="max-width:400px;text-align:center;">
      <p style="margin-bottom:1.5rem;font-size:1rem;">${message}</p>
      <div style="display:flex;gap:0.75rem;justify-content:center;">
        <button class="btn btn-outline" onclick="this.closest('.modal-overlay').remove()">Cancel</button>
        <button class="btn btn-primary" id="confirm-btn">Confirm</button>
      </div>
    </div>
  `;
  document.body.appendChild(overlay);
  document.getElementById('confirm-btn').onclick = () => {
    overlay.remove();
    callback();
  };
}

/* ===========================================================================
 * Heart / Save Listing — site-wide
 * ========================================================================= */

/** Toggle save state for any listing. Shows toast, handles errors. */
function toggleSave(listingType, listingId, btn) {
  if (!CURRENT_USER) {
    showToast('Please log in to save listings', 'warning');
    setTimeout(function () { window.location.href = (window.APP_URL || '') + '/login'; }, 1200);
    return;
  }
  if (btn.disabled) return;
  btn.disabled = true;

  var params = 'listing_type=' + encodeURIComponent(listingType)
             + '&listing_id=' + encodeURIComponent(listingId)
             + '&_csrf=' + encodeURIComponent(CSRF_TOKEN);

  fetch((window.APP_URL || '') + '/api/toggle-save.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: params
  }).then(function (r) {
    if (!r.ok) throw new Error('Server error');
    return r.json();
  }).then(function (d) {
    btn.classList.toggle('saved', d.saved);
    /* Update label span if present (detail-page button style) */
    var label = btn.querySelector('span');
    if (label) label.textContent = d.saved ? 'Saved' : 'Save';
    if (d.saved) {
      showToast('Saved to your list', 'success');
    } else {
      showToast('Removed from saved', 'info');
    }
    loadSavedCount();
  }).catch(function () {
    showToast('Failed to save. Try again.', 'error');
  }).finally(function () {
    btn.disabled = false;
  });
}

/** Fetch saved count and update all .saved-count badges. */
function loadSavedCount() {
  var badges = document.querySelectorAll('.saved-count');
  if (!badges.length) return;
  if (!CURRENT_USER) { badges.forEach(function (b) { b.textContent = '0'; b.style.display = 'none'; }); return; }

  fetch((window.APP_URL || '') + '/api/get-saved.php?count=1&_=' + Date.now())
    .then(function (r) { return r.ok ? r.json() : null; })
    .then(function (d) {
      var c = (d && typeof d.count === 'number') ? d.count : 0;
      badges.forEach(function (b) {
        b.textContent = c > 9 ? '9+' : c;
        b.style.display = c > 0 ? '' : 'none';
      });
    }).catch(function () {});
}

/** Open the saved-listings modal (fetches data from API). */
function openSavedModal() {
  if (!CURRENT_USER) {
    showToast('Please log in to view saved listings', 'warning');
    setTimeout(function () { window.location.href = (window.APP_URL || '') + '/login'; }, 1200);
    return;
  }

  var existing = document.getElementById('saved-modal');
  if (existing) { existing.classList.add('open'); return; }

  var overlay = document.createElement('div');
  overlay.className = 'modal-overlay saved-modal-overlay';
  overlay.id = 'saved-modal';
  overlay.onclick = function (e) { if (e.target === overlay) closeSavedModal(); };

  overlay.innerHTML =
    '<div class="modal-content saved-modal-content">' +
      '<div class="saved-modal-head">' +
        '<h3>Saved Listings</h3>' +
        '<button class="modal-close" onclick="closeSavedModal()" aria-label="Close">&times;</button>' +
      '</div>' +
      '<div class="saved-modal-body" id="saved-modal-body">' +
        '<div class="saved-loading">' +
          '<div class="spinner"></div>' +
          '<p>Loading saved listings&hellip;</p>' +
        '</div>' +
      '</div>' +
    '</div>';

  document.body.appendChild(overlay);

  /* slight delay for CSS transition */
  requestAnimationFrame(function () { overlay.classList.add('open'); });

  fetch((window.APP_URL || '') + '/api/get-saved.php?_=' + Date.now())
    .then(function (r) {
      if (!r.ok) throw new Error('Failed to fetch');
      return r.json();
    })
    .then(function (data) {
      renderSavedItems(data.items || [], data.count || 0);
    })
    .catch(function () {
      document.getElementById('saved-modal-body').innerHTML =
        '<div class="saved-empty">' +
          '<p>Could not load saved listings. Please try again.</p>' +
          '<button class="btn btn-sm btn-primary" onclick="closeSavedModal()">Close</button>' +
        '</div>';
    });
}

function closeSavedModal() {
  var m = document.getElementById('saved-modal');
  if (m) { m.classList.remove('open'); setTimeout(function () { m.remove(); }, 250); }
}

function renderSavedItems(items, count) {
  var body = document.getElementById('saved-modal-body');
  if (!body) return;

  if (!items || !items.length) {
    body.innerHTML =
      '<div class="saved-empty">' +
        '<span class="saved-empty-icon">&hearts;</span>' +
        '<p>No saved listings yet.</p>' +
        '<p class="saved-empty-sub">Click the <i class="fas fa-heart"></i> heart icon on any listing to save it here.</p>' +
      '</div>';
    return;
  }

  var html = '';
  for (var i = 0; i < items.length; i++) {
    var item = items[i];
    var title = item.title || 'Untitled';
    var info = item.info || '';
    var url = item.url || '#';
    var label = item.type_label || item.type;
    html +=
      '<a href="' + url + '" class="saved-item" onclick="closeSavedModal()">' +
        '<div class="saved-item-body">' +
          '<span class="saved-item-type">' + label + '</span>' +
          '<div class="saved-item-title">' + title + '</div>' +
          (info ? '<div class="saved-item-info">' + info + '</div>' : '') +
        '</div>' +
        '<span class="saved-item-date">' + (item.since || '') + '</span>' +
      '</a>';
  }
  body.innerHTML = html;
}

/* Keyboard shortcut: Escape closes saved modal */
document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') closeSavedModal();
});

/* ---- Existing password UX code below ---- */

/* ---------------------------------------------------------------------------
 * Password UX: show/hide toggle on every password field + live match check.
 * Progressive enhancement — runs on load, needs no template changes.
 * ------------------------------------------------------------------------- */
function initPasswordToggles() {
  const eyeOpen = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>';
  const eyeOff  = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';

  document.querySelectorAll('input[type="password"]').forEach(function (input) {
    if (input.dataset.pwToggle) return;
    input.dataset.pwToggle = '1';

    const wrap = document.createElement('div');
    wrap.className = 'pw-wrap';
    input.parentNode.insertBefore(wrap, input);
    wrap.appendChild(input);

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'pw-toggle';
    btn.setAttribute('aria-label', 'Show password');
    btn.innerHTML = eyeOff;
    wrap.appendChild(btn);

    btn.addEventListener('click', function () {
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.innerHTML = show ? eyeOpen : eyeOff;
      btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });
  });
}

function initPasswordMatch() {
  document.querySelectorAll('form').forEach(function (form) {
    const pw = form.querySelector('input[name="password"]');
    const confirm = form.querySelector('input[name="confirm_password"]');
    if (!pw || !confirm) return;
    const hint = form.querySelector('.pw-match-hint');

    function check() {
      if (confirm.value === '') {
        confirm.setCustomValidity('');
        if (hint) { hint.textContent = ''; }
        return;
      }
      if (pw.value !== confirm.value) {
        confirm.setCustomValidity('Passwords do not match');
        if (hint) { hint.textContent = 'Passwords do not match'; hint.style.color = 'var(--brand-red, #c0392b)'; }
      } else {
        confirm.setCustomValidity('');
        if (hint) { hint.textContent = 'Passwords match'; hint.style.color = 'var(--color-success, #2e7d32)'; }
      }
    }
    pw.addEventListener('input', check);
    confirm.addEventListener('input', check);
  });
}

/* ---------------------------------------------------------------------------
 * Dashboard chrome (Phase 1): mobile off-canvas sidebar toggle. The chrome is
 * PHP-rendered (see includes/ui.php); these just drive the open/close state.
 * ------------------------------------------------------------------------- */
function dashOpenSidebar() {
  document.getElementById('dashSidebar')?.classList.add('open');
  document.getElementById('dashSidebarBackdrop')?.classList.add('open');
  document.body.classList.add('dash-sidebar-open');
}
function dashCloseSidebar() {
  document.getElementById('dashSidebar')?.classList.remove('open');
  document.getElementById('dashSidebarBackdrop')?.classList.remove('open');
  document.body.classList.remove('dash-sidebar-open');
}
document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') dashCloseSidebar();
});

document.addEventListener('DOMContentLoaded', function () {
  initPasswordToggles();
  initPasswordMatch();
  loadSavedCount();
});
