<?php 
get_header(); ?>
<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content block-contentPage block-entryPage" id="block-contentPromoPage">
            
            
            <?php 
				/*
				$arg = array(
            		'post_type' => "post",
            		'posts_per_page' => -1,
            		
            		'meta_query'	=> array(
                		array(
                			'key'	 	=> "promo_active",
                			'value'	  	=> '',
                			'compare' 	=> '!=',
                			),
            		),
            		
            	    $tax_query = array(
            			array(
            				'taxonomy' => "category",
            				'field'    => 'id',
            				'terms'    => 36
            			)
            		)
            	);*/
            	
            	//$promoList = new WP_Query( $arg );
					
            ?>
            
            <?php //if (have_posts()): while (have_posts()): the_post(); ?>
            <?php //if (have_posts()): while ( $promoList  -> have_posts() ) : $promoList  -> the_post(); ?>
            
            
            <?php if (have_posts()): while (have_posts()): the_post(); ?>
            
            <?php 
            
                $categoryPost = get_the_category(); 
                $categoryLink = get_category_link($categoryPost[0]->term_id); 
            
            ?>
            
            <div class="kama_breadcrumbs" itemscope="" itemtype="http://schema.org/BreadcrumbList">
                
                <span itemprop="itemListElement" itemscope="" itemtype="http://schema.org/ListItem">
                    <a href="<?=get_home_url() ?>" itemprop="item"><span itemprop="name">Главная</span></a>
                </span>
                
                <span class="kb_sep"> » </span>
                
                <span itemprop="itemListElement" itemscope="" itemtype="http://schema.org/ListItem">
                    <a href="<?=$categoryLink ?>" itemprop="item"><span itemprop="name">Акции и скидки</span></a>
                </span>
                
                <span class="kb_sep"> » </span>
                
                <span class="kb_title"><?php the_title(); ?></span>
                
            </div>
            
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
                
                <?php
                
                $promoMeta = get_post_meta(get_the_id(), 'promo_active');
                
                if(!$promoMeta[0]){ ?>
                    
                    <div class="block-promoArchive">Акция в архиве</div>
                    
                <?php }
                
                
                ?>
                
                <!--
                <div id="add_news">
                    <h2 class="title">другие новости</h2>
                    <div class="list top_line">
                        <div class="row">
                        <?php
                            /*$now_id = get_the_ID();
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
                        <?php endwhile; wp_reset_postdata(); endif;  */?>
                        </div>
                
                    </div>
                </div>-->
                
            </div>
            <?php endwhile; endif; ?>
        </main>

    </div>
</section>


<?php get_footer(); ?>