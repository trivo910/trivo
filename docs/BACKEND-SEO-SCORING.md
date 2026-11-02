# Blog editor & SEO scoring (Yoast/RankMath-style, built in-house)

## Editor fields (Post)
Title, permalink (editable full path; warn when changing a published post's path → auto-create 301 from old path), body (Tiptap), excerpt, featured image + alt, category, tags, author, status/schedule.
**SEO panel**: Focus keyword (required before publish, warn only), secondary keywords (up to 4), SEO title (with pixel-width bar), meta description (char bar 120–160), canonical, robots toggles, OG/Twitter overrides, schema type (Article/BlogPosting/FAQ/HowTo), SERP preview (desktop+mobile), social preview.

## Live analysis
Run client-side (Alpine, debounced 500 ms) using the same rules as server `SeoScorer` (single source of truth: the server scorer is authoritative and runs on save; JS mirrors it). Result stored in `posts.seo_score`, `readability_score`, `seo_analysis` JSON. Show list of checks with ✔ good / ▲ improve / ✖ problem. Score colour: 0–49 red, 50–79 orange, 80–100 green. Dashboard list column + filter by score.

## SEO checks (weights sum to 100)
| # | Check | Pass rule | Weight |
|---|---|---|---|
| 1 | Focus keyword set | non-empty | 5 |
| 2 | Keyword in SEO title | present, ideally at beginning | 10 |
| 3 | Keyword in meta description | present | 8 |
| 4 | Keyword in URL slug | present | 7 |
| 5 | Keyword in first paragraph (first 10%) | present | 8 |
| 6 | Keyword in at least one H2/H3 | present | 5 |
| 7 | Keyword density | 0.5%–2.5% (stem/partial match, case-insensitive) | 8 |
| 8 | Keyword in image alt | ≥1 image alt contains it | 5 |
| 9 | Keyword unique | no other post uses same focus keyword (warn) | 5 |
| 10 | SEO title length | 30–60 chars / ≤ 580px | 5 |
| 11 | Meta description length | 120–160 chars | 5 |
| 12 | Content length | ≥ 800 words good, 300–799 improve, <300 problem | 8 |
| 13 | Internal links | ≥ 2 | 5 |
| 14 | Outbound links | ≥ 1 to authoritative site | 3 |
| 15 | Images | ≥ 1 image with alt | 3 |
| 16 | Single H1 / heading structure | no skipped levels | 3 |
| 17 | Secondary keywords | each appears ≥ 1× | 2 |
| 18 | Featured image set | yes | 0 (shown, not scored) |
Scoring: pass = full weight, improve = 50%, fail = 0. Support multi-word & Hindi/transliterated keywords; strip HTML before analysis; handle plurals via simple stemming.

## Readability checks (separate score)
Avg sentence length (≤20 words for ≥75% sentences), paragraph length (≤150 words), subheading distribution (heading every ≤300 words), passive voice ≤10%, transition words ≥30% of sentences, Flesch reading ease ≥ 60, consecutive sentences starting with same word <3.

## Extra editor helpers
- Table of contents auto-generated from H2/H3 (toggle)
- Internal link suggestions: other posts matching focus/secondary keywords
- Cornerstone-content flag (raises min length)
- Reading time auto-calc
- Auto-generated fallback meta description (first 155 chars) shown as placeholder, never silently saved
- Revision history + restore
- "Publish" blocked if: no featured image alt, duplicate path, or empty SEO title (config toggle)

## Tests (Pest)
Unit tests with fixture articles asserting each check's pass/improve/fail, plus score total; feature test that saving a post persists the score and that changing a published path creates a redirect.
