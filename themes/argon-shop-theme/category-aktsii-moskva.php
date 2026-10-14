<?php
/**
 * "Promotions and discounts" category template (Moscow)
 * 
 * Template for the category with the slug "aktsii-moskva"
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * WHERE TO TEST:
 * The "Promotions and discounts" category page
 * URL: /category/aktsii-moskva/
 * 
 * WHAT IT OUTPUTS:
 * 1. Breadcrumbs (via kama_breadcrumbs from the Argon Shop plugin)
 * 2. Category title
 * 3. List of active promotions (post_type = post, promo_active = 1, category = 36)
 * 4. Link to the promotions archive (if the archive has posts)
 * 
 * ============================================================
 * INTERACTION WITH THE ARGON SHOP PLUGIN
 * ============================================================
 * 
 * This template uses the following plugin functions:
 * 
 * - as_kama_breadcrumbs() → breadcrumbs (includes/interface/breadcrumbs.php)
 * - as_kama_pagenavi()    → pagination (includes/interface/pagenavi.php)
 *  
 * EXTERNAL DEPENDENCIES:
 * - Category ID 36 = "Promotions and discounts"
 * - Meta field promo_active = 1 (the promotion is active), 0 (archived)
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
        
        <main class="block-content block-contentPage block-contentRubric" id="block-contentPromo">
            
            <?php 
            // ============================================
            // BREADCRUMBS
            // ============================================
            // 
            // Argon Shop plugin function (includes/interface/breadcrumbs.php)
            // 
            // Outputs: Home » Promotions and discounts
            // with schema.org microdata (BreadcrumbList)
            // 
            // The default separator ' » ' is wrapped
            // in <span class="kb_sep">...</span> by the function itself.
            
            if ( function_exists( 'as_kama_breadcrumbs' ) ) {
                AS_Kama_Breadcrumbs( ' » ' );
            }
            ?>
            
            <h1 class="title"><?php esc_html_e( 'Promotions and discounts', 'argon-shop-theme' ); ?></h1>
            
            <div class="block-rubricEntrys">
            
            <?php 
            
            // ============================================
            // ACTIVE PROMOTIONS
            // ============================================
            
            $arg = array(
                'post_type'      => 'post',
                'posts_per_page' => -1,
                
                'meta_query' => array(
                    array(
                        'key'     => 'promo_active',
                        'value'   => 1,
                        'compare' => '==',
                    ),
                ),
                
                'tax_query' => array(
                    array(
                        'taxonomy' => 'category',
                        'field'    => 'id',
                        'terms'    => 36,
                    ),
                ),
            );
            
            $promoList = new WP_Query( $arg );
            
            if ( $promoList instanceof WP_Query && $promoList->have_posts() ) {
                
                while ( $promoList->have_posts() ) : $promoList->the_post();
                    
                    $imgEntry = get_the_post_thumbnail_url( get_the_ID(), 'medium' );
                    
                    ?>
                    
                    <div class="block-entry">
                        
                        <div class="block-entryImage">
                            
                            <?php if ( $imgEntry ) : ?>
                                
                                <a href="<?php the_permalink(); ?>">
                                    
                                    <img src="<?php echo esc_url( $imgEntry ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
                                    
                                </a>
                                
                            <?php endif; ?>
                            
                        </div>
                        
                        <div class="block-entryPreview">
                            
                            <div class="entryName">
                                <a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
                            </div>
                            
                            <div class="entryDescr">
                                <?php the_excerpt(); ?>
                            </div>
                            
                            <div class="entryButtons">
                                <a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php esc_html_e( 'Learn more', 'argon-shop-theme' ); ?></a>
                            </div>
                            
                        </div>
                        
                    </div>
                    
                    <?php 
                    
                endwhile;
                
                // PHP 8.5: reset the global $post after the loop
                wp_reset_postdata();
                
            } else {
                
                echo __( 'No current promotions', 'argon-shop-theme' );
            }
            ?>
            
            <?php 
            // Argon Shop plugin function (includes/interface/pagenavi.php)
            // 
            // NOTE: posts_per_page => -1 means a single page.
            // Pagination makes no sense — as_kama_pagenavi() returns false.
            // Kept for compatibility in case posts_per_page changes in the future.
            if ( function_exists( 'as_kama_pagenavi' ) ) {
                as_kama_pagenavi();
            }
            ?>
            
            </div>
            
            
            <?php 
            
            // ============================================
            // PROMOTIONS ARCHIVE (check whether there are posts)
            // ============================================
            
            $argArchive = array(
                'post_type'      => 'post',
                'posts_per_page' => -1,
                
                'meta_query' => array(
                    array(
                        'key'   => 'promo_active',
                        'value' => 0,
                    ),
                ),
                
                'tax_query' => array(
                    array(
                        'taxonomy' => 'category',
                        'field'    => 'id',
                        'terms'    => 36,
                    ),
                ),
            );
            
            $promoListArchive = new WP_Query( $argArchive );
            
            if ( $promoListArchive instanceof WP_Query && $promoListArchive->have_posts() ) {
                ?>
                
                <div class="block-archive">
                    
                    <a href="/arhiv-aktsij-moskva/"><?php esc_html_e( 'Promotions archive', 'argon-shop-theme' ); ?></a>
                    
                </div>
                
                <?php 
            }
            
            wp_reset_postdata();
            
            ?>

        </main>

    </div>
</section>

<?php get_footer(); ?>