# Product Requirements Document (PRD)
## InvestMatch Nepal — Investor & Entrepreneur Matching Platform

---

| Field | Value |
|---|---|
| **Product Name** | Asaan Marketplace — Investment & Business Matching |
| **Version** | 1.0 |
| **Document Date** | 21 May 2026 |
| **Prepared By** | Devbarat Prasad Patel — iSoftro Solutions |
| **Client** | *[To be filled]* |
| **Project Code** | IMN-V1 |
| **Status** | Draft — Pending Client Approval |

---

## 1. Executive Summary

InvestMatch Nepal is a Nepal-focused two-sided platform that connects investors with entrepreneurs seeking funding. The product helps verified investors (individuals and firms) discover relevant startup pitches across all stages, and helps entrepreneurs (individuals and registered companies) get their ventures in front of capital providers whose mandate matches their funding needs.

The platform is delivered as a **responsive website + native Android app**, with a Laravel-powered backend and an admin panel managed by the Client. The model is **free for both sides in v1**; monetization is planned for v2.

The core differentiator is **trust through manual verification** — every investor and entrepreneur is reviewed by an admin before being eligible to send or receive interest requests. This addresses the most common failure mode of two-sided matchmaking platforms: fake profiles and bad-faith actors.

---

## 2. Goals & Success Metrics

### 2.1 Product Goals

1. **Reduce friction in early-stage capital matching** for the Nepali market
2. **Build trust through verification** so both sides take the platform seriously
3. **Centralize pitch discovery** in a market currently fragmented across Facebook groups, personal networks, and informal channels
4. **Provide an admin-controlled environment** the Client can moderate end-to-end

### 2.2 Success Metrics (v1, first 90 days post-launch)

| Metric | Target |
|---|---|
| Verified investor signups | 50+ |
| Verified entrepreneur pitches | 150+ |
| Interest requests sent | 300+ |
| Successful matches (request accepted) | 50+ |
| Daily active users (rolling 7-day) | 100+ |
| Verification turnaround (admin SLA) | < 48 hours |

---

## 3. Target Users & Market

### 3.1 Geography
**Nepal only.** All users must have a Nepali citizenship or a Nepal-registered company.

### 3.2 User Types

**Investor Side:**
- Individual investors (angel investors, HNIs, professionals with disposable capital)
- Investment firms / VCs / family offices registered in Nepal

**Entrepreneur Side:**
- Solo founders with an idea or MVP
- Early-stage startups with traction
- Growth-stage companies seeking expansion capital
- Both individual founders and registered companies (Pvt. Ltd., partnerships)

### 3.3 Out of Geographic Scope
- Non-Resident Nepalis (NRN) abroad — not supported in v1
- International investors — not supported in v1

---

## 4. User Roles & Permissions

| Role | Permissions |
|---|---|
| **Guest** | Browse landing page, view marketing content, sign up |
| **Unverified User** | Complete profile, upload pitch/preferences, upload verification documents. **Cannot send or receive interest requests.** |
| **Verified Investor** | All unverified permissions + send interest requests, view contact info after acceptance, save/bookmark pitches |
| **Verified Entrepreneur** | All unverified permissions + receive and accept/reject interest requests, view investor contact info after they connect |
| **Admin (Client)** | Full access to admin panel — user management, verification queue, moderation, analytics, content management |

---

## 5. Functional Requirements

### 5.1 Authentication & Registration

- **Sign-up methods:** Email + password (no third-party logins like LinkedIn/Google in v1)
- **Role selection at signup:** Investor OR Entrepreneur (a user can have only one active role per account)
- **Account type within role:** Individual / Company (toggle during onboarding)
- **Email verification:** Required (verification link sent via email) before profile becomes active
- **Phone number:** Captured during signup but not OTP-verified in v1
- **Password reset:** Email-based reset flow
- **Session management:** Standard JWT-based auth on mobile, session-based on web

### 5.2 Investor Profile

Required fields (all categories enabled):

**Basic Information**
- Full name (or Company name if Company type)
- Profile photo / company logo
- Location (Province + District dropdowns)
- Bio (250 words max)
- Contact info (email + phone — **hidden from other users by default**, revealed only after a successful match)

**Investment Preferences**
- Preferred sectors (multi-select from admin-managed sector list)
- Preferred startup stage (Idea / MVP / Early Revenue / Growth — multi-select)
- Ticket size range (Min NPR – Max NPR, slider)
- Preferred geography (Province multi-select, or "Anywhere in Nepal")

