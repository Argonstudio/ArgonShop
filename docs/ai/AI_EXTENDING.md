# AI_EXTENDING.md — как добавить функцию, не сломав архитектуру

Продолжение [AI.md](../../AI.md). Пошаговые инструкции для типовых расширений.

## 1. Новое AJAX-действие

**Шаги:**

1. **PHP-обработчик.** В подходящем файле в `includes/interface/<функция>/`:

```php
add_action( 'wp_ajax_my_action', 'my_action_callback' );
add_action( 'wp_ajax_nopriv_my_action', 'my_action_callback' );

function my_action_callback() {
    check_ajax_referer( 'argon_shop_nonce', 'nonce' );

    // ... логика ...

    echo wp_json_encode( $result ); // как во всём проекте; wp_send_json() не используется
    wp_die();
}
```

Для действий админки — только `wp_ajax_<action>` + проверка `if ( ! current_user_can( 'manage_options' ) ) { wp_die( … ); }` сразу после nonce.

2. **Вызов из JS.** Через `arsApi`:

```js
const data = await arsApi.postJson( 'my_action', { param1: 'value' });
```

3. **Зарегистрируйте действие в `AI_AJAX_REFERENCE.md`.** Обновите таблицу.

4. **Nonce обязателен для каждого действия.** Не добавляйте действия в `config.skipNonceActions` (пуст с 1.1.0).

5. **Имя функции — с префиксом `as_`** (см. `AI_FUNCTION_NAMES.md`). Имя AJAX-действия (`action`) может быть любым.

6. **Добавьте проверку в `tests/smoke-test.php`**, если действие изменяет данные.

---

## 2. Новый ES-модуль

**Шаги:**

1. **Файл.** `assets/js/interface/modules/my-feature.js`:

```js
'use strict';
import { arsApi } from '../core/api.js';
import { t } from '../core/config.js';
import { qs, qsa, on, delegate } from '../../shared/dom.js';
import { formatPrice } from '../../shared/format.js';

class ArsMyFeature {
    #initialized = false;
    init() {
        if ( this.#initialized ) return;
        if ( ! qs( '.my-feature-root' ) ) return;
        this.#initialized = true;
        this.#bind();
    }
    #bind() { /* обработчики */ }
}

export const arsMyFeature = new ArsMyFeature();
```

2. **Импорт в `main.js`:**

```js
import { arsMyFeature } from './modules/my-feature.js';
```

3. **Вызов в `initApp()`:**

```js
arsMyFeature.init();
```

4. **Проверка контракта.** Если модуль слушает DOM-селекторы, они должны быть перечислены в `AI.md` (раздел 5) и `AI_TEMPLATE_MAP.md`.

5. **Видимый пользователю текст** — только через `t( 'key' )`. Добавьте ключ в `I18N_DEFAULTS` в `core/config.js` (на английском) и в `argon_shop_js_i18n()` в `argon-shop.php` (обёрнутый в `__()`), затем обновите файлы .pot/.po/.mo (`AI_I18N.md`).

**Чего не делать:**

- Не используйте jQuery.
- Не пишите `document.ready` — модуль загружается как `type="module"` (отложенно, DOM готов).
- Не забывайте `#initialized` — модуль вызывается на каждой странице.
- Не пишите текст строковыми литералами (`'Cart is empty'`, и тем более по-русски) — именно это было причиной бага «Files uploaded», исправленного в 1.1.0.

---

## 3. Новое мета-поле товара

**Шаги:**

1. **Вывод в админке.** В `includes/admin/fieldsProduct.php`, функция `as_mainParameters_metabox`:

```php
$myField = get_post_meta( $post->ID, '_myField', true );
?>
<li class="advanced-field">
    <div class="span-advanced-fields"><?php esc_html_e( 'My field', 'argon-shop' ); ?></div>
    <input type="text" name="_myField" value="<?php echo esc_attr( $myField ); ?>">
</li>
```

2. **Сохранение.** В `as_save_detailed_fields`:

```php
if ( isset( $_POST['_myField'] ) ) {
    $myField = sanitize_text_field( wp_unslash( $_POST['_myField'] ) );
    as_update_meta( $post_id, '_myField', $myField );
}
```

`as_update_meta` находится в `includes/api/as_action.php`. Пустое значение удаляет мета-поле.

