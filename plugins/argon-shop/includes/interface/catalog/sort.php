<?php
/**
 * AJAX sorting of the product catalog
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * WHERE TO TEST:
 * Catalog category page → choose sorting (date, name, price)
 * URL: /catalog/category/
 * 
 * WHAT IT DOES:
 * 1. Saves the selected sort type in a cookie
 * 2. Returns the sorted product list
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_ajax_sortingCatalog', 'as_sortingCatalog_callback' );
add_action( 'wp_ajax_nopriv_sortingCatalog', 'as_sortingCatalog_callback' );

/**
 * HANDLER: Catalog sorting
 * 
 * WHERE: Category page → sorting change
 * WHAT IT DOES:
 * 1. Saves the selected sorting in a cookie
 * 2. Returns the sorted products
 */
function as_sortingCatalog_callback() {
    
    // Nonce check
    check_ajax_referer( 'argon_shop_nonce', 'nonce' );
    
    // Data validation
    $typeSort = isset( $_POST['typeSort'] ) ? sanitize_text_field( wp_unslash( $_POST['typeSort'] ) ) : 'date';
    $howSorting = isset( $_POST['howSorting'] ) ? sanitize_text_field( wp_unslash( $_POST['howSorting'] ) ) : 'DESC';
    $catId = isset( $_POST['catId'] ) ? absint( $_POST['catId'] ) : 0;
    $pageNum = isset( $_POST['pageNum'] ) ? absint( $_POST['pageNum'] ) : 1;
    
    // Allowed values check
    if ( ! in_array( $typeSort, array( 'date', 'name', 'price' ), true ) ) {
        $typeSort = 'date';
    }
    
    $howSorting = strtoupper( $howSorting );
    
    if ( ! in_array( $howSorting, array( 'ASC', 'DESC' ), true ) ) {
        $howSorting = 'DESC';
    }
    
    if ( $pageNum < 1 ) {
        $pageNum = 1;
    }
    
    // Save the sorting in a cookie
    $cookieCatalogSort = array(
        'typeSort' => $typeSort,
        'howSort'  => $howSorting,
    );
    
    $cookieCatalogSort = wp_json_encode( $cookieCatalogSort );
    
    setcookie( 'AS_CatalogSorting', $cookieCatalogSort, time() + 1209600, COOKIEPATH, COOKIE_DOMAIN );
    
    // Get the sorted product list
    // api/product/productList/catalog.php
    $productsList = as_get_sort_products( $typeSort, $howSorting, $catId, $pageNum );
    
    as_view_products_list( $productsList );
    
    wp_die();
}