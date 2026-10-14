# AI_TEMPLATE_MAP.md — Theme templates ↔ plugin functions

> Continuation of [AI.md](../../AI.md). Map: which template calls what.
> Plugin functions use the `as_*` prefix (since 1.1.0; the old `get_*` / `view_*` names exist only as wrappers, see `AI_FUNCTION_NAMES.md`); theme functions are mostly `astheme_*` (exceptions are listed in section 6).
>
> ⚠️ Templates marked **"not created yet"** do not exist in the repository. They are needed only for the multi-region (cities) feature — see `AI_EXTENDING.md`, section 11.

---

## 1. Global templates

### `header.php`

| Calls | Purpose |
|--------------|-----------|
| `wp_head()` | Core and plugin scripts/styles |
| `wp_nav_menu()` (`top_menu`) | Top menu |
| `as_infocart()` | Cart widget (item count + total in the store currency) |
| `as_search($searchParameters)` | Search form (AJAX) |
| `do_shortcode('[contact-form-7 id="7"]')` | Feedback form in a modal |
| `#feedback` (markup) | CF7 modal |
| `.sitySelect` | City select (only the current city is active; the second city option is commented out — multi-region, `AI_EXTENDING.md` §11) |

**DOM used by the theme JS:**

```
.block-topSearch
.topSearchSubmit   (from search-toggle.js)
```

### `footer.php`

| Calls | Purpose |
|--------------|-----------|
| `wp_nav_menu()` (`bottom_menu`, `bottom_menu2`) | Footer menus |
| `wp_footer()` | Footer scripts |

### `sidebar.php`

| Calls | Purpose |
|--------------|-----------|
| `as_get_catalog_terms()` | Catalog tree |
| `as_splitMenu()` | Split into columns |
| `as_view_menu_elements()` | Catalog menu output |
| `as_getCookie('productsShoppingCart')` | For product cards |
| `as_get_history_products(2)` | "You viewed" |
| `as_get_marked_products(2, '_hit')` | "Best sellers" |
| `as_get_marked_products(2, '_newProduct')` | "New arrivals" |
| `as_view_products_list()` | Card output |
| `as_post_in_term([37, 1], 'category')` | For the tag cloud |
| `wp_tag_cloud()` | Tag cloud |

---

## 2. Pages (page templates)

| Template | Template Name | Calls |
|--------|---------------|----------|
| `page.php` | — | `as_kama_breadcrumbs()` |
| `aboutCompany.php` | About company | `as_kama_breadcrumbs()`, `get_field('our_advantages')`, `WP_Query(ourclients)`, `get_field('our_clients')`, `WP_Query(sertificates)` |
| `contacts.php` | Contacts | `as_kama_breadcrumbs()`, `get_template_part('addressMap')`, CF7 |
| `history.php` | View history | `as_kama_breadcrumbs()`, `as_getCookie()`, `as_get_history_products()`, `generate_product_card()`, `as_kama_pagenavi()` |
| `myCabinetPage.php` | Account page | `as_kama_breadcrumbs()`, `get_template_part('template-parts/cabinet/*')` |
| `shoppingCartPage.php` | Cart page | `as_kama_breadcrumbs()`, `as_getCookie()`, `as_getActualPrice()`, `as_getClassActiveItem()`, `astheme_price_html()`, `get_template_part('template-parts/cart/*')` |
| `addressMap.php` | Map (AJAX) | `iframe` Google Maps |
| `404.php` | — | `as_kama_breadcrumbs()` |

---

## 3. Single entries (single)

| Template | Outputs |
|--------|-------------|
| `single.php` | **Dispatcher:** `in_category(36)` → `single-aktsii-moskva`, `in_category(35)` → `single-aktsii-istra` (**template not created yet**; the call is also written incorrectly as `get_template_part( 'single-aktsii-istra.php' )` — the `.php` must be removed when the template is created), otherwise → `single-default` |
| `single-default.php` | `as_kama_breadcrumbs()`, standard loop |
| `single-product.php` | `as_kama_breadcrumbs()`, `as_getCookie()`, `as_check_saleSteps()`, `as_getActualStepDiscont()`, `as_getActualPrice()`, `as_slider_product`, `astheme_currency_symbol()` / `astheme_currency_position()` (`span.currencySymbol`), `get_template_part('template-parts/productPage/characteristics')` |
| `single-aktsii-moskva.php` | Manual breadcrumbs, `get_post_meta('promo_active')` |
| `single-aktsii-istra.php` | ⚠️ **Not created yet.** Create it (as a copy of `single-aktsii-moskva.php` adapted to the second city) **only when the task is to enable city selection** — follow `AI_EXTENDING.md`, section 11, and first ask the user whether they would rather use WordPress Multisite |

---

## 4. Archives

