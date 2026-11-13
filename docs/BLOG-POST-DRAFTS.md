# Blog post drafts

The administration area now has post-draft screens at `posts`. Drafts store a title, separate slug, immutable exact path, optional excerpt, plain-text body, optional active author/category/tags, and an optional ready accessible featured image.

The initial body editor intentionally rejects HTML. This is a safe interim editor, not the final Tiptap experience: rich-text input, its server-side sanitiser, editor toolbar, publication and public rendering remain pending.

Each saved draft calculates a transparent, server-side SEO score on a 0–100 scale plus a simple readability score. It checks the focus keyword, metadata, URL, opening content, keyword density, content length and secondary keywords. The score supports the index filters: under 50 needs attention and 80 or above is good. It is editorial guidance only, not a publication gate.

Author, category and tag selectors accept active records only. Post saves use the shared global path-write lock and an optimistic content version. They preserve source IDs and SEO/publication fields, save as drafts, and retain scoped revisions that can be restored only to the same post. Draft-only, non-imported posts can be archived and restored; their paths and history stay reserved. Saved post paths cannot be changed through this editor.
