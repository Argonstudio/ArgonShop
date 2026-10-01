# AI_ARCHITECTURE.md — слои, модули, карта папок

Продолжение [AI.md](../../AI.md). Здесь — как устроен проект внутри.

## 1. Монорепозиторий

```
plugins/argon-shop/          Логика магазина
themes/argon-shop-theme/     Вёрстка и стили
```

**Плагин без темы** не выводит ничего на фронтенде — его функции просто не вызываются. **Тема без плагина** не работает — нет CPT `product`, таксономии `catalog`, функций `as_search` и др.

## 2. Плагин: три уровня

### 2.1. Точка входа — `argon-shop.php`

Читает константы, определяет пути, регистрирует хуки `admin_enqueue_scripts` и `wp_enqueue_scripts`, локализует JS (`arsWpAjax`, `myajax`), подключает все модули через `require_once`, добавляет `type="module"` к `main.js`.

Порядок `require_once` соответствует слоям: сначала API, потом VIEW, потом интерфейсы, потом админка.

### 2.2. Папка `includes/`

| Подпапка | Отвечает за |
|---|---|
| `includes/*.php` (корень) | `createPostType.php` — CPT и таксономии; `siteLine.php` — мультирегиональность |
| `includes/admin/` | Метабоксы и хуки админки: товар, заказ, фильтры, галерея |
| `includes/api/` | Внутреннее API: получение данных (`getData/`), вывод (`view/`), настройки, проверки |
| `includes/interface/` | Публичные интерфейсы: корзина, ЛК, поиск, слайдеры, пагинация, хлебные крошки |

**Папка `api/` — это не REST API.** Это внутренние PHP-функции для получения данных. REST-эндпоинтов в проекте нет, всё идёт через `admin-ajax.php`.

### 2.3. Папка `assets/`

- `assets/css/interface/interface-style.css` — функциональный CSS (начальные состояния, управляемые JS).
- `assets/css/admin/` — стили метабоксов.
- `assets/js/interface/` — ES-модули фронтенда.
- `assets/js/admin/modules/` — скрипты админки (с jQuery).
- `assets/vendor/` — Swiper и GLightbox с сохранёнными лицензиями.

## 3. Структура ES-модулей фронтенда

```
assets/js/interface/
├── main.js                    Точка входа
├── core/                      Ядро
│   ├── config.js              url, nonce, cartUrl
│   ├── api.js                 fetch-клиент (arsApi)
│   └── nonce.js               перехватчик XMLHttpRequest
├── shared/                    Общие утилиты
│   ├── dom.js                 qs, qsa, on, delegate
│   └── format.js              formatPrice, formatWeight, parseNumber
└── modules/                   21 функциональный модуль
    ├── cart.js                корзина: количество, удаление, submit
    ├── cart-form.js           форма: регистрация, файлы
    ├── cart-widget.js         виджет корзины в шапке
    ├── product.js             страница товара
    ├── card-product.js        кнопки в карточках
    ├── add-to-cart.js         общая логика добавления
    ├── search.js              AJAX-поиск
    ├── sort.js                сортировка
    ├── cabinet.js             ЛК
    ├── orders.js              фильтр заказов
    ├── consent.js             чекбоксы согласия
    ├── control-panels.js      табы
    ├── mobile-menu.js         мобильное меню
    ├── menu-catalog.js        меню каталога
    ├── header.js              выбор города
    ├── contact-form.js        CF7 в модалке
    ├── catalog-page.js        мобильные блоки каталога
    ├── request.js             популярные запросы
    ├── slider-main.js         слайдер главной
    ├── slider-product.js      слайдер товара
    └── history.js             история просмотров
```

### 3.1. Паттерн модуля

Каждый модуль экспортирует синглтон с методом `init()`. Метод идемпотентный, при отсутствии своих DOM-элементов ничего не делает.

Схема:

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

`initApp()` в `main.js` вызывает `init()` у всех модулей на всех страницах. Это безопасно — каждый сам решает, что делать.

### 3.2. Почему без jQuery

- Нет неявных зависимостей.
- Нет проблем с порядком загрузки (обработчики навешиваются делегированием на `document`).
- Нет конкуренции за `$`.
- Меньше веса.

## 4. Тема: слои

```
themes/argon-shop-theme/
├── style.css                  Метаданные темы (только заголовок)
├── functions.php              Подключение скриптов, CPT ourclients/sertificates
├── header.php, footer.php,    Глобальные шаблоны
│   sidebar.php
├── index.php, page.php,       Шаблоны страниц по типам
│   404.php, search.php, ...
├── single-*.php,              Одиночные записи
│   category-*.php, taxonomy-*.php
├── template-parts/            Частичные шаблоны
│   ├── cabinet/               Вкладки ЛК
│   ├── cart/                  Формы заказа
│   └── productPage/           Блоки страницы товара
├── includes/cardProduct/      generate_product_card
└── assets/                    JS и CSS темы
```

**Что где лежит:**

- **Шаблоны страниц** — прямо в корне темы (`single-product.php`, `shoppingCartPage.php`).
- **Части шаблонов** — в `template-parts/`.
- **Стили** — в `assets/css/`, разложены по блокам (главная, корзина, ЛК, каталог).
- **JS темы** — `assets/js/` (модалка, раскрытие поиска, слайдер сертификатов).

## 5. Точки соприкосновения плагина и темы

**Тема вызывает функции плагина** в шаблонах:

| Функция | Файл плагина |
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

**Плагин подключает JS темы** через зависимости — например, `slider-sertificates.js` зависит от `swiper-js` и `glightbox-js`.

## 6. Регистрация CPT и таксономий

Всё в `includes/createPostType.php`:

| Сущность | Имя | Тип |
|---|---|---|
| Товар | `product` | CPT, публичный |
| Заказ | `shoporder` | CPT, `publicly_queryable => false` |
| Слайд | `slider` | CPT, публичный |
| Каталог | `catalog` | Иерархическая таксономия |
| Характеристики | `characteristics` | Иерархическая таксономия |
| Шаги оптовых цен | `wholesalePrice` | Плоская, регистрируется условно |
| Статусы заказов | `statusorders` | Плоская |
| Клиенты (тема) | `ourclients` | CPT, регистрируется в `functions.php` темы |
| Сертификаты (тема) | `sertificates` | CPT, регистрируется в `functions.php` темы |

## 7. Зависимости от сторонних плагинов

**ACF** — опционален. Используется для полей `unit_product`, `application`, `second_title`, `second_desc`, `bottom_desc`, `image_catalog`, `catalog_type`, `our_advantages`, `our_clients`. Если ACF нет — функции `get_field()` не вызываются, блоки просто пустые.

**Contact Form 7** — опционален. Используется для формы обратной связи. Если нет — форма не выводится.

**Других обязательных зависимостей нет.** Плагин и тема самодостаточны.
