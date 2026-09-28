<?php get_header(); ?>


<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content block-contentPage block-contentRubric" id="block-contentPromo">
            
            <div class="kama_breadcrumbs" itemscope="" itemtype="http://schema.org/BreadcrumbList">
                
                <span itemprop="itemListElement" itemscope="" itemtype="http://schema.org/ListItem">
                    <a href="<?=get_home_url() ?>" itemprop="item"><span itemprop="name">Главная</span></a>
                </span>
                
                <span class="kb_sep"> » </span>
                
                <span class="kb_title">Акции и скидки</span>
                
            </div>
            
            <h1 class="title">Акции и скидки</h1>
            
            <div class="block-rubricEntrys">
            
            <?php 
				
				$arg = array(
            		'post_type' => "post",
            		'posts_per_page' => -1,
            		
            		'meta_query'	=> array(
                		array(
                			'key'	 	=> "promo_active",
                			'value'	  	=> 1,
                			'compare' 	=> '==',
                			),
            		),
            		
            	    'tax_query' => array(
            			array(
            				'taxonomy' => "category",
            				'field'    => 'id',
            				'terms'    => 36
            			)
            		)
            	);
            	
            	$promoList = new WP_Query( $arg );
					
            ?>
            
            <?php if ( $promoList  -> have_posts() ) { 
            
                while ( $promoList  -> have_posts() ) : $promoList  -> the_post(); ?>
                <?php //if ( have_posts() ): while ( $promoList  -> have_posts() ) : $promoList  -> the_post(); ?>
                
                <div class="block-entry">
                        
                    <div class="block-entryImage">
                        
                        <?php $imgEntry = get_the_post_thumbnail_url($post,'medium'); ?>
                        
                        <a href="<?php the_permalink() ?>">
                                        
                            <img src="<?=$imgEntry ?>">
                                        
                        </a>
                        
                    </div>
                    
                    <div class="block-entryPreview">
                        
                        <div class="entryName"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute();?>"><?php the_title();?></a></div>
                        <div class="entryDescr"><?php echo the_excerpt() ?></div>
                        <div class="entryButtons">
                            <a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute();?>">Узнать больше</a>
                        </div>
                        
                    </div>
                    
                </div>
                
                <?php endwhile; 
                
                }else{
                    echo "Актуальных акций нет";
                }
                ?>
                <?php kama_pagenavi(); ?>	
            </div>
            
            
            <?php 
				
				$argArchive = array(
            		'post_type' => "post",
            		'posts_per_page' => -1,
            		
            		'meta_query'	=> array(
                		array(
                			'key'	 	=> "promo_active",
                			'value'	  	=> 0
                			),
            		),
            		
            	    'tax_query' => array(
            			array(
            				'taxonomy' => "category",
            				'field'    => 'id',
            				'terms'    => 36
            			)
            		)
            	);
            	
            	$promoListArchive = new WP_Query( $argArchive );
					
            ?>
            
            <?php if ( $promoListArchive  -> have_posts() ) { ?>
            
            <div class="block-archive">
                
                <a href="/arhiv-aktsij-moskva/">Архив акций</a>
                
            </div>
            
            <?php } ?>

        </main>

    </div>
</section>

<?php get_footer(); ?>