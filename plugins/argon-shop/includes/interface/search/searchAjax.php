<?php
/**
 * AJAX site search
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * WHERE TO TEST:
 * 1. Search field in the site header → typing text → dropdown results
 * 2. Search page → search bar
 * 3. In the admin panel
 * 
 * WHAT IT DOES:
 * 1. Searches products by name and SKU
 * 2. Searches catalog categories
 * 3. Searches posts (promotions, articles, news)
 * 4. Fixes the keyboard layout depending on the site language (see searchLanguage.php)
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * AJAX handler registration
 * wp_ajax_ — for logged-in users
 * wp_ajax_nopriv_ — for guests
 */
add_action( 'wp_ajax_getAjaxSearchResult', 'as_getAjaxSearchResult_callback' );
add_action( 'wp_ajax_nopriv_getAjaxSearchResult', 'as_getAjaxSearchResult_callback' );

/**
 * HANDLER: Site search
 * 
 * WHERE TO TEST: Search field → typing text
 * WHAT IT DOES:
 * 1. Gets the search text
 * 2. Calls as_queryManagerSearchResult() to search
 * 3. If there are no results — fixes the keyboard layout
 * 4. Outputs the results HTML
 */
function as_getAjaxSearchResult_callback() {
    
    // Nonce check
    check_ajax_referer( 'argon_shop_nonce', 'nonce' );
    
    // Check that the data is present
    if ( ! isset( $_POST['searchStr'] ) ) {
        wp_die( __( 'Not enough data', 'argon-shop' ) );
    }
    
    // Clean the search text
    $searchStr = sanitize_text_field( wp_unslash( $_POST['searchStr'] ) );
    
    // Get the search parameters
    $searchParameters = '';
    
    if ( isset( $_POST['dataAjax'] ) ) {
        $searchParameters = stripslashes( strip_tags( $_POST['dataAjax'] ) );
        $searchParameters = json_decode( $searchParameters );
    }
    
    // If the parameters are valid — run the search
    if ( gettype( $searchParameters ) === 'object' ) {
        
        $searchResult = as_queryManagerSearchResult( $searchStr, $searchParameters );
        
        // If empty and the text is not a number — try to fix the keyboard layout
        if ( empty( $searchResult ) && ! ctype_digit( $searchStr ) ) {
            
            foreach ( as_search_layout_variants( $searchStr ) as $searchStrFix ) {
                
                $searchResult = as_queryManagerSearchResult( $searchStrFix, $searchParameters );
                
                if ( ! empty( $searchResult ) ) {
                    break;
                }
            }
        }
        
        // Output the results
        if ( ! empty( $searchResult ) ) {
            as_showResult( $searchResult );
        } else {
            echo __( 'No matching results', 'argon-shop' );
        }
    }
    
    wp_die();
}

/*
 ** Getting matching categories and products from the database
*/

/**
 * FUNCTION: Search manager function
 * 
 * CALLED FROM: as_getAjaxSearchResult_callback()
 * WHAT IT DOES:
 * 1. Cleans the search parameters
 * 2. Searches products (if enabled)
 * 3. Searches catalog categories (if enabled)
 * 4. Searches posts (promotions, articles, news)
 * 
 * @param string $searchStr       — search text
 * @param object $searchParameters — search parameters (what to search)
 * 
 * @return array Array of results by type: product, catalog, post
 */
