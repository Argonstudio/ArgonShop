# AI_ARCHITECTURE.md — layers, modules, folder map

Continuation of [AI.md](../../AI.md). Here — how the project is structured internally.

## 1. Monorepo

```
plugins/argon-shop/          Shop logic
themes/argon-shop-theme/     Markup and styles
```

**Plugin without the theme** outputs nothing on the frontend — its functions are simply never called. **Theme without the plugin** does not work — there is no `product` CPT, no `catalog` taxonomy, no `as_search` function, etc.

## 2. Plugin: three levels

### 2.1. Entry point — `argon-shop.php`

Reads constants, defines paths, registers the `admin_enqueue_scripts` and `wp_enqueue_scripts` hooks, localizes JS (`arsWpAjax`, `myajax`), includes all modules via `require_once`, adds `type="module"` to `main.js`.

The order of `require_once` follows the layers: first API, then VIEW, then interfaces, then admin.

### 2.2. The `includes/` folder

| Subfolder | Responsible for |
|---|---|
| `includes/*.php` (root) | `createPostType.php` — CPTs and taxonomies; `siteLine.php` — multi-region support |
| `includes/admin/` | Meta boxes and admin hooks: product, order, filters, gallery |
| `includes/api/` | Internal API: data retrieval (`getData/`), output (`view/`), settings, checks |
| `includes/interface/` | Public interfaces: cart, account, search, sliders, pagination, breadcrumbs |

**The `api/` folder is not a REST API.** These are internal PHP functions for data retrieval. There are no REST endpoints in the project; everything goes through `admin-ajax.php`.

### 2.3. The `assets/` folder

- `assets/css/interface/interface-style.css` — functional CSS (initial states controlled by JS).
- `assets/css/admin/` — meta box styles.
- `assets/js/interface/` — frontend ES modules.
- `assets/js/admin/modules/` — admin scripts (with jQuery).
- `assets/vendor/` — Swiper and GLightbox with retained licenses.

## 3. Frontend ES module structure

```
assets/js/interface/
├── main.js                    Entry point
├── core/                      Core
│   ├── config.js              url, nonce, cartUrl
│   ├── api.js                 fetch client (arsApi)
│   └── nonce.js               XMLHttpRequest interceptor
├── shared/                    Shared utilities
│   ├── dom.js                 qs, qsa, on, delegate
│   └── format.js              formatPrice, formatWeight, parseNumber
└── modules/                   21 functional modules
    ├── cart.js                cart: quantity, removal, submit
    ├── cart-form.js           form: registration, files
    ├── cart-widget.js         cart widget in the header
    ├── product.js             product page
    ├── card-product.js        buttons in product cards
    ├── add-to-cart.js         shared add-to-cart logic
    ├── search.js              AJAX search
    ├── sort.js                sorting
    ├── cabinet.js             user account
    ├── orders.js              order filter
    ├── consent.js             consent checkboxes
    ├── control-panels.js      tabs
    ├── mobile-menu.js         mobile menu
    ├── menu-catalog.js        catalog menu
    ├── header.js              city selection
    ├── contact-form.js        CF7 in a modal
    ├── catalog-page.js        mobile catalog blocks
    ├── request.js             popular queries
    ├── slider-main.js         home page slider
    ├── slider-product.js      product slider
    └── history.js             view history
```

### 3.1. Module pattern

Each module exports a singleton with an `init()` method. The method is idempotent; if its DOM elements are absent, it does nothing.

Pattern:

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

`initApp()` in `main.js` calls `init()` on every module on every page. This is safe — each module decides for itself what to do.

### 3.2. Why no jQuery

- No implicit dependencies.
- No load-order issues (handlers are attached via delegation on `document`).
- No competition for `$`.
- Less weight.

## 4. Theme: layers

```
themes/argon-shop-theme/
├── style.css                  Theme metadata (header only)
├── functions.php              Enqueue scripts, register ourclients/sertificates CPTs
├── header.php, footer.php,    Global templates
│   sidebar.php
├── index.php, page.php,       Page templates by type
│   404.php, search.php, ...
├── single-*.php,              Single posts
│   category-*.php, taxonomy-*.php
├── template-parts/            Partial templates
│   ├── cabinet/               Account tabs
│   ├── cart/                  Checkout forms
│   └── productPage/           Product page blocks
├── includes/cardProduct/      generate_product_card
└── assets/                    Theme JS and CSS
```

**What goes where:**

- **Page templates** — directly in the theme root (`single-product.php`, `shoppingCartPage.php`).
- **Template parts** — in `template-parts/`.
- **Styles** — in `assets/css/`, grouped by block (home, cart, account, catalog).
- **Theme JS** — `assets/js/` (modal, search expansion, certificates slider).

## 5. Touchpoints between plugin and theme

**The theme calls plugin functions** in templates:

| Function | Plugin file |
|---|---|
| `kama_breadcrumbs` | `includes/interface/breadcrumbs.php` |
| `kama_pagenavi` | `includes/interface/pagenavi.php` |
| `as_search` | `includes/interface/search/searchForm.php` |
| `as_infocart` | `includes/interface/shoppingCart/infoCart.php` |
| `as_sort` | `includes/interface/catalog/sort.php` |
| `as_slider_main` | `includes/interface/slider/sliderMain.php` |
| `as_slider_product` | `includes/interface/slider/sliderProduct.php` |
| `get_catalog_terms` | `includes/api/getData/catalog.php` |
| `view_menu_elements` | `includes/api/getData/catalog.php` |
| `splitMenu` | `includes/api/getData/catalog.php` |
| `view_products_list` | `includes/api/view/product/productsLists/productsLists.php` |
| `get_sort_products` | `includes/api/getData/product/productLists/catalog.php` |
| `get_history_products` | `includes/api/getData/product/productLists/history.php` |
| `get_marked_products` | `includes/api/getData/product/productLists/marked.php` |
| `getCookie`, `getDataCart`, `getActualPrice` | `includes/api/getData/getData.php` |
| `get_cartPageURL`, `get_cabinetPageURL`, `getClassActiveItem`, `show_user_fields`, `check_saleSteps` | `includes/api/getSetting.php` |
| `button_card_product` | `includes/interface/product/cardProduct.php` |
| `view_user_orders` | `includes/interface/cabinet/userOrders/ordersView.php` |
| `get_charact` | `includes/api/getData/admin/get_characteristics.php` |

**The plugin enqueues theme JS** through dependencies — for example, `slider-sertificates.js` depends on `swiper-js` and `glightbox-js`.

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
| Clients (theme) | `ourclients` | CPT, registered in the theme's `functions.php` |
| Certificates (theme) | `sertificates` | CPT, registered in the theme's `functions.php` |

## 7. Third-party plugin dependencies

**ACF** — optional. Used for the fields `unit_product`, `application`, `second_title`, `second_desc`, `bottom_desc`, `image_catalog`, `catalog_type`, `our_advantages`, `our_clients`. If ACF is not present, `get_field()` calls are skipped and the blocks are simply empty.

**Contact Form 7** — optional. Used for the contact form. If absent, the form is not rendered.

**There are no other mandatory dependencies.** The plugin and the theme are self-contained.
