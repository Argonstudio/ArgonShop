<?php
/**
 * Plugin Name:       Argon Shop
 * Plugin URI:        https://github.com/Argonstudio/ArgonShop
 * Description:       Highly optimized online store plugin for WordPress with AJAX search and fully theme-controlled cart and customer account templates.
 * Version:           1.1.0
 * Requires at least: 7.1
 * Requires PHP:      8.3
 * Author:            Ivan Voitkov
 * Author URI:        https://argon-studio.ru
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       argon-shop
 * Domain Path:       /languages
 * 
 * Copyright (C) 2018-2026 Ivan Voitkov (voit.ne@gmail.com)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ============================================
// КОНСТАНТЫ ПЛАГИНА
// ============================================

define( 'ARGON_SHOP_VERSION', '1.1.0' );
define( 'ARGON_SHOP_FILE', __FILE__ );
define( 'ARGON_SHOP_PATH', plugin_dir_path( __FILE__ ) );
define( 'ARGON_SHOP_URL', plugin_dir_url( __FILE__ ) );

/**
 * Загружает переводы плагина: languages/argon-shop-{locale}.mo (ru_RU, fr_FR).
 * Переводы из wp-content/languages/plugins/ имеют приоритет, поэтому сайт может их переопределить.
 *
 * @since 1.1.0
 */
function as_load_textdomain() {
    load_plugin_textdomain( 'argon-shop', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'as_load_textdomain', 0 );

// ============================================
// ЖУРНАЛ ОШИБОК (только в режиме разработки)
// ============================================

if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'DOING_AJAX' ) && DOING_AJAX ) {
    @ini_set( 'display_errors', 0 );
    @ini_set( 'log_errors', 1 );
}

// ============================================
// API ПЛАГИНА
// ============================================

// Функция-фильтр, преобразующая названия цен в числа для корректной сортировки
function as_sort_terms_clause( $orderby, $args, $taxonomies ) {
    return 't.name+0';
}

// Страница настроек магазина
require_once ARGON_SHOP_PATH . 'settingPage.php';

// Установка, миграции данных, деактивация
require_once ARGON_SHOP_PATH . 'includes/install.php';

// Старые имена функций (до 1.1.0) — обёртки совместимости для сторонних тем
require_once ARGON_SHOP_PATH . 'includes/legacy-functions.php';

// Функции действий
require_once ARGON_SHOP_PATH . 'includes/api/as_action.php';

// Функции проверок
require_once ARGON_SHOP_PATH . 'includes/api/check.php';

// Функции получения данных для различных модулей
require_once ARGON_SHOP_PATH . 'includes/api/getData/getData.php';
require_once ARGON_SHOP_PATH . 'includes/api/getData/orders.php';
require_once ARGON_SHOP_PATH . 'includes/api/getData/catalog.php';

// Галерея изображений товара
require_once ARGON_SHOP_PATH . 'includes/api/getData/gallery.php';

require_once ARGON_SHOP_PATH . 'includes/api/getData/product/productLists/marked.php';
require_once ARGON_SHOP_PATH . 'includes/api/getData/product/productLists/catalog.php';
require_once ARGON_SHOP_PATH . 'includes/api/getData/product/productLists/history.php';

require_once ARGON_SHOP_PATH . 'includes/api/getData/admin/get_characteristics.php';

// Функции получения настроек
require_once ARGON_SHOP_PATH . 'includes/api/getSetting.php';

// ============================================
// VIEW (ВЫВОД)
// ============================================

require_once ARGON_SHOP_PATH . 'includes/api/view/product/productsLists/productsLists.php';
require_once ARGON_SHOP_PATH . 'includes/api/view/catalog/catalogPage.php';

// ============================================
// VIEW: СЛАЙДЕРЫ
// ============================================

// Слайдер главной страницы
require_once ARGON_SHOP_PATH . 'includes/interface/slider/sliderMain.php';

// Слайдер изображений товара
require_once ARGON_SHOP_PATH . 'includes/interface/slider/sliderProduct.php';

// ============================================
// ПОДКЛЮЧЕНИЕ СКРИПТОВ И СТИЛЕЙ
// ============================================

/**
 * Стили и скрипты админки
 * 
 * ГДЕ: страницы админки с товарами и заказами
 * ЧТО: подключает CSS и JS для метабоксов
 */