| Template | Outputs |
|--------|-------------|
| `archive.php` | `as_kama_breadcrumbs()`, `get_terms(category)`, `wp_tag_cloud` (when `ID=37`), `as_kama_pagenavi()` |
| `tag.php` | Manual breadcrumbs, `as_kama_pagenavi()` |
| `taxonomy-catalog.php` | `as_kama_breadcrumbs()`, `get_field` (`second_title`, `image_catalog`, `catalog_type`, `second_desc`, `bottom_desc`), `get_terms(catalog)`, `as_getCookie('AS_CatalogSorting')`, `as_sort()`, `as_get_sort_products()`, `as_view_products_list()`, `as_kama_pagenavi()` |
| `category-aktsii-moskva.php` | `as_kama_breadcrumbs()`, `WP_Query` with `promo_active=1`, `as_kama_pagenavi()` |
| `category-aktsii-istra.php` | ⚠️ **Not created yet** — second-city promotions category (category ID 35). Create it only for the city-selection task (`AI_EXTENDING.md`, section 11) |
| `categoryArchive-aktsii-moskva.php` | Page template "Promotions archive (Moscow)": manual breadcrumbs, `WP_Query` with `promo_active=0` |
| `categoryArchive-aktsii-istra.php` | ⚠️ **Not created yet** — second-city promotions archive. Create it only for the city-selection task (`AI_EXTENDING.md`, section 11) |
| `search.php` | `as_kama_breadcrumbs()`, `as_search()`, `as_getCookie()`, `generate_product_card()`, `as_kama_pagenavi()` |

---

## 5. Template parts

### `template-parts/cabinet/`

| File | Outputs |
|------|-------------|
| `account.php` | `as_show_user_fields('person')`, legal-entity checkbox, `as_show_user_fields('legalPerson')`, button `#accountSaveButton` |
| `setting.php` | `#editEmail`, `#editOldPass`, `#editNewPass`, `#editNewPassConfirm`, `#editEmailPasswordButton`, link `wp_logout_url()` |
| `ordering.php` | `as_view_user_orders($user_ID)` |

### `template-parts/cart/`

| File | Outputs |
|------|-------------|
| `quickOrder.php` | `as_show_user_fields('quick')`, `messageUser`, submit |
| `person.php` | `as_show_user_fields('person')`, `emailUser`, `messageUser`, `fileCart[]`, `cartReg`, `shipping`, `payment`, submit |
| `legalPerson.php` | `as_show_user_fields('person')`, `emailUser`, `as_show_user_fields('legalPerson')`, `messageUser`, `fileCart[]`, `cartReg`, `shipping`, `payment`, submit |

### `template-parts/productPage/`

| File | Outputs |
|------|-------------|
| `characteristics.php` | `_weight`, `_characteristics_text_field`, `_characteristics_checkbox_field`, `get_term_by('id', …, 'characteristics')`. The term description is read into `$productDescr` but **not output** (the old Fancybox popup was removed; Fancybox is no longer used — modals are `modal.js`, galleries are GLightbox) |

---

## 6. Theme functions (not plugin)

### PHP — `functions.php`

| Function | Does |
|---------|-----------|
| `astheme_load_textdomain` | Loads the `argon-shop-theme` text domain |
| `astheme_after_setup` | Menus, `add_theme_support`, `add_image_size` |
| `astheme_scripts` | `wp_enqueue_style`, `wp_enqueue_script` |
| `astheme_create_post_type` | CPT `ourclients`, `sertificates` |
| `true_catalog_redirect` | Redirect of nested catalog categories |
| `astheme_excerpt_length`, `astheme_excerpt_more` | Excerpt settings |
| `post_is_in_descendant_category` | Tag helper |
| `astheme_top_menu`, `astheme_category_menu` | Menu output helpers |
| `disable_wp_emojis_in_tinymce` | Removes the emoji plugin from TinyMCE |
| `astheme_price_html`, `astheme_currency_symbol`, `astheme_currency_position` | Currency wrappers around the plugin helpers |

`generate_product_card()` — product card output, `includes/cardProduct/cardProduct.php` of the theme.

### Theme JS (not plugin)

| File | Does |
|------|-----------|
| `assets/js/modal.js` | Lightweight modal (AJAX / inline / iframe), strings from `arsModalI18n` |
| `assets/js/search-toggle.js` | Header search expand |
| `assets/js/slider-sertificates.js` | Certificate slider (Swiper + GLightbox) |

> **Note:** in the historic branch `historic/php56-jquery` the theme function
> prefix is `bito_*`, not `astheme_*`. In the current version new theme functions use
> `astheme_*` (older unprefixed ones: `true_catalog_redirect`, `post_is_in_descendant_category`,
> `disable_wp_emojis_in_tinymce`, `generate_product_card`), JS objects use `ars*`,
> plugin functions use `as_*` (since 1.1.0, see `AI_FUNCTION_NAMES.md`).

---

## 7. Rules for editing templates

1. **Don't change plugin function calls.** If `as_infocart()` is
   called — keep it. If it is wrapped in `if ( function_exists(...) )` —
   keep the wrapper too.

2. **Don't add `get_search_form()`** — the search form is rendered by
   `as_search()`.

3. **Don't call plugin functions without `function_exists`** if the
   template may be used without the plugin (it can't today, but keep the margin).

4. **Don't move `<script>` into templates** — JS is loaded via
   `wp_enqueue_script` in `functions.php` or `argon-shop.php`.

5. **No page template for orders (`single-shoporder.php`) is needed** —
   `publicly_queryable => false`.

6. **Prices and currency** — only via `astheme_price_html()` /
   `astheme_currency_symbol()`; never hardcode `₽`, `$`, `€` or `руб.`.

7. **Text** — in English inside `__()` / `esc_html_e()` with the
   `argon-shop-theme` domain; comments in English.

---

