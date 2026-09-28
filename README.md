# ArgonShop версия 2018

> Плагин интернет-магазина на WordPress. Историческая версия 2018 года
> с поддержкой PHP 5.6 и jQuery 2.2.

**Ветка:** `historic/php56-jquery`

---

## 📖 О репозитории

**ArgonShop** — это полноценный интернет-магазин, состоящий из **плагина** и **темы**,
которые работают только в связке:

- **Плагин `argon-shop`** — вся бизнес-логика: каталог, корзина, заказы,
  личный кабинет, скидки, поиск, AJAX, настройки.
- **Тема `bmshop`** — вёрстка и отображение: шаблоны страниц, CSS, JS,
  вызовы функций плагина.

> ⚠️ **Важно:** тема **не самодостаточна**. Она напрямую вызывает функции плагина
> (`as_infocart()`, `view_products_list()`, `getActualPrice()` и др.). Без активированного
> плагина тема не работает.

### Стек

- **PHP 5.6** (без пространств имён, глобальные функции)
- **jQuery 2.2.2** (локальная копия в плагине)
- **WordPress** ≥ 4.9
- **Advanced Custom Fields (ACF)** + аддон **ACF Photo Gallery Field** (обязательно)
- **Библиотеки:** Fancybox 3.3.5, Slick
- **Опционально:** All in One SEO Pack, Contact Form 7

---

## 🗂️ Карта репозитория

```
ArgonShop/
│
├── AI_CONTEXT.md                          # ← Точка входа для ИИ (общая карта)
├── README.md                              # ← Этот файл
│
├── docs/
│   └── AI_DATA_FLOWS.md                   # ← Сквозные сценарии (плагин + тема)
│
├── plugins/
│   └── argon-shop/                        # Плагин интернет-магазина
│       ├── AI_CONTEXT.md                  # ← Подробное описание плагина для ИИ
│       ├── argon-shop.php                 # Точка входа плагина
│       ├── settingPage.php                # Страница настроек магазина
│       ├── docs/
│       │   └── AI_AJAX_REFERENCE.md       # ← Справочник AJAX-экшенов
│       ├── assets/
│       │   ├── admin/
│       │   │   ├── css/                   # fields-catalog.css, order.css
│       │   │   ├── js/                    # admin-script.js, orders.js
│       │   │   └── img/                   # Иконки админки
│       │   └── interface/
│       │       ├── css/                   # interface-style.css
│       │       └── js/                    # jquery.js, site.js, product.js, ...
│       │           └── catalog/           # sort.js
│       └── includes/
│           ├── createPostType.php         # CPT и таксономии
│           ├── siteLine.php               # Замена метки as_homepage в меню
│           ├── admin/                     # Метабоксы, AJAX админки
│           │   ├── additionalFilters.php
│           │   ├── fieldsTaxonomy.php
│           │   ├── fieldsProduct.php
│           │   └── order/
│           │       ├── orderMetabox.php
│           │       ├── orderAjax.php
│           │       └── orderSave.php
│           ├── api/                       # Бизнес-логика
│           │   ├── as_action.php
│           │   ├── check.php
│           │   ├── getSetting.php
│           │   ├── getData/
│           │   │   ├── getData.php
│           │   │   ├── orders.php
│           │   │   ├── catalog.php
│           │   │   ├── product/productLists/  # marked, catalog, history
│           │   │   └── admin/                 # get_characteristics
│           │   └── view/
│           │       ├── product/productsLists/ # productsLists.php
│           │       └── catalog/               # catalogPage.php
│           └── interface/                 # AJAX-обработчики фронта
│               ├── shoppingCart/          # shoppingCart.php, infoCart.php
│               ├── product/               # cardProduct.php
│               ├── catalog/               # sort.php
│               ├── cabinet/               # myCabinet.php, redirectUser.php
│               │   └── userOrders/        # ordersView.php, ordersAjax.php
│               ├── breadcrumbs.php
│               ├── pagenavi.php
│               ├── history.php
│               └── search/                # searchForm, searchAjax, searchGetFilters
│
└── themes/
    └── bmshop/                            # Тема оформления
        ├── AI_CONTEXT.md                  # ← Подробное описание темы для ИИ
        ├── docs/
        │   └── AI_TEMPLATE_MAP.md         # ← Карта URL → шаблон → функции
        ├── functions.php                  # Регистрация меню, стилей, CPT, walker'ов
        ├── style.css                      # Метаданные темы
        ├── header.php, footer.php, sidebar.php
        ├── index.php                      # Главная
        ├── page.php, 404.php, search.php
        ├── archive.php, tag.php, taxonomy.php
        ├── taxonomy-catalog.php           # Каталог (ключевой шаблон)
        ├── single.php                     # Маршрутизация single-*
        ├── single-default.php
        ├── single-aktsii-istra.php
        ├── single-aktsii-moskva.php
        ├── single-product.php             # Карточка товара
        ├── single-shoporder.php
        ├── category-aktsii-{istra,moskva}.php
        ├── categoryArchive-aktsii-{istra,moskva}.php
        ├── archive-product.php, archive-products.php
        ├── aboutCompany.php, contacts.php, addressMap.php
        ├── history.php, myCabinetPage.php, shoppingCartPage.php
        ├── includes/
        │   └── cardProduct/cardProduct.php  # generate_product_card()
        ├── template-parts/
        │   ├── cabinet/                     # account, ordering, setting
        │   ├── cart/                        # quickOrder, person, legalPerson
        │   └── productPage/                 # characteristics
        ├── libs/
        │   ├── fancybox/                    # jquery.fancybox.min.{css,js}
        │   └── slick/                       # slick.{css,min.js}
        └── assets/                          # CSS/JS по блокам
            ├── main.css, controlPanels.css, form.css
            ├── topBlock/                    # Фиксированный верхний блок
            ├── header/                      # Шапка, CF7
            ├── sitebar/                     # Сайдбар
            ├── cabinet/                     # Кабинет
            ├── cart/                        # Корзина
            ├── homePage/                    # Главная
            ├── product/                     # Карточка товара
            ├── aboutCompany/                # О компании
            ├── rubrics/                     # Статьи/акции
            ├── catalog/                     # Каталог
            ├── search/                      # Поиск
            └── footer/                      # Подвал
```

