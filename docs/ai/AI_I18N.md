# AI_I18N.md — translations (English, Russian, French)

Continuation of [AI.md](../../AI.md). Translations follow the WordPress rules.

## 1. How it works

- **The source language of the code is English.** All texts in PHP are written in English inside translation functions. Russian and French are translations.
- **Comments and documentation are in English too** (since 1.1.0). `tests/static-check.php` (check 6) fails on Cyrillic in PHP comments and anywhere in JS/CSS files.
- The language follows the WordPress "Site Language" setting (for the admin — the user's "Language" in the profile).
- Languages without a translation show the English text.

| | Plugin | Theme |
|---|---|---|
| Text Domain | `argon-shop` | `argon-shop-theme` |
| Folder | `plugins/argon-shop/languages/` | `themes/argon-shop-theme/languages/` |
| Template | `argon-shop.pot` | `argon-shop-theme.pot` |
| Russian | `argon-shop-ru_RU.po` / `.mo` | `ru_RU.po` / `.mo` |
| French | `argon-shop-fr_FR.po` / `.mo` | `fr_FR.po` / `.mo` |
| Loading | `as_load_textdomain()` in `argon-shop.php` (`init`) | `astheme_load_textdomain()` in `functions.php` (`after_setup_theme`) |

Theme file names contain only the locale (`ru_RU.po`), plugin files have the domain prefix. This is a WordPress rule, do not rename them.

Translations in `wp-content/languages/plugins/` and `wp-content/languages/themes/` take priority: a site can override any string without touching the plugin.

## 2. Texts in JavaScript

JS files contain **no user-facing texts in any language other than English fallbacks**. Translated texts come from PHP:

| Script | Global object | Where it is built | How JS reads it |
|---|---|---|---|
| `ars-main` (frontend ES modules) | `arsWpAjax.i18n` | `argon_shop_js_i18n()` in `argon-shop.php` (filter `argon_shop_js_i18n`) | `t( key, ...args )` from `assets/js/interface/core/config.js` |
| `ars-gallery-js` (admin gallery) | `arsGalleryI18n` | `as_add_admin_styleScript()` in `argon-shop.php` | `i18n.*` in `gallery.js` |
| `ars-modal-js` (theme modal) | `arsModalI18n` | `astheme_scripts()` in the theme `functions.php` | `i18n.*` in `modal.js` |

Adding a frontend text:

1. Add the key with the English text to `I18N_DEFAULTS` in `core/config.js`.
2. Add the same key with `__( '…', 'argon-shop' )` to `argon_shop_js_i18n()` (with a `translators:` comment if it has placeholders).
3. Use `t( 'key' )` in the module (`import { t } from '../core/config.js';`). Placeholders: `t( 'fileTooBig', name, maxMb )` for `'The file "%1$s" exceeds %2$s MB.'`.
4. Update the `.pot`/`.po`/`.mo` (section 4).

Messages that the server returns in AJAX responses are already translated in PHP.

## 3. Rules for the code

```php
// Text in HTML
<?php esc_html_e( 'Add to cart', 'argon-shop' ); ?>
// Text in an attribute
placeholder="<?php esc_attr_e( 'Your comment', 'argon-shop-theme' ); ?>"
// Text in a variable / in an AJAX response
$message = __( 'Your cart is empty. Add products and try again.', 'argon-shop' );
// Placeholders — only via sprintf, with a comment for the translator
/* translators: %s: order number. */
sprintf( __( 'Order #%s', 'argon-shop' ), $number );
// One English word with different meanings — context
_x( 'Price', 'sort by', 'argon-shop-theme' );
// Number + word — plural forms (Russian has three)
sprintf( _n( '%d product', '%d products', $count, 'argon-shop' ), $count );
```

Forbidden:

- **Writing a user-facing text without a translation function** — neither in Russian nor in English (in JS — see section 2).
- **Gluing a phrase from pieces:** `'File "' . $name . '" is larger than '` — a translator cannot change the word order. Use `sprintf` with `%1$s`, `%2$s`.
- **Passing a variable or an expression to `__()`** — the tools cannot extract the string. Only a literal: `__( 'Text', 'argon-shop' )`.
- **Mixing up domains:** plugin files use `'argon-shop'`, theme files use `'argon-shop-theme'`. A call without a domain is not allowed (that is the WordPress core domain).
- **Translating data:** array and meta keys, `name`/`value` of form fields, AJAX action names, CSS classes. Only what a person sees is translated.
- **Creating `.l10n.php`** without updating it on every change: WordPress 6.5+ prefers it to `.mo`, an outdated `.l10n.php` overrides a fresh translation. The project ships only `.po`/`.mo`.
- **Hard-coding a currency sign.** Prices are printed with `as_price_html()` / `astheme_price_html()` (see AI_EXTENDING.md, "Currency").

Deliberate exceptions (Russian in code is data, not UI) are listed in `$cyr_allow` / `$cyr_allow_files` in `tests/static-check.php`: the old `/запятая/` comma marker (still accepted next to `/comma/`), the `"Название компании"` key in saved user data, and the keyboard layout maps in `includes/interface/search/searchLanguage.php`.

Texts entered by the store owner (form field names in "Settings → Store", products, pages) are stored in the database and translated by a multilingual plugin (Polylang, WPML), not by `.po` files.

## 4. Added or changed a text — update the translations

Changing an English string = a new string: the old translation does not apply to it.

**With a terminal and WP-CLI** (from the repository root):

```bash
# 1. .pot templates from the code
wp i18n make-pot plugins/argon-shop plugins/argon-shop/languages/argon-shop.pot --domain=argon-shop --exclude=assets/vendor
wp i18n make-pot themes/argon-shop-theme themes/argon-shop-theme/languages/argon-shop-theme.pot --domain=argon-shop-theme --exclude=assets/vendor
# 2. New strings into the .po files (existing translations are kept, obsolete ones removed)
wp i18n update-po plugins/argon-shop/languages/argon-shop.pot
wp i18n update-po themes/argon-shop-theme/languages/argon-shop-theme.pot
# 3. Fill in msgstr of the new strings in all .po files (ru_RU and fr_FR)
# 4. Compile the .mo files
wp i18n make-mo plugins/argon-shop/languages
wp i18n make-mo themes/argon-shop-theme/languages
# 5. Check
php tests/static-check.php
```

**Without a terminal** (an AI in a chat editing individual files): the AI writes code with `__()` and gives an "English → Russian → French" table for the new strings in its answer. The user opens the `.pot`/`.po` in [Poedit](https://poedit.net/) ("Translation → Update from POT file"), fills in the translations and saves — Poedit creates the `.mo` itself. An AI without a terminal does not edit `.mo` (it is a binary file).

## 5. Translation quality

- The Russian translation must match what the site showed before the move to English: Russian sites look as before. Changing a Russian wording is a `.po` edit, not a code edit.
- French: a non-breaking space before `: ; ! ?` is acceptable, but the project uses a regular space — keep one style. Quotes — « ».
- Placeholders (`%s`, `%1$s`, `%d`) in a translation are the same as in the original. `tests/static-check.php` checks this.
- Currency is a store setting (Settings → Store → Currency: USD, EUR, RUB), not a translation. Only the ruble sign goes through the translation (`msgid "₽"`, Russian: "Р").

## 6. Search and the site language

The search rules depend on the site language (`as_search_language()` in `includes/interface/search/searchLanguage.php`): `ru` — QWERTY → ЙЦУКЕН layout fix (as before 1.1.0); `en` — Russian layout → QWERTY, English stop words and plural endings; `fr` — Russian layout → AZERTY, QWERTY ↔ AZERTY swaps, elisions, French stop words and plurals. Other locales use the `en` rules. Filters: `argon_shop_search_language`, `argon_shop_search_stop_words`, `argon_shop_search_layout_variants`.

## 7. A new language

1. Copy the `.pot` to `argon-shop-xx_XX.po` (plugin) and `xx_XX.po` (theme), fill in `Language` and `Plural-Forms` in the header.
2. Translate, compile the `.mo` (`wp i18n make-mo` or Poedit).
3. Add the language to the checks in `tests/static-check.php` (the `array( 'ru_RU', 'fr_FR' )` array in section 4) and to the table in section 1 of this file.
4. If the language needs its own search rules (stop words, keyboard layout), add them to `searchLanguage.php` or via the filters from section 6.
5. An entry in `CHANGELOG.md` → "Added".
