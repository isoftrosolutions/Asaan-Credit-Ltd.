---
name: Asaan Marketplace System
colors:
  surface: '#FFFFFF'
  surface-dim: '#d2d9f4'
  surface-bright: '#faf8ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f2f3ff'
  surface-container: '#eaedff'
  surface-container-high: '#e2e7ff'
  surface-container-highest: '#dae2fd'
  on-surface: '#131b2e'
  on-surface-variant: '#42474e'
  inverse-surface: '#283044'
  inverse-on-surface: '#eef0ff'
  outline: '#73777f'
  outline-variant: '#c2c7cf'
  surface-tint: '#3b6187'
  primary: '#00243f'
  on-primary: '#ffffff'
  primary-container: '#0b3a5e'
  on-primary-container: '#7fa4ce'
  inverse-primary: '#a4caf5'
  secondary: '#1816e9'
  on-secondary: '#ffffff'
  secondary-container: '#3b41ff'
  on-secondary-container: '#d9d9ff'
  tertiary: '#371c00'
  on-tertiary: '#ffffff'
  tertiary-container: '#552e00'
  on-tertiary-container: '#cf955e'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#d0e4ff'
  primary-fixed-dim: '#a4caf5'
  on-primary-fixed: '#001d34'
  on-primary-fixed-variant: '#20496e'
  secondary-fixed: '#e0e0ff'
  secondary-fixed-dim: '#bfc2ff'
  on-secondary-fixed: '#02006d'
  on-secondary-fixed-variant: '#120ee7'
  tertiary-fixed: '#ffdcbf'
  tertiary-fixed-dim: '#f9ba80'
  on-tertiary-fixed: '#2d1600'
  on-tertiary-fixed-variant: '#673d0d'
  background: '#F8FAFC'
  on-background: '#131b2e'
  surface-variant: '#dae2fd'
  border: '#E2E8F0'
  text-secondary: '#475569'
  success: '#16A34A'
  warning: '#F59E0B'
  danger: '#DC2626'
  accent-soft: '#E9F0FE'
typography:
  hero-title:
    fontFamily: Plus Jakarta Sans
    fontSize: 48px
    fontWeight: '700'
    lineHeight: '1.2'
    letterSpacing: -0.02em
  section-title:
    fontFamily: Plus Jakarta Sans
    fontSize: 32px
    fontWeight: '600'
    lineHeight: '1.3'
    letterSpacing: -0.01em
  metric-value:
    fontFamily: Plus Jakarta Sans
    fontSize: 28px
    fontWeight: '700'
    lineHeight: '1.1'
  card-title:
    fontFamily: Plus Jakarta Sans
    fontSize: 20px
    fontWeight: '600'
    lineHeight: '1.4'
  body-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: '400'
    lineHeight: '1.6'
  body-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 14px
    fontWeight: '400'
    lineHeight: '1.5'
  label:
    fontFamily: Plus Jakarta Sans
    fontSize: 12px
    fontWeight: '600'
    lineHeight: '1'
    letterSpacing: 0.05em
  hero-title-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 32px
    fontWeight: '700'
    lineHeight: '1.2'
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  container-max: 1440px
  inline-padding: 24px
  gutter: 24px
  section-gap: 64px
  card-padding: 32px
  stack-sm: 8px
  stack-md: 16px
  stack-lg: 24px
---

## Brand & Style

The brand identity is built on the pillars of **Institutional Trust**, **Market Fluidity**, and **Clarity**. It is designed for high-stakes business transactions, catering to serious entrepreneurs and institutional investors. The aesthetic is "Premium Investor-Grade," moving away from the cluttered layouts of traditional marketplaces toward the refined, high-performance feel of fintech leaders like AngelList and Acquire.

The design style is **Corporate / Modern** with a lean toward **Minimalism**. It utilizes a systematic approach to whitespace and hierarchy to ensure complex financial data remains digestible. The interface should feel "expensive" through its restraint—relying on precise alignment, generous breathing room, and a palette of deep navy and slate rather than decorative elements. Every visual choice must reinforce the security and professional caliber of the marketplace.

## Colors

