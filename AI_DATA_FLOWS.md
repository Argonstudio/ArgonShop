# AI_DATA_FLOWS.md — Сценарии данных

> Продолжение [AI.md](AI.md). Здесь — 7 ключевых сценариев: как данные
> ходят между фронтендом, PHP и БД.
>
> **Формат:** триггер → JS → AJAX → PHP → ответ → JS → DOM.

---

## Сценарий 1. Добавление товара в корзину

**Триггер:** клик по кнопке «В корзину» на странице товара или в карточке.

### JS

```javascript
// modules/add-to-cart.js
addProductToCart(productId, amount);
```

1. `arsApi.postJson('addProducts_shoppingCart', { productID, amountProduct })`.
2. При успехе — `arsCartWidget.update({ productAmountCart, totalPrice })`
   обновляет виджет в шапке.
3. Дальше — колбэки вызывающего модуля:
   - `product.js`: показать `#howManyProducts`, скрыть `#addProductError`.
   - `card-product.js`: скрыть `.block-cardProductBascet`, показать `.card-alreadyAdded`.
4. «Купить» — переход на `config.cartUrl`.

### PHP

**Файл:** `shoppingCart.php` → `addProducts_shoppingCart_callback`.

```php
check_ajax_referer('argon_shop_nonce', 'nonce');

$productsShoppingCart = getCookie('productsShoppingCart');

if (isset($productsShoppingCart[$productID])) {
    $productsShoppingCart[$productID]['amountProduct'] += $amountProduct;
}

setcookie('productsShoppingCart', wp_json_encode($productsShoppingCart), time() + 1209600, COOKIEPATH, COOKIE_DOMAIN);

wp_send_json([
    'amountProduct'     => $amountProduct,
    'productAmountCart' => $productAmountCart,
    'totalPrice'        => $totalPrice,
]);
```

### DOM-эффекты

- Виджет корзины: `.viewBlock-amountProducts span`, `.viewBlock-priceProducts`.

---

## Сценарий 2. Изменение количества в корзине

**Триггер:** ввод в `.shoppingCartAmountProduct` или клик по `.plusProduct` / `.minusProduct`.

### JS

**Файл:** `modules/cart.js` → `#bindAmountChange` и `#bindPlusMinus`.

```javascript
// Плюс/минус меняют input.value и вручную диспатчат input,
// чтобы сработал один обработчик.
input.dispatchEvent(new Event('input', { bubbles: true }));

// #requestAmountChange(productId, amount) через AbortController
// (отмена предыдущего запроса).
const controller = new AbortController();

fetch(url, {
    method: 'POST',
    body: formData,
    signal: controller.signal,
});
```

> **Примечание:** `fetch` используется напрямую (не через `arsApi`),
> потому что нужен `signal`.

### PHP

**Файл:** `shoppingCart.php` → `amountProducts_shoppingCart_callback`.

1. Валидирует `productAmount > 0`.
2. Обновляет куку.
3. Считает вес товара и `actualPrices` для всей корзины.
4. Возвращает JSON:

```json
{
    "weight": 12.5,
    "actualPrices": { "12": 500, "15": 700 },
    "productsShoppingCart": { "12": { "amountProduct": 2, "price": 500 } }
}
```

### JS-обработка ответа

```javascript
// #updateProductWeight обновляет вес товара.
// #recountCart(actualPrices) пересчитывает все цены, сумму и вес.
// #updateCartTotals пишет в .cartTotalPrice span и .cartTotalWeight span.
```

### DOM-эффекты

- Цены товаров, итоговая сумма, итоговый вес.
- Скрытие/показ `.cartTotalWeight`.

---

## Сценарий 3. Удаление товара и очистка корзины

**Триггер:** клик по `.deleteProduct` или `.deleteAllProducts`.

### JS

```javascript
// .deleteProduct → arsApi.post('deleteProducts_shoppingCart', { productID })
arsApi.post('deleteProducts_shoppingCart', { productID })
    .then(text => {
        if (text === 'false' || text === '0') {
            // Корзина пуста
            block.remove();
            document.getElementById('shoppingCart').innerHTML = 'Корзина пуста';
        } else {
            const data = JSON.parse(text);
            parent.remove();
            recountCart(data.actualPrices);
        }
    });

// .deleteAllProducts → arsApi.post('deleteAllProducts_shoppingCart')
// без nonce (в config.skipNonceActions)
arsApi.post('deleteAllProducts_shoppingCart');
```

