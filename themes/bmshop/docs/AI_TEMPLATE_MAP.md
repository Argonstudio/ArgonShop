# AI_TEMPLATE_MAP.md — Карта шаблонов темы BMShop

> Соответствие «URL → шаблон → ключевые функции» для темы `bmshop`.
> Основано на анализе файлов темы и структуре WordPress.
> Плагин `argon-shop` предоставляет бизнес-логику, тема — отображение.

## 1. Общие сведения

- **Тема:** `bmshop` (архивная, 2018 г.).
- **Зависимость:** **обязательно** требует активированного плагина `argon-shop`.
- **Шаблоны:** используют `get_header()`, `get_footer()`, `kama_breadcrumbs()`, `kama_pagenavi()`.
- **Функции плагина:** вызываются напрямую (например, `as_infocart()`, `view_products_list()`).

### ⚠️ Важное замечание о URL `/catalog/`

Базовая страница таксономии `/catalog/` (без конкретной категории) на демо-сайте
`plugin.argon-studio.ru` **возвращает 404**. Это связано  с настройками магазина.

**Как это отражается в документации:**
- URL `/catalog/` указан как **потенциально нерабочий** — для просмотра каталога
  нужно перейти на конкретную категорию, например `/catalog/strojmaterialy/`.
- Если вы хотите сделать `/catalog/` рабочим, необходимо:
  1. Создать статическую страницу с ярлыком `catalog` и назначить ей шаблон
     `taxonomy-catalog.php` (через иерархию шаблонов WordPress).
  2. Или пересохранить постоянные ссылки в `Настройки → Постоянные ссылки`.

## 2. Карта URL → шаблон

| URL (пример) | Шаблон | Тип | Ключевые функции (тема + плагин) |
|--------------|--------|-----|-----------------------------------|
| `/` | `index.php` | Главная | `get_catalog_terms()`, `splitMenu()`, `view_menu_elements()`, `get_marked_products()` |
| `/catalog/` | — | (404, см. примечание выше) | — |
| `/catalog/{category}/` | `taxonomy-catalog.php` | Таксономия `catalog` | `as_sort()`, `get_sort_products()`, `view_products_list()`, `generate_product_card()` |
| `/catalog/{category}/{product-slug}/` | `single-product.php` | Запись `product` | `getActualPrice()`, `getActualStepDiscont()`, `get_charact()`, `button_card_product()` |
| `/?s=...` | `search.php` | Поиск | `as_search()`, `generate_product_card()` |
| `/cart/` (ID из `settingShop`) | `shoppingCartPage.php` | Страница | `getCookie()`, `getActualPrice()`, `getClassActiveItem()`, `show_user_fields()` |
| `/my-account/` (ID из `settingShop`) | `myCabinetPage.php` | Страница | `view_user_orders()`, `show_user_fields()` |
| `/history/` | `history.php` | Страница | `get_history_products()`, `view_products_list()` |
| `/about/` | `aboutCompany.php` | Страница | `WP_Query` (CPT `ourclients`, `sertificates`) |
| `/contacts/` | `contacts.php` | Страница | `do_shortcode('[contact-form-7 ...]')`, `get_template_part('addressMap')` |
| `/aktsii-moskva/` | `category-aktsii-moskva.php` | Категория | `WP_Query` с `meta_query` (`promo_active`), `kama_pagenavi()` |
| `/arhiv-aktsij-moskva/` | `categoryArchive-aktsii-moskva.php` | Шаблон страницы | `WP_Query` с `meta_query` (`promo_active = 0`) |
| `/aktsii-istra/` | `category-aktsii-istra.php` | Категория | Перенаправляет на `404.php` |
| `/arhiv-aktsij-istra/` | `categoryArchive-aktsii-istra.php` | Шаблон страницы | `WP_Query` с `meta_query` (`promo_active = 0`) |
| `/stati/` | `archive.php` или `category.php` | Архив | `kama_pagenavi()`, `get_terms('category')` |
| `/stati/{post-slug}/` | `single-default.php` | Запись | `the_content()`, `kama_breadcrumbs()` |
| `/shoporder/{order-slug}/` | `single-shoporder.php` | Запись `shoporder` | `get_the_id()` |
| Любая несуществующая | `404.php` | 404 | `kama_breadcrumbs()` |

## 3. Детальное описание ключевых шаблонов

### 3.1. `index.php` — Главная

**URL:** `/`

**Что выводит:**
1. **Слайдер** — `WP_Query` по CPT `slider`, Slick.
2. **Блок «Наши преимущества»** — статический HTML (4 карточки).
3. **Блок «Каталог строительных материалов»:**
   - `get_catalog_terms()` — иерархия каталога.
   - `splitMenu($catalog, 3, ...)` — разбивка на 3 столбца.
   - `view_menu_elements($subMenuValue, "homePageCatalogList", 2)` — рендер.
