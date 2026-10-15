# AI_DATA_FLOWS.md — Сценарии работы с данными

> Продолжение [AI.md](../../AI.md). Семь ключевых сценариев: как данные
> проходят между фронтендом, PHP и базой данных.
>
> **Формат:** триггер → JS → AJAX → PHP → ответ → JS → DOM.

---

## Сценарий 1. Добавление товара в корзину

**Триггер:** клик по кнопке «В корзину» на странице товара или в карточке товара.

### JS

```javascript
// modules/add-to-cart.js
addProductToCart(productId, amount);
```

1. `arsApi.postJson('addProducts_shoppingCart', { productID, amountProduct })`.
2. При успехе — `arsCartWidget.update({ productAmountCart, totalPrice })`
   обновляет виджет в шапке.
3. Затем выполняются колбэки вызывающего кода:
   - `product.js`: показать `#howManyProducts`, скрыть `#addProductError`.
   - `card-product.js`: скрыть `.block-cardProductBascet`, показать `.card-alreadyAdded`.
4. «Купить» — переход на `config.cartUrl`.

### PHP

**Файл:** `shoppingCart/cart-ajax.php` → `as_addProducts_shoppingCart_callback`.

> `as_getCookie('productsShoppingCart')` возвращает корзину, уже прошедшую
> `as_sanitize_cart()`: только опубликованные товары, количество > 0,
> **цена всегда берётся из `_price` в базе данных** (цена из cookie игнорируется).

```php
check_ajax_referer('argon_shop_nonce', 'nonce');

$productsShoppingCart = as_getCookie('productsShoppingCart');

if (isset($productsShoppingCart[$productID])) {
    $productsShoppingCart[$productID]['amountProduct'] += $amountProduct;
}

setcookie('productsShoppingCart', wp_json_encode($productsShoppingCart), time() + 1209600, COOKIEPATH, COOKIE_DOMAIN);

$dataCart = as_getDataCart($productsShoppingCart);

// amountProduct — новое общее количество этого товара в корзине
echo wp_json_encode([
    'amountProduct'     => $data['amountProduct'],
    'productAmountCart' => $dataCart['productAmountCart'],
    'totalPrice'        => $dataCart['totalPrice'],
]);
wp_die();
```

### Изменения в DOM

- Виджет корзины: `.viewBlock-amountProducts span`, `.viewBlock-priceProducts`.

---

## Сценарий 2. Изменение количества в корзине

**Триггер:** ввод в `.shoppingCartAmountProduct` или клик по `.plusProduct` / `.minusProduct`.

### JS

**Файл:** `modules/cart.js` → `#bindAmountChange` и `#bindPlusMinus`.

```javascript
// Плюс/минус меняют input.value и вручную вызывают событие "input",
// чтобы срабатывал единый обработчик.
input.dispatchEvent(new Event('input', { bubbles: true }));

// #requestAmountChange(productId, amount) использует AbortController
// (отменяет предыдущий запрос).
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

**Файл:** `shoppingCart/cart-ajax.php` → `as_amountProducts_shoppingCart_callback`.

1. Проверяет `productAmount > 0`.
2. Обновляет cookie.
3. Считает вес товара и `actualPrices` для всей корзины.
4. Возвращает JSON:

```json
{
    "weight": 12.5,
    "actualPrices": { "12": 500, "15": 700 },
    "productsShoppingCart": { "12": { "amountProduct": 2, "price": 500 } }
}
```

### Обработка ответа в JS

```javascript
// #updateProductWeight обновляет вес товара.
// #recountCart(actualPrices) пересчитывает все цены, итог и вес.
// #updateCartTotals пишет в .cartTotalPrice span и .cartTotalWeight span.
```

### Изменения в DOM

- Цены товаров, итог корзины, общий вес.
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
            document.getElementById('shoppingCart').textContent = t('cartEmpty');
        } else {
            // Ответ — сама карта цен { productID: price }, без обёртки
            const actualPrices = JSON.parse(text);
            parent.remove();
            recountCart(actualPrices);
        }
    });

// .deleteAllProducts → arsApi.post('deleteAllProducts_shoppingCart')
// с 1.1.0 — с nonce, как и все остальные действия
arsApi.post('deleteAllProducts_shoppingCart');
```

> Видимый пользователю текст берётся из `t()` (`core/config.js`), никогда из строковых литералов.

### PHP

```php
// as_deleteProducts_shoppingCart_callback
unset($productsShoppingCart[$productID]);
setcookie('productsShoppingCart', wp_json_encode($productsShoppingCart), time() + 1209600, COOKIEPATH, COOKIE_DOMAIN);
// echo wp_json_encode($actualPriceAllProducts) — сама карта цен или 'false', если корзина опустела.

// as_deleteAllProducts_shoppingCart_callback
setcookie('productsShoppingCart', '', time() - 1209600, COOKIEPATH, COOKIE_DOMAIN);
// Ничего не возвращает.
```

