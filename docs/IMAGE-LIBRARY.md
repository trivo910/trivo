# Image library

The admin image library handles website images only. Video uploading, video libraries, transcoding and processing are excluded; future video embeds use ordinary `<video>` tags with manually supplied sources.

## Workflow

Administrators and Editors with `media.manage` can upload JPEG, PNG, WebP and AVIF, search/filter the library, edit alt/title/caption and assign virtual folder labels. SEO Managers cannot access the library. All mutations use Form Requests, Policies, CSRF and the existing authenticated 2FA session. Uploads are limited to ten per minute per account.

New uploads require alt text. Limits are 8 MiB, 6,000 pixels per side and eight megapixels. SVG, PDF, video, executable/double-extension uploads, MIME mismatches, known embedded script markers and invalid container boundaries are rejected. Folder labels never become filesystem paths.

Validated input goes into private staging under a random UUID. The database queue receives only a media ID. The worker verifies the staged hash, decodes/re-encodes the pixels, applies JPEG EXIF orientation and strips source metadata. It publishes a sanitized JPEG/PNG master plus WebP/AVIF variants at 480/768/1200/1920 widths, capped at the source width. Smaller images are not enlarged. Private input is removed after successful processing; failed input remains private for retry.

Duplicate source/content hashes reuse existing entries without overwriting metadata. A per-hash cache lock and unique source-hash index protect duplicate uploads. Re-uploading a failed image can replace its staged copy and retry. Background status polling is bounded and does not refresh the admin idle timer.

## Published URLs and archiving

Metadata edits do not change file paths. Archiving checks direct media references, image blocks, HTML paths, revisions and non-mail settings. An in-use image cannot be archived. Published files are retained when an unused entry is archived, preserving external/indexed image URLs. Archived entries can be restored; no permanent file-deletion endpoint is exposed.

Legacy media identified by its WordPress ID or `/wp-content/uploads/` path is protected from processing and archiving. The existing legacy uploads folder was not processed, moved or modified. Legacy content/media import remains a later migration task.

Future content-saving actions must validate referenced images as ready/non-archived and coordinate assignments with the media row lock. New block schemas should use explicit `media_id` fields. The current usage scanner is conservative; large content libraries may eventually need a dedicated usage index.

## Rendering and deployment

`<x-media.picture :media="$image" sizes="..." :priority="true" />` renders AVIF/WebP srcsets, a sanitized master fallback, alt text and intrinsic dimensions. Default images are lazy-loaded; priority images are eager with high fetch priority. No conversion occurs during public rendering.

Public assets are stored on the public disk. Run `php artisan storage:link`, or use the documented shared-hosting public-media copy strategy. Admin previews use an authenticated file response and do not require a symlink. Never expose the private staging root. Optional `MEDIA_PUBLIC_ROOT` and `MEDIA_PRIVATE_ROOT` configure storage locations for deployments and isolated QA.

PHP 8.3 needs GD with WebP and AVIF support, EXIF, fileinfo and mbstring. Composer declares the core image extensions; the worker checks format support. Configure `upload_max_filesize=8M` and `post_max_size=12M` or higher, with adequate worker memory. The portable development runtime is configured for these limits; system XAMPP remains unchanged.

The existing minute-based database queue scheduler processes conversions without Redis/Supervisor. Image jobs have a 60-second timeout and one attempt; queue retry-after must remain longer than the timeout. Failed entries provide a protected retry action.

## Verification

Synthetic tests cover unsafe uploads, byte/pixel limits, private staging, WebP/AVIF output, no upscaling, EXIF orientation/stripping, duplicate reuse, metadata escaping, immutable file paths, reference protection, retained URLs after archive, restoration, failed-file retry, permissions, CSRF and idle-session behavior.

Browser QA uses a separate synthetic database and storage roots. Upload, detail and library pages pass nine axe A/AA audits across desktop light/dark and a true 390px mobile viewport. The browser test performs a real upload and invokes the queue worker on a generated test image. No actual website image was converted during development validation.

Still pending: legacy import, public templates/content integration, supported production database/hosting checks and full release performance/accessibility acceptance.
