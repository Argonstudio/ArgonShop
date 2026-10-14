<?php
/**
 * Search result filters and sorting (GET part)
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * WHERE TO TEST:
 * Search form → enter a query → the /?s=query page
 * URL: /?s=query&post_type[]=product&as_taxonomy[catalog]=all
 * 
 * WHAT IT DOES:
 * 1. Applies the taxonomy filter from the GET parameters
 *    (as_taxonomy[catalog]=all → search in all catalog categories)
 * 
 * 2. Applies the custom search:
 *    - Splits the query into words (AND logic; language rules — searchLanguage.php)
 *    - Searches title, excerpt, content and also the SKU (_article)
 * 
 * 3. Sorts by relevance:
 *    - Exact title / SKU match — top priority
 *    - Title starting with the query
 *    - Word occurring in the title
 *    - Word occurring in the SKU
 * 
 * Relation to the AJAX search (searchAjax.php):
 * Uses the same priority logic as as_queryProductSearchResult().
 * Differs by using a score system instead of simple grouping.
 * 
 * The JOIN on postmeta is needed to access the _article field in WHERE and ORDER BY.
 * DISTINCT protects against duplicates caused by the JOIN.
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FUNCTION: Check whether the custom search applies to the query
 *
 * The custom search applies only:
 * - on the frontend
 * - to the main query
 * - only in search (is_search)
 * - if products (product) are among the post types
 *
 * @param WP_Query $query — WordPress query object
 *
 * @return bool
 */
function as_search_is_custom_search( $query ) {
    
    if ( is_admin() || ! $query->is_main_query() ) {
        return false;
    }
    
    if ( ! $query->is_search ) {
        return false;
    }
    
    $s = $query->get( 's' );
    
    if ( empty( $s ) || ! is_string( $s ) ) {
        return false;
    }
    
    // If post_type is restricted and it is not product — do not apply
    $post_types = $query->get( 'post_type' );
    
    if ( ! empty( $post_types ) && $post_types !== 'any' ) {
        
        $post_types = (array) $post_types;
        
        if ( ! in_array( 'product', $post_types, true ) ) {
            return false;
        }
    }
    
    return true;
}

/**
 * FUNCTION: Keyboard layout fix for the GET search
 * 
 * If the query found nothing and it was typed in the wrong
 * layout (for example, "ghjlern" typed on a Russian site, or "super" typed with a Russian layout on an English one) —
 * automatically fixes the layout and replaces the 's' parameter in
 * the main query.
 * 
 * Works like the AJAX search; the variants come from as_search_layout_variants()
 * (includes/interface/search/searchLanguage.php).
 * 
 * @param WP_Query $query — main WordPress query object
 */
function argon_search_fix_keyboard_layout( $query ) {
    
    if ( is_admin() || ! $query->is_main_query() || ! $query->is_search ) {
        return;
    }
    
    $s = $query->get( 's' );
    
    if ( empty( $s ) || ! is_string( $s ) || ctype_digit( $s ) ) {
        return;
    }
    
    // Check whether the original query has results
    $test_query = new WP_Query( array(
        'post_type'      => 'any',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        's'              => $s,
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ) );
    
    if ( $test_query->have_posts() ) {
        return;
    }
    
    wp_reset_postdata();
    
    // Variants depend on the site language (searchLanguage.php)
    if ( ! function_exists( 'as_search_layout_variants' ) ) {
        return;
    }
    
    foreach ( as_search_layout_variants( $s ) as $fixed ) {
        
        // Check whether the fixed query has results
        $test_query2 = new WP_Query( array(
            'post_type'      => 'any',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            's'              => $fixed,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ) );
        
        if ( $test_query2->have_posts() ) {
            // Replace s in the main query
            $query->set( 's', $fixed );
            
            // Keep the original query to show it to the user
            $query->set( '_argon_original_s', $s );
            
            break;
        }
    }
    
    wp_reset_postdata();
}

add_action( 'pre_get_posts', 'argon_search_fix_keyboard_layout', 5 );

// ============================================
// TAXONOMY FILTER (pre_get_posts)
// ============================================

