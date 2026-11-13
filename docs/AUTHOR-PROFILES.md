# Blog author profiles

The administration area manages blog author profiles at `authors`. These are content records only: creating or editing one never creates a user, changes a user, or grants sign-in access.

Each profile has a name, a required exact author-archive path, optional plain-text biography, optional ready accessible avatar, and optional HTTPS website, LinkedIn, X, and Instagram links. Links are stored only; the application does not fetch them or render a public author page yet.

Saved paths remain immutable and are checked against the shared content-path catalog, including archived paths and case/trailing-slash aliases. Profiles keep their source ID and existing SEO fields untouched. The separate author SEO editor and public archive rendering are pending.

Every mutation uses a content version and a short global path-write lock. A bounded profile revision is retained on each save and can be restored only for the same author profile. Archive is reversible, but imported profiles and profiles referenced by any post cannot be archived. Avatar validation requires a non-archived image-library record that is `ready` and has alt text.

Permissions reuse the existing post permissions: readers can view profiles, post creators can add them, post editors can edit them, and post deleters can archive/restore eligible profiles. SEO Managers remain read-only. Browser and test fixtures use synthetic data only.
