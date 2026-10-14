<?php
/**
 * Working with user orders
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * WHERE IT IS USED:
 * - includes/interface/cabinet/userOrders/ordersView.php — order output in the account
 * - includes/interface/cabinet/userOrders/ordersAjax.php — order filter
 * 
 * DATA RETURNED BY as_get_user_orders():
 * 
 *   $orders['orders']        — array of order WP_Post objects
 *   $orders['orders_meta']   — meta fields: [order_id => ['number', 'totalPrice', 'productsCart']]
 *   $orders['orders_status'] — statuses: [order_id => 'Status name']
 * 
 * If there are no orders or the data is invalid — an empty array is returned.
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FUNCTION: Get order statuses
 * 
 * Returns all statuses of the statusorders taxonomy.
 * If $statuses_orders_user is passed — keeps only those
 * that are in that array (intersection).
 * 
 * @param array $statuses_orders_user — statuses of a specific user
 * 
 * @return array Array [term_id => name]
 */
function as_get_used_statuses( $statuses_orders_user = array() ) {
    
    $argumentStatusAddTerms = array(
        'taxonomy'   => 'statusorders',
        'hide_empty' => true,
        'fields'     => 'id=>name',
    );
    
    $statusTerms = get_terms( $argumentStatusAddTerms );
    
    // WP_Error check
    if ( is_wp_error( $statusTerms ) || ! is_array( $statusTerms ) ) {
        return array();
    }
    
    if ( ! empty( $statuses_orders_user ) && is_array( $statuses_orders_user ) ) {
        $statusTerms = array_intersect( $statusTerms, $statuses_orders_user );
    }
    
    return $statusTerms;
}


/**
 * FUNCTION: Get user orders
 * 
 * @param int   $user_ID — user ID
 * @param array $filters — filters:
 *                         'orderStatus' => array|int — array of status IDs or a single ID
 * 
 * @return array {
 *     @type array $orders        — array of WP_Post
 *     @type array $orders_meta   — order meta data
 *     @type array $orders_status — order statuses
 * }
 * 
 * Or an empty array if there are no orders / the data is invalid.
 */
function as_get_user_orders( $user_ID, $filters = array() ) {
    
    // ============================================
    // INPUT VALIDATION
    // ============================================
    
    if ( ! is_int( $user_ID ) ) {
        return array();
    }
    
    // $filters may be missing — cast it to an array
    if ( ! is_array( $filters ) ) {
        $filters = array();
    }
    
    // Status filter validation
    if ( ! empty( $filters['orderStatus'] ) ) {
        
        if ( is_array( $filters['orderStatus'] ) ) {
            
            foreach ( $filters['orderStatus'] as $key ) {
                
                if ( ! is_int( $key ) ) {
                    return array();
                }
            }
            
        } elseif ( ! is_int( $filters['orderStatus'] ) ) {
            
            return array();
        }
    }
    
    // ============================================
    // ORDER QUERY
    // ============================================
    
    $args = array(
        'post_type'      => 'shoporder',
        'posts_per_page' => -1,
        'author'         => $user_ID,
    );
    
    // Status filter
    if ( ! empty( $filters['orderStatus'] ) ) {
        
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'statusorders',
                'field'    => 'id',
                'terms'    => $filters['orderStatus'],
            ),
        );
    }
    
    $ordersWP = get_posts( $args );
    
    // No orders — return an empty array
    if ( empty( $ordersWP ) ) {
        return array();
    }
    
    // ============================================
    // COLLECTING META DATA AND STATUSES
    // ============================================
    
    $order_meta   = array();
    $order_status = array();
    
    foreach ( $ordersWP as $value ) {
        
        if ( ! is_object( $value ) || ! isset( $value->ID ) ) {
            continue;
        }
        
        $order_id = absint( $value->ID );
        
        if ( 0 === $order_id ) {
            continue;
        }
        
        // Order meta fields
        $order_meta[ $order_id ] = array(
            'number'       => get_post_meta( $order_id, '_number', true ),
            'totalPrice'   => get_post_meta( $order_id, '_totalPrice', true ),
            'productsCart' => get_post_meta( $order_id, '_productAmountCart', true ),
        );
        
        // Order status
        $currentStatus = get_the_terms( $order_id, 'statusorders' );
        
        // Check: the status may not be set
        if ( ! empty( $currentStatus ) && ! is_wp_error( $currentStatus ) && isset( $currentStatus[0]->name ) ) {
            $order_status[ $order_id ] = $currentStatus[0]->name;
        } else {
            $order_status[ $order_id ] = '';
        }
    }
    
    // ============================================
    // BUILDING THE RESULT
    // ============================================
    
    $orders = array(
        'orders'        => $ordersWP,
        'orders_meta'   => $order_meta,
        'orders_status' => $order_status,
    );
    
    return $orders;
}