**Track Record**
- Number of past investments
- List of portfolio companies (free text + optional company logos)
- Total capital deployed to date (NPR)

**Verification Documents** *(uploaded to admin for review)*
- Citizenship copy (Individual) OR Company registration (PAN/VAT certificate) for Company type
- Optional: PAN card

**Social Proof**
- LinkedIn URL
- Personal/company website URL
- References (free text — up to 3 references with name + contact)

### 5.3 Entrepreneur Pitch / Profile

Required fields (all categories enabled):

**Basic Information**
- Founder name / Company name
- Founder photo / Company logo
- Location (Province + District)
- One-line tagline (140 chars max)

**Pitch Content**
- Problem statement (500 words max)
- Solution (500 words max)
- Market size & opportunity (250 words max)
- Business model (250 words max)
- Current traction (250 words max — revenue, users, partnerships, etc.)

**Funding Ask**
- Amount sought (NPR, numeric)
- Equity offered (% slider, 0–49%)
- Intended use of funds (250 words max — categorized: product, hiring, marketing, ops, other)
- Current valuation (NPR, optional)

**Media**
- Pitch deck upload (PDF, max 10 MB)
- Pitch video — **YouTube or Vimeo URL embed only** *(see Section 9 — Technical Recommendations)*
- Product photos (up to 5 images, max 2 MB each)

**Team Info**
- Co-founders (name + role + LinkedIn URL, up to 5)
- Team size (numeric)
- Key hires (free text)

> **Note:** Entrepreneur identity verification documents (citizenship/company registration) were not selected during requirements gathering. **Recommended for v1.1** to maintain platform integrity. Decision deferred to Client.

### 5.4 Discovery & Search

**Browse Screen**
- Default view: Smart Suggestions tab (system-recommended matches) + All tab (full directory)
- Card-based list view with key info: name/company, location, sector, stage, funding ask (entrepreneur) or ticket range (investor), Verified badge

**Filters (all available simultaneously, AND-logic):**
1. Sector / Industry (multi-select from admin-managed list)
2. Funding stage (Idea / MVP / Early Revenue / Growth)
3. Investment size / ticket range (NPR slider — Min to Max)
4. Location (Province / District dropdown)
5. Verified-only toggle (default: ON)
6. Equity offered range (slider — applies when browsing entrepreneurs)
7. Keyword search (free-text search across pitch title, one-liner, and company name)

**Smart Suggestions Logic (v1 — rule-based, not AI):**
- Suggestions ranked by overlap score across three dimensions:
  - **Sector match** (40% weight)
  - **Stage match** (30% weight)
  - **Budget/ticket-size overlap** (30% weight)
- Geography is a tiebreaker (same province ranked higher)
- Only Verified profiles appear in Smart Suggestions
- Algorithm is deterministic and runs server-side on profile/pitch updates

### 5.5 Interest Request Flow (Core Connection Mechanism)

1. **Investor views entrepreneur pitch** → "Express Interest" button (visible only if both parties are Verified)
2. **Investor sends interest request** with optional 250-character message
3. **Entrepreneur receives notification** (in-app bell + email)
4. **Entrepreneur reviews investor's full profile** including track record and references
5. **Entrepreneur accepts or rejects** the request
   - **Accept:** Both parties' contact info (email + phone) becomes visible to each other; a "Match Made" record is created; both receive confirmation email
   - **Reject:** Investor receives a polite system-generated rejection (no reason required); investor cannot re-send interest to the same entrepreneur for 60 days
6. **Match record** persists in both users' "My Connections" screen with contact details and timestamp

**Limits (anti-spam):**
- Unverified users: Cannot send/receive interest requests at all
- Verified investors: 10 interest requests per day (resets at midnight NPT)
- Verified entrepreneurs: No limit on receiving (their pitch is public)

### 5.6 Verification Workflow

1. User completes profile → uploads verification documents (citizenship/company registration + optional PAN)
2. Verification status set to **"Pending Review"** — user receives email confirmation
3. **Admin reviews in verification queue:**
   - Approves → "Verified" badge added to profile; user gets approval email
   - Rejects → Reason provided; user gets rejection email; can re-upload
4. SLA target: Admin reviews within 48 hours (operational target, not enforced by system)
5. Verified badge displays as a blue checkmark next to user name on all surfaces (profile, browse cards, interest request screen)

### 5.7 Notifications

