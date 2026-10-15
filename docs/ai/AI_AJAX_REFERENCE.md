# AI_AJAX_REFERENCE.md — справочник AJAX-действий

> Продолжение [AI.md](../../AI.md). Каждый AJAX-эндпоинт: имя, файл, параметры,
> ответ, JS-обработчик.

**Базовый URL:** `admin_url('admin-ajax.php')` — в JS: `config.url`
(из `arsWpAjax.url`).

**Nonce:** фронтенд — `argon_shop_nonce`, админка — `argon_shop_order_nonce`.
С 1.1.0 исключений нет: **каждое** действие проверяет nonce, `config.skipNonceActions` пуст.

**Текст в ответах:** HTML и сообщения, которые возвращает PHP, уже переведены (`__()`), а цены выводятся в валюте магазина (`as_price_html()`, с 1.1.0). JS добавляет собственный текст только через `t()`.

---

## Фронтенд

### `addProducts_shoppingCart`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/interface/shoppingCart/cart-ajax.php` |
| **Функция** | `as_addProducts_shoppingCart_callback` |
| **Nonce** | да |
| **JS-модуль** | `modules/add-to-cart.js` |
| **Принимает** | `productID` (int), `amountProduct` (int) |
| **Возвращает** | JSON: `{ amountProduct, productAmountCart, totalPrice }` |

### `amountProducts_shoppingCart`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/interface/shoppingCart/cart-ajax.php` |
| **Функция** | `as_amountProducts_shoppingCart_callback` |
| **Nonce** | да |
| **JS-модуль** | `modules/cart.js` (через `fetch` с `AbortController`) |
| **Принимает** | `productID`, `productAmount` (float) |
| **Возвращает** | JSON: `{ weight, actualPrices, productsShoppingCart }` |

### `deleteProducts_shoppingCart`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/interface/shoppingCart/cart-ajax.php` |
| **Функция** | `as_deleteProducts_shoppingCart_callback` |
| **Nonce** | да |
| **JS-модуль** | `modules/cart.js` |
| **Принимает** | `productID` |
| **Возвращает** | JSON-карта `{ productID: actualPrice }` (сама карта, без ключа-обёртки) ИЛИ строка `'false'`, если корзина опустела |

### `deleteAllProducts_shoppingCart`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/interface/shoppingCart/cart-ajax.php` |
| **Функция** | `as_deleteAllProducts_shoppingCart_callback` |
| **Nonce** | да (с 1.1.0) |
| **JS-модуль** | `modules/cart.js` |
| **Принимает** | — |
| **Возвращает** | пусто |

### `wholesalePrice_shoppingCart`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/interface/shoppingCart/cart-ajax.php` |
| **Функция** | `as_wholesalePrice_shoppingCart_callback` |
| **Nonce** | да |
| **JS-модуль** | `modules/product.js` |
| **Принимает** | `type='get'`, `productID`, `amountProduct` |
| **Возвращает** | число (итоговая цена) ИЛИ строка `'none'` |

### `submitCart_shoppingCart`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/interface/shoppingCart/checkout.php` |
| **Функция** | `as_submitCart_shoppingCart_callback` |
| **Nonce** | да |
| **JS-модуль** | `modules/cart.js` |
| **Принимает** | `FormData`: `typeForm` (`quick`/`person`/`legalPerson`), `productsCart`, поля формы, `fileCart[]` (до 3 файлов по 5 МБ). Браузер по-прежнему отправляет `required`, но сервер его **игнорирует**: обязательные поля берутся из `as_get_required_fields()` |
| **Возвращает** | JSON: `{ type: 'success'\|'error', typeForm, message }` |

### `getAjaxSearchResult`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/interface/search/searchAjax.php` |
| **Функция** | `as_getAjaxSearchResult_callback` |
| **Nonce** | да |
| **JS-модуль** | `modules/search.js` |
| **Принимает** | `dataAjax` (JSON-строка), `searchStr` |
| **Возвращает** | HTML блоков результатов (запрос нормализуется по языковым правилам магазина, `searchLanguage.php`) |

### `sortingCatalog`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/interface/catalog/sort.php` |
| **Функция** | `as_sortingCatalog_callback` |
| **Nonce** | да |
| **JS-модуль** | `modules/sort.js` |
| **Принимает** | `typeSort` (`date`/`name`/`price`), `howSorting` (`ASC`/`DESC`), `catId`, `pageNum` |
| **Возвращает** | HTML списка товаров |

### `cabinetEditEmailPassword`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/interface/cabinet/myCabinet.php` |
| **Функция** | `as_cabinetEditEmailPassword_callback` |
| **Nonce** | да |
| **JS-модуль** | `modules/cabinet.js` |
| **Принимает** | `userID`, `email`, `oldEmail`, `pass`, `newPass`, `newPassConfirm` — пароли: проверка строкового типа и только `wp_unslash()`, без `sanitize_text_field()`; сохраняются `<`, `>`, `%xx` и повторяющиеся пробелы. Новый пароль — не менее 6 символов (`mb_strlen()` в UTF-8); пустая строка означает отсутствие смены пароля |
| **Возвращает** | JSON: `{ edited: { newEmail?, newPass? }, errors: { email?, pass?, newPass?, newPassConfirm? }, newNonce? }` — все значения `edited` / `errors` — переведённые строки-сообщения. Чужой `userID` → `wp_die()` с текстом, без JSON |

