# AI_CONTEXT.md — Карта репозитория ArgonShop (ветка `historic/php56-jquery`)

> Этот файл — точка входа для ИИ-ассистентов. Он описывает назначение репозитория,
> его структуру, ключевые технологии и указывает, где искать детали.

## 1. Что это

**ArgonShop** — самописный интернет-магазин на WordPress, историческая версия 2018 года.
Репозиторий содержит **плагин** (`plugins/argon-shop`) и **тему** (`themes/bmshop`),
которые работают только в связке: тема вызывает функции плагина напрямую.

**Стек:**
- PHP 5.6 (без пространств имён, глобальные функции)
- jQuery 2.2.2 (локальная копия в `plugins/argon-shop/assets/interface/js/jquery.js`)
- WordPress ≥ 4.9
- Библиотеки: Fancybox 3.3.5, Slick
- Плагин All in One SEO Pack (опционально, тема использует его фильтры)

**Лицензия:** GNU GPL v2.0 or later.

## 2. Карта репозитория

```
ArgonShop/
├── plugins/argon-shop/        # Плагин: бизнес-логика магазина
│   └── AI_CONTEXT.md          # ← подробное описание плагина
├── themes/bmshop/             # Тема: вёрстка и отображение
│   └── AI_CONTEXT.md          # ← подробное описание темы
├── docs/
│   └── AI_DATA_FLOWS.md       # ← сквозные сценарии (плагин + тема)
└── AI_CONTEXT.md              # ← этот файл
```

## 3. Разделение ответственности

| Слой | Где | Что делает |
|------|-----|------------|
| Бизнес-логика | `plugins/argon-shop/` | CPT, таксономии, корзина, заказы, скидки, поиск, AJAX, настройки |
| Представление | `themes/bmshop/` | Шаблоны страниц, вёрстка, CSS, JS, вызовы функций плагина |
| Данные | Метаданные, cookie, опции | `_price`, `productsShoppingCart`, `AS_History`, `settingShop` |

**Важно:** тема **не самодостаточна**. Она вызывает функции плагина:
`as_infocart()`, `as_search()`, `get_catalog_terms()`, `view_products_list()`,
`getActualPrice()`, `get_user_orders()`, `kama_breadcrumbs()`, `kama_pagenavi()` и др.
Без активированного плагина тема не работает.

## 4. Ключевые сущности

### Типы записей (CPT)
- `product` — товар
- `shoporder` — заказ
- `slider` — слайд главной
- `ourclients` — клиент (для «О компании», регистрируется темой)
- `sertificates` — сертификат (для «О компании», регистрируется темой)

### Таксономии
- `catalog` — иерархический каталог товаров
- `characteristics` — иерархические характеристики товаров
- `wholesalePrice` — шаги оптовых цен (если включены в настройках)
- `statusorders` — статусы заказов (для `shoporder`)
- `category`, `post_tag` — стандартные для статей/акций

### Опции WordPress
- `settingShop` — массив настроек магазина (ID страниц корзины/кабинета, тип скидок, поля форм)
- `counterOrders` — счётчик заказов (для нумерации)

### Cookie
- `productsShoppingCart` — корзина: `{productID: {amountProduct, price}}`
- `AS_History` — история просмотров: `[productID, ...]`
- `AS_CatalogSorting` — выбранная сортировка каталога: `{typeSort, howSort}`

### Ключевые метаданные
- Товар: `_price`, `_weight`, `_article`, `_hit`, `_newProduct`, `_wholesalePrice`,
  `_characteristics_text_field`, `_characteristics_checkbox_field`
- Заказ: `_productsCart`, `_fieldsCart`, `_number`, `_productAmountCart`,
  `_totalPrice`, `_shipping`, `_payment`
- Термин характеристики: `checkCategory` (привязка к категориям каталога)
- Пользователь: `accountData` (сохранённые поля форм)

## 5. Ключевые AJAX-экшены

Все через `admin-ajax.php`, имена — `action` в POST.

| Экшен | Назначение | Файл |
|-------|-----------|------|
| `addProducts_shoppingCart` | Добавить товар в cookie-корзину | `shoppingCart.php` |
| `amountProducts_shoppingCart` | Изменить количество | `shoppingCart.php` |
| `deleteProducts_shoppingCart` | Удалить товар | `shoppingCart.php` |
| `deleteAllProducts_shoppingCart` | Очистить корзину | `shoppingCart.php` |
| `submitCart_shoppingCart` | Оформить заказ | `shoppingCart.php` |
| `wholesalePrice_shoppingCart` | Цена с учётом скидки | `shoppingCart.php` |
| `sortingCatalog` | AJAX-сортировка каталога | `catalog/sort.php` |
| `getAjaxSearchResult` | AJAX-поиск | `search/searchAjax.php` |
| `cabinetEditEmailPassword` | Смена email/пароля | `cabinet/myCabinet.php` |
| `cabinetAccountSave` | Сохранить данные пользователя | `cabinet/myCabinet.php` |
| `ordersFiltersStatus` | Фильтр заказов по статусу | `cabinet/userOrders/ordersAjax.php` |
| `addCookieHistory_history` | Добавить в историю просмотров | `interface/history.php` |
| `update_characteristics` | Обновить поля характеристик в админке | `admin/fieldsProduct.php` |

## 6. Ключевые сценарии (сквозные)

См. `docs/AI_DATA_FLOWS.md`. Кратко:
1. **Каталог** → `taxonomy-catalog.php` → `get_sort_products()` → `view_products_list()` → `generate_product_card()`.
2. **Корзина** → JS `addProducts()` → AJAX → cookie → обновление виджета.
3. **Оформление** → форма → AJAX `submitCart_shoppingCart` → регистрация → запись `shoporder` → email → очистка cookie.
4. **Личный кабинет** → AJAX сохранение → мета `accountData`.
5. **Поиск** → AJAX → `queryManagerSearchResult()` → `showResult()`.

## 7. Известные легаси-проблемы

- `preg_replace` с модификатором `/e` в `themes/bmshop/last_viewed_posts.php` (удалён в PHP 7).
- `each()` в `foreach` (удалён в PHP 8).
- Отсутствие nonce в AJAX-обработчиках корзины/кабинета.
- `mail()` вместо `wp_mail()`.
- Прямой вывод переменных без `esc_html()` во многих шаблонах.
- `extract()` в настройках и функциях вывода.
- Глобальные функции без префиксов (риск коллизий).

## 8. Как ИИ следует работать с этим репозиторием

1. **Сначала** прочитай `plugins/argon-shop/AI_CONTEXT.md` и `themes/bmshop/AI_CONTEXT.md`.
2. Для конкретного сценария — `docs/AI_DATA_FLOWS.md`.
3. Помни: тема и плагин **жёстко связаны**; изменения в API плагина ломают тему.
4. Учитывай PHP 5.6: не предлагай синтаксис PHP 7+ без явного запроса на миграцию.
5. Учитывай jQuery 2.2: не предлагай ES6+ синтаксис в JS без явного запроса.
