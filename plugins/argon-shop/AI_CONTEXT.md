# AI_CONTEXT.md — Плагин Argon Shop

> Подробное описание плагина `argon-shop` для ИИ-ассистентов.
> См. также корневой `AI_CONTEXT.md` и `themes/bmshop/AI_CONTEXT.md`.

## 1. Назначение

Плагин реализует **всю бизнес-логику** интернет-магазина:
- каталог товаров с иерархическими категориями и характеристиками;
- корзину на cookie с AJAX-обновлением;
- оформление заказа (быстрое / физлицо / юрлицо) с загрузкой файлов и email;
- личный кабинет с редактированием данных и просмотром заказов;
- систему оптовых скидок «шаги цен»;
- AJAX-поиск по товарам, категориям и записям;
- историю просмотров;
- админ-панель для управления товарами, заказами, статусами и характеристиками.

**Точка входа:** `argon-shop.php`. Он подключает все модули через `require_once`.

## 2. Структура

```
plugins/argon-shop/
├── argon-shop.php              # Точка входа, хуки, подключение модулей
├── settingPage.php             # Страница настроек магазина
├── assets/
│   ├── admin/                  # CSS/JS/IMG для админки
│   └── interface/              # CSS/JS/IMG для фронта (включая jQuery 2.2.2)
└── includes/
    ├── createPostType.php      # CPT и таксономии
    ├── siteLine.php            # Замена метки as_homepage в меню
    ├── admin/                  # Метабоксы, AJAX админки
    ├── api/                    # Бизнес-логика (данные, настройки, вывод)
    └── interface/              # AJAX-обработчики фронта, шаблоны
```

## 3. Точка входа `argon-shop.php`

**Что делает:**
1. Запрещает прямой доступ (`ABSPATH`).
2. Определяет `$directory = plugin_dir_path(__FILE__)`.
3. Подключает `settingPage.php`.
4. Подключает все модули через `require_once`.
5. Регистрирует хуки:
   - `admin_head` → `add_admin_styleScript()` — стили/скрипты админки.
   - `wp_enqueue_scripts` → `add_interface_styleScript()` — стили/скрипты фронта.
   - `pre_option_default_role` → принудительно `subscriber` для новых пользователей.
6. **Заменяет jQuery**: `wp_deregister_script('jquery')` и подключает локальный `jquery.js` (2.2.2).
7. Локализует `myajax.url` для JS (`admin_url('admin-ajax.php')`).
8. Условно подключает скрипты: `product.js`, `cabinet.js`, `cart.js` — в зависимости от типа страницы.

**Важно:** `sort_terms_clause()` определена здесь и используется в `fieldsProduct.php` для числовой сортировки шагов оптовых цен.

## 4. Настройки (`settingPage.php`)

**Страница:** `Настройки → Магазин` (`options-general.php?page=settings-shop`).

**Опция:** `settingShop` (массив).

**Секции и поля:**
- **Основные:** `shoppingCartPage` (ID страницы корзины), `cabinetPage` (ID страницы кабинета).
- **Скидки:** `typeSale` (`noSale` | `wholesalePriceSteps`).
- **Данные пользователя:** `quickLine`, `personLine`, `legalPersonLine` — текстовые поля с построчным описанием полей формы.

**Формат полей форм:**
```
Название поля[placeholder,размер]
Название поля2[placeholder,размер]
```
Где `размер` — `50` или `100` (ширина поля).

**Санитизация:** `sanitize_callback()` разбирает текст в массив:
```php
[
  'имя_поля' => ['name' => '...', 'placeholder' => '...', 'size' => 50]
]
```
Для placeholder поддерживается замена `/запятая/` → `,`.

## 5. Типы записей и таксономии (`createPostType.php`)

Регистрируется на хуке `init` → `as_create_post_type()`.

### CPT
| Ключ | Название | Поддержка | Особенности |
|------|----------|-----------|-------------|
| `slider` | Слайдер | title, editor, thumbnail | Иконка `dashicons-images-alt`, позиция 13 |
| `shoporder` | Заказы | title | `publicly_queryable = false`, иконка `dashicons-clipboard`, позиция 15 |
| `product` | Товары | title, editor, excerpt, thumbnail | Иконка `dashicons-clipboard`, позиция 14 |

