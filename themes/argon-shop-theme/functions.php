<?php
/**
 * ArgonShopTheme theme functions
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * ============================================================
 * INTERACTION WITH THE ARGON SHOP PLUGIN
 * ============================================================
 * 
 * The ArgonShopTheme theme is tightly coupled with the Argon Shop plugin.
 * The plugin registers post types (product, shoporder, slider),
 * taxonomies (catalog, characteristics, wholesalePrice, statusorders)
 * and provides API functions for working with the store.
 * 
 * Additional theme modules included:
 * - /includes/cardProduct/cardProduct.php
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ============================================
// THEME SETUP
// ============================================

/**
 * Loads theme translations. Theme translation files are named by locale only:
 * languages/ru_RU.po|mo, languages/fr_FR.po|mo (WordPress rule for themes).
 *
 * @since 1.1.0
 */
function astheme_load_textdomain() {
    load_theme_textdomain( 'argon-shop-theme', get_template_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'astheme_load_textdomain', 0 );

add_action( 'after_setup_theme', 'astheme_after_setup' );

function astheme_after_setup() {
    
    register_nav_menus( array(
        'top_menu' => __( 'Header menu', 'argon-shop-theme' ),
    ) );
    
    register_nav_menus( array(
        'bottom_menu' => __( 'Footer menu', 'argon-shop-theme' ),
    ) );
    
    register_nav_menus( array(
        'bottom_menu2' => __( 'Footer menu 2', 'argon-shop-theme' ),
    ) );
    
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'custom-background' );
    add_theme_support( 'custom-header' );
    add_theme_support( 'title-tag' );
}

// Image sizes
add_image_size( 'slider_thumb', 880, 305, true );
add_image_size( 'blog_thumb', 260, 200, true );
add_image_size( 'product_thumb', 280, 130, true );
add_image_size( 'obj_thumb', 180, 120, true );
add_image_size( 'obj_about_thumb', 260, 200, true );

// ============================================
// THEME SCRIPTS AND STYLES
// ============================================