function as_queryManagerSearchResult( $searchStr, $searchParameters ) {
    
    $searchResult = array();
    
    // Clean the parameters
    $searchParameters = as_cleanParameters( $searchParameters );
    
    // Product search
    if ( empty( $searchParameters->productResult ) || ( $searchParameters->productResult ?? false ) === true ) {
        
        $searchResultProduct = as_queryAllocatorSearchResult( 'product', 10, $searchStr );
        
        if ( $searchResultProduct ) {
            
            $searchResult['product'] = array(
                'nameBlock'  => __( 'Matching products', 'argon-shop' ),
                'listResult' => as_prepProduct( $searchResultProduct ),
            );
        }
    }
    
    // Catalog category search
    if ( empty( $searchParameters->catalogResult ) || ( $searchParameters->catalogResult ?? false ) === true ) {
        
        $searchResultCatalog = as_queryAllocatorSearchResult( 'category', 10, $searchStr );
        
        if ( $searchResultCatalog ) {
            
            $searchResult['catalog'] = array(
                'nameBlock'  => __( 'Categories', 'argon-shop' ),
                'listResult' => as_prepCatalog( $searchResultCatalog ),
            );
        }
    }
    
    // Post search (promotions, articles, news)
    if ( ! empty( $searchParameters->postResult ) ) {
        
        foreach ( $searchParameters->postResult as $key => $value ) {
            
            $searchResultPost = as_queryAllocatorSearchResult( 'post', 10, $searchStr, $value );
            
            if ( $searchResultPost ) {
                
                $postListResult = array(
                    'nameBlock'  => $value->name,
                    'listResult' => as_prepPost( $searchResultPost ),
                );
                
                if ( $value->class ) {
                    $postListResult['classBlock'] = $value->class;
                }
                
                $searchResult['post'][] = $postListResult;
            }
        }
    }
    
    return $searchResult;
}

/**
 * FUNCTION: Query dispatcher
 * 
 * CALLED FROM: as_queryManagerSearchResult()
 * WHAT IT DOES: Routes the query to the right search function
 * 
 * @param string $type       — 'product', 'post' or 'category'
 * @param int    $quantity   — number of results
 * @param string $searchStr  — search text
 * @param object $parameters — additional parameters
 * 
 * @return array Search result
 */
function as_queryAllocatorSearchResult( $type = null, $quantity = null, $searchStr = null, $parameters = null ) {
    
    $searchResult = null;
    
    switch ( $type ) {
        
        case 'product':
            $searchResult = as_queryProductSearchResult( $searchStr, $quantity );
            break;
            
        case 'post':
            $searchResult = as_queryPostSearchResult( $searchStr, $quantity, $parameters );
            break;
            
        case 'category':
            $searchResult = as_queryTaxonomySearchResult( $searchStr, $quantity );
            break;
    }
    
    return $searchResult;
}

/*
 ** Database queries
*/

/**
 * FUNCTION: Taxonomy search (catalog categories)
 * 
 * CALLED FROM: as_queryAllocatorSearchResult()
 * WHAT IT DOES: Searches catalog taxonomy terms by name
 * 
 * @param string $searchStr — search text
 * @param int    $quantity  — maximum number of results
 * 
 * @return array List of terms id=>name
 */
function as_queryTaxonomySearchResult( $searchStr, $quantity ) {
    
    $argumentSearch = array(
        'taxonomy'     => 'catalog',
        'number'       => $quantity,
        'hierarchical' => true,
        'fields'       => 'id=>name',
        'search'       => $searchStr,
    );
    
    $querySearchTerms = get_terms( $argumentSearch );
    
    // WP_Error check
    if ( is_wp_error( $querySearchTerms ) ) {
        return array();
    }
    
    return $querySearchTerms;
}

/**
 * FUNCTION: Post search (articles, promotions)
 * 
 * CALLED FROM: as_queryAllocatorSearchResult()
 * WHAT IT DOES: Searches posts of type post in the given categories
 * 
 * @param string $searchStr  — search text
 * @param int    $quantity   — number of results
 * @param object $parameters — parameters (categories to search)
 * 
 * @return array Array of found post IDs
 */
function as_queryPostSearchResult( $searchStr, $quantity, $parameters ) {
    
    // Get the categories to search
    $categories = array();
    
    if ( isset( $parameters->category ) && is_array( $parameters->category ) ) {
        $categories = array_map( 'absint', $parameters->category );
    }
    
    $argumentSearch = array(
        'post_type'      => 'post',
        'posts_per_page' => $quantity,
        'fields'         => 'ids',
        's'              => $searchStr,
        'tax_query'      => array(
            array(
                'taxonomy' => 'category',
                'field'    => 'id',
                'terms'    => $categories,
            ),
        ),
    );
    
    $querySearchStr = new WP_Query( $argumentSearch );
    $querySearchStr = $querySearchStr->posts;
    
    return $querySearchStr;
}

