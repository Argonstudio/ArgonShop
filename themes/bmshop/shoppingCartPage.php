<?php 


/*
    Template name: shoppingCart
    
    БЛОК СОДЕРЖАЩИЙ ТОВАР(для edit/delete): < data-productid="id товара" class="blockCartProduct" >
    
    КНОПКА УДАЛЕНИЯ ТОВАРА: <input type="button" data-productid="id товара" class="deleteProduct">
    
    КНОПКА ОЧИСТКИ КОРЗИНЫ          <input type="button" class="deleteAllProducts" value="Очистить корзину">
    ПОЛЕ ДЛЯ ОШИБКИ ПРИ ОЧИСТКЕ     <div class="errorDeleteProducts"></div>
    
    ПОЛЕ ИЗМЕНЕНИЯ ЧИСЛА ТОВАРА: <input type="text" data-productid="<?php echo $key ?>" class="shoppingCartAmountProduct" value="<?php echo $value["amountProduct"]; ?>">
    +1 <input type="button" data-productid="<?php echo $key ?>" class="plusProduct" value="+">
    -1 <input type="button" data-productid="<?php echo $key ?>" class="minusProduct" value="-">
    
    ЦЕНА ТОВАРА ЗА ШТУКУ: < class="productPrice"><span> ЦЕНА </span> </ >
    
    ОБЩИЙ ВЕС ТОВАРА: < class="amountProductWeight" style="<?php if(!$cartTotalWeight) echo "display:none"; ?>"><span> ВЕС </span> </ >
                                            
    СУММАРНАЯ СТОИМОСТЬ ТОВАРА: < class="amountProductPrice"><span> ЦЕНА </span> </ >
    
    ВЕС ВСЕХ ТОВАРОВ В КОРЗИНЕ: < class="cartTotalWeight"> <span> ВЕС </span> </ >
     
    ЦЕНА ВСЕХ ТОВАРОВ В КОРЗИНЕ: < class="cartTotalPrice"> <span> ЦЕНА </span> </ >
    
    
    ФУНКЦИИ
    
    Получаем актуальную цену товара со скидкой getActualPrice("cart",куки,ID,цена за единицу);
    
    Получаем куки корзины getCookie("productsShoppingCart"); 
    
    Array ( [ID ТОВАРА] => Array ( [amountProduct] => 233 [price] => 430 ) [ID ТОВАРА] => Array ( [amountProduct] => 1 [price] => 500 ) ) 
    
    
*/


	get_header();
?>