---

## Сценарий 4. Оформление заказа

**Триггер:** отправка формы `.form-cart`.

### JS

**Файл:** `modules/cart.js` → `#bindFormSubmit` → `#submitCart`.

```javascript
event.preventDefault();

const formData = new FormData(form);
formData.append('typeForm', form.dataset.type);
formData.append('productsCart', JSON.stringify(productsCart));
formData.append('required', JSON.stringify(required)); // сервер игнорирует (с 1.1.0)

arsApi.postFormData('submitCart_shoppingCart', formData);
```

### PHP

**Файл:** `shoppingCart/checkout.php` → `as_submitCart_shoppingCart_callback`
(модули: `checkout-fields.php`, `checkout-files.php`, `checkout-registration.php`, `checkout-emails.php`).

1. `check_ajax_referer`; `typeForm` — только `quick` / `person` / `legalPerson`.
2. `as_get_valueFieldsCart` собирает поля формы. **Обязательные поля определяет сервер**
   (`as_get_required_fields()`, фильтр `argon_shop_required_fields`); `$_POST['required']` игнорируется.
   При регистрации email обязателен.
3. Корзина берётся из cookie через `as_sanitize_cart()` (цены из базы). Пустая корзина → ошибка.
   Количества в cookie должны совпадать с `productsCart` со страницы.
4. Расчёт `actualPriceAllProducts`, `totalPrice`, `productAmountCart`.
   Доставка/оплата — только коды из `as_get_shipping_methods()` / `as_get_payment_methods()`.
5. Фильтр `argon_shop_checkout_errors` — дополнительные проверки из других модулей.
6. Файлы — `as_checkout_handle_files()`: **сначала все проверки** (до 3 файлов, ≤ 5 МБ каждый,
   ≤ 15 МБ всего, белый список расширений, реальный тип через `wp_check_filetype_and_ext`),
   затем сохранение в защищённую `uploads/argon-shop-private/<случайная папка>/`.
7. Необязательная регистрация — `as_checkout_register_user()`: случайный пароль никогда не отправляется,
   покупатель получает ссылку для установки пароля, вход через `wp_set_auth_cookie`.
8. `as_checkout_create_order()`: `wp_insert_post` (`shoporder`, фильтр `argon_shop_order_post_data`),
   мета-поля, статус `neworder`, `update_option('counterOrders')`.
9. `do_action('argon_shop_order_created', $order_id, $order)`.
10. `as_checkout_send_emails()` — администратору и покупателю (фильтр `argon_shop_email`).
    Цены в письмах выводятся в валюте магазина (`as_get_currency_symbol()` / `as_price_html()`).
11. `as_checkout_cleanup_files()` — удаляет файлы заказа.
12. Ответ (фильтр `argon_shop_checkout_response`), cookie корзины очищается.

Мета-поля заказа:
   ```php
   _productsCart
   _fieldsCart
   _number
   _productAmountCart
   _totalPrice
   _shipping
   _payment
   ```

### Ответ

```json
{
    "type": "success",
    "typeForm": "person",
    "message": "Order placed successfully"
}
```

(`message` — переведённая строка из PHP.)

> Форма ищется по `.form-cart${data.typeForm}` (`.form-cartquick`, `.form-cartperson`, `.form-cartlegalPerson`).
> Виджет корзины в шапке JS после заказа **не** очищает — сервер удаляет cookie, виджет покажет 0 после следующей загрузки страницы.

### Обработка в JS

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

---

## Сценарий 5. AJAX-поиск

**Триггер:** ввод в `.asInputSearchForm` (с `data-ajax`).

### JS

**Файл:** `modules/search.js`.

```javascript
// #onInput — сбрасывает предыдущий debounce, проверяет длину ≥ 2.
// Через 300 мс — #requestSearch.
let searchToken = Symbol('search');

arsApi.post('getAjaxSearchResult', { dataAjax, searchStr })
    .then(html => {
        if (currentToken !== searchToken) return; // Игнорируем устаревший ответ
        resultBlock.innerHTML = html;
        resultBlock.style.display = 'block';
    });
```

> **Токены Symbol** защищают от гонок: устаревший ответ игнорируется.

### PHP

**Файл:** `searchAjax.php` → `as_getAjaxSearchResult_callback`.

