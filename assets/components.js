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
            <div style="display:flex;flex-direction:column;gap:6px;font-size:0.82rem;color:rgba(255,255,255,0.6);margin-top:var(--space-4);">
              <div style="display:flex;gap:8px;align-items:flex-start;"><span style="font-family:'Material Symbols Outlined';font-size:18px;line-height:1.4;">location_on</span><span>Madhyapur Thimi Municipality-9, Bhaktapur, Nepal</span></div>
              <div style="display:flex;gap:8px;align-items:center;"><span style="font-family:'Material Symbols Outlined';font-size:18px;">call</span><span><a href="tel:+9779848714990" style="color:rgba(255,255,255,0.6);">+977-9848714990</a>, <a href="tel:+977982000470" style="color:rgba(255,255,255,0.6);">+977-982000470</a></span></div>
              <div style="display:flex;gap:8px;align-items:center;"><span style="font-family:'Material Symbols Outlined';font-size:18px;">mail</span><span><a href="mailto:info@asaancapital.com" style="color:rgba(255,255,255,0.6);">info@asaancapital.com</a></span></div>
            </div>
            <div style="display:flex;gap:14px;flex-wrap:wrap;margin-top:var(--space-4);">
              <a href="https://facebook.com/asaancapital" target="_blank" rel="noopener" aria-label="Facebook" style="color:rgba(255,255,255,0.6);display:inline-flex;"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.69 4.53-4.69 1.31 0 2.68.24 2.68.24v2.97h-1.51c-1.49 0-1.96.93-1.96 1.89v2.25h3.33l-.53 3.49h-2.8V24C19.61 23.1 24 18.1 24 12.07z"/></svg></a>
              <a href="https://instagram.com/asaancapital" target="_blank" rel="noopener" aria-label="Instagram" style="color:rgba(255,255,255,0.6);display:inline-flex;"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.16c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.41a3.72 3.72 0 01-1.38-.9 3.72 3.72 0 01-.9-1.38c-.16-.42-.36-1.06-.41-2.23C2.17 15.58 2.16 15.2 2.16 12s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.43-.16 1.06-.36 2.23-.41C8.42 2.17 8.8 2.16 12 2.16M12 0C8.74 0 8.33.01 7.05.07 5.78.13 4.9.33 4.14.63c-.79.31-1.46.72-2.13 1.38C1.35 2.68.94 3.35.63 4.14.33 4.9.13 5.78.07 7.05.01 8.33 0 8.74 0 12s.01 3.67.07 4.95c.06 1.27.26 2.15.56 2.91.31.79.72 1.46 1.38 2.13.67.66 1.34 1.07 2.13 1.38.76.3 1.64.5 2.91.56C8.33 23.99 8.74 24 12 24s3.67-.01 4.95-.07c1.27-.06 2.15-.26 2.91-.56.79-.31 1.46-.72 2.13-1.38.66-.67 1.07-1.34 1.38-2.13.3-.76.5-1.64.56-2.91.06-1.28.07-1.69.07-4.95s-.01-3.67-.07-4.95c-.06-1.27-.26-2.15-.56-2.91a5.9 5.9 0 00-1.38-2.13A5.9 5.9 0 0019.86.63c-.76-.3-1.64-.5-2.91-.56C15.67.01 15.26 0 12 0zm0 5.84A6.16 6.16 0 1018.16 12 6.16 6.16 0 0012 5.84zm0 10.16A4 4 0 1116 12a4 4 0 01-4 4zm6.41-10.4a1.44 1.44 0 11-1.44-1.44 1.44 1.44 0 011.44 1.44z"/></svg></a>
              <a href="https://x.com/asaancapital" target="_blank" rel="noopener" aria-label="X" style="color:rgba(255,255,255,0.6);display:inline-flex;"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M18.9 1.15h3.68l-8.04 9.19L24 22.85h-7.41l-5.8-7.58-6.64 7.58H.46l8.6-9.83L0 1.15h7.6l5.24 6.93zM17.61 20.64h2.04L6.49 3.24H4.3z"/></svg></a>
              <a href="https://youtube.com/@asaancapital" target="_blank" rel="noopener" aria-label="YouTube" style="color:rgba(255,255,255,0.6);display:inline-flex;"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M23.5 6.2a3.02 3.02 0 00-2.12-2.14C19.5 3.55 12 3.55 12 3.55s-7.5 0-9.38.51A3.02 3.02 0 00.5 6.2 31.6 31.6 0 000 12a31.6 31.6 0 00.5 5.8 3.02 3.02 0 002.12 2.14c1.88.51 9.38.51 9.38.51s7.5 0 9.38-.51a3.02 3.02 0 002.12-2.14A31.6 31.6 0 0024 12a31.6 31.6 0 00-.5-5.8zM9.55 15.57V8.43L15.82 12z"/></svg></a>
              <a href="https://tiktok.com/@asaancapital" target="_blank" rel="noopener" aria-label="TikTok" style="color:rgba(255,255,255,0.6);display:inline-flex;"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M16.6 5.82a4.28 4.28 0 01-1.01-2.82h-3.3v13.34a2.59 2.59 0 01-2.59 2.5 2.59 2.59 0 01-2.59-2.59 2.59 2.59 0 012.59-2.59c.27 0 .53.04.78.12v-3.35a5.94 5.94 0 00-.78-.05A5.94 5.94 0 003.36 16.3a5.94 5.94 0 005.94 5.94 5.94 5.94 0 005.94-5.94V9.4a7.57 7.57 0 004.42 1.42V7.5a4.28 4.28 0 01-3.06-1.68z"/></svg></a>
              <a href="https://linkedin.com/company/asaancapital" target="_blank" rel="noopener" aria-label="LinkedIn" style="color:rgba(255,255,255,0.6);display:inline-flex;"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M20.45 20.45h-3.56v-5.57c0-1.33-.02-3.04-1.85-3.04-1.85 0-2.13 1.45-2.13 2.94v5.67H9.35V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46zM5.34 7.43a2.07 2.07 0 110-4.14 2.07 2.07 0 010 4.14zM7.12 20.45H3.56V9h3.56zM22.22 0H1.77C.79 0 0 .77 0 1.73v20.54C0 23.23.79 24 1.77 24h20.45c.98 0 1.78-.77 1.78-1.73V1.73C24 .77 23.2 0 22.22 0z"/></svg></a>
              <a href="https://threads.net/@asaancapital" target="_blank" rel="noopener" aria-label="Threads" style="color:rgba(255,255,255,0.6);display:inline-flex;"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.3 11.2c-.1-.05-.2-.1-.3-.14-.18-3.27-1.97-5.15-4.97-5.17h-.04c-1.8 0-3.29.77-4.21 2.16l1.65 1.13c.69-1.04 1.77-1.26 2.56-1.26h.03c.98.01 1.72.29 2.2.84.35.4.58.95.7 1.65-.88-.15-1.83-.2-2.85-.14-2.86.16-4.7 1.83-4.58 4.15.06 1.18.65 2.19 1.65 2.85.85.56 1.94.83 3.08.77 1.5-.08 2.68-.65 3.5-1.7.62-.79 1.02-1.82 1.2-3.12.72.44 1.26 1.01 1.55 1.7.5 1.18.53 3.12-1.05 4.7-1.39 1.38-3.05 1.98-5.56 2-2.78-.02-4.88-.91-6.25-2.64C7.13 17.5 6.47 15.36 6.45 12c.02-3.36.68-5.5 1.96-7.36C9.78 2.91 11.88 2.02 14.66 2c2.8.02 4.94.91 6.36 2.65.7.85 1.22 1.92 1.57 3.17l1.94-.52c-.42-1.54-1.08-2.87-1.98-3.97C20.74 1.1 18.04.02 14.67 0h-.01C11.3.02 8.63 1.1 6.74 3.33 5.06 5.32 4.2 8.09 4.17 11.99v.02c.03 3.9.89 6.67 2.57 8.66 1.89 2.23 4.56 3.31 7.92 3.33h.01c2.99-.02 5.1-.8 6.83-2.53 2.27-2.26 2.2-5.1 1.45-6.84-.54-1.25-1.57-2.27-2.98-2.94zM12.26 16.19c-1.26.07-2.57-.5-2.63-1.73-.05-.91.65-1.92 2.71-2.04.24-.01.47-.02.7-.02.75 0 1.45.07 2.09.21-.24 2.98-1.64 3.51-2.87 3.58z"/></svg></a>
              <a href="https://wa.me/9779848714990" target="_blank" rel="noopener" aria-label="WhatsApp" style="color:rgba(255,255,255,0.6);display:inline-flex;"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.5 14.4c-.3-.15-1.77-.87-2.04-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.07-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48 0 1.46 1.07 2.88 1.22 3.08.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.69.62.71.23 1.36.2 1.87.12.57-.09 1.77-.72 2.02-1.42.25-.7.25-1.29.17-1.42-.07-.13-.27-.2-.57-.35zM12.04 21.5h-.01a9.45 9.45 0 01-4.82-1.32l-.35-.2-3.58.94.96-3.49-.23-.36a9.43 9.43 0 01-1.45-5.03c0-5.22 4.25-9.46 9.48-9.46a9.43 9.43 0 016.7 2.78 9.4 9.4 0 012.77 6.69c0 5.22-4.25 9.46-9.47 9.46zM20.5 3.49A11.78 11.78 0 0012.04 0C5.5 0 .19 5.31.18 11.84c0 2.09.55 4.13 1.59 5.92L.08 24l6.4-1.68a11.83 11.83 0 005.56 1.42h.01c6.53 0 11.85-5.31 11.85-11.84a11.77 11.77 0 00-3.4-8.41z"/></svg></a>
            </div>
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
