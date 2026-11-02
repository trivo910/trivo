# Admin Backend Design — Glassmorphism

Applies to the **admin panel only** (`/{ADMIN_PATH}`). The public site keeps the light, rounded Trivo design from `DESIGN-trivo-in.md`. The admin shares the same brand colours and Montserrat font so both feel like one product.

## Look & feel
Frosted-glass panels floating over a soft, colourful mesh-gradient background. Light theme by default, optional dark theme (toggle, saved per user). Calm, premium, uncluttered; data stays readable above all.

## Design tokens (Tailwind theme + CSS variables)
```css
:root {
  /* background mesh */
  --bg-base: #f4f6fb;
  --bg-mesh:
    radial-gradient(40rem 30rem at 10% 0%,  rgba(0,106,206,.35), transparent 60%),
    radial-gradient(35rem 28rem at 90% 10%, rgba(255,190,17,.30), transparent 60%),
    radial-gradient(40rem 32rem at 50% 100%, rgba(233,229,255,.90), transparent 60%);
  /* glass surfaces */
  --glass-bg:        rgba(255,255,255,.55);
  --glass-bg-strong: rgba(255,255,255,.75);   /* tables, forms, editor */
  --glass-border:    rgba(255,255,255,.65);
  --glass-shadow:    0 8px 32px rgba(34,22,119,.12);
  --glass-blur:      16px;
  --glass-saturate:  160%;
  /* brand */
  --primary: #006ace; --primary-600: #004b8a; --accent: #ffbe11;
  --text: #0b1220; --text-muted: #4b5563;      /* meets AA on glass */
  --success:#16a34a; --warning:#f59e0b; --danger:#dc2626;
  --radius-card: 24px; --radius-control: 14px; --radius-pill: 999px;
}
[data-theme="dark"] {
  --bg-base:#0b1020;
  --glass-bg: rgba(20,26,48,.55);
  --glass-bg-strong: rgba(20,26,48,.78);
  --glass-border: rgba(255,255,255,.12);
  --glass-shadow: 0 8px 32px rgba(0,0,0,.45);
  --text:#e8ecf8; --text-muted:#a3adc7;
}
```

## Core glass utility
```css
.glass {
  background: var(--glass-bg);
  -webkit-backdrop-filter: blur(var(--glass-blur)) saturate(var(--glass-saturate));
  backdrop-filter: blur(var(--glass-blur)) saturate(var(--glass-saturate));
  border: 1px solid var(--glass-border);
  box-shadow: var(--glass-shadow), inset 0 1px 0 rgba(255,255,255,.6);
  border-radius: var(--radius-card);
}
.glass-strong { background: var(--glass-bg-strong); }
@supports not ((backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))) {
  .glass { background: rgba(255,255,255,.92); }            /* solid fallback */
  [data-theme="dark"] .glass { background: rgba(20,26,48,.95); }
}
@media (prefers-reduced-transparency: reduce) {
  .glass { backdrop-filter: none; background: var(--glass-bg-strong); }
}
```

## Layout
- Body: `--bg-base` + fixed `--bg-mesh` (with 2–3 slow-floating blurred blobs, `prefers-reduced-motion` disables animation).
- **Sidebar**: full-height glass panel (collapsible, icon-only on tablet, off-canvas drawer on mobile), active item = primary pill with soft glow. Grouped: Content, SEO, Marketing, Media, Settings, System.
- **Topbar**: glass, sticky; global search (⌘K command palette), notifications, theme toggle, user menu.
- **Content area**: max-width 1440px, 24–30px gaps (10px grid multiples), cards radius 24px.
- Dashboard: KPI glass cards (leads, posts, avg SEO score ring, 404s), mini sparkline charts, recent leads, SEO score overview.

## Components
| Component | Spec |
|---|---|
| Cards / panels | `.glass`, padding 24px, subtle hover lift (translateY(-2px)) |
| Buttons | Primary: `#006ace` solid + white text, radius 14px, glow on hover. Secondary: glass + border. Danger: red solid. Focus ring 3px `rgba(0,106,206,.4)` |
| Inputs / selects / textarea | `.glass-strong`, 1px border, radius 14px, focus: primary border + ring; labels above, helper text muted |
| Tables | Wrapper `.glass-strong`; sticky header, zebra via 4% tint, row hover tint, sortable columns, bulk-select, pagination as pills |
| Modals / drawers | Backdrop `rgba(10,15,30,.35)` + blur(6px); panel `.glass-strong`, radius 28px |
| Dropdowns / toasts | `.glass-strong`, shadow-lg; toasts top-right, auto-dismiss 5s, ARIA live |
| Badges | Tinted pill (status: draft grey, scheduled amber, published green) |
| Tabs | Glass segmented control with sliding primary indicator |
| **SEO score ring** | Circular progress; red <50, orange 50–79, green ≥80, number in centre; checks list below with ✔ / ▲ / ✖ icons |
| **SERP preview** | White card (Google look) inside glass panel, desktop/mobile toggle |
| Editor (Tiptap) | Toolbar = glass pill bar; the writing canvas is **opaque white / near-solid** for readability |
| Media library | Masonry grid of glass tiles, drag-drop upload zone with dashed glass border, progress bars |
| Login page | Centred glass card on mesh background, logo, 2FA step as second card state |
| Empty states | Simple SVG + one CTA inside glass card |

## Readability & accessibility rules (non-negotiable)
- Text on glass must meet **WCAG AA (4.5:1)**; if the background behind is busy, use `.glass-strong`. Test light and dark.
- Never put long-form reading or dense data directly on low-opacity glass — use `.glass-strong` (≥ .75 alpha).
- Respect `prefers-reduced-motion`, `prefers-reduced-transparency`, and `prefers-color-scheme` (default theme follows OS, user can override).
- Visible keyboard focus, ARIA roles, skip link, 44px touch targets, form errors announced.
- Icons: one set (Lucide, inlined SVG), 20px, stroke 1.75.

## Performance rules
- `backdrop-filter` is GPU-heavy: **max 3 stacked blurred layers visible at once**; don't blur table rows or list items individually (blur the wrapper only); blur radius ≤ 20px; no blur on mobile data tables (use `.glass-strong` solid-ish instead).
- Mesh background is CSS gradients only (no big images). Admin JS stays small (Alpine + Tiptap lazy-loaded only on editor pages).
- Admin assets are a separate Vite entry (`resources/css/admin.css`, `resources/js/admin.js`) so the public site never ships glass CSS.

## Implementation notes
- Blade components: `<x-admin.glass-card>`, `<x-admin.button>`, `<x-admin.input>`, `<x-admin.table>`, `<x-admin.modal>`, `<x-admin.score-ring>`, `<x-admin.stat-card>`, `<x-admin.sidebar>`.
- Build a `/{ADMIN_PATH}/_styleguide` page (Super Admin only, disabled in production) showing every component in light & dark.
- Admin pages always send `X-Robots-Tag: noindex, nofollow`.

## Acceptance
- [ ] Every admin screen uses the shared components, no ad-hoc styles
- [ ] Light + dark verified; contrast audit passes (axe)
- [ ] Works without `backdrop-filter` (fallback tested)
- [ ] Lighthouse accessibility ≥ 95 on dashboard, post editor, media library
- [ ] Post editor remains smooth with a 3,000-word article