/**
 * FUNCTION: Product search
 * 
 * Uses the same logic as the GET search (searchGetFilters.php):
 * - Multi-word AND search over title, excerpt, content, SKU
 * - Relevance score system
 * 
 * Additionally attaches the searchGetFilters.php filters for the duration
 * of the query, so that AJAX and GET return the same result.
 * 
 * @param string $searchStr — search text
 * @param int    $quantity  — number of results
 * 
 * @return array Array of WP_Post objects
 */
function as_queryProductSearchResult( $searchStr, $quantity ) {
    
    $quantity = absint( $quantity );
    
    if ( empty( $searchStr ) || $quantity < 1 ) {
        return array();
    }
    
    // Attach the filters from searchGetFilters.php.
    // They are active only if as_search_is_custom_search()
    // returns true. For an AJAX request it returns false (no is_main_query),
    // so separate wrapper filters with the _argon_search flag are used.
    
    add_filter( 'posts_search',  'argon_search_ajax_where', 10, 2 );
    add_filter( 'posts_join',    'argon_search_ajax_join', 10, 2 );
    add_filter( 'posts_distinct', 'argon_search_ajax_distinct', 10, 2 );
    add_filter( 'posts_orderby', 'argon_search_ajax_orderby', 10, 2 );
    
    $query = new WP_Query( array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => $quantity,
        's'              => $searchStr,
        '_argon_search'  => true,
    ) );
    
    remove_filter( 'posts_search',  'argon_search_ajax_where', 10 );
    remove_filter( 'posts_join',    'argon_search_ajax_join', 10 );
    remove_filter( 'posts_distinct', 'argon_search_ajax_distinct', 10 );
    remove_filter( 'posts_orderby', 'argon_search_ajax_orderby', 10 );
    
    if ( ! $query instanceof WP_Query || empty( $query->posts ) ) {
        return array();
    }
    
    return $query->posts;
}

// ============================================
// AJAX PRODUCT SEARCH FILTERS
// ============================================
// 
// They duplicate the searchGetFilters.php logic but apply only to
// queries with the _argon_search flag (AJAX search). The main WordPress
// search on /?s= is not affected.

/**
 * Check whether the AJAX filters should be applied
 */
function argon_search_ajax_needed( $query ) {
    return (bool) $query->get( '_argon_search' );
}

/**
 * FILTER: WHERE — multi-word AND search
 */
function argon_search_ajax_where( $search, $query ) {
    
    if ( ! argon_search_ajax_needed( $query ) ) {
        return $search;
    }
    
    // The SQL is built by as_search_where_sql() (searchLanguage.php)
    $where = as_search_where_sql( $query->get( 's' ) );
    
    return '' === $where ? $search : $where;
}

/**
 * FILTER: JOIN on postmeta for the SKU
 */
function argon_search_ajax_join( $join, $query ) {
    
    global $wpdb;
    
    if ( ! argon_search_ajax_needed( $query ) ) {
        return $join;
    }
    
    $join .= " LEFT JOIN {$wpdb->postmeta} AS as_article_meta 
               ON ( {$wpdb->posts}.ID = as_article_meta.post_id 
                    AND as_article_meta.meta_key = '_article' ) ";
    
    return $join;
}

/**
 * FILTER: DISTINCT — protection against duplicates
 */
function argon_search_ajax_distinct( $distinct, $query ) {
    
    if ( ! argon_search_ajax_needed( $query ) ) {
        return $distinct;
    }
    
    return 'DISTINCT';
}

/**
 * FILTER: ORDER BY — sorting by relevance score
 */
