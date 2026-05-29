function injectFooter() {
  const root = document.getElementById('footer-root');
  if (!root) return;

  root.innerHTML = `
    <footer style="background:var(--color-text-heading);color:rgba(255,255,255,0.7);padding:3rem 0 1.5rem;margin-top:4rem;">
      <div class="container">
        <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:2rem;margin-bottom:2rem;">
          <div>
            <h4 style="font-family:var(--font-heading);color:#fff;margin-bottom:0.75rem;">Asaan Capital Ltd</h4>
            <p style="font-size:0.85rem;line-height:1.7;">Financial &amp; Investment Services — connecting verified business owners with qualified investors, buyers, and franchise partners in Nepal.</p>
          </div>
          <div>
            <h5 style="color:#fff;margin-bottom:0.75rem;font-size:0.9rem;">Marketplace</h5>
            <div style="display:flex;flex-direction:column;gap:6px;font-size:0.85rem;">
              <a href="/browse/businesses" style="color:rgba(255,255,255,0.6);">Businesses for Sale</a>
              <a href="/browse/investors" style="color:rgba(255,255,255,0.6);">Find Investors</a>
              <a href="/browse/franchises" style="color:rgba(255,255,255,0.6);">Franchises</a>
              <a href="/business-valuation" style="color:rgba(255,255,255,0.6);">Valuation Tool</a>
            </div>
          </div>
          <div>
            <h5 style="color:#fff;margin-bottom:0.75rem;font-size:0.9rem;">Company</h5>
            <div style="display:flex;flex-direction:column;gap:6px;font-size:0.85rem;">
              <a href="/about" style="color:rgba(255,255,255,0.6);">About Us</a>
              <a href="/how-it-works" style="color:rgba(255,255,255,0.6);">How It Works</a>
              <a href="/support" style="color:rgba(255,255,255,0.6);">Support</a>
              <a href="/legal" style="color:rgba(255,255,255,0.6);">Terms of Service</a>
            </div>
          </div>
          <div>
            <h5 style="color:#fff;margin-bottom:0.75rem;font-size:0.9rem;">For Users</h5>
            <div style="display:flex;flex-direction:column;gap:6px;font-size:0.85rem;">
              <a href="/signup" style="color:rgba(255,255,255,0.6);">Create Account</a>
              <a href="/login" style="color:rgba(255,255,255,0.6);">Sign In</a>
              <a href="/support" style="color:rgba(255,255,255,0.6);">FAQ</a>
            </div>
          </div>
        </div>
        <div style="border-top:1px solid rgba(255,255,255,0.1);padding-top:1.5rem;font-size:0.8rem;text-align:center;">
          &copy; ${new Date().getFullYear()} Asaan Capital Ltd. All rights reserved.
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
        <input type="hidden" name="_csrf" value="">
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
  const colors = { success: '#166534', error: '#b91c1c', info: '#1e40af', warning: '#b45309' };
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
