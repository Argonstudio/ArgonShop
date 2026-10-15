# AI_TEMPLATE_MAP.md — Шаблоны темы ↔ функции плагина

> Продолжение [AI.md](../../AI.md). Карта: какой шаблон что вызывает.
> Функции плагина имеют префикс `as_*` (с 1.1.0; старые имена `get_*` / `view_*` остались только как обёртки, см. `AI_FUNCTION_NAMES.md`); функции темы — в основном `astheme_*` (исключения перечислены в разделе 6).

---

## 1. Глобальные шаблоны

### `header.php`

| Вызывает | Назначение |
|--------------|-----------|
| `wp_head()` | Скрипты/стили ядра и плагина |
| `wp_nav_menu()` (`top_menu`) | Верхнее меню |
| `as_infocart()` | Виджет корзины (количество товаров + сумма в валюте магазина) |
| `as_search($searchParameters)` | Форма поиска (AJAX) |
| `do_shortcode('[contact-form-7 id="7"]')` | Форма обратной связи в модальном окне |
| `#feedback` (разметка) | Модальное окно CF7 |
| `.sitySelect` | Выбор города (активен только текущий город; опция второго города закомментирована — мультирегиональность, `AI_EXTENDING.md` §13) |

**DOM, используемый JS темы:**

```
.block-topSearch
.topSearchSubmit   (из search-toggle.js)
```

### `footer.php`

| Вызывает | Назначение |
|--------------|-----------|
| `wp_nav_menu()` (`bottom_menu`, `bottom_menu2`) | Меню в подвале |
| `wp_footer()` | Скрипты подвала |

### `sidebar.php`

| Вызывает | Назначение |
|--------------|-----------|
| `as_get_catalog_terms()` | Дерево каталога |
| `as_splitMenu()` | Разбиение на колонки |
| `as_view_menu_elements()` | Вывод меню каталога |
| `as_getCookie('productsShoppingCart')` | Для карточек товаров |
| `as_get_history_products(2)` | «Вы смотрели» |
| `as_get_marked_products(2, '_hit')` | «Хиты продаж» |
| `as_get_marked_products(2, '_newProduct')` | «Новинки» |
| `as_view_products_list()` | Вывод карточек |
| `as_post_in_term([37, 1], 'category')` | Для облака меток |
| `wp_tag_cloud()` | Облако меток |

---

## 2. Страницы (шаблоны страниц)

| Шаблон | Template Name | Вызывает |
|--------|---------------|----------|
| `page.php` | — | `as_kama_breadcrumbs()` |
| `aboutCompany.php` | О компании | `as_kama_breadcrumbs()`, `get_field('our_advantages')`, `WP_Query(ourclients)`, `get_field('our_clients')`, `WP_Query(sertificates)` |
| `contacts.php` | Контакты | `as_kama_breadcrumbs()`, `get_template_part('addressMap')`, CF7 |
| `history.php` | История просмотров | `as_kama_breadcrumbs()`, `as_getCookie()`, `as_get_history_products()`, `generate_product_card()`, `as_kama_pagenavi()` |
| `myCabinetPage.php` | Страница кабинета | `as_kama_breadcrumbs()`, `get_template_part('template-parts/cabinet/*')` |
| `shoppingCartPage.php` | Страница корзины | `as_kama_breadcrumbs()`, `as_getCookie()`, `as_getActualPrice()`, `as_getClassActiveItem()`, `astheme_price_html()`, `get_template_part('template-parts/cart/*')` |
| `addressMap.php` | — (подключается через `get_template_part('addressMap')`) | `iframe` Google Maps |
| `404.php` | — | `as_kama_breadcrumbs()` |

---

## 3. Отдельные записи (single)

