# AI_ARCHITECTURE.md — layers, modules, folder map

Continuation of [AI.md](../../AI.md). This file explains how the project is organized internally.

## 1. Monorepo

```
plugins/argon-shop/          Store logic
themes/argon-shop-theme/     Markup and styles
```

**The plugin without the theme** renders nothing on the front end — its functions are simply never called. **The theme without the plugin** does not work — there is no `product` CPT, no `catalog` taxonomy, no `as_search` function, etc.

## 2. Plugin: three levels

### 2.1. Entry point — `argon-shop.php`

Reads constants, defines paths, registers the `admin_enqueue_scripts` and `wp_enqueue_scripts` hooks, localizes JS (`arsWpAjax` including `arsWpAjax.i18n`, `myajax`, `arsGalleryI18n`), loads every module via `require_once`, adds `type="module"` to `main.js`.

The `require_once` order is roughly: settings page, install, legacy wrappers, API (`as_action.php`, `check.php`, `getData/`, `getSetting.php`), VIEW (`view/`), sliders, post types, admin meta boxes, cart and checkout, account, breadcrumbs/pagination, search, history, order admin, `siteLine.php`. Admin and interface files are interleaved — check `argon-shop.php` before relying on load order.

`includes/interface/last_viewed_posts.php` (WP-LastViewedPosts fork) is **not loaded** by `argon-shop.php` — it is dead code kept for reference. "Recently viewed" works through `history.php` and the `AS_History` cookie.

### 2.2. The `includes/` folder

| Subfolder | Responsible for |
|---|---|
| `includes/*.php` (root) | `createPostType.php` — CPT and taxonomies; `siteLine.php` — multi-region support (the `as_homepage` menu marker, see `AI_EXTENDING.md`, section 11); `install.php` — activation/deactivation, migrations (`as_maybe_upgrade()`, `as_get_migrations()`); `legacy-functions.php` — wrappers for old function names (before 1.1.0) |
| `includes/admin/` | Admin meta boxes and hooks: product, order, filters, gallery |
| `includes/api/` | Internal API: data retrieval (`getData/`), output (`view/`), settings, checks |
| `includes/interface/` | Public interfaces: cart, customer account, search, sliders, pagination, breadcrumbs |

`includes/api/getSetting.php` also holds the **currency helpers** (since 1.1.0): `as_get_currencies()`, `as_get_currency()`, `as_get_currency_symbol()`, `as_get_currency_position()`, `as_price_html()`, `as_get_currency_per_piece()`. All prices must be printed through them — never hardcode a currency sign.

### 2.2.1. Cart and checkout — `includes/interface/shoppingCart/`

`shoppingCart.php` only loads the modules:

| File | Responsible for |
|---|---|
| `cart-ajax.php` | Cart AJAX: add, change quantity, remove, clear |
| `checkout.php` | Checkout: `as_submitCart_shoppingCart_callback()` — orchestrator, `as_checkout_create_order()`, `as_checkout_error()`, delivery/payment methods |
| `checkout-fields.php` | Form fields, required fields (`as_get_required_fields()`) |
| `checkout-files.php` | Validation and private storage of order files |
| `checkout-registration.php` | Customer registration during checkout (set-password link) |
| `checkout-emails.php` | Order emails (`as_checkout_send_emails()`, `as_emailTextsGenerator()`) |
| `infoCart.php` | Cart widget `as_infocart()` (loaded separately from `argon-shop.php`) |

### 2.2.2. Search — `includes/interface/search/`

| File | Responsible for |
|---|---|
| `searchForm.php` | Search form `as_search()` |
| `searchAjax.php` | AJAX search handler |
| `searchGetFilters.php` | Search query building / filters |
| `searchLanguage.php` | Language-aware query normalization (since 1.1.0): rules for Russian, English and French (wrong-keyboard-layout fixes incl. AZERTY, stop words, plural/elision normalization). Language comes from the site locale (`as_search_language()`); filters `argon_shop_search_language`, `argon_shop_search_stop_words`, `argon_shop_search_layout_variants` |

### 2.2.3. Plugin lifecycle

- `register_activation_hook` → `includes/install.php`: creates (or finds) the cart and account pages, fills default form fields and settings (`currency` = `USD`), flushes rewrite rules.
- `init` (priority 1) → `as_maybe_upgrade()`: compares the stored version with `ARGON_SHOP_VERSION` and runs migrations.
- `uninstall.php` (plugin root): removes settings, service options and private order files. Products, orders, users and the order counter are **kept**.

**The `api/` folder is not a REST API.** It contains internal PHP functions for retrieving data. The project has no REST endpoints; everything goes through `admin-ajax.php`.

