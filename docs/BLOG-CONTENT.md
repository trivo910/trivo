# Database-backed blog management

The public blog listing and individual article routes use database records only. The former sample arrays in `BlogController` have been removed. `/blog` paginates published articles (18 per page), excludes future publication dates and trashed records, and displays an empty state when no articles exist. Cards and detail pages retain existing saved paths. A `/blog/{slug}` alias redirects to a legacy article's saved path when needed; an exact saved `/blog/{slug}` takes precedence over other records with that slug.

## Admin workflow

Open **Admin → Blog posts**. All active articles appear by default, with draft, scheduled, published and Trash filters. Create an article with its title, slug, full path, author, category, excerpt, tags, featured image and SEO metadata. Save as a draft and publish separately. Existing saved paths remain locked.

The visual body editor supports paragraphs, headings, bold/italic text, links, lists, content images and captions. HTML source editing is also available; unsafe elements and attributes are removed when saved. Pasting into the visual editor uses plain text. A source textarea is available without JavaScript.

Choose ready library images, or upload an image directly within the editor. Uploading does not save the article. Existing media processing handles uploaded files; the editor polls briefly and refreshes the available images when ready. If background processing is still queued, run the configured worker and use **Refresh images** afterward. Image alt text and captions can be changed for an occurrence in the article. Click an image to replace, move or remove it. The featured image is managed separately. Legacy source images can be retained, and new inline images must come from the media library. Ready-state/alt validation and image row locks protect saves against simultaneous media archival. Historic revision image references remain protected by the existing media-usage service.

Published articles remain editable with `posts.update` permission. **Save published post** preserves published status and its publication timestamp while updating content immediately. Every save increments `content_version`, records a revision and recalculates reading time and content scores. Stale submissions are rejected. Authors, categories, tags, dates, reading time and selected featured images are displayed from saved data. The table of contents is off by default and can be explicitly enabled in admin. Imported articles have it disabled to match the source pages.

## Trash and restoration

**Delete post** opens a confirmation dialog and moves the record to Trash. Both draft and published articles can be deleted by accounts with `posts.delete`. Soft deletion removes the article from public queries without deleting content, revisions, tags or image files; the path remains reserved. **Restore post as draft** returns it for review with its publication date/schedule cleared. It does not silently republish or resume an expired schedule. Existing URL locks and revision history remain intact.

## Schema and deployment

Apply `2026_11_18_000001_classify_blog_article_sources.php`. The migration adds `posts.content_kind` (article or portfolio_source) and `posts.body_format` (legacy or managed HTML). Existing article bodies and URLs are retained. Known case-study source records and retained `/portfolio-source/` records are classified separately, so they cannot appear in blog listings or be changed through the Blog Posts module. Portfolio imports set this classification on retained source posts as well.

Legacy rendering retains its prior featured-image extraction behavior until a post is saved through the new editor. Managed HTML articles use their explicit featured image and keep inline images in the body. Removing a featured image does not promote or remove a content image on the article detail page.

Rebuild frontend assets after deploying the editor JavaScript/CSS. No new package or external editor service is required.

## Verification status

Automated and browser verification was performed on 18 November 2026 after the user authorized testing. See [BLOG-QA.md](BLOG-QA.md) for scope, results, reproducible checks and remaining issues. Test mutations use an isolated SQLite database; synthetic articles and uploads were not added to application content.

Two follow-up data migrations repair eight missing Elementor image references using the same imported original images, and classify three imported portfolio design demos separately from articles. These corrections were applied locally. The editor's upload and refresh endpoints are captured during initialization so button events use the correct API URLs.
