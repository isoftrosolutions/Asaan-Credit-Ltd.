# Asaan Capital — Design System (`design.md`)

> **Single source of truth** for all UI/UX and front-end development.
> Brand: **Asaan Capital Limited** — Investment & Capital Solutions, Nepal.
> Tagline: *"Trusted Capital, Secure Future."*
> Brand personality (derived from assets): **trustworthy, established, secure, growth-minded, approachable.** A shield (security) fused with an upward arrow (growth) and a serif wordmark (heritage/credibility), softened by warm, friendly touches. Visual tone = *premium-but-accessible financial services.*
>
> _Note: All HEX values were sampled directly from the supplied logo and poster artwork. Typography, component specs, iconography, and motion are professional decisions inferred from the brand's visual tone and are flagged `(inferred)` where they were not literally present in the assets._

---

## 1. Brand Colors

### Core palette (sampled from assets)

| Role | Name | HEX | Where it appears in the assets |
|------|------|-----|--------------------------------|
| **Primary** | Burgundy | `#6B1D22` | Wordmark "ASAAN CAPITAL", decorative wave bands, brand anchor |
| **Primary (vivid)** | Brand Red | `#98202A` | Logo "C", glossy center coin, all section headings (OUR SERVICES, ABOUT US, etc.) |
| **Secondary** | Steel Navy | `#1E4866` | Poster wave bands, "Mission" chip, building signage, deep blue accents |
| **Accent (brand blue)** | Steel Blue | `#205880` | Logo "A" growth-arrow, shield outline (lighter, cooler blue) |
| **Neutral – silver** | Silver | `#C3C6C5` | Shield inner ring / metallic edge |
| **Neutral – light** | Light Grey | `#ECECEC` | Table-of-contents bars, dividers, logo backdrop |
| **Neutral – mid** | Slate Grey | `#5A5A5A` | TOC item labels, muted/secondary text |
| **Background – base** | White | `#FFFFFF` | Primary poster/page background |
| **Background – soft** | Off-White | `#F8F8F8` | Section fills, card surfaces |
| **Text – heading** | Near-Black | `#1A1A1A` | Body copy headings, company name |
| **Text – body** | Charcoal | `#2A2A2A` | Long-form paragraph text |
| **Text – inverse** | White | `#FFFFFF` | Text on burgundy / navy fills |

### Decorative-only accent _(inferred — use sparingly)_

| Name | HEX | Notes |
|------|-------|-------|
| Playful Azure | `#2E72D6` | The 3D "THANK YOU" word-art. **One-off illustrative element — not part of the core UI palette.** Keep out of buttons, links, and system states to protect the refined navy. |

### Usage ratio (60-30-10)
- **60% Neutral** — white / off-white surfaces, generous breathing room.
- **30% Primary** — burgundy for brand presence, headings, key surfaces.
- **10% Secondary/Accent** — navy + brand red for CTAs, highlights, data points.

### Tonal ramps (derived from the two brand hues — for hovers, states, charts)

**Burgundy / Red**
```
--burgundy-50:  #F6EAEB
--burgundy-100: #E7C9CB
--burgundy-300: #C46B70
--burgundy-500: #98202A   /* brand red — vivid */
--burgundy-700: #6B1D22   /* PRIMARY — base */
--burgundy-900: #4A1317
```

**Steel Blue / Navy**
```
--navy-50:  #EAF0F5
--navy-100: #C9DAE6
--navy-300: #6E96B3
--navy-500: #205880   /* steel blue — logo */
--navy-700: #1E4866   /* SECONDARY — base */
--navy-900: #12304A
```

### Functional / system colors _(inferred — tuned to brand)_

| State | HEX |
|-------|-----|
| Success | `#1E7A4D` |
| Warning | `#C77A12` |
| Error / Danger | `#98202A` _(reuses brand red — intentional)_ |
| Info | `#205880` _(reuses steel blue)_ |

### Accessibility notes
- `#6B1D22`, `#98202A`, `#1E4866` on `#FFFFFF` → all pass **WCAG AA** for normal text and **AAA** for large text.
- `#5A5A5A` on white passes AA for normal text. Do **not** use Silver `#C3C6C5` for text — borders/decoration only.
- White text on `#205880` is borderline for small text; reserve steel blue for ≥16px bold or large surfaces.

### CSS custom properties (drop-in)
```css
:root {
  --color-primary:        #6B1D22;
  --color-primary-vivid:  #98202A;
  --color-secondary:      #1E4866;
  --color-accent-blue:    #205880;

  --color-silver:         #C3C6C5;
  --color-grey-light:     #ECECEC;
  --color-grey-mid:       #5A5A5A;
  --color-border:         #E2E2E2;

  --color-bg:             #FFFFFF;
  --color-bg-soft:        #F8F8F8;

  --color-text:           #2A2A2A;
  --color-text-heading:   #1A1A1A;
  --color-text-muted:     #5A5A5A;
  --color-text-inverse:   #FFFFFF;

  --color-success: #1E7A4D;
  --color-warning: #C77A12;
  --color-error:   #98202A;
  --color-info:    #205880;
}
```

