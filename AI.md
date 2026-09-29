# AI.md — индекс документации для ИИ-ассистентов

> Этот файл — точка входа. Он короткий. Детальная информация — в
> специализированных файлах, чтобы не перегружать контекстное окно LLM.

## Карта документов

| Файл | О чём |
|------|-------|
| `AI.md` | этот файл — индекс, ключевые контракты, жёсткие запреты |
| `AI_CONTEXT.md` | общая карта репозитория: сущности, технологии, структура |
| `plugins/argon-shop/AI_CONTEXT.md` | плагин: модули, API, AJAX, админка, легаси |
| `themes/bmshop/AI_CONTEXT.md` | тема: шаблоны, ресурсы, DOM-селекторы |
| `docs/AI_DATA_FLOWS.md` | 7 ключевых сценариев данных (корзина, заказ, поиск, кабинет) |
| `plugins/argon-shop/docs/AI_AJAX_REFERENCE.md` | все AJAX-экшены с параметрами и ответами |
| `themes/bmshop/docs/AI_TEMPLATE_MAP.md` | шаблоны темы ↔ функции плагина |
| `AGENTS.md` | указатель для Cursor / Codex / Claude Code |

## 1. Что это

**ArgonShop (ветка `historic/php56-jquery`)** — плагин интернет-магазина
для WordPress + тема `bmshop`. Монорепозиторий. **Историческая версия 2018 года.**
Не продакшн — учебный / портфолио-стенд.

| Параметр | Значение |
|----------|----------|
| Стек | PHP 5.6, WordPress 4.9+, jQuery 2.2.2, CSS3 |
| Лицензия | GPL-2.0-or-later |
| Репозиторий | https://github.com/Argonstudio/ArgonShop/tree/historic/php56-jquery |
| ACF | Advanced Custom Fields + ACF Photo Gallery Field (обязательно) |
| Библиотеки | Fancybox 3.3.5, Slick |
| Опционально | All in One SEO Pack, Contact Form 7 |

## 2. Жёсткие запреты

1. **Не менять имена функций плагина**, которые вызывает тема
   (`as_infocart()`, `view_products_list()`, `getActualPrice()` и др.) —
   без одновременного обновления темы.
2. **Не удалять DOM-селекторы**, которые ищет JS (список — в
   `themes/bmshop/AI_CONTEXT.md`).
3. **Не менять формат метаполей и куки** — есть уже залитые данные
   (`slider_imgs`, `_price`, `productsShoppingCart`, `AS_History`,
   `AS_CatalogSorting`).
4. **Не предлагать синтаксис PHP 7+ / ES6+** без явного запроса на миграцию.
5. **Не использовать `mail()`** — только `wp_mail()` (в новом коде).
6. **ACF-поля обязательны** — без них часть контента не отрендерится.

## 3. Ключевые сущности

### Типы записей (CPT)

`product` · `shoporder` · `slider` · `ourclients` · `sertificates`

### Таксономии

`catalog` · `characteristics` · `wholesalePrice` · `statusorders` ·
`category` · `post_tag`

### Опции WordPress

`settingShop` (настройки магазина) · `counterOrders` (счётчик заказов)

### Cookie

| Cookie | Назначение |
|--------|-----------|
| `productsShoppingCart` | Корзина |
| `AS_History` | История просмотров |
| `AS_CatalogSorting` | Сортировка каталога |

### Ключевые AJAX-экшены (полный список — в `AI_AJAX_REFERENCE.md`)

Клиентская часть: `addProducts_shoppingCart` · `amountProducts_shoppingCart` ·
`deleteProducts_shoppingCart` · `deleteAllProducts_shoppingCart` ·
`submitCart_shoppingCart` · `sortingCatalog` · `getAjaxSearchResult` ·
`cabinetEditEmailPassword` · `cabinetAccountSave` · `ordersFiltersStatus` ·
`addCookieHistory_history`

Админка: `update_characteristics` · `as_searchSuitableProduct_order` ·
`as_addProduct_order` · `as_addFields_order` · `as_admin_recount_order`

## 4. Известные легаси-проблемы

- `preg_replace` с модификатором `/e` в `themes/bmshop/last_viewed_posts.php`
  (удалён в PHP 7).
- `each()` в `foreach` (удалён в PHP 8).
- `extract()` в настройках и функциях вывода.
- `mail()` вместо `wp_mail()` в `submitCart_shopping_cart_callback`.
- Отсутствие nonce в некоторых AJAX-обработчиках исторической ветки.
- Глобальные функции без префиксов (риск коллизий с другими плагинами).

## 5. Как ИИ следует работать с этим репозиторием

1. **Сначала** прочитай `AI_CONTEXT.md` (корень) — он даст общую картину.
2. **Для работы с плагином** — `plugins/argon-shop/AI_CONTEXT.md`.
3. **Для работы с темой** — `themes/bmshop/AI_CONTEXT.md`.
4. **Для конкретного сценария** — `docs/AI_DATA_FLOWS.md`.
5. **Для AJAX** — `plugins/argon-shop/docs/AI_AJAX_REFERENCE.md`.
6. **Для URL и шаблонов** — `themes/bmshop/docs/AI_TEMPLATE_MAP.md`.

**Помни:** тема и плагин жёстко связаны. Изменения в API плагина ломают тему.
Изменения в DOM ломают JS. Изменения в формате метаполей ломают данные.

---

*Подготовлено при участии DeepSeek.*
