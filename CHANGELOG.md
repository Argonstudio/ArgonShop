# Changelog

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), versions follow [SemVer](https://semver.org/).

## [Unreleased]

## [1.1.0]

### Security

- **Prices are no longer taken from the cookie.** The cart is cleaned by `as_sanitize_cart()`, the price is always read from the database via `as_get_product_price()` — on the frontend, at checkout and in emails. Unpublished and deleted products drop out of the cart.
- **Required fields are checked on the server** (`as_get_required_fields()`), the `required` value from the browser is ignored. Theme templates take the field list from the same function.
- **Order files:** all checks happen before saving (at most 3 files, 5 MB per file, 15 MB in total, an extension whitelist, a check of the real file type). Files are stored in the private `uploads/argon-shop-private/` folder and deleted after the emails are sent. The old public `uploads/order-files` folder is removed by a migration.
- **Registration at checkout:** the password is no longer sent by email — the customer gets a link to set the password. An email is required for registration.
- A nonce check was added to all AJAX actions (clearing the cart and the account order filter did not have one).
- Search: database queries via `$wpdb->prepare()`.
- Output escaping on the catalog page; validation of the sort cookie value.
- Customer account: AJAX requests from guests are rejected.

### Added

- `.claude/commands/`: Claude Code commands `/check` (run `static-check` and explain the result) and `/new-ajax` (add an AJAX action by the `AI_EXTENDING.md` checklist).
- `.claude/settings.json` + `.claude/hooks/static-check.sh`: after a PHP file is edited, Claude Code runs `php tests/static-check.php` automatically and gets the errors back.
- `CLAUDE.md` for Claude Code: imports `AGENTS.md` and `AI.md`; the tool table in `AGENTS.md` updated.
- `docs/ai/AI_EXTENDING.md`, section 11 "Multi-region: several cities, one admin": the WordPress Multisite question to ask first, historical compatibility with the AA-DomainMirror plugin, suggested city functions (not in the code — to be written when needed), the Multisite variant, how to create the city templates (`single-aktsii-istra.php` and others).
- Documentation authorship: Ivan Voitkov, DeepSeek, Claude.

- **Store currency: Settings → Store → Currency** — US dollar (`USD`, default), euro (`EUR`) or Russian ruble (`RUB`). All prices use it: catalog cards, product page, wholesale steps, cart, header cart widget, "Recently viewed", customer account, order admin, emails. Helpers: `as_get_currency()`, `as_get_currency_symbol()`, `as_get_currency_position()`, `as_price_html()`, `as_get_currency_per_piece()`; filters `argon_shop_currencies`, `argon_shop_currency`, `argon_shop_currency_symbol`, `argon_shop_currency_position`. Theme wrappers `astheme_price_html()`, `astheme_currency_symbol()`, `astheme_currency_position()` keep the theme working without the plugin. USD is shown before the amount (`$120`), EUR and RUB after it (`120 €`).
- **Search adapted to English and French** (`includes/interface/search/searchLanguage.php`, chosen by the site language):
  - English: a query typed in the Russian layout by mistake is converted to QWERTY; stop words (the, of, for…) are ignored; plural and possessive endings are removed (screws → screw, batteries → battery); punctuation and typographic quotes are normalized.
  - French: Russian layout → AZERTY, QWERTY ↔ AZERTY mix-ups (a↔q, z↔w); elisions (l', d', qu'…), stop words (le, la, les, de, des…) and plural -s/-x are handled; œ/oe, æ/ae and ' / ’ match each other.
  - Russian: unchanged (QWERTY → ЙЦУКЕН, as before).
  - Filters `argon_shop_search_language`, `argon_shop_search_stop_words`, `argon_shop_search_layout_variants`. The same rules work in the header AJAX search, on the `/?s=` page and in the admin "Add product" field of an order.
- Frontend JS texts are translatable: `arsWpAjax.i18n` (built by `argon_shop_js_i18n()`, filter `argon_shop_js_i18n`) and `t()` in `assets/js/interface/core/config.js`; admin gallery texts via `arsGalleryI18n`; theme modal texts via `arsModalI18n`.
- `tests/static-check.php`: check 6 "English-only sources" — no Cyrillic in PHP comments and in JS/CSS files.
- Smoke test: "8. Search language" (keyboard layout fixes and word normalization for the current site language), "10. Currency", JS strings in the site language and "no Cyrillic text on the cart page of a non-Russian site".

- **Translations by WordPress rules: English (source language), Russian and French.** Text domains `argon-shop` (plugin) and `argon-shop-theme` (theme), files in `languages/` (`.pot`, `.po`, `.mo`). The language follows the "Site Language" setting. Russian sites look as before: the Russian translation matches the former texts.
- `tests/static-check.php` — a check without WordPress and a database (`php tests/static-check.php`): syntax, untranslated texts, text domains, completeness of translations and placeholders, matching versions.
- The smoke test got the "Translations" section.
- Documentation: `docs/ai/AI_VERSIONING.md`, `docs/ai/AI_TESTING.md` (with a manual checklist), `docs/ai/AI_I18N.md`.

- Extension hooks: `argon_shop_product_price`, `argon_shop_cart`, `argon_shop_required_fields`, `argon_shop_shipping_methods`, `argon_shop_payment_methods`, `argon_shop_checkout_errors`, `argon_shop_order_post_data`, `argon_shop_order_created`, `argon_shop_checkout_response`, `argon_shop_email`, `argon_shop_allowed_file_types`, `argon_shop_file_limits`, `argon_shop_customer_registered`.
- Activation (`includes/install.php`): creating or finding the cart and account pages, default form fields, flushing rewrite rules. Deactivation.
- Migration system: `ARGON_SHOP_VERSION`, `as_maybe_upgrade()`, `as_get_migrations()`.
- `uninstall.php`: removes settings, service options and order files; products, orders, users and the order counter are kept.
- `tests/smoke-test.php` — an end-to-end test on WP-CLI (see `tests/README.md`).
- Documentation: status and limitations, hooks, tests, `CHANGELOG.md`.

### Changed

- **Minimum requirements raised: PHP 8.3 and WordPress 7.1** (were PHP 7.4 and WordPress 6.0): `Requires PHP` / `Requires at least` headers in `argon-shop.php` and the theme `style.css`, README, AI documentation. WordPress will not activate the plugin and theme on older versions.

- **All code comments and documentation are in English** (`README.ru.md` stays the Russian translation of the README).
- The comma marker of the form field format in Settings → Store is now `/comma/`; the old `/запятая/` still works.
- The `[contact-form-7]` shortcodes in the theme use `title="Feedback"` (the title is not displayed, the form is selected by `id`).
- The translation templates (`.pot`) were regenerated; `.po`/`.mo` updated for the new strings.

- **The version is stored only in `argon-shop.php` (header and `ARGON_SHOP_VERSION`) and the theme `style.css`.** `@version` tags were removed from files; new functions get `@since`.
- Theme styles and scripts are enqueued with the theme version (`?ver=`), so browsers refresh them after a theme update.
- Plugin and theme headers and page template names ("Template Name") are in English and translated via `.po`. Template assignments of pages do not change (WordPress stores the file name).
- Default form fields are created on activation in the site language.
- Breadcrumbs: strings are translated at output (`AS_Kama_Breadcrumbs::default_l10n()`), the `kama_breadcrumbs_default_loc` filter works as before.

- `shoppingCart.php` (~1200 lines) was split into modules: `cart-ajax.php`, `checkout.php`, `checkout-fields.php`, `checkout-files.php`, `checkout-registration.php`, `checkout-emails.php`.
- **All plugin functions got the `as_` prefix** (119 functions; the `Kama_Breadcrumbs` class → `AS_Kama_Breadcrumbs`). The old names of the public API work via `includes/legacy-functions.php`. Mapping table — `docs/ai/AI_FUNCTION_NAMES.md`. Names of AJAX actions, hooks, CSS classes, meta fields and options did not change.
- Theme: header `Theme Name: Argon Shop Theme`, version 1.1.0, `Requires PHP: 7.4`.

### Fixed

- Wholesale decimal prices and thresholds: `as_to_float()` reads the complete number instead of truncating at a comma; thresholds are matched numerically and the highest reached threshold wins regardless of stored order. The product page and catalog use `as_get_product_price()` (including its filter).
- PHP and JS number parsing now share a strict contract: decimal comma/dot, whitespace, validated thousands grouping, scientific notation and finite values; malformed strings return zero. Cart weights use `parseNumber()` instead of `parseFloat()`. The new helper and its annotations belong to release 1.1.0.
- Account passwords: string type checks and `wp_unslash()` only, preserving tags, `%xx`, repeated spaces, quotes and backslashes; strict empty-string checks and UTF-8 character length. Nonce and account ownership checks are retained.
- Regression coverage: shared numeric fixtures, isolated PHP checks of actual plugin functions with WordPress doubles, JavaScript checks, token-based guards with mutation tests, and real WordPress smoke scenarios for fractional thresholds/prices, boundary totals, step order, AJAX totals and password edge cases.

- Theme translations: the address strings changed in the templates ("New York City Hall on Google Maps", "City Hall, New York, NY", "New York") were missing from `argon-shop-theme.pot` and the `ru_RU` / `fr_FR` translations (static-check ".pot contains all strings from the code" failed). The `.pot`, `.po` and `.mo` files are updated; the unused Moscow strings are removed.
- AI documentation (`AGENTS.md`, `AI.md`, `docs/ai/*`) checked against the code and corrected: file locations of functions, AJAX capability checks, response formats in the data-flow scenarios, the module list, the search ranking, templates that do not exist yet, unprefixed functions, copyright notice, tool table in `AGENTS.md`.

- **Russian texts on English and French sites** that 1.1.0 missed because they were hard-coded in JS: "ЗАГРУЖЕНО ФАЙЛОВ" (files uploaded) in the cart, file limit errors, "Корзина пуста", cart/checkout server errors, "Ищем результаты...", the contact form confirmation, account and product page errors, admin gallery texts, theme modal ("Закрыть", "Загрузка…", load error).
- The ruble sign was hard-coded in templates and emails; amounts now use the store currency.
- `as_sanitize_settings()` no longer fails (PHP 8 TypeError) when already parsed settings are sanitized again.
- Identifiers with a Cyrillic "С" on the product page (`productCharacteristics` tab) and in a theme style handle.

- In the order admin the "Server error" message was hard-coded in JS — now it is translated.
- Double `__( __( … ) )` calls in the theme menu and the order status filter.
- The "Юридическое лицо1" (legal entity) typo in the customer account.

- A fatal error when placing an order with an empty cart — now the "Cart is empty" message is shown.
- The order recount in the admin lost the cents if the price was written with a comma.
- The customer name in the checkout response was not escaped.
- The theme `style.css` had a `Plugin Name` header, so the theme was displayed without a name.

### Removed

- `README.ru.md`: the Russian version (README, AI documentation, code comments) lives in the separate `ru` branch; `README.md` links to it.

### Upgrading from 1.0.0

- Check the hosting before updating: PHP 8.3+ and WordPress 7.1+ are required.

- **Russian (ruble) stores: open Settings → Store and choose "Russian ruble (₽)" in the "Currency" field, then save.** The default currency is USD, so without this step prices are shown with `$`. Russian texts are unchanged: the ruble is still shown as "Р" (`Р/шт`); the former "руб"/"руб." labels in "Recently viewed" and the product total are now "Р" too.
- Custom code that printed `₽` next to prices should use `as_price_html()` (plugin) or `astheme_price_html()` (theme).
- No database migration is needed.

- **nginx:** close the order files folder — `location ^~ /wp-content/uploads/argon-shop-private/ { deny all; }` (for Apache the automatically created `.htaccess` is enough).
- If your code (child theme, snippets) uses **internal** plugin functions — for example, as a callback in `add_action` / `add_filter` / `remove_action` — rename them using the `docs/ai/AI_FUNCTION_NAMES.md` table. Functions called from templates keep working under the old names.
- Customers will notice: an email with a link instead of a password, drafts and unpublished products disappear from the cart, file limits are checked by the server.

## [1.0.0]

- First public release.