add_action( 'admin_enqueue_scripts', 'as_add_admin_styleScript' );
function as_add_admin_styleScript() {
    
    wp_enqueue_style(
        'style-admin',
        ARGON_SHOP_URL . 'assets/css/admin/fields-catalog.css',
        array(),
        ARGON_SHOP_VERSION
    );
    
    wp_enqueue_style(
        'order-style-admin',
        ARGON_SHOP_URL . 'assets/css/admin/order.css',
        array(),
        ARGON_SHOP_VERSION
    );
    
    wp_enqueue_script(
        'script-admin',
        ARGON_SHOP_URL . 'assets/js/admin/modules/admin-script.js',
        array(),
        ARGON_SHOP_VERSION,
        true
    );

    wp_enqueue_script(
        'orders-admin',
        ARGON_SHOP_URL . 'assets/js/admin/modules/orders.js',
        array( 'jquery' ),
        ARGON_SHOP_VERSION,
        true
    );
    
    // ============================================
    // МЕТАБОКС «ГАЛЕРЕЯ» (только на экране редактирования товара)
    // ============================================

    $screen = get_current_screen();

    if ( $screen && $screen->post_type === 'product' && $screen->base === 'post' ) {

        wp_enqueue_style(
            'ars-gallery-css',
            ARGON_SHOP_URL . 'assets/css/admin/gallery.css',
            array(),
            ARGON_SHOP_VERSION
        );

        wp_enqueue_media();
        wp_enqueue_script( 'jquery-ui-sortable' );

        wp_enqueue_script(
            'ars-gallery-js',
            ARGON_SHOP_URL . 'assets/js/admin/modules/gallery.js',
            array( 'jquery', 'jquery-ui-sortable' ),
            ARGON_SHOP_VERSION,
            true
        );

        // Тексты интерфейса галереи (при их отсутствии gallery.js использует английские).
        wp_localize_script( 'ars-gallery-js', 'arsGalleryI18n', array(
            'selectImages' => __( 'Select images', 'argon-shop' ),
            'addToGallery' => __( 'Add to gallery', 'argon-shop' ),
            'removeImage'  => __( 'Remove image', 'argon-shop' ),
        ) );
    }
}

/**
 * Смена роли нового пользователя на «подписчик»
 * 
 * ВАЖНО: это осознанное решение для защиты магазина —
 * чтобы зарегистрированный пользователь не получил доступ к админке.
 */
add_filter( 'pre_option_default_role', function( $default_role ) {
    return 'subscriber';
} );

/**
 * Стили и скрипты фронтенда.
 *
 * ГДЕ: на сайте (фронтенд).
 *
 * ЧТО ПОДКЛЮЧАЕТ:
 * 1. Функциональный CSS плагина (interface-style.css).
 * 2. ES-модуль ars-main (type="module") — точку входа
 *    современного JavaScript-кода ArgonShop.
 *
 * JQUERY:
 * Плагин использует встроенный в WordPress jQuery, если тема
 * или другие плагины его подключают (например, для Slick
 * и Fancybox). Собственный jQuery 2.2.2 больше не
 * подключается — он устарел и заменён встроенной версией.
 *
 * ЛОКАЛИЗАЦИЯ:
 * arsWpAjax — глобальная переменная для ES-модулей,
 * содержит URL admin-ajax.php, nonce, URL страницы корзины
 * и переведённые строки интерфейса (i18n, см. argon_shop_js_i18n()).
 *
 */
