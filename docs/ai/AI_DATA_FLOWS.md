# AI_DATA_FLOWS.md — Data scenarios

> Continuation of [AI.md](../../AI.md). Seven key scenarios: how data
> travels between the front end, PHP and the database.
>
> **Format:** trigger → JS → AJAX → PHP → response → JS → DOM.

---

## Scenario 1. Adding a product to the cart

**Trigger:** click on the "Add to cart" button on the product page or in a product card.

### JS

```javascript
// modules/add-to-cart.js
addProductToCart(productId, amount);
```

1. `arsApi.postJson('addProducts_shoppingCart', { productID, amountProduct })`.
2. On success — `arsCartWidget.update({ productAmountCart, totalPrice })`
   updates the header widget.
3. Then the caller's callbacks run:
   - `product.js`: show `#howManyProducts`, hide `#addProductError`.
   - `card-product.js`: hide `.block-cardProductBascet`, show `.card-alreadyAdded`.
4. "Buy" — navigates to `config.cartUrl`.

### PHP

**File:** `shoppingCart/cart-ajax.php` → `as_addProducts_shoppingCart_callback`.

> `as_getCookie('productsShoppingCart')` returns a cart that has already passed
> `as_sanitize_cart()`: only published products, quantity > 0,
> **the price always comes from `_price` in the database** (the cookie price is ignored).

```php
check_ajax_referer('argon_shop_nonce', 'nonce');

$productsShoppingCart = as_getCookie('productsShoppingCart');

if (isset($productsShoppingCart[$productID])) {
    $productsShoppingCart[$productID]['amountProduct'] += $amountProduct;
}

setcookie('productsShoppingCart', wp_json_encode($productsShoppingCart), time() + 1209600, COOKIEPATH, COOKIE_DOMAIN);

$dataCart = as_getDataCart($productsShoppingCart);

// amountProduct — the new total quantity of this product in the cart
echo wp_json_encode([
    'amountProduct'     => $data['amountProduct'],
    'productAmountCart' => $dataCart['productAmountCart'],
    'totalPrice'        => $dataCart['totalPrice'],
]);
wp_die();
```

### DOM effects

- Cart widget: `.viewBlock-amountProducts span`, `.viewBlock-priceProducts`.

---

## Scenario 2. Changing a quantity in the cart

**Trigger:** typing in `.shoppingCartAmountProduct` or clicking `.plusProduct` / `.minusProduct`.

### JS

**File:** `modules/cart.js` → `#bindAmountChange` and `#bindPlusMinus`.

```javascript
// Plus/minus change input.value and dispatch "input" manually
// so that a single handler runs.
input.dispatchEvent(new Event('input', { bubbles: true }));

// #requestAmountChange(productId, amount) uses an AbortController
// (cancels the previous request).
const controller = new AbortController();

fetch(url, {
    method: 'POST',
    body: formData,
    signal: controller.signal,
});
```

> **Note:** `fetch` is used directly (not via `arsApi`)
> because `signal` is needed.

### PHP

**File:** `shoppingCart/cart-ajax.php` → `as_amountProducts_shoppingCart_callback`.

1. Validates `productAmount > 0`.
2. Updates the cookie.
3. Calculates the product weight and `actualPrices` for the whole cart.
4. Returns JSON:

```json
{
    "weight": 12.5,
    "actualPrices": { "12": 500, "15": 700 },
    "productsShoppingCart": { "12": { "amountProduct": 2, "price": 500 } }
}
```

### JS response handling

```javascript
// #updateProductWeight updates the product weight.
// #recountCart(actualPrices) recalculates all prices, the total and the weight.
// #updateCartTotals writes to .cartTotalPrice span and .cartTotalWeight span.
```

### DOM effects

- Product prices, cart total, total weight.
- Hiding/showing `.cartTotalWeight`.

---

## Scenario 3. Removing a product and clearing the cart

**Trigger:** click on `.deleteProduct` or `.deleteAllProducts`.

### JS

```javascript
// .deleteProduct → arsApi.post('deleteProducts_shoppingCart', { productID })
arsApi.post('deleteProducts_shoppingCart', { productID })
    .then(text => {
        if (text === 'false' || text === '0') {
            // Cart is empty
            block.remove();
            document.getElementById('shoppingCart').textContent = t('cartEmpty');
        } else {
            // The response IS the price map { productID: price }, no wrapper key
            const actualPrices = JSON.parse(text);
            parent.remove();
            recountCart(actualPrices);
        }
    });

// .deleteAllProducts → arsApi.post('deleteAllProducts_shoppingCart')
// since 1.1.0 — with a nonce, like every other action
arsApi.post('deleteAllProducts_shoppingCart');
```

> User-visible text comes from `t()` (`core/config.js`), never from string literals.

### PHP

