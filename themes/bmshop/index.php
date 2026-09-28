<?php get_header(); ?>


<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content">
           
           <div id="slider_index">
                <div class="list">
                <?php
                    $arg = array(
                        'post_status' => 'publish',
                        'post_type' => 'slider',
                        'posts_per_page' => -1
                    );
                    $slides = new WP_Query( $arg );
                    if ( $slides -> have_posts() ) :
                        $i = 0;
                        while ( $slides -> have_posts() ) : $slides -> the_post();
                        $meta_data= get_post_meta( get_the_ID(), 'slider_custom_options', true );
                        $link = get_post_meta( get_the_ID(), 'slide_link', true );
                        
                        //=wp_get_attachment_image_url(get_the_ID())
                ?>
                    <div class="list_row">
                        
                        <?php echo ($link) ? '<a href="'.$link.'">' : ''; ?>
                        
                        <?php the_post_thumbnail('slider_thumb', array( 'class' => 'img-responsive' )); ?>
                        
                        <?php echo ($link) ? '</a>' : ''; ?>
                        </div>
                <?php $i = $i + 1; endwhile; wp_reset_postdata(); endif;  ?>
                </div>
                
            </div>
            
            
            
            <div class="block-advantages">
                
                <div class="homepage-block-title">Наши преимущества</div>
                
                <div class="block-advantages-cards">
                
                    <div class="card-advantage twentyYear">
                        
                        <div class="advantage-img"><span>+</span></div>
                        <div class="advantage-title">20 лет</div>
                        <div class="advantage-descr">на рынке стройматериалов</div>
                        
                    </div>
                    
                    <div class="card-advantage assortment">
                        
                        <div class="advantage-img"><span>+</span></div>
                        <div class="advantage-title">Ассортимент</div>
                        <div class="advantage-descr">Более 60 тысяч наименований товаров</div>
                        
                    </div>
                    
                    <div class="card-advantage trust">
                        
                        <div class="advantage-img"><span>+</span></div>
                        <div class="advantage-title">Нам доверяют</div>
                        <div class="advantage-descr">Более тысячи клиентов в месяц </div>
                        
                    </div>
                    
                    <div class="card-advantage autopark">
                        
                        <div class="advantage-img"><span>+</span></div>
                        <div class="advantage-title">Автопарк</div>
                        <div class="advantage-descr">12 машин и манипуляторы, доставка по МО</div>
                        
                    </div>
                
                </div>
                
            </div>
            
            <div class="block-homePageCatalog">
                
                <div class="homepage-block-title">Каталог строительных материалов</div>
                
                <?php 
                
                $catalog = get_catalog_terms();
                
                
                $catalogFirstLevelChild = count($catalog);
                $catalogSecondLevelChild;
                
                foreach($catalog as $key => $value){
                    
                    $catalogSecondLevelChild += $value["data"]["firstLevelChild"];
                    
                }
                
                $promoDiscontArgs = array( 
                        'numberposts' => 10, 
                        'category' => 36,
                        'meta_query'	=> array(
                    		array(
                    			'key'	 	=> "promo_active",
                    			'value'	  	=> 1,
                    			'compare' 	=> '==',
                    			),
                		),
                );
                
                $promoDiscont = get_posts( $promoDiscontArgs );
                
                $countPromoDiscont = count($promoDiscont);
                
                $countPromoDiscont++;
                
                //Разбиваем каталог на 3 примерно равных стобца +передаем число значений в блоке акции и скидки 
                $splitMenuQuery = splitMenu($catalog, 3, $catalogFirstLevelChild, $catalogSecondLevelChild, 3, 5, $countPromoDiscont);
                
                //Полученные столбцы
                $splitMenu = $splitMenuQuery[0];
                
                //Числовые значений полученных столбцов(индексы в массиве совпадают с $splitMenuQuery[0])
                $splitMenuCountsList = $splitMenuQuery[1];
                
                //Находим наименьший столбец
                $leactList = array_keys( $splitMenuCountsList, min($splitMenuCountsList) )[0];
                
                
                    //Прибавляем к наименьшему столбцу акции и скидки
                    $splitMenu[$leactList][36]["data"] = Array(
                        
                        "term_id" => 36,
                        "name"    => "Акции и скидки",
                        "class"   => "catalog-promoDiscontLink"
                        
                    );
                    
                    foreach($promoDiscont as $key => $value){
                        $splitMenu[$leactList][36]["children"][$value->ID]["data"] = Array(
                            
                                "post_id" => $value->ID,
                                "name"    => $value->post_title
                            );
                    };
                
                
                foreach($splitMenu as $subMenuID => $subMenuValue){
                                         
                    if(!empty($subMenuValue)){
                        view_menu_elements($subMenuValue, "homePageCatalogList", 2);
                    }
                                         
                                         
                }
                
                
                ?>
                
            </div>
            
            
        </main>

    </div>
</section>

<?php get_footer(); ?>