---

## 2. Typography

The assets show a **three-voice type system**: an engraved serif for the wordmark, a heavy geometric sans for headings, and a clean readable sans for body. Specific font names are `(inferred)` matches to the artwork — swap for the licensed originals if the brand owns them.

### Font families
| Voice | Recommended (Google Fonts) | Why it matches the assets | Fallback stack |
|-------|---------------------------|---------------------------|----------------|
| **Brand / Wordmark serif** | **Cinzel** _(inferred)_ | Matches the classical, engraved small-caps "ASAAN CAPITAL" + "TRUSTED CAPITAL, SECURE FUTURE" tagline. Use **only** in the logo lockup and ceremonial brand moments. | `'Cinzel', 'Trajan Pro', Georgia, serif` |
| **Headings / Display** | **Montserrat** _(inferred)_ | Heavy, geometric, uppercase — matches "OUR SERVICES / VISION & MISSION / ABOUT US". | `'Montserrat', 'Poppins', system-ui, sans-serif` |
| **Body / UI** | **Inter** _(inferred)_ | Neutral, highly legible at small sizes for digital long-form (posters used a bold geometric sans; Inter is the screen-optimised equivalent). | `'Inter', 'Poppins', system-ui, sans-serif` |

> Display one-offs in the posters ("COMPANY PROFILE" brush font, the "THANK YOU" balloon font) are **decorative and not part of the system** — do not reproduce them in product UI.

### Type scale (1.250 — Major Third, 16px base)
| Token | Element | Size (rem / px) | Weight | Line-height | Letter-spacing | Case |
|-------|---------|------------------|--------|-------------|----------------|------|
| `--text-h1` | **H1** | 3.05rem / 49px | 800 (ExtraBold) | 1.1 | -0.5px | UPPERCASE |
| `--text-h2` | **H2** | 2.44rem / 39px | 800 | 1.15 | -0.25px | UPPERCASE |
| `--text-h3` | **H3** | 1.95rem / 31px | 700 (Bold) | 1.2 | 0 | Title Case |
| `--text-h4` | **H4** | 1.56rem / 25px | 700 | 1.25 | 0 | Title Case |
| `--text-lg` | Lead / intro | 1.25rem / 20px | 500 | 1.5 | 0 | Sentence |
| `--text-base` | **Body** | 1rem / 16px | 400 | 1.6 | 0 | Sentence |
| `--text-sm` | **Caption** | 0.8rem / 13px | 400 | 1.5 | 0.1px | Sentence |
| `--text-xs` | **Label** | 0.75rem / 12px | 600 (SemiBold) | 1.4 | 0.6px | UPPERCASE |

- **Headings** → Montserrat, weights 700/800. Section titles uppercase, mirroring the posters.
- **Body** → Inter 400, with 500 for emphasis. Posters set body fairly bold; on screen keep body at 400 and reserve 600+ for emphasis to avoid fatigue.
- **Labels / overlines** → Inter 600, uppercase, slight tracking (matches the small-caps tagline rhythm).

```css
:root {
  --font-brand:   'Cinzel', 'Trajan Pro', Georgia, serif;
  --font-heading: 'Montserrat', 'Poppins', system-ui, sans-serif;
  --font-body:    'Inter', 'Poppins', system-ui, sans-serif;

  --text-h1: 3.05rem; --text-h2: 2.44rem; --text-h3: 1.95rem;
  --text-h4: 1.56rem; --text-lg: 1.25rem; --text-base: 1rem;
  --text-sm: 0.8rem;  --text-xs: 0.75rem;
}
```
> On screens < 768px, scale headings down one step (e.g. H1 → ~32px) using a fluid `clamp()` for responsiveness.

---

## 3. Spacing & Layout

The posters are built on **ISO A-series portrait** proportions with generous, confident margins and a clear vertical rhythm. The system below standardises that into an **8px base grid** _(inferred from the consistent poster gutters)_.

### Spacing scale (base unit = 8px, 4px half-step)
| Token | px | Typical use |
|-------|----|-------------|
| `--space-1` | 4 | Icon-to-label gaps, tight chips |
| `--space-2` | 8 | Inner padding, small gaps |
| `--space-3` | 12 | Compact component padding |
| `--space-4` | 16 | Default element padding / gap |
| `--space-5` | 24 | Card padding, paragraph spacing |
| `--space-6` | 32 | Section inner padding |
| `--space-8` | 48 | Block separation |
| `--space-10` | 64 | Section vertical padding (desktop) |
| `--space-12` | 96 | Hero / major section breaks |