---

## 🚀 Установка

1. Скопируйте `plugins/argon-shop/` в `wp-content/plugins/`.
2. Скопируйте `themes/bmshop/` в `wp-content/themes/`.
3. Активируйте плагин **Argon Shop** в админке WordPress.
4. Активируйте тему **BMShop**.
5. Перейдите в `Настройки → Магазин` и укажите:
   - ID страницы корзины,
   - ID страницы личного кабинета,
   - тип системы скидок,
   - поля форм (быстрая, физлицо, юрлицо).
6. Создайте страницы с шаблонами:
   - «Мой кабинет» → шаблон **Мой кабинет**,
   - «Корзина» → шаблон **shoppingCart**,
   - «История просмотров» → шаблон **История просмотров**,
   - «О компании» → шаблон **О компании**,
   - «Контакты» → шаблон **Контакты**.
7. Настройте меню (`Внешний вид → Меню`):
   - `top_menu` — верхнее меню,
   - `aside_menu` — боковое меню (каталог),
   - `bottom_menu`, `bottom_menu2` — нижние меню.
8. **Установите и настройте ACF + ACF Photo Gallery Field** (см. следующий раздел).
9. Создайте обязательные ACF-поля (см. таблицу ниже).

---

## 🔧 Обязательные плагины для работы с изображениями товара

> 💡 **Рекомендация:** в **современной версии плагина ArgonShop**
> (см. ветку main) галерея изображений товара
> реализована **средствами самого плагина** — без необходимости
> устанавливать ACF Photo Gallery Field отдельно.
>
> Если вы начинаете новый проект — используйте актуальную версию плагина.
> ACF + ACF Photo Gallery Field нужны **только для этой исторической ветки**
> `historic/php56-jquery`.

### 8.1. Advanced Custom Fields (ACF)

**Зачем:** тема `bmshop` использует `get_field()` и `the_field()` для доступа
к дополнительным полям товаров, категорий и страниц. Без ACF часть контента
**не отобразится** (описания, единицы измерения, изображения категорий и т.д.).

**Установка:**
1. `Плагины → Добавить новый → Advanced Custom Fields`.
2. Установить и активировать.

### 8.2. ACF Photo Gallery Field

**Зачем:** в этой исторической версии **галерея изображений товара** реализована
через аддон **ACF Photo Gallery Field**. Именно этот аддон сохраняет изображения
в мета-поле `slider_imgs` в виде **строки с ID вложений через запятую** —
именно в таком формате их читает шаблон `single-product.php`.

