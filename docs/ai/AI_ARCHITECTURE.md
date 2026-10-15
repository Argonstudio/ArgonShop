# AI_ARCHITECTURE.md — слои, модули, карта папок

Продолжение [AI.md](../../AI.md). Этот файл объясняет внутреннее устройство проекта.

## 1. Монорепозиторий

```
plugins/argon-shop/          Логика магазина
themes/argon-shop-theme/     Разметка и стили
```

**Плагин без темы** ничего не выводит на фронтенде — его функции просто никто не вызывает. **Тема без плагина** не работает — нет CPT `product`, таксономии `catalog`, функции `as_search` и т. д.

## 2. Плагин: три уровня

### 2.1. Точка входа — `argon-shop.php`

Читает константы, определяет пути, регистрирует хуки `admin_enqueue_scripts` и `wp_enqueue_scripts`, локализует JS (`arsWpAjax`, включая `arsWpAjax.i18n`, `myajax`, `arsGalleryI18n`), подключает все модули через `require_once`, добавляет `type="module"` к `main.js`.

Порядок `require_once` важен, но не строго послойный: сначала `settingPage.php`, `install.php`, `legacy-functions.php` и `includes/api/*`, затем слайдеры, `createPostType.php` и `admin/*` (товар, таксономии, галерея), затем интерфейсы (корзина, карточка, сортировка, кабинет, хлебные крошки, поиск, история), затем `admin/order/*` и в конце `siteLine.php`. Новый файл подключайте после всего, от чего он зависит, и сверяйтесь с `argon-shop.php`.

`includes/interface/last_viewed_posts.php` (форк WP-LastViewedPosts) `argon-shop.php` **не подключает** — это мёртвый код, оставленный для справки. «Недавно просмотренные» работают через `history.php` и cookie `AS_History`.

### 2.2. Папка `includes/`

| Подпапка | Отвечает за |
|---|---|
| `includes/*.php` (корень) | `createPostType.php` — CPT и таксономии; `siteLine.php` — мультирегиональность (маркер пункта меню `as_homepage`, см. `AI_EXTENDING.md`, раздел 13); `install.php` — активация/деактивация, миграции (`as_maybe_upgrade()`, `as_get_migrations()`); `legacy-functions.php` — обёртки для старых имён функций (до 1.1.0) |
| `includes/admin/` | Метабоксы и хуки админки: товар, заказ, фильтры, галерея |
| `includes/api/` | Внутренний API: получение данных (`getData/`), вывод (`view/`), настройки, проверки |
| `includes/interface/` | Публичные интерфейсы: корзина, личный кабинет, поиск, слайдеры, пагинация, хлебные крошки |

В `includes/api/getSetting.php` также находятся **хелперы валюты** (с 1.1.0): `as_get_currencies()`, `as_get_currency()`, `as_get_currency_symbol()`, `as_get_currency_position()`, `as_price_html()`, `as_get_currency_per_piece()`. Все цены должны выводиться через них — никогда не хардкодьте знак валюты.

### 2.2.1. Корзина и оформление заказа — `includes/interface/shoppingCart/`

`shoppingCart.php` только подключает модули:

| Файл | Отвечает за |
|---|---|
| `cart-ajax.php` | AJAX корзины: добавление, изменение количества, удаление, очистка |
| `checkout.php` | Оформление: `as_submitCart_shoppingCart_callback()` — оркестратор, `as_checkout_create_order()`, `as_checkout_error()`, способы доставки/оплаты |
| `checkout-fields.php` | Поля формы, обязательные поля (`as_get_required_fields()`) |
| `checkout-files.php` | Проверка и приватное хранение файлов заказа |
| `checkout-registration.php` | Регистрация покупателя при оформлении (ссылка для установки пароля) |
| `checkout-emails.php` | Письма о заказе (`as_checkout_send_emails()`, `as_emailTextsGenerator()`) |
| `infoCart.php` | Виджет корзины `as_infocart()` (подключается отдельно из `argon-shop.php`) |

### 2.2.2. Поиск — `includes/interface/search/`

| Файл | Отвечает за |
|---|---|
| `searchForm.php` | Форма поиска `as_search()` |
| `searchAjax.php` | AJAX-обработчик поиска |
| `searchGetFilters.php` | Построение поискового запроса / фильтры |
| `searchLanguage.php` | Нормализация запроса с учётом языка (с 1.1.0): правила для русского, английского и французского (исправление неверной раскладки, включая AZERTY, стоп-слова, нормализация множественного числа/элизии). Язык берётся из локали сайта (`as_search_language()`); фильтры `argon_shop_search_language`, `argon_shop_search_stop_words`, `argon_shop_search_layout_variants` |

### 2.2.3. Жизненный цикл плагина

