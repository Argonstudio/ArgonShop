<?php
/**
 * Plugin Name:       Argon Shop
 * Plugin URI:        https://github.com/Argonstudio/ArgonShop
 * Description:       Высокооптимизированный плагин интернет-магазина для WordPress с AJAX-поиском и кастомной шаблонизацией корзины и ЛК.
 * Version:           1.0.0
 * Author:            Иван Войтков (Ivan Voitkov)
 * Author URI:        https://argon-studio.ru
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       argon-shop
 * 
 * Copyright (C) 2018-2026 Иван Войтков (voit.ne@gmail.com)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ============================================
// КОНСТАНТЫ ПЛАГИНА
// ============================================

define( 'ARGON_SHOP_VERSION', '1.0.0' );
define( 'ARGON_SHOP_PATH', plugin_dir_path( __FILE__ ) );
define( 'ARGON_SHOP_URL', plugin_dir_url( __FILE__ ) );

// ============================================
// ЛОГИРОВАНИЕ ОШИБОК (только в режиме разработки)
// ============================================

if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'DOING_AJAX' ) && DOING_AJAX ) {
    @ini_set( 'display_errors', 0 );
    @ini_set( 'log_errors', 1 );
}

// ============================================
// API ПЛАГИНА
// ============================================

// Функция для фильтра, преобразующего name цен в числа для правильной сортировки
function sort_terms_clause( $orderby, $args, $taxonomies ) {
    return 't.name+0';
}

// Страница настроек магазина
require_once ARGON_SHOP_PATH . 'settingPage.php';

// Функции действия
require_once ARGON_SHOP_PATH . 'includes/api/as_action.php';

// Функции проверки
require_once ARGON_SHOP_PATH . 'includes/api/check.php';

// Функции получения данных различными модулями
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
// VIEW
// ============================================

require_once ARGON_SHOP_PATH . 'includes/api/view/product/productsLists/productsLists.php';
require_once ARGON_SHOP_PATH . 'includes/api/view/catalog/catalogPage.php';

// ============================================
// VIEW: СЛАЙДЕРЫ
// ============================================

// Слайдер на главной странице
require_once ARGON_SHOP_PATH . 'includes/interface/slider/sliderMain.php';

// Слайдер изображений товара
require_once ARGON_SHOP_PATH . 'includes/interface/slider/sliderProduct.php';

// ============================================
// ПОДКЛЮЧЕНИЕ СКРИПТОВ И СТИЛЕЙ
// ============================================

/**
 * Админские стили и скрипты
 * 
 * ГДЕ: Страницы админки с товарами и заказами
 * ЧТО: Подключает CSS и JS для работы метабоксов
 */
add_action( 'admin_enqueue_scripts', 'add_admin_styleScript' );
function add_admin_styleScript() {
    
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
    // МЕТАБОКС «ГАЛЕРЕЯ» (только на странице товара)
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
    }
}

/**
 * Меняем роль нового пользователя на "подписчик"
 * 
 * ВАЖНО: Это осознанное решение для защиты магазина —
 * чтобы зарегистрированный пользователь не получил доступ к админке.
 */
add_filter( 'pre_option_default_role', function( $default_role ) {
    return 'subscriber';
} );

/**
 * Фронтенд-стили и скрипты.
 *
 * ГДЕ: На сайте (фронтенд).
 *
 * ЧТО ПОДКЛЮЧАЕТ:
 * 1. Функциональный CSS плагина (interface-style.css).
 * 2. ES-модуль ars-main (type="module") — точка входа
 *    современного JavaScript-кода ArgonShop.
 *
 * JQUERY:
 * Плагин использует встроенную jQuery WordPress, если её
 * подключают тема или другие плагины (например, для Slick
 * и Fancybox). Своя кастомная jQuery 2.2.2 больше не
 * подключается — она устарела и заменена встроенной версией.
 *
 * ЛОКАЛИЗАЦИЯ:
 * arsWpAjax — глобальная переменная для ES-модулей,
 * содержит url admin-ajax.php, nonce и URL страницы корзины.
 *
 */