function argon_search_ajax_orderby( $orderby, $query ) {
    
    global $wpdb;
    
    if ( ! argon_search_ajax_needed( $query ) ) {
        return $orderby;
    }
    
    $s     = $query->get( 's' );
    $words = function_exists( 'as_search_get_words' ) ? as_search_get_words( $s ) : array();
    
    if ( empty( $words ) ) {
        return $orderby;
    }
    
    // Full phrase
    $full_trim  = trim( $s );
    $full_exact = $full_trim;
    $full_start = $wpdb->esc_like( $full_trim ) . '%';
    
    $score_parts = array();
    
    $score_parts[] = $wpdb->prepare( "CASE WHEN {$wpdb->posts}.post_title = %s THEN 1000 ELSE 0 END", $full_exact );
    $score_parts[] = $wpdb->prepare( "CASE WHEN as_article_meta.meta_value = %s THEN 800 ELSE 0 END", $full_exact );
    $score_parts[] = $wpdb->prepare( "CASE WHEN {$wpdb->posts}.post_title LIKE %s THEN 500 ELSE 0 END", $full_start );
    $score_parts[] = $wpdb->prepare( "CASE WHEN as_article_meta.meta_value LIKE %s THEN 400 ELSE 0 END", $full_start );
    
    foreach ( $words as $word ) {
        
        $word_start       = $wpdb->esc_like( $word ) . '%';
        $word_like        = '%' . $wpdb->esc_like( $word ) . '%';
        $word_after_space = '% ' . $wpdb->esc_like( $word ) . '%';
        
        $score_parts[] = $wpdb->prepare( "CASE WHEN {$wpdb->posts}.post_title LIKE %s THEN 50 ELSE 0 END", $word_start );
        $score_parts[] = $wpdb->prepare( "CASE WHEN {$wpdb->posts}.post_title LIKE %s THEN 30 ELSE 0 END", $word_after_space );
        $score_parts[] = $wpdb->prepare( "CASE WHEN {$wpdb->posts}.post_title LIKE %s THEN 10 ELSE 0 END", $word_like );
        $score_parts[] = $wpdb->prepare( "CASE WHEN as_article_meta.meta_value LIKE %s THEN 5 ELSE 0 END", $word_like );
    }
    
    $score_sql = '( ' . implode( ' + ', $score_parts ) . ' )';
    
    return "{$score_sql} DESC, {$wpdb->posts}.post_date DESC, {$wpdb->posts}.ID DESC";
}

/*
 ** Building arrays with output information (url/title)
*/

/**
 * FUNCTION: Prepare category data for output
 * 
 * CALLED FROM: as_queryManagerSearchResult()
 * WHAT IT DOES: Converts terms to an array with title and url
 * 
 * @param array $searchResultCatalog — taxonomy terms
 * 
 * @return array Array with post_title and post_url
 */
function as_prepCatalog( $searchResultCatalog ) {
    
    $prepCatalog = array();
    
    foreach ( $searchResultCatalog as $key => $value ) {
        
        $key = absint( $key );
        
        $prepCatalog[ $key ] = array(
            'post_title' => esc_html( $value ),
            'post_url'   => esc_url( get_category_link( $key ) ),
        );
    }
    
    return $prepCatalog;
}

/**
 * FUNCTION: Prepare post data for output
 * 
 * CALLED FROM: as_queryManagerSearchResult()
 * WHAT IT DOES: Converts post IDs to an array with title and url
 * 
 * @param array $searchResultPost — array of post IDs
 * 
 * @return array Array with post_title and post_url
 */
function as_prepPost( $searchResultPost ) {
    
    $prepPost = array();
    
    foreach ( $searchResultPost as $key => $value ) {
        
        $value = absint( $value );
        
        $prepPost[ $value ] = array(
            'post_title' => esc_html( get_the_title( $value ) ),
            'post_url'   => esc_url( get_permalink( $value ) ),
        );
    }
    
    return $prepPost;
}

/**
 * FUNCTION: Prepare product data for output
 * 
 * CALLED FROM: as_queryManagerSearchResult()
 * WHAT IT DOES:
 * 1. Gets the product name and URL
 * 2. Gets the product category (if any)
 * 
 * @param array $searchResultProduct — array of WP_Post objects
 * 
 * @return array Array with post_title, post_url, category_name, category_url
 */
