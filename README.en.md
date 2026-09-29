# ArgonShop — 2018 Legacy Version

> A self-built WordPress e-commerce plugin. Historical 2018 release
> with PHP 5.6 and jQuery 2.2 support.

**Branch:** `historic/php56-jquery`

---

## 📖 About the repository

**ArgonShop** is a full-featured e-commerce solution for WordPress, consisting of a **plugin** and a **theme** that only work together:

- **Plugin `argon-shop`** — all business logic: catalog, cart, orders, user account, discounts, search, AJAX, settings.
- **Theme `bmshop`** — layout and rendering: page templates, CSS, JS, direct calls to plugin functions.

> ⚠️ **Important:** the theme is **not self-contained**. It calls plugin functions
> directly (`as_infocart()`, `view_products_list()`, `getActualPrice()`, etc.).
> Without the plugin activated, the theme will not work.

### Stack

- **PHP 5.6** (no namespaces, global functions)
- **jQuery 2.2.2** (bundled locally in the plugin)
- **WordPress** ≥ 4.9
- **Advanced Custom Fields (ACF)** + **ACF Photo Gallery Field** add-on (required)
- **Libraries:** Fancybox 3.3.5, Slick
- **Optional:** All in One SEO Pack, Contact Form 7

---

## 🗂️ Repository map

```
ArgonShop/
│
├── AI_CONTEXT.md                          # ← AI entry point (general map)
├── README.md                              # ← Russian README
├── README.en.md                           # ← This file
│
├── docs/
│   └── AI_DATA_FLOWS.md                   # ← Cross-layer data flows
│
├── plugins/
│   └── argon-shop/                        # E-commerce plugin
│       ├── AI_CONTEXT.md                  # ← Detailed plugin context for AI
│       ├── argon-shop.php                 # Plugin entry point
│       ├── settingPage.php                # Shop settings page
│       ├── docs/
│       │   └── AI_AJAX_REFERENCE.md       # ← AJAX actions reference
│       ├── assets/
│       │   ├── admin/
│       │   │   ├── css/                   # fields-catalog.css, order.css
│       │   │   ├── js/                    # admin-script.js, orders.js
│       │   │   └── img/                   # Admin icons
│       │   └── interface/
│       │       ├── css/                   # interface-style.css
│       │       └── js/                    # jquery.js, site.js, product.js, ...
│       │           └── catalog/           # sort.js
│       └── includes/
│           ├── createPostType.php         # CPTs and taxonomies
│           ├── siteLine.php               # Replaces as_homepage label in menus
│           ├── admin/                     # Metaboxes, admin AJAX
│           │   ├── additionalFilters.php
│           │   ├── fieldsTaxonomy.php
│           │   ├── fieldsProduct.php
│           │   └── order/
│           │       ├── orderMetabox.php
│           │       ├── orderAjax.php
│           │       └── orderSave.php
│           ├── api/                       # Business logic
│           │   ├── as_action.php
│           │   ├── check.php
│           │   ├── getSetting.php
│           │   ├── getData/
│           │   │   ├── getData.php
│           │   │   ├── orders.php
│           │   │   ├── catalog.php
│           │   │   ├── product/productLists/  # marked, catalog, history
│           │   │   └── admin/                 # get_characteristics
│           │   └── view/
│           │       ├── product/productsLists/ # productsLists.php
│           │       └── catalog/               # catalogPage.php
│           └── interface/                 # Frontend AJAX handlers
│               ├── shoppingCart/          # shoppingCart.php, infoCart.php
│               ├── product/               # cardProduct.php
│               ├── catalog/               # sort.php
│               ├── cabinet/               # myCabinet.php, redirectUser.php
│               │   └── userOrders/        # ordersView.php, ordersAjax.php
│               ├── breadcrumbs.php
│               ├── pagenavi.php
│               ├── history.php
│               └── search/                # searchForm, searchAjax, searchGetFilters
│
└── themes/
    └── bmshop/                            # Theme
        ├── AI_CONTEXT.md                  # ← Detailed theme context for AI
        ├── docs/
        │   └── AI_TEMPLATE_MAP.md         # ← URL → template → functions map
        ├── functions.php                  # Menus, scripts, CPT, walkers
        ├── style.css                      # Theme metadata
        ├── header.php, footer.php, sidebar.php
        ├── index.php                      # Homepage
        ├── page.php, 404.php, search.php
        ├── archive.php, tag.php, taxonomy.php
        ├── taxonomy-catalog.php           # Catalog (key template)
        ├── single.php                     # single-* dispatcher
        ├── single-default.php
        ├── single-aktsii-istra.php
        ├── single-aktsii-moskva.php
        ├── single-product.php             # Product page
        ├── single-shoporder.php
        ├── category-aktsii-{istra,moskva}.php
        ├── categoryArchive-aktsii-{istra,moskva}.php
        ├── archive-product.php, archive-products.php
        ├── aboutCompany.php, contacts.php, addressMap.php
        ├── history.php, myCabinetPage.php, shoppingCartPage.php
        ├── includes/
        │   └── cardProduct/cardProduct.php  # generate_product_card()
        ├── template-parts/
        │   ├── cabinet/                     # account, ordering, setting
        │   ├── cart/                        # quickOrder, person, legalPerson
        │   └── productPage/                 # characteristics
        ├── libs/
        │   ├── fancybox/                    # jquery.fancybox.min.{css,js}
        │   └── slick/                       # slick.{css,min.js}
        └── assets/                          # CSS/JS grouped by block
            ├── main.css, controlPanels.css, form.css
            ├── topBlock/                    # Fixed top bar
            ├── header/                      # Header, CF7
            ├── sitebar/                     # Sidebar
            ├── cabinet/                     # User account
            ├── cart/                        # Cart
            ├── homePage/                    # Homepage
            ├── product/                     # Product page
            ├── aboutCompany/                # About company
            ├── rubrics/                     # Articles / promos
            ├── catalog/                     # Catalog
            ├── search/                      # Search
            └── footer/                      # Footer
```