4. **Блок «Акции и скидки»** — интегрируется в разбивку каталога (`$splitMenu[$leactList][36]`).

**Ключевые функции плагина:**
- `get_catalog_terms()`
- `splitMenu()`
- `view_menu_elements()`
- `get_marked_products()` (для сайдбара)

**JS:** `homePage/slick-slider/settingSliders.js`, `sitebar/menuCatalog/menuCatalog.js`

### 3.2. `taxonomy-catalog.php` — Каталог

**URL:** `/catalog/{category}/` (например, `/catalog/strojmaterialy/`)

> **Важно:** базовая страница `/catalog/` может возвращать 404 — см. примечание в начале.

**Что выводит:**
1. **Заголовок:** `second_title` (ACF) или `single_term_title()`.
2. **Описание:** `term_description()`, раскрывающееся на мобильных (`catalogPage_openMobile`).
3. **Дочерние категории:**
   - `get_terms('catalog', ['parent' => $current_id])`.
   - Делятся на `base` и `request` по полю `catalog_type` (ACF).
4. **Популярные запросы** — `$termsRequest`.
5. **Панель сортировки:**
   - `as_sort($settingSort, $catID, $pageNum)` — плагин.
6. **Список товаров:**
   - `$typeSort = getCookie('AS_CatalogSorting')` — если есть.
   - `get_sort_products($typeSort['typeSort'], $typeSort['howSort'], $catID, $pageNum)`.
   - `view_products_list($productsList)` → `generate_product_card()`.
7. **Нижнее описание:** `bottom_desc` (ACF).
8. **Пагинация:** `kama_pagenavi()`.

**JS:** `catalog/catalogPage/catalogPage.js`, `catalog/catalogPage/request/request.js`, `catalog/catalogPage/sort/sort.js`

### 3.3. `single-product.php` — Карточка товара

**URL:** `/catalog/{category}/{product-slug}/`

**Что выводит:**
1. **Заголовок** и **артикул** (`_article`).
2. **Слайдер** (Slick + Fancybox):
   - `slider_imgs` (мета) — массив ID изображений.
3. **Цена:**
   - `_price` (мета).
   - Если включены скидки — `getActualPrice("pageProduct", ...)`.
4. **Оптовые цены** (`_wholesalePrice`):
   - Таблица шагов, активный — `.actualStepDiscont`.
5. **Поле количества** (`#amountProduct`), кнопки `#addShoppingCart` и `#productPageCartBuy`.
6. **Блок `#howManyProducts`** — сколько уже в корзине.
7. **Блок `#addProductError`** — ошибки.
8. **Табы:** Описание / Характеристики / Применение.
9. **Характеристики:** `template-parts/productPage/characteristics.php`.

**Ключевые функции плагина:**
- `getActualPrice()`
- `getActualStepDiscont()`
- `get_charact()`
- `button_card_product()`

**JS:** `product/singleProduct/singleProductSlider/singleProductSlider.js`, `product.js` (плагин)

### 3.4. `shoppingCartPage.php` — Корзина

**URL:** `/cart/` (ID из `settingShop['shoppingCartPage']`)

**Что выводит:**
1. **Чтение cookie** `productsShoppingCart` через `getCookie()`.
2. **Таблица товаров** (`.blockCartProduct`):
   - Картинка, название, артикул, кнопка удаления.
   - Колонки: цена/шт, количество, вес, цена.
   - Мобильный блок.
3. **Итоги:**
   - `.cartTotalPrice` — общая цена.
   - `.cartTotalWeight` — общий вес.
4. **Кнопка «Очистить корзину»** (`.deleteAllProducts`).
5. **Блок оформления заказа:**
   - Табы: «Краткое оформление» / «Физические лица» / «Юридические лица».
   - Формы: `template-parts/cart/{quickOrder,person,legalPerson}.php`.

**Ключевые функции плагина:**
- `getCookie()`
- `getActualPrice()`
- `getClassActiveItem()`
- `show_user_fields()`

**JS:** `cart.js` (плагин)

### 3.5. `myCabinetPage.php` — Личный кабинет

**URL:** `/my-account/` (ID из `settingShop['cabinetPage']`)

**Что выводит:**
1. **Табы:** Аккаунт и адрес / Настройки / Заказы.
2. **Аккаунт:** `template-parts/cabinet/account.php` — `show_user_fields()` для физ/юр.
3. **Настройки:** `template-parts/cabinet/setting.php` — email/пароль, выход.
4. **Заказы:** `template-parts/cabinet/ordering.php` — `view_user_orders()`.

