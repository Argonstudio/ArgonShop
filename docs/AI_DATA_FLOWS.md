# AI_DATA_FLOWS.md — Сквозные сценарии ArgonShop

> Описание того, как данные текут между плагином и темой в ключевых сценариях.
> См. также корневой `AI_CONTEXT.md`, `plugins/argon-shop/AI_CONTEXT.md`,
> `themes/bmshop/AI_CONTEXT.md`.

---

## 1. Каталог

### 1.1. Просмотр категории
1. WordPress определяет шаблон `taxonomy-catalog.php` (тема).
2. Шаблон получает `$current_id = get_queried_object()->term_id`.
3. Рендерит описание (`term_description()`).
4. Получает дочерние категории `get_terms('catalog', ['parent' => $current_id])`.
5. Делит их на `base` и `request` по полю `catalog_type` (ACF).
6. Выводит панель сортировки: `as_sort($settingSort, $catID, $pageNum)` (плагин).
7. Выводит список товаров:
   - `$typeSort = getCookie('AS_CatalogSorting')` — если есть, использует его;
   - `$productsList = get_sort_products($typeSort['typeSort'], $typeSort['howSort'], $catID, $pageNum)` (плагин);
   - `view_products_list($productsList)` (плагин) → `generate_product_card()` (тема).

### 1.2. AJAX-сортировка
1. Пользователь кликает `.as-sort` (тема).
2. JS `sort.js` (плагин) → `sortingCatalog(sorting, typeSort, howSorting, catId, pageNum)`.
3. AJAX POST `action=sortingCatalog` → `sortingCatalog_callback()` (плагин).
4. Сохраняет cookie `AS_CatalogSorting`.
5. `get_sort_products()` → `view_products_list()` → HTML.
6. JS вставляет HTML в `.catalogProductList`, обновляет классы `.sort-min`/`.sort-max`.

---

## 2. Корзина

### 2.1. Добавление товара
1. Пользователь кликает кнопку (тема: `.card-inBascet`, `#addShoppingCart`).
2. JS `cardProduct.js`/`product.js` (плагин) → `addProducts(productID, amount, type, button)`.
3. AJAX POST `action=addProducts_shoppingCart` → `addProducts_shoppingCart_callback()` (плагин).
4. Читает cookie `productsShoppingCart`, добавляет/суммирует количество, сохраняет.
5. Возвращает `{amountProduct, productAmountCart, totalPrice}`.
6. JS обновляет:
   - `.viewBlock-amountProducts span` (виджет корзины);
   - `.viewBlock-priceProducts span`;
   - блок `.card-alreadyAdded` / `#howManyProducts`.

### 2.2. Изменение количества
1. Пользователь меняет `.shoppingCartAmountProduct` (или кликает `+`/`-`).
2. JS `cart.js` (плагин) → `cartAmountProductsAjax(productID, amount, price, ...)`.
3. AJAX POST `action=amountProducts_shoppingCart` → `amountProducts_shoppingCart_callback()` (плагин).
4. Обновляет cookie, возвращает `{weight, actualPrices, productsShoppingCart}`.
5. JS вызывает `cartAmountProducts(actualPrices)` — обновляет все цены/вес.

### 2.3. Удаление товара
1. JS `cart.js` → `deleteProducts(productID, block, weight, price)`.
2. AJAX POST `action=deleteProducts_shoppingCart` → `deleteProducts_shoppingCart_callback()` (плагин).
3. Удаляет из cookie, возвращает `false` (если пусто) или `actualPrices`.
4. JS удаляет DOM-блок, пересчитывает.

### 2.4. Оформление заказа
1. Пользователь заполняет форму (тема: `template-parts/cart/{quickOrder,person,legalPerson}.php`).
2. JS `cart.js` → `submitCart(dataForm)`.
3. AJAX POST `action=submitCart_shoppingCart` → `submitCart_shoppingCart_callback()` (плагин).
4. Логика:
   - `get_valueFieldsCart($typeForm, $_POST)` — валидация и сборка полей;
   - проверка совпадения cookie-корзины и `productsCart` из формы;
   - обработка файлов (`$_FILES['fileCart']`);
   - регистрация (если `cartReg`): `wp_create_user`, `wp_signon`, мета `accountData`;
   - `wp_insert_post(['post_type' => 'shoporder', ...])`;
   - `add_post_meta`: `_productsCart`, `_fieldsCart`, `_number`, `_productAmountCart`, `_totalPrice`, `_shipping`, `_payment`;
   - `wp_set_object_terms($post_id, 'neworder', 'statusorders')`;
   - `mail()` админу и пользователю;
   - `update_option('counterOrders', $counterOrders + 1)`.
5. JS: при успехе — `deleteAllProductsShoppingCart(message)` (очищает DOM).

---

## 3. Личный кабинет

