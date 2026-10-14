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

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ============================================
// PLUGIN CONSTANTS
// ============================================

define( 'ARGON_SHOP_VERSION', '1.1.0' );
define( 'ARGON_SHOP_FILE', __FILE__ );
define( 'ARGON_SHOP_PATH', plugin_dir_path( __FILE__ ) );
define( 'ARGON_SHOP_URL', plugin_dir_url( __FILE__ ) );

/**
 * Loads plugin translations: languages/argon-shop-{locale}.mo (ru_RU, fr_FR).
 * Translations from wp-content/languages/plugins/ take priority, so a site can override them.
 *
 * @since 1.1.0
 */
function as_load_textdomain() {
    load_plugin_textdomain( 'argon-shop', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'as_load_textdomain', 0 );

// ============================================
// ERROR LOGGING (development mode only)
// ============================================

if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'DOING_AJAX' ) && DOING_AJAX ) {
    @ini_set( 'display_errors', 0 );
    @ini_set( 'log_errors', 1 );
}

// ============================================
// PLUGIN API
// ============================================

// Filter function that converts price names to numbers for correct sorting
function as_sort_terms_clause( $orderby, $args, $taxonomies ) {
    return 't.name+0';
}

// Store settings page
require_once ARGON_SHOP_PATH . 'settingPage.php';

// Installation, data migrations, deactivation
require_once ARGON_SHOP_PATH . 'includes/install.php';

// Old function names (before 1.1.0) — compatibility wrappers for third-party themes
require_once ARGON_SHOP_PATH . 'includes/legacy-functions.php';

// Action functions
require_once ARGON_SHOP_PATH . 'includes/api/as_action.php';

// Check functions
require_once ARGON_SHOP_PATH . 'includes/api/check.php';

// Data retrieval functions for various modules
require_once ARGON_SHOP_PATH . 'includes/api/getData/getData.php';
require_once ARGON_SHOP_PATH . 'includes/api/getData/orders.php';
require_once ARGON_SHOP_PATH . 'includes/api/getData/catalog.php';

// Product image gallery
require_once ARGON_SHOP_PATH . 'includes/api/getData/gallery.php';

require_once ARGON_SHOP_PATH . 'includes/api/getData/product/productLists/marked.php';
require_once ARGON_SHOP_PATH . 'includes/api/getData/product/productLists/catalog.php';
require_once ARGON_SHOP_PATH . 'includes/api/getData/product/productLists/history.php';

require_once ARGON_SHOP_PATH . 'includes/api/getData/admin/get_characteristics.php';

// Settings retrieval functions
require_once ARGON_SHOP_PATH . 'includes/api/getSetting.php';

// ============================================
// VIEW
// ============================================

require_once ARGON_SHOP_PATH . 'includes/api/view/product/productsLists/productsLists.php';
require_once ARGON_SHOP_PATH . 'includes/api/view/catalog/catalogPage.php';

// ============================================
// VIEW: SLIDERS
// ============================================

// Home page slider
require_once ARGON_SHOP_PATH . 'includes/interface/slider/sliderMain.php';

// Product image slider
require_once ARGON_SHOP_PATH . 'includes/interface/slider/sliderProduct.php';

// ============================================
// ENQUEUEING SCRIPTS AND STYLES
// ============================================

/**
 * Admin styles and scripts
 * 
 * WHERE: Admin pages with products and orders
 * WHAT: Enqueues CSS and JS for the meta boxes
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
    // "GALLERY" META BOX (product edit screen only)
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

        // Interface texts of the gallery (gallery.js falls back to English).
        wp_localize_script( 'ars-gallery-js', 'arsGalleryI18n', array(
            'selectImages' => __( 'Select images', 'argon-shop' ),
            'addToGallery' => __( 'Add to gallery', 'argon-shop' ),
            'removeImage'  => __( 'Remove image', 'argon-shop' ),
        ) );
    }
}

/**
 * Change the role of a new user to "subscriber"
 * 
 * IMPORTANT: This is a deliberate decision to protect the store —
 * so that a registered user does not get access to the admin area.
 */
add_filter( 'pre_option_default_role', function( $default_role ) {
    return 'subscriber';
} );

