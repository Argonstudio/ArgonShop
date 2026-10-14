<?php

/*
 * CHANGES FROM ARGON SHOP:
 * 
 * 1. Added a separate view counter (WP-LastViewedPosts1)
 * 2. Added price and "hit" label output in the product block
 * 3. Changed output structure for responsive layout
 * 4. Added direct file access protection (ABSPATH)
 * 5. FIXED CRITICAL ERROR WITH PHP 8.5:
 *     - Removed /e modifier in preg_replace() — removed since PHP 7.0
 *     - Will cause Fatal Error on modern PHP when accessed
 * 6. Replaced unsafe unserialize() without restrictions with safe
 *     version using allowed_classes => false + array validation
 * 7. Fixed typo: is_singular( product ) → is_singular( 'product' )
 * 8. Fixed call to as_zg_recently_viewed() — default parameters passed
 * 9. Removed extract() function — unsafe and outdated
 * 10. Removed debug output (echo, print_r) in production
 * 11. Added isset() checks for all $_POST, $_COOKIE, $meta_data
 * 12. Replaced outdated functions: attribute_escape → esc_attr
 * 13. Fixed Deprecated warnings in PHP 8.1+:
 *     - setcookie() with 7 arguments (outdated)
 *     - $value+0 (implicit conversion)
 *
 * @package Argon_Shop
 * @author  Olaf Baumann (original author)
 * @author  Syed Balkhi (fork author)
 * @author  Ivan Voitkov (customization for ArgonShop)
 * Based on: Last Viewed Posts 0.7.3 (Olaf Baumann). Version of this copy = plugin version.
 */

/* Copyright 2007 Olaf Baumann  (http://zeitgrund.de)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

Use:
For the ouput use the sidebar widget OR place following code just anywhere (outside the loop) into your theme (e.g. sidebar.php).
Note that the output will not appear if there's no cookie set (because cookies are disabled or the user didn't view any single post).
-------------------------------------------
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/* Here are some parameters you may want to change: */
$zg_cookie_expire = 360; // After how many days should the cookie expire? Default is 360.
$zg_number_of_posts = 15; // How many posts should be displayed in the list? Default is 10.
$zg_recognize_pages = true; // Should pages to be recognized and listed? Default is true.

/* Do not edit after this line! */

/**
 * FUNCTION: Hook on every generated post
 * 
 * WHERE: Runs via get_header on the product page
 * WHAT IT DOES:
 * 1. Increments the view counter in the cookie (WP-LastViewedPosts1)
 * 2. Adds the product ID to the list of viewed products (WP-LastViewedPosts)
 */
function as_zg_lwp_header() {
    
    // PHP 8.5: is_singular( product ) is an error, it must be 'product' (a string)
    if ( is_singular( 'product' ) ) {
        
        $lastViewedPosts = array();
        
        if ( as_getCookie( 'WP-LastViewedPosts1' ) ) {
            
            $lastViewedPosts = as_getCookie( 'WP-LastViewedPosts1' );
            
            if ( ! is_array( $lastViewedPosts ) ) {
                $lastViewedPosts = array();
            }
            
            $lastViewedPosts['counter'] = isset( $lastViewedPosts['counter'] ) 
                ? intval( $lastViewedPosts['counter'] ) + 1 
                : 1;
                
        } else {
            $lastViewedPosts['counter'] = 1;
        }
        
        $lastViewedPosts = wp_json_encode( $lastViewedPosts );
        
        setcookie(
            'WP-LastViewedPosts1',
            $lastViewedPosts,
            time() + 1209600,
            COOKIEPATH,
            COOKIE_DOMAIN
        );
        
        as_zg_lw_setcookie();
    }
}

/**
 * FUNCTION: Save the viewed product in the cookie
 * 
 * WHERE: Called automatically from as_zg_lwp_header()
 * WHAT IT DOES:
 * 1. Reads the current list from the WP-LastViewedPosts cookie
 * 2. Adds the new ID to the beginning
 * 3. Limits the list to the configured number
 * 4. Saves it back to the cookie
 * 
 * PHP 8.5: replaced the deprecated preg_replace() with /e (removed in PHP 7.0)
 */