| Шаблон | Выводит |
|--------|-------------|
| `single.php` | **Диспетчер:** `in_category(36)` → `single-aktsii-moskva`, `in_category(35)` → `single-aktsii-istra` (**файла нет в репозитории**, см. примечание ниже), иначе → `single-default` |
| `single-default.php` | `as_kama_breadcrumbs()`, стандартный цикл |
| `single-product.php` | `as_kama_breadcrumbs()`, `as_getCookie()`, `as_check_saleSteps()`, `as_getActualStepDiscont()`, `as_getActualPrice()`, `as_slider_product`, `astheme_currency_symbol()` / `astheme_currency_position()` (`span.currencySymbol`), `get_template_part('template-parts/productPage/characteristics')` |
| `single-aktsii-moskva.php` | Хлебные крошки вручную, `get_post_meta('promo_active')` |
| `single-aktsii-istra.php` | **Нет в репозитории — создаёт ИИ по задаче «выбор города»** (раздел 13 `AI_EXTENDING.md`). Образец: `single-aktsii-moskva.php`; прежний вариант использовал `as_kama_breadcrumbs()` с разделителем `<b> / </b>` |

---

## 4. Архивы

| Шаблон | Выводит |
|--------|-------------|
| `archive.php` | `as_kama_breadcrumbs()`, `get_terms(category)`, `wp_tag_cloud` (при `ID=37`), `as_kama_pagenavi()` |
| `tag.php` | Хлебные крошки вручную, `as_kama_pagenavi()` |
| `taxonomy-catalog.php` | `as_kama_breadcrumbs()`, `get_field` (`second_title`, `image_catalog`, `catalog_type`, `second_desc`, `bottom_desc`), `get_terms(catalog)`, `as_getCookie('AS_CatalogSorting')`, `as_sort()`, `as_get_sort_products()`, `as_view_products_list()`, `as_kama_pagenavi()` |
| `category-aktsii-moskva.php` | `as_kama_breadcrumbs()`, `WP_Query` с `promo_active=1`, `as_kama_pagenavi()` |
| `category-aktsii-istra.php` | **Нет в репозитории — создаёт ИИ по задаче «выбор города».** Прежний вариант — заглушка `include(404.php)` (рубрика Истры открывалась на другом сайте) |
| `categoryArchive-aktsii-moskva.php` | Шаблон страницы «Архив акций (Москва)»: хлебные крошки вручную, `WP_Query` с `promo_active=0` |
| `categoryArchive-aktsii-istra.php` | **Нет в репозитории. Этот шаблон должен создать ИИ, если задача — сделать выбор городов** (раздел 13 `AI_EXTENDING.md`). Образец — `categoryArchive-aktsii-moskva.php`: хлебные крошки вручную, `WP_Query` с `promo_active=0` для категории города. Перед созданием **спросите пользователя, не хочет ли он использовать WP Multisite** — при Multisite отдельные городские шаблоны, как правило, не нужны |
| `search.php` | `as_kama_breadcrumbs()`, `as_search()`, `as_getCookie()`, `generate_product_card()`, `as_kama_pagenavi()` |

> **Шаблоны городов (Истра).** Файлов `single-aktsii-istra.php`, `category-aktsii-istra.php` и `categoryArchive-aktsii-istra.php` в репозитории нет, хотя на них ссылаются `README`, `single.php` и этот файл. Не считайте их ошибкой удаления и не «восстанавливайте» по памяти: создавайте только по задаче «сделать выбор городов» — по правилам из раздела 13 `AI_EXTENDING.md`.
>
> **Баг в `single.php`:** ветка `in_category(35)` вызывает `get_template_part( 'single-aktsii-istra.php' )` — **с расширением `.php`**. WordPress добавляет `.php` сам и ищет `single-aktsii-istra.php.php`, поэтому шаблон не найдётся. При создании шаблона города исправьте вызов на `get_template_part( 'single-aktsii-istra' )`.

---

## 5. Части шаблонов

### `template-parts/cabinet/`

| Файл | Выводит |
|------|-------------|
| `account.php` | `as_show_user_fields('person')`, чекбокс юрлица, `as_show_user_fields('legalPerson')`, кнопка `#accountSaveButton` |
| `setting.php` | `#editEmail`, `#editOldPass`, `#editNewPass`, `#editNewPassConfirm`, `#editEmailPasswordButton`, ссылка `wp_logout_url()` |
| `ordering.php` | `as_view_user_orders($user_ID)` |

### `template-parts/cart/`

