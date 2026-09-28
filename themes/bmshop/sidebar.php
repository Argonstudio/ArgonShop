<aside id="aside" class="block-sidebar">
    
            <div class="openMobileCatalog">Каталог продукции</div>
            
            <?php 
            
            $blockMenuCatalogClass = ( !is_front_page() ) ? "block-menuCatalog" : "block-menuCatalog block-menuCatalogHomepage"; 
            $blockLinkSitebarClass = ( !is_front_page() ) ? "block-linkSidebar" : "block-linkSidebar block-linkSidebarHomepage"; 
            
            ?>
    
            <div class="<?=$blockMenuCatalogClass ?>">
                
                <?php
                
                $menuCatalog = get_catalog_terms();
                
                //print_r($menuCatalog);
                ?>
                
                <ul class="menuCatalog">
                    
                    <?php
                    
                    foreach($menuCatalog as $key => $value){
                        
                        $imageID = get_option("catalog_".$value["data"]["term_id"]."_image_catalog");
                        $imageUrl = wp_get_attachment_image_url($imageID);
                        $imageBase64 = base64_encode( file_get_contents($imageUrl) );
                        
                        //print_r($value);
                        ?>
                        
                        <li style="<?=($imageBase64)?'background-image: url(data:image/png;base64,'.$imageBase64.')':'' ?>;" class="menuCatalogItem<?=($value["children"])?' itemHasChildren':'' ?>">
                            
                            <a href="/catalog/<?=$value["data"]["slug"] ?>">
                                <div class="block-textMenuUrl">
                                     <span><?=$value["data"]["name"] ?> <?//=$value["data"]["firstLevelChild"]." / ".$value["data"]["secondLevelChild"] ?>
                                </div>
                            </a>
                        
                            <?php
                            
                            
                            
                            if( $value["children"] ){
                                
                                $childs = round( ($value["data"]["firstLevelChild"] + $value["data"]["secondLevelChild"])/3 );
                                
                                ?>
                                
                                <div class="submenuCatalog">
                                    
                                 <?php 
                                 
                                    $splitMenuQuery = splitMenu($value["children"], 3, $value["data"]["firstLevelChild"], $value["data"]["secondLevelChild"]);
                                    
                                    $splitMenu = $splitMenuQuery[0];
                                    
                                    foreach($splitMenu as $subMenuID => $subMenuValue){
                                         
                                         if(!empty($subMenuValue)){
                                             view_menu_elements($subMenuValue, "submenuCatalogUl", 3);
                                         }
                                         
                                         
                                     }
                                 
                                 ?>
                                 
                                </div>
                                
                                <?php
                                    
                            }
                            ?>
                        
                        </li>
                        
                        <?php
                        //print_r($value["children"]);
                    }
                    
                    ?>
                    
                </ul>
                
            </div>
            
            <div class="<?=$blockLinkSitebarClass ?>">
                
                <a href="/aktsii-moskva/" class="promoDiscontLink"><span>%</span>Акции и скидки</a>
                <a href="/history/" class="historyLink"><span></span>История просмотров</a>
                
            </div>
			
			
			<?php
			    
			    $viewTag = false;
			    
			    $queried_object = get_queried_object();
			    
		        $current_taxonomy = $queried_object->taxonomy;
		        $current_postType = $queried_object->post_type;
                $current_id = $queried_object->term_id;
			    
		        if( $current_taxonomy === "category" ){
		            
		            //if( $current_id === 1 || $current_id === 37){
		            if( $current_id === 37){
		                
		                $viewTag = true;
		                
		            }else{
		                
		                $ancestors = get_ancestors( $current_id, 'category' );
		                
		                foreach($ancestors as $ancestorID){
		                    
		                    if( $ancestorID === 37){
        		                
        		                $viewTag = true;
        		                
        		            }
		                    
		                }
		                
		            }
		            
		        }else if( $current_taxonomy === "post_tag" ){
		            
		            $viewTag = true;
		            
		        }else if($current_postType === "post"){
		            
		            if( post_in_term(37, "category", $queried_object->ID) ){
		                
		                $viewTag = true;
		                
		            }
		            
		        }
		        
		        
		        if($viewTag){
			
                    if ( function_exists('wp_tag_cloud') ){
                        
                        $tag_args = Array(
                            
                            'smallest'  => 12,
                        	'largest'   => 22,
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
                           
                        <div class="block-entryTags">   
                            
                            <div class="h3 title-entry-tags">Облако тегов</div>
                            
                             <?php wp_tag_cloud($tag_args); ?>
                        
                        </div>
                        
                        <?php
                        	
                    }
		            
		        }
                
                
                
            ?>
			
			<div class="block-marked-lists">
			    
			<?php $productsShoppingCart = getCookie("productsShoppingCart"); 
			
			$history_products = get_history_products(2);
			
			if ( $history_products && !is_page(23) ) :
			 
			 ?>
    			 <div class="block-marked-products block-history">
    			     
    				<div class="h3 title-marked-products">Вы смотрели</div>
    				
    				<div class="list-marked-products">
    			
    				    <?php view_products_list($history_products) ?>
    					
    				</div>
    				
    				<a href="/history/" class="linkHistory">история просмотров</a>
    				
                </div>
                
			<?php endif;?>
			
			
			<?php /* if ( function_exists('zg_recently_viewed') && !is_page(3158) ): if (isset($_COOKIE["WP-LastViewedPosts"])) { ?>
			
    			<div class="aside_block widget-hits hide-when-960px">
    			     
        			<a href="/history/"><div class="h3 title show-for-large">Вы смотрели</div></a>
            			 
                    <?php zg_recently_viewed(3,true); ?>
                    
                    <a href="/history/" class="linkHistory">история просмотров</a>
                     	
                </div>
            
            <?php } endif */; 
            
			 
			 /* ХИТЫ ПРОДАЖ */
			 
			 $sidebar_hits = get_marked_products(2, "_hit");
					
			 if ( $sidebar_hits  -> have_posts() ) :
			 
			 ?>
    			 <div class="block-marked-products block-hits">
    			     
    				<div class="h3 title-marked-products">Хиты продаж</div>
    				
    				<div class="list-marked-products">
    			
    				    <?php view_products_list($sidebar_hits) ?>
    					
    				</div>
    				
                </div>
                
			<?php endif;?>
			
			<?php
			
			/* НОВИНКИ */
			
			$sidebar_newProducts = get_marked_products(2, "_newProduct");
					
			 if ( $sidebar_newProducts  -> have_posts() ) :
			 
			 ?>
    			 <div class="block-marked-products block-newProducts">
    			     
    				<div class="h3 title-marked-products">Новинки</div>
    				
    				<div class="list-marked-products">
    			
    				    <?php view_products_list($sidebar_newProducts) ?>
    					
    				</div>
    				
                </div>
                
			<?php endif;?>
				
            </div>    
            
            
			
            <!--div class="aside_block news">
                <div class="h3 title show-for-large">новости</div>
                <div class="list row">
                <?php
                    $arg = array(
                        'post_status' => 'publish',
                        'post_type' => 'post',
                        'posts_per_page' => 3
                    );
                    $sidebar_posts = new WP_Query( $arg );
                    if ( $sidebar_posts  -> have_posts() ) :
                        while ( $sidebar_posts  -> have_posts() ) : $sidebar_posts  -> the_post();
                ?>
                    <div class="column small-12 medium-6 large-12">
                        <div class="list_row">
                            <div class="date"><?php echo get_the_date('d.m.Y'); ?></div>
                            <a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute();?>" class="title_link"><?php the_title(); ?> </a>
                            <p class="descr"><?php echo wp_trim_words(get_the_content(), '15'); ?></p>
                        </div>
                    </div>
                <?php endwhile; wp_reset_postdata(); endif;  ?>
                </div>
            </div-->
			
			
        </aside>
