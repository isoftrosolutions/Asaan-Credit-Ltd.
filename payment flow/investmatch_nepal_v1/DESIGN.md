---
name: InvestMatch Nepal v1
colors:
  surface: '#fcf9f8'
  surface-dim: '#dcd9d9'
  surface-bright: '#fcf9f8'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f6f3f2'
  surface-container: '#f0eded'
  surface-container-high: '#eae7e7'
  surface-container-highest: '#e5e2e1'
  on-surface: '#1c1b1b'
  on-surface-variant: '#554242'
  inverse-surface: '#313030'
  inverse-on-surface: '#f3f0ef'
  outline: '#887271'
  outline-variant: '#dbc0bf'
  surface-tint: '#9b4144'
  primary: '#4d060f'
  on-primary: '#ffffff'
  primary-container: '#6b1d22'
  on-primary-container: '#f08484'
  inverse-primary: '#ffb3b2'
  secondary: '#3b6281'
  on-secondary: '#ffffff'
  secondary-container: '#b1d9fd'
  on-secondary-container: '#385f7e'
  tertiary: '#00263f'
  on-tertiary: '#ffffff'
  tertiary-container: '#003d60'
  on-tertiary-container: '#76a8d5'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#ffdad9'
  primary-fixed-dim: '#ffb3b2'
  on-primary-fixed: '#410008'
  on-primary-fixed-variant: '#7d2a2e'
  secondary-fixed: '#cce5ff'
  secondary-fixed-dim: '#a4cbef'
  on-secondary-fixed: '#001e31'
  on-secondary-fixed-variant: '#214a68'
  tertiary-fixed: '#cde5ff'
  tertiary-fixed-dim: '#9accfa'
  on-tertiary-fixed: '#001d32'
  on-tertiary-fixed-variant: '#0a4a72'
  background: '#fcf9f8'
  on-background: '#1c1b1b'
  surface-variant: '#e5e2e1'
typography:
  headline-xl:
    fontFamily: Montserrat
    fontSize: 40px
    fontWeight: '800'
    lineHeight: 48px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Montserrat
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    letterSpacing: -0.01em
  headline-md:
    fontFamily: Montserrat
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  headline-sm:
    fontFamily: Montserrat
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-md-bold:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '600'
    lineHeight: 24px
  body-sm:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-md:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.05em
  headline-xl-mobile:
    fontFamily: Montserrat
    fontSize: 32px
    fontWeight: '800'
    lineHeight: 38px
  headline-lg-mobile:
    fontFamily: Montserrat
    fontSize: 28px
    fontWeight: '700'
    lineHeight: 34px
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  xs: 4px
  sm: 8px
  md: 16px
  lg: 24px
  xl: 32px
  xxl: 48px
  gutter: 24px
  margin: 24px
---

## Brand & Style

The design system is built on the pillar of **"Trust. Clarity. Action."** It targets investors and entrepreneurs within the Nepalese ecosystem, necessitating a visual language that feels both authoritative and accessible. 

The style is **Corporate / Modern** with a refined financial edge. It utilizes premium white space to denote transparency and "warm confidence" through a sophisticated palette. The aesthetic avoids the coldness of typical fintech by integrating rich burgundy and navy tones, creating a high-contrast environment that guides users toward decisive actions. Signature wave dividers—using gradients from burgundy to navy—soften the rigid structure of financial data, adding a sense of momentum and growth.

## Colors

The palette is anchored by **Burgundy (#6B1D22)**, used for primary calls-to-action and essential brand moments to evoke passion and confidence. **Navy (#1E4866)** provides a grounding secondary force for headers and navigation, ensuring the UI feels stable and professional.

**Accent Blue (#205880)** is reserved specifically for the "Verified Badge" and trust-related indicators. Feedback loops use high-visibility tones: **Vivid Primary** for errors/destructive actions, **Success Green** for completions, and **Warning Gold** for pending states. The background is kept strictly white to maximize legibility and a sense of cleanliness.

## Typography

This design system uses a dual-font strategy. **Montserrat** is the display face, providing a bold, geometric authority for headings. H1 elements must use the ExtraBold (800) weight to establish immediate hierarchy. 

**Inter** handles all functional UI and body text. It is chosen for its exceptional legibility in data-heavy environments. Use SemiBold (600) for sub-headers or emphasized body text to ensure it stands out against the neutral backgrounds. Labels and small metadata should use uppercase with slight letter spacing to maintain a "refined financial" feel.

## Layout & Spacing

A **Base-4** system governs all measurements. Layouts follow a **12-column fixed grid** on desktop (max-width 1200px) and a fluid 4-column grid on mobile. 

- **Desktop:** 24px gutters, 24px margins.
- **Tablet:** 16px gutters, 16px margins.
- **Mobile:** 16px gutters, 16px margins.

Section fills using the "Soft Background" (#F8F8F8) should always span the full width of the viewport to create clear horizontal zoning. Vertical spacing between sections should ideally be `xxl` (48px) to allow the design to breathe.

## Elevation & Depth

Visual hierarchy is managed through **Tonal Layers** combined with **Ambient Shadows**. The interface uses a three-tier shadow system to define depth:

- **sm:** 0px 2px 4px rgba(0,0,0,0.05). Used for cards and secondary buttons.
- **md:** 0px 4px 12px rgba(0,0,0,0.08). Used for dropdowns and sticky navigation.
- **lg:** 0px 12px 24px rgba(0,0,0,0.12). Used for modals and floating action elements.

The navigation bar is a "Sticky Top Bar," rendered in pure white with a `shadow-sm` to separate it from the content scrolling beneath.

## Shapes

The shape language is "Rounded" to balance the corporate seriousness with approachable warmth. Standard UI components (Inputs, Buttons, Cards) use a **radius-md (8px)**. Larger layout containers or featured marketing cards should use **radius-lg (16px)** to emphasize the "warm confidence" of the brand.

## Components

### Buttons
- **Primary:** Solid Burgundy (#6B1D22) with White text. High emphasis.
- **Secondary:** Solid Navy (#1E4866) with White text. Medium emphasis.
- **Outline:** Burgundy 1px border with Burgundy text. Used for secondary actions on white backgrounds.
- **Ghost:** Navy text with no background. Used for tertiary actions or within navigation.

### Cards
Cards are the primary container for information. They must be Pure White (#FFFFFF) with a `shadow-sm` and `radius-md`. Hover states should transition to `shadow-md` for interactivity.

### Inputs & Fields
Inputs use a 1px border in #E2E2E2. On focus, the border transitions to Navy (#1E4866) with a subtle 2px outer glow in the same color at 10% opacity.

### Verified Badge
A critical component for trust: A small Blue ShieldCheck icon using Accent Blue (#205880), typically placed next to user or company names in headings.

### Chips & Tags
Used for industry categories (e.g., "SaaS", "Agriculture"). These use a background of `surface-soft` (#F8F8F8) with `text-muted` (#5A5A5A) and `radius-md`.

### Wave Divider
A decorative motif used to separate major homepage sections. It consists of a gentle organic curve featuring a gradient from #6B1D22 to #1E4866, providing a visual bridge between the brand's primary colors.