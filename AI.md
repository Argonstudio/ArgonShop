# AI.md — контекст проекта для ИИ-ассистентов

Этот файл — краткое структурированное описание проекта. Предназначен
для LLM-ассистентов (DeepSeek, Gemini, Claude, ChatGPT, Cursor, Codex, Aider),
которые помогают с разработкой, рефакторингом или развёртыванием.

**Читайте первым.** Файл заменит десяток вопросов и убережёт от типичных
ошибок, которые человек допускает при первом знакомстве с кодовой базой.

---

## 0. Об этом файле

Структура, карта файлов и раздел «Контракт JS ↔ PHP» подготовлены
при участии [DeepSeek](https://www.deepseek.com/) — по итогам реальной
работы над репозиторием: разбора багов после перехода с jQuery на
нативный JS, выноса логики из PHP в ES-модули, тестирования
на PHP 8.5 и WordPress 7.x.

Файл не является официальной документацией WordPress. Если найдёшь
неточность — правь смело. Это внутренний инструмент проекта,
а не священный текст.

---

## 1. Что это

**ArgonShop** — плагин интернет-магазина для WordPress + тема для него.
Собраны в монорепозиторий: плагин и тема образуют единое целое и
распространяются вместе.

| Параметр | Значение |
|---|---|
| Тип | Монорепозиторий (плагин + тема) |
| Стек | PHP 7.4+, WordPress 6.0+, Vanilla JS (ES-модули), CSS3 |
| Лицензия | GPL-3.0-or-later |
| Репозиторий | https://github.com/Argonstudio/ArgonShop |

Проект — **демонстрационный стенд** (портфолио). Реальные заказы не
обрабатываются, персональные данные не собираются.

---

## 2. Быстрая навигация

```
plugins/argon-shop/          Логика магазина (CPT, AJAX, БД, письма)
themes/argon-shop-theme/     Вёрстка и стили (шаблоны, CSS, меню)
```

**Правило разделения:**
- Плагин — **что делает** магазин.
- Тема — **как выглядит**.

Ничего из логики в теме быть не должно. Ничего из вёрстки — в плагине
(кроме служебного функционального CSS: `assets/css/interface/interface-style.css`).

---

## 3. Точка входа для чтения

Если нужно понять проект за 15 минут — читай в этом порядке:

1. **`plugins/argon-shop/argon-shop.php`** — точка входа плагина.
   Список подключённых модулей, порядок хуков, локализация JS.
2. **`plugins/argon-shop/includes/createPostType.php`** — все сущности
   магазина: CPT `product` / `shoporder` / `slider`, таксономии `catalog` /
   `characteristics` / `wholesalePrice` / `statusorders`.
3. **`plugins/argon-shop/includes/interface/shoppingCart/shoppingCart.php`** —
   AJAX-логика корзины, самый частый источник багов.
4. **`plugins/argon-shop/assets/js/interface/main.js`** — точка входа
   фронтенд-JS, список ES-модулей.
5. **`themes/argon-shop-theme/functions.php`** — подключение скриптов
   и стилей темы, регистрация CPT темы (`ourclients`, `sertificates`).

---

## 4. Карта «фича → где искать»

| Фича | PHP | JS |
|---|---|---|
| Каталог | `api/getData/catalog.php`, `api/view/catalog/` | `modules/catalog-page.js`, `modules/sort.js` |
| Товар | `interface/product/cardProduct.php`, `interface/slider/sliderProduct.php` | `modules/product.js`, `modules/slider-product.js` |
| Корзина | `interface/shoppingCart/shoppingCart.php` | `modules/cart.js`, `modules/cart-widget.js` |
| Форма заказа | `interface/shoppingCart/shoppingCart.php` | `modules/cart-form.js` |
| Оформление + письма | `submitCart_shoppingCart_callback` | — |
| ЛК: аккаунт, пароль | `interface/cabinet/myCabinet.php` | `modules/cabinet.js` |
| ЛК: заказы | `interface/cabinet/userOrders/` | `modules/orders.js` |
| Поиск (GET) | `interface/search/searchGetFilters.php` | — |
| Поиск (AJAX) | `interface/search/searchAjax.php` | `modules/search.js` |
| Раскрытие поиска | — | тема: `search-toggle.js` |
| AJAX-сортировка | `interface/catalog/sort.php` | `modules/sort.js` |
| История просмотров | `interface/history.php`, `last_viewed_posts.php` | `modules/history.js` |
| Модальное окно | — | тема: `modal.js` |
| Админка: товар | `admin/fieldsProduct.php`, `admin/galleryMetabox.php` | `admin/modules/admin-script.js`, `admin/modules/gallery.js` |
| Админка: заказ | `admin/order/order*.php` | `admin/modules/orders.js` |
| Настройки магазина | `settingPage.php` | — |

---

## 5. Архитектурные принципы (НЕ нарушать)

### 5.1. Плагин и тема разделены по ответственности

- **Логика** (бизнес-правила, запросы, AJAX) — только в плагине.
- **Вёрстка** (шаблоны страниц, оформление) — только в теме.
- **Функциональный CSS** — в плагине: `assets/css/interface/interface-style.css`.
  Это только начальные состояния элементов, управляемых JS
  (`display:none` для ошибок, табов, результатов поиска). Не путать
  с оформлением.

### 5.2. Фронтенд — на ES-модулях, без jQuery

- `main.js` подключается как `<script type="module">`.
- Внутри — `import` из `core/`, `shared/`, `modules/`.
- **Никаких jQuery-вызовов на фронтенде.** Только `fetch`, `querySelector`,
  `addEventListener`.
- jQuery остался только в админке, где его требует `wp.media` и
  `jQuery UI Sortable` — это интерфейсы ядра WordPress, переписывать их
  смысла нет.

### 5.3. Данные магазина — в куках, не в БД

- **Корзина:** кука `productsShoppingCart` (JSON).
- **История просмотров:** кука `AS_History` (JSON).
- **Сортировка каталога:** кука `AS_CatalogSorting` (JSON).
- **Заказ:** создаётся в БД (CPT `shoporder`) **только** в момент оформления.

Это осознанное решение: снижает нагрузку на MySQL при «примерке» товаров.

### 5.4. Мета-поля и их названия

| Поле | Где | Формат |
|---|---|---|
| `_price` | product | число |
| `_weight` | product | число |
| `_article` | product | строка |
| `_hit` | product | `'on'` / отсутствует |
| `_newProduct` | product | `'on'` / отсутствует |
| `_wholesalePrice` | product | `[term_id => price]` |
| `_characteristics_text_field` | product | `[term_id => value]` |
| `_characteristics_checkbox_field` | product | `[parent_id => [child_id => 'on']]` |
| `slider_imgs` | product | `"12,15,18"` |
| `_productsCart` | shoporder | массив товаров |
| `_fieldsCart` | shoporder | массив полей покупателя |
| `_number` | shoporder | номер заказа |
| `_totalPrice` | shoporder | сумма |
| `_shipping` | shoporder | `selfExport` / `shippingToAddress` |
| `_payment` | shoporder | `paimentUponReceipt` |
| `accountData` | user | `['accountDetail' => [...], 'accountLegalDetail' => [...]]` |
| `checkCategory` | term (characteristics) | `[term_id => 'on']` |

---

## 6. Контракт JS ↔ PHP (не менять без синхронной правки)

### 6.1. DOM-классы и ID, за которыми следит JS

**Корзина:**
- `.shoppingCartAmountProduct` (input, `data-productid`)
- `.plusProduct`, `.minusProduct` (`data-productid`)
- `.deleteProduct` (`data-productid`), `.deleteAllProducts`
- `.blockCartProduct` (`data-productid`)
- `.productPrice`, `.amountProductPrice`, `.amountProductWeight` (внутри — `span`)
- `.cartTotalPrice span`, `.cartTotalWeight span`, `.cartTotalWeight`
- `#shoppingCart`, `.shoppingCartError`, `.errorDeleteProducts`
- `.form-cart[data-type]`, `.submitError span`
- `.cartReg`, `.cartLogin`, `.block-regQuestion`
- `.labelAddFile`, `input[type=file][name="fileCart[]"]`
- `.block-fileCartMessage`, `.fileCartMessage`, `.loadSaccess`, `.loadError`, `.loadedFiles`

**Товар:**
- `#amountProduct`, `.plusProduct-Page`, `.minusProduct-Page`
- `#addShoppingCart`, `#productPageCartBuy` (`data-productid`)
- `#howManyProducts span`, `#addProductError`, `.totalPrice span`, `#basePrice`

**Карточка:**
- `.card-inBascet`, `.card-buy` (`data-productid`)
- `.block-cardProductBascet`, `.card-alreadyAdded`, `.card-addProductError`

**Поиск:**
- `.asInputSearchForm` (`data-ajax`, `data-blockresult`)
- `.asSubmitSearchForm`, `.searchAjaxResult`

**Сортировка:**
- `.as-sort[name]`, `.sortParameters[data-cat-id][data-page]`
- `.catalogProductList`, `.as-error-sort`

**ЛК:**
- `#editEmail`, `#editEmailActive`, `#editOldPass`, `#editNewPass`, `#editNewPassConfirm`
- `#editEmailPasswordButton`, `#accountSaveButton`
- `.editError`, `.editEmailError`, `.editPassError`, `.editNewPassError`
- `.accountCustomCheckbox`, `#accountLegal`, `#accountLegalBlock`
- `.as-orderCabinet-filterStatus`, `.as-ordersCabinet-block`

**Общее:**
- `.itemControlPanel`, `.mobileItemControlPanel` (`data-type`, `name`)
- `.blockItemPage[id="block_{name}"]`
- `.mobileItemControlPanel[data-mobileWidth]`

### 6.2. AJAX-действия

Все идут через `admin-ajax.php`, у каждого — свой `add_action('wp_ajax_...')`:

| Action | Назначение |
|---|---|
| `addProducts_shoppingCart` | добавить товар |
| `amountProducts_shoppingCart` | изменить количество |
| `deleteProducts_shoppingCart` | удалить товар |
| `deleteAllProducts_shoppingCart` | очистить корзину |
| `wholesalePrice_shoppingCart` | пересчёт оптовой цены |
| `submitCart_shoppingCart` | оформить заказ |
| `getAjaxSearchResult` | AJAX-поиск |
| `sortingCatalog` | сортировка каталога |
| `cabinetEditEmailPassword` | смена email/пароля |
| `cabinetAccountSave` | сохранение аккаунта |
| `ordersFiltersStatus` | фильтр заказов в ЛК |
| `addCookieHistory_history` | добавить в историю |
| `update_characteristics` | обновление метабокса характеристик (админка) |
| `as_searchSuitableProduct_order` | поиск товара в заказе (админка) |
| `as_addProduct_order` | добавить товар в заказ (админка) |
| `as_addFields_order` | добавить поля формы (админка) |
| `as_admin_recount_order` | пересчёт заказа (админка) |

Nonce: единый `argon_shop_nonce` для фронтенда, `argon_shop_order_nonce`
для страницы заказа в админке.

---

## 7. Локальное развёртывание (для ассистентов)

### Минимальный путь (5 минут)

```bash
# 1. Развернуть WordPress (пример через wp-cli, если установлен)
wp core download
wp config create --dbname=argon --dbuser=root --dbpass=
wp core install --url=http://argon.local \
                --title=ArgonShop \
                --admin_user=admin \
                --admin_email=admin@example.com \
                --admin_password=admin

# 2. Склонировать репозиторий
cd wp-content
git clone https://github.com/Argonstudio/ArgonShop.git argon-repo

# 3. Симлинки
ln -s argon-repo/plugins/argon-shop plugins/argon-shop
ln -s argon-repo/themes/argon-shop-theme themes/argon-shop-theme

# 4. Активировать
wp plugin activate argon-shop
wp theme activate argon-shop-theme

# 5. Рекомендуемые плагины
wp plugin install advanced-custom-fields contact-form-7 --activate
```

На Windows — `mklink /D` вместо `ln -s` в cmd с правами администратора.

### Демо-данные

Готового импорта нет. Для теста нужно создать вручную:

1. Категории каталога: **Товары → Каталог → Добавить**.
2. Несколько товаров: **Товары → Добавить товар**. Заполнить `_price`, `_weight`, `_article`. Отметить `_hit` / `_newProduct` по желанию.
3. Статусы заказов: **Заказы → Статусы заказов** (например: Новый, В обработке, Выполнен).
4. Настройки: **Настройки → Магазин** — указать ID страницы корзины и кабинета.
5. Создать страницы: **Корзина** (шаблон `shoppingCart`), **Личный кабинет** (шаблон `CabinetPage`), **История просмотров**, **О компании**, **Контакты**.

---

## 8. Что нельзя делать

- **Не использовать jQuery на фронтенде.** Это регресс.
- **Не хранить HTML-разметку в JS.** Рендер — задача PHP-шаблонов.
- **Не трогать `*.min.js` и `*.min.css` в `assets/vendor/`.**
  Это сторонние библиотеки с сохранёнными лицензиями. Если нужно обновить
  версию — скачать с официального источника и проверить заголовок.
- **Не переименовывать DOM-селекторы и AJAX-действия без синхронной
  правки в PHP и JS.** Они — контракт между слоями.
- **Не добавлять `wp_enqueue_style` для темы в плагине.** Стилизация — в теме.
- **Не писать `echo` внутри `functions.php` плагина без `wp_die()`** —
  испортит JSON-ответы AJAX.
- **Не менять формат куки `productsShoppingCart`** без миграции — у части
  пользователей она уже заполнена.

---

## 9. Известные ограничения

Это не баги, а честные ограничения текущей версии. Ассистент, зная о них,
не будет тратить время на «исправление того, что и так работает».

- **`searchGetFilters.php` и `searchAjax.php`** дублируют логику
  ранжирования. Обе реализации сверены, но любое изменение в одной
  требует синхронного изменения в другой.
- **`last_viewed_posts.php`** — форк старого плагина. Используется только
  на одной странице (виджет). Постепенно уходит.
- **`breadcrumbs.php` и `pagenavi.php`** — форки Kama, адаптированы под
  PHP 8.5 и защиту от WP_Error. Форки изолированы, конфликтов с
  оригинальными плагинами Kama нет.
- **Мультирегиональность (Москва / Истра)** реализована на уровне
  шаблонов (`single-aktsii-moskva.php`, `single-aktsii-istra.php`)
  и не использует WP Multisite.
- **ACF опционален.** Если не установлен — часть полей на страницах
  «О компании», категориях каталога останется пустой, но ошибок не будет.
- **Contact Form 7 опционален.** Без него форма обратной связи не выведется.

---

## 10. Полезные точки входа для задач

| Задача | Откуда начать |
|---|---|
| «Не работает добавление в корзину» | `modules/cart.js` → `add-to-cart.js` → `shoppingCart.php` |
| «Не появляются результаты поиска» | `modules/search.js` → `searchAjax.php` |
| «Не оформляется заказ» | `modules/cart-form.js` → `submitCart_shoppingCart_callback` |
| «Не работает смена пароля» | `modules/cabinet.js` → `myCabinet.php` |
| «Плохо выглядит корзина» | `themes/argon-shop-theme/assets/css/cart/` |
| «Нужно поле в форме заказа» | `settingPage.php` → `show_user_fields()` |
| «Не сохраняется метабокс товара» | `admin/fieldsProduct.php` → `save_detailed_fields` |
| «Не работает поиск товара в заказе» | `admin/order/orderAjax.php` |

---

## 11. Соглашения по коду

- **PHP:** `snake_case` для функций и переменных, `PascalCase` для классов.
- **JS:** `camelCase` для переменных/методов, `PascalCase` для классов.
  Модули — синглтоны с методом `init()`.
- **CSS:** классы в `lowerCamelCase` или через дефисы — как в существующем файле.
- **Отступы:** 4 пробела в PHP, 4 пробела в JS.
- **Комментарии:** многострочные блоки с `@param`, `@return`, `@version`.
- **Защита:** в каждом PHP-файле — `if ( ! defined( 'ABSPATH' ) ) exit;`.
- **Безопасность:** `esc_html`, `esc_attr`, `esc_url`, `absint`, `sanitize_*`
  везде, где выводится или принимается пользовательский ввод.

---

## 12. Как задавать вопросы

Если ассистент работает через CLI/IDE и не понимает, где искать:

1. Файл `AI.md` (этот).
2. `plugins/argon-shop/argon-shop.php` — там перечислены все модули
   с комментариями.
3. Комментарии внутри конкретного файла — они подробные.

Если нужно предложить изменение — сначала проверить раздел «Контракт
JS ↔ PHP»: изменение DOM-класса может порвать связь между слоями.

---

**Автор:** Иван Войтков · [argon-studio.ru](https://argon-studio.ru) · [voit.ne@gmail.com](mailto:voit.ne@gmail.com)
