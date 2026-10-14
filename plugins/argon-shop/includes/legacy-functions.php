<?php
/**
 * Old function names (before version 1.1.0) — for compatibility
 *
 * Since version 1.1.0 all plugin functions have the as_ prefix (getCookie → as_getCookie),
 * so as not to conflict with other plugins and themes.
 *
 * Wrappers with the old names are declared here for the public API that
 * third-party themes and snippets may call. A wrapper is declared only if the name
 * is free: if another plugin has already declared a function with that name, ArgonShop
 * does not fail with a fatal error, and the plugin and theme themselves use the new names.
 *
 * IN NEW CODE USE ONLY NAMES WITH THE as_ PREFIX.
 * The file is generated automatically; the full mapping is in docs/ai/AI_FUNCTION_NAMES.md
 *
 * @package Argon_Shop
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'array_partial_merge' ) ) {
    /** @deprecated 1.1.0 Use as_array_partial_merge() */
    function array_partial_merge( ...$args ) {
        return as_array_partial_merge( ...$args );
    }
}

if ( ! function_exists( 'button_card_product' ) ) {
    /** @deprecated 1.1.0 Use as_button_card_product() */
    function button_card_product( ...$args ) {
        return as_button_card_product( ...$args );
    }
}

if ( ! function_exists( 'cheak_detailed_fields' ) ) {
    /** @deprecated 1.1.0 Use as_cheak_detailed_fields() */
    function cheak_detailed_fields( ...$args ) {
        return as_cheak_detailed_fields( ...$args );
    }
}

if ( ! function_exists( 'check_saleSteps' ) ) {
    /** @deprecated 1.1.0 Use as_check_saleSteps() */
    function check_saleSteps( ...$args ) {
        return as_check_saleSteps( ...$args );
    }
}

if ( ! function_exists( 'countDigits' ) ) {
    /** @deprecated 1.1.0 Use as_countDigits() */
    function countDigits( ...$args ) {
        return as_countDigits( ...$args );
    }
}

if ( ! function_exists( 'create_user_ordersTable' ) ) {
    /** @deprecated 1.1.0 Use as_create_user_ordersTable() */
    function create_user_ordersTable( ...$args ) {
        return as_create_user_ordersTable( ...$args );
    }
}

if ( ! function_exists( 'emailTextsGenerator' ) ) {
    /** @deprecated 1.1.0 Use as_emailTextsGenerator() */
    function emailTextsGenerator( ...$args ) {
        return as_emailTextsGenerator( ...$args );
    }
}

if ( ! function_exists( 'getActualPrice' ) ) {
    /** @deprecated 1.1.0 Use as_getActualPrice() */
    function getActualPrice( ...$args ) {
        return as_getActualPrice( ...$args );
    }
}

if ( ! function_exists( 'getActualPriceAllProducts' ) ) {
    /** @deprecated 1.1.0 Use as_getActualPriceAllProducts() */
    function getActualPriceAllProducts( ...$args ) {
        return as_getActualPriceAllProducts( ...$args );
    }
}

if ( ! function_exists( 'getActualStepDiscont' ) ) {
    /** @deprecated 1.1.0 Use as_getActualStepDiscont() */
    function getActualStepDiscont( ...$args ) {
        return as_getActualStepDiscont( ...$args );
    }
}

if ( ! function_exists( 'getClassActiveItem' ) ) {
    /** @deprecated 1.1.0 Use as_getClassActiveItem() */
    function getClassActiveItem( ...$args ) {
        return as_getClassActiveItem( ...$args );
    }
}

if ( ! function_exists( 'getCookie' ) ) {
    /** @deprecated 1.1.0 Use as_getCookie() */
    function getCookie( ...$args ) {
        return as_getCookie( ...$args );
    }
}

if ( ! function_exists( 'getDataCart' ) ) {
    /** @deprecated 1.1.0 Use as_getDataCart() */
    function getDataCart( ...$args ) {
        return as_getDataCart( ...$args );
    }
}

if ( ! function_exists( 'getDiscount' ) ) {
    /** @deprecated 1.1.0 Use as_getDiscount() */
    function getDiscount( ...$args ) {
        return as_getDiscount( ...$args );
    }
}

if ( ! function_exists( 'get_cabinetPageID' ) ) {
    /** @deprecated 1.1.0 Use as_get_cabinetPageID() */
    function get_cabinetPageID( ...$args ) {
        return as_get_cabinetPageID( ...$args );
    }
}

if ( ! function_exists( 'get_cabinetPageURL' ) ) {
    /** @deprecated 1.1.0 Use as_get_cabinetPageURL() */
    function get_cabinetPageURL( ...$args ) {
        return as_get_cabinetPageURL( ...$args );
    }
}

if ( ! function_exists( 'get_cartPageID' ) ) {
    /** @deprecated 1.1.0 Use as_get_cartPageID() */
    function get_cartPageID( ...$args ) {
        return as_get_cartPageID( ...$args );
    }
}