function as_prepProduct( $searchResultProduct ) {
    
    $prepProduct = array();
    
    foreach ( $searchResultProduct as $key => $value ) {
        
        $product_id = absint( $value->ID );
        
        $prepProduct[ $product_id ] = array(
            'post_title' => esc_html( $value->post_title ),
            'post_url'   => esc_url( get_permalink( $product_id ) ),
        );
        
        // Get the product category
        $categoryProduct = get_the_terms( $product_id, 'catalog' );
        
        if ( ! empty( $categoryProduct ) && ! is_wp_error( $categoryProduct ) ) {
            
            $prepProduct[ $product_id ]['category_name'] = esc_html( $categoryProduct[0]->name );
            $prepProduct[ $product_id ]['category_url'] = esc_url( get_category_link( $categoryProduct[0]->term_id ) );
        }
    }
    
    return $prepProduct;
}

/*
 ** Building the HTML view of the result
*/

/**
 * FUNCTION: Output the search results
 * 
 * CALLED FROM: as_getAjaxSearchResult_callback()
 * WHAT IT DOES: Iterates over all result types and calls as_generateResultHTML()
 * 
 * @param array $searchResult — search results
 */
function as_showResult( $searchResult ) {
    
    foreach ( $searchResult as $keyEntirelyResult => $valueEntirelyResult ) {
        
        // For posts (several blocks)
        if ( $keyEntirelyResult === 'post' ) {
            
            foreach ( $valueEntirelyResult as $keyOneTypeResults => $valueOneTypeResults ) {
                as_generateResultHTML( $keyEntirelyResult, $valueOneTypeResults );
            }
            
        } else {
            // For products and categories (a single block)
            as_generateResultHTML( $keyEntirelyResult, $valueEntirelyResult );
        }
    }
}

/**
 * FUNCTION: Generate the HTML of one result block
 * 
 * CALLED FROM: as_showResult()
 * WHAT IT DOES: Outputs the block title and the list of found items
 * 
 * @param string $type                — result type
 * @param array  $valueEntirelyResult — block data (nameBlock, listResult, classBlock)
 */
function as_generateResultHTML( $type, $valueEntirelyResult ) {
    
    $classBlockOneTypeResult = 'blockOneTypeResult';
    
    if ( ! empty( $valueEntirelyResult['classBlock'] ) ) {
        $classBlockOneTypeResult .= ' ' . sanitize_html_class( $valueEntirelyResult['classBlock'] );
    }
    
    $classBlockOneTypeResult_esc = esc_attr( $classBlockOneTypeResult );
    $nameBlock = isset( $valueEntirelyResult['nameBlock'] ) ? esc_html( $valueEntirelyResult['nameBlock'] ) : '';
    
    ?>
    
    <div class="<?php echo $classBlockOneTypeResult_esc; ?>">
        
        <div class="titleOneTypeResult">
            <?php echo $nameBlock; ?>
        </div>
        
        <?php
        
        $classBlockProduct = 'resultProduct';
        
        if ( ! empty( $valueEntirelyResult['listResult'] ) && is_array( $valueEntirelyResult['listResult'] ) ) {
            
            foreach ( $valueEntirelyResult['listResult'] as $idOneResult => $valueOneResult ) {
                
                $category_name = isset( $valueOneResult['category_name'] ) ? esc_html( $valueOneResult['category_name'] ) : '';
                $category_url = isset( $valueOneResult['category_url'] ) ? esc_url( $valueOneResult['category_url'] ) : '';
                $post_url = isset( $valueOneResult['post_url'] ) ? esc_url( $valueOneResult['post_url'] ) : '';
                $post_title = isset( $valueOneResult['post_title'] ) ? esc_html( $valueOneResult['post_title'] ) : '';
                
                ?>
                
                <div class="<?php echo esc_attr( $classBlockProduct ); ?>">
                    
                    <?php if ( ! empty( $category_name ) ) { ?>
                        
                        <span><a href="<?php echo $category_url; ?>"><?php echo $category_name; ?></a></span> → 
                        
                    <?php } ?>
                    
                    <a href="<?php echo $post_url; ?>"><?php echo $post_title; ?></a>
                    
                </div>
                
                <?php
            }
        }
        ?>
        
    </div>
    <?php
}

