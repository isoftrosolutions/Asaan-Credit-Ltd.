# Multi-Step Onboarding Form — Design Spec

## Overview

A modern, production-ready 4-step onboarding wizard for Asaan Capital Ltd, built with the existing vanilla PHP + vanilla JS stack. New users complete account setup, company details, preferences, and review before being registered.

## Architecture

Hybrid approach: PHP renders all 4 steps in one page; JS toggles visibility, handles client-side validation, and drives transitions. Final submit sends all data in one POST.

### Files

| File | Action | Purpose |
|---|---|---|
| `index.php` | Edit | Add `'/onboarding' => 'pages/onboarding.php'` route |
| `pages/onboarding.php` | **Create** | Page shell + 4 step render functions + POST handler |
| `assets/onboarding.js` | **Create** | Step manager, validation, transitions, progress bar |
| `assets/styles.css` | Append | Step progress bar, step panels, animations |

## Form Structure

### Step 1: Account Setup
- Full name (text, required)
- Email (email, required, format validation)
- Password (password, required, min 8 chars, show/hide toggle)

### Step 2: Company Details
- Company name (text, required)
- Role (select, required — options: Owner/Founder, CEO, CFO/Finance, Investment Manager, Broker/Advisor, Other)
- Company size (select, required — options: 1-10, 11-50, 51-200, 201-1000, 1000+)

### Step 3: Preferences
- Usage goal (radio cards — Buy a Business, Sell a Business, Raise Investment, Invest in Startups, Franchise, Advisory)
- Notifications (checkbox toggle — Email notifications, Product updates)

### Step 4: Review & Submit
- Summary table of all inputs
- Agree to Terms checkbox
- Submit button

## UX Flow

1. User visits `/onboarding` → PHP renders page, Step 1 visible, steps 2-4 hidden
2. JS initializes: progress bar, step manager, validation listeners
3. User fills Step 1 → clicks "Next" → JS validates → shows Step 2, progress advances
4. User can click "Back" at any time to edit previous steps (data preserved in DOM)
5. Step 4 shows review summary populated from current field values
6. Submit → JS collects data → `fetch` POST to same URL
7. PHP validates → redirects to `/dashboard` with flash on success, or re-renders with errors

## Validation

- **Client-side**: On blur + on step change. Error messages below each field. "Next" disabled if errors.
- **Server-side**: Same validations in PHP. Returns `$errors` array, re-renders page with error styling.

## Progress Indicator

- Horizontal 4-segment bar at top
- States: `pending` (gray), `active` (primary maroon), `completed` (green with checkmark)
- Responsive: labels hidden on mobile, only numbers visible

## Transitions

- Next: slide-left (translateX -30px → 0, opacity 0→1, 250ms)
- Back: slide-right (translateX 30px → 0, opacity 0→1, 250ms)
- CSS transitions, no animation library

## CSS Variables Used

All existing design tokens: `--color-primary`, `--color-success`, `--color-error`, `--color-border`, `--radius-md`, `--shadow-sm`, `--motion-base`, `--ease-standard`, etc.

## State Management

Fields live in the DOM. JS reads values directly from inputs on step change (for validation) and on final submit. Back button shows previously entered data automatically.

## Edge Cases

- **JS disabled**: Page renders all steps; JS-less fallback uses a hidden step input + PHP handles step transitions via POST (same as existing signup)
- **Browser back**: Data preserved in DOM (all 4 steps are in the page)
- **Submit failure**: Server returns errors, page re-renders with error messages and user data
- **Empty form**: All required fields validated before next step; form cannot be submitted empty
