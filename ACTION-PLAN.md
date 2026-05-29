# SEO Action Plan — Asaan Capital Ltd

**Priority**: Critical > High > Medium > Low  
**Current Score**: 38/100  
**Target Score**: 70/100  

---

## Sprint 1: Critical (Do Today)

### 1.1 Create robots.txt
```txt
User-agent: *
Allow: /
Sitemap: https://asaancapital.com/sitemap.xml
```
📄 File: `public/robots.txt`

### 1.2 Generate sitemap.xml
Include all public pages: `/`, `/about`, `/how-it-works`, `/support`, `/legal`, `/signup`, `/login`, `/browse/businesses`, `/browse/investors`, `/browse/franchises`, `/browse/entrepreneurs`, `/business-valuation`, plus all business/pitch detail pages.

📄 File: `public/sitemap.xml` (dynamically generated or static)

### 1.3 Add canonical tags
In `includes/header.php`, add:
```php
<link rel="canonical" href="<?= APP_URL . $_SERVER['REQUEST_URI'] ?>">
```

### 1.4 Fix broken CTAs
- `/business/create` → should map to existing page or create proper route
- Pitch detail: fix `/pitch/N` route or remove broken links to it
- Franchise detail: fix `/franchise/detail/N` route or remove broken links

### 1.5 Add Organization + WebSite JSON-LD
In `includes/header.php`:
```php
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "Asaan Capital Ltd",
  "description": "Financial & Investment Services",
  "url": "https://asaancapital.com",
  "logo": "https://asaancapital.com/logo.png",
  "contactPoint": {
    "@type": "ContactPoint",
    "email": "hello@asaancapital.com",
    "contactType": "customer service"
  }
}
</script>
```

---

## Sprint 2: High (This Week)

### 2.1 Unique meta descriptions per page
Make `$pageDescription` dynamic in each page file, then in header.php:
```php
<meta name="description" content="<?= e($pageDescription ?? 'The premium marketplace for buying, selling, franchising, and funding SMEs.') ?>">
```

### 2.2 BreadcrumbList schema
Add JSON-LD for breadcrumbs on all inner pages that have breadcrumb HTML.

### 2.3 FAQ schema
Add `FAQPage` JSON-LD to `/support` page for rich snippet eligibility.

### 2.4 Disable directory listing
Add to `.htaccess` or web.config:
```apache
Options -Indexes
```

### 2.5 Open Graph + Twitter Card meta tags
In `includes/header.php`:
```php
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($pageDescription ?? '...') ?>">
<meta property="og:url" content="<?= APP_URL . $_SERVER['REQUEST_URI'] ?>">
<meta property="og:type" content="website">
<meta property="og:image" content="<?= APP_URL ?>/og-image.png">
<meta name="twitter:card" content="summary_large_image">
```

---

## Sprint 3: Medium (This Month)

### 3.1 Product/Service schema for listings
Add `Product` or `Service` schema to business detail pages with price, description, category.

### 3.2 Review schema for testimonials
Add `Review` schema for each testimonial on homepage.

### 3.3 Blog/content section
Create `/blog` with articles on SME investing, business valuation, M&A tips — builds topical authority.

### 3.4 Inline critical CSS
Extract above-fold styles and inline them in `<head>` to improve LCP.

---

## Sprint 4: Low (Backlog)

### 4.1 llms.txt
Create `public/llms.txt` for AI crawler guidance.

### 4.2 Author bios
Add author metadata to content pages.

### 4.3 Lazy load images
Add `loading="lazy"` to below-fold images.

### 4.4 RSS feed
Generate RSS feed for any future blog content.