### PHP

```php
// deleteProducts_shoppingCart_callback
unset($productsShoppingCart[$productID]);
setcookie('productsShoppingCart', wp_json_encode($productsShoppingCart), time() + 1209600, COOKIEPATH, COOKIE_DOMAIN);
// Возвращает JSON актуальных цен или 'false'.

// deleteAllProducts_shoppingCart_callback
setcookie('productsShoppingCart', '', time() - 1209600, COOKIEPATH, COOKIE_DOMAIN);
// Ничего не возвращает.
```

---

## Сценарий 4. Оформление заказа

**Триггер:** submit формы `.form-cart`.

### JS

**Файл:** `modules/cart.js` → `#bindFormSubmit` → `#submitCart`.

```javascript
event.preventDefault();

const formData = new FormData(form);
formData.append('typeForm', form.dataset.type);
formData.append('productsCart', JSON.stringify(productsCart));
formData.append('required', JSON.stringify(required));

arsApi.postFormData('submitCart_shoppingCart', formData);
```

### PHP

**Файл:** `shoppingCart.php` → `submitCart_shoppingCart_callback`.

1. `check_ajax_referer` и `get_valueFieldsCart` — собирает поля формы,
   валидирует обязательные.
2. Проверка соответствия товаров формы и куки — защита от подмены.
3. Расчёт `actualPriceAllProducts`, `totalPrice`, `productAmountCart`.
4. Обработка файлов:
   ```php
   move_uploaded_file($_FILES['fileCart']['tmp_name'][$i], $uploadDir . $filename);
   // Проверка размера (< 15 МБ) и расширений
   // (doc, docx, xls, xlsx, jpg, jpeg, gif, bmp, png, pdf).
   ```
5. Опциональная регистрация: `wp_create_user`, `wp_signon`.
6. `wp_insert_post` с `post_type = 'shoporder'`.
7. `add_post_meta` с:
   ```php
   _productsCart
   _fieldsCart
   _number
   _productAmountCart
   _totalPrice
   _shipping
   _payment
   ```
8. `wp_set_object_terms($post_id, 'neworder', 'statusorders')`.
9. `emailTextsGenerator()` — HTML для писем.
10. `wp_mail` администратору (с вложениями) и покупателю.
11. Удаление временных файлов после отправки.
12. `update_option('counterOrders', $actuallyCounterOrder)`.
13. Очистка куки `productsShoppingCart` (`setcookie` с истёкшим сроком).

### Ответ

```json
{
    "type": "success",
    "typeForm": "person",
    "message": "Заказ успешно оформлен"
}
```

### JS-обработка

```javascript
if (data.type === 'error') {
    showFormError(data.message);
}

if (data.type === 'success') {
    hideFormError();
    document.getElementById('shoppingCart').innerHTML = data.message;
    arsCartWidget.clear();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
```

---

## Сценарий 5. AJAX-поиск

**Триггер:** ввод в `.asInputSearchForm` (с `data-ajax`).

### JS

**Файл:** `modules/search.js`.

```javascript
// #onInput — сброс предыдущего debounce, проверка длины ≥ 2.
// Через 300 мс — #requestSearch.
let searchToken = Symbol('search');

arsApi.post('getAjaxSearchResult', { dataAjax, searchStr })
    .then(html => {
        if (currentToken !== searchToken) return; // Игнорируем устаревший ответ
        resultBlock.innerHTML = html;
        resultBlock.style.display = 'block';
    });
```

> **Токены-Symbol** защищают от гонок: ответ устаревшего запроса игнорируется.

### PHP

**Файл:** `searchAjax.php` → `getAjaxSearchResult_callback`.

