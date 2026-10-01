# AI_AJAX_REFERENCE.md — Справочник AJAX-действий

> Продолжение [AI.md](../../AI.md). Все AJAX-эндпоинты: имя, файл, параметры,
> ответ, обработчик в JS.

**Базовый URL:** `admin_url('admin-ajax.php')` — в JS: `config.url`
(из `arsWpAjax.url`).

**Nonce:** фронтенд — `argon_shop_nonce`, админка — `argon_shop_order_nonce`.
Исключения указаны в колонке «Nonce».

---

## Фронтенд

### `addProducts_shoppingCart`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/interface/shoppingCart/shoppingCart.php` |
| **Функция** | `addProducts_shoppingCart_callback` |
| **Nonce** | да |
| **JS-модуль** | `modules/add-to-cart.js` |
| **Принимает** | `productID` (int), `amountProduct` (int) |
| **Возвращает** | JSON: `{ amountProduct, productAmountCart, totalPrice }` |

### `amountProducts_shoppingCart`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/interface/shoppingCart/shoppingCart.php` |
| **Функция** | `amountProducts_shoppingCart_callback` |
| **Nonce** | да |
| **JS-модуль** | `modules/cart.js` (через `fetch` с `AbortController`) |
| **Принимает** | `productID`, `productAmount` (float) |
| **Возвращает** | JSON: `{ weight, actualPrices, productsShoppingCart }` |

### `deleteProducts_shoppingCart`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/interface/shoppingCart/shoppingCart.php` |
| **Функция** | `deleteProducts_shoppingCart_callback` |
| **Nonce** | да |
| **JS-модуль** | `modules/cart.js` |
| **Принимает** | `productID` |
| **Возвращает** | JSON актуальных цен ИЛИ строку `'false'` |

### `deleteAllProducts_shoppingCart`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/interface/shoppingCart/shoppingCart.php` |
| **Функция** | `deleteAllProducts_shoppingCart_callback` |
| **Nonce** | нет (в `config.skipNonceActions`) |
| **JS-модуль** | `modules/cart.js` |
| **Принимает** | — |
| **Возвращает** | пусто |

### `wholesalePrice_shoppingCart`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/interface/shoppingCart/shoppingCart.php` |
| **Функция** | `wholesalePrice_shoppingCart_callback` |
| **Nonce** | да |
| **JS-модуль** | `modules/product.js` |
| **Принимает** | `type='get'`, `productID`, `amountProduct` |
| **Возвращает** | число (итоговая цена) ИЛИ строку `'none'` |

### `submitCart_shoppingCart`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/interface/shoppingCart/shoppingCart.php` |
| **Функция** | `submitCart_shoppingCart_callback` |
| **Nonce** | да |
| **JS-модуль** | `modules/cart.js` |
| **Принимает** | `FormData`: `typeForm`, `required`, `productsCart`, поля формы, `fileCart[]` |
| **Возвращает** | JSON: `{ type: 'success'\|'error', typeForm, message }` |

### `getAjaxSearchResult`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/interface/search/searchAjax.php` |
| **Функция** | `getAjaxSearchResult_callback` |
| **Nonce** | да |
| **JS-модуль** | `modules/search.js` |
| **Принимает** | `dataAjax` (JSON-строка), `searchStr` |
| **Возвращает** | HTML блоков результатов |

### `sortingCatalog`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/interface/catalog/sort.php` |
| **Функция** | `sortingCatalog_callback` |
| **Nonce** | да |
| **JS-модуль** | `modules/sort.js` |
| **Принимает** | `typeSort` (`date`/`name`/`price`), `howSorting` (`ASC`/`DESC`), `catId`, `pageNum` |
| **Возвращает** | HTML списка товаров |

### `cabinetEditEmailPassword`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/interface/cabinet/myCabinet.php` |
| **Функция** | `cabinetEditEmailPassword_callback` |
| **Nonce** | да |
| **JS-модуль** | `modules/cabinet.js` |
| **Принимает** | `userID`, `email`, `oldEmail`, `pass`, `newPass`, `newPassConfirm` |
| **Возвращает** | JSON: `{ edited: {}, errors: {}, newNonce? }` |