---

## 🚀 Installation

1. Copy `plugins/argon-shop/` to `wp-content/plugins/`.
2. Copy `themes/bmshop/` to `wp-content/themes/`.
3. Activate the **Argon Shop** plugin in the WordPress admin.
4. Activate the **BMShop** theme.
5. Go to `Settings → Shop` and set:
   - Cart page ID,
   - Account page ID,
   - Discount system type,
   - Form fields (quick order, individual, legal entity).
6. Create pages with the appropriate templates:
   - "My Account" → template **CabinetPage**,
   - "Cart" → template **shoppingCart**,
   - "View History" → template **History**,
   - "About Company" → template **About Company**,
   - "Contacts" → template **Contacts**.
7. Configure menus (`Appearance → Menus`):
   - `top_menu` — top menu,
   - `aside_menu` — side menu (catalog),
   - `bottom_menu`, `bottom_menu2` — footer menus.
8. **Install and configure ACF + ACF Photo Gallery Field** (see next section).
9. Create the required ACF fields (see table below).

---

## 🔧 Required plugins for product images

> 💡 **Recommendation:** in the **modern version of the ArgonShop plugin**
> (see the `main` branch), product image galleries are implemented
> **by the plugin itself** — no need to install ACF Photo Gallery Field separately.
>
> If you're starting a new project, use the current plugin version.
> ACF + ACF Photo Gallery Field are required **only for this legacy branch**
> `historic/php56-jquery`.

### 8.1. Advanced Custom Fields (ACF)

**Why:** the `bmshop` theme uses `get_field()` and `the_field()` to access
additional fields for products, categories, and pages. Without ACF, some content
**will not render** (descriptions, units of measure, category images, etc.).

**Installation:**
1. `Plugins → Add New → Advanced Custom Fields`.
2. Install and activate.

### 8.2. ACF Photo Gallery Field

**Why:** in this legacy version, the **product image gallery** is implemented
via the **ACF Photo Gallery Field** add-on. This add-on saves images
into the `slider_imgs` meta field as a **comma-separated list of attachment IDs** —
this is exactly the format the `single-product.php` template reads.

**How it works in the theme code** (`themes/bmshop/single-product.php`):

