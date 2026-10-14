# AI_EXTENDING.md — how to add a feature without breaking the architecture

Continuation of [AI.md](../../AI.md). Step-by-step instructions for typical extensions.

## 1. New AJAX action

**Steps:**

1. **PHP handler.** In a suitable file under `includes/interface/<feature>/`:

```php
add_action( 'wp_ajax_my_action', 'as_my_action_callback' );
add_action( 'wp_ajax_nopriv_my_action', 'as_my_action_callback' );

/**
 * @since 1.1.0
 */
function as_my_action_callback() {
    check_ajax_referer( 'argon_shop_nonce', 'nonce' );

    // ... logic ...

    echo wp_json_encode( $result );
    wp_die();
}
```

For admin actions — only `wp_ajax_<action>`, nonce `argon_shop_order_nonce` and
`if ( ! current_user_can( 'manage_options' ) ) { wp_die( __( 'Insufficient permissions', 'argon-shop' ) ); }`.

2. **JS call.** Via `arsApi`:

```js
const data = await arsApi.postJson( 'my_action', { param1: 'value' });
```

3. **Register the action in `AI_AJAX_REFERENCE.md`.** Update the table.

4. **A nonce is required for every action.** Do not add actions to `config.skipNonceActions` (empty since 1.1.0).

5. **Function name — with the `as_` prefix** (see `AI_FUNCTION_NAMES.md`). The AJAX action name (`action`) can be anything.

6. **Add a check to `tests/smoke-test.php`** if the action changes data.

---

## 2. New ES module

**Steps:**

1. **File.** `assets/js/interface/modules/my-feature.js`:

```js
import { arsApi } from '../core/api.js';
import { t } from '../core/config.js';
import { qs, qsa, on, delegate } from '../../shared/dom.js';
import { formatPrice } from '../../shared/format.js';

class ArsMyFeature {
    #initialized = false;
    init() {
        if ( this.#initialized ) return;
        if ( ! qs( '.my-feature-root' ) ) return;
        this.#initialized = true;
        this.#bind();
    }
    #bind() { /* handlers */ }
}

export const arsMyFeature = new ArsMyFeature();
```

2. **Import in `main.js`:**

```js
import { arsMyFeature } from './modules/my-feature.js';
```

3. **Call in `initApp()`:**

```js
arsMyFeature.init();
```

4. **Contract check.** If the module listens to DOM selectors, they must be listed in `AI.md` (section 5) and `AI_TEMPLATE_MAP.md`.

5. **User-visible text** — only via `t( 'key' )`. Add the key to `I18N_DEFAULTS` in `core/config.js` (English) and to `argon_shop_js_i18n()` in `argon-shop.php` (wrapped in `__()`), then update the .pot/.po/.mo files (`AI_I18N.md`).

**Don'ts:**

- Don't use jQuery.
- Don't write `document.ready` — the module is loaded as `type="module"` (deferred, the DOM is ready).
- Don't forget `#initialized` — the module is called on every page.
- Don't put text in string literals (`'Cart is empty'`, and especially not in Russian) — that was the cause of the "Files uploaded" bug fixed in 1.1.0.

---

## 3. New product meta field

**Steps:**

1. **Admin output.** In `includes/admin/fieldsProduct.php`, function `as_mainParameters_metabox`:

```php
$myField = get_post_meta( $post->ID, '_myField', true );
?>
<li class="advanced-field">
    <div class="span-advanced-fields"><?php esc_html_e( 'My field', 'argon-shop' ); ?></div>
    <input type="text" name="_myField" value="<?php echo esc_attr( $myField ); ?>">
</li>
```

2. **Saving.** In `as_save_detailed_fields`:

```php
if ( isset( $_POST['_myField'] ) ) {
    $myField = sanitize_text_field( wp_unslash( $_POST['_myField'] ) );
    as_update_meta( $post_id, '_myField', $myField );
}
```

`as_update_meta` comes from `includes/api/as_action.php`. An empty value deletes the meta.

3. **Front-end usage.** In a template:

```php
$myField = get_post_meta( get_the_ID(), '_myField', true );
```

4. **Documentation.** Add it to `AI.md` (section 5) and to `AI_TEMPLATE_MAP.md`.

