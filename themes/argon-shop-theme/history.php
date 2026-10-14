<?php
/**
 * Template Name: View history
 * 
 * "View history" page template
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 *  * 
 * WHAT IT OUTPUTS:
 * 1. Breadcrumbs
 * 2. Page title and content
 * 3. List of viewed products (as cards)
 * 4. Pagination
 * 
 * ============================================================
 * INTERACTION WITH THE ARGON SHOP PLUGIN
 * ============================================================
 * 
 * This template uses the following plugin functions:
 * 
 * - as_kama_breadcrumbs()       → breadcrumbs (breadcrumbs.php)
 * - as_kama_pagenavi()          → pagination (pagenavi.php)
 * - as_getCookie()              → reading the cart cookie (getData.php)
 * - as_get_history_products()   → view history (history.php)
 * - generate_product_card()  → product card (productsLists.php)
 * 
 * ============================================================
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header(); ?>

<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content">

            <?php 
            // Argon Shop plugin function (includes/interface/breadcrumbs.php)
            if ( function_exists( 'as_kama_breadcrumbs' ) ) {
                AS_Kama_Breadcrumbs( '<span class="b"> » </span>' );
            }
            ?>

            <?php 
            // Standard WordPress loop
            while ( have_posts() ) : the_post(); 
            ?>

            <div class="page">

                <h1 class="title titleHistory"><?php the_title(); ?></h1>
                <div class="descr"><?php the_content(); ?></div><!-- .descr -->
                
                <?php 
                
                // Argon Shop plugin function (includes/api/getData/getData.php)
                // Get the products in the cart (to check whether a product has been added)
                $productsShoppingCart = function_exists( 'as_getCookie' ) 
                    ? as_getCookie( 'productsShoppingCart' ) 
                    : false;
                
                // Argon Shop plugin function (includes/interface/history.php)
                // Get the list of viewed products
                $history_products = function_exists( 'as_get_history_products' ) 
                    ? as_get_history_products() 
                    : false;
                
                if ( $history_products instanceof WP_Query && $history_products->have_posts() ) : 
                ?>
                
                     <div class="block-products-list products-list-history">
                        
                        <div class="products-list catalogProductList">
                            
                            <?php 
                            
                            while ( $history_products->have_posts() ) : $history_products->the_post();
                                
                                global $post;
                                
                                if ( ! $post instanceof WP_Post ) {
                                    continue;
                                }
                                
                                // Argon Shop plugin function
                                // (includes/api/view/product/productsLists/productsLists.php)
                                if ( function_exists( 'generate_product_card' ) ) {
                                    generate_product_card( $post, $productsShoppingCart );
                                }
                                
                            endwhile; 
                            ?>
                            
                            <?php 
                            // Argon Shop plugin function (includes/interface/pagenavi.php)
                            // NOTE: as_kama_pagenavi() uses the global $wp_query,
                            // not $history_products. Pagination works incorrectly
                            // if the history has more products than fit on one page.
                            // For correct behavior $history_products must be passed:
                            // as_kama_pagenavi( '', '', true, array(), $history_products );
                            
                            if ( function_exists( 'as_kama_pagenavi' ) ) {
                                as_kama_pagenavi( '', '', true, array(), $history_products );
                            }
                            ?>
                        </div>
                        
                    </div>
                    
                <?php endif; ?>
                
                <?php 
                // PHP 8.5: resetting the posts is moved outside the condition
                // so that it always runs, even if the query is empty
                wp_reset_postdata(); 
                ?>
                
                
            </div><!-- #production_list_page -->
                
            <?php endwhile; ?>

        </main>

    </div><!-- .row -->
</section><!-- #out -->


<?php get_footer(); ?>