```php
$post_imgs = get_post_meta( get_the_ID(), "slider_imgs", true );
$post_imgs = explode(",", $post_imgs);   // "12,34,56" → [12, 34, 56]
$imgs_count = count($post_imgs);
```

**Installation:**
1. Download the add-on from the official page:
   - https://www.advancedcustomfields.com/add-ons/photo-gallery-field/
   - or https://wordpress.org/plugins/acf-photo-gallery-field/
2. Install as a regular plugin and activate.
3. Make sure ACF is already activated (see 8.1).

**Creating the product field:**

| Parameter | Value |
|-----------|-------|
| **Field Label** | Product images |
| **Field Name** | `slider_imgs` ← **must be exactly this** |
| **Field Type** | `Photo Gallery` |
| **Location Rules** | Post Type == `product` |
| **Return Format** | `Photo Gallery` (default — comma-separated IDs) |

> ⚠️ **Key point:** the field name must be **`slider_imgs`**. If named differently,
> the product gallery will not render.

### 8.3. Required ACF fields (complete list)

Beyond the gallery, the theme uses other ACF fields. Create them via
`Custom Fields → Add New` (Field Group), attaching to the relevant
post types, taxonomies, or pages.

#### For `product`

| Field name | Type | Purpose | Used in |
|------------|------|---------|---------|
| `slider_imgs` | Photo Gallery | Product image gallery | `single-product.php` |
| `unit_product` | Text | Unit of measure (pcs, kg, m², …) | `single-product.php`, `cardProduct.php`, `shoppingCartPage.php` |
| `application` | WYSIWYG / Textarea | Product application (tab) | `single-product.php` |

#### For `catalog` taxonomy

| Field name | Type | Purpose | Used in |
|------------|------|---------|---------|
| `image_catalog` | Image | Category image | `taxonomy-catalog.php`, `sidebar.php`, `archive.php` |
| `catalog_type` | Select (`base` / `request`) | Category type: regular or popular query | `taxonomy-catalog.php`, `get_catalog_terms()` |
| `second_title` | Text | Alternative category H1 | `taxonomy-catalog.php` |
| `second_desc` | WYSIWYG | Additional category description | `taxonomy-catalog.php` |
| `bottom_desc` | WYSIWYG | Bottom category description (SEO) | `taxonomy-catalog.php` |

> **Location Rules for taxonomy:** Taxonomy Term == `catalog`.

#### For Pages

| Field name | Type | Purpose | Used in |
|------------|------|---------|---------|
| `our_advantages` | WYSIWYG | "Our advantages" text | `aboutCompany.php` |
| `our_clients` | WYSIWYG | "Our partners" text | `aboutCompany.php` |

#### For Posts

| Field name | Type | Purpose | Used in |
|------------|------|---------|---------|
| `promo_active` | True / False | Whether the promotion is active | `category-aktsii-moskva.php`, `index.php` |

#### For `ourclients` CPT (registered by the theme)

| Field name | Type | Purpose | Used in |
|------------|------|---------|---------|
| `client_link` | URL | Client website link | `aboutCompany.php` |

#### For `slider` CPT (registered by the plugin)

| Field name | Type | Purpose | Used in |
|------------|------|---------|---------|
| `slide_link` | URL | Slide link | `index.php` |

---

## 🤖 AI documentation

The repository contains a set of documents specifically prepared for
**AI assistants** (DeepSeek, Claude, GPT, Gemini, etc.) so they can
quickly navigate the codebase and produce correct answers.

> **Authorship:** All `AI_*` documents (contexts, maps, references) were
> prepared by **DeepSeek (深度求索)** based on analysis of the repository's
> source code as part of collaborative work on the project documentation.
> These are not auto-generated — the descriptions were written manually,
> taking into account the architecture and legacy-code specifics.

### Document list