/**
 * Frontend styles and scripts.
 *
 * WHERE: On the site (frontend).
 *
 * WHAT IT ENQUEUES:
 * 1. The plugin's functional CSS (interface-style.css).
 * 2. The ars-main ES module (type="module") — the entry point
 *    of the modern ArgonShop JavaScript code.
 *
 * JQUERY:
 * The plugin uses WordPress's bundled jQuery if the theme
 * or other plugins enqueue it (for example, for Slick
 * and Fancybox). The custom jQuery 2.2.2 is no longer
 * enqueued — it was outdated and replaced with the bundled version.
 *
 * LOCALIZATION:
 * arsWpAjax — global variable for the ES modules,
 * contains the admin-ajax.php URL, the nonce, the cart page URL
 * and the translated UI strings (i18n, see argon_shop_js_i18n()).
 *
 */
add_action( 'wp_enqueue_scripts', 'as_add_interface_styleScript' );
function as_add_interface_styleScript() {

    // ============================================
    // LIBRARIES (bundled with the plugin)
    // ============================================
    //
    // Swiper — modern slider
    // GLightbox — lightweight lightbox
    //
    // The libraries live in the plugin's assets/vendor/ folder and are available
    // on every frontend page. The theme can use the
    // global Swiper and GLightbox objects without enqueueing
    // them itself. If the theme wants to guarantee the loading
    // order, it lists the handles as dependencies:
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
    // PLUGIN FUNCTIONAL CSS
    // ============================================
    //
    // Only the initial states of JS-controlled elements.
    // All store styling lives in the theme.

    wp_enqueue_style(
        'style-interface',
        ARGON_SHOP_URL . 'assets/css/interface/interface-style.css',
        array(),
        ARGON_SHOP_VERSION
    );

    // ============================================
    // ES MODULE (ArgonShop 2.0)
    // ============================================
    //
    // Enqueued without dependencies: internally it uses only
    // native fetch and the DOM API. Does not require jQuery.
    //
    // The type="module" attribute is added by the
    // ars_add_module_type() filter below.

    wp_enqueue_script(
        'ars-main',
        ARGON_SHOP_URL . 'assets/js/interface/main.js',
        array(),
        ARGON_SHOP_VERSION,
        true
    );

    // Localization for the new module.
    // config.js reads window.arsWpAjax.

    wp_localize_script( 'ars-main', 'arsWpAjax', array(
        'url'     => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'argon_shop_nonce' ),
        'cartUrl' => as_get_cartPageURL() ? as_get_cartPageURL() : '',
        'i18n'    => argon_shop_js_i18n(),
    ) );

    // ============================================
    // TEMPORARY STUB FOR OLD INLINE SCRIPTS
    // ============================================
    //
    // Some plugin PHP files still contain inline JS
    // in wp_footer and read window.myajax.nonce. Until they
    // are removed, the myajax variable must stay, otherwise
    // AJAX requests from those scripts will fail.
    //
    // Once all inline scripts are removed from PHP, this block
    // can be deleted entirely.

    wp_register_script( 'ars-legacy-stub', false, array(), ARGON_SHOP_VERSION, true );
    wp_enqueue_script( 'ars-legacy-stub' );

    wp_localize_script( 'ars-legacy-stub', 'myajax', array(
        'url'   => admin_url( 'admin-ajax.php' ),
        'nonce' => wp_create_nonce( 'argon_shop_nonce' ),
    ) );
}

/**
 * Translated UI strings for the frontend ES modules.
 *
 * The keys must match I18N_DEFAULTS in assets/js/interface/core/config.js,
 * where the English fallbacks live. Placeholders follow printf syntax
 * (%d, %s, %1$s) and are substituted by t() on the JS side.
 *
 * @since 1.1.0
 * @return array<string, string>
 */