- `register_activation_hook` → `includes/install.php`: создаёт (или находит) страницы корзины и кабинета, заполняет поля форм и настройки по умолчанию (`currency` = `USD`), сбрасывает правила перезаписи.
- `init` (приоритет 1) → `as_maybe_upgrade()`: сравнивает сохранённую версию с `ARGON_SHOP_VERSION` и запускает миграции.
- `uninstall.php` (корень плагина): удаляет настройки, служебные опции и приватные файлы заказов. Товары, заказы, пользователи и счётчик заказов **сохраняются**.

**Папка `api/` — не REST API.** В ней внутренние PHP-функции для получения данных. REST-эндпоинтов в проекте нет; всё идёт через `admin-ajax.php`.

### 2.3. Папка `assets/`

- `assets/css/interface/interface-style.css` — функциональный CSS (начальные состояния, которыми управляет JS).
- `assets/css/admin/` — стили метабоксов.
- `assets/js/interface/` — ES-модули фронтенда.
- `assets/js/admin/modules/` — скрипты админки (jQuery).
- `assets/vendor/` — Swiper и GLightbox с сохранёнными лицензиями.

## 3. Структура ES-модулей фронтенда

```
assets/js/interface/
├── main.js                    Точка входа
├── core/                      Ядро
│   ├── config.js              url, nonce, cartUrl, строки i18n, t()
│   ├── api.js                 fetch-клиент (arsApi)
│   └── nonce.js               перехватчик XMLHttpRequest
└── modules/                   21 функциональный модуль
    ├── cart.js                корзина: количество, удаление, отправка
    ├── cart-form.js           форма: регистрация, файлы
    ├── cart-widget.js         виджет корзины в шапке
    ├── product.js             страница товара
    ├── card-product.js        кнопки в карточках товаров
    ├── add-to-cart.js         общая логика добавления в корзину
    ├── search.js              AJAX-поиск
    ├── sort.js                сортировка
    ├── cabinet.js             личный кабинет
    ├── orders.js              фильтр заказов
    ├── consent.js             чекбоксы согласия
    ├── control-panels.js      вкладки
    ├── mobile-menu.js         мобильное меню
    ├── menu-catalog.js        меню каталога
    ├── header.js              выбор города (мультирегиональность, AI_EXTENDING.md §13)
    ├── contact-form.js        CF7 в модальном окне
    ├── catalog-page.js        мобильные блоки каталога
    ├── request.js             популярные запросы
    ├── slider-main.js         слайдер главной страницы
    ├── slider-product.js      слайдер товара
    └── history.js             недавно просмотренные
```

### 3.1. Шаблон модуля

Общие утилиты лежат **на уровень выше**, в `assets/js/shared/` (не внутри `interface/`); модули импортируют их как `../../shared/dom.js`:

```
assets/js/shared/
├── dom.js                     qs, qsa, on, delegate
└── format.js                  parseNumber, formatPrice, formatWeight, formatAmount
```

Функциональные модули экспортируют синглтон с методом `init()`. Исключения: `add-to-cart.js` экспортирует функцию `addProductToCart()`, `cart-widget.js` (`arsCartWidget`) и `history.js` (`arsHistory`) — помощники без инициализации страницы; их импортируют другие модули, а не `main.js`. Метод `init()` идемпотентен и ничего не делает, если нужных DOM-элементов нет.

```js
class ArsModule {
    #initialized = false;
    init() {
        if ( this.#initialized ) return;
        if ( ! qs( '.some-selector' ) ) return;
        this.#initialized = true;
        // обработчики
    }
}
export const arsModule = new ArsModule();
```

`initApp()` в `main.js` вызывает `initNonceInterceptor()` и `init()` у 18 функциональных модулей на каждой странице. Это безопасно — каждый модуль решает сам.

### 3.2. Видимые пользователю строки в JS

В JS никогда нет захардкоженного видимого текста. Используйте `t( 'key' )` из `core/config.js`: функция читает переведённые строки из `arsWpAjax.i18n` (их формирует `argon_shop_js_i18n()` в PHP, фильтр `argon_shop_js_i18n`) и при отсутствии берёт английские `I18N_DEFAULTS`. Галерея в админке использует `arsGalleryI18n`; модальное окно темы — `arsModalI18n`. См. `AI_I18N.md`.

### 3.3. Почему без jQuery

- Нет неявных зависимостей.
- Нет проблем с порядком загрузки (обработчики делегируются на `document`).
- Нет конкуренции за `$`.
- Меньший объём загрузки.

### 3.4. Тесты — `tests/`

- `tests/static-check.php` — проверки без WordPress (`php tests/static-check.php`): синтаксис, переводы, версии, кириллицу в коде JS/CSS (комментарии RU-ветки на русском и ошибкой не считаются).
- `tests/smoke-test.php` — сквозная проверка на тестовом сайте через WP-CLI (`wp eval-file tests/smoke-test.php`), работает с любым языком сайта.

Не входит в продакшен-сборку. Подробнее: `tests/README.md`, `AI_TESTING.md`.

