<?php
/**
 * Product view history (AJAX)
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * WHERE TO TEST:
 * Product page → view a product → "You viewed" block
 * 
 * WHAT IT DOES:
 * 1. Saves the ID of the viewed product in the AS_History cookie
 * 2. Removes duplicates
 * 3. Adds new products to the beginning of the list
 * 
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// AJAX handler registration
add_action( 'wp_ajax_addCookieHistory_history', 'as_addCookieHistory_history_callback' );
add_action( 'wp_ajax_nopriv_addCookieHistory_history', 'as_addCookieHistory_history_callback' );

/**
 * HANDLER: Add a product to the view history
 * 
 * WHERE: Product page → on load
 * WHAT IT DOES:
 * 1. Gets the product ID from the request
 * 2. Reads the current history from the cookie
 * 3. Removes duplicates
 * 4. Adds the ID to the beginning
 * 5. Saves the cookie
 */
function as_addCookieHistory_history_callback() {
    
    // Nonce check
    check_ajax_referer( 'argon_shop_nonce', 'nonce' );
    
    // productID validation
    $productID = isset( $_POST['productID'] ) ? absint( $_POST['productID'] ) : 0;
    
    if ( 0 === $productID ) {
        wp_die();
    }
    
    // Make sure the product exists
    if ( ! get_post( $productID ) ) {
        wp_die();
    }
    
    $ASHistory = array();
    
    $cookie_data = as_getCookie( 'AS_History' );
    
    if ( is_array( $cookie_data ) && ! empty( $cookie_data ) ) {
        
        // Remove duplicates and validate
        $cleanHistory = array();
        
        foreach ( $cookie_data as $value ) {
            
            $value = absint( $value );
            
            if ( $value === 0 || $value === $productID ) {
                continue;
            }
            
            $cleanHistory[] = $value;
        }
        
        $ASHistory = $cleanHistory;
    }
    
    // Add the new product to the beginning
    array_unshift( $ASHistory, $productID );
    
    // Limit the history length (DoS protection)
    if ( count( $ASHistory ) > 100 ) {
        $ASHistory = array_slice( $ASHistory, 0, 100 );
    }
    
    $ASHistory = wp_json_encode( $ASHistory );
    
    // PHP 8.5: setcookie() with an options array
    $cookie_options = array(
        'expires'  => time() + 1209600,
        'path'     => COOKIEPATH ? COOKIEPATH : '/',
        'domain'   => COOKIE_DOMAIN ? COOKIE_DOMAIN : '',
        'secure'   => is_ssl(),
        'httponly' => false, // Must be false — it is read from JS
        'samesite' => 'Lax',
    );
    
    setcookie( 'AS_History', $ASHistory, $cookie_options );
    
    wp_die();
}