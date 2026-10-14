<?php
/**
 * Single post template of the "Promotions and discounts" category (Moscow)
 * 
 * Template for displaying a single promotion from the category with the slug "aktsii-moskva"
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * WHERE TO TEST:
 * A single promotion page
 * 
 * WHAT IT OUTPUTS:
 * 1. Breadcrumbs (manual markup with a link to the category)
 * 2. Promotion title
 * 3. Post image
 * 4. Promotion content
 * 5. The "Promotion archived" badge (if promo_active = 0)
 * 
 * ============================================================
 * INTERACTION WITH THE ARGON SHOP PLUGIN
 * ============================================================
 * 
 * This template does not call plugin functions directly.
 * 
 * The breadcrumbs are output with manual markup (not via kama_breadcrumbs),
 * because the second link must lead to the post's category — the plugin
 * does not build such a chain automatically for single posts.
 * 
 * EXTERNAL DEPENDENCIES:
 * - The promo_active meta field — defines whether the promotion is active (1) or archived (0)
 * - Promotions category — ID 36 (Promotions and discounts)
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header(); ?>

<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content block-contentPage block-entryPage" id="block-contentPromoPage">
            
            
            <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            
            <?php 
            
            // ============================================
            // POST CATEGORY AND ITS LINK
            // ============================================
            // 
            // Used to build the second link in the breadcrumbs.
            
            $categoryPost  = get_the_category();
            $categoryLink  = '';
            $category_name = '';
            
            if ( ! empty( $categoryPost ) && isset( $categoryPost[0] ) ) {
                
                $categoryLink = get_category_link( $categoryPost[0]->term_id );
                $category_name = isset( $categoryPost[0]->name ) ? $categoryPost[0]->name : '';
                
                if ( is_wp_error( $categoryLink ) ) {
                    $categoryLink = '#';
                }
            }
            
            ?>
            
            <!-- Breadcrumbs (manual markup with a link to the category) -->
            <div class="kama_breadcrumbs" itemscope="" itemtype="http://schema.org/BreadcrumbList">
                
                <span itemprop="itemListElement" itemscope="" itemtype="http://schema.org/ListItem">
                    <a href="<?php echo esc_url( get_home_url() ); ?>" itemprop="item"><span itemprop="name"><?php esc_html_e( 'Home', 'argon-shop-theme' ); ?></span></a>
                </span>
                
                <span class="kb_sep"> » </span>
                
                <span itemprop="itemListElement" itemscope="" itemtype="http://schema.org/ListItem">
                    <a href="<?php echo esc_url( $categoryLink ); ?>" itemprop="item"><span itemprop="name"><?php echo esc_html( $category_name ); ?></span></a>
                </span>
                
                <span class="kb_sep"> » </span>
                
                <span class="kb_title"><?php the_title(); ?></span>
                
            </div>
            
            <h1 class="title"><?php the_title(); ?></h1>
            
            <div class="block-entryContent">
                
                <?php if ( has_post_thumbnail() ) { ?>
                    <div class="block-entryImage">
                        <?php the_post_thumbnail( '', array( 'class' => '' ) ); ?>
                    </div>
                <?php } ?>
                
                <div class="block-entryText">
                    <?php the_content(); ?>
                </div>
                
                <?php
                
                // ============================================
                // "PROMOTION ARCHIVED" BADGE
                // ============================================
                // 
                // If the promo_active meta field is empty or 0, the promotion is archived.
                
                $promoMeta = get_post_meta( get_the_ID(), 'promo_active', true );
                
                if ( empty( $promoMeta ) ) { ?>
                    
                    <div class="block-promoArchive"><?php esc_html_e( 'Promotion archived', 'argon-shop-theme' ); ?></div>
                    
                <?php } ?>
                
            </div>
            <?php endwhile; endif; ?>
        </main>

    </div>
</section>


<?php get_footer(); ?>