3. **Использование на фронтенде.** В шаблоне:

```php
$myField = get_post_meta( get_the_ID(), '_myField', true );
```

4. **Документация.** Добавьте поле в `AI.md` (раздел 5) и в `AI_TEMPLATE_MAP.md`.

**Формат мета-ключа:** подчёркивание в начале, затем имя в том регистре, который уже принят рядом: `_price`, `_article` и `_snake_case`, но также `_totalPrice`, `_productsCart`, `_newProduct` (camelCase). Не переименовывайте существующие ключи. Так поле скрывается из стандартного блока «Произвольные поля» в админке.

---

## 4. Новый шаблон страницы

**Шаги:**

1. **Файл.** `themes/argon-shop-theme/my-page.php`:

```php
<?php
/**
 * Template Name: My page
 *
 * @package ArgonShopTheme
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); ?>

<section id="out">
    <div class="row">
        <?php get_sidebar(); ?>
        <main class="block-content">
            <?php if ( function_exists( 'as_kama_breadcrumbs' ) ) as_kama_breadcrumbs( ' » ' ); ?>
            <?php while ( have_posts() ) : the_post(); ?>
                <h1 class="title"><?php the_title(); ?></h1>
                <?php the_content(); ?>
            <?php endwhile; ?>
        </main>
    </div>
</section>

<?php get_footer(); ?>
```

2. **Создайте страницу в админке** и назначьте ей шаблон.

3. **Стили.** При необходимости — в `assets/css/`, подключаются в `functions.php` через `wp_enqueue_style`.

4. **Добавьте запись в `AI_TEMPLATE_MAP.md`.**

---

## 5. Новый тип записи

**Шаги:**

1. **Регистрация.** В `includes/createPostType.php` или в `functions.php` темы (в зависимости от CPT):

```php
add_action( 'init', 'register_my_post_type' );
function register_my_post_type() {
    register_post_type( 'mypost', array(
        'labels' => array(
            'name'          => __( 'My posts', 'argon-shop' ),
            'singular_name' => __( 'Post', 'argon-shop' ),
        ),
        'public' => true,
        'show_in_menu' => true,
        'supports' => array( 'title', 'editor', 'thumbnail' ),
        'has_archive' => true,
    ));
}
```

2. **Шаблоны.** `archive-mypost.php`, `single-mypost.php` в теме.

3. **Добавьте запись в `AI_ARCHITECTURE.md`** (раздел 6).

---

## 6. Новое правило скидок

Сложная функция. Правила:

- **Не создавайте собственный расчёт цены.** Всегда используйте `as_getActualPrice` (в `includes/api/getData/getData.php`).
- **Расширяйте функцию, а не дублируйте её.** Новая логика скидок добавляется внутрь `as_getActualPrice` в виде условий, а не в новую функцию рядом.
- **Синхронизируйте все места вызова.** Скидка должна работать одинаково:
  - На странице товара (`product.js` → `wholesalePrice_shoppingCart`).
  - В корзине (`amountProducts_shoppingCart`).
  - При оформлении заказа (`as_submitCart_shoppingCart_callback`).
  - В письмах (`as_emailTextsGenerator`).
  - В админке заказа (`orderSave.php`).

**Если пропустить хотя бы одно место, скидка будет считаться по-разному.**

---

## 7. Новое поле формы оформления заказа

**Шаги:**

1. **Настройки.** Админка → Настройки → Магазин → «Данные пользователя» → добавьте строку в textarea нужного типа (`quickLine`, `personLine`, `legalPersonLine`):

```
myField[Field name,placeholder,50]
```

Формат: `ID[Название,Плейсхолдер,класс]`. `класс` — `50` или `100` (ширина поля).

2. **Остальное сделает `as_show_user_fields`.** Функция выводит поле в формах корзины, сохраняет его в `accountData` при регистрации и показывает в письме.

3. **Если поле обязательное**, добавьте его ключ в `as_get_required_fields()` (`shoppingCart/checkout-fields.php`) или через фильтр в теме:

```php
add_filter( 'argon_shop_required_fields', function( $fields, $typeForm ) {
    if ( 'person' === $typeForm ) {
        $fields[] = 'myField';
    }
    return $fields;
}, 10, 2 );
```

Этот же список выводит атрибут `required` в шаблонах `template-parts/cart/*.php` и проверяется сервером — менять шаблоны не нужно.