<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content">
            <?php if( function_exists('kama_breadcrumbs') ) kama_breadcrumbs(' » '); ?> 

            
            <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
            <div id="product_page" class="white_block">
                
                <h1 class="title" id="<?php echo get_the_id(); ?>"><?php the_title(); ?></h1>
                
                <div id="shoppingCart">
                
                <?php
                
                $productsShoppingCart = getCookie("productsShoppingCart");
                
                if($productsShoppingCart){
                
                    $cartTotalWeight = 0;
                    $cartTotalPrice = 0;
                    
                    foreach($productsShoppingCart as $key => $value){
                        
                        $post_weight = get_post_meta( $key, '_weight', true );
                        $post_weight = str_replace(",",".",$post_weight);
                        $post_weight = (float)$post_weight;
                        
                        $post_price = get_post_meta( $key, '_price', true );
                        $post_price = getActualPrice("cart", $productsShoppingCart, $key);
                        
                        $cartTotalWeight += $post_weight*$value["amountProduct"];
                        $cartTotalPrice += $post_price*$value["amountProduct"];
                    }
                    
                    ?>
                    <div class="block-cartProductsControl">
                        
                        <div class="block-totalResult">
                        
                            <div class="cartTotalPrice">Итоговая цена: <span><?php echo $cartTotalPrice; ?></span> Р </div>
                            <div class="cartTotalWeight" style="<?php if(!$cartTotalWeight) echo "display:none"; ?>">Итоговый вес: <span><?php echo $cartTotalWeight; ?></span> кг </div>
                            
                        </div>
                        
                        <div class="block-deleteAllProducts">
                        
                            <input type="button" class="deleteAllProducts" value="Очистить корзину">
                            <div class="errorDeleteProducts"></div>
                        
                        </div>
                        
                    </div>
                    <?php
                    
                    
                    foreach($productsShoppingCart as $key => $value){
                        
                        $post_data = get_post( $key );
                        
                        $post_img = get_the_post_thumbnail_url( $key, "thumbnail" );
                        $post_link = get_post_permalink($key);
                        $post_name = $post_data->post_title;
                        $post_weight = get_post_meta( $key, '_weight', true );
                        $post_article = get_post_meta( $key, '_article', true );
                        
                        $post_price = get_post_meta( $key, '_price', true );
                        $post_price = getActualPrice("cart", $productsShoppingCart, $key);
                        
                        $post_weight = str_replace(",",".",$post_weight);
                        $post_weight = (float)$post_weight;
                        
                        ?>
                        
                        <table data-productid="<?php echo $key ?>" class="blockCartProduct">
                            <tr>
                                <td>
                                    
                                    <table class="block-bascetDescrProduct">
                                        <tr>
                                            
                                            <?php if($post_img){ ?>
                                            
                                            <td class="block-bascetProductImg"> <img class="bascetProductImg" src="<?php echo $post_img;  ?>"></td>
                                            
                                            <?php } ?>
                                
                                            <td class="block-bascetProductNameArticle">
                                                
                                                <a  href="<?php echo $post_link;  ?>"><?php echo $post_name; ?></a>
                                                
                                                <?php if($post_article){ ?>
                                                    
                                                    <div class="block-bascetProductArticle">артикул: <?php echo $post_article; ?></div>
                                                
                                                <?php } ?>
                                                
                                            </td>
                                            
                                            <td class="block-bascetDeleteShoppingCart">
                                                <input type="button" data-productid="<?php echo $key ?>" class="deleteProduct">
                                            </td>
                                            
                                        </tr>
                                    </table>
                                    
                                </td>
                                
                            </tr>
                            
                            <tr>
                                <td>
                                    
                                    <table class="block-bascetCharactProduct">
                                        
                                        <?php 
							    
        							        $unitProduct = get_field ('unit_product', $key); 
        							        
        							        if(!$unitProduct){
        							            
        							            $unitProduct = "шт";
        							            
        							        }
        							    
        							    ?>
                                        
                                        <tr class="block-bascetCharactProductTitle">
                                            <td class="productPriceTitle">Р/<?=$unitProduct ?></td>
                                            <td>Количество</td>
                                            
                                            <?php if($post_weight){ ?>
                                            
                                                <td class="amountProductWeightTitle">Вес</td>
                                            
                                            <?php } ?>
                                            
                                            <td class="amountProductPriceTitle">Цена</td>
                                            
                                            <td class="mobileBlock-amountProductTitle">Товар</td>
                                        </tr>
                                        
                                        <tr class="block-bascetCharactProductContent">
                                            <td class="productPrice"><span><?php echo $post_price;  ?></span> </td>
                                            
                                            <td class="block-bascetAmountProduct">
                                                
                                                <input type="text" data-productid="<?php echo $key ?>" class="shoppingCartAmountProduct" value="<?php echo $value["amountProduct"]; ?>">
                                                
                                                <div class="block-bascetChangeValue">
                                                    
                                                    <input type="button" data-productid="<?php echo $key ?>" class="plusProduct" value="+">
                                                    <input type="button" data-productid="<?php echo $key ?>" class="minusProduct" value="-">
                                                    
                                                </div>
                                                
                                            </td>
                                            
                                            <?php if($post_weight){ ?>
                                            
                                                <td class="amountProductWeight"><span><?php echo $post_weight*$value["amountProduct"];  ?></span> кг</td>
                                            
                                            <?php } ?>
                                            
                                            <td class="amountProductPrice"><span><?php echo $post_price*$value["amountProduct"];  ?></span> Р</td>
                                            
                                            <td class="mobileBlock-amountProduct">
                                                
                                                <div class="productPrice mobileBlock-productPrice">Р/<?=$unitProduct ?>: <span><?php echo $post_price;  ?></span> </div>
                                                
                                                <?php if($post_weight){ ?>
                                            
                                                <div class="amountProductWeight">Вес: <span><?php echo $post_weight*$value["amountProduct"];  ?></span> кг</div>
                                            
                                                <?php } ?>
                                                
                                                <div class="amountProductPrice">Цена: <span><?php echo $post_price*$value["amountProduct"];  ?></span> Р</div>
                                                
                                            </td>
                                        </tr>
                                        
                                    </table>
                                    
                                </td>
                            </tr>
                            
                            <tr><td class="shoppingCartError"><span></span></td></tr>
                        </table>
                        
                        
                        <?php
                    }
                    
                    ?>
                    
                    <div class="block-cartProductsControl block-cartControlAfter">
                        
                        <div class="block-totalResult">
                        
                            <div class="cartTotalPrice">Итоговая цена: <span><?php echo $cartTotalPrice; ?></span> Р </div>
                            <div class="cartTotalWeight" style="<?php if(!$cartTotalWeight) echo "display:none"; ?>">Итоговый вес: <span><?php echo $cartTotalWeight; ?></span> кг </div>
                            
                        </div>
                        
                        <div class="block-deleteAllProducts">
                        
                            <input type="button" class="deleteAllProducts" value="Очистить корзину">
                            <div class="errorDeleteProducts"></div>
                        
                        </div>
                        
                    </div>
				
    				<div class="block-orderRegistration">
    				
        				<h1>Оформление заказа</h1>
        				
        				<?php 
        				    $accountData = get_user_meta($user_ID, "accountData", true);
        				    
        				?>
        				
        				
        				<div class="pageControlPanel cartControlPanel">
                            
                            <div class="itemControlPanel <?php echo getClassActiveItem($user_ID,"cartQuickOrder"); ?>" data-type="switch" name="cartQuickOrder">Краткое оформление</div>
                            <div class="itemControlPanel <?php echo getClassActiveItem($user_ID,"cartPerson"); ?>" data-type="switch" name="cartPerson">Физические лица</div>
                            <div class="itemControlPanel <?php echo getClassActiveItem($user_ID,"cartLegalPerson"); ?>" data-type="switch" name="cartLegalPerson">Юридические лица</div>
                            
                        </div>
                        
                        <div class="pageControlPanel cartControlPanel-mobile">
                            
                            <div class="itemControlPanel <?php echo getClassActiveItem($user_ID,"cartQuickOrder"); ?>" data-type="switch" name="cartQuickOrder">Быстро</div>
                            <div class="itemControlPanel <?php echo getClassActiveItem($user_ID,"cartPerson"); ?>" data-type="switch" name="cartPerson">Физлица</div>
                            <div class="itemControlPanel <?php echo getClassActiveItem($user_ID,"cartLegalPerson"); ?>" data-type="switch" name="cartLegalPerson">Юрлица</div>
                            
                        </div>
                        
                        
                        
                            <div class="blockItemPage" id="block_cartQuickOrder">
                                
                                <?php get_template_part( 'template-parts/cart/quickOrder' ); ?>
                                
                            </div>
                            
                            <div class="blockItemPage" id="block_cartPerson">
                                
                                <?php get_template_part( 'template-parts/cart/person' ); ?>
                                
                            </div>
                            
                            <div class="blockItemPage" id="block_cartLegalPerson">
                                
                                <?php get_template_part( 'template-parts/cart/legalPerson' ); ?>
                                
                            </div>
                        
        				
        				<?php
        				
        				}else{
        				    
        				    echo "Корзина пуста";
        				  
        				} 
        				
        				?>
    				
    				</div>
				</div> <!-- productsShoppingCart -->
            </div>
            <?php endwhile; ?>
           
        </main>
        
         
         
    </div>
    
</section>

<?php get_footer(); ?>