**Channels: In-app + Email only.** No push notifications, no SMS in v1.

| Event | In-App | Email |
|---|---|---|
| Email verification needed | — | ✅ |
| Profile approved (verified) | ✅ | ✅ |
| Profile rejected | ✅ | ✅ |
| New interest request received | ✅ | ✅ |
| Interest request accepted | ✅ | ✅ |
| Interest request rejected | ✅ | — |
| Admin broadcast announcement | ✅ | ✅ |
| Account suspended by admin | ✅ | ✅ |

In-app notifications appear under a bell icon with unread count and a feed showing the last 30 notifications.

### 5.8 Admin Panel

All features hosted on a separate `/admin` route, accessible only by Client. Built with Laravel + Tailwind UI (server-rendered, no separate SPA needed).

**v1 Core Modules (operational essentials):**

1. **User Management**
   - List all users with filters (role, verification status, signup date)
   - View full profile of any user
   - Suspend / unban users
   - Edit user profiles (name, contact, profile data) if needed for corrections
   - Bulk export user list as CSV

2. **Verification Queue**
   - List of pending verifications with uploaded documents preview
   - Approve / Reject with optional rejection reason
   - History log of all verification decisions

3. **Pitch Moderation**
   - List of new/recent pitches
   - Hide / unhide individual pitches (without deleting user account)
   - Flag-for-review system: pitches reported by users land here

4. **Reports & Complaints**
   - List of user-submitted reports against other users or pitches
   - Mark as Resolved / Dismissed
   - Take action (warning, suspension, ban) from same screen

**v1 Lighter Build (read-only or basic CRUD):**

5. **Interest Request Log** — Read-only table of all interest requests across the platform (sender, receiver, status, date). Filterable, exportable to CSV.

6. **Content Management** — Basic CRUD for:
   - Sector/Industry list (used by both investor preferences and entrepreneur pitches)
   - FAQs (displayed on a public `/faq` page)
   - Homepage banner image + headline

7. **Broadcast / Announcements** — Compose a message + select audience (All / Investors only / Entrepreneurs only / Verified only) → sends as in-app notification + email blast.

**v1 Basic Build (simple cards/numbers, no advanced visualizations):**

8. **Analytics Dashboard**
   - Total users (split by role)
   - New signups this week / this month
   - Verified vs Unverified count
   - Total pitches active
   - Total interest requests sent
   - Total matches made (accepted requests)
   - Simple line chart for user growth (Chart.js)
   - **Out of scope:** Funnels, cohort analysis, heatmaps, behavior tracking, real-time dashboards

---

## 6. Non-Functional Requirements

### 6.1 Performance
- Page load (web): under 3 seconds on 4G mobile network
- API response time: under 500ms for browse/search endpoints (p95)
- Image lazy-loading on all list views
- Pagination: 20 items per page on browse screens

