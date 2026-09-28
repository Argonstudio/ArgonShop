# AI_CONTEXT.md — Тема BMShop

> Подробное описание темы `bmshop` для ИИ-ассистентов.
> См. также корневой `AI_CONTEXT.md` и `plugins/argon-shop/AI_CONTEXT.md`.

## 1. Назначение

Тема **обеспечивает вёрстку и отображение** интернет-магазина:
- главная страница (слайдер, преимущества, каталог, акции);
- каталог (`taxonomy-catalog.php`);
- карточка товара (`single-product.php`);
- корзина (`shoppingCartPage.php`);
- личный кабинет (`myCabinetPage.php`);
- история просмотров, поиск, контакты, «О компании», статьи, акции.

**Критически важно:** тема **не самодостаточна**. Она **напрямую вызывает** функции
плагина `argon-shop`. Без активированного плагина тема не работает.

## 2. Стек

- PHP 5.6
- jQuery 2.2.2 (подключается **плагином**, не темой)
- Fancybox 3.3.5 (`libs/fancybox/`)
- Slick (`libs/slick/`)
- Contact Form 7 (шорткод в `contacts.php` и `header.php`)
- All in One SEO Pack (опционально, тема использует его фильтры)

## 3. Структура

```
themes/bmshop/
├── functions.php               # Регистрация меню, стилей, скриптов, CPT, walker'ов
├── style.css                   # Метаданные темы
├── header.php, footer.php, sidebar.php
├── index.php                   # Главная
├── page.php, 404.php, search.php
├── archive.php, tag.php, taxonomy.php
├── taxonomy-catalog.php        # Каталог (ключевой шаблон)
├── archive-product.php, archive-products.php
├── single.php                  # Маршрутизация single-*
├── single-default.php, single-aktsii-*.php, single-product.php, single-shoporder.php
├── category-*.php, categoryArchive-*.php
├── aboutCompany.php, contacts.php, addressMap.php
├── history.php, myCabinetPage.php, shoppingCartPage.php
├── includes/cardProduct/cardProduct.php   # generate_product_card()
├── template-parts/
│   ├── cabinet/{account,ordering,setting}.php
│   ├── cart/{quickOrder,person,legalPerson}.php
│   └── productPage/characteristics.php
├── libs/fancybox/, libs/slick/
└── assets/                     # CSS/JS по блокам
```

## 4. `functions.php` — что важно

### 4.1. Регистрация возможностей темы
- `register_nav_menus`: `top_menu`, `bottom_menu`, `bottom_menu2`.
- `add_theme_support`: thumbnails, feed-links, custom-background, custom-header, title-tag.
- `add_image_size`: `slider_thumb` (880×305), `blog_thumb` (260×200), `product_thumb` (280×130), `obj_thumb` (180×120), `obj_about_thumb` (260×200).

### 4.2. `bmshop_scripts()`
Подключает **десятки** CSS/JS из `assets/`. Группы:
- **Библиотеки:** Fancybox, Slick.
- **Базовые:** `main.css`, `controlPanels.css`, `form.css`.
- **topBlock:** `topBlock.css`, `shoppingCart.css`, `siteMenu/`, `search/`.
- **header:** `header.css`, `contactForm/`.
- **sitebar:** `sitebar.css`, `menuCatalog/`, `markedProducts/`, `entryTags/`.
- **cabinet:** `cabinet.css`, `controlPanel.css`, `account/`, `setting/`, `orders/`.
- **cart:** `cart.css`, `controlPanel.css`, `cartListProducts.css`, `totalValue.css`, `forms/`.
- **homePage:** `homePage.css`, `slick-slider/`, `advantages.css`, `catalog.css`.
- **product:** `singleProduct/`, `cardProduct/`.
- **aboutCompany:** `aboutCompany.css`, `aboutCompany.js`.
- **rubrics:** `rubrics.css`, `categoryRubrics/`.
- **catalog:** `catalogPage/`, `request/`, `sort/`, `productsList/`.
- **search:** `searchForm.css`.
- **footer:** `footer.css`.

### 4.3. CPT `ourclients` и `sertificates`
Регистрируются темой (для страницы «О компании»). Отдельно от плагина.

