---
name: lapicheck-ui
description: Use for UI/UX design tasks in LapiCheck. Covers the Tailwind design system, color palette, typography (Inter font), spacing, layout components, responsive design, dark mode, Material Symbols icons, and UX flow. Use when the user mentions "design", "UI", "UX", "warna", "layout", "responsive", "dark mode", "font", "icon", "style", or "tampilan".
---

# LapiCheck UI/UX Design System

## Color Palette (Tailwind)

All colors are defined in `tailwind.config.ts` (build-time PostCSS) and no longer use CDN runtime Tailwind.

| Token | Hex | Usage |
|-------|-----|-------|
| `primary` / `on-primary` | `#002045` / `#ffffff` | Primary actions, header |
| `surface` / `on-surface` | `#faf9fd` / `#1a1c1e` | Main background |
| `surface-container` | `#efedf1` | Cards, elevated surfaces |
| `surface-container-high` | `#e9e7eb` | Hovered/active cards |
| `surface-container-highest` | `#e3e2e6` | Highest elevation |
| `secondary` / `on-secondary` | `#555f71` / `#ffffff` | Secondary elements |
| `error` / `on-error` | `#ba1a1a` / `#ffffff` | Errors, validation |
| `outline` / `outline-variant` | `#74777f` / `#c4c6cf` | Borders, dividers |

## Typography

```css
/* Font stacks — all use Inter */
font-family: h1/h2/h3/body-lg/body-md/caption/label-bold: ["Inter"]

/* Font sizes — defined as responsive tokens */
h1:   40px, w800, -0.02em  /* Hero titles */
h2:   30px, w700, -0.01em  /* Section headers */
h3:   24px, w700           /* Card titles */
body-lg: 18px, w400        /* Large body text */
body-md: 16px, w400        /* Default body text */
label-bold: 14px, w600, 0.05em  /* Labels, buttons */
caption: 12px, w500        /* Small captions */
```

## Spacing Grid

| Token | Value | Usage |
|-------|-------|-------|
| `xs` | 4px | Tiny gaps |
| `unit` | 4px | Base unit |
| `sm` | 8px | Small padding |
| `md` | 16px | Standard padding |
| `gutter` | 20px | Grid gaps |
| `lg` | 24px | Section spacing |
| `margin` | 32px | Page margins |
| `xl` | 40px | Large sections |

## Border Radius

| Token | Value | Usage |
|-------|-------|-------|
| Default | `0.125rem` (2px) | Buttons, inputs |
| `lg` | `0.25rem` (4px) | Cards |
| `xl` | `0.5rem` (8px) | Modals |
| `full` | `0.75rem` (12px) | Pills, badges |

## Dark Mode

Toggle via `darkMode: "class"` — apply `class="dark"` on `<html>`.

## Icons

Material Symbols Outlined from Google Fonts.

## Key Files

| File | Purpose |
|------|---------|
| `FrontendService/tailwind.config.ts` | Master design tokens |
| `FrontendService/index.html` | Font & icon CDN links, base `<body>` classes |
| `FrontendService/src/assets/css/tailwind.css` | `@tailwind base/components/utilities` |
| `FrontendService/src/assets/css/app.css` | Global imports + custom animations (`status-pulse`) |
| `FrontendService/src/assets/css/lyp.css` | Additional custom styles |
| `FrontendService/src/layouts/` | App layout structure |
| `FrontendService/src/views/` | Page components |

## Design Rules

- Use semantic color tokens (`bg-surface-container`, `text-on-surface`) — never hardcode hex values in components
- Maintain dark mode compatibility for all new components
- Keep padding consistent using the spacing grid above
- Use `label-bold` for all interactive elements (buttons, links, form labels)
- Status indicators use the `status-pulse` animation class
- All pages extend the `AppLayout` component