**Meta key format:** `_snake_case` with a leading underscore (like `_price`, `_article`). This hides the field from the standard "Custom Fields" box in the admin.

---

## 4. New page template

**Steps:**

1. **File.** `themes/argon-shop-theme/my-page.php`:

```php
<?php
/**
 * Template Name: My page
 *
 * @package ArgonShopTheme
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); ?>

<section id="out">
    <div class="row">
        <?php get_sidebar(); ?>
        <main class="block-content">
            <?php if ( function_exists( 'as_kama_breadcrumbs' ) ) as_kama_breadcrumbs( ' » ' ); ?>
            <?php while ( have_posts() ) : the_post(); ?>
                <h1 class="title"><?php the_title(); ?></h1>
                <?php the_content(); ?>
            <?php endwhile; ?>
        </main>
    </div>
</section>

<?php get_footer(); ?>
```

2. **Create the page in the admin** and assign the template.

3. **Styles.** If needed — in `assets/css/`, enqueued in `functions.php` via `wp_enqueue_style`.

4. **Add an entry to `AI_TEMPLATE_MAP.md`.**

---

## 5. New post type

**Steps:**

1. **Registration.** In `includes/createPostType.php` or the theme `functions.php` (depending on the CPT):

```php
add_action( 'init', 'as_register_my_post_type' ); // in the theme: astheme_register_my_post_type
function as_register_my_post_type() {
    register_post_type( 'mypost', array(
        'labels' => array(
            'name'          => __( 'My posts', 'argon-shop' ),
            'singular_name' => __( 'Post', 'argon-shop' ),
        ),
        'public' => true,
        'show_in_menu' => true,
        'supports' => array( 'title', 'editor', 'thumbnail' ),
        'has_archive' => true,
    ));
}
```

2. **Templates.** `archive-mypost.php`, `single-mypost.php` in the theme.

3. **Add an entry to `AI_ARCHITECTURE.md`** (section 6).

---

## 6. New discount rule

A complex feature. Rules:

- **Don't create your own price calculation.** Always use `as_getActualPrice` (in `includes/api/getData/getData.php`).
- **Extend the function, don't duplicate it.** New discount logic goes inside `as_getActualPrice` as conditions, not into a new function next to it.
- **Keep every call site in sync.** The discount must work the same:
  - On the product page (`product.js` → `wholesalePrice_shoppingCart`).
  - In the cart (`amountProducts_shoppingCart`).
  - At checkout (`as_submitCart_shoppingCart_callback`).
  - In emails (`as_emailTextsGenerator` prints the prices already stored in the order products — make sure they are calculated with the discount before the email is built).
  - In the order admin (`orderSave.php` → `as_getActualPriceAllProducts()`, and the AJAX recount `as_admin_recount_order_callback`).

**If even one place is missed, the discount will be calculated differently.**

---

## 7. New checkout form field

**Steps:**

1. **Settings.** Admin → Settings → Store → "User data" → add a line to the textarea of the required type (`quickLine`, `personLine`, `legalPersonLine`):

```
myField[Field name,placeholder,50]
```

Format: `ID[Name,Placeholder,class]`. `class` is `50` or `100` (field width).

2. **`as_show_user_fields` does the rest.** It renders the field in the cart forms, saves it to `accountData` on registration and shows it in the email.

3. **If the field is required**, add its key to `as_get_required_fields()` (`shoppingCart/checkout-fields.php`) or via a filter in the theme:

```php
add_filter( 'argon_shop_required_fields', function( $fields, $typeForm ) {
    if ( 'person' === $typeForm ) {
        $fields[] = 'myField';
    }
    return $fields;
}, 10, 2 );
```

The same list prints the `required` attribute in the `template-parts/cart/*.php` templates and is checked by the server — no template changes needed.

---

## 8. Payment, shipping or integration module (via hooks)

Don't touch the cart core — the module attaches to the checkout hooks.
All hooks are listed in `AI.md`, "Hooks" section.