### Таксономии
| Ключ | Для кого | Иерархия | Особенности |
|------|----------|----------|-------------|
| `statusorders` | shoporder | нет | slug `statusorders`, `meta_box_cb = false` |
| `catalog` | product | да | slug `catalog` |
| `wholesalePrice` | product | нет | регистрируется только если `typeSale == wholesalePriceSteps` |
| `characteristics` | product | да | slug `characteristics`, `meta_box_cb = false` |

## 6. API (`includes/api/`)

### 6.1. `as_action.php`
- `as_update_meta($post_id, $key, $value)` — обновляет мета, если значение непустое, иначе удаляет.
- `array_partial_merge($quantity, $summaryArray, $attachArray)` — частичное слияние массивов (для поиска).

### 6.2. `check.php`
- `post_in_term($cats, $tax, $_post = null)` — проверяет принадлежность поста к категории **или её потомкам**.

### 6.3. `getSetting.php`
- `check_saleSteps()` — включены ли шаги оптовых цен.
- `get_cartPageID()` / `get_cartPageURL()` — ID/URL страницы корзины.
- `get_cabinetPageID()` / `get_cabinetPageURL()` — ID/URL страницы кабинета.
- `show_user_fields($userID, $type, $wrap, $class50, $class100, $classTitle, $classInput, $required, $admin)` — генерирует HTML-поля формы из настроек `settingShop`. Используется в шаблонах темы (`template-parts/cart/*.php`).

### 6.4. `getData/getData.php`
- `countDigits($str)` — количество цифр в строке.
- `cheak_detailed_fields($fields)` — фильтрует пустые значения.
- `getDataCart($productsShoppingCart)` — возвращает `['productAmountCart', 'totalPrice']`.
- `getActualPriceAllProducts($productsShoppingCart)` — массив актуальных цен.
- `getActualPrice($type, $productsShoppingCart, $productID, $productPrice, $productAmount)` — актуальная цена с учётом скидок.
- `getActualStepDiscont($productID, $productsShoppingCart, $totalPriceProduct)` — текущий шаг скидки.
- `getDiscount($id)` — массив оптовых цен товара.
- `getCookie($type)` — читает cookie и JSON-декодирует.
- `getClassActiveItem($userID, $type)` — возвращает CSS-класс активной вкладки в корзине.

### 6.5. `getData/orders.php`
- `get_used_statuses($statuses_orders_user)` — статусы заказов.
- `get_user_orders($user_ID, $filters)` — возвращает `['orders', 'orders_meta', 'orders_status']`.

### 6.6. `getData/catalog.php`
- `get_sort_products($typeSort, $howSorting, $catId, $pageNum)` — `WP_Query` товаров с сортировкой (`date`, `name`, `price`).

### 6.7. `getData/product/productLists/marked.php`
- `get_marked_products($count, $type)` — товары с `_hit` или `_newProduct`.
- `get_tax_query_marked_products(...)` — формирует `tax_query` по открытой категории.
- `get_terms_ids_marked_products(...)` — ID терминов для запроса.

### 6.8. `getData/product/productLists/history.php`
- `get_history_products($count)` — товары из cookie `AS_History` (сохраняя порядок).

### 6.9. `getData/admin/get_characteristics.php`
- `get_charact($typeField, $valueField, $productID)` — значение характеристики товара (`name` или `id`).

### 6.10. `view/product/productsLists/productsLists.php`
- `view_products_list($productsList)` — обходит `WP_Query` и вызывает `generate_product_card()` (определена в теме).

### 6.11. `view/catalog/catalogPage.php`
- `as_sort($settingSort, $catID, $pageNum)` — рендер панели сортировки.
- `as_catalog($catalogSetting, $catID, $pageNum)` — рендер каталога.

## 7. Админские модули (`includes/admin/`)

### 7.1. `fieldsProduct.php`
- Метабоксы: `mainParameters`, `wholesalePrice`, `characteristics`.
- Поля: `_price`, `_weight`, `_article`, `_hit`, `_newProduct`, `wholesalePrice[term_id]`, `characteristics_text_field[term_id]`, `characteristics_checkbox_field[term_id]`.
- `save_detailed_fields()` — сохранение.
- AJAX `update_characteristics` — перерисовка полей при смене категории товара.
- `get_product_parentterms()` — родительские категории товара (включая цепочку).

### 7.2. `fieldsTaxonomy.php`
- Поля для терминов таксономии `characteristics`: `checkCategory[term_id]` — к каким категориям каталога относится характеристика.
- Функции: `edit_new_custom_fields`, `add_new_custom_fields`, `save_custom_taxonomy_meta`.

### 7.3. `additionalFilters.php`
- Фильтр заказов по статусу в списке админки.

