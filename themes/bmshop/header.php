<?php
/*

ПОЛУЧАЕМ КУКИ getCookie("productsShoppingCart");
ПОЛУЧАЕМ ЧИСЛО ТОВАРОВ И ИХ СТОИМОСТЬ ИЗ КОРЗИНЫ getDataCart($productsShoppingCart);

ВЫВОД ЧИСЛА ТОВАРОВ < class="viewBlock-amountProducts"><span>ЧИСЛО</span></ >
ВЫВОД СТОИМОСТИ ТОВАРОВ < class="viewBlock-priceProducts"><span>ЦЕНА</span> </ >

*/

?>

<!DOCTYPE html>
<html>
<head lang="ru" class="no-js" dir="ltr">
    
    <meta charset="UTF-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
	
	
</head>
<body <?php body_class();?>>

<header id="header">
    
    <?php
    
    $pageSearch = false;
    
    if( isset($_GET["s"]) ){
        
        $pageSearch = true;
        
    }
    
    ?>
    
    <div class="top-block compensate-for-scrollbar"> 
			<div class="top-content">
			    
			    <input type="button" class="openMobileMenu">
			    
			    <a data-fancybox data-type="ajax" data-src="/adres-na-karte-moskva/" href="javascript:;">
			        <input type="button" class="openMobileMap">
			    </a>
			    
				<nav class="block-menu" id="block-menu">   
					<?php 
						wp_nav_menu( array(
							'theme_location'    => 'top_menu',
							'menu_id'           => 'top-menu'
						) );
					?>
				</nav> 
			
			    <div class="block-rightElementsTopContent" style="<?=(!$pageSearch )?'margin-right:51px':'' ?>">
			
        			<a href="/lichnyj-kabinet/"><input type="button" class="myCabinetButton" value="Мой кабинет"></a>
        			
    			    <?php as_infocart($post->ID); ?>
    			    
    			</div>
    			
    			<?php if( !$pageSearch ){  ?>
    			
        			<div class="block-topSearch">
            			    
            			    <?php 
            			    
            			      $searchParameters = Array(
            			            
            			            "post_type"   => Array('product'),
            			            'taxonomy'    => Array(
            			                
            			                "catalog"  => "all",
            			                
            			                ),
            			                
            			            "classForm"   => "topSearchForm",
            			            "classInput"  => "topSearchInput",
            			            "classSubmit" => "topSearchSubmit",
            			            "valueSubmit" => "",
            			            
            			            "ajax"        => Array(
            			                
            			                              "classBlockResult" => "topSearchBlockResult",
            			                              "positionResult"   => "bottom",
            			                              'productResult'    => true,
            			                              "catalogResult"    => true,
            			                              "postResult"       => Array(
            			                                  
                                			                     Array(
                                                			                      
                                			                         "name"         => "Акции и статьи",
                                			                         "class"        => "resultEntry",
                                			                         "category"     => Array(36,37) 
                                			                                      
                                			                      )
                            			                                  
                            			                     )
        			                                )     
            			            
            			            );    
            			            
            			    
            			        as_search($searchParameters); 
            			    
            			    ?>
            			    
            			</div>
        			
        			<?php }  ?>
			
			</div>
	</div>
    
    <div class="header_top">
        <div class="row align-justify">
		
			<div class="icon_call_address menu_mobile menu_mobile_address topmenu_mobile column"></div>
			
            <div class="logo">
                <a href="/"><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/img/logo.png" alt=""></a>
            </div>
            
            <div class="block-address">
                
                <p class="address"><span class="map_icon"></span><a data-fancybox data-type="ajax" data-src="/adres-na-karte-moskva/" href="javascript:;" class="maps_link" >Москва, ул. Ленина, 1</a></p>
                
                <p class="work_time">ПН-ПТ: с 9:00 до 18:00</br>СБ: с 10:00 до 15:00</p>
                                
            </div>
            
            <div class="block-communication">
                <a href="tel:89999999999" class="phone"><span class="phone_icon"></span> +7 (999) 999-99-99 </a>
    				
    			<a data-fancybox data-src="#feedback" id="linkFeedback" href="javascript:;">
                    ОБРАТНАЯ СВЯЗЬ
                </a>
                
                <?php 
                
                if(is_archive() || is_single() || is_page(639)){
                    
                    $istra_promoCategory = get_category(35);
                    
                    $istra_dataSitySelect = "data-promocategory='/".$istra_promoCategory->slug."/'";
                    
                    $istra_promoArchive = get_post(642);
                    
                    $istra_dataSitySelect .= " data-promoarchive='/".$istra_promoArchive->post_name."/'";
                    
                }
                
                ?>
                
                <select class="sitySelect">
                    <option selected="" value="http://site.com">Москва</option>
                    <option <?=$istra_dataSitySelect ?> value="http://istra.site.com">Истра</option>
                </select>
                
            </div>
            
            <div style="display: none;" id="feedback">
                <?php echo do_shortcode( '[contact-form-7 id="7" title="Обратная связь"]' ); ?>
            </div>
            
        </div>
    </div>
    
</header>



