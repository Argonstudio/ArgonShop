<?php
/**
 * Static page template (page)
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * WHERE TO TEST:
 * Any static WordPress page
 * URL: /page-name/
 * 
 * WHAT IT OUTPUTS:
 * 1. Breadcrumbs (via kama_breadcrumbs from the Argon Shop plugin)
 * 2. Page title
 * 3. Page image (if any)
 * 4. Page content
 * 
 * ============================================================
 * INTERACTION WITH THE ARGON SHOP PLUGIN
 * ============================================================
 * 
 * This template uses the following plugin function:
 * 
 * - as_kama_breadcrumbs( ' » ' )
 *   File: includes/interface/breadcrumbs.php
 *   Outputs a chain like: "Home » Page title"
 *   For nested pages: "Home » Parent » Child"
 * 
 * ============================================================
 * TEMPLATES THAT OVERRIDE THIS FILE
 * ============================================================
 * 
 * Some pages have their own templates and do not use page.php:
 * 
 * - page-history.php (or Template Name: View history) → history.php
 * - page-contacts.php (Template Name: Contacts) → Contacts
 * - page-about.php (Template Name: About company) → aboutCompany.php
 * - page-shoppingCart.php (Template Name: Cart page) → shoppingCartPage.php
 * - Moscow promotions archive → arhiv-aktsij-moskva.php
 * 
 * This file is the fallback for all other static pages.
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
                AS_Kama_Breadcrumbs( ' » ' );
            }
            ?>

            <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            
            <div id="single_page" class="grey_block">
                <h1 class="title"><?php the_title(); ?></h1>
                <article class="top_line">
                    <?php if ( has_post_thumbnail() ) { ?>
                        <div class="image">
                            <?php the_post_thumbnail( '', array( 'class' => '' ) ); ?>
                        </div>
                    <?php } ?>
                    <div class="text">
                        <?php the_content(); ?>
                    </div>
                </article>

            </div>
            
            <?php endwhile; endif; ?>
        </main>

    </div>
</section>


<?php get_footer(); ?>