### 7.4. `order/orderMetabox.php`
- Метабоксы: `detailOrders` (товары), `detailBuyer` (информация), `statusOrders` (статус).
- `createTableProduct()` — HTML-таблица товара в заказе.
- `as_getHTMLField()` — рендер поля формы.

### 7.5. `order/orderAjax.php`
- `as_searchSuitableProduct_order` — поиск товара для добавления в заказ.
- `as_addProduct_order` — добавить товар в заказ.
- `as_addFields_order` — добавить поля из другой формы.

### 7.6. `order/orderSave.php`
- `save_data_order()` — сохранение заказа: пересчёт, обновление заголовка, мета, статус.

## 8. Интерфейсные модули (`includes/interface/`)

### 8.1. `shoppingCart/shoppingCart.php`
**Основной AJAX-модуль корзины.** Определяет JS-функции (`getWholesalePrice`, `addProducts`, `cartAmountProducts`, `cartAmountProductsAjax`, `deleteAllProductsShoppingCart`, `deleteProducts`, `submitCart`) и PHP-обработчики.

Обработчики:
- `wholesalePrice_shoppingCart_callback` — возвращает цену с учётом скидки.
- `amountProducts_shoppingCart_callback` — меняет количество в cookie, возвращает `{weight, actualPrices, productsShoppingCart}`.
- `deleteAllProducts_shoppingCart_callback` — очищает cookie.
- `deleteProducts_shoppingCart_callback` — удаляет товар, возвращает актуальные цены.
- `addProducts_shoppingCart_callback` — добавляет товар, возвращает `{amountProduct, productAmountCart, totalPrice}`.
- `submitCart_shoppingCart_callback` — оформление заказа:
  1. Валидация полей (`get_valueFieldsCart`).
  2. Проверка совпадения корзины и формы.
  3. Обработка файлов (форматы: doc, docx, xls, xlsx, pdf, jpg, jpeg, gif, bmp, png; ≤15 МБ).
  4. Регистрация пользователя (`wp_create_user` + `wp_signon`).
  5. Создание `shoporder` (`wp_insert_post`).
  6. Запись мета: `_productsCart`, `_fieldsCart`, `_number`, `_productAmountCart`, `_totalPrice`, `_shipping`, `_payment`.
  7. Установка статуса `neworder`.
  8. Отправка двух писем (`mail()`) — админу и пользователю.
  9. `update_option('counterOrders', $counterOrders + 1)`.

**Важно:** использует `mail()`, не `wp_mail()`.

### 8.2. `shoppingCart/infoCart.php`
- `as_infocart($pageID)` — виджет корзины (число товаров и сумма).

### 8.3. `product/cardProduct.php`
- `button_card_product($productID, $cardInBascet, $cardBuy, $productsShoppingCart)` — кнопки «В корзину» / «Купить».

### 8.4. `catalog/sort.php`
- AJAX `sortingCatalog` — сортировка каталога, сохранение в cookie `AS_CatalogSorting`.

### 8.5. `cabinet/myCabinet.php`
- AJAX `cabinetEditEmailPassword` — смена email/пароля.
- AJAX `cabinetAccountSave` — сохранение `accountData` (мета пользователя).

### 8.6. `cabinet/redirectUser.php`
- `my_login_redirect` — редирект не-админов в кабинет.
- `redirect_after_logout` — редирект на главную.
- `my_function_admin_bar` — скрывает админ-бар для не-админов.
- `template_redirect` — редирект из кабинета на `/wp-login.php`.

### 8.7. `cabinet/userOrders/ordersView.php`
- `view_user_orders($user_ID, $filters)` — панель + таблица заказов.
- `create_user_ordersTable($orders)` — HTML-таблица.
- `view_user_order($order, $orders_meta, $orders_status)` — одна строка.

### 8.8. `cabinet/userOrders/ordersAjax.php`
- AJAX `ordersFiltersStatus` — фильтр заказов по статусу.

### 8.9. `breadcrumbs.php`
- Класс `Kama_Breadcrumbs` + `kama_breadcrumbs($sep, $l10n, $args)`.
- Поддерживает микроразметку `schema.org` и `rdf.data-vocabulary.org`.
- Кастомизация: `markup`, `priority_tax`, `priority_terms`, `nofollow`.

### 8.10. `pagenavi.php`
- Функция `kama_pagenavi($before, $after, $echo, $args, $wp_query)`.
- Альтернатива `wp_pagenavi`.
- Аргументы: `text_num_page`, `num_pages`, `step_link`, `back_text`, `next_text` и др.