### `cabinetAccountSave`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/interface/cabinet/myCabinet.php` |
| **Функция** | `cabinetAccountSave_callback` |
| **Nonce** | да |
| **JS-модуль** | `modules/cabinet.js` |
| **Принимает** | `userID`, `forBack` (JSON-строка с полями) |
| **Возвращает** | текст («Информация успешно сохранена» или ошибка) |

### `ordersFiltersStatus`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/interface/cabinet/userOrders/ordersAjax.php` |
| **Функция** | `ordersFiltersStatus_callback` |
| **Nonce** | нет (в `config.skipNonceActions`) |
| **JS-модуль** | `modules/orders.js` |
| **Принимает** | `userID`, `status` (строка ID через запятую) |
| **Возвращает** | HTML таблицы заказов |

### `addCookieHistory_history`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/interface/history.php` |
| **Функция** | `addCookieHistory_history_callback` |
| **Nonce** | да |
| **JS-модуль** | `modules/history.js` |
| **Принимает** | `productID` |
| **Возвращает** | пусто (сохраняет в куку `AS_History`) |

---

## Админка

### `update_characteristics`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/admin/fieldsProduct.php` |
| **Функция** | `updateCharacteristicsFunction` |
| **Nonce** | `argon_shop_order_nonce` |
| **JS** | inline в `updateCharacteristicsAjax` |
| **Принимает** | `activeCategoryId`, `characteristicsTextField`, `characteristicsCheckboxField` |
| **Возвращает** | HTML метабокса характеристик |
| **Права** | `manage_options` |

### `as_searchSuitableProduct_order`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/admin/order/orderAjax.php` |
| **Функция** | `as_search_suitable_product_order_callback` |
| **Nonce** | `argon_shop_order_nonce` |
| **JS** | `window.searchSuitableProduct` + `admin/modules/orders.js` |
| **Принимает** | `searchStr`, `addedProduct` (массив ID) |
| **Возвращает** | HTML результатов поиска |
| **Права** | ⚠️ нет проверки — TODO |

### `as_addProduct_order`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/admin/order/orderAjax.php` |
| **Функция** | `as_add_product_order_callback` |
| **Nonce** | `argon_shop_order_nonce` |
| **JS** | `window.addProduct` |
| **Принимает** | `productID`, `postID` (ID заказа) |
| **Возвращает** | HTML таблицы товара |
| **Права** | `manage_options` |

### `as_addFields_order`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/admin/order/orderAjax.php` |
| **Функция** | `as_add_fields_order_callback` |
| **Nonce** | `argon_shop_order_nonce` |
| **JS** | `window.addFields` |
| **Принимает** | `type` (`person`/`legalPerson`), `fieldsInStock` |
| **Возвращает** | HTML полей |
| **Права** | ⚠️ нет проверки — TODO |

### `as_admin_recount_order`

| Параметр | Значение |
|----------|----------|
| **Файл** | `includes/admin/order/orderAjax.php` |
| **Функция** | `as_admin_recount_order_callback` |
| **Nonce** | `argon_shop_order_nonce` |
| **JS** | `window.recountOrderPrices` (`admin/modules/orders.js`) |
| **Принимает** | `postID`, `orderProducts[ID] = amount` |
| **Возвращает** | JSON: `{ actualPrices, weights, totalPrice, totalWeight }` |
| **Права** | `manage_options` |

---

## Соглашения

1. Все AJAX-обработчики фронтенда регистрируются **дважды**:
   ```php
   add_action('wp_ajax_<action>', 'callback');
   add_action('wp_ajax_nopriv_<action>', 'callback');
   ```

2. Все AJAX-обработчики админки — **только** `wp_ajax_<action>`.

3. Ответ — JSON через `wp_json_encode`. **Никакого `echo` до этого.**

4. Завершение — `wp_die()`. Без него WordPress допишет `0` в конец ответа.

5. Проверка nonce — **первой строкой**:
   ```php
   check_ajax_referer('argon_shop_nonce', 'nonce');
   ```

6. Проверка прав — **сразу после nonce**:
   ```php
   current_user_can('manage_options');
   // или
   $userID === get_current_user_id();
   ```