---

## 8. Модуль оплаты, доставки или интеграции (через хуки)

Не трогайте ядро корзины — модуль подключается к хукам оформления заказа.
Все хуки перечислены в `AI.md`, раздел «Хуки».

```php
// 1. Новый способ оплаты появляется в списке разрешённых кодов
add_filter( 'argon_shop_payment_methods', function( $methods ) {
    $methods['stripe'] = __( 'Online card payment', 'my-module' );
    return $methods;
} );

// 2. Своя проверка перед созданием заказа
add_filter( 'argon_shop_checkout_errors', function( $errors, $order ) {
    if ( 'stripe' === $order['payment'] && $order['total'] < 100 ) {
        $errors[] = sprintf(
            /* translators: %s: minimum amount with currency. */
            __( 'Online payment is available for orders from %s.', 'my-module' ),
            wp_strip_all_tags( as_price_html( '100' ) )
        );
    }
    return $errors;
}, 10, 2 );

// 3. Заказ создан — создаём платёж
add_action( 'argon_shop_order_created', function( $order_id, $order ) {
    if ( 'stripe' === $order['payment'] ) {
        update_post_meta( $order_id, '_payment_url', my_create_payment( $order_id, $order['total'], as_get_currency() ) );
    }
}, 10, 2 );

// 4. Ответ фронтенду — ссылка на оплату (обработку редиректа нужно добавить в cart.js)
add_filter( 'argon_shop_checkout_response', function( $result, $order ) {
    if ( 'stripe' === $order['payment'] ) {
        $result['redirect'] = get_post_meta( $order['order_id'], '_payment_url', true );
    }
    return $result;
}, 10, 2 );
```

Правила:
- Модуль — отдельный файл или плагин; не дописывайте его в `checkout.php`.
- Цену товара берите только из `as_get_product_price()` (фильтр `argon_shop_product_price`).
- Передавайте платёжным шлюзам код валюты магазина из `as_get_currency()`.
- Новые проверки — в `tests/smoke-test.php`.

---

## 9. Валюта

С 1.1.0 валюта магазина задаётся в **Настройки → Магазин → Валюта** (`USD` по умолчанию, `EUR`, `RUB`). Суммы хранятся как обычные числа; валюта — только настройка отображения (без пересчёта).

| Функция (`includes/api/getSetting.php`) | Возвращает |
|---|---|
| `as_get_currencies()` | список `code => label` для настройки |
| `as_get_currency()` | текущий код (`USD` / `EUR` / `RUB`) |
| `as_get_currency_symbol( $code = '' )` | `$`, `€`, `₽` (знак рубля проходит через перевод: в русской версии выводится `Р`) |
| `as_get_currency_position( $code = '' )` | `before` (USD) или `after` (EUR, RUB) |
| `as_price_html( $amount_html, $unit = '' )` | `$<span>120</span>/pc` или `<span>120</span> €/pc` |
| `as_get_currency_per_piece()` | `$/pc`, `€/pc`… для подписей в админке |

В теме используйте обёртки `astheme_price_html()`, `astheme_currency_symbol()`, `astheme_currency_position()` — они продолжают работать без плагина.

Правила:
- Никогда не хардкодьте `₽`, `$`, `€`, `руб.` в шаблонах, письмах или JS.
- Держите число внутри `<span>`, если его обновляет JS (корзина, виджет): JS меняет только число, символ остаётся в HTML.
- **Добавление валюты:**

```php
add_filter( 'argon_shop_currencies', function( $currencies ) {
    $currencies['GBP'] = __( 'Pound sterling (£)', 'my-module' );
    return $currencies;
} );
add_filter( 'argon_shop_currency_symbol', function( $symbol, $code ) {
    return 'GBP' === $code ? '£' : $symbol;
}, 10, 2 );
add_filter( 'argon_shop_currency_position', function( $position, $code ) {
    return 'GBP' === $code ? 'before' : $position;
}, 10, 2 );
```

Фильтр `argon_shop_currency` переопределяет текущий код (например, по региону в мультирегиональной конфигурации).

---

## 10. Языковые правила поиска

Правила поиска находятся в `includes/interface/search/searchLanguage.php` и зависят от локали сайта: `ru` (исправление раскладки QWERTY → ЙЦУКЕН, поведение 1.1.0), `en` (стоп-слова, множественное число, русская раскладка → QWERTY), `fr` (элизии, стоп-слова, множественное число, исправления AZERTY). Остальные локали используют `en`.