add_action( 'wp_enqueue_scripts', 'add_interface_styleScript' );
function add_interface_styleScript() {

    // ============================================
    // БИБЛИОТЕКИ (встроены в плагин)
    // ============================================
    //
    // Swiper — современный слайдер
    // GLightbox — лёгкий лайтбокс
    //
    // Библиотеки лежат в папке assets/vendor/ плагина и доступны
    // на всех страницах фронтенда. Тема может использовать
    // глобальные объекты Swiper и GLightbox без собственного
    // подключения. Если тема хочет гарантировать порядок
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
    // Только начальные состояния элементов, управляемых JS.
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
    // нативный fetch и DOM API. Не требует jQuery.
    //
    // Атрибут type="module" добавляется через фильтр
    // ars_add_module_type() ниже.

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
        'cartUrl' => get_cartPageURL() ? get_cartPageURL() : '',
    ) );

    // ============================================
    // ВРЕМЕННАЯ ЗАГЛУШКА ДЛЯ СТАРЫХ INLINE-СКРИПТОВ
    // ============================================
    //
    // Некоторые PHP-файлы плагина ещё содержат inline-JS
    // в wp_footer и читают window.myajax.nonce. Пока они
    // не удалены, нужно оставить переменную myajax, иначе
    // AJAX-запросы из этих скриптов будут падать.
    //
    // После удаления всех inline-скриптов из PHP этот блок
    // можно убрать целиком.

    wp_register_script( 'ars-legacy-stub', false, array(), ARGON_SHOP_VERSION, true );
    wp_enqueue_script( 'ars-legacy-stub' );

    wp_localize_script( 'ars-legacy-stub', 'myajax', array(
        'url'   => admin_url( 'admin-ajax.php' ),
        'nonce' => wp_create_nonce( 'argon_shop_nonce' ),
    ) );
}

/**
 * Добавляет атрибут type="module" к скрипту ars-main.
 *
 * ГДЕ: Автоматически при выводе тега <script> в футере.
 *
 * ЗАЧЕМ: WordPress не умеет подключать ES-модули напрямую.
 * Единственный способ — отфильтровать готовый HTML тега и
 * добавить атрибут вручную.
 *
 * ВАЖНО: фильтр не трогает другие скрипты — только ars-main.
 *
 * @param  string $tag    — готовый HTML тега <script>
 * @param  string $handle — идентификатор скрипта
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

// Дополнительные поля товаров (админка)
require_once ARGON_SHOP_PATH . 'includes/admin/fieldsProduct.php';

// Метабокс «Галерея» товара
require_once ARGON_SHOP_PATH . 'includes/admin/galleryMetabox.php';

// ============================================
// ИНТЕРФЕЙС: КОРЗИНА
// ============================================

// Корзина покупок (AJAX-обработчики)
require_once ARGON_SHOP_PATH . 'includes/interface/shoppingCart/shoppingCart.php';

// Виджет корзины (число товаров и сумма)
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

// Личный кабинет
require_once ARGON_SHOP_PATH . 'includes/interface/cabinet/myCabinet.php';

// Вывод заказов пользователя
require_once ARGON_SHOP_PATH . 'includes/interface/cabinet/userOrders/ordersView.php';

// AJAX-фильтры заказов
require_once ARGON_SHOP_PATH . 'includes/interface/cabinet/userOrders/ordersAjax.php';

// Переадресация пользователя при входе
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

// AJAX-функционал страницы заказа
require_once ARGON_SHOP_PATH . 'includes/admin/order/orderAjax.php';

// Сохранение заказа
require_once ARGON_SHOP_PATH . 'includes/admin/order/orderSave.php';

// ============================================
// ПРОЧИЕ МОДУЛИ
// ============================================

// Сеть сайтов (мультирегиональность)
require_once ARGON_SHOP_PATH . 'includes/siteLine.php';