**Ключевые функции плагина:**
- `show_user_fields()`
- `view_user_orders()`
- `get_user_orders()`

**JS:** `cabinet.js` (плагин)

### 3.6. `history.php` — История просмотров

**URL:** `/history/`

**Что выводит:**
1. Заголовок, `the_content()`.
2. Список товаров:
   - `get_history_products()` — плагин.
   - `view_products_list()` → `generate_product_card()`.

**JS:** `product.js` (плагин) — `addCookieHistory()`

### 3.7. `search.php` — Поиск

**URL:** `/?s=...`

**Что выводит:**
1. **Форма поиска:** `as_search($searchParameters)`.
2. **Результаты:**
   - Если `get_search_query()` — `generate_product_card()`.
   - Иначе — «Введите поисковой запрос».

**JS:** `site.js` (плагин) — `getAjaxSearchResult()`

### 3.8. `aboutCompany.php` — О компании

**URL:** `/about/`

**Что выводит:**
1. Блок преимуществ (4 карточки).
2. `our_advantages` (ACF) — текст.
3. **Наши клиенты:** `WP_Query` по CPT `ourclients`.
4. `our_clients` (ACF) — текст.
5. **Сертификаты:** `WP_Query` по CPT `sertificates`, Slick + Fancybox.

### 3.9. `contacts.php` — Контакты

**URL:** `/contacts/`

**Что выводит:**
1. `the_content()`.
2. `addressMap.php` — iframe Google Maps.
3. `[contact-form-7 id="7" ...]` — форма обратной связи.

### 3.10. `category-aktsii-moskva.php` — Акции (Москва)

**URL:** `/aktsii-moskva/`

**Что выводит:**
1. Хлебные крошки (вручную).
2. `WP_Query` по `post_type='post'`, `meta_query: promo_active = 1`, `tax_query: category = 36`.
3. Карточки записей.
4. `kama_pagenavi()`.
5. Ссылка на архив (`/arhiv-aktsij-moskva/`), если есть архивные записи.

### 3.11. `categoryArchive-aktsii-moskva.php` — Архив акций (Москва)

**URL:** `/arhiv-aktsij-moskva/`

**Что выводит:**
1. Хлебные крошки (вручную).
2. `WP_Query` по `post_type='post'`, `meta_query: promo_active = 0`, `tax_query: category = 36`.
3. Карточки записей.
4. `kama_pagenavi()`.

### 3.12. `single-shoporder.php` — Заказ

**URL:** `/shoporder/{order-slug}/`

**Что выводит:**
1. Заголовок (`the_title()`).
2. ID записи.
3. (Легаси-шаблон, не используется для клиентов — только для админов.)

### 3.13. `404.php` — Ошибка 404

**URL:** любая несуществующая

**Что выводит:**
1. Хлебные крошки.
2. Заголовок «Данная страница не существует!».
3. Текст с причинами.
4. Ссылка на главную.

## 4. Части шаблонов (`template-parts/`)

| Файл | Где используется | Ключевые функции |
|------|------------------|-------------------|
| `cabinet/account.php` | `myCabinetPage.php` | `show_user_fields()` |
| `cabinet/setting.php` | `myCabinetPage.php` | `wp_logout_url()` |
| `cabinet/ordering.php` | `myCabinetPage.php` | `view_user_orders()` |
| `cart/quickOrder.php` | `shoppingCartPage.php` | `show_user_fields()` |
| `cart/person.php` | `shoppingCartPage.php` | `show_user_fields()` |
| `cart/legalPerson.php` | `shoppingCartPage.php` | `show_user_fields()` |
| `productPage/characteristics.php` | `single-product.php` | `get_post_meta()`, `get_term_by()` |

## 5. Особенности и замечания

- **`single.php`** — маршрутизация:
  ```php
  if (in_category('36')) → single-aktsii-moskva.php
  else if (in_category('35')) → 404.php
  else → single-default.php
  ```
- **ID страниц** корзины и кабинета **настраиваются** в `settingShop`.
- **`get_catalog_terms()`** — ключевая функция, определена в `getData/catalog.php` (плагин), используется в теме.
- **`generate_product_card()`** — определена в **теме** (`includes/cardProduct/cardProduct.php`), используется в `view_products_list()` (плагин).
- **`splitMenu()`** и **`view_menu_elements()`** — определены в `getData/catalog.php` (плагин), используются в теме.
- **`kama_breadcrumbs()`** и **`kama_pagenavi()`** — определены в плагине, используются в теме.
