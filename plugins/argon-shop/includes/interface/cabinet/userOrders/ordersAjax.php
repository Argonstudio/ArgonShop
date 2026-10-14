<?php
/**
 * AJAX order filter in the account
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * WHERE TO TEST:
 * Account → "Orders" tab → status filter
 * URL: /account/ (is changed in the control panel)
 * 
 * WHAT IT DOES:
 * 1. Accepts the userID and a list of statuses
 * 2. Checks permissions (a user sees only their own orders)
 * 3. Returns the HTML table of filtered orders
 * 
 * DEPENDENCIES:
 * - as_get_user_orders()          — includes/interface/cabinet/userOrders/ordersView.php
 * - as_create_user_ordersTable()  — includes/interface/cabinet/userOrders/ordersView.php
 * 
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// AJAX handler registration
add_action( 'wp_ajax_ordersFiltersStatus', 'as_ordersFiltersStatus_callback' );
add_action( 'wp_ajax_nopriv_ordersFiltersStatus', 'as_ordersFiltersStatus_callback' );

/**
 * HANDLER: Filter orders by status
 * 
 * WHERE: Account → filter change
 * WHAT IT DOES:
 * 1. Checks that the user requests their own orders
 * 2. Returns the HTML table with the filtered orders
 */
function as_ordersFiltersStatus_callback() {
    
    check_ajax_referer( 'argon_shop_nonce', 'nonce' );
    
    // userID validation
    $userID = isset( $_POST['userID'] ) ? absint( $_POST['userID'] ) : 0;
    
    // Permission check: a user sees only their own orders.
    // Exception: administrators can view any orders.
    $current_user_id = get_current_user_id();
    
    if ( $userID !== $current_user_id && ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Insufficient permissions', 'argon-shop' ) );
    }
    
    // Status validation
    $status_raw = isset( $_POST['status'] ) 
        ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) 
        : '';
    
    $status = explode( ',', $status_raw );
    
    foreach ( $status as $key => $value ) {
        $status[ $key ] = absint( $value );
    }
    
    // Remove zero values
    $status = array_filter( $status );
    
    if ( $userID !== 0 ) {
        
        $filters = array();
        $filters['orderStatus'] = $status;
        
        $orders = as_get_user_orders( $userID, $filters );
        
        if ( ! empty( $orders ) ) {
            as_create_user_ordersTable( $orders );
        } else {
            echo __( 'No matching posts', 'argon-shop' );
        }
        
    } else {
        echo __( 'Invalid request data', 'argon-shop' );
    }
    
    wp_die();
}