function as_zg_lw_setcookie() {
    
    global $wp_query;
    
    // PHP 8.5: make sure the post object exists
    if ( ! isset( $wp_query->post->ID ) ) {
        return;
    }
    
    $zg_post_ID = absint( $wp_query->post->ID );
    
    if ( $zg_post_ID === 0 ) {
        return;
    }
    
    // Read the cookie and unserialize it safely
    $zg_cookiearray = array();
    
    if ( isset( $_COOKIE['WP-LastViewedPosts'] ) ) {
        
        $cookie_raw = stripslashes( $_COOKIE['WP-LastViewedPosts'] );
        
        // PHP 8.5: replaced preg_replace(/e) with safe unserialization
        // Size limit for DoS protection
        if ( strlen( $cookie_raw ) <= 10000 ) {
            
            $unserialized = @unserialize( $cookie_raw, array( 'allowed_classes' => false ) );
            
            if ( is_array( $unserialized ) ) {
                // Validation — integers only
                $zg_cookiearray = array_map( 'absint', $unserialized );
                $zg_cookiearray = array_filter( $zg_cookiearray );
                $zg_cookiearray = array_values( $zg_cookiearray );
            }
        }
    }
    
    // If the array is empty or corrupted — create a new one
    if ( ! is_array( $zg_cookiearray ) || empty( $zg_cookiearray ) ) {
        $zg_cookiearray = array( $zg_post_ID );
    }
    
    // If the product is already in the list — remove it
    if ( in_array( $zg_post_ID, $zg_cookiearray, true ) ) {
        
        $zg_key = array_search( $zg_post_ID, $zg_cookiearray, true );
        
        if ( $zg_key !== false ) {
            array_splice( $zg_cookiearray, $zg_key, 1 );
        }
    }
    
    // Add the new ID to the beginning
    array_unshift( $zg_cookiearray, $zg_post_ID );
    
    // Limit the number
    global $zg_number_of_posts;
    
    $zg_number_of_posts = absint( $zg_number_of_posts );
    
    if ( $zg_number_of_posts < 1 ) {
        $zg_number_of_posts = 15;
    }
    
    while ( count( $zg_cookiearray ) > $zg_number_of_posts ) {
        array_pop( $zg_cookiearray );
    }
    
    // Determine the cookie domain
    $zg_blog_url_array = parse_url( get_bloginfo( 'url' ) );
    
    if ( ! is_array( $zg_blog_url_array ) ) {
        return;
    }
    
    $zg_blog_url = isset( $zg_blog_url_array['host'] ) ? $zg_blog_url_array['host'] : '';
    $zg_blog_url = str_replace( 'www.', '', $zg_blog_url );
    $zg_blog_url_dot = '.' . $zg_blog_url;
    
    $zg_path_url = isset( $zg_blog_url_array['path'] ) ? $zg_blog_url_array['path'] : '';
    $zg_path_url .= '/';
    
    // Counter (for compatibility with the Argon ZBK modification)
    $lastViewedPosts = array();
    
    if ( as_getCookie( 'WP-LastViewedPosts1' ) ) {
        $lastViewedPosts = as_getCookie( 'WP-LastViewedPosts1' );
        
        if ( ! is_array( $lastViewedPosts ) ) {
            $lastViewedPosts = array();
        }
    }
    
    array_unshift( $lastViewedPosts, $zg_post_ID );
    
    // PHP 8.5: setcookie() with 7 arguments is deprecated, use an options array
    setcookie(
        'WP-LastViewedPosts',
        serialize( $zg_cookiearray ),
        array(
            'expires'  => time() + ( absint( $zg_cookie_expire ) * 86400 ),
            'path'     => $zg_path_url,
            'domain'   => $zg_blog_url_dot,
            'secure'   => is_ssl(),
            'httponly' => false, // Must be false — it is read from JS
            'samesite' => 'Lax',
        )
    );
}

/**
 * FUNCTION: Output the list of viewed products
 * 
 * WHERE: Called in a theme template or widget
 * WHAT IT DOES:
 * 1. Reads the WP-LastViewedPosts cookie
 * 2. Outputs product cards as a list
 * 
 * @param int  $rightAmount — maximum number of products to output
 * @param bool $sidebarOn   — output in the sidebar (different grid)
 */
