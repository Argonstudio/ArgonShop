# AI_TEMPLATE_MAP.md — Шаблоны темы ↔ функции плагина

> Продолжение [AI.md](../../AI.md). Карта: какой шаблон что вызывает.
> Функции плагина помечены как `as_*` / `get_*` / `view_*`; функции темы — `astheme_*`.

---

## 1. Глобальные шаблоны

### `header.php`

| Что вызывает | Назначение |
|--------------|-----------|
| `wp_head()` | Подключение скриптов/стилей ядра и плагинов |
| `wp_nav_menu()` (`top_menu`) | Верхнее меню |
| `as_infocart()` | Виджет корзины (число товаров + сумма) |
| `as_search($searchParameters)` | Форма поиска (AJAX) |
| `do_shortcode('[contact-form-7 id="7"]')` | Форма обратной связи в модалке |
| `#feedback` (разметка) | Модалка CF7 |
| `.sitySelect` | Селект городов |

**DOM, на который смотрит JS темы:**

```
.block-topSearch
.topSearchSubmit   (из search-toggle.js)
```

### `footer.php`

| Что вызывает | Назначение |
|--------------|-----------|
| `wp_nav_menu()` (`bottom_menu`, `bottom_menu2`) | Нижние меню |
| `wp_footer()` | Подключение скриптов в подвале |

### `sidebar.php`

| Что вызывает | Назначение |
|--------------|-----------|
| `get_catalog_terms()` | Дерево каталога |
| `splitMenu()` | Разбивка на столбцы |
| `view_menu_elements()` | Вывод меню каталога |
| `getCookie('productsShoppingCart')` | Для карточек товаров |
| `get_history_products(2)` | «Вы смотрели» |
| `get_marked_products(2, '_hit')` | «Хиты продаж» |
| `get_marked_products(2, '_newProduct')` | «Новинки» |
| `view_products_list()` | Вывод карточек |
| `post_in_term([37, 1], 'category')` | Для облака тегов |
| `wp_tag_cloud()` | Облако тегов |

---

## 2. Страницы (page templates)

| Шаблон | Template Name | Вызывает |
|--------|---------------|----------|
| `page.php` | — | `kama_breadcrumbs()` |
| `aboutCompany.php` | О компании | `kama_breadcrumbs()`, `get_field('our_advantages')`, `WP_Query(ourclients)`, `get_field('our_clients')`, `WP_Query(sertificates)` |
| `contacts.php` | Контакты | `kama_breadcrumbs()`, `get_template_part('addressMap')`, CF7 |
| `history.php` | История просмотров | `kama_breadcrumbs()`, `getCookie()`, `get_history_products()`, `generate_product_card()`, `kama_pagenavi()` |
| `myCabinetPage.php` | CabinetPage | `kama_breadcrumbs()`, `get_template_part('template-parts/cabinet/*')` |
| `shoppingCartPage.php` | shoppingCart | `kama_breadcrumbs()`, `getCookie()`, `getActualPrice()`, `getClassActiveItem()`, `get_template_part('template-parts/cart/*')` |
| `addressMap.php` | Карта AJAX | `iframe` Google Maps |
| `404.php` | — | `kama_breadcrumbs()` |

---

## 3. Одиночные записи (single)

| Шаблон | Что выводит |
|--------|-------------|
| `single.php` | **Диспетчер:** `in_category(36)` → `single-aktsii-moskva`, `in_category(35)` → `single-aktsii-istra`, иначе → `single-default` |
| `single-default.php` | `kama_breadcrumbs()`, стандартный цикл |
| `single-product.php` | `kama_breadcrumbs()`, `getCookie()`, `check_saleSteps()`, `getActualStepDiscont()`, `getActualPrice()`, `as_slider_product`, `get_template_part('template-parts/productPage/characteristics')` |
| `single-aktsii-moskva.php` | Ручные хлебные крошки, `get_post_meta('promo_active')` |
| `single-aktsii-istra.php` | `kama_breadcrumbs()` с разделителем `<b> / </b>` |

---

## 4. Архивы

