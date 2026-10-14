<?php
/**
 * User redirects and access control
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * WHERE TO TEST:
 * 1. Log in → redirect to the account
 * 2. Log out → redirect to the home page
 * 3. Opening the account without logging in → redirect to login
 * 
 * WHAT IT DOES:
 * 1. Redirects users after login
 * 2. Redirects after logout
 * 3. Hides the admin bar for non-administrators
 * 4. Protects the account page from guests
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FUNCTION: Redirect the user after login
 * 
 * @param string  $redirect_to — redirect URL
 * @param string  $request     — requested URL
 * @param WP_User $user        — user object
 * 
 * @return string Redirect URL
 */
function as_my_login_redirect( $redirect_to, $request, $user ) {
    
    // Check whether the user has roles
    if ( isset( $user->roles ) && is_array( $user->roles ) ) {
        
        // Administrator — default redirect
        if ( in_array( 'administrator', $user->roles, true ) ) {
            return $redirect_to;
        }
        
        // Everyone else — to the account
        $cabinet_url = as_get_cabinetPageURL();
        
        if ( $cabinet_url ) {
            return $cabinet_url;
        }
        
        // If the account page is not configured — to the home page
        return home_url();
    }
    
    return $redirect_to;
}

/**
 * FUNCTION: Redirect to the home page after logout
 * 
 * @param string $logout_url — logout URL
 * @param string $redirect   — current redirect
 * 
 * @return string Logout URL with the redirect
 */
function as_redirect_after_logout( $logout_url, $redirect ) {
    
    if ( empty( $redirect ) ) {
        $logout_url = add_query_arg( 'redirect_to', urlencode( home_url() ), $logout_url );
    }
    
    return $logout_url;
}

/**
 * FUNCTION: Hide the admin bar for non-administrators
 * 
 * @param bool $content — current admin bar state
 * 
 * @return bool
 */
function as_my_function_admin_bar( $content ) {
    return current_user_can( 'administrator' ) ? $content : false;
}

add_filter( 'show_admin_bar', 'as_my_function_admin_bar' );
add_filter( 'logout_url', 'as_redirect_after_logout', 10, 2 );
add_filter( 'login_redirect', 'as_my_login_redirect', 10, 3 );

/**
 * FUNCTION: Protect the account page from guests
 * 
 * WHERE: When the account page is opened without logging in
 * WHAT IT DOES: Redirects the guest to the login page
 */
add_action( 'template_redirect', function() {
    
    if ( is_user_logged_in() ) {
        return;
    }
    
    $cabinet_id = as_get_cabinetPageID();
    
    // If the account page is not configured — exit
    if ( ! $cabinet_id ) {
        return;
    }
    
    if ( is_page( $cabinet_id ) ) {
        wp_safe_redirect( wp_login_url() );
        exit;
    }
} );