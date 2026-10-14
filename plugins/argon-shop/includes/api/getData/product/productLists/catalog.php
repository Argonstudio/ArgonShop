<?php
/**
 * Getting a sorted list of catalog products
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * WHERE TO TEST:
 * Catalog on the site → category page → product sorting
 * URL: /catalog/category/
 * 
 * WHAT IT DOES:
 * Returns a WP_Query object with products sorted by date, name or price
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FUNCTION: Get a sorted list of products
 * 
 * @param string $typeSort   — sort type: 'date', 'name', 'price'
 * @param string $howSorting — direction: 'ASC' or 'DESC'
 * @param int    $catId      — catalog category ID
 * @param int    $pageNum    — pagination page number
 * 
 * @return WP_Query Object with the results
 */
function as_get_sort_products( $typeSort, $howSorting, $catId, $pageNum ) {
    
    $post_type = 'product';
    $taxonomy = 'catalog';
    
    // Validation
    $catId = absint( $catId );
    $pageNum = absint( $pageNum );
    
    if ( $pageNum < 1 ) {
        $pageNum = 1;
    }
    
    // Sort direction validation
    $howSorting = strtoupper( $howSorting );
    
    if ( ! in_array( $howSorting, array( 'ASC', 'DESC' ), true ) ) {
        $howSorting = 'DESC';
    }
    
    $arg = array(
        'post_type'      => $post_type,
        'post_status'    => 'publish',
        'paged'          => $pageNum,
        'posts_per_page' => get_option( 'posts_per_page', 12 ),
        'tax_query'      => array(
            array(
                'taxonomy' => $taxonomy,
                'field'    => 'id',
                'terms'    => $catId,
            ),
        ),
        'order'          => $howSorting,
    );
    
    // Sort type validation
    switch($typeSort){
	    
	    case "date":
	        // Sort by date + ID as a secondary criterion.
	        // Keeps the order of products added at the same time stable.
	        $arg["orderby"] = array(
	            'date' => $howSorting,
	            'ID'   => 'DESC',
	        );
	        break;
	        
	    case "name":
	        // Sort by name + ID as a secondary criterion.
	        $arg["orderby"] = array(
	            'title' => $howSorting,
	            'ID'   => 'DESC',
	        );
	        break;
	        
	    case "price":
	        // Sort by price. The direction comes from the shared 'order'.
	        $arg["orderby"]  = "meta_value_num";
	        $arg['meta_key'] = '_price';
	        break;
	}
    
    $productsList = new WP_Query( $arg );
    
    return $productsList;
}