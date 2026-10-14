# AI_TESTING.md — how to check changes

Continuation of [AI.md](../../AI.md). Which checks exist, when to run what, how to change the tests and how to work without a terminal.

## 1. Three levels of checks

| Level | Command | Needs | Catches |
|---|---|---|---|
| **Static** | `php tests/static-check.php` | PHP only (no WordPress, no database) | syntax, untranslated texts, a wrong text domain, untranslated strings and broken `%s`, version mismatch, Cyrillic in comments and in JS/CSS |
| **Smoke test** | `wp eval-file tests/smoke-test.php` | a test WordPress + WP-CLI | cart, prices, required fields, files, order, emails, registration, account, search and keyboard layouts, pages, admin, currency, translations (PHP and JS), PHP errors in `debug.log` |
| **Manual checklist** | section 6 below | a browser | layout, JS, what a person sees |

The static check runs in seconds anywhere — **always run it**. The smoke test — when there is a test site (see `tests/README.md`); run it in every site language you touched (Settings → General → Site Language: English, Русский, Français). The manual checklist — the items related to the change.

## 2. AI working modes

**AI with a terminal** (Codex, Claude Code, Cursor, etc.):

1. Before the change — run both checks, remember the initial state (if something is already red — report it, do not fix it silently).
2. The change.
3. Both checks again. Red — fix the code, not the test (section 4).
4. In the answer: what was run and the result (`OK: all 28 checks passed`, `Success: All checks passed: 62`).

**AI in a chat without a terminal** (edits individual files, the user runs the tests):

1. Do not claim "checked" — you did not run anything.
2. At the end of the answer give a "How to check" block:
   - the `php tests/static-check.php` command (and `wp eval-file tests/smoke-test.php` if the user has a test site);
   - **which output lines matter** for this change — see the table in section 3;
   - **2–5 manual checklist items** from section 6 related to the change.
3. If the change added texts — a translation table (see `AI_I18N.md`, section 4).
4. The user sends the output — the `✘` lines show what broke and where.

## 3. Which check covers what

| Changed file / area | Look at the smoke test | Manual checklist |
|---|---|---|
| `getData.php` (`as_get_product_price`, `as_sanitize_cart`), `cart-ajax.php`, `modules/cart.js`, `add-to-cart.js` | "1. Cart", "4. Quick order" (total, price) | 1–4 |
| `getData.php`, `myCabinet.php`, `shared/format.js`, `modules/cart.js` | static-check "7", isolated `regression-check.php`, JS `number-check.mjs`, smoke "12. Decimal numbers, wholesale steps and passwords" | Decimal prices, thresholds, boundary totals, step order and exact password preservation |
| `checkout-fields.php`, theme templates `template-parts/cart/*` | "2. Required fields" | 5–6 |
| `checkout-files.php`, `cart-form.js` | "3. Order files", "4. Quick order with a file" | 7 |
| `checkout.php`, `checkout-emails.php` | "4", "5" | 5, 8 |
| `checkout-registration.php` | "5. Order with registration" | 9 |
| `cabinet/*`, `modules/cabinet.js`, `orders.js` | "6. Customer account" | 10–11 |
| `search/*` (incl. `searchLanguage.php`), `modules/search.js` | "7. Search", "8. Search language" | 12 |
| theme templates, `functions.php`, `header.php` | "9. Pages open without errors" | 13–14 |
| `includes/admin/*` | "Admin: …" | 15 |
| currency: `getSetting.php` (`as_price_html` etc.), `settingPage.php`, price templates | "10. Currency" | 17 |
| texts, `languages/*`, `argon_shop_js_i18n()`, `core/config.js` | static-check "2–4", "6", smoke "11. Translations" | 16 |
| version, `CHANGELOG.md` | static-check "5. Versions" | — |
| anything | "No new PHP errors or warnings in debug.log" | — |

## 4. Rules for changing the tests