function as_zg_recently_viewed( $rightAmount = 15, $sidebarOn = true ) {
    
    $rightAmount = absint( $rightAmount );
    
    if ( $rightAmount < 1 ) {
        $rightAmount = 15;
    }
    
    echo "<div class='list row'>";
    
    if ( isset( $_COOKIE['WP-LastViewedPosts'] ) ) {
        
        $cookie_raw = stripslashes( $_COOKIE['WP-LastViewedPosts'] );
        
        $zg_post_IDs = array();
        
        // PHP 8.5: safe unserialization instead of preg_replace(/e)
        if ( strlen( $cookie_raw ) <= 10000 ) {
            
            $unserialized = @unserialize( $cookie_raw, array( 'allowed_classes' => false ) );
            
            if ( is_array( $unserialized ) ) {
                $zg_post_IDs = array_map( 'absint', $unserialized );
                $zg_post_IDs = array_filter( $zg_post_IDs );
                $zg_post_IDs = array_values( $zg_post_IDs );
            }
        }
        
        $counter = 0;
        
        foreach ( $zg_post_IDs as $value ) {
            
            if ( $counter >= $rightAmount ) {
                break;
            }
            
            $counter++;
            
            $value = absint( $value );
            
            if ( $value === 0 ) {
                continue;
            }
            
            // Product price
            $price = '?';
            
            $price_meta = get_post_meta( $value, '_price', true );
            
            if ( $price_meta ) {
                
                $price = $price_meta;
                
            } else {
                
                $meta_data = get_post_meta( $value, 'product_custom_options', true );
                
                // PHP 8.5: make sure it is an array and the key exists
                if ( is_array( $meta_data ) && isset( $meta_data['price'] ) ) {
                    $price = $meta_data['price'];
                }
            }
            
            if ( $sidebarOn ) {
                echo "<div class='column small-12 medium-6 large-12'>";
            } else {
                echo "<div class='column small-12 medium-4 large-4'>";
            }
            
            ?>
            
            <div class='list_row'>
                
                <div class="image">
                    
                    <?php 
                    // PHP 8.5: get_field may return null/false
                    if ( function_exists( 'get_field' ) && get_field( '_hit', $value ) ) { 
                        ?>
                        <span class="goods-label goods-label-hit"><?php esc_html_e( 'hit', 'argon-shop' ); ?></span>
                        <?php 
                    } 
                    ?>
                    
                    <a href="<?php echo esc_url( get_permalink( $value ) ); ?>">
                        <?php 
                        // PHP 8.5: get_the_post_thumbnail returns a string or false
                        echo get_the_post_thumbnail( 
                            $value, 
                            'blog_thumb', 
                            array( 'class' => 'img-responsive' ) 
                        ); 
                        ?>
                    </a>
                    
                </div>
                
                <div class="name"><?php echo esc_html( get_the_title( $value ) ); ?></div>
                
                <div class="price">
                    <span class="summ"><?php echo as_price_html( esc_html( $price ) ); ?></span>
                </div>
                
                <div class="link">
                    <a href="<?php echo esc_url( get_permalink( $value ) ); ?>" rel="bookmark"> <?php esc_html_e( 'Details', 'argon-shop' ); ?> </a>
                </div>
                
            </div> 
            
            </div>
            
            <?php
        }
    }
    
    echo "</div>";
}

/**
 * FUNCTION: Output the "Viewed products" widget
 * 
 * WHERE: Sidebar widget
 * 
 * PHP 8.5: extract() removed, checks added
 */
function as_zg_lwp_widget( $args ) {
    
    // PHP 8.5: extract($args) is unsafe and deprecated
    $before_widget = isset( $args['before_widget'] ) ? $args['before_widget'] : '';
    $after_widget  = isset( $args['after_widget'] )  ? $args['after_widget']  : '';
    $before_title  = isset( $args['before_title'] )  ? $args['before_title']  : '';
    $after_title   = isset( $args['after_title'] )   ? $args['after_title']   : '';
    
    $options = get_option( 'zg_lwp_widget' );
    
    if ( ! is_array( $options ) ) {
        $options = array();
    }
    
    $title = isset( $options['title'] ) ? $options['title'] : 'Last viewed posts';
    $title = esc_html( $title );
    
    if ( isset( $_COOKIE['WP-LastViewedPosts'] ) ) {
        
        echo $before_widget . $before_title . $title . $after_title;
        
        as_zg_recently_viewed( 15, true );
        
        echo $after_widget;
    }
}

/**
 * FUNCTION: Widget management in the admin
 * 
 * WHERE: Widgets page in the admin
 */
function as_zg_lwp_widget_control() {
    
    $options = $newoptions = get_option( 'zg_lwp_widget' );
    
    if ( ! is_array( $options ) ) {
        $options = array();
    }
    
    if ( ! is_array( $newoptions ) ) {
        $newoptions = array();
    }
    
    // PHP 8.5: check isset before accessing $_POST
    if ( isset( $_POST['lwp-submit'] ) ) {
        
        $title = isset( $_POST['lwp-title'] ) 
            ? sanitize_text_field( wp_unslash( $_POST['lwp-title'] ) ) 
            : '';
        
        $newoptions['title'] = $title;
    }
    
    if ( $options !== $newoptions ) {
        $options = $newoptions;
        update_option( 'zg_lwp_widget', $options );
    }
    
    // PHP 8.5: attribute_escape() was removed in WP 2.8
    $title = isset( $options['title'] ) ? esc_attr( $options['title'] ) : '';
    
    ?>
    <p>
        <label for="lwp-title">
            <?php esc_html_e( 'Title:', 'argon-shop' ); ?>
            <input type="text" style="width:250px" id="lwp-title" name="lwp-title" value="<?php echo $title; ?>" />
        </label>
    </p>
    <input type="hidden" name="lwp-submit" id="lwp-submit" value="1" />
    <?php
}

/**
 * FUNCTION: Widget initialization
 * 
 * WHERE: The widgets_init hook
 */
function as_zg_lwp_init() {
    
    if ( ! function_exists( 'register_sidebar_widget' ) ) {
        return;
    }
    
    register_sidebar_widget( 'Last Viewed Posts', 'as_zg_lwp_widget' );
    register_widget_control( 'Last Viewed Posts', 'as_zg_lwp_widget_control', 250, 100 );
}

add_action( 'get_header', 'as_zg_lwp_header' );
add_action( 'widgets_init', 'as_zg_lwp_init' );