```php
// as_deleteProducts_shoppingCart_callback
unset($productsShoppingCart[$productID]);
setcookie('productsShoppingCart', wp_json_encode($productsShoppingCart), time() + 1209600, COOKIEPATH, COOKIE_DOMAIN);
// echo wp_json_encode($actualPriceAllProducts) — the price map itself, or 'false' if the cart is empty.

// as_deleteAllProducts_shoppingCart_callback
setcookie('productsShoppingCart', '', time() - 1209600, COOKIEPATH, COOKIE_DOMAIN);
// Returns nothing.
```

---

## Scenario 4. Checkout

**Trigger:** submit of the `.form-cart` form.

### JS

**File:** `modules/cart.js` → `#bindFormSubmit` → `#submitCart`.

```javascript
event.preventDefault();

const formData = new FormData(form);
formData.append('typeForm', form.dataset.type);
formData.append('productsCart', JSON.stringify(productsCart));
formData.append('required', JSON.stringify(required)); // ignored by the server (since 1.1.0)

arsApi.postFormData('submitCart_shoppingCart', formData);
```

### PHP

**File:** `shoppingCart/checkout.php` → `as_submitCart_shoppingCart_callback`
(modules: `checkout-fields.php`, `checkout-files.php`, `checkout-registration.php`, `checkout-emails.php`).

1. `check_ajax_referer`; `typeForm` — only `quick` / `person` / `legalPerson`.
2. `as_get_valueFieldsCart` collects the form fields. **Required fields are defined by the server**
   (`as_get_required_fields()`, filter `argon_shop_required_fields`); `$_POST['required']` is ignored.
   Email is required when registering.
3. The cart comes from the cookie via `as_sanitize_cart()` (prices from the database). Empty cart → error.
   Quantities in the cookie must match `productsCart` from the page.
4. Calculation of `actualPriceAllProducts`, `totalPrice`, `productAmountCart`.
   Delivery/payment — only codes from `as_get_shipping_methods()` / `as_get_payment_methods()`.
5. Filter `argon_shop_checkout_errors` — extra checks from other modules.
6. Files — `as_checkout_handle_files()`: **all checks first** (up to 3 files, ≤ 5 MB each,
   ≤ 15 MB total, extension allow-list, real type via `wp_check_filetype_and_ext`),
   then saving to the protected `uploads/argon-shop-private/<random folder>/`.
7. Optional registration — `as_checkout_register_user()`: the random password is never sent,
   the customer gets a set-password link, login via `wp_set_auth_cookie`.
8. `as_checkout_create_order()`: `wp_insert_post` (`shoporder`, filter `argon_shop_order_post_data`),
   meta fields, status `neworder`, `update_option('counterOrders')`.
9. `do_action('argon_shop_order_created', $order_id, $order)`.
10. `as_checkout_send_emails()` — to the administrator and the customer (filter `argon_shop_email`).
    Prices in emails use the store currency (`as_get_currency_symbol()` / `as_price_html()`).
11. `as_checkout_cleanup_files()` — removes the order files.
12. Response (filter `argon_shop_checkout_response`), cart cookie cleared.

Order meta fields:
   ```php
   _productsCart
   _fieldsCart
   _number
   _productAmountCart
   _totalPrice
   _shipping
   _payment
   ```

### Response

```json
{
    "type": "success",
    "typeForm": "person",
    "message": "Order placed successfully"
}
```

(`message` is a translated string from PHP.)

### JS handling

```javascript
if (data.type === 'error') {
    showFormError(data.message);
}

if (data.type === 'success') {
    hideFormError();
    document.getElementById('shoppingCart').innerHTML = data.message;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
```

> The form is found by `.form-cart${data.typeForm}` (`.form-cartquick`, `.form-cartperson`, `.form-cartlegalPerson`).
> The header cart widget is **not** cleared by JS after the order — the server clears the cookie, the widget shows 0 after the next page load.

---

## Scenario 5. AJAX search

**Trigger:** typing in `.asInputSearchForm` (with `data-ajax`).

### JS

**File:** `modules/search.js`.

```javascript
// #onInput — resets the previous debounce, checks length ≥ 2.
// After 300 ms — #requestSearch.
let searchToken = Symbol('search');

arsApi.post('getAjaxSearchResult', { dataAjax, searchStr })
    .then(html => {
        if (currentToken !== searchToken) return; // Ignore a stale response
        resultBlock.innerHTML = html;
        resultBlock.style.display = 'block';
    });
```

> **Symbol tokens** protect against races: a stale response is ignored.

### PHP

**File:** `searchAjax.php` → `as_getAjaxSearchResult_callback`.

```php
check_ajax_referer('argon_shop_nonce', 'nonce');

$searchResult = as_queryManagerSearchResult($searchStr, $searchParameters);

// Ranking (argon_search_ajax_orderby / as_search_custom_orderby, sum of points):
// - title = full phrase            → 1000
// - SKU (_article) = full phrase   → 800
// - title starts with the phrase   → 500
// - SKU starts with the phrase     → 400
// per query word:
// - title starts with the word     → 50
// - word at the start of a word    → 30
// - word anywhere in the title     → 10
// - word in the SKU                → 5
// ties: post_date DESC, ID DESC

if (empty($searchResult) && !ctype_digit($searchStr)) {
    // Wrong keyboard layout: variants depend on the store language
    // ru: ghjlern → продукт; en: ыскуц → screw; fr: chqise → chaise
    foreach (as_search_layout_variants($searchStr) as $searchStrFix) {
        $searchResult = as_queryManagerSearchResult($searchStrFix, $searchParameters);
        if ($searchResult) break;
    }
}

as_showResult($searchResult); // HTML via as_generateResultHTML
```