```php
// 1. A new payment method appears in the list of allowed codes
add_filter( 'argon_shop_payment_methods', function( $methods ) {
    $methods['stripe'] = __( 'Online card payment', 'my-module' );
    return $methods;
} );

// 2. Custom check before the order is created
add_filter( 'argon_shop_checkout_errors', function( $errors, $order ) {
    if ( 'stripe' === $order['payment'] && $order['total'] < 100 ) {
        $errors[] = sprintf(
            /* translators: %s: minimum amount with currency. */
            __( 'Online payment is available for orders from %s.', 'my-module' ),
            wp_strip_all_tags( as_price_html( '100' ) )
        );
    }
    return $errors;
}, 10, 2 );

// 3. Order created — create the payment
add_action( 'argon_shop_order_created', function( $order_id, $order ) {
    if ( 'stripe' === $order['payment'] ) {
        update_post_meta( $order_id, '_payment_url', my_create_payment( $order_id, $order['total'], as_get_currency() ) );
    }
}, 10, 2 );

// 4. Response to the front end — payment link (redirect handling must be added to cart.js)
add_filter( 'argon_shop_checkout_response', function( $result, $order ) {
    if ( 'stripe' === $order['payment'] ) {
        $result['redirect'] = get_post_meta( $order['order_id'], '_payment_url', true );
    }
    return $result;
}, 10, 2 );
```

Rules:
- The module is a separate file or plugin; don't append to `checkout.php`.
- Take the product price only from `as_get_product_price()` (filter `argon_shop_product_price`).
- Pass the store currency code from `as_get_currency()` to payment gateways.
- New checks go to `tests/smoke-test.php`.

---

## 9. Currency

Since 1.1.0 the store currency is set in **Settings → Store → Currency** (`USD` by default, `EUR`, `RUB`). Amounts are stored as plain numbers; the currency is only a display setting (no conversion).

| Function (`includes/api/getSetting.php`) | Returns |
|---|---|
| `as_get_currencies()` | `code => label` list for the setting |
| `as_get_currency()` | current code (`USD` / `EUR` / `RUB`) |
| `as_get_currency_symbol( $code = '' )` | `$`, `€`, `₽` (the ruble sign goes through translation: Russian prints `Р`) |
| `as_get_currency_position( $code = '' )` | `before` (USD) or `after` (EUR, RUB) |
| `as_price_html( $amount_html, $unit = '' )` | `$<span>120</span>/pc` or `<span>120</span> €/pc` |
| `as_get_currency_per_piece()` | `$/pc`, `€/pc`… for admin labels |

In the theme use the wrappers `astheme_price_html()`, `astheme_currency_symbol()`, `astheme_currency_position()` — they keep working without the plugin.

Rules:
- Never hardcode `₽`, `$`, `€`, `руб.` in templates, emails or JS.
- Keep the number inside a `<span>` when JS updates it (cart, widget): JS changes only the number, the symbol stays in the HTML.
- **Adding a currency:**

```php
add_filter( 'argon_shop_currencies', function( $currencies ) {
    $currencies['GBP'] = __( 'Pound sterling (£)', 'my-module' );
    return $currencies;
} );
add_filter( 'argon_shop_currency_symbol', function( $symbol, $code ) {
    return 'GBP' === $code ? '£' : $symbol;
}, 10, 2 );
add_filter( 'argon_shop_currency_position', function( $position, $code ) {
    return 'GBP' === $code ? 'before' : $position;
}, 10, 2 );
```

The `argon_shop_currency` filter overrides the current code (e.g. per region in a multi-region setup).

---

## 10. Search language rules

Search rules live in `includes/interface/search/searchLanguage.php` and depend on the site locale: `ru` (keyboard layout fix QWERTY → ЙЦУКЕН, behaviour of 1.1.0), `en` (stop words, plurals, Russian layout → QWERTY), `fr` (elisions, stop words, plurals, AZERTY fixes). Other locales use `en`.

```php
// Force a language: Ukrainian site → Russian rules (other locales already fall back to 'en').
// The minimum is PHP 8.3, so str_starts_with() (PHP 8.0+) is fine.
add_filter( 'argon_shop_search_language', function( $lang, $locale ) {
    return str_starts_with( $locale, 'uk' ) ? 'ru' : $lang;
}, 10, 2 );

// Extra stop words
add_filter( 'argon_shop_search_stop_words', function( $words, $lang ) {
    if ( 'en' === $lang ) {
        $words[] = 'buy';
    }
    return $words;
}, 10, 2 );

// Extra spelling variants tried when nothing was found
add_filter( 'argon_shop_search_layout_variants', function( $variants, $searchStr, $lang ) {
    return $variants;
}, 10, 3 );
```

