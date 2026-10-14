<?php
/**
 * Site search form
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * WHERE TO TEST:
 * 1. Search form in the site header
 * 
 * WHAT IT DOES:
 * 1. Outputs the search form with its settings
 * 2. Supports AJAX search
 * 3. Outputs hidden fields for post types and taxonomies
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FUNCTION: Output the search form
 * 
 * WHERE IT IS USED: In theme templates to output the search form
 * 
 * SUPPORTS:
 * 1. Regular search (GET submission to /?s=query)
 * 2. AJAX search (results without reloading the page)
 * 3. Search across several post types (products, articles, pages)
 * 4. Filtering by taxonomies (catalog, categories)
 * 5. Several result blocks (promotions, news, blog)
 * 
 * IMPORTANT: Search over posts and post categories works "out of the box",
 *        without extra plugin changes. If there are no
 *        products or no catalog taxonomy — the corresponding blocks
 *        simply do not appear in the results (without errors).
 * 
 * PARAMETER STRUCTURE:
 * 
 * @param array $searchParameters {
 *     Array of search form parameters.
 * 
 *     @type array  $post_type      Array of post types to search.
 *                                  For posts and categories: array('post').
 *                                  For products: array('product').
 *                                  Can be combined: array('product', 'post').
 *                                  Default: all types.
 * 
 *     @type array  $taxonomy       Array of taxonomies to filter by.
 *                                  For post categories: array('category' => 'all')
 *                                  or array('category' => array(36, 37)).
 *                                  For the catalog: array('catalog' => 'all').
 *                                  Default: all taxonomies.
 * 
 *     @type string $classForm      CSS class for <form>.
 *                                  Default: 'asSearchForm'.
 * 
 *     @type string $classInput     CSS class for <input type="text">.
 *                                  Default: 'asInputSearchForm'.
 * 
 *     @type string $classSubmit    CSS class for <input type="submit">.
 *                                  Default: 'asSubmitSearchForm'.
 * 
 *     @type string $valueSubmit    Search button text.
 *                                  Default: 'Search' (translated).
 * 
 *     @type array  $ajax {
 *         AJAX search settings. If missing — regular search.
 * 
 *         @type string       $classBlockResult CSS class of the results container.
 *         @type string       $positionResult   Container position: 'top' or 'bottom'.
 *                                              Default: 'bottom'.
 * 
 *         @type bool         $productResult    Whether to search products.
 *                                              If there are no products — the block does not appear.
 *                                              Default: not passed.
 * 
 *         @type bool         $catalogResult    Whether to search catalog categories.
 *                                              If there is no catalog taxonomy — the block does not appear.
 *                                              Default: not passed.
 * 
 *         @type array|bool   $postResult {
 *             Array of additional post search blocks.
 *             Works independently of productResult and catalogResult.
 * 
 *             @type array $item {
 *                 @type string $name     Block title (for example, "Promotions and articles").
 *                 @type string $class    Block CSS class.
 *                 @type array  $category Array of post category IDs to search.
 *             }
 *         }
 *     }
 * }
 * 
 * CALL EXAMPLES:
 * 
 * 1. Simple search (without AJAX):
 * 
 *     as_search( array(
 *         'post_type'   => array( 'post' ),
 *         'taxonomy'    => array( 'category' => 'all' ),
 *         'valueSubmit' => 'Find',
 *     ) );
 * 
 * 2. AJAX search over posts and categories (works without changes):
 * 
 *     as_search( array(
 *         'post_type' => array( 'post' ),
 *         'taxonomy'  => array( 'category' => 'all' ),
 *         'ajax'      => array(
 *             'classBlockResult' => 'topSearchBlockResult',
 *             'positionResult'   => 'bottom',
 *             'postResult'       => array(
 *                 array(
 *                     'name'     => 'Articles',
 *                     'class'    => 'resultEntry',
 *                     'category' => array( 36, 37 ),
 *                 ),
 *                 array(
 *                     'name'     => 'News',
 *                     'class'    => 'resultEntry',
 *                     'category' => array( 1 ),
 *                 ),
 *             ),
 *         ),
 *     ) );
 * 
 * 3. Full AJAX search (products + posts, requires catalog):
 * 
 *     as_search( array(
 *         'post_type' => array( 'product', 'post' ),
 *         'taxonomy'  => array( 'catalog' => 'all' ),
 *         'ajax'      => array(
 *             'classBlockResult' => 'topSearchBlockResult',
 *             'positionResult'   => 'bottom',
 *             'productResult'    => true,
 *             'catalogResult'    => true,
 *             'postResult'       => array(
 *                 array(
 *                     'name'     => 'Promotions and articles',
 *                     'class'    => 'resultEntry',
 *                     'category' => array( 36, 37 ),
 *                 ),
 *             ),
 *         ),
 *     ) );
 * 
 * RETURNS: Nothing. Outputs the search form HTML.
 * 
 * @see as_ajaxBlock() — output of the AJAX results container
 */