function astheme_scripts() {
    
    $uri = esc_url( get_template_directory_uri() );
    // Theme version from style.css: browsers re-download CSS/JS after a theme update.
    $ver = wp_get_theme( get_template() )->get( 'Version' );
    
    /** KEY STYLES */
    
    wp_enqueue_style( 'main-style', $uri . '/assets/css/main.css', array(), $ver );
    wp_enqueue_style( 'main-styleControlPanels', $uri . '/assets/css/controlPanels.css', array(), $ver );
    wp_enqueue_style( 'main-styleForm', $uri . '/assets/css/form.css', array(), $ver );
    
    /** TOP BLOCK */
    
    wp_enqueue_style( 'topBlockStyle-main', $uri . '/assets/css/topBlock/topBlock.css', array(), $ver );
    wp_enqueue_style( 'topBlockStyle-shoppingCart', $uri . '/assets/css/topBlock/shoppingCart.css', array(), $ver );
    wp_enqueue_style( 'topBlockStyle-siteMenu', $uri . '/assets/css/topBlock/siteMenu/siteMenu.css', array(), $ver );
    wp_enqueue_style( 'topBlockStyle-search', $uri . '/assets/css/topBlock/search/topSearch.css', array(), $ver );
    
    wp_enqueue_script(
        'search-toggle',
        $uri . '/assets/js/search-toggle.js',
        array(),
        $ver,
        true
    );

    /** HEADER */
    
    wp_enqueue_style( 'headerStyle-main', $uri . '/assets/css/header/header.css', array(), $ver );
    wp_enqueue_style( 'headerStyle-contactForm', $uri . '/assets/css/header/contactForm/contactForm.css', array(), $ver );
    
    // Theme modal window: map and feedback form.
    wp_enqueue_style(
        'ars-modal-css',
        $uri . '/assets/css/modal.css',
        array(),
        $ver
    );

    wp_enqueue_script(
        'ars-modal-js',
        $uri . '/assets/js/modal.js',
        array(),
        $ver,
        true
    );

    // Modal window texts (modal.js falls back to English).
    wp_localize_script( 'ars-modal-js', 'arsModalI18n', array(
        'close'     => __( 'Close', 'argon-shop-theme' ),
        'loading'   => __( 'Loading…', 'argon-shop-theme' ),
        'loadError' => __( 'Loading failed. Please try again.', 'argon-shop-theme' ),
    ) );
    
    /** SITEBAR */
    
    wp_enqueue_style( 'sitebarStyle-main', $uri . '/assets/css/sitebar/sitebar.css', array(), $ver );
    wp_enqueue_style( 'sitebarStyle-menuCatalog', $uri . '/assets/css/sitebar/menuCatalog/menuCatalog.css', array(), $ver );
    wp_enqueue_style( 'sitebarStyle-markedProducts', $uri . '/assets/css/sitebar/markedProducts/markedProducts.css', array(), $ver );
    wp_enqueue_style( 'sitebarStyle-entryTags', $uri . '/assets/css/sitebar/entryTags/entryTags.css', array(), $ver );
    
    /** CABINET */
    
    wp_enqueue_style( 'cabinetStyle-main', $uri . '/assets/css/cabinet/cabinet.css', array(), $ver );
    wp_enqueue_style( 'cabinetStyle-controlPanel', $uri . '/assets/css/cabinet/controlPanel.css', array(), $ver );
    
    wp_enqueue_style( 'cabinetStyle-account', $uri . '/assets/css/cabinet/account/account.css', array(), $ver );
    
    wp_enqueue_style( 'cabinetStyle-setting', $uri . '/assets/css/cabinet/setting/setting.css', array(), $ver );
    wp_enqueue_style( 'cabinetStyle-orders', $uri . '/assets/css/cabinet/orders/orders.css', array(), $ver );
    
    /** CART */
    
    wp_enqueue_style( 'cartStyle-main', $uri . '/assets/css/cart/cart.css', array(), $ver );
    wp_enqueue_style( 'cartStyle-controlPanel', $uri . '/assets/css/cart/controlPanel.css', array(), $ver );
    wp_enqueue_style( 'cartStyle-cartListProducts', $uri . '/assets/css/cart/cartListProducts.css', array(), $ver );
    wp_enqueue_style( 'cartStyle-totalValue', $uri . '/assets/css/cart/totalValue.css', array(), $ver );
    wp_enqueue_style( 'cartStyle-forms', $uri . '/assets/css/cart/forms/forms.css', array(), $ver );
    
    /** HOMEPAGE */
    
    wp_enqueue_style( 'homePageStyle-main', $uri . '/assets/css/homePage/homePage.css', array(), $ver );
    wp_enqueue_style( 'homePageStyle-slider', $uri . '/assets/css/homePage/slider/homePageSlider.css', array(), $ver );
    wp_enqueue_style( 'homePageStyle-advantages', $uri . '/assets/css/homePage/advantages.css', array(), $ver );
    wp_enqueue_style( 'homePageStyle-catalog', $uri . '/assets/css/homePage/catalog.css', array(), $ver );
    
    /** PRODUCT */
    
    wp_enqueue_style( 'singleProductStyle-main', $uri . '/assets/css/product/singleProduct/product.css', array(), $ver );
    wp_enqueue_style( 'singleProductStyle-singleProductSlider', $uri . '/assets/css/product/singleProduct/singleProductSlider/singleProductSlider.css', array(), $ver );
    wp_enqueue_style( 'singleProductStyle-aboutProducts', $uri . '/assets/css/product/singleProduct/aboutProduct/aboutProducts.css', array(), $ver );
    wp_enqueue_style( 'singleProductStyle-aboutProductsCharacteristics', $uri . '/assets/css/product/singleProduct/aboutProduct/characteristics/characteristics.css', array(), $ver );
    wp_enqueue_style( 'cardProductStyle-main', $uri . '/assets/css/product/cardProduct/cardProduct.css', array(), $ver );
    
    /** ABOUT THE COMPANY */
    
    wp_enqueue_style( 'aboutCompanyStyle-main', $uri . '/assets/css/aboutCompany.css', array(), $ver );
    
    // Certificate slider on the "About the company" page.
    // Depends on swiper-js and glightbox-js enqueued by the ArgonShop plugin.
    wp_enqueue_script(
        'aboutCompanyScripts-sliderSertificates',
        $uri . '/assets/js/slider-sertificates.js',
        array( 'swiper-js', 'glightbox-js' ),
        $ver,
        true
    );
    
    /** PROMOTIONS, ARTICLES, NEWS */
    
    wp_enqueue_style( 'rubricsStyle-main', $uri . '/assets/css/rubrics/rubrics.css', array(), $ver );
    wp_enqueue_style( 'rubricsStyle-categoryRubrics', $uri . '/assets/css/rubrics/categoryRubrics/categoryRubrics.css', array(), $ver );
    
    /** CATALOG */
    
    wp_enqueue_style( 'catalogStyle-catalogPage', $uri . '/assets/css/catalog/catalogPage/catalogPage.css', array(), $ver );
    
    wp_enqueue_style( 'catalogStyle-catalogPageRequest', $uri . '/assets/css/catalog/catalogPage/request/request.css', array(), $ver );
    
    wp_enqueue_style( 'catalogStyle-catalogPageSort', $uri . '/assets/css/catalog/catalogPage/sort/sort.css', array(), $ver );
    wp_enqueue_style( 'catalogStyle-catalogPageProductsList', $uri . '/assets/css/catalog/catalogPage/productsList/productsList.css', array(), $ver );
    
    /** SEARCH */
    
    wp_enqueue_style( 'searchStyle-searchForm', $uri . '/assets/css/search/searchForm.css', array(), $ver );
    
    /** FOOTER */
    
    wp_enqueue_style( 'footerStyle-main', $uri . '/assets/css/footer.css', array(), $ver );
}