### 3.5. Переводы — `languages/`

`plugins/argon-shop/languages/` (`argon-shop.pot`, `argon-shop-ru_RU.po|mo`, `argon-shop-fr_FR.po|mo`) и `themes/argon-shop-theme/languages/` (`argon-shop-theme.pot`, `ru_RU.po|mo`, `fr_FR.po|mo`). Язык комментариев и документации в RU-ветке — русский; код и идентификаторы — английские. Подробнее: `AI_I18N.md`.

## 4. Тема: слои

```
themes/argon-shop-theme/
├── style.css                  Метаданные темы (Theme Name, версия, Requires at least 7.1, Requires PHP 8.3)
├── functions.php              Подключение скриптов, CPT ourclients/sertificates, обёртки валюты
├── header.php, footer.php,    Глобальные шаблоны
│   sidebar.php
├── index.php, page.php,       Шаблоны страниц по типам
│   404.php, search.php, ...
├── single-*.php,              Отдельные записи
│   category-*.php, taxonomy-*.php
├── template-parts/            Части шаблонов
│   ├── cabinet/               Вкладки кабинета
│   ├── cart/                  Формы заказа
│   └── productPage/           Блоки страницы товара
├── includes/cardProduct/      generate_product_card
└── assets/                    JS и CSS темы
```

**Где что лежит:**

- **Шаблоны страниц** — в корне темы (`single-product.php`, `shoppingCartPage.php`).
- **Части шаблонов** — в `template-parts/`.
- **Стили** — в `assets/css/`, разбиты по блокам (главная, корзина, кабинет, каталог).
- **JS темы** — `assets/js/` (модальное окно, раскрытие поиска, слайдер сертификатов).
- **Цены** — через `astheme_price_html()`, `astheme_currency_symbol()`, `astheme_currency_position()` (обёртки над хелперами валюты плагина, корректно деградирующие без плагина).

## 5. Точки соприкосновения плагина и темы

**Тема вызывает функции плагина** в шаблонах:

| Функция | Файл плагина |
|---|---|
| `as_kama_breadcrumbs` | `includes/interface/breadcrumbs.php` (объявлена как `AS_Kama_Breadcrumbs()`; имена функций в PHP не чувствительны к регистру) |
| `as_kama_pagenavi` | `includes/interface/pagenavi.php` |
| `as_search` | `includes/interface/search/searchForm.php` |
| `as_infocart` | `includes/interface/shoppingCart/infoCart.php` |
| `as_sort` | `includes/api/view/catalog/catalogPage.php` (AJAX-колбэк `as_sortingCatalog_callback` — в `includes/interface/catalog/sort.php`) |
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
| `as_price_html`, `as_get_currency_symbol`, `as_get_currency_position` | `includes/api/getSetting.php` (через обёртки темы) |
| `as_button_card_product` | `includes/interface/product/cardProduct.php` |
| `as_view_user_orders` | `includes/interface/cabinet/userOrders/ordersView.php` |
| `as_get_charact` | `includes/api/getData/admin/get_characteristics.php` |

Если функция была переименована в 1.1.0, старое имя продолжает работать через `includes/legacy-functions.php` (см. `AI_FUNCTION_NAMES.md`). Новый код должен использовать только новые имена.

**Тема зависит от скриптов, которые регистрирует плагин.** `astheme_scripts()` в `functions.php` темы подключает `slider-sertificates.js` с зависимостями `swiper-js` и `glightbox-js`, которые подключает плагин (`argon-shop.php`). Без плагина этих хэндлов нет, и WordPress не выводит скрипт темы.

## 6. Регистрация CPT и таксономий

Всё находится в `includes/createPostType.php`:

| Сущность | Имя | Тип |
|---|---|---|
| Товар | `product` | CPT, публичный |
| Заказ | `shoporder` | CPT, `publicly_queryable => false` |
| Слайд | `slider` | CPT, публичный |
| Каталог | `catalog` | Иерархическая таксономия |
| Характеристики | `characteristics` | Иерархическая таксономия |
| Оптовые ступени цен | `wholesalePrice` | Плоская, регистрируется по условию |
| Статусы заказов | `statusorders` | Плоская |
| Клиенты (тема) | `ourclients` | CPT, регистрируется в `functions.php` темы |
| Сертификаты (тема) | `sertificates` | CPT, регистрируется в `functions.php` темы |

## 7. Зависимости от сторонних плагинов

**ACF** — необязателен. Используется для полей `_hit` (в `last_viewed_posts.php`, не подключается), `unit_product`, `application`, `second_title`, `second_desc`, `bottom_desc`, `image_catalog`, `catalog_type`, `our_advantages`, `our_clients`. Без ACF `get_field()` не вызывается, и блоки просто пустые.

**Contact Form 7** — необязателен. Используется для формы обратной связи. Без него форма не выводится.

**Других обязательных зависимостей нет.** Плагин и тема самодостаточны.