### 3.1. Сохранение данных
1. Пользователь заполняет поля `.accountDetail`, `.accountLegalDetail` (тема: `template-parts/cabinet/account.php`).
2. JS `cabinet.js` (плагин) собирает `forBack` (JSON).
3. AJAX POST `action=cabinetAccountSave` → `cabinetAccountSave_callback()` (плагин).
4. Санитизация: `stripslashes`, `strip_tags`, `json_decode`, `sanitize_text_field`, `wptexturize`, `wp_slash`.
5. `update_user_meta($userID, 'accountData', $data)`.
6. Возвращает строку результата.

### 3.2. Смена email/пароля
1. Пользователь заполняет `#editEmail`, `#editOldPass`, `#editNewPass`, `#editNewPassConfirm`.
2. JS `cabinet.js` → `cabinetEditEmailPassword(userID, email, oldEmail, pass, newPass, newPassConfirm)`.
3. AJAX POST `action=cabinetEditEmailPassword` → `cabinetEditEmailPassword_callback()` (плагин).
4. Валидация:
   - пароль (`wp_check_password`);
   - длина нового (`strlen >= 6`);
   - совпадение;
   - `email_exists`.
5. При успехе: `wp_update_user`, `wp_set_password`, `wp_signon`.
6. Возвращает JSON `{edited: {...}, errors: {...}}`.

### 3.3. Просмотр заказов
1. `template-parts/cabinet/ordering.php` → `view_user_orders($user_ID)` (плагин).
2. `get_user_orders($user_ID, $filters)` — `WP_Query` + мета + статусы.
3. `create_user_ordersTable($orders)` — HTML-таблица.
4. Фильтр по статусу: JS `cabinet.js` → AJAX `ordersFiltersStatus` → `ordersFiltersStatus_callback()` → `create_user_ordersTable()`.

---

## 4. Поиск

### 4.1. AJAX-поиск (форма в шапке/на странице)
1. Пользователь вводит в `.asInputSearchForm` (тема) — `data-ajax` с параметрами.
2. JS `site.js` (плагин) → `getAjaxSearchResult(inputForm, dataAjax, searchStr, blockResult)`.
3. AJAX POST `action=getAjaxSearchResult` → `getAjaxSearchResult_callback()` (плагин).
4. `queryManagerSearchResult($searchStr, $searchParameters)`:
   - `cleanParameters` — санитизация;
   - `queryAllocatorSearchResult('product' | 'category' | 'post', ...)`:
     - `queryProductSearchResult` — по артикулу (`_article`) + `s`;
     - `queryTaxonomySearchResult` — `get_terms('catalog', ['search' => ...])`;
     - `queryPostSearchResult` — `WP_Query` по `post_type='post'` с `tax_query`;
   - `prepCatalog`, `prepPost`, `prepProduct` — подготовка данных;
   - при пустом результате и не-цифровом запросе — `fixKeyboardlayout()` (латиница → кириллица).
5. `showResult($searchResult)` → `generateResultHTML()` — HTML.
6. JS вставляет HTML в `.searchAjaxResult` / `.topSearchBlockResult` / `.pageSearchBlockResult`.

### 4.2. Поиск на странице `/?s=`
1. `search.php` (тема) → `as_search($params)` — форма.
2. `pre_get_posts` → `search_filter($query)` (плагин).
3. Если `$_GET['post_type']` и `$_GET['as_taxonomy']` — формирует `tax_query` (OR).
4. Вывод: `generate_product_card()`.

---

## 5. История просмотров

### 5.1. Добавление
1. На странице товара JS `product.js` (плагин) вызывает `addCookieHistory(productID)`.
2. AJAX POST `action=addCookieHistory_history` → `addCookieHistory_history_callback()` (плагин).
3. Читает cookie `AS_History`, удаляет дубликат, `array_unshift`, сохраняет.

### 5.2. Вывод
1. `history.php` (тема) или `sidebar.php` → `get_history_products($count)` (плагин).
2. `WP_Query` с `post__in` и `orderby=post__in`.
3. `view_products_list()` → `generate_product_card()`.

---

## 6. Оптовые скидки (шаги цен)

### 6.1. Включение
1. В настройках `settingShop['typeSale'] = 'wholesalePriceSteps'`.
2. `check_saleSteps()` возвращает `true`.
3. При `init` регистрируется таксономия `wholesalePrice`.
4. В метабоксе товара появляется блок оптовых цен.

### 6.2. Расчёт
1. `getActualPriceAllProducts($productsShoppingCart)` (плагин) для всех товаров.
2. Для каждого товара:
   - `getDiscount($productID)` — `_wholesalePrice[term_id]`;
   - `getActualStepDiscont($productID, $productsShoppingCart, $totalPriceProduct)`:
     - считает сумму корзины;
     - перебирает шаги (`get_term_by('id', $key, 'wholesalePrice')`);
     - выбирает максимальный шаг, который `<= $totalPriceInShoppingCart`;
   - `$actualPrice = $productDiscount[$termActualStep->term_id]`.