### 2.3. The `assets/` folder

- `assets/css/interface/interface-style.css` — functional CSS (initial states controlled by JS).
- `assets/css/admin/` — meta box styles.
- `assets/js/interface/` — front-end ES modules.
- `assets/js/admin/modules/` — admin scripts (jQuery).
- `assets/vendor/` — Swiper and GLightbox with their licenses preserved.

## 3. Front-end ES module structure

```
assets/js/interface/
├── main.js                    Entry point
├── core/                      Core
│   ├── config.js              url, nonce, cartUrl, i18n strings, t()
│   ├── api.js                 fetch client (arsApi)
│   └── nonce.js               XMLHttpRequest interceptor
├── shared/                    Shared utilities
│   ├── dom.js                 qs, qsa, on, delegate
│   └── format.js              formatPrice, formatWeight, formatAmount, parseNumber
└── modules/                   21 feature modules
    ├── cart.js                cart: quantity, removal, submit
    ├── cart-form.js           form: registration, files
    ├── cart-widget.js         header cart widget
    ├── product.js             product page
    ├── card-product.js        buttons in product cards
    ├── add-to-cart.js         shared add-to-cart logic
    ├── search.js              AJAX search
    ├── sort.js                sorting
    ├── cabinet.js             customer account
    ├── orders.js              order filter
    ├── consent.js             consent checkboxes
    ├── control-panels.js      tabs
    ├── mobile-menu.js         mobile menu
    ├── menu-catalog.js        catalog menu
    ├── header.js              city selection (multi-region, AI_EXTENDING.md §11)
    ├── contact-form.js        CF7 in a modal
    ├── catalog-page.js        mobile catalog blocks
    ├── request.js             popular queries
    ├── slider-main.js         home page slider
    ├── slider-product.js      product slider
    └── history.js             recently viewed
```

### 3.1. Module pattern

Feature modules export a singleton with an `init()` method. Exceptions: `add-to-cart.js` exports the function `addProductToCart()`, `cart-widget.js` (`arsCartWidget`) and `history.js` (`arsHistory`) export helpers without page initialization; they are imported by other modules, not by `main.js`. The method is idempotent and does nothing when its DOM elements are missing.

```js
class ArsModule {
    #initialized = false;
    init() {
        if ( this.#initialized ) return;
        if ( ! qs( '.some-selector' ) ) return;
        this.#initialized = true;
        // handlers
    }
}
export const arsModule = new ArsModule();
```

`initApp()` in `main.js` calls `initNonceInterceptor()` and `init()` of the 18 feature modules on every page. This is safe — each module decides for itself.

### 3.2. User-visible strings in JS

JS never contains hardcoded user-visible text. Use `t( 'key' )` from `core/config.js`: it reads translated strings from `arsWpAjax.i18n` (built by `argon_shop_js_i18n()` in PHP, filter `argon_shop_js_i18n`) and falls back to the English `I18N_DEFAULTS`. Admin gallery uses `arsGalleryI18n`; the theme modal uses `arsModalI18n`. See `AI_I18N.md`.

### 3.3. Why no jQuery

- No implicit dependencies.
- No load-order issues (handlers are delegated on `document`).
- No competition for `$`.
- Smaller payload.

### 3.4. Tests — `tests/`

- `tests/static-check.php` — checks without WordPress (`php tests/static-check.php`): syntax, translations, versions, no Cyrillic in code comments.
- `tests/smoke-test.php` — end-to-end check on a test site via WP-CLI (`wp eval-file tests/smoke-test.php`), works with any site language.

Not part of the production build. Details: `tests/README.md`, `AI_TESTING.md`.

### 3.5. Translations — `languages/`

`plugins/argon-shop/languages/` (`argon-shop.pot`, `argon-shop-ru_RU.po|mo`, `argon-shop-fr_FR.po|mo`) and `themes/argon-shop-theme/languages/` (`argon-shop-theme.pot`, `ru_RU.po|mo`, `fr_FR.po|mo`). The source language of code, comments and docs is English. Details: `AI_I18N.md`.

## 4. Theme: layers

```
themes/argon-shop-theme/
├── style.css                  Theme metadata (Theme Name, version, Requires at least 7.1, Requires PHP 8.3)
├── functions.php              Script loading, ourclients/sertificates CPT, currency wrappers
├── header.php, footer.php,    Global templates
│   sidebar.php
├── index.php, page.php,       Page templates by type
│   404.php, search.php, ...
├── single-*.php,              Single entries
│   category-*.php, taxonomy-*.php
├── template-parts/            Template parts
│   ├── cabinet/               Account tabs
│   ├── cart/                  Order forms
│   └── productPage/           Product page blocks
├── includes/cardProduct/      generate_product_card
└── assets/                    Theme JS and CSS
```

