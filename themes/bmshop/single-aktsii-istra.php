<?php 
get_header(); ?>
<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content">
            <?php /*bito_breadcrumbs();*/ ?>
            <?php if( function_exists('kama_breadcrumbs') ) kama_breadcrumbs('<b> / </b>'); ?>
            
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
                    <div class="date"><?php echo get_the_date('d.m.Y'); ?></div>
                    <div class="text">
                        <?php the_content(); ?>
                    </div>
                </article>

                <div id="add_news">
                    <h2 class="title">другие новости</h2>
                    <div class="list top_line">
                        <div class="row">
                        <?php
                            $now_id = get_the_ID();
                            $arg = array(
                                'post_status' => 'publish',
                                'post_type' => 'post',
                                'post__not_in' => array($now_id),
                                'posts_per_page' => 2
                            );
                            $sidebar_posts = new WP_Query( $arg );
                            if ( $sidebar_posts  -> have_posts() ) :
                                while ( $sidebar_posts  -> have_posts() ) : $sidebar_posts  -> the_post();
                        ?>
                            <div class="column small-12 medium-6 large-6">
                                <div class="list_row">
                                    <div class="date"><?php echo get_the_date('d.m.Y'); ?></div>
                                    <a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute();?>" class="title_link"><?php the_title(); ?></a>
                                    <p class="descr"><?php echo wp_trim_words(get_the_content(), '25'); ?></p>
                                </div>
                            </div>
                        <?php endwhile; wp_reset_postdata(); endif;  ?>
                        </div>
                
                    </div>
                </div>
            </div>
            <?php endwhile; endif; ?>
        </main>

    </div>
</section>


<?php get_footer(); ?>