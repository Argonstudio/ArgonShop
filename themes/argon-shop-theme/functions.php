<?php
/**
 * Функции темы ArgonShopTheme
 *
 * @package ArgonShopTheme
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 * 
 * Тема ArgonShopTheme тесно связана с плагином Argon Shop.
 * Плагин регистрирует типы записей (product, shoporder, slider),
 * таксономии (catalog, characteristics, wholesalePrice, statusorders)
 * и предоставляет API-функции для работы с магазином.
 * 
 * Подключение дополнительных модулей темы:
 * - /includes/cardProduct/cardProduct.php
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Отключаем автообновления (осознанное решение)
define( 'AUTOMATIC_UPDATER_DISABLED', true );

// ============================================
// НАСТРОЙКА ТЕМЫ
// ============================================

add_action( 'after_setup_theme', 'astheme_after_setup' );

function astheme_after_setup() {
    
    register_nav_menus( array(
        'top_menu' => __( 'Верхнее меню', 'astheme' ),
    ) );
    
    register_nav_menus( array(
        'bottom_menu' => __( 'Нижнее меню', 'astheme' ),
    ) );
    
    register_nav_menus( array(
        'bottom_menu2' => __( 'Нижнее меню2', 'astheme' ),
    ) );
    
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'custom-background' );
    add_theme_support( 'custom-header' );
    add_theme_support( 'title-tag' );
}

// Размеры изображений
add_image_size( 'slider_thumb', 880, 305, true );
add_image_size( 'blog_thumb', 260, 200, true );
add_image_size( 'product_thumb', 280, 130, true );
add_image_size( 'obj_thumb', 180, 120, true );
add_image_size( 'obj_about_thumb', 260, 200, true );

// ============================================
// ПОДКЛЮЧЕНИЕ СКРИПТОВ И СТИЛЕЙ ТЕМЫ
// ============================================

function astheme_scripts() {
    
    $uri = esc_url( get_template_directory_uri() );
    
    /** КЛЮЧЕВЫЕ СТИЛИ */
    
    wp_enqueue_style( 'main-style', $uri . '/assets/css/main.css' );
    wp_enqueue_style( 'main-styleControlPanels', $uri . '/assets/css/controlPanels.css' );
    wp_enqueue_style( 'main-styleForm', $uri . '/assets/css/form.css' );
    
    /** TOP BLOCK */
    
    wp_enqueue_style( 'topBlockStyle-main', $uri . '/assets/css/topBlock/topBlock.css' );
    wp_enqueue_style( 'topBlockStyle-shoppingCart', $uri . '/assets/css/topBlock/shoppingCart.css' );
    wp_enqueue_style( 'topBlockStyle-siteMenu', $uri . '/assets/css/topBlock/siteMenu/siteMenu.css' );
    wp_enqueue_style( 'topBlockStyle-search', $uri . '/assets/css/topBlock/search/topSearch.css' );
    
    wp_enqueue_script(
        'search-toggle',
        $uri . '/assets/js/search-toggle.js',
        array(),
        '1.0.0',
        true
    );

    /** HEADER */
    
    wp_enqueue_style( 'headerStyle-main', $uri . '/assets/css/header/header.css' );
    wp_enqueue_style( 'headerStyle-contactForm', $uri . '/assets/css/header/contactForm/contactForm.css' );
    
    // Модальное окно темы: карта и форма обратной связи.
    wp_enqueue_style(
        'ars-modal-css',
        $uri . '/assets/css/modal.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_script(
        'ars-modal-js',
        $uri . '/assets/js/modal.js',
        array(),
        '1.0.0',
        true
    );
    
    /** SITEBAR */
    
    wp_enqueue_style( 'sitebarStyle-main', $uri . '/assets/css/sitebar/sitebar.css' );
    wp_enqueue_style( 'sitebarStyle-menuCatalog', $uri . '/assets/css/sitebar/menuCatalog/menuCatalog.css' );
    wp_enqueue_style( 'sitebarStyle-markedProducts', $uri . '/assets/css/sitebar/markedProducts/markedProducts.css' );
    wp_enqueue_style( 'sitebarStyle-entryTags', $uri . '/assets/css/sitebar/entryTags/entryTags.css' );
    
    /** CABINET */
    
    wp_enqueue_style( 'cabinetStyle-main', $uri . '/assets/css/cabinet/cabinet.css' );
    wp_enqueue_style( 'cabinetStyle-controlPanel', $uri . '/assets/css/cabinet/controlPanel.css' );
    
    wp_enqueue_style( 'cabinetStyle-account', $uri . '/assets/css/cabinet/account/account.css' );
    
    wp_enqueue_style( 'cabinetStyle-setting', $uri . '/assets/css/cabinet/setting/setting.css' );
    wp_enqueue_style( 'cabinetStyle-orders', $uri . '/assets/css/cabinet/orders/orders.css' );
    
    /** CART */
    
    wp_enqueue_style( 'cartStyle-main', $uri . '/assets/css/cart/cart.css' );
    wp_enqueue_style( 'cartStyle-controlPanel', $uri . '/assets/css/cart/controlPanel.css' );
    wp_enqueue_style( 'cartStyle-cartListProducts', $uri . '/assets/css/cart/cartListProducts.css' );
    wp_enqueue_style( 'cartStyle-totalValue', $uri . '/assets/css/cart/totalValue.css' );
    wp_enqueue_style( 'cartStyle-forms', $uri . '/assets/css/cart/forms/forms.css' );
    
    /** HOMEPAGE */
    
    wp_enqueue_style( 'homePageStyle-main', $uri . '/assets/css/homePage/homePage.css' );
    wp_enqueue_style( 'homePageStyle-slider', $uri . '/assets/css/homePage/slider/homePageSlider.css' );
    wp_enqueue_style( 'homePageStyle-advantages', $uri . '/assets/css/homePage/advantages.css' );
    wp_enqueue_style( 'homePageStyle-catalog', $uri . '/assets/css/homePage/catalog.css' );
    
    /** PRODUCT */
    
    wp_enqueue_style( 'singleProductStyle-main', $uri . '/assets/css/product/singleProduct/product.css' );
    wp_enqueue_style( 'singleProductStyle-singleProductSlider', $uri . '/assets/css/product/singleProduct/singleProductSlider/singleProductSlider.css' );
    wp_enqueue_style( 'singleProductStyle-aboutProducts', $uri . '/assets/css/product/singleProduct/aboutProduct/aboutProducts.css' );
    wp_enqueue_style( 'singleProductStyle-aboutProductsСharacteristics', $uri . '/assets/css/product/singleProduct/aboutProduct/characteristics/characteristics.css' );
    wp_enqueue_style( 'cardProductStyle-main', $uri . '/assets/css/product/cardProduct/cardProduct.css' );
    
    /** О КОМПАНИИ */
    
    wp_enqueue_style( 'aboutCompanyStyle-main', $uri . '/assets/css/aboutCompany.css' );
    
    // Слайдер сертификатов на странице «О компании».
    // Зависит от swiper-js и glightbox-js, подключённых плагином ArgonShop.
    wp_enqueue_script(
        'aboutCompanyScripts-sliderSertificates',
        $uri . '/assets/js/slider-sertificates.js',
        array( 'swiper-js', 'glightbox-js' ),
        '1.0.0',
        true
    );
    
    /** АКЦИИ, СТАТЬИ, НОВОСТИ */
    
    wp_enqueue_style( 'rubricsStyle-main', $uri . '/assets/css/rubrics/rubrics.css' );
    wp_enqueue_style( 'rubricsStyle-categoryRubrics', $uri . '/assets/css/rubrics/categoryRubrics/categoryRubrics.css' );
    
    /** CATALOG */
    
    wp_enqueue_style( 'catalogStyle-catalogPage', $uri . '/assets/css/catalog/catalogPage/catalogPage.css' );
    
    wp_enqueue_style( 'catalogStyle-catalogPageRequest', $uri . '/assets/css/catalog/catalogPage/request/request.css' );
    
    wp_enqueue_style( 'catalogStyle-catalogPageSort', $uri . '/assets/css/catalog/catalogPage/sort/sort.css' );
    wp_enqueue_style( 'catalogStyle-catalogPageProductsList', $uri . '/assets/css/catalog/catalogPage/productsList/productsList.css' );
    
    /** SEARCH */
    
    wp_enqueue_style( 'searchStyle-searchForm', $uri . '/assets/css/search/searchForm.css' );
    
    /** FOOTER */
    
    wp_enqueue_style( 'footerStyle-main', $uri . '/assets/css/footer.css' );
}