/*
 ** Helper functions
*/

/**
 * FUNCTION: Merge result arrays
 * 
 * CALLED FROM: as_queryProductSearchResult()
 * WHAT IT DOES: Merges several arrays giving priority to the first ones
 * 
 * @param array $typesResultArray — array of arrays to merge
 * @param int   $quantity         — resulting number of items
 * 
 * @return array Merged array
 */
function as_merge_search_result( $typesResultArray, $quantity ) {
    
    $quantityResults = (int) $quantity;
    $searchStrResult = array();
    
    foreach ( $typesResultArray as $key => $value ) {
        
        if ( isset( $value ) && is_array( $value ) ) {
            
            $searchStrResult = as_array_partial_merge( $quantityResults, $searchStrResult, $value );
            $quantityResults -= count( $value );
        }
    }
    
    return $searchStrResult;
}

/**
 * FUNCTION: Clean the search parameters
 * 
 * CALLED FROM: as_queryManagerSearchResult()
 * WHAT IT DOES: Validates the incoming parameters, keeping only valid ones
 * 
 * @param object $searchParameters — raw parameters
 * 
 * @return object Cleaned parameters
 */
function as_cleanParameters( $searchParameters ) {
    
    $cleanSearchParameters = (object) array();
    
    // Clean the productResult parameter (boolean)
    if ( isset( $searchParameters->productResult ) && gettype( $searchParameters->productResult ) === 'boolean' ) {
        $cleanSearchParameters->productResult = $searchParameters->productResult;
    }
    
    // Clean the catalogResult parameter (boolean)
    if ( isset( $searchParameters->catalogResult ) && gettype( $searchParameters->catalogResult ) === 'boolean' ) {
        $cleanSearchParameters->catalogResult = $searchParameters->catalogResult;
    }
    
    // Clean the postResult parameter (array)
    if ( ! empty( $searchParameters->postResult ) && gettype( $searchParameters->postResult ) === 'array' ) {
        
        foreach ( $searchParameters->postResult as $key => $value ) {
            
            $cleanValue = (object) array();
            
            if ( gettype( $value ) === 'object' ) {
                
                if ( ! empty( $value->name ) && gettype( $value->name ) === 'string' ) {
                    $cleanValue->name = sanitize_text_field( $value->name );
                }
                
                if ( ! empty( $value->class ) && gettype( $value->class ) === 'string' ) {
                    $cleanValue->class = sanitize_text_field( $value->class );
                }
                
                if ( ! empty( $value->category ) && gettype( $value->category ) === 'array' ) {
                    
                    $cleanCategory = array();
                    
                    foreach ( $value->category as $idCategory ) {
                        
                        if ( gettype( $idCategory ) === 'integer' ) {
                            $cleanCategory[] = $idCategory;
                        }
                    }
                    
                    if ( ! empty( $cleanCategory ) ) {
                        $cleanValue->category = $cleanCategory;
                    }
                }
            }
            
            $cleanSearchParameters->postResult[] = $cleanValue;
        }
    }
    
    return $cleanSearchParameters;
}

/**
 * FUNCTION: Keyboard layout fix (kept for compatibility with themes and older code)
 *
 * Returns the first variant from as_search_layout_variants() (searchLanguage.php),
 * which depends on the site language: on a Russian site "ghjlern" becomes
 * the Russian word for "product", on an English site Cyrillic input becomes QWERTY,
 * on a French site — AZERTY.
 *
 * @param string $searchStr — text typed in the wrong layout
 *
 * @return string Fixed text (or the original if there is nothing to fix)
 */
function as_fixKeyboardlayout( $searchStr ) {
    
    $variants = as_search_layout_variants( $searchStr );
    
    return $variants ? $variants[0] : (string) $searchStr;
}
