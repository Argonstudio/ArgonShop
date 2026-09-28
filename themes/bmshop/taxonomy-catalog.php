<?php 

$queried_object = get_queried_object();
$current_id = $queried_object->term_id;
$parent_id = $queried_object->parent;

get_header(); ?>
<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content">
            <?php if( function_exists('kama_breadcrumbs') ) kama_breadcrumbs('<span class="b"> » </span>'); ?> 

            <div id="production_list_page" class="white_block">

					<h1 class="title"> 
						<?php if(get_field('second_title','catalog_'.$current_id)){ ?>
							<?php the_field('second_title','catalog_'.$current_id); ?>
						<?php } else { ?>
							<?php single_term_title(); ?>
						<?php } ?>
					</h1>

					<?php
					    
					$termDescription = term_description();

                    if ( $termDescription && empty($_GET)  ) { ?>
                        
                        <div class="catalogPage_openMobile mobileOpen-topDescr">Описание категории</div>
                            
                        <div class="catalogPage_mobileExtensible topDescr"> <?=$termDescription ?> </div>
                     
                    <?php        
                    } 

                    ?>
					
					<?php 
						$args = array(
						'hide_empty'    => false, 
						'parent'        => $current_id,
					);
						
					$terms = get_terms( 'catalog', $args );
					$termsRequest = Array();
						
					if ( !empty( $terms ) && !is_wp_error( $terms ) ) {
						
						$count = count($terms); $i=0;  ?>
							
						<div class="catalogPage_openMobile mobileOpen-cardsCategory">Дочерние категории</div>
							
						<div class="catalogPage_mobileExtensible block-cards-category">
							    
    						<?php foreach ($terms as $term) { $i++; ?>
    							
    							<?php 
    								
    							$image = get_field('image_catalog',$term);
    							$type = get_field('catalog_type',$term);
    								
    							if($type === 'base'){
    								
    							?>
    							
    							<span class="block-card-category">
    							    
    							    <a href="<?php echo get_term_link( $term );?>" class="card-category<?=(!$image)?' card-category-notImg':''?>" style="background-image:url(<?=$image[url] ?>)">
    								    
        								<span><?php echo $term->name;?></span>
        									
        							</a>
    							    
    							</span>
    							
    								
    							<?php 
    								    
    							}else{
    								
    								array_push($termsRequest, $term);
    								
    							}
    							
    							?>
    								
    						<?php } ?>
    							
						 </div><!-- .block-cards-category -->
						
						<?php } ?>
						
						
						<?php if(!empty($termsRequest)){ ?>
						
						<div class="block-request">
						        
            				<div class="catalogPage_openMobile titleCardsRequest">Популярные запросы</div>
            				
            				<div class="catalogPage_mobileExtensible">
            							
                        		<div class="block-cards-request">
                        						    
                        		<?php foreach($termsRequest as $term){ ?>
                        						        
                        			<a href="<?php echo get_term_link( $term );?>" class="card-request">
                            								    
                            			<span><?php echo $term->name;?></span>
                            									
                            		</a>
                        						      
                        		<?php  } ?>
                        						    
                        		</div><!-- .block-cards-request -->
                		
                		        <div class="view-allCards-request">смотреть все</div>
                		        
                		    </div>
                		</div><!-- .block-request -->				    
                		
						    
						<?php } ?>
						
						
						<?php if(get_field('second_desc','catalog_'.$current_id)){;?>
							<div id="topTwoDescr"><?php the_field('second_desc','catalog_'.$current_id); ?></div>
						<?php } ?>
					
					
					<?php 
						if(function_exists('wp_product_filter')){ ?>
							<div id="productFilter">
								<?php wp_product_filter(); ?>
							</div>
					<?php } ?>
					
					
					
					<?php //Выводим посты
					
					//$catalogPosts = as_get_products(true);
					
					if( have_posts() ){ 
					    
					    $pageNum = (get_query_var('paged')) ? get_query_var('paged') : 1;
					    $catID = get_queried_object_id(); //get_queried_object()->term_id
					    $typeSort = getCookie('AS_CatalogSorting');
					    
					    $settingSort = Array(
					        
					        "baseSort"   => Array(
					            
					            "typeSort"    => "date",
					            "howSort"     => "DESC"
					            
					            ),
					            
					         "typesSort" => Array(
					             
					             "price"  => Array("name"=>"Цене"),
					             "name"   => Array("name"=>"Названию"),
					             "date"   => Array("name"=>"Дате добавления")    
					             
					             )         
					        
					        );
					    
					    ?>
					    
					    <div class="block-catalogSort">
					        
					        <div class="catalogPage_openMobile titleCatalogSort">Сортировать по:</div>
					        
					        <span class="catalogPage_mobileExtensible">
					        
					            <?php as_sort($settingSort, $catID, $pageNum); ?>
					        
					        </span>
					        
					        <div class="as-error-sort"></div>
					        
					    </div>
					    
					    <div class="catalogProductList"> 
					    
					        <?php 
					        
					        if($typeSort){
					            
					            $productsList = get_sort_products($typeSort['typeSort'], $typeSort['howSort'], $catID, $pageNum);
					            
					        }else{
					            
					            $productsList = get_sort_products('date', 'DESC', $catID, $pageNum);
					            
					        }
					        
					        view_products_list($productsList) ?> 
    					
    					 </div> 
    					
					<?php }else{
					    
					    echo "<p>Категория пуста.</p>";
					    
					} ?>
					    
					    <div class="descr">
							<?php echo wpautop (get_field ('bottom_desc','catalog_'.$current_id)); ?>
						</div><!-- .descr -->
					
					     
			<?php kama_pagenavi(); ?>	
			</div><!-- #production_list_page -->

        </main>

    </div><!-- .row -->
</section><!-- #out -->


<?php get_footer(); ?>
