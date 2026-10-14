# AI_VERSIONING.md — project versioning

Continuation of [AI.md](../../AI.md).

## 1. Current release and source of truth

The plugin and theme share release **1.1.0**. The release number is set in exactly three places:

| File | Field |
|---|---|
| `plugins/argon-shop/argon-shop.php` | `Version:` header |
| `plugins/argon-shop/argon-shop.php` | `ARGON_SHOP_VERSION` |
| `themes/argon-shop-theme/style.css` | `Version:` header |

All three must match. `AI.md` and the `Project-Id-Version` headers in `.pot` / `.po` must describe the same release; recompile `.mo` after changing `.po`.

The version also invalidates CSS/JS URLs. Static-check verifies matching runtime versions and a matching changelog section. Do not add per-file `@version` tags.

## 2. Choosing a future release

Choose a release number explicitly with the maintainer; do not automatically assign a future minor version to a helper.

- Bug fix without an incompatible API change: increment the patch component (`X.Y.Z` → `X.Y.(Z+1)`).
- Backward-compatible feature: increment the minor component (`X.Y.Z` → `X.(Y+1).0`).
- Incompatible change: increment the major component (`X.Y.Z` → `(X+1).0.0`).

The largest change in a release determines the increment. `Requires PHP` and `Requires at least` are environment requirements, not ArgonShop's release number.

## 3. Editing and releasing

For unreleased work, add a changelog entry under `[Unreleased]` and leave the version unchanged unless a release is explicitly requested.

For a release:

1. Confirm the target number.
2. Update the three fields above, current-version documentation and translation headers; rebuild `.mo`.
3. Group release entries under one `## [X.Y.Z]` section and leave an empty `[Unreleased]` above it.
4. Add necessary migration/upgrade instructions; preserve data contracts unless a migration is provided.
5. Run `php tests/static-check.php`, `php tests/regression-check.php`, `node tests/number-check.mjs`, and the test-site `wp eval-file tests/smoke-test.php`.

## 4. Function and hook annotations

`@since` identifies the release in which a function/hook or described behavior became available. Features in the current release use `@since 1.1.0`. For later work, use the release number explicitly approved by the maintainer, not an invented future version. Do not rewrite these annotations once a release is published.

Keep public API compatibility wrappers and their `@deprecated` annotations in `includes/legacy-functions.php`.

## 5. Data migrations

The data version is stored in the WordPress option `argon_shop_version`; it is separate from code comments. `as_maybe_upgrade()` in `includes/install.php` compares it with `ARGON_SHOP_VERSION`.

The current migration key is `1.1.0`, callback `as_migrate_1_1_0`. Preserve both: upgrading from 1.0.0 to 1.1.0 must run the cleanup of old public order files. Do not reset database options or rename migration keys merely to change documentation.

Future migrations must be idempotent and listed in `as_get_migrations()` under their approved release. Stored meta/cookie/option changes require a migration; release-number edits do not roll back data.

## 6. Third-party code and add-ons

Original version labels in forks such as `breadcrumbs.php`, `pagenavi.php` and `last_viewed_posts.php`, vendor library versions and licenses are not ArgonShop versions. Preserve them. Local changes to third-party code are recorded in the project's changelog without adding a per-file version.

Modules inside the repository share the project release. Only an independently released add-on gets its own plugin header, changelog and minimum ArgonShop requirement.
