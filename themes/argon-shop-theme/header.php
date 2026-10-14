<?php
/**
 * Site header
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * ============================================================
 * INTERACTION WITH THE ARGON SHOP PLUGIN
 * ============================================================
 *  
 * Plugin functions used in this file:
 * 
 * 1. as_infocart()
 *    ─────────────────────────────────────────────
 *    SOURCE: includes/interface/shoppingCart/infoCart.php
 *    
 *    WHAT IT DOES:
 *    - Reads the cart cookies via as_getCookie("productsShoppingCart")
 *    - Counts the number and total of products via as_getDataCart()
 *    - Outputs the .viewBlock-shoppingCart block with the count and price
 *    - Is not shown on the cart page (as_get_cartPageID() check)
 *    
 *    USED IN: the header, to show the cart icon with the total
 * 
 * 2. as_search( $searchParameters )
 *    ─────────────────────────────────────────────
 *    SOURCE: includes/interface/search/searchForm.php
 *    
 *    WHAT IT DOES:
 *    - Outputs the search form with its settings
 *    - Wires the getAjaxSearchResult AJAX handler
 *      (from searchAjax.php)
 *    - Supports searching products, categories and posts
 *    
 *    USED IN: the header, for product search

 * ============================================================
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>

<!DOCTYPE html>
<html>
<head lang="ru" class="no-js" dir="ltr">
    
    <meta charset="UTF-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
	
</head>
<body <?php body_class(); ?>>

<header id="header">
    
    <?php
    
    // Check whether the current page is the search results page
    $pageSearch = false;
    
    if ( isset( $_GET['s'] ) && is_string( $_GET['s'] ) ) {
        $pageSearch = true;
    }
    
    ?>
    
    <div class="top-block compensate-for-scrollbar"> 
        <div class="top-content">
            
            <input type="button" class="openMobileMenu">
            
            <a href="/adres-na-karte-moskva/"
               data-modal-ajax="/adres-na-karte-moskva/">
                <input type="button" class="openMobileMap">
            </a>
            
            <nav class="block-menu" id="block-menu">   
                <?php 
                wp_nav_menu( array(
                    'theme_location' => 'top_menu',
                    'menu_id'        => 'top-menu',
                ) );
                ?>
            </nav> 
        
            <div class="block-rightElementsTopContent" style="<?php echo ( ! $pageSearch ) ? 'margin-right:51px' : ''; ?>">
        
                
				<a href="<?php echo esc_url( as_get_cabinetPageURL() ); ?>">
					<input type="button" class="myCabinetButton" value="<?php esc_attr_e( 'My account', 'argon-shop-theme' ); ?>">
				</a>
                
                <?php 
                // ============================================
                // PLUGIN FUNCTION CALL: as_infocart()
                // ============================================
                // 
                // File: includes/interface/shoppingCart/infoCart.php
                // 
                // The function outputs a block with the number and total of products in the cart.
                // Internally it uses:
                //   - as_getCookie("productsShoppingCart") — from getData.php
                //   - as_getDataCart() — from getData.php
                // 
                // The block is NOT output on the cart page.
                // 
                // ADDED BY THE PLUGIN: in argon-shop.php via require_once
                
                as_infocart(); 
                ?>
                
            </div>
            
            <?php if ( ! $pageSearch ) { ?>
            
                <div class="block-topSearch">
                        
                    <?php 
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
                     * PARAMETER STRUCTURE:                     *
                     * 
                     * @param array $searchParameters {
                     *     Array of search form parameters.
                     * 
                     *     @type array  $post_type      Array of post types to search.
                     *                                  For products: array('product').
                     *                                  So far tested only with array('product'); other post types may be supported.
                     * 
                     * 
                     *     @type array  $taxonomy       Array of taxonomies for filtering.
                     *                                  For the catalog: array('catalog' => 'all').
                     * 
                     *                                  The "catalog" taxonomy is hardcoded in the AJAX handler (searchAjax.php). Everything else is untested and made for the future.
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
                    
                    $searchParameters = array(
                        'post_type'   => array( 'product' ),
                        'taxonomy'    => array(
                            'catalog' => 'all',
                        ),
                        'classForm'   => 'topSearchForm',
                        'classInput'  => 'topSearchInput',
                        'classSubmit' => 'topSearchSubmit',
                        'valueSubmit' => '',
                        
                        'ajax' => array(
                            'classBlockResult' => 'topSearchBlockResult',
                            'positionResult'   => 'bottom',
                            'productResult'    => true,
                            'catalogResult'    => true,
                            'postResult'       => array(
                                array(
                                    'name'     => __( 'Promotions and articles', 'argon-shop-theme' ),
                                    'class'    => 'resultEntry',
                                    'category' => array( 36, 37 ),
                                ),
                                array(
                                    'name'     => __( 'News', 'argon-shop-theme' ),
                                    'class'    => 'resultEntry',
                                    'category' => array( 1 ),
                                ),
                            ),
                        ),
                    );
                    
                    // Argon Shop plugin function call
                    as_search( $searchParameters );
                    ?>
                    
                </div>
                
            <?php } ?>
        
        </div>
    </div>
    
    <div class="header_top">
        <div class="row align-justify">
        
            <div class="icon_call_address menu_mobile menu_mobile_address topmenu_mobile column"></div>
            
            <div class="logo">
                <a href="/"><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/img/logo.webp" alt=""></a>
            </div>
            
            <div class="block-address">
                
                <p class="address"><span class="map_icon"></span><a href="/adres-na-karte-moskva/" class="maps_link" data-modal-ajax="/adres-na-karte-moskva/"><?php esc_html_e( 'City Hall, New York, NY', 'argon-shop-theme' ); ?> </a></p>
                
                <p class="work_time"><?php esc_html_e( 'Mon–Fri: 9:00 to 18:00', 'argon-shop-theme' ); ?></br><?php esc_html_e( 'Sat: 10:00 to 15:00', 'argon-shop-theme' ); ?></p>
                
            </div>
            
            <div class="block-communication">
                <a href="tel:12125550199" class="phone"><span class="phone_icon"></span> +1 (212) 555-0199 </a>
                    
                <a href="#feedback"
                   id="linkFeedback"
                   data-modal-inline="#feedback">
                    <?php esc_html_e( 'FEEDBACK', 'argon-shop-theme' ); ?>
                </a>
                
                <?php 
                
                // ============================================
                // CITY SWITCHER (Moscow / Istra)
                // ============================================
                // 
                // When switching to the Istra site, the following is passed:
                // - data-promocategory — slug of the promotions category (ID 35)
                // - data-promoarchive  — slug of the promotions archive page (ID 642)
                // 
                // This data is used by a JS script to replace the links
                // to the promotions pages when the city is switched.
                // 
                // PHP 8.5: $istra_dataSitySelect is initialized with an empty string,
                // otherwise a "Undefined variable" warning is raised when no condition matches
                
                $istra_dataSitySelect = '';
                
                if ( is_archive() || is_single() || is_page( 639 ) ) {
                    
                    $istra_promoCategory = get_category( 35 );
                    
                    if ( $istra_promoCategory && ! is_wp_error( $istra_promoCategory ) && isset( $istra_promoCategory->slug ) ) {
                        $istra_dataSitySelect = "data-promocategory='/" . esc_attr( $istra_promoCategory->slug ) . "/'";
                    }
                    
                    $istra_promoArchive = get_post( 642 );
                    
                    if ( $istra_promoArchive && isset( $istra_promoArchive->post_name ) ) {
                        $istra_dataSitySelect .= " data-promoarchive='/" . esc_attr( $istra_promoArchive->post_name ) . "/'";
                    }
                }
                
                ?>
                
                <select class="sitySelect">
                    <option selected="" value="/"><?php esc_html_e( 'New York', 'argon-shop-theme' ); ?></option>
                    <!--<option <?//php echo $istra_dataSitySelect; ?> value="http://istra.site.com">Istra</option>-->
                </select>
                
            </div>
            
            <div style="display: none;" id="feedback">
                <?php echo do_shortcode( '[contact-form-7 id="7" title="Feedback"]' ); ?>
            </div>
            
        </div>
    </div>
    
</header>