Rules:
- Change both search branches (AJAX and GET) through the shared functions in `searchLanguage.php`, not separately.
- Add a case to section "8. Search language" of `tests/smoke-test.php` for every new rule.

---

## 11. Multi-region: several cities, one admin

> **Status.** Only the groundwork exists in the code. The second city (Istra in the demo) is **not** finished:
> its templates are not created, its option in the city switcher is commented out. Do this section only when
> the user's task is to enable city selection / several regional sites.

### 11.1. First ask the user: WordPress Multisite?

Before writing code, **ask the user** whether they want to use **WordPress Multisite** instead. It is the
recommended option (README: "regular Multisite is recommended"). Explain the choice briefly:

| | WordPress Multisite (recommended) | One site on several domains (as on the demo stand) |
|---|---|---|
| Admin | network admin + a separate dashboard per city | one dashboard for all cities |
| Products, prices, orders, settings | separate per city (shared only via extra plugins / sync) | shared by all cities |
| City-specific content | natural — each site has its own pages, menus, promotions | via categories / meta / filters (11.4) |
| Code changes in ArgonShop | almost none (see 11.5) | the functions from 11.4 must be written |
| Risks | per-site settings, a network to maintain | host validation, caching per domain, SEO duplicates |

If the user chooses Multisite — go to 11.5 and do not create the city templates. Continue with 11.4 and 11.6
only if the user confirms the single-admin variant. If the site already runs AA-DomainMirror — read 11.3 first.

### 11.2. What already exists in the code

- `includes/siteLine.php` — `as_change_nav_menu_objects()` (filter `wp_nav_menu_objects`) replaces the
  `as_homepage` marker in menu URLs with the current domain (`as_get_safe_server_name()`, validated host,
  fallback to `home_url()`). One menu works for every city domain.
- `modules/header.js` + `.sitySelect` in `header.php` — the city switcher: the option `value` is the base URL
  of a city; `data-promocategory` / `data-promoarchive` let JS jump to the promotions page / archive of the
  other city. The second city option is **commented out** in `header.php`.
- `single.php` — dispatcher: category `36` → `single-aktsii-moskva`, category `35` → second city (template missing).
- Hard-coded city IDs and slugs: category `36` (Moscow promotions), `35` (second city promotions), `37` (articles,
  together with 36 in the header menu query), pages `639` / `642` (promotions page / archive used by the
  switcher), slugs `/aktsii-moskva/`, `/arhiv-aktsij-moskva/`, `/adres-na-karte-moskva/`.
- Filter `argon_shop_currency` — can return a currency per city.

### 11.3. Historical compatibility: the AA-DomainMirror plugin

City selection on the demo stand (and on older ArgonShop installations) was built on a separate plugin
**AA-DomainMirror** — a customized copy of "Domain Mirror" 1.0 by Dimox (http://dimox.name/wordpress-domain-mirror-plugin/).
It is **not part of this repository** and is not maintained here. What it does:

- the main domain and the mirror domains are **hard-coded** in the plugin file (`$main`, `$mirror`, `$mirror1`, e.g.
  `voitkoze.beget.tech` → `istra.voitkoze.beget.tech`, `istra1.voitkoze.beget.tech`);
- when `$_SERVER['SERVER_NAME']` equals a mirror domain, it adds filters (priority 1) on `option_siteurl`,
  `option_home`, `option_stats_url` that return `"http://" . SERVER_NAME`;
- it replaces the main domain with the current one in `the_content`, `the_excerpt`, `comment_text`,
  `get_comment_author_link`;
- everything else (products, orders, settings, users) is the same single WordPress — one admin for all cities.
  The menu works on every domain thanks to the `as_homepage` marker (`siteLine.php`).

Rules for an AI:

- If the site **still uses AA-DomainMirror**, keep it working: do not add a second set of `option_home` /
  `option_siteurl` filters, do not remove the `as_homepage` marker logic, do not rename `.sitySelect` or the promo IDs
  without the user's consent. New domains are added in the plugin file (the user edits it, or the AI after
  confirmation).
