<?php 
	get_header();
	
	/*
	
	ПОЛУЧАЕМ ОПТОВЫЕ ЦЕНЫ $wholesalePrice = get_post_meta(get_the_ID(), '_wholesalePrice',true);
	
	ХАРАКТЕРИСТИКИ ТЕКСТОВЫЕ ЗНАЧЕНИЯ $characteristicsText = get_post_meta(get_the_ID(), '_characteristics_text_field',true);
	ХАРАКТЕРИСТИКИ С ЧЕКБОКСАМИ $characteristicsCheckbox = get_post_meta(get_the_ID(), '_characteristics_checkbox_field',true);
	
	ЦЕНА ТОВАРА < id="basePrice"> ЦЕНА </ >
	
	ПОЛЕ ВВОДА КОЛИЧЕСТВА ТОВАРА <input type="text" id="amountProduct" value="1">
	КНОПКА ДОБАВЛЕНИЯ В КОРЗИНУ <input type="button" id="addShoppingCart" data-productid="ID товара" value="В корзину">
	
	СТОИМОСТЬ УКАЗАННОГО В ПОЛЕ ВВОДА ТОВАРА <div class="totalPrice">сумма <span></span> руб.</div>
	СКОЛЬКО ТОВАРА В КОРЗИНЕ <div id="howManyProducts"><span>число</span></div>
	
	ОШИБКА ПРИ ДОБАВЛЕНИИ В КОРЗИНУ <div id="addProductError"></div>    
	
	
	*/
	
?>

