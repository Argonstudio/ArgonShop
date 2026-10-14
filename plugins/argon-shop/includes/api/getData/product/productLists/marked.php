<?php
/**
 * Getting marked products (best sellers, new products)
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * WHERE TO TEST:
 * 1. Home page → "Best sellers" and "New products" blocks
 * 2. Category page → marked products of the category
 * 3. Product page → marked products (without the current one)
 * 
 * WHAT IT DOES:
 * Returns products with the _hit or _newProduct meta field
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FUNCTION: Get marked products
 * 
 * @param int    $count — number of products
 * @param string $type  — meta field type: '_hit' or '_newProduct'
 * 
 * @return WP_Query Object with products
 */
function as_get_marked_products( $count, $type ) {
    
    $post_type = 'product';
    $taxonomy = 'catalog';
    
    // Validation
    $count = absint( $count );
    
    if ( $count < 1 ) {
        $count = 12;
    }
    
    $type = sanitize_key( $type );
    
    $arg = array(
        'post_type'      => $post_type,
        'posts_per_page' => $count,
        'meta_query'     => array(
            array(
                'key'     => $type,
                'value'   => '',
                'compare' => '!=',
            ),
        ),
        'tax_query' => as_get_tax_query_marked_products( $post_type, $type, $taxonomy ),
    );
    
    // Do not show the current product's card on its own page
    if ( is_singular( $post_type ) ) {
        $current_id = get_the_ID();
        
        if ( $current_id ) {
            $arg['post__not_in'] = array( absint( $current_id ) );
        }
    }
    
    // On the cart page, exclude products that are already added
    if ( as_get_cartPageID() === get_queried_object_id() ) {
        
        $productsShoppingCart = as_getCookie( 'productsShoppingCart' );
        
        if ( isset( $productsShoppingCart ) && is_array( $productsShoppingCart ) && ! empty( $productsShoppingCart ) ) {
            
            $postNotIn = array();
            
            foreach ( $productsShoppingCart as $key => $value ) {
                $key_clean = absint( $key );
                
                if ( $key_clean > 0 ) {
                    $postNotIn[] = $key_clean;
                }
            }
            
            if ( ! empty( $postNotIn ) ) {
                $arg['post__not_in'] = $postNotIn;
            }
        }
    }
    
    $marked_products = new WP_Query( $arg );
    
    return $marked_products;
}

/**
 * FUNCTION: Build the tax_query for marked products
 * 
 * @param string $post_type — post type
 * @param string $type      — meta field type
 * @param string $taxonomy  — taxonomy
 * 
 * @return array|string tax_query array or an empty string
 */
function as_get_tax_query_marked_products( $post_type, $type, $taxonomy ) {
    
    $tax_query = '';
    
    $term_ids = as_get_terms_ids_marked_products( $post_type, $type, $taxonomy );
    
    if ( ! empty( $term_ids ) ) {
        $tax_query = array(
            array(
                'taxonomy' => $taxonomy,
                'field'    => 'id',
                'terms'    => $term_ids,
            ),
        );
    }
    
    return $tax_query;
}

/**
 * FUNCTION: Build the list of term IDs
 * 
 * @param string $post_type — post type
 * @param string $type      — meta field type
 * @param string $taxonomy  — taxonomy
 * 
 * @return array|string Array of term IDs or an empty string
 */
function as_get_terms_ids_marked_products( $post_type, $type, $taxonomy ) {
    
    if ( is_singular( $post_type ) ) {
        
        $terms = get_the_terms( get_the_ID(), $taxonomy );
        
        if ( ! $terms || is_wp_error( $terms ) ) {
            return '';
        }
        
        $ids = array();
        
        foreach ( $terms as $term ) {
            $ids[] = absint( $term->term_id );
        }
        
        $term_ids = $ids;
        
    } elseif ( is_tax( $taxonomy ) ) {
        
        $queried_object = get_queried_object();
        
        if ( ! $queried_object || ! isset( $queried_object->term_id ) ) {
            return '';
        }
        
        $term_ids = array( absint( $queried_object->term_id ) );
        
        $arg = array(
            'post_type'      => $post_type,
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'     => $type,
                    'value'   => '',
                    'compare' => '!=',
                ),
            ),
            'tax_query' => array(
                array(
                    'taxonomy' => $taxonomy,
                    'field'    => 'id',
                    'terms'    => $term_ids,
                ),
            ),
        );
        
        $markedProducts = new WP_Query( $arg );
        $found_markedProducts = $markedProducts->found_posts;
        
        if ( $found_markedProducts == 0 ) {
            $term_ids = '';
        }
        
    } else {
        $term_ids = '';
    }
    
    return $term_ids;
}