### Language rules (since 1.1.0)

`searchLanguage.php` defines the language (`as_search_language()` → `ru` / `en` / `fr`,
from the site locale), splits the query into words (`as_search_get_words()`: stop words,
elisions, plural endings for en/fr) and builds the SQL (`as_search_where_sql()`).
Russian behaviour is unchanged from 1.1.0.

### Sync with the GET search

The same ranking is implemented in `searchGetFilters.php` / `searchAjax.php` via hooks:

```php
posts_search
posts_join
posts_distinct
posts_orderby
```

> ⚠️ **Important:** changes in one search branch must be mirrored in the other.

---

## Scenario 6. Customer account — changing email / password

**Trigger:** click on `#editEmailPasswordButton`.

### JS

**File:** `modules/cabinet.js`.

```javascript
const payload = { userID, email, oldEmail, pass, newPass, newPassConfirm };

arsApi.postJson('cabinetEditEmailPassword', payload);
```

### PHP

**File:** `myCabinet.php` → `as_cabinetEditEmailPassword_callback`.

```php
check_ajax_referer('argon_shop_nonce', 'nonce');

// Check: userID === get_current_user_id().
$userID = isset($_POST['userID']) ? absint($_POST['userID']) : 0;
if (0 === $userID || $userID !== get_current_user_id()) {
    wp_die(__('Insufficient permissions', 'argon-shop')); // plain text, not JSON
}

// Validation:
// - is_email
// - email_exists
// - password length ≥ 6
// - wp_check_password($pass, ...)

// Email update
wp_update_user(['ID' => $userID, 'user_email' => $email]);

// Password change
wp_set_password($newPass, $userID);
wp_signon($creds, false);
// Session and nonce are invalidated — newNonce is returned.
```

### Response

```json
{
    "edited": {
        "newEmail": "Email changed",
        "newPass": "Password changed, you are logged in with the new password"
    },
    "errors": {},
    "newNonce": "a1b2c3d4e5"
}
```

All values in `edited` and `errors` are **translated messages**, not data (`errors` keys: `email`, `pass`, `newPass`, `newPassConfirm`). `newNonce` is present only after a password change.

### JS handling

```javascript
// #showErrors — distributes errors into .editEmailError span,
// .editPassError span, etc.

// edited.newEmail — updates data-oldemail with the entered email and shows the message.
// edited.newPass — window.location.reload() after 2 seconds
// (new session).
```

### Nonce interceptor

```javascript
// core/nonce.js listens to XMLHttpRequest.load and updates config.nonce
// when newNonce is received.
// arsApi also checks responses in tryApplyNonceFromText.
```

---

## Scenario 7. Catalog sorting

**Trigger:** click on `.as-sort[name]` inside `.sortParameters`.

### JS

**File:** `modules/sort.js`.

```javascript
const catId   = sorting.closest('.sortParameters').dataset.catId;
const pageNum = sorting.closest('.sortParameters').dataset.page;

// howSorting = 'DESC' if the button has sort-min, otherwise 'ASC'.
const howSorting = sorting.classList.contains('sort-min') ? 'DESC' : 'ASC';

arsApi.post('sortingCatalog', { typeSort, howSorting, catId, pageNum });

// #showResult(html, button, howSorting) — resets highlighting (sort-min / sort-max),
// sets the class, productList.innerHTML = html.
```

### PHP

**File:** `sort.php` → `as_sortingCatalog_callback`.

1. Saves the sorting in the `AS_CatalogSorting` cookie (JSON).
2. `as_get_sort_products($typeSort, $howSorting, $catId, $pageNum)` —
   `WP_Query` with a `tax_query` on `catalog` and ordering:
   - `typeSort = date` → `orderby => date`;
   - `typeSort = name` → `orderby => title`;
   - `typeSort = price` → `orderby => meta_value_num`, `meta_key => _price`.
3. `as_view_products_list($productsList)` renders the card HTML.

### DOM effects

- `.catalogProductList` is fully replaced.
- `.as-error-sort` is hidden.

---

## Auxiliary: client-side storage

The project does **not use** `sessionStorage` or `localStorage`.
All store data lives in cookies only:

| Cookie | Purpose |
|--------|---------|
| `productsShoppingCart` | Cart |
| `AS_History` | Recently viewed |
| `AS_CatalogSorting` | Sorting |
| `WP-LastViewedPosts` | **Not used.** Only read/written by `includes/interface/last_viewed_posts.php`, which `argon-shop.php` does not load (dead code); `uninstall.php` still removes its `zg_lwp_widget` option |

---