- Known limitations of the old plugin — tell the user, do not silently "fix" it: it always returns `http://`
  (no HTTPS), uses unprefixed global functions (`wpdm_filter_url`, `wpdm_filter_text`) and globals, the domain
  list is in the code, the file comments are in Windows-1251 (they show as garbage in UTF-8), and it has not
  been updated since version 1.0.
- For a new installation **do not recommend** AA-DomainMirror: offer Multisite (11.5) or writing the functions from
  11.4 inside the ArgonShop plugin.

### 11.4. Possible functions for the single-admin variant (not in the code — they must be written)

> ⚠️ **None of the functions below exist in ArgonShop.** They are a suggested design. Write them only when the
> user asked for city selection and confirmed the single-admin variant (11.1). Follow the project rules:
> `as_` prefix, `@since` of the next version, English comments, translations, a `CHANGELOG.md` line, checks
> in `tests/` (`AI_VERSIONING.md`, `AI_TESTING.md`). Do not add them "in advance".

| Suggested name (to be written) | Purpose |
|---|---|
| `as_get_regions()` + filter `argon_shop_regions` | City map — the single source of truth: `host => [ slug, name, promo category ID, promo archive page ID, map page, currency, contacts ]`. Replaces the hard-coded IDs/slugs from 11.2. Could be stored in the store settings instead of code |
| `as_get_current_region()` | Current city by the validated host (`as_get_safe_server_name()`); falls back to the main city. Accept only hosts from `as_get_regions()` — never trust `Host` / `SERVER_NAME` as is |
| `as_region_filter_home_url()` | Replacement of AA-DomainMirror: filters `option_home` / `option_siteurl` for whitelisted hosts, with the correct scheme (`is_ssl()`, HTTPS). Do not write it if AA-DomainMirror is still active |
| `as_region_filter_content()` | Replaces the main domain with the current one in content, excerpts and comments (what AA-DomainMirror did) |
| `as_region_currency()` | Callback for the existing `argon_shop_currency` filter — currency per city |
| `as_get_region_promo_category()`, `as_get_region_promo_archive()` | IDs / URLs of the promotions category and archive of the current city — used by `single.php`, the city templates and `header.php` instead of `35` / `36` / `639` / `642` |
| `as_region_switcher_options()` (or a theme function `astheme_city_select()`) | Builds the `.sitySelect` options with `value` and `data-promocategory` / `data-promoarchive` from the city map, so `header.php` has no commented-out cities |

Also to consider: the cart, history and sorting cookies are per domain (the cart is not carried over when the
city changes — tell the user, do not change the cookie format without a migration); the page cache must vary by
host; each city needs its own canonical URLs, titles and descriptions so the sites are not treated as duplicates.

### 11.5. Variant: WordPress Multisite

- One network, a site (subdomain or domain mapping, built into WordPress) per city; each city has its own
  dashboard, pages, menus, promotions, settings, products and orders.
- ArgonShop and the theme: **network-activate** the plugin, enable the theme for the network, then set
  "Settings → Store" (currency, cart / account pages, form fields) on **every** site — the options are per site.
  The activation hook creates the cart and account pages only on the site where it runs: check the other sites.
- The city switcher `.sitySelect` then only needs the URLs of the network sites. The functions from 11.4 and the
  extra `-istra` templates are not needed: every site uses the existing `-moskva` promo templates, but their
  hard-coded category / page IDs (11.2) differ between sites — make them configurable (a store setting) instead of
  copying templates.
- Shared catalog / orders across cities are **not** provided by Multisite — if the user needs them, this is an
  argument for the single-admin variant.
- Before promising Multisite support, test the plugin on a network on a test site (`tests/smoke-test.php` on each site).

### 11.6. City templates (single-admin variant)

For every additional city (the second city in the demo is `istra`):

1. Create `single-aktsii-<city>.php`, `category-aktsii-<city>.php` and `categoryArchive-aktsii-<city>.php` as copies
   of the `-moskva` templates; replace category `36` and the `-moskva` slugs with the city values (better — read
   them from the city map, 11.4). Template Name for the archive: "Promotions archive (<City>)".
2. Fix the dispatcher in `single.php`: `get_template_part( 'single-aktsii-istra' )` — **without** `.php`
   (the current call with `.php` never finds a file).