### Grid system
- **12-column** fluid grid.
- **Gutter:** 24px (desktop) / 16px (mobile).
- **Outer margins:** 64px (desktop), 24px (tablet), 16px (mobile) — echoing the wide poster margins.

### Containers (max-width)
| Token | Max-width | Use |
|-------|-----------|-----|
| `--container-sm` | 640px | Forms, focused reading |
| `--container-md` | 768px | Article / long-form body |
| `--container-lg` | 1024px | Standard content pages |
| `--container-xl` | 1280px | Marketing / dashboard shells |
| `--container-full` | 100% (padded) | Full-bleed wave/banner sections |

### Radius & elevation
| Token | Value | Use |
|-------|-------|-----|
| `--radius-sm` | 6px | Inputs, small chips |
| `--radius-md` | 10px | Buttons, cards _(matches rounded poster bars)_ |
| `--radius-lg` | 16px | Modals, large cards |
| `--radius-pill` | 999px | Badges, tags, social chips |
| `--shadow-sm` | `0 1px 2px rgba(26,26,26,.06)` | Resting cards |
| `--shadow-md` | `0 4px 12px rgba(26,26,26,.10)` | Hover / raised cards |
| `--shadow-lg` | `0 12px 32px rgba(26,26,26,.14)` | Modals, popovers |

```css
:root {
  --space-1:4px; --space-2:8px; --space-3:12px; --space-4:16px;
  --space-5:24px; --space-6:32px; --space-8:48px; --space-10:64px; --space-12:96px;
  --container-xl:1280px; --container-lg:1024px; --container-md:768px;
  --radius-sm:6px; --radius-md:10px; --radius-lg:16px; --radius-pill:999px;
}
```

---

## 4. Components

Component styling is _(inferred)_ from the brand tone — the asset system already establishes the cues: **rounded bars** (TOC), **diamond/hex number chips alternating burgundy & navy**, soft full-bleed **wave dividers**, and a clean outlined **"Call Now"** treatment.

### Buttons
| Variant | Fill | Text | Border | Radius | Use |
|---------|------|------|--------|--------|-----|
| **Primary** | `#6B1D22` | `#FFFFFF` | none | `--radius-md` | Main CTAs ("Invest with us", "Contact") |
| **Primary-vivid** | `#98202A` | `#FFFFFF` | none | `--radius-md` | High-emphasis / promotional CTA |
| **Secondary** | `#1E4866` | `#FFFFFF` | none | `--radius-md` | Alternate actions |
| **Outline** | transparent | `#6B1D22` | 1.5px `#6B1D22` | `--radius-md` | Low-emphasis ("Learn more") |
| **Ghost** | transparent | `#1E4866` | none | `--radius-md` | Tertiary / in-card actions |

- **Padding:** 12px 24px (default), 8px 16px (small), 16px 32px (large).
- **States:** hover → darken one ramp step (`#6B1D22` → `#4A1317`); focus → 3px `rgba(32,88,128,.4)` ring; disabled → 40% opacity, no shadow.
- **Label:** Inter 600, 15–16px, no all-caps for buttons (keep accessible/friendly).

### Cards
- Surface `#FFFFFF`, radius `--radius-lg`, `--shadow-sm` (→ `--shadow-md` on hover), padding `--space-5`.
- Optional **left accent bar** (4px, burgundy or navy) to categorise — mirrors the colored TOC number chips.
- Card title H4 (Montserrat 700, `#1A1A1A`); body Inter 400 `#2A2A2A`.

### Input fields
- Background `#FFFFFF`; border 1.5px `#E2E2E2`; radius `--radius-sm`; padding 12px 14px.
- **Focus:** border `#205880` + 3px `rgba(32,88,128,.25)` ring.
- **Error:** border `#98202A`, helper text `#98202A`.
- Label: Inter 600 13px `#1A1A1A`, 6px above field. Placeholder `#9A9A9A`.

### Navigation
- **Top bar:** white `#FFFFFF`, `--shadow-sm`, height 64–72px; logo left, links right.
- Link rest `#2A2A2A`; hover/active `#6B1D22` with a 2px burgundy underline indicator.
- **Mobile:** hamburger → slide-in drawer, white surface, burgundy active items.
- Optional accent: a thin burgundy→navy gradient rule under the bar, echoing the poster waves.

### Badges / Tags
- **Pill** (`--radius-pill`), Inter 600 12px uppercase.
- Number/step badges → **diamond or hex**, alternating `#6B1D22` and `#1E4866` with white numerals (directly from the Table-of-Contents pattern).
- Status badges use the functional colors at 12% tint background + solid text.

