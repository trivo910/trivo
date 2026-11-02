# PRD — trivo.in on Laravel

## 1. Goal
Replace WordPress with Laravel with zero SEO loss, a faster site, an easier admin, and strong security.

## 2. Public site (replicate every section of live trivo.in)
Audit the live site + local project and list ALL pages/sections in `docs/SITE-INVENTORY.md`. Expected scope (verify, do not assume):
- Header: logo, mega/dropdown nav (services), CTA button, mobile menu
- Home: hero ("Digital marketing that drives revenue"), clients/partners (Google, Meta, Shopify, Razorpay), services grid, stats/counters, process, industries/travel focus, case studies, testimonials, blog teaser, FAQ, CTA, enquiry form
- Services pages: SEO, Paid Search/PPC, Social Media, Performance Marketing, Web Development, + any others found
- Location/city pages and GCC pages (if they exist on the live site — preserve exact URLs)
- About, Contact (form, map, phone, email, address), Careers (if any)
- Portfolio / case studies, Testimonials
- Blog: listing, pagination, category, tag, author, single post (TOC, share, related posts, author box, comments OFF unless live site has them)
- Legal: Privacy Policy, Terms, Refund (if present)
- Floating WhatsApp (Joinchat-style) button, click-to-call, cookie notice (only if the live site has it)
- 404 page (branded, noindex), search (only if live site has it)
- Forms: contact/enquiry/quote/newsletter → stored in DB, emailed via SMTP, spam-protected

## 3. Admin backend (`/{ADMIN_PATH}`) — glassmorphism UI (see `ADMIN-DESIGN-GLASSMORPHISM.md`)
| Module | Features |
|---|---|
| Dashboard | Leads count, posts, top pages, SEO score overview, last 404s |
| Pages | CRUD, slug/path (full path editable), sections builder (JSON blocks), SEO panel, status/scheduling, revisions |
| Blog | Posts, categories, tags, authors; Tiptap editor; featured image + alt; excerpt; scheduled publishing; **Focus keyword + SEO score + readability score**; SERP preview; canonical; robots; OG/Twitter; schema type; internal-link suggestions |
| Services / Portfolio / Testimonials / FAQs / Clients | CRUD, ordering, SEO panel |
| Leads | List, filters, export CSV, status, notes, reply |
| Media library | Upload (validated), auto WebP/AVIF + sizes, alt text, folder, usage tracking |
| Redirects | 301/302/410 manager, CSV import, hit counter, 404 log → "create redirect" |
| SEO settings | Site title template, default meta, organisation schema, social profiles, robots.txt editor, sitemap controls, verification tags (GSC/Bing), GA4/GTM/Meta Pixel IDs, header/footer scripts (admin-only, CSP-aware) |
| Mail / SMTP | host, port, encryption, username, password (encrypted), from name/email, reply-to, test email button, queue toggle, delivery log |
| Users & Roles | Super Admin, Admin, Editor, SEO Manager; 2FA; login history |
| Activity log | Who changed what, IP, time |
| Settings | Site info, contacts, social links, menus, footer, backups info |

## 4. Non-functional
Performance (CWV green), accessibility (WCAG 2.1 AA), security (see SECURITY.md), i18n-ready (English only now), observability (Laravel log daily rotation, optional Sentry), backups documented.

## 5. Out of scope
E-commerce, public user accounts, comments, multi-language.
