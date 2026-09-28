<?php
/**
 * Template Name: Контакты
 */


get_header(); ?>
<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content">

            <?php if (function_exists('kama_breadcrumbs')) kama_breadcrumbs('<span class="b"> » </span>'); ?>

            <?php while (have_posts()) the_post(); ?>

            <div class="page pageContacts">

                <h1 class="title titleHistory">Контакты</h1>
                
                <div class="descr"><?php the_content(); ?></div><!-- .descr -->
                
                <div class="pageMap">
                    
                    <?php get_template_part( 'addressMap' ); ?>
                    
                </div>
                
                <div class="pageContactForm">
                    
                    <div class="h2 pageContactForm-title">Обратная связь</div>
                    
                    <?php echo do_shortcode( '[contact-form-7 id="7" title="Обратная связь"]' ); ?>
                    
                </div>
                
                
            </div><!-- #production_list_page -->
                
            

        </main>

    </div><!-- .row -->
</section><!-- #out -->


<?php get_footer(); ?>
