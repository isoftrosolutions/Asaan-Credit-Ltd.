# SEO Audit Report — Asaan Capital Ltd

**URL**: https://asaancapital.com  
**Date**: 29 May 2026  
**Business Type**: Financial & Investment Services / SME Marketplace  
**Pages Crawled**: ~20 public pages  

---

## Executive Summary

**SEO Health Score: 38 / 100** ⚠️

The site has solid on-page content quality and a clear value proposition, but is held back by critical technical SEO gaps, missing structured data, and duplicate content issues.

### Top 5 Critical Issues
1. **No robots.txt or sitemap.xml** — search engines cannot properly discover pages
2. **No canonical tags** — duplicate content across `/business/N` and `/business/detail.php?id=N`
3. **Generic meta description on every page** — same description on all 20+ pages
4. **No schema.org / structured data** — zero JSON-LD markup (Organization, WebSite, BreadcrumbList, Product, etc.)
5. **Broken CTAs** — `/business/create`, `/pitch/2`, `/franchise/detail/1` all return 404/500 errors

### Top 5 Quick Wins
1. Create a `robots.txt` and `sitemap.xml`
2. Add unique meta descriptions per page
3. Add `rel="canonical"` to resolve duplicate content
4. Add Organization + WebSite + BreadcrumbList JSON-LD schema
5. Fix the broken `/business/create` CTA link on homepage

---

## 1. Technical SEO (Score: 25/100 — Weight: 22%)

### ❌ robots.txt — **404 Not Found**
Search engines have no instructions on what to crawl.

### ❌ sitemap.xml — **404 Not Found**
No XML sitemap — search engines cannot efficiently discover all pages.

### ❌ Canonical Tags — **Missing**
- Homepage: no `<link rel="canonical">`
- Business detail accessible at both `/business/1` and `/business/detail.php?id=1` — duplicate content risk
- Pitch detail: `/pitch/2` returns 500 error; `/entrepreneur/pitch/2` returns 404

### ❌ Security Headers (not verified live, but no evidence of)
- Missing: `X-Robots-Tag`, `X-Frame-Options`, `Content-Security-Policy`

### ❌ URL Structure
- Mixed URL styles: clean `/browse/businesses` vs parameter `/business/detail.php?id=1`
- The `.php` extension visible in some URLs (`/discover/businesses.php`, `/business/detail.php?id=1`)

### ❌ Directory Listing Exposed
- `/connections/` has directory listing enabled — leaks PHP file names and internal structure

### ⚠️ Core Web Vitals
- Heavy reliance on Google Fonts (render-blocking)
- All styles in external CSS files (good), but no critical CSS inlined
- Images: logo.png loaded but no explicit width/height attributes (could cause CLS)

---

## 2. On-Page SEO (Score: 40/100 — Weight: 20%)

### Title Tags
- ✅ All pages have unique titles
- ✅ Include brand name
- ❌ Titles are very long (e.g., "Browse Businesses — Asaan Capital Ltd - Financial & Investment Services" = ~65 chars, borderline)
- ❌ Homepage title is extremely long: `Asaan Capital Ltd - Financial & Investment Services — Connect with Investors. Sell or Grow Your Business Faster.` (~100+ chars) — may be truncated in SERPs

### Meta Descriptions
- ❌ **All pages share the same meta description**: "The premium marketplace for buying, selling, franchising, and funding SMEs."
- This is a major missed opportunity — every page should have a unique, compelling meta description

### Heading Structure
- ✅ Homepage has a single `<h1>` — good
- ✅ Content pages use hierarchical headings (`h1 > h2 > h3 > h4`)
- ❌ Browse pages missing `<h1>` — uses `<h2>` instead
- ⚠️ FAQ page has `<h2>` "Frequently Asked Questions" but no `<h1>`

### Internal Linking
- ✅ Navigation links work well across main sections
- ✅ Breadcrumbs present on most pages
- ❌ Broken internal links: `/business/create` (404), pitch and franchise detail pages
- ⚠️ No contextual cross-linking between related businesses/pitches

---

## 3. Content Quality (Score: 65/100 — Weight: 23%)

### E-E-A-T Signals
- ✅ Clear "Our Story" page with team background
- ✅ Press mentions (Nepali Times, TechKhabar)
- ✅ Contact information (email, address) on about page
- ✅ Testimonials from real users
- ✅ Trust signals: verification process clearly explained
- ❌ No author bios or individual team member profiles
- ❌ No "About the Company" entity description for schema
- ❌ No privacy policy details beyond basic terms link

### Readability
- ✅ Clear, straightforward language
- ✅ Good use of short paragraphs and bullet points
- ✅ Content targets both business owners and investors effectively

### Thin Content
- ✅ Homepage is comprehensive
- ✅ How It Works page is thorough with role-specific guides
- ⚠️ Browse pages have minimal content beyond listing results
- ❌ No blog or educational content — no content marketing strategy visible

---

## 4. Schema & Structured Data (Score: 0/100 — Weight: 10%)