**Where things live:**

- **Page templates** — in the theme root (`single-product.php`, `shoppingCartPage.php`).
- **Template parts** — in `template-parts/`.
- **Styles** — in `assets/css/`, split by block (home, cart, account, catalog).
- **Theme JS** — `assets/js/` (modal, search expand, certificate slider).
- **Prices** — through `astheme_price_html()`, `astheme_currency_symbol()`, `astheme_currency_position()` (wrappers around the plugin currency helpers that degrade gracefully without the plugin).

## 5. Plugin–theme touch points

**The theme calls plugin functions** in templates:

| Function | Plugin file |
|---|---|
| `as_kama_breadcrumbs` | `includes/interface/breadcrumbs.php` (declared as `AS_Kama_Breadcrumbs()`; PHP function names are case-insensitive) |
| `as_kama_pagenavi` | `includes/interface/pagenavi.php` |
| `as_search` | `includes/interface/search/searchForm.php` |
| `as_infocart` | `includes/interface/shoppingCart/infoCart.php` |
| `as_sort` | `includes/api/view/catalog/catalogPage.php` (the AJAX handler `as_sortingCatalog_callback` is in `includes/interface/catalog/sort.php`) |
| `as_slider_main` | `includes/interface/slider/sliderMain.php` |
| `as_slider_product` | `includes/interface/slider/sliderProduct.php` |
| `as_get_catalog_terms` | `includes/api/getData/catalog.php` |
| `as_view_menu_elements` | `includes/api/getData/catalog.php` |
| `as_splitMenu` | `includes/api/getData/catalog.php` |
| `as_view_products_list` | `includes/api/view/product/productsLists/productsLists.php` |
| `as_get_sort_products` | `includes/api/getData/product/productLists/catalog.php` |
| `as_get_history_products` | `includes/api/getData/product/productLists/history.php` |
| `as_get_marked_products` | `includes/api/getData/product/productLists/marked.php` |
| `as_getCookie`, `as_getDataCart`, `as_getActualPrice`, `as_getClassActiveItem` | `includes/api/getData/getData.php` |
| `as_get_cartPageURL`, `as_get_cabinetPageURL`, `as_show_user_fields`, `as_check_saleSteps` | `includes/api/getSetting.php` |
| `as_price_html`, `as_get_currency_symbol`, `as_get_currency_position` | `includes/api/getSetting.php` (via theme wrappers) |
| `as_button_card_product` | `includes/interface/product/cardProduct.php` |
| `as_view_user_orders` | `includes/interface/cabinet/userOrders/ordersView.php` |
| `as_get_charact` | `includes/api/getData/admin/get_characteristics.php` |

If a function was renamed in 1.1.0, the old name keeps working via `includes/legacy-functions.php` (see `AI_FUNCTION_NAMES.md`). New code must use only the new names.

**The theme depends on scripts registered by the plugin.** `astheme_scripts()` in the theme `functions.php` enqueues `slider-sertificates.js` with the dependencies `swiper-js` and `glightbox-js`, which are enqueued by the plugin (`argon-shop.php`). Without the plugin these handles do not exist and WordPress does not print the theme script.

## 6. CPT and taxonomy registration

Everything is in `includes/createPostType.php`:

| Entity | Name | Type |
|---|---|---|
| Product | `product` | CPT, public |
| Order | `shoporder` | CPT, `publicly_queryable => false` |
| Slide | `slider` | CPT, public |
| Catalog | `catalog` | Hierarchical taxonomy |
| Characteristics | `characteristics` | Hierarchical taxonomy |
| Wholesale price steps | `wholesalePrice` | Flat, registered conditionally |
| Order statuses | `statusorders` | Flat |
| Clients (theme) | `ourclients` | CPT, registered in the theme `functions.php` |
| Certificates (theme) | `sertificates` | CPT, registered in the theme `functions.php` |

## 7. Third-party plugin dependencies

**ACF** — optional. Used for the fields `_hit` (in `last_viewed_posts.php`, not loaded), `unit_product`, `application`, `second_title`, `second_desc`, `bottom_desc`, `image_catalog`, `catalog_type`, `our_advantages`, `our_clients`. Without ACF, `get_field()` is not called and the blocks are simply empty.

**Contact Form 7** — optional. Used for the feedback form. Without it the form is not rendered.

**No other required dependencies.** The plugin and theme are self-contained.