| Шаблон | Что выводит |
|--------|-------------|
| `archive.php` | `kama_breadcrumbs()`, `get_terms(category)`, `wp_tag_cloud` (при `ID=37`), `kama_pagenavi()` |
| `tag.php` | Ручные крошки, `kama_pagenavi()` |
| `taxonomy-catalog.php` | `kama_breadcrumbs()`, `get_field` (`second_title`, `image_catalog`, `catalog_type`, `second_desc`, `bottom_desc`), `get_terms(catalog)`, `getCookie('AS_CatalogSorting')`, `as_sort()`, `get_sort_products()`, `view_products_list()`, `kama_pagenavi()` |
| `category-aktsii-moskva.php` | `kama_breadcrumbs()`, `WP_Query` с `promo_active=1`, `kama_pagenavi()` |
| `category-aktsii-istra.php` | **Заглушка:** `include(404.php)` |
| `categoryArchive-aktsii-moskva.php` | Ручные крошки, `WP_Query` с `promo_active=0` |
| `categoryArchive-aktsii-istra.php` | Ручные крошки, `WP_Query` с `promo_active=0` |
| `search.php` | `kama_breadcrumbs()`, `as_search()`, `getCookie()`, `generate_product_card()`, `kama_pagenavi()` |

---

## 5. Template parts

### `template-parts/cabinet/`

| Файл | Что выводит |
|------|-------------|
| `account.php` | `show_user_fields('person')`, чекбокс юрлица, `show_user_fields('legalPerson')`, кнопка `#accountSaveButton` |
| `setting.php` | `#editEmail`, `#editOldPass`, `#editNewPass`, `#editNewPassConfirm`, `#editEmailPasswordButton`, ссылка `wp_logout_url()` |
| `ordering.php` | `view_user_orders($user_ID)` |

### `template-parts/cart/`

| Файл | Что выводит |
|------|-------------|
| `quickOrder.php` | `show_user_fields('quick')`, `messageUser`, submit |
| `person.php` | `show_user_fields('person')`, `emailUser`, `messageUser`, `fileCart[]`, `cartReg`, `shipping`, `payment`, submit |
| `legalPerson.php` | `show_user_fields('person')`, `emailUser`, `show_user_fields('legalPerson')`, `messageUser`, `fileCart[]`, `cartReg`, `shipping`, `payment`, submit |

### `template-parts/productPage/`

| Файл | Что выводит |
|------|-------------|
| `characteristics.php` | `_weight`, `_characteristics_text_field`, `_characteristics_checkbox_field`, `get_term_by('characteristics')`, Fancybox-описания |

---

## 6. Функции темы (не плагина)

### PHP — `functions.php`

| Функция | Что делает |
|---------|-----------|
| `astheme_after_setup` | Меню, `add_theme_support`, `add_image_size` |
| `astheme_scripts` | `wp_enqueue_style`, `wp_enqueue_script` |
| `astheme_create_post_type` | CPT `ourclients`, `sertificates` |
| `true_catalog_redirect` | Редирект вложенных категорий каталога |
| `astheme_excerpt_length`, `astheme_excerpt_more` | Настройки excerpt |
| `post_is_in_descendant_category` | Хелпер для тегов |
| `astheme_top_menu`, `astheme_category_menu` | Хелперы для вывода меню |

### JS темы (не плагина)

| Файл | Что делает |
|------|-----------|
| `assets/js/modal.js` | Лёгкая модалка (AJAX / inline / iframe) |
| `assets/js/search-toggle.js` | Раскрытие поиска в шапке |
| `assets/js/slider-sertificates.js` | Слайдер сертификатов (Swiper + GLightbox) |

> **Примечание:** в исторической ветке `historic/php56-jquery` префикс функций
> темы — `bito_*`, а не `astheme_*`. В актуальной версии плагина префиксы
> приведены к `astheme_*` / `ars*`. См. `AI_CONTEXT.md` для деталей.

---

## 7. Правила правок в шаблонах

1. **Не менять вызовы функций плагина.** Если функция `as_infocart()`
   вызывается — оставить. Если обёрнута в `if ( function_exists(...) )` —
   обёртку тоже.

2. **Не добавлять `get_search_form()`** — форма поиска выводится через
   `as_search()`.

3. **Не вызывать функции плагина напрямую без `function_exists`,** если
   шаблон может использоваться без плагина (сейчас не может, но оставь запас).

4. **Не перемещать `<script>` в шаблонах** — JS подключается через
   `wp_enqueue_script` в `functions.php` или `argon-shop.php`.

5. **Шаблон страницы для заказа (`single-shoporder.php`) не нужен** —
   `publicly_queryable => false`.

---

