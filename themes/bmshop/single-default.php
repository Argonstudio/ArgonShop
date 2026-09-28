<?php 
get_header(); ?>
<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content block-contentPage block-entryPage">
            <?php /*bito_breadcrumbs();*/ ?>
            <?php if( function_exists('kama_breadcrumbs') ) kama_breadcrumbs(' » '); ?>
            
            <?php if (have_posts()): while (have_posts()): the_post(); ?>
            
            <h1 class="title"><?php the_title(); ?></h1>
            
            <div class="block-entryContent">
                
                <?php 
                if( has_post_thumbnail() ) { ?>
                    <div class="block-entryImage">
                        <?php the_post_thumbnail('', array( 'class' => '' )); ?>
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