### 4.4. Walker'ы меню
- `bito_walker_nav_menu` — для `aside_menu` (боковое меню).
- `bito_walker_nav_topmenu` — для `top_menu` (верхнее).
- `bitomobile_walker_nav_menu`, `bitomobile_walker_nav_topmenu` — мобильные версии.
- Функции: `bito_header_menu()`, `bito_top_menu()`, `bito_category_menu()`, `bito_mobile_menu()`, `bito_mobile_topmenu()`.

### 4.5. Прочие фильтры и функции
- `bito_excerpt_length` → 100 слов.
- `bito_excerpt_more` → `...`.
- `bito_comments` — кастомный шаблон комментария.
- `bito_reorder_comment_fields` — порядок полей.
- `zbk_widgets_init` — 4 виджета подвала.
- `custom_posts_per_page_home` — 4 поста на главной.
- `hide_hit_class` — вспомогательная.
- **AIOSEO:** `changeDescription`, `changeKeywords`, `changeTitle`, `change_canonical_url` — зависят от URL-параметров фильтров (используются в теме).
- **`true_catalog_redirect`** — редирект древовидных URL каталога (если категория не в родителе).
- **`html_detailed_fields`** — рендер таблицы характеристик (используется в `template-parts/productPage/characteristics.php`).
- Отключение Emoji.
- `require get_template_directory() . '/includes/cardProduct/cardProduct.php'`.

### 4.6. `includes/cardProduct/cardProduct.php`
- `generate_product_card($post, $productsShoppingCart)` — **ключевая функция рендера карточки товара**. Используется в `view_products_list()` (плагин) и в шаблонах темы.
- Внутри: `get_charact("id", 24, $productID)` (ТМ), `button_card_product(...)` (кнопки).

## 5. Шаблоны страниц

### 5.1. `index.php` — главная
- Слайдер (`slider` CPT) через Slick.
- Блок «Наши преимущества» (4 карточки).
- Блок «Каталог строительных материалов» — `get_catalog_terms()` + `splitMenu()` + `view_menu_elements()`.
- Блок «Акции и скидки» (интегрируется в разбивку каталога).

### 5.2. `taxonomy-catalog.php` — каталог
**Ключевой шаблон.**
- Заголовок (`second_title` из ACF или `single_term_title`).
- Описание категории (`term_description`), раскрывающееся на мобильных.
- Дочерние категории (`get_terms` по `catalog`) — делятся на «base» и «request» (популярные запросы) по полю `catalog_type`.
- Блок популярных запросов.
- Панель сортировки: `as_sort($settingSort, $catID, $pageNum)`.
- Список товаров: `get_sort_products()` + `view_products_list()`.
- Нижнее описание (`bottom_desc`).
- `kama_pagenavi()`.

### 5.3. `single-product.php` — карточка товара
- Слайдер (Slick + Fancybox).
- Цена (`#basePrice`), оптовые цены (`_wholesalePrice`).
- Поле количества (`#amountProduct`), кнопки `#addShoppingCart` и `#productPageCartBuy`.
- Блок `#howManyProducts` — сколько уже в корзине.
- Блок `#addProductError` — ошибки.
- Табы: Описание / Характеристики / Применение.
- Характеристики — `template-parts/productPage/characteristics.php`.

### 5.4. `shoppingCartPage.php` — корзина
- Чтение cookie `productsShoppingCart`.
- Таблица товаров (`blockCartProduct`): картинка, название, артикул, кнопка удаления.
- Колонки: цена/шт, количество, вес, цена, мобильный блок.
- Итоги: `cartTotalPrice`, `cartTotalWeight`.
- Кнопка «Очистить корзину» (`deleteAllProducts`).
- Блок оформления заказа с табами: «Краткое оформление» / «Физические лица» / «Юридические лица».
- Формы — `template-parts/cart/{quickOrder,person,legalPerson}.php`.

### 5.5. `myCabinetPage.php` — кабинет
- Табы: Аккаунт и адрес / Настройки / Заказы.
- `template-parts/cabinet/account.php` — `show_user_fields()` для физ/юр.
- `template-parts/cabinet/setting.php` — email/пароль, выход.
- `template-parts/cabinet/ordering.php` — `view_user_orders()`.

### 5.6. `history.php` — история просмотров
- `get_history_products()` (плагин) → `view_products_list()`.

