<?php
/**
 * Additional order filters in the admin panel
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * WHERE TO TEST:
 * WordPress Admin → Orders → Order list
 * URL: /wp-admin/edit.php?post_type=shoporder
 * 
 * WHAT IT DOES:
 * 1. Adds a dropdown for filtering orders by status
 * 2. Applies the selected filter to the query
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FUNCTION: Output the order status filter dropdown
 * 
 * WHERE: Order list page → above the table
 * WHAT IT DOES: Outputs a select with the statusorders taxonomy
 */
function as_restrict_posts_by_money() {
    
    global $typenow;
    $post_type = 'shoporder';
    $taxonomy = 'statusorders';
    
    $countPosts = wp_count_posts( 'shoporder' );
    
    if ( $countPosts->publish ) {
        
        if ( $typenow == $post_type ) {
            
            $selected = isset( $_GET[ $taxonomy ] ) ? absint( $_GET[ $taxonomy ] ) : '';
            
            $info_taxonomy = get_taxonomy( $taxonomy );
            
            wp_dropdown_categories( array(
                'show_option_all' => __( 'All statuses', 'argon-shop' ),
                'taxonomy'        => $taxonomy,
                'name'            => $taxonomy,
                'orderby'         => 'name',
                'selected'        => $selected,
                'show_count'      => true,
                'hide_empty'      => true,
            ) );
        }
    }
}

add_action( 'restrict_manage_posts', 'as_restrict_posts_by_money' );

/**
 * FUNCTION: Convert the term ID to a slug for filtering
 * 
 * WHERE: Automatically when the order list is queried
 * WHAT IT DOES: When the user picks a status from the dropdown,
 *             WordPress passes the term ID. This function converts
 *             the ID to a slug so that WP_Query works correctly.
 */
function as_convert_id_to_term_in_query( $query ) {
    
    global $pagenow;
    
    $post_type = 'shoporder';
    $taxonomy = 'statusorders';
    
    $q_vars = &$query->query_vars;
    
    if ( $pagenow == 'edit.php' && isset( $q_vars['post_type'] ) && $q_vars['post_type'] == $post_type && isset( $q_vars[ $taxonomy ] ) && is_numeric( $q_vars[ $taxonomy ] ) && $q_vars[ $taxonomy ] != 0 ) {
        
        $term_id = absint( $q_vars[ $taxonomy ] );
        $term = get_term_by( 'id', $term_id, $taxonomy );
        
        // Make sure the term was found and is not an error
        if ( $term && ! is_wp_error( $term ) ) {
            $q_vars[ $taxonomy ] = $term->slug;
        }
    }
}

add_filter( 'parse_query', 'as_convert_id_to_term_in_query' );