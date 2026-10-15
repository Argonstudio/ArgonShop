---
description: Добавить новое AJAX-действие по правилам проекта (docs/ai/AI_EXTENDING.md, раздел 1)
argument-hint: <имя_действия> <что оно делает>
---

Задача: добавить AJAX-действие. Описание от пользователя: $ARGUMENTS

Перед кодом прочитай `docs/ai/AI_EXTENDING.md` (раздел 1 «Новое AJAX-действие» и раздел 12 «Чек-лист перед коммитом») и `docs/ai/AI_AJAX_REFERENCE.md` (раздел «Соглашения» — правила для всех обработчиков). Если имя действия или назначение неясны, или действие нужно и гостям, и админке — сначала спроси пользователя.

Сделай по шагам:

1. **PHP-обработчик** в подходящем файле `plugins/argon-shop/includes/interface/<функция>/` (или `includes/admin/…` для админки):
   - `add_action( 'wp_ajax_<action>', … )`; `wp_ajax_nopriv_<action>` — только если действие нужно гостям; для админки — никогда;
   - функция с префиксом `as_` и `@since` следующей версии (`docs/ai/AI_VERSIONING.md`);
   - первая строка — `check_ajax_referer( 'argon_shop_nonce', 'nonce' );` (заказы в админке — `argon_shop_order_nonce`);
   - сразу после nonce — проверка прав, которая **останавливает** запрос: `if ( ! current_user_can( 'manage_options' ) ) { wp_die( __( 'Insufficient permissions', 'argon-shop' ) ); }` или проверка `userID === get_current_user_id()`;
   - входные данные — `absint()` / `sanitize_*()`; цены — только `as_get_product_price()`, никогда из запроса;
   - ответ — `echo wp_json_encode( $data );` (или экранированный HTML) и `wp_die();`; `wp_send_json()` в проекте не используется; никакого другого вывода до ответа;
   - тексты — на английском в `__()` с доменом `argon-shop`; комментарии — на русском.
2. **JS**: вызов через `arsApi.post` / `arsApi.postJson` в модуле `assets/js/interface/modules/`; без jQuery; видимый текст только через `t( 'key' )` — ключ добавь в `I18N_DEFAULTS` (`core/config.js`) и в `argon_shop_js_i18n()`. Новый модуль — по разделу 2 `AI_EXTENDING.md` (импорт и `init()` в `main.js`).
3. **Переводы**: обнови `.pot`, `ru_RU`, `fr_FR` (`docs/ai/AI_I18N.md`); нет терминала — дай пользователю таблицу «английский → русский → французский».
4. **Документация**: добавь действие в `docs/ai/AI_AJAX_REFERENCE.md` (файл, функция, nonce, JS, параметры, ответ, права); новые DOM-селекторы — в `AI.md` (раздел 5); строку в `CHANGELOG.md` → `## [Unreleased]` → «Добавлено». Версию не повышай. Скажи пользователю, что изменения нужно перенести и в английскую ветку `main`.
5. **Проверка**: `php tests/static-check.php` (хук запустит его сам после правки PHP); предложи проверку в `tests/smoke-test.php` (`as_smoke_check()`), если действие меняет данные.

В конце пройди чек-лист раздела 12 `AI_EXTENDING.md` и коротко перечисли изменённые файлы.