if ( ! function_exists( 'get_cartPageURL' ) ) {
    /** @deprecated 1.1.0 Use as_get_cartPageURL() */
    function get_cartPageURL( ...$args ) {
        return as_get_cartPageURL( ...$args );
    }
}

if ( ! function_exists( 'get_catalog_terms' ) ) {
    /** @deprecated 1.1.0 Use as_get_catalog_terms() */
    function get_catalog_terms( ...$args ) {
        return as_get_catalog_terms( ...$args );
    }
}

if ( ! function_exists( 'get_catalog_termsWP' ) ) {
    /** @deprecated 1.1.0 Use as_get_catalog_termsWP() */
    function get_catalog_termsWP( ...$args ) {
        return as_get_catalog_termsWP( ...$args );
    }
}

if ( ! function_exists( 'get_charact' ) ) {
    /** @deprecated 1.1.0 Use as_get_charact() */
    function get_charact( ...$args ) {
        return as_get_charact( ...$args );
    }
}

if ( ! function_exists( 'get_history_products' ) ) {
    /** @deprecated 1.1.0 Use as_get_history_products() */
    function get_history_products( ...$args ) {
        return as_get_history_products( ...$args );
    }
}

if ( ! function_exists( 'get_marked_products' ) ) {
    /** @deprecated 1.1.0 Use as_get_marked_products() */
    function get_marked_products( ...$args ) {
        return as_get_marked_products( ...$args );
    }
}

if ( ! function_exists( 'get_sort_products' ) ) {
    /** @deprecated 1.1.0 Use as_get_sort_products() */
    function get_sort_products( ...$args ) {
        return as_get_sort_products( ...$args );
    }
}

if ( ! function_exists( 'get_tax_query_marked_products' ) ) {
    /** @deprecated 1.1.0 Use as_get_tax_query_marked_products() */
    function get_tax_query_marked_products( ...$args ) {
        return as_get_tax_query_marked_products( ...$args );
    }
}

if ( ! function_exists( 'get_terms_ids_marked_products' ) ) {
    /** @deprecated 1.1.0 Use as_get_terms_ids_marked_products() */
    function get_terms_ids_marked_products( ...$args ) {
        return as_get_terms_ids_marked_products( ...$args );
    }
}

if ( ! function_exists( 'get_used_statuses' ) ) {
    /** @deprecated 1.1.0 Use as_get_used_statuses() */
    function get_used_statuses( ...$args ) {
        return as_get_used_statuses( ...$args );
    }
}

if ( ! function_exists( 'get_user_orders' ) ) {
    /** @deprecated 1.1.0 Use as_get_user_orders() */
    function get_user_orders( ...$args ) {
        return as_get_user_orders( ...$args );
    }
}

if ( ! function_exists( 'get_valueFieldsCart' ) ) {
    /** @deprecated 1.1.0 Use as_get_valueFieldsCart() */
    function get_valueFieldsCart( ...$args ) {
        return as_get_valueFieldsCart( ...$args );
    }
}

if ( ! function_exists( 'kama_breadcrumbs' ) ) {
    /** @deprecated 1.1.0 Use as_kama_breadcrumbs() */
    function kama_breadcrumbs( ...$args ) {
        return as_kama_breadcrumbs( ...$args );
    }
}

if ( ! function_exists( 'kama_pagenavi' ) ) {
    /** @deprecated 1.1.0 Use as_kama_pagenavi() */
    function kama_pagenavi( ...$args ) {
        return as_kama_pagenavi( ...$args );
    }
}

if ( ! function_exists( 'post_in_term' ) ) {
    /** @deprecated 1.1.0 Use as_post_in_term() */
    function post_in_term( ...$args ) {
        return as_post_in_term( ...$args );
    }
}

if ( ! function_exists( 'show_user_fields' ) ) {
    /** @deprecated 1.1.0 Use as_show_user_fields() */
    function show_user_fields( ...$args ) {
        return as_show_user_fields( ...$args );
    }
}

if ( ! function_exists( 'splitMenu' ) ) {
    /** @deprecated 1.1.0 Use as_splitMenu() */
    function splitMenu( ...$args ) {
        return as_splitMenu( ...$args );
    }
}

if ( ! function_exists( 'view_menu_elements' ) ) {
    /** @deprecated 1.1.0 Use as_view_menu_elements() */
    function view_menu_elements( ...$args ) {
        return as_view_menu_elements( ...$args );
    }
}

if ( ! function_exists( 'view_products_list' ) ) {
    /** @deprecated 1.1.0 Use as_view_products_list() */
    function view_products_list( ...$args ) {
        return as_view_products_list( ...$args );
    }
}

if ( ! function_exists( 'view_user_order' ) ) {
    /** @deprecated 1.1.0 Use as_view_user_order() */
    function view_user_order( ...$args ) {
        return as_view_user_order( ...$args );
    }
}

if ( ! function_exists( 'view_user_orders' ) ) {
    /** @deprecated 1.1.0 Use as_view_user_orders() */
    function view_user_orders( ...$args ) {
        return as_view_user_orders( ...$args );
    }
}