- **A test is red — first assume the code is broken.** A test is changed only if the behavior changed **on purpose** and the task says so.
- **Forbidden:** weakening or removing a check to make the test pass — changing the expected total to "whatever came out", removing the nonce check, bypassing an error with an `if` in the test. This hides the bug from the user.
- Changed the behavior on purpose → fix the corresponding check **and** write in `CHANGELOG.md` why.
- Added a feature → add a check:
  - PHP/AJAX logic — in `tests/smoke-test.php`: `as_smoke_check( $condition, 'What is checked, in plain words', $details )`; HTTP requests — via `$browser->ajax()` / `$browser->request()` (like a browser, with cookies and nonce); register test data in `$GLOBALS['as_smoke']['cleanup']` so that the test removes it.
  - A code-level rule (a new prohibition, a new structure) — in `tests/static-check.php` via `sc_ok()`.
- A check label is a statement about correct behavior ("The price from the cookie is ignored"), not about the code.
- The test must not depend on the site language: do not compare with translated messages; check `type`, codes, numbers or texts from your own test data, or compare with `__()` of the same string. Language-specific checks branch on `as_search_language()` / `determine_locale()`.
- Use `'cache_results' => false` in `get_posts()` when the data was created by an HTTP request in another process.
- The test must not leave garbage or send real emails (a temporary mu-plugin intercepts emails).
- Test output and comments are in English, like the rest of the code.

## 5. What automation does not cover

JS behavior in the browser (animations, tab switching, field masks), mobile layout, real email delivery, the nginx rule for `argon-shop-private`. That is the manual checklist.

## 6. Manual checklist

Open the site in an incognito window (clean cookies). The numbers are used in the table of section 3.

1. Product card in the catalog → "Add to cart": the counter and the total in the header update without a reload.
2. Product page: "+" / "−" change the quantity and the total; "Buy" leads to the cart.
3. Cart: changing the quantity recalculates the row, the total and the weight; removing a product; "Clear cart".
4. Reload the cart page — the contents are kept.
5. Quick order with empty fields — a message about the empty fields, no order is created.
6. The "Individuals" / "Legal entities" tabs switch, the fields match the store settings.
7. Attach 1 image and 1 PDF → the "Files uploaded: 2" counter is in the site language, the order is placed; attach a file > 5 MB → a clear error.
8. After the order: a message with the number, the cart is empty, the order with the correct total appears in the admin "Orders", the email arrived.
9. An order with the "Register you?" box and an email → the email contains a password setup link (no password), the password can be set via the link.
10. Customer account: changing and saving the account data.
11. Account → "Orders": the order is visible, the status filter works.
12. Header search: suggestions while typing; a query in the wrong layout finds products — Russian site: `njdfh` → «товар»; English site: `ыскуцы` → "screws" (typed in the Russian layout); French site: `срйшыу` / `chqise` → "chaise".
13. Home, catalog, product, cart, account, 404 open, the layout is not broken (including at 375 px width).
14. The browser console (F12) has no red errors on these pages.
15. Admin: editing a product (price, gallery, characteristics) and an order (add a product, recount) is saved.
16. "Settings → General → Site Language" = Français / English: the storefront and the store admin are in the selected language, including JS messages (cart, search, modal) — no Russian text; switch back to Russian — everything is as before.
17. "Settings → Store → Currency": USD → `$120` everywhere (cards, product page, cart, header, emails); EUR → `120 €`; RUB → `120 Р` on a Russian site.

## Decimal/wholesale/password regression checks

```bash
php tests/static-check.php
php tests/regression-check.php
node tests/number-check.mjs
wp eval-file tests/smoke-test.php
```

`static-check` section 7 uses token-based guards (ignoring comments/whitespace) and runs the isolated PHP suite. `regression-check.php` loads the actual pricing/account functions with WordPress test doubles; it does **not** verify WordPress hashing, cookies, HTTP, database or hooks. The PHP and JS suites read the same number fixtures. Node is needed only for the JS check, not for PHP static-check.

Smoke section 12 uses real WordPress: prices/terms with commas, totals below and exactly on thresholds, both step orders, disabled discounts, cookie price replacement and the cart AJAX total; passwords with `%xx`, tags, repeated spaces, quotes/backslashes, current `"0"`, short new `"0"`, UTF-8 length and mismatched confirmation. Newly created terms/products/users are removed; existing terms are reused without being deleted. Always run smoke on a test site. A successful isolated test is not a substitute for smoke.

This build is release 1.1.0. Its versioned CSS/JS URLs change; clear CDN or page caches that may still serve older assets.