add_action( 'wp_enqueue_scripts', 'astheme_scripts' );

// ============================================
// THEME POST TYPES
// ============================================

add_action( 'init', 'astheme_create_post_type' );

function astheme_create_post_type() {
    
    register_post_type( 'ourclients',
        array(
            'labels' => array(
                'name'               => __( 'Our clients', 'argon-shop-theme' ),
                'singular_name'      => __( 'Client', 'argon-shop-theme' ),
                'add_new'            => __( 'Add client', 'argon-shop-theme' ),
                'add_new_item'       => __( 'Add client', 'argon-shop-theme' ),
                'edit'               => __( 'Edit', 'argon-shop-theme' ),
                'edit_item'          => __( 'Edit client', 'argon-shop-theme' ),
                'new_item'           => __( 'New client', 'argon-shop-theme' ),
                'view'               => __( 'View', 'argon-shop-theme' ),
                'view_item'          => __( 'View client', 'argon-shop-theme' ),
                'search_items'       => __( 'Search clients', 'argon-shop-theme' ),
                'not_found'          => __( 'No clients yet', 'argon-shop-theme' ),
                'not_found_in_trash' => __( 'No clients found in Trash', 'argon-shop-theme' ),
                'parent'             => __( 'Parent client', 'argon-shop-theme' ),
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
                'name'               => __( 'Certificates', 'argon-shop-theme' ),
                'singular_name'      => __( 'Certificate', 'argon-shop-theme' ),
                'add_new'            => __( 'Add certificate', 'argon-shop-theme' ),
                'add_new_item'       => __( 'Add certificate', 'argon-shop-theme' ),
                'edit'               => __( 'Edit', 'argon-shop-theme' ),
                'edit_item'          => __( 'Edit certificate', 'argon-shop-theme' ),
                'new_item'           => __( 'New certificate', 'argon-shop-theme' ),
                'view'               => __( 'View', 'argon-shop-theme' ),
                'view_item'          => __( 'View certificate', 'argon-shop-theme' ),
                'search_items'       => __( 'Search certificates', 'argon-shop-theme' ),
                'not_found'          => __( 'No certificates yet', 'argon-shop-theme' ),
                'not_found_in_trash' => __( 'No certificates found in Trash', 'argon-shop-theme' ),
                'parent'             => __( 'Parent certificate', 'argon-shop-theme' ),
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
// HELPERS
// ============================================

function post_is_in_descendant_category( $cats, $_post = null ) {
    
    foreach ( (array) $cats as $cat ) {
        // get_term_children() accepts only an int ID
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
        echo '<span class="no-menu">' . sprintf(
            /* translators: %s: URL of the Appearance > Menus screen. */
            __( 'Please create a header menu: <a href="%s" target="_blank">Appearance &gt; Menus</a>', 'argon-shop-theme' ),
            esc_url( admin_url( 'nav-menus.php' ) )
        ) . '</span>';
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
        echo '<span class="no-menu">' . sprintf(
            /* translators: %s: URL of the Appearance > Menus screen. */
            __( 'Please create a header menu: <a href="%s" target="_blank">Appearance &gt; Menus</a>', 'argon-shop-theme' ),
            esc_url( admin_url( 'nav-menus.php' ) )
        ) . '</span>';
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
// CATALOG REDIRECT
// ============================================

add_action( 'template_redirect', 'true_catalog_redirect' );

function true_catalog_redirect() {
    
    $taxonomy_name = 'catalog';
    
    // PHP 8.5: check $_SERVER['REQUEST_URI']
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
// DISABLING EMOJI
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
// STORE CURRENCY (since 1.1.0)
// ============================================
//
// The currency is chosen in Settings → Store (ArgonShop plugin).
// The wrappers keep the theme working when the plugin is disabled.

/**
 * Amount with the store currency symbol.
 *
 * @since 1.1.0
 * @param string $amount_html Escaped amount or HTML with the amount (e.g. "<span>120</span>").
 * @param string $unit        Unit of measure (not escaped), optional.
 * @return string HTML
 */
function astheme_price_html( $amount_html, $unit = '' ) {
    if ( function_exists( 'as_price_html' ) ) {
        return as_price_html( $amount_html, $unit );
    }
    return $amount_html . ( $unit !== '' ? ' /' . esc_html( $unit ) : '' );
}

/**
 * Store currency symbol (empty string if the plugin is disabled).
 *
 * @since 1.1.0
 * @return string Symbol (not escaped)
 */
function astheme_currency_symbol() {
    return function_exists( 'as_get_currency_symbol' ) ? as_get_currency_symbol() : '';
}

/**
 * Store currency symbol position: 'before' or 'after' the amount.
 *
 * @since 1.1.0
 * @return string
 */
function astheme_currency_position() {
    return function_exists( 'as_get_currency_position' ) ? as_get_currency_position() : 'after';
}

// ============================================
// ADDITIONAL THEME MODULES
// ============================================

require get_template_directory() . '/includes/cardProduct/cardProduct.php';