```php
check_ajax_referer('argon_shop_nonce', 'nonce');

$searchResult = as_queryManagerSearchResult($searchStr, $searchParameters);

// Ранжирование (argon_search_ajax_orderby / as_search_custom_orderby, сумма баллов):
// - заголовок = вся фраза              → 1000
// - артикул (_article) = вся фраза     → 800
// - заголовок начинается с фразы       → 500
// - артикул начинается с фразы         → 400
// по каждому слову запроса:
// - заголовок начинается со слова      → 50
// - слово в начале слова заголовка     → 30
// - слово где угодно в заголовке       → 10
// - слово в артикуле                   → 5
// при равенстве: post_date DESC, ID DESC

if (empty($searchResult) && !ctype_digit($searchStr)) {
    // Неверная раскладка: варианты зависят от языка магазина
    // ru: ghjlern → продукт; en: ыскуц → screw; fr: chqise → chaise
    foreach (as_search_layout_variants($searchStr) as $searchStrFix) {
        $searchResult = as_queryManagerSearchResult($searchStrFix, $searchParameters);
        if ($searchResult) break;
    }
}

as_showResult($searchResult); // HTML через as_generateResultHTML
```

### Языковые правила (с 1.1.0)

`searchLanguage.php` определяет язык (`as_search_language()` → `ru` / `en` / `fr`,
по локали сайта), разбивает запрос на слова (`as_search_get_words()`: стоп-слова,
элизии, окончания множественного числа для en/fr) и строит SQL (`as_search_where_sql()`).
Поведение для русского не изменилось по сравнению с 1.1.0.

### Синхронизация с GET-поиском

То же ранжирование реализовано в `searchGetFilters.php` / `searchAjax.php` через хуки:

```php
posts_search
posts_join
posts_orderby
posts_distinct
```

> ⚠️ **Важно:** изменения в одной ветке поиска нужно зеркально повторять в другой.

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

**Файл:** `myCabinet.php` → `as_cabinetEditEmailPassword_callback`.

```php
check_ajax_referer('argon_shop_nonce', 'nonce');

// Проверка: userID === get_current_user_id().
// Иначе выполнение прерывается: wp_die( __( 'Insufficient permissions' ) ) — без JSON и без кода 403.
$userID = absint($_POST['userID'] ?? 0);
if (0 === $userID || $userID !== get_current_user_id()) {
    wp_die(__('Insufficient permissions', 'argon-shop'));
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
// Сессия и nonce становятся недействительными — возвращается newNonce.
```

### Ответ

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

Все значения в `edited` и `errors` — **переведённые сообщения**, а не данные (ключи `errors`: `email`, `pass`, `newPass`, `newPassConfirm`). `newNonce` есть только после смены пароля.

### Обработка в JS

```javascript
// #showErrors — распределяет ошибки по .editEmailError span,
// .editPassError span и т. д.

// edited.newEmail — записывает введённый email в data-oldemail и показывает сообщение.
// edited.newPass — window.location.reload() через 2 секунды
// (новая сессия).
```

### Перехватчик nonce

```javascript
// core/nonce.js слушает XMLHttpRequest.load и обновляет config.nonce
// при получении newNonce.
// arsApi также проверяет ответы в tryApplyNonceFromText.
```

---

## Сценарий 7. Сортировка каталога

**Триггер:** клик по `.as-sort[name]` внутри `.sortParameters`.

### JS

**Файл:** `modules/sort.js`.

```javascript
const catId   = sorting.closest('.sortParameters').dataset.catId;
const pageNum = sorting.closest('.sortParameters').dataset.page;

// howSorting = 'DESC', если у кнопки есть sort-min, иначе 'ASC'.
const howSorting = sorting.classList.contains('sort-min') ? 'DESC' : 'ASC';

arsApi.post('sortingCatalog', { typeSort, howSorting, catId, pageNum });

// #showResult(html, button, howSorting) — сбрасывает подсветку (sort-min / sort-max),
// ставит класс, productList.innerHTML = html.
```

### PHP

**Файл:** `sort.php` → `as_sortingCatalog_callback`.

1. Сохраняет сортировку в cookie `AS_CatalogSorting` (JSON).
2. `as_get_sort_products($typeSort, $howSorting, $catId, $pageNum)` —
   `WP_Query` с `tax_query` по `catalog` и сортировкой:
   - `typeSort = date` → `orderby` по дате (+ ID);
   - `typeSort = name` → `orderby = title` (по названию, + ID);
   - `typeSort = price` → `orderby = meta_value_num`, `meta_key = _price`.
3. `as_view_products_list($productsList)` формирует HTML карточек.

### Изменения в DOM

- `.catalogProductList` полностью заменяется.
- `.as-error-sort` скрывается.

---

## Вспомогательное: хранение на клиенте

Проект **не использует** `sessionStorage` и `localStorage`.
Все данные магазина хранятся только в cookie:

| Cookie | Назначение |
|--------|---------|
| `productsShoppingCart` | Корзина |
| `AS_History` | Недавно просмотренные |
| `AS_CatalogSorting` | Сортировка |
| `WP-LastViewedPosts`, `WP-LastViewedPosts1` | **Не используются.** Их читает/пишет только `includes/interface/last_viewed_posts.php`, который `argon-shop.php` не подключает (мёртвый код); `uninstall.php` всё равно удаляет его опцию `zg_lwp_widget` |

---