### `cabinetAccountSave`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/interface/cabinet/myCabinet.php` |
| **Функция** | `as_cabinetAccountSave_callback` |
| **Nonce** | да |
| **JS-модуль** | `modules/cabinet.js` |
| **Принимает** | `userID`, `forBack` (JSON-строка с полями) |
| **Возвращает** | текст (переведённое сообщение об успехе или ошибке) |

### `ordersFiltersStatus`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/interface/cabinet/userOrders/ordersAjax.php` |
| **Функция** | `as_ordersFiltersStatus_callback` |
| **Nonce** | да (с 1.1.0) |
| **JS-модуль** | `modules/orders.js` |
| **Принимает** | `userID`, `status` (строка ID через запятую) |
| **Возвращает** | HTML таблицы заказов |

### `addCookieHistory_history`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/interface/history.php` |
| **Функция** | `as_addCookieHistory_history_callback` |
| **Nonce** | да |
| **JS-модуль** | `modules/history.js` |
| **Принимает** | `productID` |
| **Возвращает** | пусто (сохраняет в cookie `AS_History`) |

---

## Админка

### `update_characteristics`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/admin/fieldsProduct.php` |
| **Функция** | `as_updateCharacteristicsFunction` |
| **Nonce** | `argon_shop_order_nonce` |
| **JS** | встроен в `as_updateCharacteristicsAjax` |
| **Принимает** | `activeCategoryId`, `characteristicsTextField`, `characteristicsCheckboxField` |
| **Возвращает** | HTML метабокса характеристик |
| **Право доступа** | `manage_options` |

### `as_searchSuitableProduct_order`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/admin/order/orderAjax.php` |
| **Функция** | `as_search_suitable_product_order_callback` |
| **Nonce** | `argon_shop_order_nonce` |
| **JS** | `window.searchSuitableProduct` + `admin/modules/orders.js` |
| **Принимает** | `searchStr`, `addedProduct` (массив ID) |
| **Возвращает** | HTML результатов поиска |
| **Право доступа** | `manage_options` |

### `as_addProduct_order`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/admin/order/orderAjax.php` |
| **Функция** | `as_add_product_order_callback` |
| **Nonce** | `argon_shop_order_nonce` |
| **JS** | `window.addProduct` |
| **Принимает** | `productID`, `postID` (ID заказа) |
| **Возвращает** | HTML строки товара |
| **Право доступа** | `manage_options` |

### `as_addFields_order`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/admin/order/orderAjax.php` |
| **Функция** | `as_add_fields_order_callback` |
| **Nonce** | `argon_shop_order_nonce` |
| **JS** | `window.addFields` |
| **Принимает** | `type` (`person`/`legalPerson`), `fieldsInStock` |
| **Возвращает** | HTML полей |
| **Право доступа** | `manage_options` |

### `as_admin_recount_order`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/admin/order/orderAjax.php` |
| **Функция** | `as_admin_recount_order_callback` |
| **Nonce** | `argon_shop_order_nonce` |
| **JS** | `window.recountOrderPrices` (`admin/modules/orders.js`) |
| **Принимает** | `postID`, `orderProducts[ID] = amount` |
| **Возвращает** | JSON: `{ actualPrices, weights, totalPrice, totalWeight }` |
| **Право доступа** | `manage_options` |

---

## Соглашения

1. Каждый AJAX-обработчик фронтенда регистрируется **дважды**:
   ```php
   add_action('wp_ajax_<action>', 'callback');
   add_action('wp_ajax_nopriv_<action>', 'callback');
   ```

2. Каждый AJAX-обработчик админки использует **только** `wp_ajax_<action>`.

3. Ответ — `echo wp_json_encode( $data )` для JSON, `echo` экранированного HTML/текста для HTML-ответов (поиск, сортировка, строки админки). `wp_send_json()` в проекте не используется. **Никакого другого вывода перед ответом.**

4. Завершайте через `wp_die()`. Без него WordPress добавляет `0` к ответу.

5. Проверка nonce — **первая строка**:
   ```php
   check_ajax_referer('argon_shop_nonce', 'nonce');
   ```

6. Проверка прав — **сразу после nonce**, и она должна останавливать запрос:
   ```php
   if ( ! current_user_can( 'manage_options' ) ) {
       wp_die( __( 'Insufficient permissions', 'argon-shop' ) );
   }
   // или, для личного кабинета
   if ( 0 === $userID || $userID !== get_current_user_id() ) {
       wp_die( __( 'Insufficient permissions', 'argon-shop' ) );
   }
   ```