**Как это работает в коде темы** (`themes/bmshop/single-product.php`):

```php
$post_imgs = get_post_meta( get_the_ID(), "slider_imgs", true );
$post_imgs = explode(",", $post_imgs);   // "12,34,56" → [12, 34, 56]
$imgs_count = count($post_imgs);
```

**Установка:**
1. Скачайте аддон с официальной страницы:
   - https://www.advancedcustomfields.com/add-ons/photo-gallery-field/
   - или https://wordpress.org/plugins/acf-photo-gallery-field/
2. Установите как обычный плагин и активируйте.
3. Убедитесь, что ACF уже активирован (см. п. 8.1).

**Создание поля для товара:**

| Параметр | Значение |
|----------|----------|
| **Field Label** | Изображения товара |
| **Field Name** | `slider_imgs` ← **обязательно именно так** |
| **Field Type** | `Photo Gallery` |
| **Location Rules** | Post Type == `product` |
| **Return Format** | `Photo Gallery` (по умолчанию — строка с ID через запятую) |

> ⚠️ **Ключевой момент:** имя поля — **`slider_imgs`**. Если назвать его иначе,
> галерея на странице товара не будет отображаться.

### 8.3. Обязательные ACF-поля (полный список)

Помимо галереи, тема использует и другие ACF-поля. Их нужно создать
через `Custom Fields → Add New` (Field Group), привязав к соответствующим
типам записей, таксономиям или страницам.

#### Для товара (`product`)

| Имя поля | Тип | Назначение | Где используется |
|----------|-----|------------|------------------|
| `slider_imgs` | Photo Gallery | Галерея изображений товара | `single-product.php` |
| `unit_product` | Text | Единица измерения (шт, кг, м², …) | `single-product.php`, `cardProduct.php`, `shoppingCartPage.php` |
| `application` | WYSIWYG / Textarea | Применение товара (вкладка) | `single-product.php` |

#### Для таксономии `catalog`

| Имя поля | Тип | Назначение | Где используется |
|----------|-----|------------|------------------|
| `image_catalog` | Image | Изображение категории | `taxonomy-catalog.php`, `sidebar.php`, `archive.php` |
| `catalog_type` | Select (`base` / `request`) | Тип категории: обычная или популярный запрос | `taxonomy-catalog.php`, `get_catalog_terms()` |
| `second_title` | Text | Альтернативный H1 категории | `taxonomy-catalog.php` |
| `second_desc` | WYSIWYG | Дополнительное описание категории | `taxonomy-catalog.php` |
| `bottom_desc` | WYSIWYG | Нижнее описание категории (SEO) | `taxonomy-catalog.php` |

> **Правила привязки для таксономии:** Location Rules → Taxonomy Term == `catalog`.

#### Для страниц (Page)

| Имя поля | Тип | Назначение | Где используется |
|----------|-----|------------|------------------|
| `our_advantages` | WYSIWYG | Текст «Наши преимущества» | `aboutCompany.php` |
| `our_clients` | WYSIWYG | Текст «Наши партнёры» | `aboutCompany.php` |

#### Для записей (Post)

| Имя поля | Тип | Назначение | Где используется |
|----------|-----|------------|------------------|
| `promo_active` | True / False | Активна ли акция | `category-aktsii-moskva.php`, `index.php` |

#### Для CPT `ourclients` (регистрируется темой)

| Имя поля | Тип | Назначение | Где используется |
|----------|-----|------------|------------------|
| `client_link` | URL | Ссылка на сайт клиента | `aboutCompany.php` |

#### Для CPT `slider` (регистрируется плагином)

| Имя поля | Тип | Назначение | Где используется |
|----------|-----|------------|------------------|
| `slide_link` | URL | Ссылка слайда | `index.php` |


---

### Список документов