The color strategy is anchored by a deep **Midnight Navy** (#0B3A5E) as the primary brand color, representing stability and expertise. We introduce a high-energy **Investment Blue** (#3B41FF) as a secondary accent to denote action and modern tech-driven growth, inspired by top-tier M&A platforms.

The semantic palette is strictly functional:
- **Success (Green):** Used for verified badges, positive growth metrics, and "Closed" status.
- **Warning (Amber):** Used for pending actions or expiring listings.
- **Danger (Red):** Reserved for destructive actions or critical missing information.
- **Surface/Background:** The light-grey background creates a "paper-like" feel that allows white surface cards to pop, reinforcing the data-centric nature of the product.

## Typography

This design system uses **Plus Jakarta Sans** across all roles to achieve a modern, geometric, and welcoming personality. The typography scales from impactful hero statements for marketing to ultra-legible small labels for financial disclosures.

- **Financial Metrics:** Use `metric-value` for numbers like revenue, EBITDA, and asking price. It is designed to be the focal point of listing cards.
- **Hierarchy:** Use the `label` style for metadata tags (e.g., INDUSTRY, FOUNDED) to create a clear visual distinction from the data itself.
- **Readability:** Body text uses a 1.6 line height to ensure long "Business Overview" descriptions remain comfortable to read on high-resolution screens.

## Layout & Spacing

The layout follows a **Fixed Grid** philosophy with a maximum container width of 1440px. This ensures that on ultra-wide monitors, the financial data does not stretch to an illegible degree.

- **Horizontal Rhythm:** A standard 12-column grid is used. Main content typically occupies an 8-column span, while sidebars (actions/verification) occupy a 4-column span.
- **Vertical Rhythm:** Sections are separated by a generous 64px gap to provide clear mental separation between "Investment Snapshot," "Financial Performance," and "Seller Verification."
- **Mobile Adaptivity:** At 768px, the layout reflows to a single column. Inline padding remains at 24px, and card padding reduces to 16px to conserve screen real estate.

## Elevation & Depth

This design system avoids heavy shadows and skeuomorphism. Depth is achieved through **Tonal Layers** and **Subtle Ambient Shadows**.

1.  **Level 0 (Background):** `#F8FAFC` - The base canvas.
2.  **Level 1 (Surface):** `#FFFFFF` - All cards, input fields, and containers. These use a very subtle shadow (`0 1px 3px rgba(0,0,0,.05)`) to create a lift from the background without feeling heavy.
3.  **Level 2 (Active/Hover):** For interactive cards, the shadow deepens slightly on hover to `0 4px 12px rgba(0,0,0,.08)` and moves up 2px.
4.  **Floating Elements:** Modals and dropdowns use a more pronounced shadow to signify a different z-index layer, but still maintain the 5-8% opacity range to keep the "light and airy" feel.

## Shapes

The shape language is "Softly Geometric." We use three tiers of corner radii to establish a logical hierarchy of elements:

- **Hero & Primary Containers:** 16px radius. Used for the main listing header and featured banners.
- **Standard Cards & Components:** 12px radius. The default for all white surface containers.
- **Buttons & Small UI:** 10px radius. Used for CTA buttons and input fields to provide a slightly more "refined" and "clickable" look than the larger containers.
- **Chips/Badges:** Fully pill-shaped (100px) to distinguish them from interactive buttons.

## Components

### Buttons
- **Primary:** Background `#0B3A5E`, Text `#FFFFFF`, 10px radius. Use for "Add Listing" or "Contact Seller."
- **Secondary:** Border `1px solid #E2E8F0`, Text `#0F172A`. Use for "Save" or "Share."
- **Social:** White background with a subtle border and brand icons (Google/LinkedIn) for secure authentication.

### Cards
- White surface, 12px radius, 1px `#E2E8F0` border. 
- **Snapshot Cards:** Use a light grey background (`#F8FAFC`) for the internal data grid to separate it from the card's header.

### Input Fields
- 10px radius, 1px `#E2E8F0` border.
- Active state: Border changes to `#0B3A5E` with a subtle focus ring.
- Placeholder text: Use `#475569` for legibility.

### Verification Badges
- Utilize small, high-contrast icons (checkmarks) with light tinted backgrounds (e.g., soft green for "Verified Business"). This provides immediate visual confirmation of trust.

### Data Visualization
- Charts should use the primary `#0B3A5E` and accent `#3B41FF` for series data.
- Grid lines must be ultra-faint (`#F1F5F9`) to keep the focus on the trend lines.