| Document | Path | Purpose |
|----------|------|---------|
| **Root context** | [`AI_CONTEXT.md`](./AI_CONTEXT.md) | Entry point: repository map, key entities, technologies |
| **Plugin context** | [`plugins/argon-shop/AI_CONTEXT.md`](./plugins/argon-shop/AI_CONTEXT.md) | Detailed plugin description: modules, API, AJAX, legacy issues |
| **Theme context** | [`themes/bmshop/AI_CONTEXT.md`](./themes/bmshop/AI_CONTEXT.md) | Detailed theme description: templates, assets, plugin dependencies |
| **Data flows** | [`docs/AI_DATA_FLOWS.md`](./docs/AI_DATA_FLOWS.md) | How data flows between plugin and theme in key scenarios |
| **AJAX reference** | [`plugins/argon-shop/docs/AI_AJAX_REFERENCE.md`](./plugins/argon-shop/docs/AI_AJAX_REFERENCE.md) | Table of all AJAX actions: params, responses, JS calls |
| **Template map** | [`themes/bmshop/docs/AI_TEMPLATE_MAP.md`](./themes/bmshop/docs/AI_TEMPLATE_MAP.md) | "URL → template → key functions" mapping |

### How to use

1. **Start with** [`AI_CONTEXT.md`](./AI_CONTEXT.md) — it gives the big picture.
2. **For plugin work** — [`plugins/argon-shop/AI_CONTEXT.md`](./plugins/argon-shop/AI_CONTEXT.md).
3. **For theme work** — [`themes/bmshop/AI_CONTEXT.md`](./themes/bmshop/AI_CONTEXT.md).
4. **For a specific scenario** (cart, search, account) — [`docs/AI_DATA_FLOWS.md`](./docs/AI_DATA_FLOWS.md).
5. **For AJAX** — [`plugins/argon-shop/docs/AI_AJAX_REFERENCE.md`](./plugins/argon-shop/docs/AI_AJAX_REFERENCE.md).
6. **For URLs and templates** — [`themes/bmshop/docs/AI_TEMPLATE_MAP.md`](./themes/bmshop/docs/AI_TEMPLATE_MAP.md).

### Recommendations for AI

- **Never rename plugin functions** that the theme calls without updating the theme simultaneously.
- **Do not remove DOM selectors** that JS relies on (list is in the theme context).
- Note that the repository targets **PHP 5.6** and **jQuery 2.2** —
  do not propose PHP 7+/ES6+ syntax without an explicit migration request.
- Be aware of **legacy issues** (missing nonce, `mail()` instead of `wp_mail()`,
  `preg_replace /e`, `each()`, `extract()`) — they are listed in the contexts.
- **ACF fields are required:** if you propose changes to image logic —
  account for the `slider_imgs` format (comma-separated IDs) in `single-product.php`.

---

## 📋 Requirements

| Component | Minimum |
|-----------|---------|
| WordPress | 4.9 |
| PHP | 5.6 |
| jQuery | 2.2 (bundled) |
| MySQL | 5.6 |
| **ACF** | any current (free) |
| **ACF Photo Gallery Field** | any current |

**Tested up to:** WordPress 6.2, PHP 5.6.

> ⚠️ **Not compatible with PHP 7.2+** without fixes (`preg_replace /e` in
> `themes/bmshop/last_viewed_posts.php`, `each()` in some places).
> See the "Known legacy issues" section in `AI_CONTEXT.md`.

---

## 🔗 Useful links

- **Repository:** https://github.com/Argonstudio/ArgonShop/tree/historic/php56-jquery
- **Demo (modern version):** https://plugin.argon-studio.ru/
- **Author:** Ivan Voitkov — http://argon-studio.ru
- **License:** [GNU GPL v2.0 or later](http://gnu.org)

---

## 📝 License

```
Argon Shop WordPress Plugin, Copyright 2018 Ivan Voitkov.
BMShop WordPress Theme, Copyright 2018 Ivan Voitkov.

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <https://gnu.org>.
```

---

## 🧾 Note on AI documentation

The `AI_*` documents in this repository were prepared by **DeepSeek (深度求索)**
as part of collaborative work on the project documentation. They are **not
official documentation from the plugin author** and do not replace it.
The purpose of these documents is to help AI assistants and new developers
quickly understand the architecture and avoid common mistakes when working
with legacy code.

If discrepancies are found between the AI documentation and the actual code,
**the code takes precedence**.