### ❌ No Structured Data Found
The site has zero schema.org markup:
- No Organization schema (brand name, logo, contact info)
- No WebSite schema (site name, search URL)
- No BreadcrumbList schema (present on most pages)
- No Product/Service schema for business listings
- No LocalBusiness schema
- No FAQ schema (despite having FAQ content)
- No Review schema for testimonials
- No ItemList schema for browse/search result pages

This is the single biggest missed opportunity for rich results.

---

## 5. Performance (Score: 45/100 — Weight: 10%)

### Loading
- ✅ CSS is external (cachable)
- ❌ Google Fonts is render-blocking (preconnect helps but font CSS is still a render blocker)
- ❌ No critical CSS inlined — full styles.css must load before paint

### JavaScript
- ❌ Header/nav is JS-rendered (header.js, components.js) — may not be indexed by all crawlers
- ❌ Three external JS files loaded synchronously in `<head>` (icons.js, header.js, components.js)
- ✅ JS is minimal otherwise

### Images
- ⚠️ logo.png — no explicit width/height attributes
- ⚠️ No lazy loading on below-fold content images
- ✅ No image is excessively large based on page weight

---

## 6. AI Search Readiness / GEO (Score: 25/100 — Weight: 10%)

### ❌ No llms.txt, llms-full.txt, or robots.txt
- AI crawlers (GPTBot, ClaudeBot, Google-Extended) have no instructions

### ❌ No Open Graph or Twitter Card meta tags
- Shared links will lack title, description, and image previews — poor social performance

### ⚠️ Citability
- ❌ No citation-worthy statistics with clear sources
- ✅ Clear brand name and purpose (good for brand mention tracking)
- ❌ No author bylines on content

---

## 7. Images (Score: 30/100 — Weight: 5%)

### ✅ Logo has alt text: "Asaan Capital Ltd - Financial & Investment Services"

### ❌ Missing:
- No OG image for social sharing
- No favicon detected (should check for favicon.ico)
- No explicit width/height on any images

---

## 8. Mobile & UX

- ✅ Responsive layout (mobile-friendly classes visible)
- ✅ Viewport meta tag present
- ❌ No mobile-specific testing done (would need real device testing)
- ⚠️ Signup form is multi-step — good UX but adds friction

---

## 9. Competitor Observations

- Competitor marketplaces (dealroom.net, wefunder.com) all have:
  - Rich schema markup
  - Blog/content sections
  - RSS feeds
  - Social proof with structured reviews
  - Comprehensive breadcrumb schema

---

## Priority Action Plan

### Critical (Fix Immediately)
| # | Issue | Effort | Impact |
|---|-------|--------|--------|
| 1 | Create `robots.txt` allowing all crawlers, pointing to sitemap | 10 min | High |
| 2 | Generate and submit `sitemap.xml` to Google Search Console | 30 min | High |
| 3 | Add `rel="canonical"` tags to resolve `/business/N` vs `/business/detail.php?id=N` duplication | 20 min | High |
| 4 | Fix broken CTAs: `/business/create`, pitch/franchise detail pages | 1 hr | High |
| 5 | Add Organization + WebSite JSON-LD schema to header.php | 30 min | High |

### High (Fix Within 1 Week)
| # | Issue | Effort | Impact |
|---|-------|--------|--------|
| 6 | Unique meta descriptions per page (dynamic, based on page content) | 1 hr | Medium |
| 7 | Add BreadcrumbList schema to all pages with breadcrumbs | 30 min | Medium |
| 8 | Add FAQ schema to the FAQ page | 20 min | Medium |
| 9 | Disable directory listing on `/connections/` | 5 min | Medium |
| 10 | Add Open Graph and Twitter Card meta tags | 1 hr | Medium |

### Medium (Fix Within 1 Month)
| # | Issue | Effort | Impact |
|---|-------|--------|--------|
| 11 | Add Product/Service schema to business listing pages | 2 hr | Medium |
| 12 | Add Review schema for testimonials | 30 min | Medium |
| 13 | Create blog/content section for educational content | 2-3 days | Medium |
| 14 | Inline critical CSS for faster first paint | 2 hr | Low |
| 15 | Add hreflang tags if targeting multiple regions | 1 hr | Low |

### Low (Backlog)
| # | Issue | Effort | Impact |
|---|-------|--------|--------|
| 16 | Create llms.txt for AI crawler guidance | 15 min | Low |
| 17 | Add author bios to content pages | 1 hr | Low |
| 18 | Implement lazy loading for images | 30 min | Low |
| 19 | Add RSS feed for content updates | 30 min | Low |
| 20 | Clean up `.php` extension in URLs for consistency | 3 hr | Low |

---

## Summary

The site has **strong foundational content** and a **clear value proposition**, but is invisible to search engines in many ways. The lack of robots.txt, sitemap, canonical tags, and structured data means search engines cannot efficiently discover, understand, or feature the site's content. Addressing the **Critical** items alone would raise the SEO Health Score from ~38 to ~65.