/**
 * FUNCTION: Filter the main query by taxonomies from GET
 *
 * Reads as_taxonomy[] from the URL and adds a tax_query.
 * The "all" value is replaced with the list of all terms of the taxonomy.
 * The array of term IDs is passed from the form via serialize().
 *
 * @param WP_Query $query — WordPress query object
 */
function as_search_filter_taxonomy( $query ) {
    
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }
    
    if ( ! $query->is_search ) {
        return;
    }
    
    if ( ! isset( $_GET['post_type'] ) || ! isset( $_GET['as_taxonomy'] ) ) {
        return;
    }
    
    $taxonomy = $_GET['as_taxonomy'];
    
    if ( ! is_array( $taxonomy ) ) {
        return;
    }
    
    $tax_query = array( 'relation' => 'OR' );
    
    foreach ( $taxonomy as $key => $value ) {
        
        $key = sanitize_key( $key );
        
        if ( empty( $key ) || ! taxonomy_exists( $key ) ) {
            continue;
        }
        
        $taxonomy_query = array(
            'taxonomy' => $key,
            'field'    => 'id',
        );
        
        $taxonomy_terms = array();
        
        // "all" — take all terms of the taxonomy
        if ( $value === 'all' ) {
            
            $args = array(
                'taxonomy' => $key,
                'fields'   => 'ids',
                'get'      => 'all',
            );
            
            $taxonomy_terms = get_terms( $args );
            
            if ( is_wp_error( $taxonomy_terms ) ) {
                continue;
            }
            
        } else {
            // The array of term IDs is passed via serialize()
            $value = wp_unslash( $value );
            
            if ( strlen( $value ) > 10000 ) {
                continue;
            }
            
            $unserialized = @unserialize( $value, array( 'allowed_classes' => false ) );
            
            if ( is_array( $unserialized ) ) {
                $taxonomy_terms = array_map( 'absint', $unserialized );
            }
        }
        
        if ( empty( $taxonomy_terms ) ) {
            continue;
        }
        
        $taxonomy_query['terms'] = $taxonomy_terms;
        
        array_push( $tax_query, $taxonomy_query );
    }
    
    if ( count( $tax_query ) <= 1 ) {
        return;
    }
    
    $query->set( 'tax_query', $tax_query );
}

add_action( 'pre_get_posts', 'as_search_filter_taxonomy' );

// ============================================
// JOIN ON POSTMETA FOR THE SKU
// ============================================

/**
 * FUNCTION: JOIN on wp_postmeta to get _article
 *
 * Applied only to the custom search. Makes it possible
 * to use as_article_meta.meta_value in WHERE and ORDER BY.
 *
 * @param string   $join  — current JOIN
 * @param WP_Query $query — query object
 *
 * @return string
 */
function as_search_join_article( $join, $query ) {
    
    global $wpdb;
    
    if ( ! as_search_is_custom_search( $query ) ) {
        return $join;
    }
    
    $join .= " LEFT JOIN {$wpdb->postmeta} AS as_article_meta 
               ON ( {$wpdb->posts}.ID = as_article_meta.post_id 
                    AND as_article_meta.meta_key = '_article' ) ";
    
    return $join;
}

add_filter( 'posts_join', 'as_search_join_article', 10, 2 );

// ============================================
// DISTINCT — PROTECTION AGAINST DUPLICATES
// ============================================

/**
 * FUNCTION: DISTINCT for the query
 *
 * With a JOIN on postmeta a single post can produce several rows
 * if it has several _article records (theoretically).
 * DISTINCT guarantees unique posts in the results.
 *
 * @param string   $distinct — current value
 * @param WP_Query $query    — query object
 *
 * @return string
 */
function as_search_distinct( $distinct, $query ) {
    
    if ( ! as_search_is_custom_search( $query ) ) {
        return $distinct;
    }
    
    return 'DISTINCT';
}

add_filter( 'posts_distinct', 'as_search_distinct', 10, 2 );

// ============================================
// WHERE — CUSTOM SEARCH
// ============================================