### 8.11. `history.php`
- AJAX `addCookieHistory_history` — добавляет товар в cookie `AS_History` (в начало, без дублей).

### 8.12. `search/searchForm.php`
- `as_search($searchParameters)` — форма поиска.
- `as_ajaxBlock($class)` — контейнер для AJAX-результатов.

**Параметры:**
```php
[
  'post_type' => ['product'],
  'taxonomy' => ['catalog' => 'all'],
  'classForm', 'classInput', 'classSubmit', 'valueSubmit',
  'ajax' => [
    'classBlockResult' => '...',
    'positionResult' => 'top|bottom',
    'productResult' => true,
    'catalogResult' => true,
    'postResult' => [
      ['name' => '...', 'class' => '...', 'category' => [36, 37]]
    ]
  ]
]
```

### 8.13. `search/searchAjax.php`
- AJAX `getAjaxSearchResult`.
- `queryManagerSearchResult($searchStr, $searchParameters)` — управляет поиском.
- `queryAllocatorSearchResult($type, $quantity, $searchStr, $parameters)` — маршрутизация.
- `queryTaxonomySearchResult`, `queryPostSearchResult`, `queryProductSearchResult` — запросы.
- `prepCatalog`, `prepPost`, `prepProduct` — подготовка данных.
- `showResult`, `generateResultHTML` — вывод.
- `merge_search_result` — объединение результатов с приоритетом.
- `cleanParameters` — санитизация параметров.
- `fixKeyboardlayout($searchStr)` — замена латиницы на кириллицу (исправление раскладки).

### 8.14. `search/searchGetFilters.php`
- `search_filter($query)` — фильтрация результатов поиска на `/?s=` по `post_type` и `as_taxonomy`.

## 9. Вспомогательные функции, на которые опирается тема

Тема **обязательно** использует эти функции:
- `as_infocart($pageID)` — виджет корзины в шапке.
- `as_search($params)` — форма поиска.
- `get_catalog_terms()` — иерархия каталога (в `getData/catalog.php`, но подключается из темы).
- `splitMenu($menuElements, $countResult, ...)` — разбивка меню на столбцы.
- `view_menu_elements($menuElements, $classUL, $howManylevels)` — рендер меню.
- `view_products_list($productsList)` — список товаров.
- `generate_product_card($post, $productsShoppingCart)` — карточка товара (определена в **теме**).
- `getActualPrice(...)` — актуальная цена.
- `check_saleSteps()` — включены ли скидки.
- `get_user_orders(...)` — заказы пользователя.
- `view_user_orders(...)` — вывод заказов.
- `show_user_fields(...)` — поля форм.
- `kama_breadcrumbs(...)`, `kama_pagenavi(...)` — навигация.
- `get_history_products($count)` — история.
- `get_marked_products($count, $type)` — хиты/новинки.
- `button_card_product(...)` — кнопки в карточке.
- `getDataCart(...)`, `getCookie(...)` — работа с корзиной.

## 10. Замечания по безопасности и качеству

- **Nonce отсутствует** в большинстве AJAX-обработчиков (корзина, кабинет, поиск).
- **Санитизация** частичная: `sanitize_text_field`, `wp_strip_all_tags`, `strip_tags` — используются, но не везде.
- **`mail()`** вместо `wp_mail()` — не учитывает SMTP, хуки, вложения по-настоящему.
- **`extract()`** в `settingPage.php` и функциях вывода.
- **`preg_replace /e`** — в `themes/bmshop/last_viewed_posts.php` (не в плагине).
- **`each()`** — устаревшая конструкция.
- **Отсутствие префиксов** у многих функций — риск коллизий с другими плагинами.
- **Cookie без HttpOnly/Secure** — по умолчанию.
- **Прямой доступ** к `$_POST`/`$_GET` без проверки `isset()` в некоторых местах.

## 11. Рекомендации для ИИ

- При анализе **сначала** смотри `argon-shop.php` — там видно, какие модули подключены.
- Помни: **порядок `require_once`** влияет на доступность функций.
- Учитывай, что тема вызывает функции плагина **напрямую** — при рефакторинге нельзя менять сигнатуры без обновления темы.
- Не предлагай переход на Composer/PSR без явного запроса — это ломает легаси-среду.
- При правках в AJAX-обработчиках — не забывай, что JS-сторона ждёт конкретный JSON.
