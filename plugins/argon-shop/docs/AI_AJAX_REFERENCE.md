# AI_AJAX_REFERENCE.md — Справочник AJAX-экшенов плагина Argon Shop

> Полный список AJAX-экшенов, зарегистрированных плагином `argon-shop`.
> Для каждого экшена указаны: файл-обработчик, ожидаемые параметры,
> формат ответа и JS-функция, которая его вызывает.

## 1. Общие сведения

- **Точка входа:** `admin-ajax.php` (URL передаётся в JS через `myajax.url`).
- **Регистрация:** `add_action('wp_ajax_{action}', 'callback')` и `add_action('wp_ajax_nopriv_{action}', 'callback')`.
- **Формат ответа:** в большинстве случаев — `echo` + `wp_die()` (JSON, HTML или plain text).
- **Nonce:** в легаси-версии **отсутствует** — это известная проблема безопасности.

## 2. Сводная таблица экшенов

| № | Экшен (action) | Файл-обработчик | Параметры (POST) | Ответ | JS-вызов |
|---|----------------|-----------------|------------------|-------|----------|
| 1 | `addProducts_shoppingCart` | `includes/interface/shoppingCart/shoppingCart.php` | `productID` (int), `amountProduct` (int) | JSON: `{amountProduct, productAmountCart, totalPrice}` | `shoppingCart.php` → `addProducts()` |
| 2 | `amountProducts_shoppingCart` | `includes/interface/shoppingCart/shoppingCart.php` | `productID` (int), `productAmount` (int), `productPrice` (float) | JSON: `{weight, actualPrices, productsShoppingCart}` | `shoppingCart.php` → `cartAmountProductsAjax()` |
| 3 | `deleteProducts_shoppingCart` | `includes/interface/shoppingCart/shoppingCart.php` | `productID` (int) | JSON: `actualPrices` или `false` | `shoppingCart.php` → `deleteProducts()` |
| 4 | `deleteAllProducts_shoppingCart` | `includes/interface/shoppingCart/shoppingCart.php` | — | (пусто, cookie очищается) | `shoppingCart.php` → `deleteAllProductsShoppingCart()` |
| 5 | `wholesalePrice_shoppingCart` | `includes/interface/shoppingCart/shoppingCart.php` | `type` (string, `"get"`), `productID` (int), `amountProduct` (float) | Число (цена) или `"none"` | `shoppingCart.php` → `getWholesalePrice()` |
| 6 | `submitCart_shoppingCart` | `includes/interface/shoppingCart/shoppingCart.php` | `typeForm` (string), `required` (JSON), `productsCart` (JSON), `shipping` (string), `payment` (string), `emailUser`, `cartReg`, `loginUser`, `nameUser`, `messageUser`, `fileCart[]` (files), + все поля из `settingShop` | JSON: `{type: 'success'|'error', typeForm, message}` | `cart.js` → `submitCart()` |
| 7 | `sortingCatalog` | `includes/interface/catalog/sort.php` | `typeSort` (string), `howSorting` (string), `catId` (int), `pageNum` (int) | HTML (список товаров) | `sort.js` → `sortingCatalog()` |
| 8 | `getAjaxSearchResult` | `includes/interface/search/searchAjax.php` | `searchStr` (string), `dataAjax` (JSON) | HTML (результаты поиска) | `site.js` → `getAjaxSearchResult()` |
| 9 | `cabinetEditEmailPassword` | `includes/interface/cabinet/myCabinet.php` | `userID` (int), `email`, `oldEmail`, `pass`, `newPass`, `newPassConfirm` | JSON: `{edited: {...}, errors: {...}}` | `cabinet.js` → `cabinetEditEmailPassword()` |
| 10 | `cabinetAccountSave` | `includes/interface/cabinet/myCabinet.php` | `userID` (int), `forBack` (JSON) | Строка (сообщение) | `cabinet.js` → `accountSave()` |
| 11 | `ordersFiltersStatus` | `includes/interface/cabinet/userOrders/ordersAjax.php` | `userID` (int), `status` (string, через запятую) | HTML (таблица заказов) | `cabinet.js` → `ordersFiltersStatus()` |
| 12 | `addCookieHistory_history` | `includes/interface/history.php` | `productID` (int) | (пусто, cookie обновляется) | `product.js` → `addCookieHistory()` |
| 13 | `update_characteristics` | `includes/admin/fieldsProduct.php` | `activeCategoryId` (array), `characteristicsTextField` (array), `characteristicsCheckboxField` (array) | HTML (обновлённые поля) | `admin-script.js` → `updateCharacteristics()` |
| 14 | `as_searchSuitableProduct_order` | `includes/admin/order/orderAjax.php` | `searchStr` (string), `addedProduct` (array) | HTML (список товаров) | `orders.js` → `searchSuitableProduct()` |
| 15 | `as_addProduct_order` | `includes/admin/order/orderAjax.php` | `productID` (int) | HTML (таблица товара) | `orders.js` → `addProduct()` |
| 16 | `as_addFields_order` | `includes/admin/order/orderAjax.php` | `type` (string), `fieldsInStock` (array) | HTML (поля формы) | `orders.js` → `addFields()` |