/**
 * FUNCTION: Custom WHERE for search
 *
 * Replaces the default WP search with a multi-word one:
 * - AND between words
 * - OR inside a word (title, excerpt, content, SKU, spelling variants)
 *
 * Example for the query "moisture resistant drywall":
 *   (title LIKE '%moisture%' OR ... OR article LIKE '%moisture%')
 *   AND
 *   (title LIKE '%drywall%' OR ... OR article LIKE '%drywall%')
 *
 * @param string   $search — current search WHERE
 * @param WP_Query $query  — query object
 *
 * @return string
 */
function as_search_custom_where( $search, $query ) {
    
    if ( ! as_search_is_custom_search( $query ) ) {
        return $search;
    }
    
    // The SQL is built by as_search_where_sql() (searchLanguage.php)
    $where = as_search_where_sql( $query->get( 's' ) );
    
    return '' === $where ? $search : $where;
}

add_filter( 'posts_search', 'as_search_custom_where', 10, 2 );

// ============================================
// ORDER BY — SORTING BY RELEVANCE
// ============================================

/**
 * FUNCTION: Sort results by relevance
 *
 * Builds an SQL expression with the sum of scores for each post.
 * The more points, the higher the post in the results.
 *
 * Points:
 *   1000 — exact match title = full phrase
 *    800 — exact match SKU = full phrase
 *    500 — title starts with the full phrase
 *    400 — SKU starts with the full phrase
 *     50 — query word at the start of the title
 *     30 — query word after a space in the title
 *     10 — query word in the title (anywhere)
 *      5 — query word in the SKU
 *
 * With equal points — sort by date DESC.
 *
 * @param string   $orderby — current ORDER BY
 * @param WP_Query $query   — query object
 *
 * @return string
 */
function as_search_custom_orderby( $orderby, $query ) {
    
    global $wpdb;
    
    if ( ! as_search_is_custom_search( $query ) ) {
        return $orderby;
    }
    
    $s = $query->get( 's' );
    $words = as_search_get_words( $s );
    
    if ( empty( $words ) ) {
        return $orderby;
    }
    
    // Full phrase
    $full_trim = trim( $s );
    $full_exact = $full_trim;
    $full_start = $wpdb->esc_like( $full_trim ) . '%';
    
    $score_parts = array();
    
    // Full phrase priorities
    $score_parts[] = $wpdb->prepare( "CASE WHEN {$wpdb->posts}.post_title = %s THEN 1000 ELSE 0 END", $full_exact );
    $score_parts[] = $wpdb->prepare( "CASE WHEN as_article_meta.meta_value = %s THEN 800 ELSE 0 END", $full_exact );
    $score_parts[] = $wpdb->prepare( "CASE WHEN {$wpdb->posts}.post_title LIKE %s THEN 500 ELSE 0 END", $full_start );
    $score_parts[] = $wpdb->prepare( "CASE WHEN as_article_meta.meta_value LIKE %s THEN 400 ELSE 0 END", $full_start );
    
    // Per-word priorities
    foreach ( $words as $word ) {
        
        $word_start       = $wpdb->esc_like( $word ) . '%';
        $word_like        = '%' . $wpdb->esc_like( $word ) . '%';
        $word_after_space = '% ' . $wpdb->esc_like( $word ) . '%';
        
        // Word at the start of the title
        $score_parts[] = $wpdb->prepare( "CASE WHEN {$wpdb->posts}.post_title LIKE %s THEN 50 ELSE 0 END", $word_start );
        
        // Word after a space in the title
        $score_parts[] = $wpdb->prepare( "CASE WHEN {$wpdb->posts}.post_title LIKE %s THEN 30 ELSE 0 END", $word_after_space );
        
        // Word in the title (anywhere)
        $score_parts[] = $wpdb->prepare( "CASE WHEN {$wpdb->posts}.post_title LIKE %s THEN 10 ELSE 0 END", $word_like );
        
        // Word in the SKU
        $score_parts[] = $wpdb->prepare( "CASE WHEN as_article_meta.meta_value LIKE %s THEN 5 ELSE 0 END", $word_like );
    }
    
    $score_sql = '( ' . implode( ' + ', $score_parts ) . ' )';
    
    return "{$score_sql} DESC, {$wpdb->posts}.post_date DESC, {$wpdb->posts}.ID DESC";
}

add_filter( 'posts_orderby', 'as_search_custom_orderby', 10, 2 );