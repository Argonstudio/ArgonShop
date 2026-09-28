<?php
/**
 * Template Name: История просмотров
 */


get_header(); ?>
<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content">

            <?php if (function_exists('kama_breadcrumbs')) kama_breadcrumbs('<span class="b"> » </span>'); ?>

            <?php while (have_posts()) the_post(); ?>

            <div class="page">

                <h1 class="title titleHistory"><?php the_title(); ?></h1>
                <div class="descr"><?php the_content(); ?></div><!-- .descr -->
                
                <?php $productsShoppingCart = getCookie("productsShoppingCart"); 
			
    			$history_products = get_history_products();
    			
    			if ( $history_products  -> have_posts() ) : ?>
    			
        			 <div class="block-products-list products-list-history">
        				
        				<div class="products-list catalogProductList">
        					
        					<?php 
        					
        					while ( $history_products  -> have_posts() ) : $history_products  -> the_post();
        						
        					    generate_product_card($post, $productsShoppingCart);
        						
        					endwhile; ?>
        					<?php kama_pagenavi(); ?>
        				</div>
        				
                    </div>
                    
    			<?php endif;?>
    			<?php wp_reset_postdata(); ?>
                
                
            </div><!-- #production_list_page -->
                
            

        </main>

    </div><!-- .row -->
</section><!-- #out -->


<?php get_footer(); ?>