### 5.7. `search.php` — страница поиска
- `as_search($searchParameters)` — форма.
- Список результатов: `generate_product_card()`.

### 5.8. `aboutCompany.php` — о компании
- Блок преимуществ (4 карточки).
- Наши клиенты (CPT `ourclients`).
- Сертификаты (CPT `sertificates`, слайдер Slick, Fancybox).

### 5.9. `contacts.php` — контакты
- `the_content()`.
- `addressMap.php` (iframe Google Maps).
- CF7 шорткод `[contact-form-7 id="7" ...]`.

### 5.10. `404.php`, `page.php`, `archive.php`, `tag.php`, `taxonomy.php`
Стандартные шаблоны с `kama_breadcrumbs()` и `kama_pagenavi()`.

### 5.11. `single.php` — маршрутизация
```php
if (in_category('36')) → single-aktsii-moskva.php
else if (in_category('35')) → 404.php
else → single-default.php
```

## 6. `template-parts/`

### 6.1. `cabinet/account.php`
- `show_user_fields($user_ID, "person", "tr", ...)` — физические лица.
- Чекбокс «Юридическое лицо» (`#accountLegal`).
- `show_user_fields($user_ID, "legalPerson", "tr", ...)` — юридические лица.
- Кнопка `#accountSaveButton` (AJAX `cabinetAccountSave`).

### 6.2. `cabinet/setting.php`
- Email (`#editEmail`, `#editEmailActive`).
- Старый/новый пароль, подтверждение.
- Кнопка `#editEmailPasswordButton` (AJAX `cabinetEditEmailPassword`).
- `wp_logout_url()` — выход.

### 6.3. `cabinet/ordering.php`
- `view_user_orders($user_ID)`.

### 6.4. `cart/quickOrder.php`
- `show_user_fields($user_ID, "quick", "div", ...)`.
- Комментарий, кнопка отправки.

### 6.5. `cart/person.php`
- `show_user_fields($user_ID, "person", ...)`.
- Email, комментарий, файл, регистрация, доставка/оплата, отправка.

### 6.6. `cart/legalPerson.php`
- `show_user_fields($user_ID, "person", ...)` (контактное лицо).
- Email.
- `show_user_fields($user_ID, "legalPerson", ...)` (реквизиты).
- Комментарий, файл, регистрация, доставка/оплата, отправка.

### 6.7. `productPage/characteristics.php`
- Читает `_weight`, `_characteristics_text_field`, `_characteristics_checkbox_field`.
- Рендер таблицы `.detailed-fields`.
- Для каждой характеристики — `data-fancybox` с описанием (`term->description`).

## 7. Ресурсы (`assets/`)

### 7.1. CSS по блокам
| Папка | Что стилизует |
|-------|---------------|
| `topBlock/` | Фиксированный верхний блок (меню, поиск, корзина, «Мой кабинет») |
| `header/` | Шапка (лого, адрес, телефон, выбор города), CF7 |
| `sitebar/` | Сайдбар: меню каталога, теги, «Вы смотрели», «Хиты», «Новинки» |
| `cabinet/` | Личный кабинет (account, setting, orders, controlPanel) |
| `cart/` | Корзина (cart, controlPanel, cartListProducts, totalValue, forms) |
| `homePage/` | Главная (слайдер, преимущества, каталог) |
| `product/` | Карточка товара (singleProduct, cardProduct) |
| `aboutCompany/` | О компании |
| `rubrics/` | Статьи/акции (списки, карточки) |
| `catalog/` | Каталог (catalogPage, request, sort, productsList) |
| `search/` | Форма поиска |
| `footer/` | Подвал |

### 7.2. JS по блокам
| Файл | Назначение |
|------|------------|
| `topBlock/siteMenu/siteMenu.js` | Мобильное меню (открытие/закрытие, подменю) |
| `topBlock/search/topSearch.js` | Скрытие результатов поиска при уходе мыши |
| `header/header.js` | Выбор города (редирект) |
| `header/contactForm/contactForm.js` | CF7 + Fancybox (закрытие после отправки) |
| `sitebar/menuCatalog/menuCatalog.js` | Мобильное меню каталога, подгонка высоты |
| `cabinet/account/account.js` | Чекбокс «Юридическое лицо» |
| `homePage/slick-slider/settingSliders.js` | Slick-слайдер главной |
| `product/singleProduct/singleProductSlider/singleProductSlider.js` | Слайдер товара |
| `product/singleProduct/aboutProduct/characteristics/characteristics.js` | (закомментировано) |
| `aboutCompany/aboutCompany.js` | Slick-слайдер сертификатов |
| `catalog/catalogPage/catalogPage.js` | Раскрытие блоков на мобильных |
| `catalog/catalogPage/request/request.js` | Раскрытие популярных запросов |
| `cart/forms/forms.js` | (закомментировано) |

