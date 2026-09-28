# AI.md — индекс документации для ИИ-ассистентов

Этот файл — точка входа. Он короткий. Детальная информация — в специализированных файлах, чтобы не перегружать контекстное окно LLM.

| Файл | О чём |
|---|---|
| **AI.md** | этот файл — индекс, ключевые контракты, жёсткие запреты |
| [AI_ARCHITECTURE.md](AI_ARCHITECTURE.md) | слои, модули, карта папок |
| [AI_DATA_FLOWS.md](AI_DATA_FLOWS.md) | 7 ключевых сценариев данных |
| [AI_AJAX_REFERENCE.md](AI_AJAX_REFERENCE.md) | все AJAX-действия с параметрами |
| [AI_TEMPLATE_MAP.md](AI_TEMPLATE_MAP.md) | шаблоны темы ↔ функции плагина |
| [AI_EXTENDING.md](AI_EXTENDING.md) | как добавить фичу, не сломав архитектуру |
| [AGENTS.md](AGENTS.md) | указатель для Cursor / Codex / Claude Code |

---

## 1. Что это

**ArgonShop** — плагин интернет-магазина для WordPress + тема к нему. Монорепозиторий. Демонстрационный стенд (портфолио), не продакшн.

| Параметр | Значение |
|---|---|
| Стек | PHP 7.4+, WordPress 6.0+, Vanilla JS (ES-модули), CSS3 |
| Лицензия | GPL-3.0-or-later |
| Репозиторий | https://github.com/Argonstudio/ArgonShop |
| Фронтенд JS | `<script type="module">`, никакого jQuery |
| Данные корзины | кука `productsShoppingCart` (JSON), не БД |

## 2. Как читать этот набор файлов

- **Если задача — понять проект с нуля:** `AI_ARCHITECTURE.md` → `AI_DATA_FLOWS.md`.
- **Если править AJAX-обработчик:** `AI_AJAX_REFERENCE.md` + нужный сценарий из `AI_DATA_FLOWS.md`.
- **Если править шаблон темы:** `AI_TEMPLATE_MAP.md`.
- **Если править JS-модуль:** сценарий из `AI_DATA_FLOWS.md` + раздел 5 этого файла.
- **Если добавляешь фичу:** `AI_EXTENDING.md`.

## 3. Ключевые контракты (что нельзя ломать)

### 3.1. Разделение слоёв

- **Логика** (CPT, таксономии, AJAX, БД, письма) — только в плагине.
- **Вёрстка** (шаблоны, оформление) — только в теме.
- **Функциональный CSS** — в плагине: `assets/css/interface/interface-style.css`. Только начальные состояния элементов, управляемых JS.

### 3.2. jQuery

**На фронтенде jQuery запрещён.** Только `fetch` и DOM API. В админке jQuery разрешён, потому что его требуют `wp.media` и `jQuery UI Sortable` — интерфейсы ядра WordPress.

### 3.3. Данные магазина

- Корзина — кука `productsShoppingCart` (JSON).
- История — кука `AS_History`.
- Сортировка — кука `AS_CatalogSorting`.
- Заказ в БД (`shoporder`) создаётся только в момент оформления.

### 3.4. Nonce

- Фронтенд: `argon_shop_nonce`, локализуется как `window.arsWpAjax.nonce`.
- Админка заказа: `argon_shop_order_nonce`, локализуется как `window.argonShopOrderNonce`.

### 3.5. Точка входа JS

`plugins/argon-shop/assets/js/interface/main.js` подключается как `<script type="module">`. Все модули импортируются оттуда. Атрибут `type="module"` добавляется фильтром `ars_add_module_type` в `argon-shop.php`.

## 4. Жёсткие запреты

Раздел действует как **негативный промпт** — модель отсекает неверные варианты сразу.

- **Не использовать jQuery на фронтенде.** Регресс архитектуры.
- **Не менять DOM-селекторы** из `AI_AJAX_REFERENCE.md` и `AI_TEMPLATE_MAP.md` без синхронной правки PHP и JS.
- **Не менять имена AJAX-действий** без синхронной правки `add_action` и `arsApi.post`.
- **Не менять формат мета-полей** (`_price`, `_productsCart`, `slider_imgs`, `accountData` и др.) — есть уже залитые данные.
- **Не менять формат куки** `productsShoppingCart` без миграции — у части пользователей она заполнена.
- **Не переименовывать функции плагина**, которые вызываются из темы (`as_search`, `as_infocart`, `kama_breadcrumbs`, `kama_pagenavi`, `view_products_list`, `as_slider_main`, `as_slider_product`, `getActualPrice`, `getCookie`, `get_cartPageURL`, `get_cabinetPageURL`, `getClassActiveItem`, `show_user_fields`, `button_card_product`, `generate_product_card`).
- **Не трогать `*.min.js` и `*.min.css` в `assets/vendor/`.** Это сторонние библиотеки с сохранёнными лицензиями.
- **Не добавлять `<script>` в PHP-файлы** без веской причины. Вся клиентская логика — в ES-модулях.
- **Не писать `echo` в AJAX-обработчике перед `wp_die()`** — сломает JSON.