## 3. Детальное описание ключевых экшенов

### 3.1. `addProducts_shoppingCart`

**Назначение:** добавить товар в корзину (cookie `productsShoppingCart`).

**Параметры:**
- `productID` — ID товара (`int`).
- `amountProduct` — количество (`int`).

**Логика:**
1. `$productID = (int)$_POST['productID'];`
2. `$productPrice = get_post_meta($productID, '_price', true);` — приведение к `float`.
3. Формирование `$data = ['amountProduct' => (int)$_POST['amountProduct'], 'price' => $productPrice]`.
4. Чтение cookie `productsShoppingCart` через `getCookie()`.
5. Если товар уже есть — суммирование количества.
6. `setcookie('productsShoppingCart', wp_json_encode($productsShoppingCart), time()+1209600, COOKIEPATH, COOKIE_DOMAIN);`
7. `getDataCart($productsShoppingCart)` — пересчёт.
8. **Ответ:** `{"amountProduct": N, "productAmountCart": M, "totalPrice": P}`.

**JS-вызов:**
```javascript
function addProducts(productID, amountProduct, type, thisButton) {
    var data = { action: 'addProducts_shoppingCart', productID: productID, amountProduct: amountProduct };
    $.ajax({ url: myajax.url, data: data, type: 'POST',
        success: function(response) { /* обновление DOM */ }
    });
}
```

### 3.2. `submitCart_shoppingCart`

**Назначение:** оформление заказа (создание `shoporder`).

**Параметры:**
- `typeForm` — тип формы: `quick`, `person`, `legalPerson`.
- `required` — JSON-объект с обязательными полями.
- `productsCart` — JSON-объект `{productID: {amountProduct}}`.
- `shipping` — `selfExport` или `shippingToAddress`.
- `payment` — `paimentUponReceipt`.
- `emailUser`, `loginUser`, `nameUser`, `messageUser` — поля форм.
- `cartReg` — флаг регистрации.
- `fileCart[]` — загруженные файлы (до 3 шт., до 5 МБ каждый).

**Логика:**
1. `get_valueFieldsCart($typeForm, $_POST)` — валидация и сборка полей.
2. Проверка совпадения `productsCart` из формы и cookie.
3. Обработка файлов: проверка форматов (doc, docx, xls, xlsx, pdf, jpg, jpeg, gif, bmp, png) и общего размера (≤ 15 МБ).
4. Если `cartReg` — `wp_create_user()`, `wp_signon()`, сохранение `accountData`.
5. `wp_insert_post(['post_type' => 'shoporder', ...])`.
6. `add_post_meta()`: `_productsCart`, `_fieldsCart`, `_number`, `_productAmountCart`, `_totalPrice`, `_shipping`, `_payment`.
7. `wp_set_object_terms($post_id, 'neworder', 'statusorders')`.
8. `mail()` — два письма (админу и пользователю).
9. `update_option('counterOrders', $counterOrders + 1)`.
10. **Ответ:** `{type: 'success', typeForm, message}` или `{type: 'error', message}`.

