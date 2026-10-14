# ArgonShop

[РУССКАЯ ВЕРСИЯ](../../tree/ru) — Russian version: the `ru` branch (Russian README, AI documentation and code comments).

WordPress e-commerce plugin.

> [!IMPORTANT]
> For any questions about the plugin, its operation, or installation, feel free to consult DeepSeek, [ChatGPT](https://chatgpt.com/s/t_6abe8276fabc8191a0389d716ed8ace2), or other AI assistants. The plugin includes a dedicated AI documentation set (AI.md, AI_ARCHITECTURE.md, and related files) specifically designed to provide AIs with accurate context. Note: since the site theme examples and comments are in Russian, using an AI assistant will make it much easier for English-speaking readers (and speakers of other languages) to navigate and work with the code.

> 🤖 **Want to customize the plugin for your project with AI?** See [Recommended AI for Customizing the Plugin](#-recommended-ai-for-customizing-the-plugin).

---

## Table of Contents

- [✨ Key Features and Advantages](#-key-features-and-advantages)
- [📺 Demo and Video Overview](#-demo-and-video-overview)
- [📊 Technical Overview: Why ArgonShop Is Faster Than Heavy CMS Platforms](#-technical-overview-why-argonshop-is-faster-than-heavy-cms-platforms)
- [🧩 Development Philosophy](#-development-philosophy-why-vanilla-wordpress-core-is-a-strength)
- [🚀 Features](#-features)
- [🛠️ Technology Stack](#%EF%B8%8F-technology-stack)
- [⚙️ Requirements](#%EF%B8%8F-requirements)
- [📦 Installation](#-installation)
- [📁 Repository Structure](#-repository-structure)
- [🔌 Plugin Structure](#plugin-argon-shop)
- [🖼️ Theme Structure](#theme-argon-shop-theme)
- [🔄 How It Works](#-how-it-works)
- [🌐 Languages](#-languages)
- [🧪 Tests](#-tests)
- [📚 Third-Party Libraries](#-third-party-libraries)- 
- [🤖 Recommended AI for Customizing the Plugin](#-recommended-ai-for-customizing-the-plugin)
- [📄 License](#-license)

## ✨ Key Features and Advantages

1. **🧱 Full UI/UX Independence (Custom Templating)**
   PHP templates and CSS styles are mostly placed in the site's theme. Even complex sections such as the shopping cart and user account are built with a unique design without the need to override hard-coded plugin styles.

2. **⚡ Maximum Performance**
   The plugin core and the demo theme are optimized for instant loading and score **100/100** in Google PageSpeed Insights. The store works reliably even on slow connections or through sluggish VPN services.

3. **📊 Big Data Optimized (50,000+ Products)**
   The database architecture handles huge catalogs without speed loss or increased server response time. The plugin is an excellent lightweight alternative for projects that started suffering from performance issues on heavy CMS platforms (e.g. *1C-Bitrix*).

   **Here is a real test example**: [https://pagespeed.web.dev/analysis/https-plugin-argon-studio-ru/](https://pagespeed.web.dev/analysis/https-plugin-argon-studio-ru/ji0g2b2rwv?form_factor=desktop)

   As of 2026, it scores a perfect 100/100 on desktop and 99 on mobile, with 100/100 across all optimization recommendations.   
   The Speed Index is between 0.8 and 1.1 seconds — nearly three times faster than Google's "Good" benchmark. Best of all, the test theme uses no caching or code optimization plugins, and the code isn't even compressed.
   It achieves this performance purely through the plugin's clean architecture.

5. **🔍 Smart AJAX Search Without Reloads**
   Intelligent "live" search displays results while the user is typing. Search scopes are configurable via the admin panel without touching the code. Complex ranking mechanisms are implemented, and AJAX results are synchronized with the standard results page.

6. **📂 Open Source and Transparent License**
   The project is distributed under the **GNU GPL v3.0** license. Source code is thoroughly documented inside files, allowing developers to freely study, modify, and scale the system for their business needs.

7. **🚀 Modern Technology Stack**
   The plugin architecture is fully adapted and tested on current **PHP 8.5** and **WordPress 7**. All frontend client logic is rewritten in pure native JavaScript (*Vanilla JS*), eliminating unnecessary dependencies.

8. **⚙️ High Ecosystem Compatibility**
   The plugin integrates seamlessly with the standard WordPress environment. It works correctly with popular SEO modules, the *Advanced Custom Fields (ACF)* plugin, and other common solutions. No critical conflicts with third-party software were found.

9. **📦 Lightweight JS Library Integration**
   The frontend bundle includes modern third-party libraries with open licenses for interactive elements, as well as custom modals for displaying interactive maps and feedback forms:
   * 🛒 **[Swiper 12.2.0](https://github.com/nolimits4web/swiper)** (*MIT License*) — touch sliders and product galleries.
   * 🖼️ **[GLightbox 3.3.1](https://github.com/biati-digital/glightbox)** (*MIT License*) — responsive modals and media viewing.

10. **🛠️ Modernized Classic Solutions (WP-Kama) for PHP 8.5**
   The plugin includes and deeply reworks popular interface modules by Timur Kamaev (Kama). The ten-year-old original code was fully freed from legacy issues and vulnerabilities and adapted to the strict standards of modern servers:
   * 🍞 **[Breadcrumbs module (breadcrumbs.php)](https://github.com/Argonstudio/ArgonShop/blob/main/plugins/argon-shop/includes/interface/breadcrumbs.php)** — protected from direct calls via `ABSPATH`, switched to the safe `get_search_query()` method, with strict validation of input data types against `null` and `WP_Error`.
   * 🔢 **[Pagination module (pagenavi.php)](https://github.com/Argonstudio/ArgonShop/blob/main/plugins/argon-shop/includes/interface/pagenavi.php)** — completely free of the deprecated `extract()` function, loose comparisons (`==`) replaced with safe strict ones (`===`), and hardware-level protection against division by zero added.

11. **🤖 AI-Ready Architecture**
    The plugin is designed to be simple and understandable for AI — just give the AI a link and it will be able to write the necessary modules.     

    ### 📈 **GPT-6 Luna review:** [View Full Chat Review](https://chatgpt.com/share/6acfa646-1674-83eb-8b56-0674a05ed2bf), My Understandability Scores
    *These scores measure how easily an AI assistant can comprehend the codebase, navigate it, predict side effects, and safely implement new functionality.*

    * **ArgonShop 1.0.0** — `8.0 / 10`
      > Good architecture and documentation, but more effort required to understand some internal relationships.
    * **ArgonShop 1.1.0** — `9.2 / 10`
      > 🚀 **Verdict:** Very high readability for AI-assisted development, with explicit architectural contracts and testing procedures.

    #### 📊 Detailed Breakdown & Comparison
    
    | Assessment Area | Version 1.0.0 | Version 1.1.0 | Key Improvements & Notes |
    | :--- | :---: | :---: | :--- |
    | **🏢 Overall architecture** | 8.0 | **9.0** | Clearer boundaries and decoupled modules in the newer version. |
    | **🐘 PHP code navigation** | 8.0 | **9.0** | Enhanced class structures and better type-hinting/strict typing. |
    | **🟨 JavaScript organization** | 8.5 | **8.5** | Remained stable, well-structured, and easy to maintain. |
    | **🔄 Understanding data flows** | 8.0 | **9.0** | Predictable state mutations and explicit data pipelines. |
    | **📝 Documentation** | 8.5 | **9.5** | Exceptional inline comments and comprehensive README/API specs. |
    | **🤖 AI-assisted development** | 8.0 | **9.5** | Code patterns are highly optimized for LLM context windows. |
    | **🛡️ Safe modification & regression** | 7.0 | **9.5** | Robust testing procedures and architectural contracts reduce breakages. |


    

---
🌐 Live Demo: [https://plugin-en.argon-studio.ru/](https://plugin-en.argon-studio.ru/) ( Test access: login **admin**, password **123** )
---

## 📺 Demo and Video Overview

📺 [Watch the ArgonShop video overview](https://www.youtube.com/watch?v=QPdLPoL79vg)

> 💡 **Note about the video:** The video is in Russian, but you can enable automatic YouTube subtitles/translation. Also, please note that the video mentions different search result outputs between standard GET search and AJAX requests. This and other minor issues have been completely fixed in the current version of the plugin—the search logic was substantially rewritten, so please refer directly to the code. Most of the video remains fully relevant, and all recent architectural changes are well-documented in `AI.md`.

---

## 📊 Technical Overview: Why ArgonShop Is Faster Than Heavy CMS Platforms

Unlike monolithic systems (such as *1C-Bitrix* or *Magento*) that are overloaded with built-in business tools (CRM, warehouse accounting, end-to-end analytics), **ArgonShop focuses solely on the e-commerce storefront**.

The plugin is based on the standard **WordPress 7 API**, creating custom post types, taxonomies, and meta fields, and fully trusting data storage to the optimized WordPress core.

### ⚔️ Performance Comparison on Large Catalogs (50,000+ Products)

| Criterion | 🟢 ArgonShop (WordPress 7) | 🔴 Classic Heavy CMS (Bitrix) |
| :--- | :--- | :--- |
| **Server RAM Usage** | **Minimal.** Only core WP classes and Vanilla JS on the frontend are initialized. | **High.** The CPU spends resources compiling hundreds of unused business modules. |
| **SQL Load** | **Low.** Data is fetched through the optimized `WP_Query`. No extra joins or layers. | **Extreme.** The infoblock architecture generates heavy queries with dozens of `JOIN`s per property. |
| **Frontend Dependencies** | **Pure JavaScript.** Only the ultra-light Swiper and GLightbox libraries are bundled. | **Overloaded.** Tons of system UI scripts and core libraries are loaded out of the box. |
| **Infrastructure** | Works great even on **cheap shared hosting** due to low TTFB. | Requires a **powerful VPS/VDS** with mandatory dedicated web environment setup. |

### 🛠 Architectural Secrets Behind the 100/100 Google PageSpeed Score

<details>
<summary>1. No Software Overhead (excess code) ⚡</summary>
The plugin performs no background computations unrelated to rendering the storefront, cart, or user account. The server processes requests instantly, delivering data directly to your theme's HTML templates without intermediate visual editors or heavy component layers.
</details>

<details>
<summary>2. Efficient WordPress Database Handling 📂</summary>
When using the standard WP database with large volumes (50k+ products), it is critical to avoid chaotic queries. ArgonShop uses clean WordPress API methods, making it easy to enable **object caching (Redis / Memcached)**. With caching enabled, repeated requests for products and categories bypass the SQL database entirely, keeping load times around **900 ms**.
</details>

<details>
<summary>3. Asynchronous Native AJAX 🔍</summary>
The "live" search, filtering, and cart update modules are written in native JavaScript and processed by isolated PHP handlers. This eliminates page reloads and reduces web server load under thousands of concurrent users.
</details>

---

## 🧩 Development Philosophy: Why Vanilla WordPress Core Is a Strength

For an independent developer or a small team, trying to "reinvent the wheel" and write a custom CMS or deeply modify the WordPress core is a path to a software dead end. **ArgonShop consciously and 100% uses the standard WordPress 7 core and its official API.**

And here is why this is the only correct approach for a commercial product:

* **🛡️ Security out of the box:** Protection of the database against SQL injections, data filtering, and user authorization security are provided by thousands of WordPress core developers worldwide. The plugin does not duplicate these functions but trusts proven standards.
* **🔄 Seamless updates:** Because the plugin does not interfere with the core and only extends it via hooks and custom post types, the site is protected from "breaking" when minor and major CMS updates are released.
* **📈 Built-in optimization:** In WordPress 7, the mechanisms of query caching, index building, and custom post handling are perfected. The plugin simply takes this ready-made speed without building extra layers.
* **🛠️ Zero Vendor Lock-in:** Any third-party WordPress developer can understand the e-commerce code in a couple of hours. They won't have to study a "custom engine" — all logic is built on standard functions such as `WP_Query`, taxonomy registration, and meta fields, familiar to every web master.

> **Summary:** The strength of ArgonShop is not in changing WordPress standards, but in their **maximally light and correct use**. The plugin takes the best from the stable WP ecosystem and cuts out everything that usually slows down e-commerce sites.

---

## 🚀 Features

**Store:**
- Product catalog with hierarchical taxonomy and characteristics
- Cookie-based cart (no DB writes until order placement)
- Three checkout forms: quick order, individuals, legal entities
- File attachments to orders (up to 3 files, up to 5 MB each, 15 MB total; checked on the server, stored privately, deleted after emails are sent)
- Wholesale discounts via price steps
- User account: profile, email/password change, order history
- Product view history
- Bestsellers and new arrivals
- Multi-region support (Moscow / Istra) — one engine, different sites
- Store currency in Settings → Store: US dollar (default), euro, Russian ruble

**AJAX and interface:**
- AJAX search with language-aware rules for English, French and Russian: stop words, plurals, French elisions, wrong keyboard layout correction (QWERTY / AZERTY / ЙЦУКЕН)
- AJAX catalog sorting
- AJAX order filter in the user account
- AJAX checkout with email sending
- Modern ES-module JavaScript (no jQuery on the frontend)
- Swiper sliders
- GLightbox lightbox

**Admin:**
- Product metaboxes: main parameters, gallery, characteristics, wholesale prices
- Order metaboxes: products, customer data, status
- Category tree in characteristics with cascading checkboxes
- AJAX product search right in the order
- Custom store settings page

---

## 🛠️ Technology Stack

| Layer | Technologies |
|---|---|
| **Server** | PHP 8.3+, WordPress 7.1+ |
| **Storage** | Postmeta, termmeta, usermeta, cookies |
| **Frontend** | Vanilla JS (ES modules), Fetch API, DOM API |
| **Styles** | CSS3, Flexbox |
| **Vendors** | Swiper 12, GLightbox 3.3 |
| **jQuery** | Only in admin (for `wp.media` and `jQuery UI Sortable`) |

---

## ⚙️ Requirements

- **WordPress:** 7.1 or newer
- **PHP:** 8.3 or newer
- **MySQL:** 5.7+ / MariaDB 10.3+
- **Optional:**
  - **ACF (Advanced Custom Fields)** — for the fields `unit_product`, `application`, `second_title`, `second_desc`, `bottom_desc`, `image_catalog`, `catalog_type`, `our_advantages`, `our_clients`
  - **Contact Form 7** — for the feedback form

The plugin and theme also work without ACF; a small number of theme blocks are demonstrated via ACF.

All main post types, taxonomies, and additional fields are created by ArgonShop independently.

---

## 📦 Installation

The plugin was developed as the primary plugin of the system. Ideally, install it first on a fresh, clean WordPress.

### Method 1. Clone into `wp-content`

In the WordPress root:

```bash
cd wp-content
git clone https://github.com/Argonstudio/ArgonShop.git argon-shop-repo

# Symbolic links (Linux / macOS)
ln -s argon-shop-repo/plugins/argon-shop plugins/argon-shop
ln -s argon-shop-repo/themes/argon-shop-theme themes/argon-shop-theme
```

On Windows — use `mklink /D` in a command prompt with administrator rights.

### Method 2. Manual installation

Copy:
- `plugins/argon-shop/` → `wp-content/plugins/argon-shop/`
- `themes/argon-shop-theme/` → `wp-content/themes/argon-shop-theme/`

Activate the plugin in admin, then activate the theme.

### Protecting private order files (nginx)

Order files are stored in a private folder that is closed on Apache
by `.htaccess` + `index.php`. Nginx does not read `.htaccess`, so on
nginx add to the server config:

```nginx
location ^~ /wp-content/uploads/argon-shop-private/ { deny all; }
```

Without this rule the files can be opened by a direct URL.

### Method 3. Installation via an AI assistant

The project is prepared for deployment using any LLM assistant (DeepSeek, Claude, ChatGPT, Cursor, Codex). A file [`AI.md`](AI.md) is placed in the repository root — a compact description of the architecture, file map, and contracts that must not be violated. An assistant that reads it will understand the project faster than a human.

Copy the prompt to your assistant:

For hosting:

```text
Help me deploy a WordPress site with the ArgonShop plugin on hosting.

Repository: https://github.com/Argonstudio/ArgonShop
Project context: AI.md in the repository root — read it first.

Hosting conditions:
- WordPress is already installed via the control panel (if not, ask me and help me install it).
- File access: SFTP / control panel file manager (SSH — ask me).
- Database is created by the panel, wp-config.php is configured (ask me again).

Task:
1. Tell me exactly where on the hosting to place the files:
   - plugin folder: wp-content/plugins/argon-shop/
   - theme folder:  wp-content/themes/argon-shop-theme/
2. List which files and folders from the repository to upload to each,
   and which to ignore (LICENSE, README, AI.md, .git, etc.).
3. Check that I don't forget about:
   - file permissions (644) and folders (755);
   - absence of extra files like .git;
   - PHP support on hosting (8.3+ minimum).
4. Help me activate the plugin and theme via admin.
5. Install the recommended plugins: ACF, Contact Form 7.
6. Open the home page, find and help fix errors if any.
7. Show how to register a test customer via the cart form
   and how to delete that user from the DB afterwards.
8. Explain hosting cache — how to flush it after installation.

If any step lacks data — ask a question, don't guess.
```

Or locally:

```text
Help me deploy the ArgonShop WordPress project locally.

Repository: https://github.com/Argonstudio/ArgonShop
Project context: AI.md in the repository root — read it first.

Task:
1. Deploy a clean WordPress (Local by Flywheel, Docker, or XAMPP — pick the easiest).
2. Clone the repository into a convenient folder.
3. Copy or symlink:
   - plugins/argon-shop → wp-content/plugins/argon-shop
   - themes/argon-shop-theme → wp-content/themes/argon-shop-theme
4. Activate the plugin and theme via WP-CLI or admin.
5. Install the recommended plugins: ACF, Contact Form 7.
6. Verify the home page opens without errors.
7. Explain how to create a test user and add a couple of products.

If any step is unclear or fails — ask me a question, don't guess.
```

Such a prompt works in a web chat, in IDE assistants, and in CLI agents.

**Why this works:**

- `AI.md` contains the contracts (which selectors not to change, where the AJAX actions are), and the assistant won't break the interface with a random edit.
- The file states that jQuery on the frontend is taboo. The assistant won't start suggesting "rewrite with jQuery, it's faster".
- There is a "Known limitations" section — the assistant knows where bugs may occur and isn't surprised.

**If the assistant works in an IDE** (Cursor, VS Code + Copilot, Windsurf) — opening the project folder is usually enough for tools that read `AGENTS.md` (Codex, Cursor); `AI.md` is not read automatically — for Claude Code, Aider and others see the table in `AGENTS.md`.

**If multi-region is required**, the demo site uses the AA-DomainMirror plugin. The plugin is not part of the repository; for AI, the note on historical compatibility with it, the functions that would have to be written for city selection in one admin (they do not exist in the code yet) and the Multisite option is in [docs/ai/AI_EXTENDING.md](docs/ai/AI_EXTENDING.md), section 11. However, regular Multisite is recommended — the AI is instructed to ask you about it first.

---

## 📁 Repository Structure

```text
ArgonShop/
├── LICENSE
├── README.md
├── CHANGELOG.md
├── AI.md, AGENTS.md, docs/ai/          Documentation for AI assistants
├── CLAUDE.md, .claude/                 Claude Code: docs import, /check and /new-ajax commands, check hook
├── POSITIONING.md                       When to choose ArgonShop and when not to
├── tests/                               static-check.php (PHP only) and
│                                        smoke-test.php (WP-CLI, test site only)
├── .gitignore
├── plugins/
│   └── argon-shop/
└── themes/
    └── argon-shop-theme/
```

---

### Plugin Argon Shop

```text
plugins/argon-shop/
│
├── argon-shop.php                      Entry point, hook registration,
│                                       module loading, JS localization
├── settingPage.php                     "Settings → Store" page
├── languages/                          argon-shop.pot, ru_RU and fr_FR .po/.mo
├── uninstall.php                       Cleanup on plugin deletion (products,
│                                       orders and users are kept)
│
├── includes/
│   ├── createPostType.php              CPTs: product, shoporder, slider
│   │                                   Taxonomies: catalog, characteristics,
│   │                                   wholesalePrice, statusorders
│   ├── siteLine.php                    Replaces the as_homepage marker
│   │                                   with the current domain
│   │                                   (for multi-region)
│   ├── install.php                     Activation, deactivation, migrations
│   ├── legacy-functions.php            Wrappers for pre-1.1.0 function names
│   │
│   ├── admin/
│   │   ├── additionalFilters.php       Order filter by status in admin
│   │   ├── fieldsProduct.php           Product metaboxes: price, weight,
│   │   │                               article, hit, newProduct, characteristics
│   │   ├── fieldsTaxonomy.php          "Belongs to categories" field for characteristics
│   │   ├── galleryMetabox.php          Product gallery metabox
│   │   │
│   │   └── order/
│   │       ├── orderMetabox.php        Order metaboxes: products, customer, status
│   │       ├── orderAjax.php           AJAX: product search, add, recalc
│   │       └── orderSave.php           Order saving on save_post
│   │
│   ├── api/
│   │   ├── as_action.php               as_update_meta, as_array_partial_merge
│   │   ├── check.php                   as_post_in_term
│   │   ├── getSetting.php              Store settings, as_show_user_fields, currency helpers
│   │   │
│   │   ├── getData/
│   │   │   ├── getData.php             as_getDataCart, as_getActualPrice, as_getCookie,
│   │   │   │                           as_getDiscount, as_getActualStepDiscont
│   │   │   ├── orders.php              as_get_user_orders, as_get_used_statuses
│   │   │   ├── catalog.php             as_get_catalog_terms, as_splitMenu,
│   │   │   │                           as_view_menu_elements
│   │   │   ├── gallery.php             as_get_product_gallery
│   │   │   │
│   │   │   ├── admin/
│   │   │   │   └── get_characteristics.php   as_get_charact
│   │   │   │
│   │   │   └── product/
│   │   │       └── productLists/
│   │   │           ├── marked.php      as_get_marked_products (hits, new)
│   │   │           ├── catalog.php     as_get_sort_products
│   │   │           └── history.php     as_get_history_products
│   │   │
│   │   └── view/
│   │       ├── product/
│   │       │   └── productsLists/
│   │       │       └── productsLists.php   as_view_products_list
│   │       └── catalog/
│   │           └── catalogPage.php     as_sort, as_catalog
│   │
│   └── interface/
│       ├── breadcrumbs.php             Breadcrumbs (Kama fork)
│       ├── pagenavi.php                Pagination (Kama fork)
│       ├── history.php                 AJAX: addCookieHistory_history
│       ├── last_viewed_posts.php       "Recently viewed" widget (ZG fork)
│       │
│       ├── slider/
│       │   ├── sliderMain.php          as_slider_main (Swiper)
│       │   └── sliderProduct.php       as_slider_product (Swiper + GLightbox)
│       │
│       ├── shoppingCart/
│       │   ├── shoppingCart.php        Module loader
│       │   ├── cart-ajax.php           AJAX: add/amount/delete/deleteAll
│       │   ├── checkout.php            AJAX submitCart: checkout orchestrator
│       │   ├── checkout-fields.php     Form fields, required fields
│       │   ├── checkout-files.php      Order file validation and storage
│       │   ├── checkout-registration.php  Customer registration at checkout
│       │   ├── checkout-emails.php     Order emails
│       │   └── infoCart.php            as_infocart — cart widget in header
│       │
│       ├── product/
│       │   └── cardProduct.php         as_button_card_product
│       │
│       ├── catalog/
│       │   └── sort.php                AJAX catalog sorting
│       │
│       ├── cabinet/
│       │   ├── myCabinet.php           AJAX: email/password change, account save
│       │   ├── redirectUser.php        Redirects after login/logout
│       │   └── userOrders/
│       │       ├── ordersView.php      as_view_user_orders
│       │       └── ordersAjax.php      AJAX order filter
│       │
│       └── search/
│           ├── searchForm.php          as_search — search form output
│           ├── searchGetFilters.php    GET search filters (/ ?s=)
│           ├── searchAjax.php          AJAX search + as_fixKeyboardlayout
│           └── searchLanguage.php      Search rules for ru / en / fr
│
└── assets/
    │
    ├── css/
    │   ├── interface/
    │   │   └── interface-style.css     Only initial states
    │   │                               of JS-controlled elements
    │   └── admin/
    │       ├── fields-catalog.css      Characteristic fields
    │       ├── gallery.css             Gallery metabox
    │       └── order.css               Order page
    │
    ├── js/
    │   ├── admin/
    │   │   └── modules/
    │   │       ├── admin-script.js     Category tree, cascading checkboxes
    │   │       ├── gallery.js          Gallery metabox (wp.media + Sortable)
    │   │       └── orders.js           Order editing, recalculation
    │   │
    │   └── interface/
    │       ├── main.js                 Entry point, imports all modules
    │       │
    │       ├── core/
    │       │   ├── config.js           url, nonce, cartUrl, i18n strings + t() from wp_localize_script
    │       │   ├── api.js              Single fetch client
    │       │   └── nonce.js            XMLHttpRequest interceptor
    │       │
    │       ├── shared/
    │       │   ├── dom.js              qs, qsa, on, delegate
    │       │   └── format.js           formatPrice, formatWeight, parseNumber
    │       │
    │       └── modules/
    │           ├── cart.js             Cart: amount, remove, order
    │           ├── cart-form.js        Form: registration, files
    │           ├── cart-widget.js      Cart widget in header
    │           ├── product.js          Product page
    │           ├── card-product.js     Card buttons
    │           ├── add-to-cart.js      Shared add logic
    │           ├── search.js           AJAX search
    │           ├── sort.js             Catalog sorting
    │           ├── cabinet.js          Account: email, password, profile
    │           ├── orders.js           Account: order filter
    │           ├── consent.js          Consent checkboxes
    │           ├── control-panels.js   Tab switchers
    │           ├── mobile-menu.js      Mobile menu
    │           ├── menu-catalog.js     Catalog menu in sidebar
    │           ├── header.js           City selector
    │           ├── contact-form.js     CF7 in modal
    │           ├── catalog-page.js     Mobile catalog blocks
    │           ├── request.js          "Popular queries" block
    │           ├── slider-main.js      Homepage slider (Swiper)
    │           ├── slider-product.js   Product slider (Swiper + GLightbox)
    │           └── history.js          View history
    │
    └── vendor/
        ├── swiper/                     Swiper 12 (bundle)
        └── glightbox/                  GLightbox 3.3 (bundle)
```

---

### Theme Argon Shop Theme

```text
themes/argon-shop-theme/
│
├── style.css                           Theme metadata (header only)
├── functions.php                       Theme setup, script enqueueing,
│                                       ourclients/sertificates CPTs registration,
│                                       nested catalog category redirect
│
├── header.php                          Header: menu, search, cart, contacts
├── footer.php                          Footer: two menus, address, copyright
├── sidebar.php                         Sidebar: catalog, tags, hits, new
│
├── index.php                           Homepage: slider, advantages, catalog
├── page.php                            Static page template (fallback)
├── 404.php                             404 page
├── search.php                          Search results
├── archive.php                         Category archive (news, articles)
├── tag.php                             Tag archive
├── taxonomy-catalog.php                Catalog category
│
├── single.php                          Dispatcher: picks template by category
├── single-default.php                  Default post
├── single-product.php                  Product page
├── single-aktsii-moskva.php            Promo (Moscow)
│
├── category-aktsii-moskva.php          Promo category (Moscow)
├── (category-aktsii-istra.php)         Promo category (Istra) — not created yet, see AI_EXTENDING.md §11
├── categoryArchive-aktsii-moskva.php   Promo archive (Moscow)
├── (categoryArchive-aktsii-istra.php)  Promo archive (Istra) — not created yet, see AI_EXTENDING.md §11
│
├── shoppingCartPage.php                Template Name: shoppingCart
├── myCabinetPage.php                   Template Name: CabinetPage
├── history.php                         Template Name: View history
├── aboutCompany.php                    Template Name: About company
├── contacts.php                        Template Name: Contacts
├── addressMap.php                      Template Name: AJAX Map
│
│
├── template-parts/
│   ├── cabinet/
│   │   ├── account.php                 Account & address (individual/legal)
│   │   ├── setting.php                 Email and password
│   │   └── ordering.php                Order list
│   ├── cart/
│   │   ├── quickOrder.php              Quick order
│   │   ├── person.php                  Individuals
│   │   └── legalPerson.php             Legal entities
│   └── productPage/
│       └── characteristics.php         Product characteristics table
│
├── includes/
│   └── cardProduct/
│       └── cardProduct.php             generate_product_card
│
└── assets/
    ├── js/
    │   ├── modal.js                    Theme modal (AJAX/inline/iframe)
    │   ├── search-toggle.js            Search expansion in header
    │   └── slider-sertificates.js      Certificate slider (Swiper + GLightbox)
    │
    ├── css/
    │   ├── main.css                    Base styles
    │   ├── form.css                    Form fields
    │   ├── controlPanels.css           Tab switchers
    │   ├── modal.css                   Modal
    │   ├── footer.css                  Footer
    │   ├── topBlock/                   Fixed top bar
    │   │   ├── topBlock.css
    │   │   ├── shoppingCart.css
    │   │   ├── siteMenu/siteMenu.css
    │   │   └── search/topSearch.css
    │   ├── header/                     Header
    │   │   ├── header.css
    │   │   └── contactForm/contactForm.css
    │   ├── sitebar/                    Sidebar
    │   │   ├── sitebar.css
    │   │   ├── menuCatalog/menuCatalog.css
    │   │   ├── markedProducts/markedProducts.css
    │   │   └── entryTags/entryTags.css
    │   ├── homePage/                   Homepage
    │   │   ├── homePage.css
    │   │   ├── slider/homePageSlider.css
    │   │   ├── advantages.css
    │   │   └── catalog.css
    │   ├── product/                    Product
    │   │   ├── cardProduct/cardProduct.css
    │   │   └── singleProduct/
    │   │       ├── product.css
    │   │       ├── singleProductSlider/singleProductSlider.css
    │   │       └── aboutProduct/
    │   │           ├── aboutProducts.css
    │   │           └── characteristics/characteristics.css
    │   ├── catalog/                    Catalog
    │   │   └── catalogPage/
    │   │       ├── catalogPage.css
    │   │       ├── request/request.css
    │   │       ├── sort/sort.css
    │   │       └── productsList/productsList.css
    │   ├── cart/                       Cart
    │   │   ├── cart.css
    │   │   ├── controlPanel.css
    │   │   ├── cartListProducts.css
    │   │   ├── totalValue.css
    │   │   └── forms/forms.css
    │   ├── cabinet/                    User account
    │   │   ├── cabinet.css
    │   │   ├── controlPanel.css
    │   │   ├── account/account.css
    │   │   ├── setting/setting.css
    │   │   └── orders/orders.css
    │   ├── rubrics/                    Rubrics, articles, promos
    │   │   ├── rubrics.css
    │   │   └── categoryRubrics/categoryRubrics.css
    │   ├── search/                     Search form
    │   │   └── searchForm.css
    │   ├── aboutCompany.css
    │   └── glightbox.css               GLightbox overrides for the theme
    │
    └── img/
        └── (icons, sprites, logos)
```

---

## 🔄 How It Works

### Plugin and Theme Separation

- **Plugin** — all logic: post types, taxonomies, AJAX handlers, ES modules, queries, cookies, emails.
- **Theme** — layout and styles: page templates, CSS, menu configuration.
- **Touchpoints** — plugin functions called from templates (`as_kama_breadcrumbs`, `as_search`, `as_infocart`, `as_view_products_list`, `as_slider_product`, etc.), and ES modules from the plugin loaded as `main.js`.

### Store Data

The cart and view history are stored in **cookies**, not in the DB. An order is created in the DB (CPT `shoporder`) only at the moment of checkout, which reduces MySQL load.

Only product IDs and quantities are taken from the cookie: prices are always read from the DB, and unpublished or deleted products are dropped from the cart. Required checkout fields are checked on the server.

### Extending

The plugin provides hooks for integrations without editing its code: `argon_shop_product_price`, `argon_shop_cart`, `argon_shop_required_fields`, `argon_shop_shipping_methods`, `argon_shop_payment_methods`, `argon_shop_checkout_errors`, `argon_shop_order_post_data`, `argon_shop_order_created`, `argon_shop_checkout_response`, `argon_shop_email`, `argon_shop_allowed_file_types`, `argon_shop_file_limits`, `argon_shop_customer_registered`. Examples are in [docs/ai/AI_EXTENDING.md](docs/ai/AI_EXTENDING.md).

Since 1.1.0 all plugin functions have the `as_` prefix. Old names from the public API keep working via `includes/legacy-functions.php`; the full list is in [docs/ai/AI_FUNCTION_NAMES.md](docs/ai/AI_FUNCTION_NAMES.md).

### Modern JavaScript

The frontend is fully based on ES modules: `main.js` is loaded as `<script type="module">`, imports the core (`core/`), shared utilities (`shared/`), and functional modules (`modules/`). No jQuery on the frontend — only native `fetch` and DOM API. jQuery remains only in admin for `wp.media` and `jQuery UI Sortable`.

### Multi-Region Support

The `as_homepage` marker in menu items is replaced with the current site's domain via the `wp_nav_menu_objects` filter. The menu works across all cities without editing links.

---

## 🌐 Languages

The plugin and the theme are translation-ready by WordPress rules: English is the source language, **Russian** and **French** translations are included (`languages/` folders of the plugin and the theme). The language follows **Settings → General → Site Language**; any other language shows English. JavaScript messages (cart, file upload, account) are translated too. Search adapts to the site language (English, French or Russian rules). Code comments and developer documentation are in English. Site owners can override any string with their own files in `wp-content/languages/plugins/` and `wp-content/languages/themes/` or with Loco Translate / Poedit.

**Currency** is set separately in **Settings → Store → Currency** (USD by default, EUR, RUB): it changes the symbol and its position in prices, emails and the admin, without converting amounts. A Russian-language store should select RUB.

Store data entered in the admin (products, form field names in "Settings → Store") is not translated by these files — use a multilingual plugin (Polylang, WPML) for that.

For developers and AI assistants: [docs/ai/AI_I18N.md](docs/ai/AI_I18N.md).

---

## 🧪 Tests

`tests/static-check.php` needs only PHP (no WordPress, no database) and runs in seconds: syntax, untranslated texts, text domains, translation completeness, versions and English-only code comments.

```bash
php tests/static-check.php
```

`tests/smoke-test.php` checks the main scenarios on a real WordPress site: cart, price spoofing, required fields, file uploads, order creation, registration, account, search (including language rules), currency, translated JS strings, site and admin pages, and `debug.log`. Run it **only on a test site** via WP-CLI:

```bash
wp eval-file /path/to/ArgonShop/tests/smoke-test.php
```

Details — [tests/README.md](tests/README.md).

---

## 🤖 Recommended AI for Customizing the Plugin

ArgonShop is designed to be adapted to your project with the help of AI assistants. If you have installed the plugin and want to customize it for your store, here are two proven approaches.

### Option 1. Claude Opus 5.5 — "lazy" development with a minimum of errors

The recommended choice is **Claude Opus 5.5**, preferably via [Notion.com](http://Notion.com). Upload the plugin archive or give it a link to the repository, describe the task in plain words — and the AI will study the code and the AI documentation (`AI.md` and `docs/ai/`), make the changes itself and return ready-made files. This is the fastest and most comfortable path with the fewest errors: you mostly formulate tasks and check the result.

### Option 2. Free AI Assistants (DeepSeek, ChatGPT, Gemini) — more manual work

If the budget is limited, you can successfully leverage the potential of leading free models, such as DeepSeek, ChatGPT, or Google Gemini. These are fully capable intelligent assistants; however, working with them will require more hands-on involvement from your side:

1. Provide the model with a link to the repository or upload the target files alongside your AI documentation (AI.md and docs/ai/).
2. Formulate the task in the form of highly precise and detailed instructions.
3. Carefully review the code proposed by the AI: while free versions possess immense potential, they can sometimes overlook the deeper architectural context.
4. Manually transfer the generated code into your project.
5. Be sure to conduct thorough manual testing of every change to ensure that the code integrates correctly into your system.
   
This approach requires time and strict oversight on your part, but the capabilities of modern free models allow you to significantly optimize development at no additional cost.

---

## 📚 Third-Party Libraries

The project uses third-party libraries. Their licenses are preserved:

| Library | Version | License | Location |
|---|---|---|---|
| Swiper | 12.2.0 | MIT | `plugins/argon-shop/assets/vendor/swiper/` |
| GLightbox | 3.3.1 | MIT | `plugins/argon-shop/assets/vendor/glightbox/` |
| Kama Breadcrumbs | 3.3.2 | GPL | `plugins/argon-shop/includes/interface/breadcrumbs.php` (fork) |
| Kama Pagenavi | 2.5.2 | GPL | `plugins/argon-shop/includes/interface/pagenavi.php` (fork) |
| WP-LastViewedPosts | 0.7.3 | GPL | `plugins/argon-shop/includes/interface/last_viewed_posts.php` (fork) |

---

## 📄 License

The project is distributed under the **GNU General Public License v3.0 or later**.

See the [LICENSE](LICENSE) file for the full text.

```text
Copyright (C) 2018-2026 Ivan Voitkov (voit.ne@gmail.com)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.
```