add_action( 'wp_enqueue_scripts', 'astheme_scripts' );

// ============================================
// ТИПЫ ЗАПИСЕЙ ТЕМЫ
// ============================================

add_action( 'init', 'astheme_create_post_type' );

function astheme_create_post_type() {
    
    register_post_type( 'ourclients',
        array(
            'labels' => array(
                'name'               => 'Наши клиенты',
                'singular_name'      => 'Клиент',
                'add_new'            => 'Добавить клиента',
                'add_new_item'       => 'Добавить клиента',
                'edit'               => 'Редактировать',
                'edit_item'          => 'Редактировать клиента',
                'new_item'           => 'Новый клиент',
                'view'               => 'Просмотр',
                'view_item'          => 'Посмотреть клиента',
                'search_items'       => 'Поиск клиента',
                'not_found'          => 'Клиентов пока нет',
                'not_found_in_trash' => 'Клиенты не найдены в корзине',
                'parent'             => 'Parent клиент',
            ),
            'public'              => true,
            'show_in_menu'        => true,
            'exclude_from_search' => true,
            'menu_icon'           => 'dashicons-images-alt',
            'menu_position'       => 13,
            'supports'            => array( 'title', 'thumbnail' ),
            'has_archive'         => true,
            'query_posts'         => false,
        )
    );
    
    register_post_type( 'sertificates',
        array(
            'labels' => array(
                'name'               => 'Сертификаты',
                'singular_name'      => 'Сертификат',
                'add_new'            => 'Добавить сертификат',
                'add_new_item'       => 'Добавить сертификат',
                'edit'               => 'Редактировать',
                'edit_item'          => 'Редактировать сертификат',
                'new_item'           => 'Новый сертификат',
                'view'               => 'Просмотр',
                'view_item'          => 'Посмотреть сертификат',
                'search_items'       => 'Поиск сертификата',
                'not_found'          => 'Сертификатов пока нет',
                'not_found_in_trash' => 'Сертификаты не найдены в корзине',
                'parent'             => 'Parent сертификат',
            ),
            'public'              => true,
            'show_in_menu'        => true,
            'exclude_from_search' => true,
            'menu_icon'           => 'dashicons-images-alt',
            'menu_position'       => 13,
            'supports'            => array( 'title', 'thumbnail' ),
            'has_archive'         => true,
            'query_posts'         => false,
        )
    );
}

