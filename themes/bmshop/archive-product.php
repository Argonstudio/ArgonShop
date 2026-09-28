<?php get_header(); ?>


<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content">
            <?php //bito_breadcrumbs(); ?>

            <div id="list_page" class="grey_block">
                <h1 class="title"><?php single_cat_title(); ?></h1>
                <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
                <div class="list top_line">
                    <div class="list_row row">
                        <div class="column medium-4 large-4 text-right image"><img src="img/demo/warm_montage1.jpg" alt=""></div>
                        <div class="column medium-8 large-8 info">
                            <div class="name"><?php //the_title();?></div>
                            <div class="descr"><?php echo wp_trim_words(get_the_content(), '15'); ?></div>
                            <div class="buttons">
                                <a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute();?>">Подробнее</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
                <!--
                <div class="paginations">
                    <a href="#" class="prev"><<</a>
                    <a href="#" class="">2</a>
                    <a href="#" class="active">3</a>
                    <a href="#" class="">4</a>
                    <a href="#" class="">5</a>
                    <a href="#" class="">6</a>
                    <a href="#" class="">7</a>
                    <a href="#" class="next">></a>
                </div>-->
            </div>

        </main>

    </div>
</section>

<?php get_footer(); ?>