### 3.3. `sortingCatalog`

**Назначение:** AJAX-сортировка каталога.

**Параметры:**
- `typeSort` — `date`, `name`, `price`.
- `howSorting` — `ASC`, `DESC`.
- `catId` — ID категории.
- `pageNum` — номер страницы.

**Логика:**
1. Санитизация: `sanitize_text_field` для `typeSort`, `is_numeric` для `catId` и `pageNum`.
2. Сохранение в cookie `AS_CatalogSorting` (JSON `{typeSort, howSort}`).
3. `get_sort_products($typeSort, $howSorting, $catId, $pageNum)` — `WP_Query`.
4. `view_products_list($productsList)` — рендер HTML.
5. **Ответ:** HTML-список товаров.

### 3.4. `getAjaxSearchResult`

**Назначение:** AJAX-поиск по товарам, категориям и записям.

**Параметры:**
- `searchStr` — строка поиска.
- `dataAjax` — JSON-объект с настройками:
  ```json
  {
    "productResult": true,
    "catalogResult": true,
    "postResult": [
      {"name": "Акции и статьи", "class": "resultEntry", "category": [36, 37]}
    ]
  }
  ```

**Логика:**
1. `queryManagerSearchResult($searchStr, $searchParameters)`:
   - `cleanParameters()` — санитизация.
   - `queryAllocatorSearchResult('product' | 'category' | 'post', ...)`:
     - `queryProductSearchResult()` — поиск по `_article` (если цифры) и `s`.
     - `queryTaxonomySearchResult()` — `get_terms('catalog', ['search' => ...])`.
     - `queryPostSearchResult()` — `WP_Query` по `post_type='post'` с `tax_query`.
   - `prepCatalog()`, `prepPost()`, `prepProduct()` — подготовка.
   - Если пусто и не цифры — `fixKeyboardlayout()` (латиница → кириллица).
2. `showResult()` → `generateResultHTML()`.
3. **Ответ:** HTML-блоки с результатами.

### 3.5. `cabinetEditEmailPassword`

**Назначение:** смена email и/или пароля пользователя.

**Параметры:**
- `userID` (int)
- `email` — новый email.
- `oldEmail` — старый email.
- `pass` — текущий пароль.
- `newPass` — новый пароль.
- `newPassConfirm` — подтверждение.

**Логика:**
1. Валидация:
   - `wp_check_password($pass, ...)`;
   - `strlen($newPass) >= 6`;
   - `$newPass === $newPassConfirm`;
   - `email_exists($email)`.
2. При успехе: `wp_update_user()`, `wp_set_password()`, `wp_signon()`.
3. **Ответ:** `{"edited": {"newEmail": "...", "newPass": "..."}, "errors": {"email": "...", "pass": "..."}}`.

### 3.6. `update_characteristics` (админка)

**Назначение:** перерисовка полей характеристик товара при смене категории.

**Параметры:**
- `activeCategoryId` — массив ID выбранных категорий каталога.
- `characteristicsTextField` — значения текстовых полей.
- `characteristicsCheckboxField` — значения чекбоксов.

**Логика:**
1. `characteristics_metabox(get_post($postIdUrl), "", $activeCategoryId, ...)`.
2. **Ответ:** HTML-список полей.

**JS-вызов:** `admin-script.js` → `updateCharacteristics()`.

## 4. Особенности и замечания

- **Отсутствие nonce:** все экшены уязвимы к CSRF. Рекомендуется добавить `check_ajax_referer`.
- **`wp_die()` без кода:** по умолчанию возвращает `0`, что не всегда корректно.
- **`mail()` вместо `wp_mail()`:** не учитывает SMTP и хуки.
- **JSON-ответы:** некоторые экшены возвращают JSON через `echo json_encode(...)` + `wp_die()`, другие — plain text.
- **JS-сторона:** все вызовы идут через `myajax.url` (локализовано в `argon-shop.php`).
