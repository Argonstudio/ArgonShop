<?php
/**
 * Template Name: Contacts
 * 
 * "Contacts" page template
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 *  
 * WHAT IT OUTPUTS:
 * 1. Breadcrumbs
 * 2. Page title and content
 * 3. Address map (template part 'addressMap')
 * 4. Feedback form (Contact Form 7)
 * 
 * ============================================================
 * INTERACTION WITH THE ARGON SHOP PLUGIN
 * ============================================================
 * 
 * This template uses the following plugin functions:
 * 
 * - as_kama_breadcrumbs() → breadcrumbs (includes/interface/breadcrumbs.php)
 *  
 * EXTERNAL DEPENDENCIES:
 * - Contact Form 7 — for the feedback form (the [contact-form-7] shortcode)
 * - Template part 'addressMap' — map template (addressMap.php in the theme root)
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

            <div class="page pageContacts">

                <h1 class="title titleHistory"><?php esc_html_e( 'Contacts', 'argon-shop-theme' ); ?></h1>
                
                <div class="descr"><?php the_content(); ?></div><!-- .descr -->
                
                <div class="pageMap">
                    
                    <?php 
                    // Template part 'addressMap' — map template
                    // The file must be: /themes/argon-shop-theme/addressMap.php
                    get_template_part( 'addressMap' ); 
                    ?>
                    
                </div>
                
                <div class="pageContactForm">
                    
                    <div class="h2 pageContactForm-title"><?php esc_html_e( 'Feedback', 'argon-shop-theme' ); ?></div>
                    
                    <?php 
                    // Feedback form via Contact Form 7
                    echo do_shortcode( '[contact-form-7 id="7" title="Feedback"]' );
                    ?>
                    
                </div>
                
                
            </div><!-- #production_list_page -->
                
            <?php endwhile; ?>

        </main>

    </div><!-- .row -->
</section><!-- #out -->


<?php get_footer(); ?>