function argon_shop_js_i18n() {
    $strings = array(
        'serverError'        => __( 'Server error', 'argon-shop' ),
        'dataNotSaved'       => __( 'Error: the data was not saved', 'argon-shop' ),
        'requestError'       => __( 'Request failed', 'argon-shop' ),
        /* translators: %d: maximum number of files. */
        'tooManyFiles'       => __( 'You can attach no more than %d files.', 'argon-shop' ),
        /* translators: 1: file name, 2: maximum size in megabytes. */
        'fileTooBig'         => __( 'The file "%1$s" exceeds %2$s MB.', 'argon-shop' ),
        /* translators: %d: number of uploaded files. */
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
     * Filters the UI strings passed to the frontend ES modules.
     *
     * @since 1.1.0
     * @param array<string, string> $strings Key => translated string.
     */
    return apply_filters( 'argon_shop_js_i18n', $strings );
}

/**
 * Adds the type="module" attribute to the ars-main script.
 *
 * WHERE: Automatically when the <script> tag is printed in the footer.
 *
 * WHY: WordPress cannot enqueue ES modules directly.
 * The only way is to filter the ready tag HTML and
 * add the attribute manually.
 *
 * IMPORTANT: the filter does not touch other scripts — only ars-main.
 *
 * @param  string $tag    — ready HTML of the <script> tag
 * @param  string $handle — script handle
 * @return string         — modified HTML
 */
add_filter( 'script_loader_tag', 'ars_add_module_type', 10, 2 );
function ars_add_module_type( $tag, $handle ) {

    if ( $handle !== 'ars-main' ) {
        return $tag;
    }

    return str_replace( '<script ', '<script type="module" ', $tag );
}

// ============================================
// POST TYPES AND TAXONOMIES
// ============================================

require_once ARGON_SHOP_PATH . 'includes/createPostType.php';

// ============================================
// PLUGIN MODULES
// ============================================

// Additional filters (admin)
require_once ARGON_SHOP_PATH . 'includes/admin/additionalFilters.php';

// Additional taxonomy fields (admin)
require_once ARGON_SHOP_PATH . 'includes/admin/fieldsTaxonomy.php';

// Additional product fields (admin)
require_once ARGON_SHOP_PATH . 'includes/admin/fieldsProduct.php';

// Product "Gallery" meta box
require_once ARGON_SHOP_PATH . 'includes/admin/galleryMetabox.php';

// ============================================
// INTERFACE: CART
// ============================================

// Shopping cart (AJAX handlers)
require_once ARGON_SHOP_PATH . 'includes/interface/shoppingCart/shoppingCart.php';

// Cart widget (item count and total)
require_once ARGON_SHOP_PATH . 'includes/interface/shoppingCart/infoCart.php';

// ============================================
// INTERFACE: PRODUCTS AND CATALOG
// ============================================

// Product cards
require_once ARGON_SHOP_PATH . 'includes/interface/product/cardProduct.php';

// Catalog sorting
require_once ARGON_SHOP_PATH . 'includes/interface/catalog/sort.php';

// ============================================
// INTERFACE: ACCOUNT
// ============================================

// User account
require_once ARGON_SHOP_PATH . 'includes/interface/cabinet/myCabinet.php';

// User orders output
require_once ARGON_SHOP_PATH . 'includes/interface/cabinet/userOrders/ordersView.php';

// AJAX order filters
require_once ARGON_SHOP_PATH . 'includes/interface/cabinet/userOrders/ordersAjax.php';

// User redirect on login
require_once ARGON_SHOP_PATH . 'includes/interface/cabinet/redirectUser.php';

// ============================================
// INTERFACE: NAVIGATION
// ============================================

// Breadcrumbs
require_once ARGON_SHOP_PATH . 'includes/interface/breadcrumbs.php';

// Pagination
require_once ARGON_SHOP_PATH . 'includes/interface/pagenavi.php';

// ============================================
// INTERFACE: SEARCH
// ============================================

// Search form output
require_once ARGON_SHOP_PATH . 'includes/interface/search/searchForm.php';

// Search language rules (keyboard layouts, word normalization)
require_once ARGON_SHOP_PATH . 'includes/interface/search/searchLanguage.php';

// Results filter on the /?s= page
require_once ARGON_SHOP_PATH . 'includes/interface/search/searchGetFilters.php';

// Search AJAX events
require_once ARGON_SHOP_PATH . 'includes/interface/search/searchAjax.php';

// ============================================
// INTERFACE: OTHER
// ============================================

// View history
require_once ARGON_SHOP_PATH . 'includes/interface/history.php';

// ============================================
// ADMIN: ORDERS
// ============================================

// Order meta boxes
require_once ARGON_SHOP_PATH . 'includes/admin/order/orderMetabox.php';

// Order page AJAX
require_once ARGON_SHOP_PATH . 'includes/admin/order/orderAjax.php';

// Order saving
require_once ARGON_SHOP_PATH . 'includes/admin/order/orderSave.php';

// ============================================
// OTHER MODULES
// ============================================

// Site network (multi-region)
require_once ARGON_SHOP_PATH . 'includes/siteLine.php';
