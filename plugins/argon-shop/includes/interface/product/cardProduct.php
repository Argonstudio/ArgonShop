<?php

function button_card_product($productID, $cardInBascet, $cardBuy, $productsShoppingCart){
    
    ?>
    
    <!-- Кнопки добавления -->
    <div class="block-cardProductBascet" style="<?php if( $productsShoppingCart[$productID] ) { ?> display:none <?php } ?>">
        
        <?php if($cardInBascet !== false){ ?>
        
            <input type="button" data-productid="<?=$productID ?>" class="card-inBascet" value="В корзину">
        
        <?php } ?>
        
        <?php if($cardBuy !== false){ ?>
        
            <input type="button" data-productid="<?=$productID ?>" class="card-buy" value="Купить">
        
        <?php } ?> 
        
    </div>
    
    <!-- Блок товар в корзине -->
    <div class="card-alreadyAdded" style="<?php if( !$productsShoppingCart[$productID] ) { ?> display:none <?php } ?>">
        
        <?php 
        
        $cardalreadyAddedValue = "Товар добавлен в корзину";
        
        if( get_cartPageID() == get_queried_object()->ID ){
            $cardalreadyAddedValue = "Обновить корзину";
        } 
        
        ?>
        
        <a href="<?php echo get_cartPageURL(); ?>"><input type="button" value="<?=$cardalreadyAddedValue ?>"></a>       
        
                                                    
    </div>
    
    <!-- Блок для ошибок -->                                             
    <div class="card-addProductError"></div>
    
    <?php
}


function productInBascet(){
    echo 1;
}