// ============================================
// ХЕЛПЕРЫ
// ============================================

function post_is_in_descendant_category( $cats, $_post = null ) {
    
    foreach ( (array) $cats as $cat ) {
        // get_term_children() принимает только int ID
        $descendants = get_term_children( (int) $cat, 'category' );
        
        if ( $descendants && in_category( $descendants, $_post ) ) {
            return true;
        }
    }
    
    return false;
}

function astheme_top_menu() {
    
    if ( has_nav_menu( 'top_menu' ) ) {
        
        $settings = array(
            'theme_location'  => 'top_menu',
            'container'       => '',
            'container_class' => '',
            'container_id'    => '',
            'menu_class'      => 'menu_top',
            'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
            'walker'          => new astheme_walker_nav_topmenu,
        );
        
        wp_nav_menu( $settings );
        
    } else {
        echo '<span class="no-menu">Пожалуйста создайте меню в шапке сайта, <a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '" target="_blank">Внешний вид &gt; Меню</a></span>';
    }
}

function astheme_category_menu() {
    
    if ( has_nav_menu( 'top_menu' ) ) {
        
        $settings = array(
            'theme_location'  => 'aside_menu',
            'container'       => '',
            'container_class' => '',
            'container_id'    => '',
            'menu_class'      => 'mobile-category-menu',
            'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
            'walker'          => new astheme_walker_nav_topmenu,
        );
        
        wp_nav_menu( $settings );
        
    } else {
        echo '<span class="no-menu">Пожалуйста создайте меню в шапке сайта, <a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '" target="_blank">Внешний вид &gt; Меню</a></span>';
    }
}



// ============================================
// EXCERPT
// ============================================

function astheme_excerpt_length() {
    return 100;
}
add_filter( 'excerpt_length', 'astheme_excerpt_length', 999 );

function astheme_excerpt_more() {
    return '...';
}
add_filter( 'excerpt_more', 'astheme_excerpt_more' );



// ============================================
// РЕДИРЕКТ КАТАЛОГА
// ============================================

add_action( 'template_redirect', 'true_catalog_redirect' );

function true_catalog_redirect() {
    
    $taxonomy_name = 'catalog';
    
    // PHP 8.5: проверяем $_SERVER['REQUEST_URI']
    $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
    
    if ( $request_uri === '' ) {
        return;
    }
    
    if ( strpos( $request_uri, $taxonomy_name ) === false ) {
        return;
    }
    
    if ( strpos( $request_uri, 'price_from' ) !== false ) {
        return;
    }
    
    $term = get_term_by( 'slug', get_query_var( 'term' ), $taxonomy_name );
    
    if ( $term && ! is_wp_error( $term ) && $term->parent !== 0 ) {
        
        $term_parent = get_term_by( 'term_taxonomy_id', $term->parent, $taxonomy_name );
        
        if ( $term_parent && ! is_wp_error( $term_parent ) ) {
            
            if ( strpos( $request_uri, $term_parent->slug ) === false ) {
                
                wp_safe_redirect(
                    site_url() . '/' . $taxonomy_name . '/' . $term_parent->slug . '/' . $term->slug,
                    301
                );
                exit;
            }
        }
    }
}



// ============================================
// ОТКЛЮЧЕНИЕ EMOJI
// ============================================

remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_styles', 'print_emoji_styles' ); 
remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
remove_filter( 'comment_text_rss', 'wp_staticize_emoji' ); 
remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
add_filter( 'tiny_mce_plugins', 'disable_wp_emojis_in_tinymce' );

function disable_wp_emojis_in_tinymce( $plugins ) {
    
    if ( is_array( $plugins ) ) {
        return array_diff( $plugins, array( 'wpemoji' ) );
    }
    
    return array();
}

// ============================================
// ПОДКЛЮЧЕНИЕ ДОПОЛНИТЕЛЬНЫХ МОДУЛЕЙ ТЕМЫ
// ============================================

require get_template_directory() . '/includes/cardProduct/cardProduct.php';