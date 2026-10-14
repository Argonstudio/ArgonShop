<?php 
/**
 * Search results page template
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * WHERE TO TEST:
 * The search results page after submitting the form
 * URL: /?s=query&post_type[]=product&as_taxonomy[catalog]=all
 * 
 * WHAT IT OUTPUTS:
 * 1. Breadcrumbs (from the Argon Shop plugin)
 * 2. The search form (to search again)
 * 3. Search results as product cards
 * 4. Pagination
 * 
 * ============================================================
 * INTERACTION WITH THE ARGON SHOP PLUGIN
 * ============================================================
 * 
 * This template uses the following plugin functions:
 * 
 * 1. as_kama_breadcrumbs()
 *    File: includes/interface/breadcrumbs.php
 *    Outputs the breadcrumbs: "Home » Search results for - [query]"
 *    If the query is empty — "Home » Search"
 * 
 * 2. as_search( $searchParameters )
 *    File: includes/interface/search/searchForm.php
 *    Outputs the search form with its settings. On this page the form
 *    is duplicated so that the user can search again.
 *    
 *    AJAX form handler: action=getAjaxSearchResult
 *    (see includes/interface/search/searchAjax.php)
 * 
 *    IMPORTANT: search works "out of the box" for products and the
 *    catalog taxonomy. Searching posts requires extending
 *    searchAjax.php — the postResult parameter is not fully
 *    handled in this version.
 * 
 * 3. as_getCookie( 'productsShoppingCart' )
 *    File: includes/api/getData/getData.php
 *    Reads the cart cookies — needed to pass them to generate_product_card()
 *    to show the "Add to cart" / "Product added" buttons.
 * 
 * 4. generate_product_card( $post, $cart )
 *    File: includes/api/view/product/productsLists/productsLists.php
 *    Outputs the product HTML card in the search results.
 * 
 * 5. as_kama_pagenavi()
 *    File: includes/interface/pagenavi.php
 *    Outputs the pagination.
 * 
 * SEARCH FILTERS:
 * 
 * The tax_query is applied to the search results in:
 * - includes/interface/search/searchGetFilters.php
 * 
 * It reads $_GET['as_taxonomy'] and $_GET['post_type'] and adds
 * a filter to the main WordPress query.
 * 
 * ============================================================
 * PHP 8.5:
 * - ABSPATH protection
 * - protection against null when calling plugin functions
 * - function_exists() checks for all plugin functions
 * - WP_Query check via have_posts()
 * - wp_reset_postdata() after the loop
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content">
       
       <?php 
       // Argon Shop plugin function (includes/interface/breadcrumbs.php)
       if ( function_exists( 'as_kama_breadcrumbs' ) ) {
           AS_Kama_Breadcrumbs( '<span class="b"> » </span>' );
       }
       
       echo "<div class='block-pageSearch'>";
       
       $searchParameters = array(
            
            /**
            * ============================================
            * PLUGIN FUNCTION CALL: as_search()
            * ============================================
            * 
            * File: includes/interface/search/searchForm.php
            * 
            * FUNCTION: Outputs the search form
            * 
            * SUPPORTS:
            * 1. Regular search (GET request to /?s=query)
            * 2. AJAX search (results without a page reload)
            * 3. Search across several post types (products, articles, pages)
            * 4. Filtering by taxonomies (catalog, categories)
            * 5. Several result blocks (promotions, news, blog)
            * 
            * IMPORTANT: Searching posts and post categories works "out of the box",
            *        without extra changes to the plugin. If the system has no
            *        products or catalog taxonomy, the corresponding blocks
            *        simply do not appear in the results (no errors).
            * 
            * AJAX HANDLER:
            * The form sends the request to action=getAjaxSearchResult
            * (see includes/interface/search/searchAjax.php)
            * 
            * PARAMETER STRUCTURE:
            * 
            * @param array $searchParameters {
            *     Array of search form parameters.
            * 
            *     @type array  $post_type      Array of post types to search.
            *                                  For products: array('product').
            * 
            * 
            *     @type array  $taxonomy       Array of taxonomies for filtering.
            *                                  For the catalog: array('catalog' => 'all').
            * 
            *                                  The "catalog" taxonomy is hardcoded in the AJAX handler (searchAjax.php). Made changeable for the future.
            *                                  You can specify only certain catalog categories (by ID) instead of "all".
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
            *                                              If the system has no products, the block does not appear.
            *                                              Default: not passed.
            * 
            *         @type bool         $catalogResult    Whether to search catalog categories.
            *                                              If there is no catalog taxonomy, the block does not appear.
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
            */
            
            // Search products only
            'post_type'   => array( 'product' ),
            
            // Filtering by the catalog taxonomy
            'taxonomy'    => array(
                'catalog' => 'all',
            ),
            
            'classForm'   => 'pageSearchForm',
            'classInput'  => 'pageSearchInput',
            'classSubmit' => 'pageSearchSubmit',
            'valueSubmit' => '',
            
            // AJAX search: results without a page reload
            'ajax' => array(
                'classBlockResult' => 'pageSearchBlockResult',
                'positionResult'   => 'bottom',
                'productResult'    => true,
                'catalogResult'    => true,
                'postResult'       => array(
                    array(
                        'name'     => __( 'Promotions and articles', 'argon-shop-theme' ),
                        'class'    => 'resultEntry',
                        'category' => array( 36, 37 ),
                    ),
                ),
            ),
        );
        
        // Argon Shop plugin function call
        if ( function_exists( 'as_search' ) ) {
            as_search( $searchParameters );
        }
       
       echo "</div>";
       
       // Check whether a search query was entered
       $search_query = get_search_query();
       
       if ( $search_query ) {
           
           // Argon Shop plugin function (includes/api/getData/getData.php)
           $productsShoppingCart = function_exists( 'as_getCookie' ) ? as_getCookie( 'productsShoppingCart' ) : false;
           
       ?>
       
           <div class="block-pageSearchResult catalogProductList">
               
                <?php 
                if ( have_posts() ) : 
                    
                    while ( have_posts() ) : the_post(); 
                    
                        global $post;
                        
                        // Skip if the post is invalid
                        if ( ! $post instanceof WP_Post ) {
                            continue;
                        }
                        
                        // Argon Shop plugin function
                        // (includes/api/view/product/productsLists/productsLists.php)
                        if ( function_exists( 'generate_product_card' ) ) {
                            generate_product_card( $post, $productsShoppingCart );
                        }
                    
                    endwhile; 
                    
                    wp_reset_postdata();
                    
                endif; 
                ?>
                
                <?php 
                // Argon Shop plugin function (includes/interface/pagenavi.php)
                if ( function_exists( 'as_kama_pagenavi' ) ) {
                    as_kama_pagenavi();
                }
                ?>	
            </div>
       
       <?php
       
       } else {
           echo __( 'Enter a search query', 'argon-shop-theme' );
       }
       
       ?>
        </main>
        
    </div>
</section>

<?php get_footer(); ?>