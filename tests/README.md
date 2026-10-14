# ArgonShop tests

| File | Run | Requirements |
|---|---|---|
| `static-check.php` | `php tests/static-check.php` | PHP 8.3+ only |
| `smoke-test.php` | `wp eval-file tests/smoke-test.php` | test WordPress + WP-CLI |

When to run what, how to change the tests and the manual checklist — [docs/ai/AI_TESTING.md](../docs/ai/AI_TESTING.md).

## static-check.php — checks without WordPress

Runs from the repository root in a few seconds; no site or database needed:

```bash
php tests/static-check.php
```

Sections:

1. **PHP syntax** of every plugin and theme file.
2. **Hard-coded texts** — no user-visible text outside translation functions.
3. **Text domains** — `argon-shop` in the plugin, `argon-shop-theme` in the theme.
4. **Translations** — `.pot` matches the code, every string is translated to Russian and French (with the same `%s` placeholders).
5. **Versions** — the version matches in `argon-shop.php`, `ARGON_SHOP_VERSION` and `style.css`, and `CHANGELOG.md` has a section for it.
6. **English-only sources** (since 1.1.0) — no Cyrillic in PHP comments and none at all in JS/CSS (prevents Russian strings like "Files uploaded" from coming back in JS).

Exit code `0` — everything is fine.

In Claude Code this check runs automatically after every PHP edit (hook in `.claude/settings.json`) and on demand with `/check`.

## smoke-test.php — end-to-end store check

Runs the main scenarios on a real WordPress "like a browser" (HTTP requests to the site) and removes all test data afterwards.

**What is checked:** adding to the cart, changing quantities, removal and clearing; price tampering in the cookie; required fields (including removing `required` in the browser); file uploads (a PHP script disguised as `.jpg`, a file over 5 MB, a normal PNG and private storage); order creation and emails; registration during checkout (no password in the email, a set-password link instead); customer account (guests rejected); search with special characters; search language rules for ru/en/fr (keyboard layout fixes, stop words, plurals, elisions); the main site and admin pages; JS strings come from translations (`arsWpAjax.i18n`) and the cart page has no Cyrillic on non-Russian sites; currency setting (USD/EUR/RUB symbols and positions); the admin order recount AJAX; new PHP errors in `debug.log`.

### Requirements

- A test or local site — **not production**: the test creates and deletes products, orders and users.
- [WP-CLI](https://wp-cli.org/).
- The `argon-shop` plugin and the `argon-shop-theme` theme are active.
- The site answers requests from the same server (address from `siteurl`).
- In `wp-config.php`:

```php
define( 'WP_ENVIRONMENT_TYPE', 'local' );   // without it WordPress treats the site as production and the test will not run
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );             // for the debug.log check
define( 'WP_DEBUG_DISPLAY', false );
```

### Run

```bash
cd /path/to/wordpress
wp eval-file /path/to/ArgonShop/tests/smoke-test.php
```

Exit code `0` — all checks passed, `1` — there are failures (listed in the output). Forced run on a production-type site — `ARGON_SHOP_SMOKE_FORCE=1` (not recommended).

No emails leave the site during the test: a temporary mu-plugin intercepts `wp_mail` and is removed after the test. The store currency is restored after the currency checks.

### Before committing

```bash
php tests/static-check.php
wp eval-file tests/smoke-test.php
```

The test does not depend on the site language: run it on Russian, French and English sites (`wp site switch-language ru_RU|fr_FR|en_US`). Section "11. Translations" checks that the selected language is actually loaded; section "8. Search language" picks the cases for the current language.

Added a feature — add a check to `smoke-test.php` (function `as_smoke_check( $condition, 'description' )`).

## Additional regression checks

- `php tests/regression-check.php` — isolated actual plugin functions with WordPress doubles; PHP 8.3+ and mbstring. Also run by static-check section 7.
- `node tests/number-check.mjs` — actual JS parser against shared `number-cases.json`; Node 18+.
- `wp eval-file tests/smoke-test.php` — includes section 12 for real WordPress wholesale and password regression checks.

Token-based guards ignore comments and whitespace; mutation checks ensure `floatval ( $stepPrice )` and sanitizers hidden by comments are detected. Isolated checks do not replace the test-site smoke run.