### Modals / Dialogs
- Surface `#FFFFFF`, radius `--radius-lg`, `--shadow-lg`, max-width 560px, padding `--space-6`.
- **Header:** H4 in `#1A1A1A`, optional 4px burgundy top accent or a slim wave motif.
- Overlay: `rgba(26,26,26,.55)`.
- Primary action = burgundy button (right), secondary = ghost/outline (left). Close "×" top-right in `#5A5A5A`.

### Signature brand motif
- Reusable **wave divider** (burgundy + navy interlocking curves) as an SVG section separator — already the brand's most recognisable layout device. Provide as a top/bottom full-bleed component.

---

## 5. Iconography

| Attribute | Decision |
|-----------|----------|
| **Style** | **Outlined, rounded** _(inferred)_ — matches the line-drawn "Call Now" handset and the soft, friendly curves of the wave system. Avoid sharp/technical icon sets. |
| **Stroke weight** | ~1.75–2px, consistent across the set |
| **Corner** | Rounded line caps & joins |
| **Recommended library** | **Lucide** (primary) — clean, rounded, open-source, huge coverage. |
| **Alternative** | **Phosphor Icons** (Regular / Duotone) when a slightly warmer, friendlier look is wanted. |
| **Sizing** | 16 / 20 / 24 / 32px on the 8px grid. Default inline = 20px. |
| **Color** | Inherit text color; brand burgundy `#6B1D22` or navy `#1E4866` for emphasis/active. |
| **Social icons** | Use each platform's **official brand glyphs** in their native brand colors (as shown on the contact poster) — do not recolor third-party logos. |

> Keep filled icons only for tiny indicators (status dots, active states) where outlines lose legibility. The shield + arrow logo mark may be used as a favicon / app icon and as a subtle background watermark.

---

## 6. Motion & Animation

**Animation tone: subtle & confident** _(inferred from brand personality)_ — a financial/investment brand promising a *"Secure Future"* should feel **smooth, deliberate, and reassuring**, never bouncy or gimmicky. A small amount of warmth is on-brand (the company isn't coldly corporate), so micro-interactions can have gentle life — but restraint is the rule.

### Duration tokens
| Token | Value | Use |
|-------|-------|-----|
| `--motion-fast` | 120ms | Hovers, color/opacity changes, small toggles |
| `--motion-base` | 220ms | Buttons, dropdowns, tabs, most UI transitions |
| `--motion-slow` | 320ms | Modals, drawers, accordions, cards |
| `--motion-page` | 450ms | Page / route transitions, hero reveals |

### Easing curves
| Token | cubic-bezier | Use |
|-------|--------------|------|
| `--ease-standard` | `cubic-bezier(0.4, 0, 0.2, 1)` | Default for most movement (smooth in-out) |
| `--ease-out` | `cubic-bezier(0.0, 0, 0.2, 1)` | Elements entering (menus, toasts) |
| `--ease-in` | `cubic-bezier(0.4, 0, 1, 1)` | Elements leaving |
| `--ease-emphasis` | `cubic-bezier(0.2, 0.8, 0.2, 1)` | One subtle "lift" on key CTAs / hero — the only place with a hint of spring |

### Patterns
- **Buttons/links:** color + 1–2px lift on hover (`--motion-fast`, `--ease-standard`); never scale-bounce.
- **Cards:** shadow `sm → md` + 2px translateY on hover (`--motion-base`).
- **Modals/drawers:** fade overlay + slide/scale-from-98% panel (`--motion-slow`, `--ease-out`).
- **Scroll reveals:** gentle fade-up (16px, `--motion-page`), staggered ~60ms — ideal for the wave-section + heading combo. Use once per element; no looping.
- **Brand flourish:** the wave dividers may animate in with a slow horizontal draw/clip on first view — keep it understated and one-time.

### Accessibility
Always honour reduced-motion:
```css
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.001ms !important;
    transition-duration: 0.001ms !important;
    scroll-behavior: auto !important;
  }
}
```

```css
:root {
  --motion-fast:120ms; --motion-base:220ms; --motion-slow:320ms; --motion-page:450ms;
  --ease-standard:cubic-bezier(0.4,0,0.2,1);
  --ease-out:cubic-bezier(0,0,0.2,1);
  --ease-in:cubic-bezier(0.4,0,1,1);
  --ease-emphasis:cubic-bezier(0.2,0.8,0.2,1);
}
```

---

*End of `design.md` — Asaan Capital design system. Colors are asset-accurate; typography, components, iconography, and motion are professional recommendations aligned to the brand's secure-yet-approachable financial-services personality.*
