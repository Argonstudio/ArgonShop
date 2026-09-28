<?php 
get_header(); ?>
<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content">
            <?php /*bito_breadcrumbs();*/ ?>
            <?php if( function_exists('kama_breadcrumbs') ) kama_breadcrumbs(' » '); ?>

            <?php if (have_posts()): while (have_posts()): the_post(); ?>
            <div id="single_page" class="grey_block">
                <h1 class="title"><?php the_title(); ?></h1>
                <article class="top_line">
                    <?php 
                    if( has_post_thumbnail() ) { ?>
                        <div class="image">
                            <?php the_post_thumbnail('', array( 'class' => '' )); ?>
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