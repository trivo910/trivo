# Design System Inspired by Digital Marketing Company in India

> Auto-extracted from `https://trivo.in/` on 2026-11-02

## 1. Visual Theme & Atmosphere

Friendly, approachable design with rounded shapes and generous whitespace.

The hero section leads with "Digital marketing that drives revenue".

**Key Characteristics:**
- Montserrat as the heading font
- Montserrat as the body font for all running text
- Heading weight 700, letter-spacing -1px
- Light/white background (#ffffff) as the primary canvas
- Primary accent `#006ace` used for CTAs and brand highlights
- 8 shadow level(s) detected — tinted shadows
- Rounded corners (50px+) creating a friendly, approachable feel
- Tags: light, rounded, colorful, bold-typography, sans-serif

## 2. Color Palette & Roles

### Primary
- **Primary Accent** (`#006ace`) · `--color-primary`: Brand color, CTA backgrounds, link text, interactive highlights.
- **Secondary Accent** (`#ffbe11`) · `--color-secondary`: Secondary brand, hover states, complementary highlights.
- **Background** (`#ffffff`) · `--color-bg`: Page background, primary canvas.
- **Background Secondary** (`#e9e5ff`) · `--color-bg-secondary`: Cards, surfaces, alternating sections.

### Text
- **Text Primary** (`#000000`) · `--color-text`: Headings and body text.
- **Text Secondary** (`#69727d`) · `--color-text-secondary`: Muted text, captions, placeholders.

### Borders & Surfaces
- **Border** (`#f7f7fa`) · `--color-border`: Dividers, outlines, input borders.

### Full Extracted Palette

| # | Hex | CSS Variable | Role | Area | Contrast |
|---|---|---|---|---|---|
| 1 | `#ffffff` | `--palette-1` | block | large | text-dark |
| 2 | `#e9e5ff` | `--palette-2` | badge | large | text-dark |
| 3 | `#000000` | `--palette-3` | button | large | text-light |
| 4 | `#f7f7fa` | `--palette-4` | section | large | text-dark |
| 5 | `#006ace` | `--palette-5` | block | medium | text-light |
| 6 | `#69727d` | `--palette-6` | button | small | text-light |
| 7 | `#25d366` | `--palette-7` | button | small | text-dark |
| 8 | `#ffbe11` | `--palette-8` | badge | small | text-dark |
| 9 | `#004b8a` | `--palette-9` | text-accent | small | text-light |
| 10 | `#0077b5` | `--palette-10` | text-accent | small | text-light |
| 11 | `#1da1f2` | `--palette-11` | text-accent | small | text-dark |
| 12 | `#d62976` | `--palette-12` | text-accent | small | text-light |

## 3. Typography Rules

- **Heading Font:** `Montserrat`, sans-serif
- **Body Font:** `Montserrat`, sans-serif

### Type Hierarchy

| Role | Font | Size | Weight | Line Height | Letter Spacing |
|---|---|---|---|---|---|
| H1 | Montserrat | 66px | 700 | 72.6px | -1px |
| H2 | Montserrat | 16px | 600 | 22.4px | -1px |
| H3 | Montserrat | 40px | 600 | 48px | -2px |
| H4 | Montserrat | 24px | 600 | 28.8px | -0.6px |
| Body | Montserrat | 16px | 300 | 24px | normal |

### Type Scale

| Token | Size | Suggested Usage |
|---|---|---|
| Display | `69px` | headings |
| H1 | `66px` | headings |
| H2 | `55px` | headings |
| H3 | `50px` | headings |
| H4 | `42px` | headings |
| Body L | `40px` | body / supporting text |
| Body | `34px` | body / supporting text |
| Small | `33px` | body / supporting text |
| XS | `32px` | body / supporting text |
| Caption | `30px` | body / supporting text |

## 4. Component Stylings

### Primary Button

```css
.btn-primary {
  background: #000000;
  color: #ffffff;
  border-radius: 18px;
  padding: 13px 29px;
  font-size: 14px;
  font-weight: 700;
  border: none;
  cursor: pointer;
}
```

### Filled Button

```css
.btn-filled {
  background: #020101;
  color: #000000;
  border-radius: 0px;
  padding: 0px 0px;
  font-size: 12px;
  font-weight: 700;
  border: none;
  cursor: pointer;
}
```

### Ghost Button

```css
.btn-ghost {
  background: transparent;
  color: #000000;
  border-radius: 0px;
  padding: 0px 0px;
  font-size: 33px;
  font-weight: 300;
  border: none;
  cursor: pointer;
}
```

### Filled Button 2

```css
.btn-filled-2 {
  background: #ffffff;
  color: #004b8a;
  border-radius: 50px;
  padding: 0px 0px;
  font-size: 13px;
  font-weight: 300;
  border: none;
  cursor: pointer;
}
```

### Card

```css
.card {
  background: #f7f7f8;
  border-radius: 40px;
  padding: 100px;
}
```

## 5. Layout Principles

- **Base spacing unit:** `10px` — use multiples (20px, 30px, 40px, etc.)

### Spacing Scale (extracted from real elements)

| Token | Value | Role |
|---|---|---|
| spacing-1 | `10px` | element |
| spacing-2 | `13px` | element |
| spacing-3 | `15px` | element |
| spacing-4 | `20px` | element |
| spacing-5 | `16px` | element |
| spacing-6 | `6px` | element |
| spacing-7 | `40px` | card |
| spacing-8 | `100px` | section |

### Border Radius Scale

| Token | Value | Element |
|---|---|---|
| radius-card | `50px` | card |
| radius-card | `18px` | card |
| radius-button | `12px` | button |
| radius-card | `24px` | card |
| radius-card | `40px` | card |
| radius-button | `7px` | button |

## 6. Depth & Elevation

| Level | Shadow | Usage |
|---|---|---|
| High | `rgba(0, 0, 0, 0.03) 0px 14px 14px 0px` | Modals, floating elements |
| Mid | `rgba(99, 99, 99, 0.2) 0px 2px 8px 0px` | Dropdowns, popovers |
| High | `rgba(88, 82, 125, 0.1) 0px 14px 16px 0px` | Modals, floating elements |
| Low | `rgba(0, 0, 0, 0.2) 2px 2px 3px 0px` | Cards, subtle elevation |
| High | `rgba(34, 22, 119, 0.36) 0px 14px 16px 0px` | Modals, floating elements |

> **Note:** This site uses chromatic (color-tinted) shadows rather than pure black — this is a deliberate brand choice that adds warmth to elevation.

## 7. Do's and Don'ts

### Do
- Use `#ffffff` as the primary background color
- Use `Montserrat` for all headings and `Montserrat` for body text
- Use `#006ace` as the single dominant accent/CTA color
- Maintain `10px` as the base spacing unit — all gaps should be multiples
- Use rounded corners (`50px`+) consistently for all interactive elements
- Make headlines large and bold — typography is the hero element
- Embrace bold color combinations — playful energy is the point
- Apply the shadow system for elevation — use the extracted shadow values
- Use weight 700 for headings to match the brand's typographic voice

### Don't
- Don't use colors outside the extracted palette without justification
- Don't substitute Montserrat/Montserrat with generic alternatives
- Don't use irregular spacing — stick to 10px grid
- Don't use dark/black backgrounds — this is a light-themed design
- Don't use sharp corners — they feel hostile in this rounded design language
- Don't use pure black (#000000) for text — use `#000000` instead
- Don't add decorative elements not present in the original design — no badges, ribbons, banners, or ornaments unless the source site uses them
- Don't invent UI patterns the source site doesn't have — if the original has no NEW badge, don't add one just because a red is in the palette

## 8. Responsive Behavior

| Breakpoint | Width | Notes |
|---|---|---|
| Mobile | < 640px | Single column, stack sections, reduce font sizes ~80% |
| Tablet | 640–1024px | 2-column where appropriate, maintain spacing ratios |
| Desktop | 1024–1440px | Full layout as designed |
| Wide | > 1440px | Max-width container, center content |

- Touch targets: minimum 44×44px on mobile
- Maintain 10px base unit across breakpoints — only scale multipliers

## 9. Agent Prompt Guide

### Quick Color Reference

```
Background:  #ffffff
Text:        #000000
Accent:      #006ace
Secondary:   #ffbe11
Border:      #f7f7fa
```

### Example Prompts

1. "Build a hero section with a `#ffffff` background, `Montserrat` heading in `#000000`, and a `#006ace` CTA button with 18px radius."
2. "Create a pricing card using background `#e9e5ff`, border `#f7f7fa`, `Montserrat` for text, and 30px padding."
3. "Design a navigation bar — `#ffffff` background, `#000000` links, `#006ace` for active state."
4. "Build a feature grid with 3 columns, 30px gap, each card using the card component style."
5. "Create a footer with `#000000` background, `#ffffff` text, and 20px padding."

### Iteration Guide

1. Start with layout structure (sections, grid, spacing)
2. Apply colors from the palette — background first, then text, then accents
3. Set typography — font families, sizes from the type scale, weights
4. Add components — buttons, cards, inputs using the specs above
5. Apply border-radius consistently across all elements
6. Add shadows for depth — use the extracted shadow values, not defaults
7. Check responsive behavior — test mobile and tablet layouts
8. Final pass — verify all colors match, spacing is consistent, fonts are correct

## 10. CSS Custom Properties

> 99 custom properties extracted from `:root` / `html` stylesheets.

### Color Variables

| Variable | Value |
|---|---|
| `--vamtam-body-link-regular` | `#000000` |
| `--vamtam-body-link-visited` | `#000000` |
| `--vamtam-body-link-hover` | `#6754E9` |
| `--vamtam-body-link-active` | `#6754E9` |
| `--vamtam-input-border-color` | `#E2E2E2` |
| `--vamtam-btn-text-color` | `#FFFFFF` |
| `--vamtam-btn-hover-text-color` | `#FFFFFF` |
| `--vamtam-btn-bg-color` | `#000000` |
| `--vamtam-btn-hover-bg-color` | `#6754E9` |
| `--sp-real-primary-color` | `#1e67d8` |
| `--sp-real-primary-color-hover` | `#06f` |
| `--sp-real-link-color` | `#1a74e4` |
| `--sp-real-primary-color-70` | `#06f` |
| `--sp-real-white-color` | `#fff` |
| `--sp-real-primary-text-color` | `#2f2f2f` |
| `--sp-real-primary-bg-color` | `#eee` |
| `--sp-real-border-color` | `#e2e2e2` |
| `--sp-real-gradient-color` | `linear-gradient(90deg,rgba(255,71,81,.4),rgba(255,145,10,.4))` |
| `--swiper-theme-color` | `#007aff` |
| `--wp--preset--color--black` | `#000000` |
| `--wp--preset--color--cyan-bluish-gray` | `#abb8c3` |
| `--wp--preset--color--white` | `#ffffff` |
| `--wp--preset--color--pale-pink` | `#f78da7` |
| `--wp--preset--color--vivid-red` | `#cf2e2e` |
| `--wp--preset--color--luminous-vivid-orange` | `#ff6900` |
| `--wp--preset--color--luminous-vivid-amber` | `#fcb900` |
| `--wp--preset--color--light-green-cyan` | `#7bdcb5` |
| `--wp--preset--color--vivid-green-cyan` | `#00d084` |
| `--wp--preset--color--pale-cyan-blue` | `#8ed1fc` |
| `--wp--preset--color--vivid-cyan-blue` | `#0693e3` |
| ... | *(18 more)* |

### Spacing Variables

| Variable | Value |
|---|---|
| `--vamtam-site-max-width` | `1280px` |
| `--swiper-navigation-size` | `44px` |
| `--wp--preset--aspect-ratio--square` | `1` |
| `--wp--preset--spacing--20` | `0.44rem` |
| `--wp--preset--spacing--30` | `0.67rem` |
| `--wp--preset--spacing--40` | `1rem` |
| `--wp--preset--spacing--50` | `1.5rem` |
| `--wp--preset--spacing--60` | `2.25rem` |
| `--wp--preset--spacing--70` | `3.38rem` |
| `--wp--preset--spacing--80` | `5.06rem` |
| `--direction-multiplier` | `1` |

### Typography Variables

| Variable | Value |
|---|---|
| `--wp--preset--font-size--small` | `13px` |
| `--wp--preset--font-size--medium` | `20px` |
| `--wp--preset--font-size--large` | `36px` |
| `--wp--preset--font-size--x-large` | `42px` |
| `--joinchat-font` | `-apple-system,blinkmacsystemfont,"Segoe UI",roboto,oxygen-sans,ubuntu,cantarell,"Helvetica Neue",sans-serif` |

### Other Variables

| Variable | Value |
|---|---|
| `--vamtam-input-border-radius` | `18px 18px 18px 18px` |
| `--vamtam-icon-close` | `'\e918'` |
| `--vamtam-icon-campaigns` | `'\e916'` |
| `--vamtam-icon-chart` | `'\e917'` |
| `--vamtam-icon-amazon` | `'\e90b'` |
| `--vamtam-icon-check` | `'\e90c'` |
| `--vamtam-icon-idea` | `'\e90e'` |
| `--vamtam-icon-influencer` | `'\e90f'` |
| `--vamtam-icon-minus` | `'\e910'` |
| `--vamtam-icon-pin` | `'\e911'` |
| `--vamtam-icon-plus` | `'\e912'` |
| `--vamtam-icon-puzzle` | `'\e913'` |
| `--vamtam-icon-search` | `'\e914'` |
| `--vamtam-icon-social-media` | `'\e915'` |
| `--vamtam-icon-arrow-left` | `'\e900'` |
| ... | *(20 more)* |