```php
// Принудительно задать язык (например, для немецкого сайта правила как у английского — уже по умолчанию)
add_filter( 'argon_shop_search_language', function( $lang, $locale ) {
    return str_starts_with( $locale, 'uk' ) ? 'ru' : $lang;
}, 10, 2 );

// Дополнительные стоп-слова
add_filter( 'argon_shop_search_stop_words', function( $words, $lang ) {
    if ( 'en' === $lang ) {
        $words[] = 'buy';
    }
    return $words;
}, 10, 2 );

// Дополнительные варианты написания, которые пробуются, если ничего не найдено
add_filter( 'argon_shop_search_layout_variants', function( $variants, $searchStr, $lang ) {
    return $variants;
}, 10, 3 );
```

Правила:
- Меняйте обе ветки поиска (AJAX и GET) через общие функции в `searchLanguage.php`, а не по отдельности.
- Для каждого нового правила добавляйте случай в раздел «8. Search language» файла `tests/smoke-test.php`.

---

## 11. Что не добавлять

| Функция | Почему |
|---|---|
| Собственный ORM | `WP_Query` и мета-поля — идиоматичный путь для WP |
| React / Vue | Ничего не даёт витрине, убивает Vanilla JS |
| Собственный REST API | Если нужен — стандартный `register_rest_route`, без собственной авторизации |
| Второй набор шаблонов | Для магазина достаточно одной темы |
| jQuery на фронтенде | Регресс |

---

## 12. Чек-лист перед коммитом

- [ ] PHP: `if ( ! defined( 'ABSPATH' ) ) exit;` в начале файла.
- [ ] PHP: экранирование вывода (`esc_html`, `esc_attr`, `esc_url`).
- [ ] PHP: `absint` / `sanitize_*` для входящих данных.
- [ ] AJAX: `check_ajax_referer`.
- [ ] AJAX: ответ через `echo wp_json_encode()` (или экранированный HTML) + `wp_die()` в конце; ничего лишнего до ответа.
- [ ] JS: модуль через `export const arsXxx = new ArsXxx()`.
- [ ] JS: импортирован в `main.js`, вызывается в `initApp()`.
- [ ] JS: без jQuery; видимый текст только через `t()`.
- [ ] CSS: в теме только оформление, функциональные состояния — в `interface-style.css`.
- [ ] PHP: новые функции с префиксом `as_` и `@since` следующей версии (`AI_VERSIONING.md`); старые имена не возвращаются.
- [ ] Текст: на английском внутри `__()` / `esc_html_e()` с доменом `argon-shop` или `argon-shop-theme`, подстановки через `sprintf`; переводы ru_RU и fr_FR обновлены (`AI_I18N.md`).
- [ ] Цены: только через `as_price_html()` / обёртки темы; без захардкоженных знаков валют.
- [ ] Комментарии и документация: на русском (RU-ветка).
- [ ] PHP: цена товара только из `as_get_product_price()` / `as_sanitize_cart()`, никогда из cookie или формы.
- [ ] PHP: цены и названия оптовых ступеней → `as_to_float()`, а не `floatval()`; выбор ступени по числовому значению, наибольшая достигнутая ступень имеет приоритет.
- [ ] PHP: пароли — проверка типа и только `wp_unslash()`, без `sanitize_text_field()`; пустота через `'' === $pass`, длина через `mb_strlen()` в UTF-8.
- [ ] Данные: если меняется формат мета-поля/cookie/опции — миграция в `includes/install.php` (`AI_VERSIONING.md`, раздел 5).
- [ ] Тесты: `php tests/static-check.php` и `wp eval-file tests/smoke-test.php` полностью зелёные; новая функция получает новую проверку (`AI_TESTING.md`). Без терминала — скажите пользователю, что запустить и что проверить вручную.
- [ ] Документация: `AI_*.md` обновлены, если добавлен контракт; строка в `CHANGELOG.md` → `## [Unreleased]`. Не повышайте версию без релиза.

---

## 13. Выбор города и мультирегиональность: несколько городов, одна админка