3. Enable the city option in `.sitySelect` (`header.php`) with the city base URL and the
   `data-promocategory` / `data-promoarchive` attributes; city names go through `esc_html_e( …, 'argon-shop-theme' )`
   and the translations (`AI_I18N.md`).
4. Do not rename `.sitySelect`, `#block-contentPromo`, `#block-contentPromoPage`, `#block-contentPromoArchive` —
   `header.js` depends on them.
5. Update `AI_TEMPLATE_MAP.md` (remove the "not created yet" marks), add a `CHANGELOG.md` → `[Unreleased]` line,
   run `php tests/static-check.php`; on a test site — smoke "9. Pages open without errors" on every city domain.

---

## 12. What not to add

| Feature | Why |
|---|---|
| Custom ORM | `WP_Query` and meta fields are idiomatic for WP |
| React / Vue | Gains nothing on the storefront, kills Vanilla JS |
| Custom REST API | If needed — standard `register_rest_route`, no custom auth |
| A second template set | One theme is enough for the store |
| jQuery on the front end | Regression |

---

## 13. Pre-commit checklist

- [ ] PHP: `if ( ! defined( 'ABSPATH' ) ) exit;` at the top of the file.
- [ ] PHP: output escaping (`esc_html`, `esc_attr`, `esc_url`).
- [ ] PHP: `absint` / `sanitize_*` on incoming data.
- [ ] AJAX: `check_ajax_referer`.
- [ ] AJAX: `wp_die()` at the end.
- [ ] JS: module via `export const arsXxx = new ArsXxx()`.
- [ ] JS: imported in `main.js`, called in `initApp()`.
- [ ] JS: no jQuery; user-visible text only via `t()`.
- [ ] CSS: only styling in the theme, functional states in `interface-style.css`.
- [ ] PHP: new functions use the `as_` prefix and `@since` of the next version (`AI_VERSIONING.md`); old names are not brought back.
- [ ] Text: in English inside `__()` / `esc_html_e()` with the `argon-shop` or `argon-shop-theme` domain, placeholders via `sprintf`; ru_RU and fr_FR translations updated (`AI_I18N.md`).
- [ ] Prices: only via `as_price_html()` / theme wrappers; no hardcoded currency signs.
- [ ] Comments and docs: in English (`static-check` fails on Cyrillic in code comments).
- [ ] PHP: the product price only from `as_get_product_price()` / `as_sanitize_cart()`, never from the cookie or the form.
- [ ] PHP/JS: human-entered prices and wholesale thresholds use `as_to_float()` / `parseNumber()`; highest reached threshold wins, independent of order.
- [ ] Passwords: string type check and `wp_unslash()` only; no sanitizers, no trimming, strict empty-string comparison, UTF-8 character count.
- [ ] Data: if a meta/cookie/option format changes — a migration in `includes/install.php` (`AI_VERSIONING.md`, section 5).
- [ ] Tests: `php tests/static-check.php` and `wp eval-file tests/smoke-test.php` all green; a new feature gets a new check (`AI_TESTING.md`). Without a terminal, tell the user what to run and what to check manually.
- [ ] Documentation: `AI_*.md` updated if a contract was added; a line in `CHANGELOG.md` → `## [Unreleased]`. Don't bump the version without a release.

## Numeric input contract

`as_to_float()` and JS `parseNumber()` follow the same rules, tested by `tests/number-cases.json`:

- `60,33` and `60.33` → `60.33`; whitespace including non-breaking/narrow spaces is removed.
- With both comma and dot, the last is decimal and the other must group exactly three digits: `1,234.56` / `1.234,56` → `1234.56`.
- A single comma/dot is decimal: `1,234` means `1.234`, **not 1234**. Repeated separators must form valid thousands groups: `1,234,567` / `1.234.567` → `1234567`.
- Signs and scientific notation are supported (`1e3` → `1000`); empty, malformed, non-string/non-number and non-finite values return zero (`abc60,33`, `1,23,45`, `1e309`). These parsers do not enforce positive prices; business validation is separate.
- Parsing must consume the complete normalized string. Do not restore prefix-only `floatval()` / `parseFloat()` on human-entered fields.
- Price storage and cookie formats are not migrated. Malformed stored prices that previously yielded a numeric prefix now read as zero; review such data before deployment.
