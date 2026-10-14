<?php
/**
 * Replacing the as_homepage marker in menus with the site domain
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * WHERE TO TEST:
 * 1. Site menu (header, footer)
 * 2. A link with the as_homepage marker must be replaced with the current domain
 * 
 * WHAT IT DOES:
 * Replaces the "as_homepage" marker in menu item URLs with the current site domain (in the admin menu editor the link is http://as_homepage).
 * Used for multi-region sites — one menu item works
 * as a link to the home page for different domains.
 * 
 * PHP 8.5:
 * - $_SERVER['SERVER_NAME'] is checked for existence and type
 * - the domain is validated with a regular expression (protection against Host spoofing)
 * - $item->url is checked for null before stristr()
 * - fallback to home_url() if SERVER_NAME is empty or invalid
 * 
 * SECURITY:
 * - $_SERVER['SERVER_NAME'] can be spoofed via the Host HTTP header,
 *   so validation and a fallback are in place.
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_filter( 'wp_nav_menu_objects', 'as_change_nav_menu_objects', 10, 2 );

/**
 * FUNCTION: Replace the as_homepage marker in menu URLs with the site domain
 * 
 * @param array    $sorted_menu_items — array of menu item objects
 * @param stdClass $args              — wp_nav_menu() arguments
 * 
 * @return array Modified array of menu items
 */
function as_change_nav_menu_objects( $sorted_menu_items, $args ) {
    
    
    
    // Run only on the frontend (not in the admin or AJAX)
    if ( is_admin() || wp_doing_ajax() ) {
        return $sorted_menu_items;
    }
    
    if ( ! is_array( $sorted_menu_items ) ) {
        return $sorted_menu_items;
    }
    
    // Get a safe domain name
    $server_name = as_get_safe_server_name();
    
    // If the domain could not be obtained — return as is
    if ( empty( $server_name ) ) {
        return $sorted_menu_items;
    }
    
    foreach ( $sorted_menu_items as $index => $item ) {
        
        // PHP 8.5: make sure $item is an object and has a url
        if ( ! is_object( $item ) || ! isset( $item->url ) ) {
            continue;
        }
        
        // PHP 8.5: $item->url may be null — cast it to a string
        $url = (string) $item->url;
        
        if ( $url === '' ) {
            continue;
        }
        
        // Look for the as_homepage marker
        if ( stripos( $url, 'as_homepage' ) === false ) {
            continue;
        }
        
        // Replace the marker with the domain
        $new_url = str_ireplace( 'as_homepage', $server_name, $url );
        
        // Escape the URL
        $sorted_menu_items[ $index ]->url = esc_url_raw( $new_url );
    }
    
    return $sorted_menu_items;
}

/**
 * FUNCTION: Safe retrieval of the domain name
 * 
 * Priority:
 * 1. $_SERVER['SERVER_NAME'] — if it exists and is valid
 * 2. home_url() — as a fallback (when SERVER_NAME is missing)
 * 
 * SERVER_NAME validation is mandatory, because the value comes from
 * the Host HTTP header, which can be spoofed by an attacker.
 * 
 * @return string Domain (for example, "example.com") or an empty string
 */
function as_get_safe_server_name() {
    
    $server_name = '';
    
    // Try to get SERVER_NAME
    if ( isset( $_SERVER['SERVER_NAME'] ) && is_string( $_SERVER['SERVER_NAME'] ) ) {
        
        // Remove escaping and clean
        $raw = wp_unslash( $_SERVER['SERVER_NAME'] );
        $raw = sanitize_text_field( $raw );
        
        // Validation: allowed domain characters — letters, digits, dots, hyphens
        // The port (:) is also allowed
        if ( preg_match( '/^[a-z0-9.\-]+(:\d+)?$/i', $raw ) ) {
            $server_name = $raw;
        }
    }
    
    // Fallback — home_url() from the WordPress settings
    if ( empty( $server_name ) ) {
        
        $home_host = wp_parse_url( home_url(), PHP_URL_HOST );
        
        if ( is_string( $home_host ) && $home_host !== '' ) {
            $server_name = $home_host;
        }
    }
    
    return $server_name;
}