> **Статус.** В коде есть только заготовка. Второй город (на демо — Истра) **не доделан**: его шаблоны не созданы, его вариант в переключателе городов закомментирован. Выполняйте этот раздел, **только если пользователь просит сделать выбор городов** (или сайты для нескольких регионов). Без такой просьбы ничего из раздела не добавляйте.

### 13.0. Сначала спросите про WP Multisite

Прежде чем писать код, **уточните у пользователя, не хочет ли он использовать WordPress Multisite** (сеть сайтов). Это рекомендуемый вариант (README: «рекомендую обычный Мультисайт»). Кратко объясните выбор:

| | WordPress Multisite (рекомендуется) | Один сайт на нескольких доменах (как на демо-стенде) |
|---|---|---|
| Админка | админка сети + отдельная консоль у каждого города | одна консоль для всех городов |
| Товары, цены, заказы, настройки | у каждого города свои (общие — только через доп. плагины / синхронизацию) | общие для всех городов |
| Контент города | естественно — у каждого сайта свои страницы, меню, акции | через рубрики / мета / фильтры (13.3) |
| Изменения кода ArgonShop | почти никаких (см. 13.4) | нужно написать функции из 13.3 |
| Риски | настройки на каждом сайте, сеть нужно обслуживать | проверка домена (host), кэш по домену, дубли для SEO |

Если пользователь выбрал Multisite — переходите к 13.4 и не создавайте городские шаблоны. К 13.3 и 13.5 переходите, только если пользователь подтвердил вариант «одна админка». Если на сайте уже работает AA-DomainMirror — сначала прочитайте 13.2.

### 13.1. Что уже есть в коде

- `includes/siteLine.php` — `as_change_nav_menu_objects()` (фильтр `wp_nav_menu_objects`) заменяет маркер `as_homepage` в ссылках меню на текущий домен (`as_get_safe_server_name()` — проверенный host, иначе `home_url()`). Одно меню работает на всех доменах городов.
- `header.php` — `<select class="sitySelect">`: сейчас только «Москва» (`value="/"`). Вариант «Истра» **закомментирован** (адрес — тестовый домен). Там же собираются `data-promocategory` (слаг категории акций, ID 35) и `data-promoarchive` (слаг страницы архива, ID 642); эти ID зашиты в код.
- `assets/js/interface/modules/header.js` — при смене города переходит на тот же путь на другом домене; на страницах акций использует `data-promocategory` / `data-promoarchive` и определяет тип страницы по `id` у `main`: `block-contentPromo`, `block-contentPromoPage`, `block-contentPromoArchive`. **Эти `id` и класс `.sitySelect` — контракт, не меняйте.**
- `single.php` — диспетчер: категория `36` → `single-aktsii-moskva`, категория `35` → второй город (шаблона нет), иначе `single-default`.
- Зашитые в код ID и слаги: рубрика `36` (акции Москвы), `35` (акции второго города), `37` (статьи; вместе с 36 — в запросе меню шапки), страницы `639` / `642` (страница акций / архив, их использует переключатель), слаги `/aktsii-moskva/`, `/arhiv-aktsij-moskva/`, `/adres-na-karte-moskva/`.
- Фильтр `argon_shop_currency` — может вернуть свою валюту для города.
- Готовые образцы для Москвы: `single-aktsii-moskva.php`, `category-aktsii-moskva.php`, `categoryArchive-aktsii-moskva.php`.

### 13.2. Историческая совместимость: плагин AA-DomainMirror