function as_search( $searchParameters ) {
    
    if ( ! is_array( $searchParameters ) ) {
        $searchParameters = array();
    }
    
    $classForm = 'asSearchForm';
    $classInputForm = 'asInputSearchForm';
    $classButtonForm = 'asSubmitSearchForm';
    
    if ( ! empty( $searchParameters['classForm'] ) ) {
        $classForm .= ' ' . esc_attr( $searchParameters['classForm'] );
    }
    
    if ( ! empty( $searchParameters['classInput'] ) ) {
        $classInputForm .= ' ' . esc_attr( $searchParameters['classInput'] );
    }
    
    if ( ! empty( $searchParameters['classSubmit'] ) ) {
        $classButtonForm .= ' ' . esc_attr( $searchParameters['classSubmit'] );
    }
    
    // Button value
    if ( isset( $searchParameters['valueSubmit'] ) ) {
        $valueSubmit = $searchParameters['valueSubmit'];
    } else {
        $valueSubmit = __( 'Search', 'argon-shop' );
    }
    
    $valueSubmit_esc = esc_attr( $valueSubmit );
    $classForm_esc = esc_attr( $classForm );
    $classInputForm_esc = esc_attr( $classInputForm );
    $classButtonForm_esc = esc_attr( $classButtonForm );
    
    $dataAjax = array();
    $ajaxPositionResult = 'bottom';
    
    // Build the AJAX parameters
    if ( isset( $searchParameters['ajax'] ) ) {
        
        if ( ! empty( $searchParameters['ajax']['productResult'] ) ) {
            $dataAjax['productResult'] = $searchParameters['ajax']['productResult'];
        }
        
        if ( ! empty( $searchParameters['ajax']['catalogResult'] ) ) {
            $dataAjax['catalogResult'] = $searchParameters['ajax']['catalogResult'];
        }
        
        if ( ! empty( $searchParameters['ajax']['postResult'] ) ) {
            $dataAjax['postResult'] = $searchParameters['ajax']['postResult'];
        }
        
        // Replace quotes for correct HTML output
        $dataAjax = str_replace( "'", '"', json_encode( $dataAjax ) );
        
        if ( ! empty( $searchParameters['ajax']['positionResult'] ) ) {
            $ajaxPositionResult = $searchParameters['ajax']['positionResult'];
        }
    }
    
    // Output the results block (top)
    if ( isset( $searchParameters['ajax'] ) && $ajaxPositionResult === 'top' ) {
        
        $classBlockResult = isset( $searchParameters['ajax']['classBlockResult'] ) ? $searchParameters['ajax']['classBlockResult'] : '';
        as_ajaxBlock( $classBlockResult );
    }
    
    // data-blockresult attribute
    $linkResultBlock = '';
    
    if ( isset( $searchParameters['ajax']['classBlockResult'] ) && $searchParameters['ajax']['classBlockResult'] ) {
        $linkResultBlock = "data-blockresult='" . esc_attr( $searchParameters['ajax']['classBlockResult'] ) . "'";
    }
    
    ?>
    
    <form role="search" method="get" class="<?php echo $classForm_esc; ?>" action="<?php echo esc_url( home_url( '/' ) ); ?>">
        <div>
            <input type="text" <?php echo $linkResultBlock; ?> <?php echo ( $dataAjax ) ? "data-ajax='" . esc_attr( $dataAjax ) . "'" : ""; ?> class="<?php echo $classInputForm_esc; ?>" placeholder="<?php esc_attr_e( 'Search', 'argon-shop' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" autocomplete="off" />
            
            <?php 
            
            if ( ! empty( $searchParameters['post_type'] ) ) {
                
                // Hidden fields for post types
                if ( is_array( $searchParameters['post_type'] ) ) {
                    
                    foreach ( $searchParameters['post_type'] as $key ) {
                        
                        $postType = 'post_type[]';
                        $key_esc = esc_attr( $key );
                        
                        ?>
                        
                        <input type="hidden" value="<?php echo $key_esc; ?>" name="<?php echo esc_attr( $postType ); ?>" />
                        
                        <?php
                    }
                }
                
                // Hidden fields for taxonomies
                if ( ! empty( $searchParameters['taxonomy'] ) && is_array( $searchParameters['taxonomy'] ) ) {
                    
                    foreach ( $searchParameters['taxonomy'] as $key => $value ) {
                        
                        if ( $value !== 'all' ) {
                            $value = serialize( $value );
                        }
                        
                        $nameGet = 'as_taxonomy[' . $key . ']';
                        
                        ?>
                        
                        <input type="hidden" value="<?php echo esc_attr( $value ); ?>" name="<?php echo esc_attr( $nameGet ); ?>" />
                        
                        <?php
                    }
                }
            }
            
            ?>
            
            <input type="submit" class="<?php echo $classButtonForm_esc; ?>" value="<?php echo $valueSubmit_esc; ?>" />
        </div>
    </form>
    
    <?php
    
    // Output the results block (bottom)
    if ( isset( $searchParameters['ajax'] ) && $ajaxPositionResult === 'bottom' ) {
        
        $classBlockResult = isset( $searchParameters['ajax']['classBlockResult'] ) ? $searchParameters['ajax']['classBlockResult'] : '';
        as_ajaxBlock( $classBlockResult );
    }
}

/**
 * FUNCTION: Output the AJAX results container
 * 
 * @param string $class — additional CSS class
 */
function as_ajaxBlock( $class ) {
    
    $ajaxBlockClass = 'searchAjaxResult';
    
    if ( $class ) {
        $ajaxBlockClass .= ' ' . esc_attr( $class );
    }
    
    $ajaxBlockClass_esc = esc_attr( $ajaxBlockClass );
    
    ?>
    
    <div class="<?php echo $ajaxBlockClass_esc; ?>" style="display:none"></div>
    
    <?php
}