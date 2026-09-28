<?php

/*

ПОЛУЧАЕМ КУКИ getCookie("productsShoppingCart");
ПОЛУЧАЕМ ЧИСЛО ТОВАРОВ И ИХ СТОИМОСТЬ ИЗ КОРЗИНЫ getDataCart($productsShoppingCart);

ВЫВОД ЧИСЛА ТОВАРОВ < class="viewBlock-amountProducts"><span>ЧИСЛО</span></ >
ВЫВОД СТОИМОСТИ ТОВАРОВ < class="viewBlock-priceProducts"><span>ЦЕНА</span> </ >

*/

function as_infocart($pageID){
    
    if( $pageID != get_cartPageID() ){  
    			            
    	$productsShoppingCart = getCookie("productsShoppingCart");
    	$dataCart = getDataCart($productsShoppingCart);
    			            
    	?> <a href="<?php echo get_cartPageURL(); ?>"> <?php
    			            
    	if($dataCart){
    			      
    	?>
    		<div class="viewBlock-shoppingCart">
                <div class="viewBlock-amountProducts"><span class="viewBlock-productsStock"><?php echo $dataCart["productAmountCart"]; ?></span></div>
                <div class="viewBlock-priceProducts"><span><?php echo $dataCart["totalPrice"]; ?></span> Р </div>
            </div>
        <?php
    			                 
    	}else{
    			                
    	?>
    		<div class="viewBlock-shoppingCart">
                <div class="viewBlock-amountProducts"><span></span></div>
                <div class="viewBlock-priceProducts" style="display:none"><span></span> Р </div>
            </div>
        <?php
    			                
    	}
    			            
    	?> </a> 
         			
        			
    			    
    	<?php }  
    
}