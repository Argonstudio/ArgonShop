<?php
/**
 * Single post template (single)
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * WHERE TO TEST:
 * Single post pages
 * URL: /post-name/
 * 
 * WHAT IT OUTPUTS:
 * 1. Breadcrumbs (via kama_breadcrumbs from the Argon Shop plugin)
 * 2. Post title
 * 3. Post image (if any)
 * 4. Post content
 * 
 * ============================================================
 * INTERACTION WITH THE ARGON SHOP PLUGIN
 * ============================================================
 * 
 * This template uses the following plugin function:
 * 
 * - as_kama_breadcrumbs() → breadcrumbs (includes/interface/breadcrumbs.php)
 * 
 * For a single post the plugin automatically builds the chain:
 * "Home » [Category] » [Post title]"
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header(); ?>

<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content block-contentPage block-entryPage">
            
            <?php 
            // Argon Shop plugin function (includes/interface/breadcrumbs.php)
            if ( function_exists( 'as_kama_breadcrumbs' ) ) {
                AS_Kama_Breadcrumbs( ' » ' );
            }
            ?>
            
            <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            
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
                
            </div>
            
            
            <?php endwhile; endif; ?>
        </main>

    </div>
</section>


<?php get_footer(); ?>