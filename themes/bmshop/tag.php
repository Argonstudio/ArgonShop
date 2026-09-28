<?php get_header(); 

$queried_object = get_queried_object();
$current_id = $queried_object->term_id;
$parent_id = $queried_object->parent;


?>


<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content block-contentRubric">
            <?php //if( function_exists('kama_breadcrumbs') ) kama_breadcrumbs(' » '); ?>
            
            <div class="kama_breadcrumbs" itemscope="" itemtype="http://schema.org/BreadcrumbList">
                
                <span itemprop="itemListElement" itemscope="" itemtype="http://schema.org/ListItem">
                    <a href="<?=get_home_url() ?>" itemprop="item"><span itemprop="name">Главная</span></a>
                </span>
                
                <span class="kb_sep"> » </span>
                
                <span itemprop="itemListElement" itemscope="" itemtype="http://schema.org/ListItem">
                    <a href="/stati/" itemprop="item"><span itemprop="name">Статьи</span></a>
                </span>
                
                <span class="kb_sep"> » </span>
                
                <span class="kb_title">Записи по метке: <?php single_cat_title(); ?></span>
                
            </div>

            <h1 class="title"><?php single_cat_title(); ?></h1>
            
            
            <?php 
				$args = array(
					'hide_empty'    => false, 
					'parent'        => $current_id,
				);
						
				$terms = get_terms( 'category', $args );
						
					if ( !empty( $terms ) && !is_wp_error( $terms ) ) {
						
						$count = count($terms); $i=0;  ?>
							
						<div class="itemControlPanel" data-type="openingList" name="childRubric">Дочерние категории</div>
							
						<div class="blockItemPage block-cards-childRubric" id="block_childRubric">
							    
    						<?php foreach ($terms as $term) {  ?>
    							
    							<?php 
    							$image = get_field('image_catalog',$term);
    							?>	
    							
    							<span class="block-card-childRubric">
    							    
    							    <a href="<?php echo get_term_link( $term );?>" class="card-childRubric<?=(!$image)?' card-childRubric-notImg':''?>" style="background-image:url(<?=$image[url] ?>)">
    								    
        								<span><?php echo $term->name;?></span>
        									
        							</a>
    							    
    							</span>
    							
    								
    						<?php } ?>
    							
						 </div><!-- .block-cards-childRubric -->
						
						<?php } ?>
						
						<?php 
						
						$queried_object = get_queried_object();
						$current_id = $queried_object->term_id;
						
						if( $current_id === 37 ){
			
                            if ( function_exists('wp_tag_cloud') ){
                                
                                $tag_args = Array(
                                    
                                    'smallest'  => 11,
                                	'largest'   => 18,
                                	'unit'      => 'px',
                                	'number'    => 25,
                                	'format'    => 'flat',
                                	'separator' => "\n",
                                	'orderby'   => 'name',
                                	'order'     => 'ASC',
                                	'exclude'   => null,
                                	'include'   => null,
                                	'link'      => 'view',
                                	'taxonomy'  => 'post_tag',
                                	'echo'      => true,
                                	'topic_count_text_callback' => 'default_topic_count_text',
                                    
                                    );
                                ?>
                                
                                <div class="itemControlPanel itemControlPanel-tags" data-type="openingList" name="entryTags">Облако тегов</div>
                                   
                                <div class="blockItemPage block-mobileEntryTags" id="block_entryTags">
                                    
                                     <?php wp_tag_cloud($tag_args); ?>
                                
                                </div>
                                
                                <?php
                                	
                            }
        		            
        		        }
                        
                        ?>
            
            <div class="block-rubricEntrys">
                
                <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
                
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
                            <a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute();?>">Читать далее</a>
                        </div>
                        
                    </div>
                    
                </div>
                
                <?php endwhile; ?>
                <?php kama_pagenavi(); ?>	
            </div>

        </main>

    </div>
</section>

<?php get_footer(); ?>