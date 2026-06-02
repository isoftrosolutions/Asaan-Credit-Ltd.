function injectFooter() {
  const root = document.getElementById('footer-root');
  if (!root) return;

  root.innerHTML = `
    <footer style="background:var(--color-text-heading);color:rgba(255,255,255,0.7);padding:var(--space-10) 0 var(--space-5);margin-top:var(--space-10);">
      <div class="container">
        <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr;gap:var(--space-6);margin-bottom:var(--space-6);" class="footer-grid">
          <div>
            <h4 style="font-family:var(--font-heading);color:#fff;margin-bottom:var(--space-3);font-size:1rem;">Get Started</h4>
            <div style="display:flex;flex-direction:column;gap:6px;font-size:0.85rem;">
              <a href="/business/create" style="color:rgba(255,255,255,0.6);">Sell your Business</a>
              <a href="/business/create" style="color:rgba(255,255,255,0.6);">Finance your Business</a>
              <a href="/browse/businesses" style="color:rgba(255,255,255,0.6);">Buy a Business</a>
              <a href="/browse/businesses" style="color:rgba(255,255,255,0.6);">Invest in a Business</a>
              <a href="/franchise/create" style="color:rgba(255,255,255,0.6);">Franchise your Business</a>
              <a href="/advisor/create" style="color:rgba(255,255,255,0.6);">Register as Advisor</a>
            </div>
          </div>
          <div>
            <h4 style="font-family:var(--font-heading);color:#fff;margin-bottom:var(--space-3);font-size:1rem;">Businesses</h4>
            <div style="display:flex;flex-direction:column;gap:6px;font-size:0.85rem;">
              <a href="/browse/businesses" style="color:rgba(255,255,255,0.6);">Businesses For Sale</a>
              <a href="/browse/businesses" style="color:rgba(255,255,255,0.6);">Investment Opportunities</a>
              <a href="/browse/businesses" style="color:rgba(255,255,255,0.6);">Businesses Seeking Loan</a>
              <a href="/browse/businesses" style="color:rgba(255,255,255,0.6);">Business Assets For Sale</a>
            </div>
            <h4 style="font-family:var(--font-heading);color:#fff;margin:var(--space-4) 0 var(--space-3);font-size:1rem;">Valuation</h4>
            <div style="display:flex;flex-direction:column;gap:6px;font-size:0.85rem;">
              <a href="/how-it-works" style="color:rgba(255,255,255,0.6);">How to Value a Business</a>
              <a href="/business-valuation" style="color:rgba(255,255,255,0.6);">Business Valuation Calculator</a>
            </div>
          </div>
          <div>
            <h4 style="font-family:var(--font-heading);color:#fff;margin-bottom:var(--space-3);font-size:1rem;">Investors</h4>
            <div style="display:flex;flex-direction:column;gap:6px;font-size:0.85rem;">
              <a href="/investor/profile-create" style="color:rgba(255,255,255,0.6);">Individual Investors</a>
              <a href="/browse/investors" style="color:rgba(255,255,255,0.6);">Business Buyers</a>
              <a href="/investor/profile-create" style="color:rgba(255,255,255,0.6);">Corporate Investors</a>
              <a href="/investor/profile-create" style="color:rgba(255,255,255,0.6);">Venture Capital Firms</a>
              <a href="/investor/profile-create" style="color:rgba(255,255,255,0.6);">Private Equity Firms</a>
              <a href="/investor/profile-create" style="color:rgba(255,255,255,0.6);">Family Offices</a>
              <a href="/investor/profile-create" style="color:rgba(255,255,255,0.6);">Business Lenders</a>
            </div>
            <h4 style="font-family:var(--font-heading);color:#fff;margin:var(--space-4) 0 var(--space-3);font-size:1rem;">CIM / Business Plan</h4>
            <div style="display:flex;flex-direction:column;gap:6px;font-size:0.85rem;">
              <a href="/how-it-works" style="color:rgba(255,255,255,0.6);">Confidential Information Memorandum (CIM)</a>
            </div>
          </div>
          <div>
            <h4 style="font-family:var(--font-heading);color:#fff;margin-bottom:var(--space-3);font-size:1rem;">Advisors</h4>
            <div style="display:flex;flex-direction:column;gap:6px;font-size:0.85rem;">
              <a href="/advisor/create" style="color:rgba(255,255,255,0.6);">Businesses Seeking Advisors</a>
              <a href="/advisor/create" style="color:rgba(255,255,255,0.6);">Investment Banks</a>
              <a href="/advisor/create" style="color:rgba(255,255,255,0.6);">M&A Advisors</a>
              <a href="/advisor/create" style="color:rgba(255,255,255,0.6);">Business Brokers</a>
              <a href="/advisor/create" style="color:rgba(255,255,255,0.6);">CRE Brokers</a>
              <a href="/advisor/create" style="color:rgba(255,255,255,0.6);">Financial Consultants</a>
              <a href="/advisor/create" style="color:rgba(255,255,255,0.6);">Accountants</a>
              <a href="/advisor/create" style="color:rgba(255,255,255,0.6);">Law Firms</a>
            </div>
          </div>
          <div>
            <h4 style="font-family:var(--font-heading);color:#fff;margin-bottom:var(--space-3);font-size:1rem;">Franchises</h4>
            <div style="display:flex;flex-direction:column;gap:6px;font-size:0.85rem;">
              <a href="/browse/franchises" style="color:rgba(255,255,255,0.6);">Franchises For Sale</a>
              <a href="/browse/franchises" style="color:rgba(255,255,255,0.6);">Franchise Investors</a>
            </div>
            <h4 style="font-family:var(--font-heading);color:#fff;margin:var(--space-4) 0 var(--space-3);font-size:1rem;">Company</h4>
            <div style="display:flex;flex-direction:column;gap:6px;font-size:0.85rem;">
              <a href="/about" style="color:rgba(255,255,255,0.6);">About</a>
              <a href="/about" style="color:rgba(255,255,255,0.6);">Testimonials</a>
              <a href="/support" style="color:rgba(255,255,255,0.6);">FAQs</a>
            </div>
          </div>
        </div>
        <div style="border-top:1px solid rgba(255,255,255,0.1);padding:var(--space-5) 0;display:grid;grid-template-columns:1fr 2fr;gap:var(--space-6);align-items:start;" class="footer-bottom-grid">
          <div>
            <h4 style="font-family:var(--font-heading);color:#fff;margin-bottom:var(--space-2);font-size:1.1rem;">Asaan Capital Ltd</h4>
            <p style="font-size:0.82rem;line-height:1.7;color:rgba(255,255,255,0.5);">Asaan Capital Ltd is a user-friendly and transparent online platform that connects SMEs and franchise brands with the right investors, buyers, and M&A advisors globally. Boasting a robust network of verified members with up-to-date listings and efficient price discovery, we provide a level playing field for businesses and investors of all sizes. Leveraging AI-powered matchmaking, we offer pocket-friendly pricing plans with dedicated customer support via email and WhatsApp.</p>
          </div>
          <div style="display:flex;flex-direction:column;gap:var(--space-3);">
            <div style="display:flex;gap:var(--space-3);flex-wrap:wrap;">
              <a href="/about" style="color:rgba(255,255,255,0.6);font-size:0.85rem;">About</a>
              <a href="/how-it-works" style="color:rgba(255,255,255,0.6);font-size:0.85rem;">How it Works</a>
              <a href="/support" style="color:rgba(255,255,255,0.6);font-size:0.85rem;">Testimonials</a>
              <a href="/support" style="color:rgba(255,255,255,0.6);font-size:0.85rem;">Blog</a>
              <a href="/support" style="color:rgba(255,255,255,0.6);font-size:0.85rem;">Press</a>
              <a href="/support" style="color:rgba(255,255,255,0.6);font-size:0.85rem;">FAQs</a>
              <a href="/legal" style="color:rgba(255,255,255,0.6);font-size:0.85rem;">Terms of Service</a>
              <a href="/legal" style="color:rgba(255,255,255,0.6);font-size:0.85rem;">Privacy Policy</a>
            </div>
            <p style="font-size:0.8rem;color:rgba(255,255,255,0.4);margin-top:var(--space-2);">&copy; ${new Date().getFullYear()} Asaan Capital Ltd. All rights reserved.</p>
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
});