## 8. Ключевые DOM-элементы (селекторы, которые ищет JS плагина)

Тема **обязана** содержать эти элементы для работы JS плагина:
- Корзина: `.shoppingCartAmountProduct`, `.deleteProduct`, `.deleteAllProducts`, `.plusProduct`, `.minusProduct`, `.blockCartProduct`, `.productPrice`, `.amountProductWeight`, `.amountProductPrice`, `.cartTotalPrice`, `.cartTotalWeight`, `.shoppingCartError`, `.errorDeleteProducts`.
- Карточка: `.card-inBascet`, `.card-buy`, `.block-cardProductBascet`, `.card-alreadyAdded`, `.card-addProductError`.
- Товар: `#amountProduct`, `#addShoppingCart`, `#productPageCartBuy`, `#howManyProducts`, `#addProductError`, `.totalPrice`, `#basePrice`, `.plusProduct-Page`, `.minusProduct-Page`.
- Кабинет: `#editEmailPasswordButton`, `#accountSaveButton`, `#editEmail`, `#editOldPass`, `#editNewPass`, `#editNewPassConfirm`, `.accountDetail`, `.accountLegalDetail`, `#accountLegal`.
- Поиск: `.asInputSearchForm`, `.searchAjaxResult`, `.topSearchBlockResult`, `.pageSearchBlockResult`.
- Сортировка: `.as-sort`, `.sortParameters`, `.catalogProductList`, `.as-error-sort`.
- История: `addCookieHistory(productID)`.

## 9. Зависимости от плагина (список функций)

Тема вызывает:
- `as_infocart`, `as_search`
- `get_catalog_terms`, `splitMenu`, `view_menu_elements`
- `view_products_list`, `generate_product_card`
- `getActualPrice`, `getActualStepDiscont`, `check_saleSteps`, `getDiscount`
- `get_user_orders`, `view_user_orders`
- `show_user_fields`, `get_cartPageID`, `get_cabinetPageID`, `get_cartPageURL`, `get_cabinetPageURL`
- `get_history_products`, `get_marked_products`
- `button_card_product`, `getDataCart`, `getCookie`, `getClassActiveItem`
- `kama_breadcrumbs`, `kama_pagenavi`
- `as_update_meta` (косвенно)
- `get_charact` (в карточке товара)

## 10. Замечания по безопасности и качеству

- **`last_viewed_posts.php`** — использует `preg_replace /e` (удалён в PHP 7) — потенциальная уязвимость.
- **`each()`** — устаревшая конструкция (удалена в PHP 8).
- **Прямой вывод** `$image[url]`, `$value["..."]` без `esc_*` в шаблонах.
- **`the_content()`** без `wp_kses` — риск XSS при недоверенном контенте.
- **`extract()`** в `functions.php` и шаблонах.
- **`wp_tag_cloud`** и `get_terms` без кэширования — много запросов.
- **`file_get_contents($imageUrl)`** в `sidebar.php` — может тормозить на больших изображениях.
- **`<span>` внутри `<a>` без семантики** — есть в карточках.
- **`echo ($link) ? ... : ''`** — есть потенциальный XSS, если `$link` из недоверенного источника.

## 11. Рекомендации для ИИ

- **Никогда не меняй имена функций плагина**, которые вызывает тема, без одновременного обновления темы.
- **Не удаляй DOM-селекторы** (см. п. 8), иначе JS плагина сломается.
- Учитывай, что тема использует `get_template_part()` — если меняешь структуру `template-parts/`, обнови вызовы.
- Fancybox и Slick инициализируются в JS **темы**, не плагина.
- При добавлении нового шаблона — проверь, что он использует `get_header()` / `get_footer()` и `kama_breadcrumbs()`.
