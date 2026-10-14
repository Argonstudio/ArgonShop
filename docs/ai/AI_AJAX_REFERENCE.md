# AI_AJAX_REFERENCE.md — AJAX action reference

> Continuation of [AI.md](../../AI.md). Every AJAX endpoint: name, file, parameters,
> response, JS handler.

**Base URL:** `admin_url('admin-ajax.php')` — in JS: `config.url`
(from `arsWpAjax.url`).

**Nonce:** front end — `argon_shop_nonce`, admin — `argon_shop_order_nonce`.
Since 1.1.0 there are no exceptions: **every** action checks the nonce, `config.skipNonceActions` is empty.

**Text in responses:** HTML and messages returned by PHP are already translated (`__()`) and prices use the store currency (`as_price_html()`, since 1.1.0). JS adds its own text only via `t()`.

---

## Front end

### `addProducts_shoppingCart`

| Parameter | Value |
|----------|----------|
| **File** | `includes/interface/shoppingCart/cart-ajax.php` |
| **Function** | `as_addProducts_shoppingCart_callback` |
| **Nonce** | yes |
| **JS module** | `modules/add-to-cart.js` |
| **Accepts** | `productID` (int), `amountProduct` (int) |
| **Returns** | JSON: `{ amountProduct, productAmountCart, totalPrice }` |

### `amountProducts_shoppingCart`

| Parameter | Value |
|----------|----------|
| **File** | `includes/interface/shoppingCart/cart-ajax.php` |
| **Function** | `as_amountProducts_shoppingCart_callback` |
| **Nonce** | yes |
| **JS module** | `modules/cart.js` (via `fetch` with `AbortController`) |
| **Accepts** | `productID`, `productAmount` (float) |
| **Returns** | JSON: `{ weight, actualPrices, productsShoppingCart }` |

### `deleteProducts_shoppingCart`

| Parameter | Value |
|----------|----------|
| **File** | `includes/interface/shoppingCart/cart-ajax.php` |
| **Function** | `as_deleteProducts_shoppingCart_callback` |
| **Nonce** | yes |
| **JS module** | `modules/cart.js` |
| **Accepts** | `productID` |
| **Returns** | JSON map `{ productID: actualPrice }` (the map itself, no wrapper key) OR the string `'false'` when the cart became empty |

### `deleteAllProducts_shoppingCart`

| Parameter | Value |
|----------|----------|
| **File** | `includes/interface/shoppingCart/cart-ajax.php` |
| **Function** | `as_deleteAllProducts_shoppingCart_callback` |
| **Nonce** | yes (since 1.1.0) |
| **JS module** | `modules/cart.js` |
| **Accepts** | — |
| **Returns** | empty |

### `wholesalePrice_shoppingCart`

| Parameter | Value |
|----------|----------|
| **File** | `includes/interface/shoppingCart/cart-ajax.php` |
| **Function** | `as_wholesalePrice_shoppingCart_callback` |
| **Nonce** | yes |
| **JS module** | `modules/product.js` |
| **Accepts** | `type='get'`, `productID`, `amountProduct` |
| **Returns** | number (final price) OR the string `'none'` |

### `submitCart_shoppingCart`

| Parameter | Value |
|----------|----------|
| **File** | `includes/interface/shoppingCart/checkout.php` |
| **Function** | `as_submitCart_shoppingCart_callback` |
| **Nonce** | yes |
| **JS module** | `modules/cart.js` |
| **Accepts** | `FormData`: `typeForm` (`quick`/`person`/`legalPerson`), `productsCart`, form fields, `fileCart[]` (up to 3 files of 5 MB). The browser still sends `required`, but the server **ignores** it: required fields come from `as_get_required_fields()` |
| **Returns** | JSON: `{ type: 'success'\|'error', typeForm, message }` |

### `getAjaxSearchResult`

| Parameter | Value |
|----------|----------|
| **File** | `includes/interface/search/searchAjax.php` |
| **Function** | `as_getAjaxSearchResult_callback` |
| **Nonce** | yes |
| **JS module** | `modules/search.js` |
| **Accepts** | `dataAjax` (JSON string), `searchStr` |
| **Returns** | HTML of the result blocks (query normalized by the store language rules, `searchLanguage.php`) |

### `sortingCatalog`

| Parameter | Value |
|----------|----------|
| **File** | `includes/interface/catalog/sort.php` |
| **Function** | `as_sortingCatalog_callback` |
| **Nonce** | yes |
| **JS module** | `modules/sort.js` |
| **Accepts** | `typeSort` (`date`/`name`/`price`), `howSorting` (`ASC`/`DESC`), `catId`, `pageNum` |
| **Returns** | HTML of the product list |

### `cabinetEditEmailPassword`

| Parameter | Value |
|----------|----------|
| **File** | `includes/interface/cabinet/myCabinet.php` |
| **Function** | `as_cabinetEditEmailPassword_callback` |
| **Nonce** | yes |
| **JS module** | `modules/cabinet.js` |
| **Accepts** | `userID`, `email`, `oldEmail`, `pass`, `newPass`, `newPassConfirm` — passwords are checked for string type and only unslashed with `wp_unslash()`, never sanitized; `<`, `>`, `%xx`, quotes, backslashes and repeated spaces are preserved. A new password needs at least 6 UTF-8 characters; an empty new password means no change |
| **Returns** | JSON: `{ edited: { newEmail?, newPass? }, errors: { email?, pass?, newPass?, newPassConfirm? }, newNonce? }` — all `edited` / `errors` values are translated message strings. Foreign `userID` → `wp_die()` with "Insufficient permissions" (plain text, not JSON) |

