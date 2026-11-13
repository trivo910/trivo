# Menu management

Menu management is available under the hidden admin path at `/menus`. It uses existing glass components and native forms for menu metadata, item labels/URLs, display order, link targets and parent selection. Editing changes backend records only; public header/footer navigation and source import remain pending.

## Locations, permissions and history

Locations are unique lowercase keys with letters, digits, underscores or hyphens, up to 64 characters. Archived menus retain their location reservation. Imported locations and legacy identifiers are preserved. Menus and items are archived reversibly; imported menus/items cannot be archived through this workflow. Archiving a menu retains its items and history without changing individual item archive states.

Policies use independent `menus.view/create/update/delete` permissions, verified second-factor sessions and CSRF. SEO Managers can view but cannot mutate menus. Item archive/restore requires both update and delete permissions. Archived menus are read-only until restored.

All metadata/item/archive writes lock the owning menu and check its `content_version`. Location changes additionally use a shared cache lock to serialize unique-key checks. Every successful mutation increments the menu version, records model-scoped change history and emits an activity event. History snapshots contain menu metadata and the changed item, keeping each record bounded as archived items accumulate. The UI shows the ten most recent changes; historical revision replay is not implemented.

## Link and hierarchy validation

Labels and names accept plain text, without HTML or control characters. Link targets allow default, `_self` or `_blank`. URLs may be safe site-relative paths, HTTPS URLs without credentials, or simple `#section` anchors. Relative/protocol-relative URLs, unsafe schemes, traversal, controls and markup are rejected. External links are never fetched during editing. Public `_blank` rendering must include `rel="noopener noreferrer"` when that renderer is implemented. Contact schemes such as mailto/tel need a reviewed extension if required by the verified source menus.

Each menu supports up to 100 active items and five nesting levels. Parent references must point to active items in the same menu. Validation walks the whole proposed tree to reject self-parenting, indirect cycles, missing parents and moves that push descendants beyond the depth limit. Lower display order sorts first; item ID breaks ties. The admin table is an editing list rather than a public hierarchical preview.

Archive children before archiving their parent, or move the children to another active parent first. Restore parents before children. Cross-menu item IDs are rejected for reads, updates, archive and restore, even when a valid foreign item ID is supplied. Archived items are paginated separately, and retained URLs remain visible to existing media-usage checks.

## Verification and remaining integration

Synthetic tests cover metadata/field isolation, safe and unsafe URL formats, hierarchy/depth/count bounds, item ownership, stale edits, source preservation, permissions, archive/restore, authentication and CSRF. Reproduce browser QA with fresh isolated fixture credentials and `node tools/check_menu_ui.cjs`, following the SQLite and compiled-view setup in `docs/admin-preview/README.md`.

The full suite passes 362 tests with 1996 assertions; the 25 menu tests contribute 155 assertions. Pint, Larastan level 6, the production asset build and configuration/route/view/event cache checks pass. Chrome create/edit/list checks pass nine axe audits across desktop light/dark and 390px mobile light, with no recorded violations or external requests. Evidence is in `docs/admin-preview/menus-accessibility.json` and `menu-*.png`. The application database remains without menus, items or default accounts; all browser content is isolated synthetic data. No dependency changed; current advisory status remains unverified after the previously recorded Packagist DNS failure.

Pending: verified WordPress location/tree mapping, source import, historical revision restore, public navigation renderer and cache invalidation, supported production database/hosting checks and full URL/visual parity. No real navigation or source files are imported or published by development validation. Videos remain manual.