| Документ | Путь | Назначение |
|----------|------|------------|
| **Корневой контекст** | [`AI_CONTEXT.md`](./AI_CONTEXT.md) | Точка входа: общая карта репозитория, ключевые сущности, технологии |
| **Контекст плагина** | [`plugins/argon-shop/AI_CONTEXT.md`](./plugins/argon-shop/AI_CONTEXT.md) | Подробное описание модулей плагина, API, AJAX, легаси-проблем |
| **Контекст темы** | [`themes/bmshop/AI_CONTEXT.md`](./themes/bmshop/AI_CONTEXT.md) | Подробное описание темы, шаблонов, ресурсов, зависимостей от плагина |
| **Сквозные сценарии** | [`docs/AI_DATA_FLOWS.md`](./docs/AI_DATA_FLOWS.md) | Как данные текут между плагином и темой в ключевых сценариях |
| **Справочник AJAX** | [`plugins/argon-shop/docs/AI_AJAX_REFERENCE.md`](./plugins/argon-shop/docs/AI_AJAX_REFERENCE.md) | Таблица всех AJAX-экшенов: параметры, ответы, JS-вызовы |
| **Карта шаблонов** | [`themes/bmshop/docs/AI_TEMPLATE_MAP.md`](./themes/bmshop/docs/AI_TEMPLATE_MAP.md) | Соответствие «URL → шаблон → ключевые функции» |

### Как пользоваться

1. **Начните с** [`AI_CONTEXT.md`](./AI_CONTEXT.md) — он даст общую картину.
2. **Для работы с плагином** — [`plugins/argon-shop/AI_CONTEXT.md`](./plugins/argon-shop/AI_CONTEXT.md).
3. **Для работы с темой** — [`themes/bmshop/AI_CONTEXT.md`](./themes/bmshop/AI_CONTEXT.md).
4. **Для конкретного сценария** (корзина, поиск, кабинет) — [`docs/AI_DATA_FLOWS.md`](./docs/AI_DATA_FLOWS.md).
5. **Для AJAX** — [`plugins/argon-shop/docs/AI_AJAX_REFERENCE.md`](./plugins/argon-shop/docs/AI_AJAX_REFERENCE.md).
6. **Для URL и шаблонов** — [`themes/bmshop/docs/AI_TEMPLATE_MAP.md`](./themes/bmshop/docs/AI_TEMPLATE_MAP.md).

### Рекомендации для ИИ

- **Никогда не меняй имена функций плагина**, которые вызывает тема, без
  одновременного обновления темы.
- **Не удаляй DOM-селекторы**, которые ищет JS (список — в контексте темы).
- Учитывай, что репозиторий ориентирован на **PHP 5.6** и **jQuery 2.2** —
  не предлагай синтаксис PHP 7+/ES6+ без явного запроса на миграцию.
- Помни о **легаси-проблемах** (отсутствие nonce, `mail()` вместо `wp_mail()`,
  `preg_replace /e`, `each()`, `extract()`) — они перечислены в контекстах.
- **ACF-поля обязательны:** если предлагаешь изменить логику изображений —
  учитывай формат `slider_imgs` (comma-separated ID) в `single-product.php`.

---

## 📋 Требования

| Компонент | Минимум |
|-----------|---------|
| WordPress | 4.9 |
| PHP | 5.6 |
| jQuery | 2.2 (поставляется) |
| MySQL | 5.6 |
| **ACF** | любая актуальная (бесплатная) |
| **ACF Photo Gallery Field** | любая актуальная |

**Протестировано до:** WordPress 6.2, PHP 5.6.

> ⚠️ **Не совместимо с PHP 7.2+** без правок (`preg_replace /e` в
> `themes/bmshop/last_viewed_posts.php`, `each()` в некоторых местах).
> См. раздел «Известные легаси-проблемы» в `AI_CONTEXT.md`.

---

## 🔗 Полезные ссылки

- **Репозиторий:** https://github.com/Argonstudio/ArgonShop/tree/historic/php56-jquery
- **Демо (современная версия):** https://plugin.argon-studio.ru/
- **Автор:** Иван Войтков — http://argon-studio.ru
- **Лицензия:** [GNU GPL v2.0 or later](http://gnu.org)

---

## 📝 Лицензия

```
Argon Shop WordPress Plugin, Copyright 2018 Ivan Voitkov.
BMShop WordPress Theme, Copyright 2018 Ivan Voitkov.

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <https://gnu.org>.
```

---

## 🧾 Примечание об AI-документации

Документы серии `AI_*` в этом репозитории подготовлены **DeepSeek (深度求索)**
в рамках совместной работы над документацией проекта. Они **не являются
официальной документацией автора плагина** и не заменяют её. Цель документов —
помочь ИИ-ассистентам и новым разработчикам быстрее разобраться в архитектуре
и избежать типичных ошибок при работе с легаси-кодом.

При обнаружении расхождений между AI-документацией и фактическим кодом
**приоритет имеет код**.