<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content">
          
            <?php if( function_exists('kama_breadcrumbs') ) kama_breadcrumbs(' » '); ?> 

            
            <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
            <!--<div id="product_page" class="white_block">-->
                
                <?php 
                    $price = get_post_meta( get_the_ID(), '_price', true );
                    $article = get_post_meta( get_the_ID(), '_article', true );
                    $post_imgs = get_post_meta( get_the_ID() ,"slider_imgs", true);
                    
                    $productsShoppingCart = getCookie("productsShoppingCart");
                    
                    if( check_saleSteps() && $productsShoppingCart ){
                        
                        $actualStepDiscont = getActualStepDiscont(get_the_ID(), $productsShoppingCart, 1*$price);
                        $price = getActualPrice("pageProduct", $productsShoppingCart, get_the_id(), $price, 1);
                        
                    }
                    
                    $post_imgs = explode(",", $post_imgs);
                    $imgs_count = count($post_imgs);
                    
                    
                    if( !isset( $price ) ){
						$price = "none";
					}
                
                ?>
                
                <h1 class="title"> <?php the_title(); ?></h1>
                
                <?php if($article){ ?>
                    
                    <div class="articlePost">артикул: <?php echo $article; ?></div>
                    
                <?php } ?>
                
                
                                <!--<a href="<?//=$image_url[0] ?> " data-fancybox="images1">
                                    
                                    
                                </a> -->
            
            
                <div class="block-productMain">
                    
                    <div class="block-productSlider">
                        
                        <?php
                        
                        if($post_imgs[0]){
                        
                            $for_data_slick = ($imgs_count > 3)?'{"asNavFor": ".slider-nav"}':'';
                            //$nav_data_slick = ($imgs_count > 3)?'{"slidesToShow": 3}':'{"slidesToShow":'.$imgs_count.'}';
                            $nav_data_slick = '{"slidesToShow": 3}';
                            ?>
                            
                            <div class="slider-for" data-slick='<?=$for_data_slick ?>'>
                            
                            <?php
                            
                                foreach($post_imgs as $value){
                                    
                                    $imageData = wp_get_attachment_image_src( $value, 'full' );
                                    $bigImage = ($imageData[1] > 400 || $imageData[2] > 270)?true:false;
                                    
                                    //$maxWidth = (!$bigImage)?$imageData[1]."px":"100%";
                                    //$maxHeight = (!$bigImage)?$imageData[2]."px":"100%";
                                    
                                    //$styleMediumImg = "max-width:".$maxWidth."; max-height:".$maxHeight;
                                    $styleMediumImg = "max-width:100%; max-height:100%";
                                    
                                    echo ($bigImage)?'<a href="'.$imageData[0].'" data-fancybox="productSlider">':'';
                                    
                                    ?>
                                        <div class="block-mediumImgProductSlider">
                                            
                                            <span class="mediumImgProductSlider">
                                                <img style="<?=$styleMediumImg ?>" src="<?=$imageData[0] ?>">
                                            </span>
                                            
                                        </div>
                                        
                                        <!--<div class="mediumImageProductSlider" style="background-image:url('<?//=$imageData[0] ?>');background-size:<?//=($bigImage)?'contain':'auto auto' ?>"></div>  -->
                                    <?php
                                    
                                    echo ($bigImage)?'</a>':''; 
                                    
                                }
                                
                            
                                ?>
                            
                            </div>
                            
                            
                            <div class="slider-nav" data-slick='<?=$nav_data_slick ?>'>
                                
                                <?php
                                
                                foreach($post_imgs as $value){
                                    
                                    $imageData = wp_get_attachment_image_src( $value, 'full' );
                                    
                                    ?>
                                        
                                    <div class="smallImageProductSlider" style="background-image:url('<?=$imageData[0] ?>')"></div>
                                    <?php
                                    
                                    
                                }
                                
                                ?>
                                
                            </div>
                        
                        <?php } ?>
                        
                    </div>
                    
					<table class="product-table-details">
						<tbody>
							<tr>
							    
							    <?php 
							    
							        $unitProduct = get_field ('unit_product', get_the_ID()); 
							        
							        if(!$unitProduct){
							            
							            $unitProduct = "шт";
							            
							        }
							    
							    ?>
							    
								<td class="block-basePrice"> 
								    <span id="basePrice"><?php echo $price;?></span> 
								    <span class="unitMeasure">Р/<?=$unitProduct ?></span>
								</td>
							</tr>
								
							<?php
								    
								$wholesalePrice = get_post_meta(get_the_ID(), '_wholesalePrice',true);
								
								if($wholesalePrice){
								?>
								<tr>
									<td class="block-wholesale-price">
										    
									    <div class='title-wholesale-price'>ОПТОВАЯ ЦЕНА, от:</div>
										    
									    <table>
										        
									    <?php foreach($wholesalePrice as $key=>$value){ 
										        
        									    $term = get_term_by(id,$key,"wholesalePrice");
                        
                                                if(!$term){
                                                    continue;
                                                }
										        
										 ?>
										        
    									    <tr class="<?=($actualStepDiscont == $term->name)?'actualStepDiscont':'' ?>">
    									        
            									<td class="step-discont-name"> <span><?php echo $term->name; ?></span> Р </td>
            									<td class="step-discont-value"> <span><?php echo $value; ?></span> Р/<?=$unitProduct ?> </td>
    										            
    										</tr>
										        
										 <?php } ?>
										        
									    </table>
									    
										<div class="clarify">учитывается стоимость товаров в корзине</div>
								    </td>
								</tr>
							<?php } ?>
								
								<tr>
									<td>
										    
									<div class="block-pageAmountProduct">
										    
        								<input type="text" id="amountProduct" value="1">
        										    
        								<div class="block-pageChangeValue">
                                                            
                                            <input type="button" class="plusProduct-Page" value="+">
                                            <input type="button" class="minusProduct-Page" value="-">
                                                            
                                        </div>
    									
    									<?php 
    									
    									$disabledProduct = ($price === "none")?"disabled=''":"";
    									
    									?>
    										    
    									<input type="button" id="addShoppingCart" class="buttonPurchase" data-productid="<?php echo get_the_id(); ?>" <?=$disabledProduct ?> value="В корзину">
										<input type="button" id="productPageCartBuy" class="buttonPurchase" data-productid="<?php echo get_the_id(); ?>" <?=$disabledProduct ?> value="Купить">
									
									    <div class="totalPrice">сумма <span></span> руб.</div>
										    
									</div>
										    
									
										    
										    
									<?php 
                                        $productsShoppingCart = getCookie("productsShoppingCart");
									?>
										               
                                    <div id="howManyProducts" style="<?php if( !$productsShoppingCart[get_the_id()] ) { ?> display:none <?php } ?>">
                                        
                                        <a href="<?php echo get_cartPageURL(); ?>"><input type="button" value="Перейти в корзину"></a>
                                        
                                        <div class="howManyProductsText">
                                        
                                            добавлено 
                                                        
                                            <span>
                                                <?php 
                                                                    
                                                if( $productsShoppingCart[get_the_id()] ){
                                                                        
                                                    $amountProduct = $productsShoppingCart[get_the_id()]["amountProduct"];
                                                    echo $amountProduct; 
                                                                         
                                                }    
                                                                
                                                ?>
                                            </span> 
                                                            
                                            ед. данного товара<br/>
                                                
                                        </div>
                                                
                                    </div>
                                             
                                    <div class="ajaxError" id="addProductError"></div>    
										    
										    
								</td>
						    </tr>
						</tbody>
					</table>
						
                </div>
                
                <?php
					
					$productUsage = wpautop (get_field ('application', get_the_ID()));			
				?>
                
                <div class="block-productAbout">
                
                    <div class="pageControlPanel productPageControlPanel">
                        
                        <div class="itemControlPanel itemControlPanelActive" data-type="switch" name="productDescription">Описание</div>
                        <div class="itemControlPanel" data-type="switch" name="productСharacteristics">Характеристики</div>
                        
                        <?php if($productUsage){ ?>
                        
                            <div class="itemControlPanel " data-type="switch" name="productUsage">Применение</div>
                        
                        <?php } ?>
                        
                    </div>
                    
                    <div class="block-productDescription">
                        
                        <div class="mobileItemControlPanel" data-mobileWidth="720" data-type="openingList" name="productDescription">Описание</div>
                        
                        <div class="blockItemPage" id="block_productDescription"><?php the_content(); ?></div>
                        
                    </div>
                    
                    <div class="block-productCharacteristics">
                        
                        <div class="mobileItemControlPanel" data-mobileWidth="720" data-type="openingList" name="productСharacteristics">Характеристики</div>
                        
                        <div class="blockItemPage" id="block_productСharacteristics"><?php get_template_part( 'template-parts/productPage/characteristics' ); ?></div>
                        
                    </div>
                    
                    <?php if($productUsage){ ?>
                    
                        <div class="block-productUsage">
                            
                            <div class="mobileItemControlPanel" data-mobileWidth="720" data-type="openingList" name="productUsage">Применение</div>
                            
                            <div class="blockItemPage" id="block_productUsage"><p><?=$productUsage ?></p></div>
                            
                        </div>
                    
                    <?php } ?>
				
				</div>
				
            <!--</div>-->
            <?php endwhile; ?>
           
        </main>
        
    </div>
</section>

<?php get_footer(); ?>