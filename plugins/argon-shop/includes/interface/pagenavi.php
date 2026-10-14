<?php
/**
 * Pagination for WordPress (a wp_pagenavi alternative)
 *
 * Original author: Timur Kamaev (Kama)
 * Source: http://wp-kama.ru/?p=8
 * Original version: 2.5.1
 *
 * @package Argon_Shop
 * @author  Timur Kamaev (Kama) — original author
 * @author  Ivan Voitkov — security fixes and PHP 8.5 compatibility
 * The version of this copy = the plugin version (ARGON_SHOP_VERSION).
 * 
 * ARGON SHOP CHANGES:
 * 1. Added protection against direct file access (ABSPATH)
 * 2. Replaced loose comparisons (== / !=) with strict ones (=== / !==)
 *     in critical page number checks
 * 3. Added explicit type and value checks:
 *     - $wp_query is a WP_Query object
 *     - $max_page > 0
 *     - $pages_to_show > 0 (protection against division by zero)
 * 4. Fixed PHP 8.1+ Deprecated warnings:
 *     - implicit float to int conversion
 *     - accessing properties of null
 * 5. PHP 8.5 compatibility:
 *     - explicit type casts
 *     - safe math operations
 *
 * PAGINATION
 * 
 * A wp_pagenavi alternative. Creates pagination links on archive pages.
 *
 * @param string   $before   — text before the navigation
 * @param string   $after    — text after the navigation
 * @param bool     $echo     — return or print the result
 * @param array    $args     — function arguments
 * @param WP_Query $wp_query — WP_Query object the pagination is built from.
 *                             Defaults to the global $wp_query variable
 * 
 * @return string|void Pagination HTML or nothing (if $echo = true)
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function as_kama_pagenavi( $before = '', $after = '', $echo = true, $args = array(), $wp_query = null ) {
    
    // Get the global WP_Query
    if ( ! $wp_query ) {
        wp_reset_query();
        global $wp_query;
    }
    
    // PHP 8.5: make sure $wp_query is a WP_Query object
    if ( ! $wp_query instanceof WP_Query ) {
        return false;
    }

    // Default parameters
    $default_args = array(
        'text_num_page'   => '',
        'num_pages'       => 3,
        'step_link'       => 0,
        'dotright_text'   => '…',
        'dotright_text2'  => '…',
        'back_text'       => '« ',
        'next_text'       => ' »',
        'first_page_text' => 0,
        'last_page_text'  => 0,
    );

    $default_args = apply_filters( 'kama_pagenavi_args', $default_args );

    $args = array_merge( $default_args, $args );

    // PHP 8.5: replace extract() with explicit extraction for safety
    $text_num_page   = $args['text_num_page'];
    $num_pages       = $args['num_pages'];
    $step_link       = $args['step_link'];
    $dotright_text   = $args['dotright_text'];
    $dotright_text2  = $args['dotright_text2'];
    $back_text       = $args['back_text'];
    $next_text       = $args['next_text'];
    $first_page_text = $args['first_page_text'];
    $last_page_text  = $args['last_page_text'];

    // PHP 8.5: explicit type casting
    $paged    = (int) $wp_query->get( 'paged' );
    $max_page = (int) $wp_query->max_num_pages;

    // Check whether navigation is needed
    if ( $max_page <= 1 ) {
        return false;
    }

    if ( $paged === 0 ) {
        $paged = 1;
    }

    $pages_to_show = intval( $num_pages );
    
    // PHP 8.5: protection against an invalid value
    if ( $pages_to_show < 1 ) {
        $pages_to_show = 3;
    }
    
    $pages_to_show_minus_1 = $pages_to_show - 1;

    // PHP 8.5: explicit int casts
    $half_page_start = (int) floor( $pages_to_show_minus_1 / 2 );
    $half_page_end   = (int) ceil( $pages_to_show_minus_1 / 2 );

    $start_page = $paged - $half_page_start;
    $end_page   = $paged + $half_page_end;

    if ( $start_page <= 0 ) {
        $start_page = 1;
    }
    
    if ( ( $end_page - $start_page ) !== $pages_to_show_minus_1 ) {
        $end_page = $start_page + $pages_to_show_minus_1;
    }
    
    if ( $end_page > $max_page ) {
        $start_page = $max_page - $pages_to_show_minus_1;
        $end_page   = $max_page;
    }

    if ( $start_page <= 0 ) {
        $start_page = 1;
    }

    // Output the navigation
    $out = '';

    // Build the base so that get_pagenum_link is called only once
    $link_base = str_replace( 99999999, '___', get_pagenum_link( 99999999 ) );
    $first_url = get_pagenum_link( 1 );
    
    if ( false === strpos( $first_url, '?' ) ) {
        $first_url = user_trailingslashit( $first_url );
    }

    $out .= $before . "<div class='wp-pagenavi'>\n";

        // Text before the pagination
        if ( $text_num_page ) {
            $text_num_page = preg_replace( '!{current}|{last}!', '%s', $text_num_page );
            $out .= sprintf( "<span class='pages'>{$text_num_page}</span> ", $paged, $max_page );
        }
        
        // Previous
        if ( $back_text && $paged !== 1 ) {
            $prev_link = ( ( $paged - 1 ) === 1 ) ? $first_url : str_replace( '___', ( $paged - 1 ), $link_base );
            $out .= '<a class="prev" href="' . $prev_link . '">' . $back_text . '</a> ';
        }
        
        // To the first page
        if ( $start_page >= 2 && $pages_to_show < $max_page ) {
            $first_text = $first_page_text ? $first_page_text : 1;
            $out .= '<a class="first" href="' . $first_url . '">' . $first_text . '</a> ';
            
            if ( $dotright_text && $start_page !== 2 ) {
                $out .= '<span class="extend">' . $dotright_text . '</span> ';
            }
        }
        
        // Pagination
        for ( $i = $start_page; $i <= $end_page; $i++ ) {
            if ( $i === $paged ) {
                $out .= '<span class="current">' . $i . '</span> ';
            } elseif ( $i === 1 ) {
                $out .= '<a href="' . $first_url . '">1</a> ';
            } else {
                $out .= '<a href="' . str_replace( '___', $i, $link_base ) . '">' . $i . '</a> ';
            }
        }

        // Step links
        $dd = 0;
        if ( $step_link && $end_page < $max_page ) {
            for ( $i = $end_page + 1; $i <= $max_page; $i++ ) {
                if ( $i % $step_link === 0 && $i !== $num_pages ) {
                    if ( ++$dd === 1 ) {
                        $out .= '<span class="extend">' . $dotright_text2 . '</span> ';
                    }
                    $out .= '<a href="' . str_replace( '___', $i, $link_base ) . '">' . $i . '</a> ';
                }
            }
        }
        
        // To the last page
        if ( $end_page < $max_page ) {
            if ( $dotright_text && $end_page !== ( $max_page - 1 ) ) {
                $out .= '<span class="extend">' . $dotright_text2 . '</span> ';
            }
            $last_text = $last_page_text ? $last_page_text : $max_page;
            $out .= '<a class="last" href="' . str_replace( '___', $max_page, $link_base ) . '">' . $last_text . '</a> ';
        }
        
        // Next
        if ( $next_text && $paged !== $end_page ) {
            $out .= '<a class="next" href="' . str_replace( '___', ( $paged + 1 ), $link_base ) . '">' . $next_text . '</a> ';
        }

    $out .= "</div>" . $after . "\n";

    $out = apply_filters( 'kama_pagenavi', $out );

    if ( $echo ) {
        echo $out;
        return;
    }

    return $out;
}

/**
 * Change history:
 * 2.5 - 2.5.1 - automatic reset of the main query.
 * 
 * Argon Shop (2.5.2-argon):
 * - Added ABSPATH protection
 * - Removed the dependency on extract()
 * - Strict comparisons (=== / !==) instead of loose ones
 * - Type checks and protection against division by zero
 * - PHP 8.5 compatibility
 */