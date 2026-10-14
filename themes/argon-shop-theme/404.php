<?php
/**
 * 404 page template (not found)
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * WHERE TO TEST:
 * Any non-existent URL
 * Example: /no-such-page/
 * 
 * WHAT IT OUTPUTS:
 * 1. Breadcrumbs (via kama_breadcrumbs from the Argon Shop plugin)
 * 2. The 404 error message
 * 3. A list of possible causes
 * 4. A link to the home page
 * 
 * ============================================================
 * INTERACTION WITH THE ARGON SHOP PLUGIN
 * ============================================================
 * 
 * This template uses the following plugin function:
 * 
 * - as_kama_breadcrumbs() → breadcrumbs (includes/interface/breadcrumbs.php)
 * 
 * On the 404 page as_kama_breadcrumbs() outputs:
 * "Home » Error 404"
 * (see the '_404' string in the Kama_Breadcrumbs class localization)
 * 
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
            // ============================================
            // BREADCRUMBS
            // ============================================
            // 
            // Argon Shop plugin function (includes/interface/breadcrumbs.php)
            // On the 404 page it outputs: "Home » Error 404"
            
            if ( function_exists( 'as_kama_breadcrumbs' ) ) {
                AS_Kama_Breadcrumbs( '<span class="b"> » </span>' );
            }
            ?>
            
            <div class="page">
                <h1 class="title"><?php esc_html_e( 'This page does not exist!', 'argon-shop-theme' ); ?></h1>
                <article class="top_line">
                    <div class="text">
                        
                        <p><?php esc_html_e( 'Unfortunately, the page you requested does not exist on this site.', 'argon-shop-theme' ); ?></p>
                        
                        <p><?php esc_html_e( 'This may have happened for one of these reasons:', 'argon-shop-theme' ); ?></p>
                        
                        <ul>
                            
                            <li><?php esc_html_e( 'you mistyped the page address (URL)', 'argon-shop-theme' ); ?></li>
                            <li><?php esc_html_e( 'you followed a broken (non-working or incorrect) link', 'argon-shop-theme' ); ?></li>
                            <li><?php esc_html_e( 'the requested page never existed on the site or has been deleted', 'argon-shop-theme' ); ?></li>
                            
                        </ul>
                        
                        
                        <p><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( '« to the home page', 'argon-shop-theme' ); ?></a></p>
                    </div>
                </article>

            </div>
        </main>

    </div><!-- .row -->
</section><!-- #out -->


<?php get_footer(); ?>