3. Округление до 2 знаков.

### 6.3. Отображение
- В карточке товара: `getActualPrice("cardProduct", ...)`.
- На странице товара: таблица шагов (`block-wholesale-price`), активный шаг — `.actualStepDiscont`.
- В корзине: пересчёт через `cartAmountProducts(actualPrices)`.

---

## 7. Настройки магазина

### 7.1. Сохранение
1. Админ заполняет форму в `options-general.php?page=settings-shop`.
2. `register_setting('option_group', 'settingShop', 'sanitize_callback')`.
3. `sanitize_callback($options)`:
   - `strip_tags` для всех;
   - для `quickLine`, `personLine`, `legalPersonLine` — построчный разбор в массив;
   - `wholesalePriceSteps` — `intval`.

### 7.2. Использование
- `get_option('settingShop')` в `getSetting.php`, `createPostType.php`, `settingPage.php`.
- `get_cartPageID()`, `get_cabinetPageID()` — для редиректов и условий.
- `show_user_fields()` — рендер полей форм на основе `quick`, `person`, `legalPerson`.

---

## 8. Админские сценарии

### 8.1. Редактирование товара
1. Метабоксы `mainParameters`, `wholesalePrice`, `characteristics`.
2. При смене категории (JS `admin-script.js`) → AJAX `update_characteristics` → `updateCharacteristicsFunction()`.
3. `characteristics_metabox()` перерисовывает поля характеристик.
4. При сохранении — `save_detailed_fields()`:
   - `_price`, `_weight`, `_article`, `_hit`, `_newProduct`, `_wholesalePrice`, `_characteristics_text_field`, `_characteristics_checkbox_field`.

### 8.2. Редактирование заказа
1. Метабоксы `detailOrders`, `detailBuyer`, `statusOrders`.
2. Поиск товара → AJAX `as_searchSuitableProduct_order` → `as_searchSuitableProduct_order_callback()`.
3. Добавление → AJAX `as_addProduct_order` → `as_addProduct_order_callback()` → `createTableProduct()`.
4. Добавление полей → AJAX `as_addFields_order` → `as_addFields_order_callback()` → `as_getHTMLField()`.
5. Сохранение → `save_data_order()`:
   - пересчёт `_productsCart`, `_totalPrice`, `_productAmountCart`;
   - обновление `post_title`;
   - `_fieldsCart`, `_shipping`, `_payment`, `statusorders`.

### 8.3. Редактирование характеристики
1. Поля в форме термина таксономии `characteristics` (`checkCategory[term_id]`).
2. Сохранение → `save_custom_taxonomy_meta()` → `update_term_meta($term_id, 'checkCategory', $checkCategory)`.

---

## 9. Ключевые cookie и их жизненный цикл

| Cookie | Кто пишет | Кто читает | Формат |
|--------|-----------|------------|--------|
| `productsShoppingCart` | `addProducts_shoppingCart`, `amountProducts_shoppingCart`, `deleteProducts_shoppingCart`, `deleteAllProducts_shoppingCart` | `getDataCart`, `getActualPriceAllProducts`, шаблоны темы | JSON: `{productID: {amountProduct, price}}` |
| `AS_History` | `addCookieHistory_history` | `get_history_products` | JSON: `[productID, ...]` |
| `AS_CatalogSorting` | `sortingCatalog_callback` | `as_sort`, `as_catalog`, `taxonomy-catalog.php` | JSON: `{typeSort, howSort}` |
| `WP-LastViewedPosts` | `zg_lw_setcookie` (в `last_viewed_posts.php`) | `zg_recently_viewed` | PHP serialize |

---

## 10. Точки расширения (для будущего рефакторинга)

- **Nonce:** добавить `wp_create_nonce`/`check_ajax_referer` во все AJAX-обработчики.
- **`wp_mail`:** заменить `mail()` в `submitCart_shoppingCart_callback`.
- **Санитизация:** добавить `esc_html`, `esc_attr`, `wp_kses_post` в шаблонах темы.
- **Удалить `preg_replace /e`** в `last_viewed_posts.php`.
- **Удалить `each()`** (PHP 8).
- **Заменить `extract()`** на явные переменные.
- **Префиксы:** добавить `as_` ко всем глобальным функциям.
- **Кэширование:** `get_terms`, `get_catalog_terms`, `splitMenu` — кэшировать.
- **Перейти на `wp_send_json_success/error`** вместо `echo json_encode` + `wp_die`.
- **Убрать `file_get_contents($imageUrl)`** в `sidebar.php` — заменить на `wp_get_attachment_image`.