Выбор города на демо-стенде (и в старых установках ArgonShop) сделан на отдельном плагине **AA-DomainMirror** — доработанной копии «Domain Mirror» 1.0 от Dimox (http://dimox.name/wordpress-domain-mirror-plugin/). Он **не входит в этот репозиторий** и здесь не сопровождается. Что он делает:

- основной домен и домены-зеркала **зашиты в код** плагина (`$main`, `$mirror`, `$mirror1`, например `voitkoze.beget.tech` → `istra.voitkoze.beget.tech`, `istra1.voitkoze.beget.tech`);
- если `$_SERVER['SERVER_NAME']` совпадает с зеркалом, добавляет фильтры (приоритет 1) на `option_siteurl`, `option_home`, `option_stats_url`, которые возвращают `"http://" . SERVER_NAME`;
- заменяет основной домен на текущий в `the_content`, `the_excerpt`, `comment_text`, `get_comment_author_link`;
- всё остальное (товары, заказы, настройки, пользователи) — один и тот же WordPress, одна админка на все города. Меню работает на любом домене благодаря маркеру `as_homepage` (`siteLine.php`).

Правила для ИИ:

- Если сайт **по-прежнему использует AA-DomainMirror** — сохраните его работу: не добавляйте второй набор фильтров `option_home` / `option_siteurl`, не удаляйте логику маркера `as_homepage`, не переименовывайте `.sitySelect` и ID акций без согласия пользователя. Новые домены добавляются в файле плагина (правит пользователь или ИИ после подтверждения).
- Известные ограничения старого плагина — сообщите пользователю, не «чините» молча: всегда возвращает `http://` (без HTTPS), использует глобальные функции без префикса (`wpdm_filter_url`, `wpdm_filter_text`) и глобальные переменные, список доменов — в коде, комментарии файла в Windows-1251 (в UTF-8 выглядят как «кракозябры»), не обновлялся с версии 1.0.
- Для новой установки AA-DomainMirror **не рекомендуйте**: предложите Multisite (13.4) или написать функции из 13.3 внутри плагина ArgonShop.

### 13.3. Возможные функции для варианта «одна админка» (в коде их нет — их нужно написать)

> ⚠️ **Ни одной из функций ниже в ArgonShop нет.** Это предлагаемая схема. Пишите их, только если пользователь попросил выбор городов и подтвердил вариант «одна админка» (13.0). Соблюдайте правила проекта: префикс `as_`, `@since` следующей версии, комментарии на русском (RU-ветка), переводы, строка в `CHANGELOG.md`, проверки в `tests/` (`AI_VERSIONING.md`, `AI_TESTING.md`). Не добавляйте их «заранее».

| Предлагаемое имя (нужно написать) | Назначение |
|---|---|
| `as_get_regions()` + фильтр `argon_shop_regions` | Карта городов — единый источник данных: `host => [ слаг, название, ID рубрики акций, ID страницы архива, страница карты, валюта, контакты ]`. Заменяет зашитые ID/слаги из 13.1. Можно хранить в настройках магазина, а не в коде |
| `as_get_current_region()` | Текущий город по проверенному host (`as_get_safe_server_name()`); иначе основной город. Принимать только host из `as_get_regions()` — никогда не доверять `Host` / `SERVER_NAME` как есть |
| `as_region_filter_home_url()` | Замена AA-DomainMirror: фильтры `option_home` / `option_siteurl` для доменов из белого списка, с правильной схемой (`is_ssl()`, HTTPS). Не пишите, если AA-DomainMirror ещё активен |
| `as_region_filter_content()` | Замена основного домена на текущий в контенте, анонсах и комментариях (то, что делал AA-DomainMirror) |
| `as_region_currency()` | Колбэк для существующего фильтра `argon_shop_currency` — валюта города |
| `as_get_region_promo_category()`, `as_get_region_promo_archive()` | ID / URL рубрики акций и архива текущего города — для `single.php`, городских шаблонов и `header.php` вместо `35` / `36` / `639` / `642` |
| `as_region_switcher_options()` (или функция темы `astheme_city_select()`) | Собирает варианты `.sitySelect` с `value` и `data-promocategory` / `data-promoarchive` из карты городов, чтобы в `header.php` не было закомментированных городов |

Учтите также: cookie корзины, истории и сортировки привязаны к домену (корзина не переносится при смене города — скажите пользователю, не меняйте формат cookie без миграции); кэш страниц должен различаться по host; у каждого города свои canonical, заголовки и описания, чтобы сайты не считались дублями.

### 13.4. Вариант WordPress Multisite

- Одна сеть, по сайту на город (поддомен или привязка домена — встроено в WordPress); у каждого города своя консоль, страницы, меню, акции, настройки, товары и заказы.
- ArgonShop и тема: **активируйте плагин для сети**, включите тему для сети, затем задайте «Настройки → Магазин» (валюта, страницы корзины / кабинета, поля форм) на **каждом** сайте — опции хранятся отдельно для каждого сайта. Хук активации создаёт страницы корзины и кабинета только на том сайте, где он сработал: проверьте остальные сайты.
- Переключателю `.sitySelect` тогда нужны только адреса сайтов сети. Функции из 13.3 и дополнительные шаблоны `-istra` не нужны: каждый сайт использует существующие шаблоны акций `-moskva`, но зашитые в них ID рубрик / страниц (13.1) на разных сайтах разные — сделайте их настраиваемыми (настройка магазина), а не копируйте шаблоны.
- Общий каталог / заказы для всех городов Multisite **не даёт** — если это нужно пользователю, это аргумент за вариант «одна админка».
- Прежде чем обещать поддержку Multisite, проверьте плагин в сети на тестовом сайте (`tests/smoke-test.php` на каждом сайте).

### 13.5. Шаблоны городов (вариант «одна админка»)

Для каждого нового города (ниже `<city>` — слаг, например `istra`):

1. **Контент.** Создайте в админке рубрику акций города (слаг `aktsii-<city>`) и страницу «Архив акций» с шаблоном города. Запишите их ID (лучше — сразу внесите в карту городов, 13.3).
2. **Шаблоны.** Скопируйте московские файлы и замените ID/слаги:
   - `single-aktsii-<city>.php` — отдельная акция (с `single-aktsii-moskva.php`);
   - `category-aktsii-<city>.php` — рубрика акций (с `category-aktsii-moskva.php`; WordPress выберет его по слагу рубрики `aktsii-<city>`);
   - `categoryArchive-aktsii-<city>.php` — **архив акций города** (с `categoryArchive-aktsii-moskva.php`): хлебные крошки вручную, `WP_Query` с `promo_active = 0` и `category` = ID рубрики города; в шапке файла — `Template Name: Архив акций (<Город>)`.
3. **Диспетчер.** В `single.php` вызывайте `get_template_part( 'single-aktsii-<city>' )` — **без расширения `.php`**. Обязательно исправьте существующий вызов `get_template_part( 'single-aktsii-istra.php' )` (с `.php` файл никогда не находится).
4. **Переключатель.** В `header.php` включите/добавьте `<option value="https://домен-города" data-promocategory="…" data-promoarchive="…">Город</option>` в `.sitySelect`. Название города — через `esc_html__( '…', 'argon-shop-theme' )` и переводы (`AI_I18N.md`). По возможности не хардкодьте ID: берите их из карты городов (13.3).
5. **Не переименовывайте** `.sitySelect`, `#block-contentPromo`, `#block-contentPromoPage`, `#block-contentPromoArchive` — от них зависит `header.js`.
6. **Правила проекта.** Без jQuery; тексты только в `__()` с доменом `argon-shop-theme`, затем обновите `.pot`/`.po`/`.mo`; цены — через `astheme_price_html()`; комментарии на русском (RU-ветка).
7. **Документация и версия.** Обновите `docs/ai/AI_TEMPLATE_MAP.md` (уберите пометки «нет в репозитории»), строку в `CHANGELOG.md` → `[Unreleased]`; версию не повышайте.
8. **Проверка.** `php tests/static-check.php`; на тестовом сайте — smoke «9. Pages open without errors» на домене каждого города; вручную: переключатель ведёт на нужный город, рубрика, архив и отдельная акция открываются, ссылки меню с `as_homepage` ведут на домен текущего города.

## Контракт числового ввода

`as_to_float()` и JS `parseNumber()` следуют одним правилам, закреплённым в `tests/number-cases.json`:

- `60,33` и `60.33` → `60.33`; пробельные символы, включая неразрывные и узкие пробелы, удаляются.
- Если есть и запятая, и точка, последний разделитель — десятичный, другой обязан группировать по три цифры: `1,234.56` / `1.234,56` → `1234.56`.
- Одиночная запятая/точка — десятичная: `1,234` означает `1.234`, **не 1234**. Повторяющиеся разделители должны образовывать корректные группы тысяч: `1,234,567` / `1.234.567` → `1234567`.
- Поддерживаются знак и научная запись (`1e3` → `1000`). Пустые, некорректные, нечисловые типы и бесконечные значения возвращают ноль (`abc60,33`, `1,23,45`, `1e309`). Положительность цены проверяется отдельно бизнес-логикой.
- Число читается целиком после нормализации; не возвращайте чтение только префикса через `floatval()` / `parseFloat()` для введённых людьми значений.
- Форматы хранения цен и cookie не меняются. Некорректные сохранённые цены, ранее читавшиеся по числовому префиксу, теперь дают ноль; проверьте такие данные перед выкладкой.