add_action( 'wp_enqueue_scripts', 'as_add_interface_styleScript' );
function as_add_interface_styleScript() {

    // ============================================
    // БИБЛИОТЕКИ (поставляются с плагином)
    // ============================================
    //
    // Swiper — современный слайдер
    // GLightbox — лёгкий лайтбокс
    //
    // Библиотеки лежат в папке плагина assets/vendor/ и доступны
    // на каждой странице фронтенда. Тема может использовать
    // глобальные объекты Swiper и GLightbox, не подключая
    // их самостоятельно. Если теме нужно гарантировать порядок
    // загрузки, она указывает хэндлы как зависимости:
    //
    //     wp_enqueue_script( 'my-script', ..., array( 'swiper-js' ), ... );

    wp_enqueue_style(
        'swiper-css',
        ARGON_SHOP_URL . 'assets/vendor/swiper/swiper-bundle.min.css',
        array(),
        ARGON_SHOP_VERSION
    );

    wp_enqueue_script(
        'swiper-js',
        ARGON_SHOP_URL . 'assets/vendor/swiper/swiper-bundle.min.js',
        array(),
        ARGON_SHOP_VERSION,
        true
    );

    wp_enqueue_style(
        'glightbox-css',
        ARGON_SHOP_URL . 'assets/vendor/glightbox/glightbox.min.css',
        array(),
        ARGON_SHOP_VERSION
    );

    wp_enqueue_script(
        'glightbox-js',
        ARGON_SHOP_URL . 'assets/vendor/glightbox/glightbox.min.js',
        array(),
        ARGON_SHOP_VERSION,
        true
    );

    // ============================================
    // ФУНКЦИОНАЛЬНЫЙ CSS ПЛАГИНА
    // ============================================
    //
    // Только начальные состояния элементов, которыми управляет JS.
    // Всё оформление магазина находится в теме.

    wp_enqueue_style(
        'style-interface',
        ARGON_SHOP_URL . 'assets/css/interface/interface-style.css',
        array(),
        ARGON_SHOP_VERSION
    );

    // ============================================
    // ES-МОДУЛЬ (ArgonShop 2.0)
    // ============================================
    //
    // Подключается без зависимостей: внутри использует только
    // нативный fetch и DOM API. jQuery не требуется.
    //
    // Атрибут type="module" добавляет
    // фильтр ars_add_module_type() ниже.

    wp_enqueue_script(
        'ars-main',
        ARGON_SHOP_URL . 'assets/js/interface/main.js',
        array(),
        ARGON_SHOP_VERSION,
        true
    );

    // Локализация для нового модуля.
    // config.js читает window.arsWpAjax.

    wp_localize_script( 'ars-main', 'arsWpAjax', array(
        'url'     => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'argon_shop_nonce' ),
        'cartUrl' => as_get_cartPageURL() ? as_get_cartPageURL() : '',
        'i18n'    => argon_shop_js_i18n(),
    ) );

    // ============================================
    // ВРЕМЕННАЯ ЗАГЛУШКА ДЛЯ СТАРЫХ ВСТРОЕННЫХ СКРИПТОВ
    // ============================================
    //
    // Некоторые PHP-файлы плагина всё ещё содержат встроенный JS
    // в wp_footer и читают window.myajax.nonce. Пока они
    // не удалены, переменная myajax должна остаться, иначе
    // AJAX-запросы из этих скриптов не будут работать.
    //
    // Когда все встроенные скрипты будут удалены из PHP, этот блок
    // можно удалить целиком.

    wp_register_script( 'ars-legacy-stub', false, array(), ARGON_SHOP_VERSION, true );
    wp_enqueue_script( 'ars-legacy-stub' );

    wp_localize_script( 'ars-legacy-stub', 'myajax', array(
        'url'   => admin_url( 'admin-ajax.php' ),
        'nonce' => wp_create_nonce( 'argon_shop_nonce' ),
    ) );
}

/**
 * Переведённые строки интерфейса для ES-модулей фронтенда.
 *
 * Ключи должны совпадать с I18N_DEFAULTS в assets/js/interface/core/config.js,
 * где лежат английские значения по умолчанию. Подстановки следуют синтаксису printf
 * (%d, %s, %1$s) и подставляются функцией t() на стороне JS.
 *
 * @since 1.1.0
 * @return array<string, string>
 */
function argon_shop_js_i18n() {
    $strings = array(
        'serverError'        => __( 'Server error', 'argon-shop' ),
        'dataNotSaved'       => __( 'Error: the data was not saved', 'argon-shop' ),
        'requestError'       => __( 'Request failed', 'argon-shop' ),
        /* translators: %d: максимальное количество файлов. */
        'tooManyFiles'       => __( 'You can attach no more than %d files.', 'argon-shop' ),
        /* translators: 1: имя файла, 2: максимальный размер в мегабайтах. */
        'fileTooBig'         => __( 'The file "%1$s" exceeds %2$s MB.', 'argon-shop' ),
        /* translators: %d: количество загруженных файлов. */
        'filesUploaded'      => __( 'Files uploaded: %d', 'argon-shop' ),
        'cartEmpty'          => __( 'Cart is empty', 'argon-shop' ),
        'productNotRemoved'  => __( 'Server error, the product was not removed', 'argon-shop' ),
        'productsNotRemoved' => __( 'Server error, the products were not removed', 'argon-shop' ),
        'changesNotAccepted' => __( 'Server error, the changes were not accepted', 'argon-shop' ),
        'orderNotSent'       => __( 'Server error: the order was not sent', 'argon-shop' ),
        'messageSent'        => __( 'Your message has been sent', 'argon-shop' ),
        'searching'          => __( 'Searching...', 'argon-shop' ),
    );

    /**
     * Фильтрует строки интерфейса, передаваемые ES-модулям фронтенда.
     *
     * @since 1.1.0
     * @param array<string, string> $strings Ключ => переведённая строка.
     */
    return apply_filters( 'argon_shop_js_i18n', $strings );
}