```php
check_ajax_referer('argon_shop_nonce', 'nonce');

$searchResult = queryManagerSearchResult($searchStr, $searchParameters);

// Логика ранжирования:
// - точное совпадение      → 1000 очков
// - начало title           → 500
// - слово в title          → 10
// - артикул                → 5

if (empty($searchResult) && !ctype_digit($searchStr)) {
    // Исправление раскладки: ghjlern → продукт
    $searchStr = fixKeyboardlayout($searchStr);
    $searchResult = queryManagerSearchResult($searchStr, $searchParameters);
}

showResult($searchResult); // HTML через generateResultHTML
```

### Синхронизация с GET-поиском

Та же логика ранжирования реализована в `searchGetFilters.php` через хуки:

```php
posts_search
posts_join
posts_orderby
```

> ⚠️ **Важно:** правки в одной ветке поиска нужно синхронно вносить и в другую.

---

## Сценарий 6. Личный кабинет — смена email / пароля

**Триггер:** клик по `#editEmailPasswordButton`.

### JS

**Файл:** `modules/cabinet.js`.

```javascript
const payload = { userID, email, oldEmail, pass, newPass, newPassConfirm };

arsApi.postJson('cabinetEditEmailPassword', payload);
```

### PHP

**Файл:** `myCabinet.php` → `cabinetEditEmailPassword_callback`.

```php
check_ajax_referer('argon_shop_nonce', 'nonce');

// Проверка: userID === get_current_user_id().
if ((int)$_POST['userID'] !== get_current_user_id()) {
    wp_send_json_error(['message' => 'Forbidden'], 403);
}

// Валидация:
// - is_email
// - email_exists
// - длина пароля ≥ 6
// - wp_check_password($pass, ...)

// Обновление email
wp_update_user(['ID' => $userID, 'user_email' => $email]);

// Смена пароля
wp_set_password($newPass, $userID);
wp_signon($creds, false);
// Сессия и nonce инвалидируются — возвращаем newNonce.
```

### Ответ

```json
{
    "edited": { "newEmail": "user@example.com", "newPass": true },
    "errors": {},
    "newNonce": "a1b2c3d4e5"
}
```

### JS-обработка

```javascript
// #showErrors — раскладывает ошибки по .editEmailError span,
// .editPassError span и т.д.

// edited.newEmail — обновляет data-oldemail и очищает поля паролей.
// edited.newPass — редирект через window.location.reload() через 2 секунды
// (сессия новая).
```

### Перехватчик nonce

```javascript
// core/nonce.js слушает XMLHttpRequest.load и обновляет config.nonce
// при получении newNonce.
// Дополнительно arsApi проверяет ответ в tryApplyNonceFromText.
```

---

## Сценарий 7. Сортировка каталога

**Триггер:** клик по `.as-sort[name]` в `.sortParameters`.

### JS

**Файл:** `modules/sort.js`.

```javascript
const catId   = sorting.closest('.sortParameters').dataset.catId;
const pageNum = sorting.closest('.sortParameters').dataset.page;

// howSorting = 'DESC', если на кнопке sort-min, иначе 'ASC'.
const howSorting = sorting.classList.contains('sort-min') ? 'DESC' : 'ASC';

arsApi.post('sortingCatalog', { typeSort, howSorting, catId, pageNum });

// #showResult — сброс подсветки, установка класса,
// productList.innerHTML = html.
```

### PHP

**Файл:** `sort.php` → `sortingCatalog_callback`.

1. Сохраняет сортировку в куку `AS_CatalogSorting` (JSON).
2. `get_sort_products($typeSort, $howSorting, $catId, $pageNum)` —
   `WP_Query` с `tax_query` по `catalog` и сортировкой:
   - `date` — по дате;
   - `title` — по названию;
   - `meta_value_num` для `_price` — по цене.
3. `view_products_list($productsList)` выводит HTML карточек.

### DOM-эффекты

- `.catalogProductList` перезаписывается целиком.
- `.as-error-sort` скрывается.

---

## Вспомогательное: несохранённые данные

В проекте **не используется** `sessionStorage` и `localStorage`.
Все данные магазина — только в куках:

| Cookie | Назначение |
|--------|-----------|
| `productsShoppingCart` | Корзина |
| `AS_History` | История просмотров |
| `AS_CatalogSorting` | Сортировка |
| `WP-LastViewedPosts` | Из форка; используется только на одной странице |

---