## 5. DOM-классы и ID, за которыми следит JS

Это контракт между PHP и JS. Переименование = сломанный функционал.

**Корзина:** `.shoppingCartAmountProduct` (`data-productid`), `.plusProduct`, `.minusProduct`, `.deleteProduct`, `.deleteAllProducts`, `.blockCartProduct`, `.productPrice span`, `.amountProductPrice span`, `.amountProductWeight span`, `.cartTotalPrice span`, `.cartTotalWeight span`, `.cartTotalWeight`, `#shoppingCart`, `.shoppingCartError`, `.errorDeleteProducts`.

**Форма заказа:** `.form-cart[data-type]`, `.cartReg`, `.cartLogin`, `.block-regQuestion`, `.labelAddFile`, `input[type="file"][name="fileCart[]"]`, `.block-fileCartMessage`, `.fileCartMessage`, `.loadSaccess`, `.loadError`, `.loadedFiles`, `.submitError span`.

**Товар:** `#amountProduct`, `.plusProduct-Page`, `.minusProduct-Page`, `#addShoppingCart`, `#productPageCartBuy` (`data-productid`), `#howManyProducts span`, `#addProductError`, `.totalPrice span`, `#basePrice`.

**Карточка:** `.card-inBascet`, `.card-buy`, `.block-cardProductBascet`, `.card-alreadyAdded`, `.card-addProductError`.

**Поиск:** `.asInputSearchForm` (`data-ajax`, `data-blockresult`), `.asSubmitSearchForm`, `.searchAjaxResult`.

**Сортировка:** `.as-sort[name]`, `.sortParameters[data-cat-id][data-page]`, `.catalogProductList`, `.as-error-sort`.

**ЛК:** `#editEmail`, `#editEmailActive`, `#editOldPass`, `#editNewPass`, `#editNewPassConfirm`, `#editEmailPasswordButton`, `#accountSaveButton`, `.editError`, `.editEmailError`, `.editPassError`, `.editNewPassError`, `.editNewPassConfirmError`, `.editResult span`, `.accountCustomCheckbox`, `#accountLegal`, `#accountLegalBlock`, `.accountDetail`, `.accountLegalDetail`, `.accountResult span`, `.as-orderCabinet-filterStatus`, `.as-ordersCabinet-block`.

**Табы:** `.itemControlPanel`, `.mobileItemControlPanel` (`data-type`, `name`, `data-mobileWidth`), `.blockItemPage[id="block_{name}"]`.

**Виджет корзины:** `.viewBlock-amountProducts span`, `.viewBlock-priceProducts`, `.viewBlock-priceProducts span`.

**Поиск в шапке (тема):** `.block-topSearch`, `.topSearchSubmit`.

**Модалка (тема):** `[data-modal-ajax]`, `[data-modal-inline]`, `[data-modal-iframe]`, `[data-modal-close]`.

## 6. Быстрые ответы

| Вопрос | Ответ |
|---|---|
| Где точка входа плагина? | `plugins/argon-shop/argon-shop.php` |
| Где точка входа фронтенд-JS? | `plugins/argon-shop/assets/js/interface/main.js` |
| Где AJAX-обработчик корзины? | `includes/interface/shoppingCart/shoppingCart.php` |
| Где шаблон страницы корзины? | `themes/argon-shop-theme/shoppingCartPage.php` |
| Где ловится изменение количества? | `modules/cart.js` → `#bindAmountChange` |
| Где письма о заказе? | `submitCart_shoppingCart_callback` → `emailTextsGenerator()` |
| Где регистрация CPT? | `includes/createPostType.php` |

## 7. Авторство

Структура документации подготовлена при участии [DeepSeek](https://www.deepseek.com/) по итогам реальной работы над репозиторием. Файлы — внутренний инструмент, правь свободно.