### 6.2 Security
- HTTPS everywhere (Let's Encrypt SSL on production domain)
- Password hashing: bcrypt (Laravel default)
- File upload validation: type whitelist (PDF, JPG, PNG only for documents/images)
- SQL injection protection via Laravel Eloquent / parameterized queries
- CSRF tokens on all forms
- Admin panel: rate-limited login (5 attempts → 15-min lockout)

### 6.3 Privacy
- Contact info (email + phone) hidden from other users until match
- Verification documents accessible only to admin, stored in non-public directory on VPS
- No user data shared with third parties
- Account deletion: User can request deletion → admin processes within 7 days

### 6.4 Browser & Device Support
- **Web:** Chrome (latest), Firefox (latest), Safari (latest), Edge (latest) — desktop + mobile
- **Android app:** Android 8.0 (Oreo) and above
- Responsive design: 360px (mobile) up to 1920px (desktop) viewport widths

### 6.5 Capacity (v1 design targets)
- Up to 5,000 registered users
- Up to 2,000 active pitches
- Up to 50,000 interest requests over platform lifetime
- VPS: existing Hostinger VPS (CyberPanel + OpenLiteSpeed) — confirmed capacity per developer

---

## 7. Technical Architecture

### 7.1 Stack

| Layer | Technology |
|---|---|
| Backend API | PHP 8.2+ (Core PHP, no framework) |
| Database | MySQL 8 / MariaDB 10.6+ |
| Web Frontend | Vanilla PHP templates + Vanilla JavaScript + Custom CSS |
| Mobile App | React Native (Android only in v1) |
| Authentication | Session-based (PHP native sessions) |
| File Storage | VPS local filesystem (with directory hardening for verification docs) |
| Email | SMTP via existing Hostinger mail or transactional service |
| Hosting | Hostinger VPS with CyberPanel + OpenLiteSpeed |
| Admin Panel | Same app, `/admin` namespace, role-gated middleware |

### 7.2 Deliverables

1. **Responsive Website** — Public-facing site at `https://[domain].com`
2. **Android Mobile App** — APK + Play Store-ready AAB build
3. **Admin Panel** — Accessible at `https://[domain].com/admin`
4. **Database schema** — Documented in SQL + ER diagram
5. **API documentation** — Postman collection for all endpoints
6. **Deployment guide** — README with environment setup, deploy commands, .env template
7. **Source code handover** — Git repository (private) with full commit history
8. **Admin training** — 1 session (video call, ~1 hour) walkthrough of admin panel

### 7.3 Domains & Accounts (Client-Provided)

The Client is responsible for providing:
- Domain name (developer can recommend; registration cost not included in NPR 50,000)
- Google Play Developer account (one-time $25 fee — Client's expense)
- Production email service credentials (if not using Hostinger default)

---

## 8. Out of Scope (v1)

The following are **explicitly excluded** from this build. Any of these can be quoted separately for v2 or beyond.

| Excluded Feature | Reason / v2 Path |
|---|---|
| **iOS app** | Android-first launch. iOS build is a separate engagement (separate quote). |
| **AI-powered matching** | v1 uses rule-based weighted scoring. AI matching deferred to v2 after platform has training data. |
| **Bilingual (Nepali) UI** | English UI only. i18n setup is a significant effort. v2 candidate. |
| **Payment gateway (eSewa / Khalti)** | v1 is free for all users. No payment integration needed. Reserved for v2 monetization. |
| **In-app chat / messaging** | Connection model is interest-request → contact reveal. No real-time chat in v1. |
| **Push notifications (mobile)** | In-app + email only. Firebase setup deferred to v2. |
| **SMS notifications** | Avoided due to recurring per-SMS cost. |
| **Direct video upload to platform** | Use YouTube / Vimeo URL embedding instead. Saves storage and bandwidth costs. |
| **Real-time features** | No WebSockets / live presence / live notifications. Standard refresh-based UX. |
| **Third-party logins** | No LinkedIn / Google / Facebook OAuth in v1. Email + password only. |
| **Advanced analytics** | Basic counters + one growth chart only. No funnels, cohorts, or behavior tracking. |
| **Multiple admin roles** | Single admin role (Client only). Multi-admin / sub-admin permissions not in v1. |
| **Public API for third parties** | Internal API only. No external developer access. |
| **Calendar / meeting scheduling** | Users coordinate offline after match. No Zoom / Google Calendar integration. |
| **Document signing / NDAs** | Out of scope. Users handle legal agreements offline. |

---

## 9. Technical Recommendations (Developer Notes)

These are implementation choices that protect the project's timeline and the Client's long-term costs. Documented here so the Client understands the *why* behind certain decisions.

### 9.1 Pitch Video Strategy
**Recommendation:** Accept YouTube / Vimeo URL embedding instead of direct video upload.

**Reasoning:** Direct video upload requires:
- Significant VPS storage (a 2-minute HD video ≈ 50-100 MB; 500 pitches = 25-50 GB)
- Video transcoding for multiple resolutions
- Bandwidth costs at scale
- A streaming player implementation

URL embedding gives identical UX for the investor (video plays inline on the pitch page) while pushing storage and bandwidth costs to YouTube/Vimeo — a tested approach used by AngelList and similar platforms.

### 9.2 Smart Suggestion Algorithm
**v1 implementation:** Server-side weighted scoring (sector 40% + stage 30% + budget 30%). Re-computed when a profile is created or updated. Cached for 24 hours.

**v2 path:** Once the platform has 6+ months of behavioral data (which interest requests get accepted, which pitches get the most views, etc.), this becomes the training data for a real recommendation model.

### 9.3 Admin Panel Build Strategy
**Recommendation:** Build the admin panel as a Laravel-Blade-rendered area within the same application, using Tailwind UI components.

**Reasoning:** A separate React/Vue admin SPA would double the frontend work. Server-rendered admin keeps the build focused, and admin UX doesn't need real-time interactivity.

### 9.4 Verification Document Storage
**Recommendation:** Store verification documents in `/storage/verification-docs/` (non-public, requires admin authentication to access). Access via signed temporary URLs generated by the Laravel app.

### 9.5 Why English-Only in v1
i18n requires:
- Translation files for every UI string (~500 strings)
- DB columns for bilingual content (pitch in EN + NP)
- Devanagari font handling on Android
- Right-to-left fallbacks and font scaling tests

This is genuinely 4-5 days of careful work. For v1, English keeps focus on functionality. Nepali UI is a strong v2 addition once the platform has product-market fit.

---

## 10. Project Timeline & Milestones

**Total Duration:** 3 weeks (21 working days)
**Start Date:** *[To be confirmed after agreement signing]*
**Delivery Date:** *[Start + 21 working days]*

### Milestone Breakdown

| Milestone | Deliverable | Duration | Payment Trigger |
|---|---|---|---|
| **M1 — UI Mockup** | Clickable UI mockup of all key screens (web + mobile) | 2-3 days | Mockup approval → 10% advance (NPR 5,000) |
| **M2 — Backend + Admin Core** | Database, authentication, profile system, verification queue, basic admin panel | 7 days | Working demo on staging URL → 40% (NPR 20,000) |
| **M3 — Web Frontend + Discovery** | Public website, browse/search/filter, smart suggestions, interest request flow | 5 days | Web demo accessible → covered in M2 payment |
| **M4 — Android App** | React Native app, all screens, API integration, APK build | 5 days | APK delivered → covered in M5 payment |
| **M5 — Final Polish + Handover** | Bug fixes, content management UI, admin training session, Play Store assets | 2 days | Final delivery → 50% (NPR 25,000) |

### Revision Policy
- **2 rounds of revisions included** at each milestone (M1, M2, M3, M4)
- Additional revisions: NPR 1,500 per round (small) or NPR 5,000 per round (major scope change)
- "Revision" = adjustments to already-specified features; **does not include new feature additions** (those are scope changes, quoted separately)

---

## 11. Payment Terms

**Total Project Cost:** NPR 50,000

| Milestone | Payment | Amount |
|---|---|---|
| Mockup approval (M1) | 10% advance | NPR 5,000 |
| Backend + admin demo (M2) | 40% | NPR 20,000 |
| Final delivery (M5) | 50% | NPR 25,000 |

**Notes:**
- Payment via bank transfer, eSewa, or Khalti
- All payments non-refundable once milestone is delivered
- Late payment > 7 days pauses development until payment is resolved
- Domain registration, Play Store fee ($25), any third-party service costs are **separate** and paid directly by Client

---

## 12. Post-Launch Support

- **30 days of free bug-fix support** after final delivery — covers any bugs in v1 scope features
- **Not included in free support:** New features, scope changes, hosting issues unrelated to code, user-data-entry mistakes, third-party service outages
- **Optional maintenance contract:** Available post-launch (quoted separately based on Client's needs)

---

## 13. Risks & Mitigations

| Risk | Mitigation |
|---|---|
| Client requests features mid-build that weren't in the original scope | All scope additions require a written change order with separate timeline & cost. PRD is the boundary. |
| Verification queue grows faster than Client can review | Client commits to 48-hour SLA. Platform handles up to 100 pending verifications in queue UI without degradation. |
| Fake / spam profiles slip through verification | Reports & Complaints module gives users a way to flag bad actors. Admin can suspend post-verification. |
| Low initial signups → empty marketplace | Out of developer scope — Client owns user acquisition strategy. PRD assumes Client has a launch plan. |
| VPS resource constraints under load | Existing VPS sized for 5k users; if usage grows, vertical scaling on Hostinger is straightforward. |
| Play Store rejection during submission | App built to Play Store policy guidelines. Privacy policy, data safety form, and content rating completed as part of M5. |

---

## 14. Sign-Off

This PRD represents the complete agreed scope of v1 of InvestMatch Nepal. Both parties sign to acknowledge:

1. The scope listed in Sections 4–7 is what will be built
2. The items in Section 8 (Out of Scope) will NOT be built without a separate written agreement
3. Payment terms in Section 11 are agreed
4. Timeline in Section 10 starts after this PRD is signed AND advance payment (Milestone M1) is received

| | Signature | Date |
|---|---|---|
| **Client** | _______________________ | __________ |
| **Developer** *(Devbarat Prasad Patel, iSoftro Solutions)* | _______________________ | __________ |

---

*End of Document — InvestMatch Nepal PRD v1.0*