| Файл | Выводит |
|------|-------------|
| `quickOrder.php` | `as_show_user_fields('quick')`, `messageUser`, кнопка отправки |
| `person.php` | `as_show_user_fields('person')`, `emailUser`, `messageUser`, `fileCart[]`, `cartReg`, `shipping`, `payment`, кнопка отправки |
| `legalPerson.php` | `as_show_user_fields('person')`, `emailUser`, `as_show_user_fields('legalPerson')`, `messageUser`, `fileCart[]`, `cartReg`, `shipping`, `payment`, кнопка отправки |

### `template-parts/productPage/`

| Файл | Выводит |
|------|-------------|
| `characteristics.php` | `_weight`, `_characteristics_text_field`, `_characteristics_checkbox_field`, `get_term_by('id', …, 'characteristics')`. Описание термина читается в `$productDescr`, но **не выводится** (старое всплывающее окно Fancybox удалено; Fancybox больше не используется — модальные окна через `modal.js`, галереи через GLightbox) |

---

## 6. Функции темы (не плагина)

### PHP — `functions.php`

| Функция | Что делает |
|---------|-----------|
| `astheme_load_textdomain` | Загружает text domain `argon-shop-theme` |
| `astheme_after_setup` | Меню, `add_theme_support`, `add_image_size` |
| `astheme_scripts` | `wp_enqueue_style`, `wp_enqueue_script` |
| `astheme_create_post_type` | CPT `ourclients`, `sertificates` |
| `true_catalog_redirect` | Редирект вложенных категорий каталога |
| `astheme_excerpt_length`, `astheme_excerpt_more` | Настройки анонса |
| `post_is_in_descendant_category` | Хелпер для меток |
| `astheme_top_menu`, `astheme_category_menu` | Хелперы вывода меню |
| `disable_wp_emojis_in_tinymce` | Убирает плагин эмодзи из TinyMCE |
| `astheme_price_html`, `astheme_currency_symbol`, `astheme_currency_position` | Обёртки валюты над функциями плагина |

`generate_product_card()` — вывод карточки товара, `includes/cardProduct/cardProduct.php` темы.

### JS темы (не плагина)

| Файл | Что делает |
|------|-----------|
| `assets/js/modal.js` | Лёгкое модальное окно (AJAX / inline / iframe), строки из `arsModalI18n` |
| `assets/js/search-toggle.js` | Раскрытие поиска в шапке |
| `assets/js/slider-sertificates.js` | Слайдер сертификатов (Swiper + GLightbox) |

> **Примечание:** в исторической ветке `historic/php56-jquery` префикс функций темы —
> `bito_*`, а не `astheme_*`. В текущей версии новые функции темы — `astheme_*`
> (старые без префикса: `true_catalog_redirect`, `post_is_in_descendant_category`,
> `disable_wp_emojis_in_tinymce`, `generate_product_card`), JS-объекты — `ars*`,
> функции плагина — `as_*` (с 1.1.0, см. `AI_FUNCTION_NAMES.md`).

---

## 7. Правила редактирования шаблонов

1. **Не меняйте вызовы функций плагина.** Если вызывается `as_infocart()` —
   оставьте. Если вызов обёрнут в `if ( function_exists(...) )` —
   оставьте и обёртку.

2. **Не добавляйте `get_search_form()`** — форму поиска выводит
   `as_search()`.

3. **Не вызывайте функции плагина без `function_exists`**, если
   шаблон может использоваться без плагина (сейчас не может, но оставьте запас).

4. **Не переносите `<script>` в шаблоны** — JS подключается через
   `wp_enqueue_script` в `functions.php` или `argon-shop.php`.

5. **Шаблон страницы для заказов (`single-shoporder.php`) не нужен** —
   `publicly_queryable => false`.

6. **Цены и валюта** — только через `astheme_price_html()` /
   `astheme_currency_symbol()`; никогда не хардкодьте `₽`, `$`, `€` или `руб.`.

7. **Текст** — на английском внутри `__()` / `esc_html_e()` с доменом
   `argon-shop-theme`; комментарии на русском (RU-ветка).

---