/**
 * Добавляет атрибут type="module" к скрипту ars-main.
 *
 * ГДЕ: автоматически при выводе тега <script> в подвале.
 *
 * ЗАЧЕМ: WordPress не умеет подключать ES-модули напрямую.
 * Единственный способ — отфильтровать готовый HTML тега и
 * добавить атрибут вручную.
 *
 * ВАЖНО: фильтр не трогает другие скрипты — только ars-main.
 *
 * @param  string $tag    — готовый HTML тега <script>
 * @param  string $handle — хэндл скрипта
 * @return string         — изменённый HTML
 */
add_filter( 'script_loader_tag', 'ars_add_module_type', 10, 2 );
function ars_add_module_type( $tag, $handle ) {

    if ( $handle !== 'ars-main' ) {
        return $tag;
    }

    return str_replace( '<script ', '<script type="module" ', $tag );
}

// ============================================
// ТИПЫ ЗАПИСЕЙ И ТАКСОНОМИИ
// ============================================

require_once ARGON_SHOP_PATH . 'includes/createPostType.php';

// ============================================
// МОДУЛИ ПЛАГИНА
// ============================================

// Дополнительные фильтры (админка)
require_once ARGON_SHOP_PATH . 'includes/admin/additionalFilters.php';

// Дополнительные поля таксономий (админка)
require_once ARGON_SHOP_PATH . 'includes/admin/fieldsTaxonomy.php';

// Дополнительные поля товара (админка)
require_once ARGON_SHOP_PATH . 'includes/admin/fieldsProduct.php';

// Метабокс «Галерея» товара
require_once ARGON_SHOP_PATH . 'includes/admin/galleryMetabox.php';

// ============================================
// ИНТЕРФЕЙС: КОРЗИНА
// ============================================

// Корзина (AJAX-обработчики)
require_once ARGON_SHOP_PATH . 'includes/interface/shoppingCart/shoppingCart.php';

// Виджет корзины (количество товаров и сумма)
require_once ARGON_SHOP_PATH . 'includes/interface/shoppingCart/infoCart.php';

// ============================================
// ИНТЕРФЕЙС: ТОВАРЫ И КАТАЛОГ
// ============================================

// Карточки товаров
require_once ARGON_SHOP_PATH . 'includes/interface/product/cardProduct.php';

// Сортировка каталога
require_once ARGON_SHOP_PATH . 'includes/interface/catalog/sort.php';

// ============================================
// ИНТЕРФЕЙС: ЛИЧНЫЙ КАБИНЕТ
// ============================================

// Личный кабинет пользователя
require_once ARGON_SHOP_PATH . 'includes/interface/cabinet/myCabinet.php';

// Вывод заказов пользователя
require_once ARGON_SHOP_PATH . 'includes/interface/cabinet/userOrders/ordersView.php';

// AJAX-фильтры заказов
require_once ARGON_SHOP_PATH . 'includes/interface/cabinet/userOrders/ordersAjax.php';

// Редирект пользователя при входе
require_once ARGON_SHOP_PATH . 'includes/interface/cabinet/redirectUser.php';

// ============================================
// ИНТЕРФЕЙС: НАВИГАЦИЯ
// ============================================

// Хлебные крошки
require_once ARGON_SHOP_PATH . 'includes/interface/breadcrumbs.php';

// Пагинация
require_once ARGON_SHOP_PATH . 'includes/interface/pagenavi.php';

// ============================================
// ИНТЕРФЕЙС: ПОИСК
// ============================================

// Вывод формы поиска
require_once ARGON_SHOP_PATH . 'includes/interface/search/searchForm.php';

// Языковые правила поиска (раскладки клавиатуры, нормализация слов)
require_once ARGON_SHOP_PATH . 'includes/interface/search/searchLanguage.php';

// Фильтр результатов на странице /?s=
require_once ARGON_SHOP_PATH . 'includes/interface/search/searchGetFilters.php';

// AJAX-события поиска
require_once ARGON_SHOP_PATH . 'includes/interface/search/searchAjax.php';

// ============================================
// ИНТЕРФЕЙС: ПРОЧЕЕ
// ============================================

// История просмотров
require_once ARGON_SHOP_PATH . 'includes/interface/history.php';

// ============================================
// АДМИНКА: ЗАКАЗЫ
// ============================================

// Метабоксы заказа
require_once ARGON_SHOP_PATH . 'includes/admin/order/orderMetabox.php';

// AJAX страницы заказа
require_once ARGON_SHOP_PATH . 'includes/admin/order/orderAjax.php';

// Сохранение заказа
require_once ARGON_SHOP_PATH . 'includes/admin/order/orderSave.php';

// ============================================
// ПРОЧИЕ МОДУЛИ
// ============================================

// Сеть сайтов (мультирегиональность)
require_once ARGON_SHOP_PATH . 'includes/siteLine.php';