### `cabinetAccountSave`

| Parameter | Value |
|----------|----------|
| **File** | `includes/interface/cabinet/myCabinet.php` |
| **Function** | `as_cabinetAccountSave_callback` |
| **Nonce** | yes |
| **JS module** | `modules/cabinet.js` |
| **Accepts** | `userID`, `forBack` (JSON string with the fields) |
| **Returns** | text (translated success or error message) |

### `ordersFiltersStatus`

| Parameter | Value |
|----------|----------|
| **File** | `includes/interface/cabinet/userOrders/ordersAjax.php` |
| **Function** | `as_ordersFiltersStatus_callback` |
| **Nonce** | yes (since 1.1.0) |
| **JS module** | `modules/orders.js` |
| **Accepts** | `userID`, `status` (comma-separated ID string) |
| **Returns** | HTML of the orders table |

### `addCookieHistory_history`

| Parameter | Value |
|----------|----------|
| **File** | `includes/interface/history.php` |
| **Function** | `as_addCookieHistory_history_callback` |
| **Nonce** | yes |
| **JS module** | `modules/history.js` |
| **Accepts** | `productID` |
| **Returns** | empty (saves to the `AS_History` cookie) |

---

## Admin

### `update_characteristics`

| Parameter | Value |
|----------|----------|
| **File** | `includes/admin/fieldsProduct.php` |
| **Function** | `as_updateCharacteristicsFunction` |
| **Nonce** | `argon_shop_order_nonce` |
| **JS** | inline in `as_updateCharacteristicsAjax` |
| **Accepts** | `activeCategoryId`, `characteristicsTextField`, `characteristicsCheckboxField` |
| **Returns** | HTML of the characteristics meta box |
| **Capability** | `manage_options` |

### `as_searchSuitableProduct_order`

| Parameter | Value |
|----------|----------|
| **File** | `includes/admin/order/orderAjax.php` |
| **Function** | `as_search_suitable_product_order_callback` |
| **Nonce** | `argon_shop_order_nonce` |
| **JS** | `window.searchSuitableProduct` + `admin/modules/orders.js` |
| **Accepts** | `searchStr`, `addedProduct` (array of IDs) |
| **Returns** | HTML of the search results |
| **Capability** | `manage_options` |

### `as_addProduct_order`

| Parameter | Value |
|----------|----------|
| **File** | `includes/admin/order/orderAjax.php` |
| **Function** | `as_add_product_order_callback` |
| **Nonce** | `argon_shop_order_nonce` |
| **JS** | `window.addProduct` |
| **Accepts** | `productID`, `postID` (order ID) |
| **Returns** | HTML of the product row |
| **Capability** | `manage_options` |

### `as_addFields_order`

| Parameter | Value |
|----------|----------|
| **File** | `includes/admin/order/orderAjax.php` |
| **Function** | `as_add_fields_order_callback` |
| **Nonce** | `argon_shop_order_nonce` |
| **JS** | `window.addFields` |
| **Accepts** | `type` (`person`/`legalPerson`), `fieldsInStock` |
| **Returns** | HTML of the fields |
| **Capability** | `manage_options` |

### `as_admin_recount_order`

| Parameter | Value |
|----------|----------|
| **File** | `includes/admin/order/orderAjax.php` |
| **Function** | `as_admin_recount_order_callback` |
| **Nonce** | `argon_shop_order_nonce` |
| **JS** | `window.recountOrderPrices` (`admin/modules/orders.js`) |
| **Accepts** | `postID`, `orderProducts[ID] = amount` |
| **Returns** | JSON: `{ actualPrices, weights, totalPrice, totalWeight }` |
| **Capability** | `manage_options` |

---

## Conventions

1. Every front-end AJAX handler is registered **twice**:
   ```php
   add_action('wp_ajax_<action>', 'callback');
   add_action('wp_ajax_nopriv_<action>', 'callback');
   ```

2. Every admin AJAX handler uses **only** `wp_ajax_<action>`.

3. Response — `echo wp_json_encode( $data )` for JSON, `echo` of escaped HTML/text for HTML responses (search, sorting, admin rows). The project does not use `wp_send_json()`. **No other output before the response.**

4. Finish with `wp_die()`. Without it WordPress appends `0` to the response.

5. Nonce check — **first line**:
   ```php
   check_ajax_referer('argon_shop_nonce', 'nonce');
   ```

6. Capability check — **right after the nonce**, and it must stop the request:
   ```php
   if ( ! current_user_can( 'manage_options' ) ) {
       wp_die( __( 'Insufficient permissions', 'argon-shop' ) );
   }
   // or, for the customer account
   if ( 0 === $userID || $userID !== get_current_user_id() ) {
       wp_die( __( 'Insufficient permissions', 'argon-shop' ) );
   }
   ```



