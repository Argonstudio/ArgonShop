# AI_EXTENDING.md — как добавить фичу, не сломав архитектуру

Продолжение [AI.md](../../AI.md). Пошаговые инструкции для типичных расширений.

## 1. Новое AJAX-действие

**Шаги:**

1. **PHP-обработчик.** В подходящем файле `includes/interface/<фича>/`:

```php
add_action( 'wp_ajax_my_action', 'my_action_callback' );
add_action( 'wp_ajax_nopriv_my_action', 'my_action_callback' );

function my_action_callback() {
    check_ajax_referer( 'argon_shop_nonce', 'nonce' );

    // ... логика ...

    echo wp_json_encode( $result );
    wp_die();
}
```

Для админских действий — только `wp_ajax_<action>` + `current_user_can('manage_options')`.

2. **JS-вызов.** Через `arsApi`:

```js
const data = await arsApi.postJson( 'my_action', { param1: 'value' });
```

3. **Регистрация действия в `AI_AJAX_REFERENCE.md`.** Обнови таблицу.

4. **Если действие не требует nonce** — добавь в `config.skipNonceActions`.

---

## 2. Новый ES-модуль

**Шаги:**

1. **Файл.** `assets/js/interface/modules/my-feature.js`:

```js
'use strict';
import { arsApi } from '../core/api.js';
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

4. **Проверка контракта.** Если модуль слушает DOM-селекторы — они должны быть в `AI.md` (раздел 5) и `AI_TEMPLATE_MAP.md`.

**Чего не делать:**

- Не использовать jQuery.
- Не писать `document.ready` — модуль подключается как `type="module"` (deferred, DOM уже готов).
- Не забывать `#initialized` — модуль вызывается на всех страницах.

---

## 3. Новое мета-поле товара

**Шаги:**

1. **Вывод в админке.** В `includes/admin/fieldsProduct.php`, функция `mainParameters_metabox`:

```php
$myField = get_post_meta( $post->ID, '_myField', true );
?>
<li class="advanced-field">
    <div class="span-advanced-fields">Название</div>
    <input type="text" name="_myField" value="<?php echo esc_attr( $myField ); ?>">
</li>
```

2. **Сохранение.** В `save_detailed_fields`:

```php
if ( isset( $_POST['_myField'] ) ) {
    $myField = sanitize_text_field( wp_unslash( $_POST['_myField'] ) );
    as_update_meta( $post_id, '_myField', $myField );
}
```

`as_update_meta` — из `includes/api/as_action.php`. Пустое значение удаляет мету.

3. **Использование на фронте.** В шаблоне:

```php
$myField = get_post_meta( get_the_ID(), '_myField', true );
```

4. **Документация.** Добавь в `AI.md` (раздел 5) и в `AI_TEMPLATE_MAP.md`.

**Формат мета-ключа:** `_snake_case` с ведущим подчёркиванием (как `_price`, `_article`). Это скрывает поле от стандартного блока «Произвольные поля» в админке.

---

## 4. Новый шаблон страницы

**Шаги:**

1. **Файл.** `themes/argon-shop-theme/my-page.php`:

```php
<?php
/**
 * Template Name: Моя страница
 *
 * @package ArgonShopTheme
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); ?>

<section id="out">
    <div class="row">
        <?php get_sidebar(); ?>
        <main class="block-content">
            <?php if ( function_exists( 'kama_breadcrumbs' ) ) kama_breadcrumbs( ' » ' ); ?>
            <?php while ( have_posts() ) : the_post(); ?>
                <h1 class="title"><?php the_title(); ?></h1>
                <?php the_content(); ?>
            <?php endwhile; ?>
        </main>
    </div>
</section>

<?php get_footer(); ?>
```

2. **Создать страницу в админке** и присвоить шаблон.

3. **Стили.** Если нужны — в `assets/css/`, подключить в `functions.php` через `wp_enqueue_style`.

4. **Запись в `AI_TEMPLATE_MAP.md`.**

---

## 5. Новый тип записи

**Шаги:**

1. **Регистрация.** В `includes/createPostType.php` или `functions.php` темы (в зависимости от того, что за CPT):

```php
add_action( 'init', 'register_my_post_type' );
function register_my_post_type() {
    register_post_type( 'mypost', array(
        'labels' => array( 'name' => 'Мои записи', 'singular_name' => 'Запись' ),
        'public' => true,
        'show_in_menu' => true,
        'supports' => array( 'title', 'editor', 'thumbnail' ),
        'has_archive' => true,
    ));
}
```

2. **Шаблоны.** `archive-mypost.php`, `single-mypost.php` в теме.

3. **Запись в `AI_ARCHITECTURE.md`** (раздел 6).

---

## 6. Новое правило скидок

Сложная фича. Правила:

- **Не создавать свой расчёт цены.** Всегда через `getActualPrice` (в `includes/api/getData/getData.php`).
- **Расширять функцию, а не дублировать.** Если новая логика скидок — добавить внутрь `getActualPrice` условия, а не писать рядом функцию.
- **Синхронизировать все места вызова.** Скидка должна одинаково работать:
  - На странице товара (`product.js` → `wholesalePrice_shoppingCart`).
  - В корзине (`amountProducts_shoppingCart`).
  - При оформлении заказа (`submitCart_shoppingCart_callback`).
  - В письмах (`emailTextsGenerator`).
  - В админке заказа (`orderSave.php`).

**Если хотя бы одно место пропущено — скидка будет считаться по-разному.**

---

## 7. Новое поле формы заказа

**Шаги:**

1. **Настройки.** Админка → Настройки → Магазин → «Данные пользователя» → в textarea нужного типа (`quickLine`, `personLine`, `legalPersonLine`) добавить строку:

```
myField[Название поля,placeholder,50]
```

Формат: `ID[Имя,Placeholder,class]`. `class` — `50` или `100` (ширина поля).

2. **Всё остальное сделает `show_user_fields`.** Оно выведет поле в формах корзины, оно сохранит в `accountData` при регистрации, оно отобразится в письме.

3. **Если поле обязательное** — добавить в массив `$required` в соответствующем шаблоне `template-parts/cart/*.php`.

---

## 8. Что не надо добавлять

| Фича | Почему |
|---|---|
| Собственный ORM | `WP_Query` и метаполя — идиоматично для WP |
| React / Vue | На витрине не даст ничего, убьёт Vanilla JS |
| Свой REST API | Если нужен — стандартный `register_rest_route`, не своя авторизация |
| Второй набор шаблонов | Темы для магазина хватает одной |
| jQuery на фронте | Регресс |

---

## 9. Чек-лист перед коммитом

- [ ] PHP: `if ( ! defined( 'ABSPATH' ) ) exit;` в начале файла.
- [ ] PHP: экранирование вывода (`esc_html`, `esc_attr`, `esc_url`).
- [ ] PHP: `absint` / `sanitize_*` на входящих данных.
- [ ] AJAX: `check_ajax_referer`.
- [ ] AJAX: `wp_die()` в конце.
- [ ] JS: модуль через `export const arsXxx = new ArsXxx()`.
- [ ] JS: импорт в `main.js`, вызов в `initApp()`.
- [ ] JS: без jQuery.
- [ ] CSS: только оформление в теме, функциональные состояния — в `interface-style.css`.
- [ ] Документация: обновил `AI_*.md`, если добавил контракт.
