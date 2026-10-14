<?php
/**
 * Viewed products history
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * WHERE TO TEST:
 * Product page → view a product → "You viewed" block
 * "View history" page (/history/)
 * 
 * WHAT IT DOES:
 * Returns the list of viewed products from the AS_History cookie
 * 
 * PHP 8.5:
 * - 'paged' and 'posts_per_page' are now passed to WP_Query
 * - this is needed for pagination to work correctly
 * - protection against an empty array
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FUNCTION: Get viewed products
 * 
 * @param int|string $count — how many products to return (default "all")
 * 
 * @return WP_Query|false — Object with products or false if there is no history
 */
function as_get_history_products( $count = 'all' ) {
    
    $post_type = 'product';
    $history = as_getCookie( 'AS_History' );
    
    if ( ! $history || ! is_array( $history ) ) {
        return false;
    }
    
    // Remove non-numeric values from the array
    $history = array_map( 'absint', $history );
    $history = array_filter( $history );
    $history = array_values( $history );
    
    if ( empty( $history ) ) {
        return false;
    }
    
    // ============================================
    // PAGINATION
    // ============================================
    // 
    // PHP 8.5: get the current pagination page from the global query.
    // WordPress uses either 'paged' (for archives) or 'page'
    // (for static pages with pagination).
    
    $paged = 1;
    
    if ( $count === 'all' ) {
        
        $paged_from_query = get_query_var( 'paged' );
        
        if ( ! $paged_from_query ) {
            $paged_from_query = get_query_var( 'page' );
        }
        
        if ( $paged_from_query ) {
            $paged = absint( $paged_from_query );
        }
        
        if ( $paged < 1 ) {
            $paged = 1;
        }
    }
    
    // Products per page
    if ( $count !== 'all' ) {
        
        $count = absint( $count );
        
        if ( $count > 0 ) {
            $history = array_slice( $history, 0, $count );
        }
        
        // With a limit — a single page
        $posts_per_page = $count > 0 ? $count : 12;
        $paged = 1;
        
    } else {
        
        $posts_per_page = absint( get_option( 'posts_per_page', 12 ) );
        
        if ( $posts_per_page < 1 ) {
            $posts_per_page = 12;
        }
    }
    
    // ============================================
    // QUERY
    // ============================================
    
    $arg = array(
        'post_type'      => $post_type,
        'post__in'       => $history,
        'orderby'        => 'post__in',
        'posts_per_page' => $posts_per_page,
        'paged'          => $paged,
    );
    
    $history_products = new WP_Query( $arg );
    
    return $history_products;
}