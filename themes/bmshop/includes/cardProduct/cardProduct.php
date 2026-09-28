<?php

function generate_product_card($post, $productsShoppingCart){
    
    $productID = get_the_ID();
    
    $price = get_post_meta( $productID, '_price', true );
    $article = get_post_meta( $productID, '_article', true );
    $weight = get_post_meta( $productID, '_weight', true );
    $hit = get_post_meta( $productID, '_hit', true );
    $new = get_post_meta( $productID, '_newProduct', true );
    
    $trademark = get_charact("id", 24, $productID ); //("name", "ТОРГОВАЯ МАРКА", $productID)
    
    ?>
    
    <div class="block-productCard">
        
        <div class="cardHeader">
            
            <div class="block-cardProductImage">
            
    			<?php if( $hit === "on" ): ?>
    			    <div class="cardProductMarked cardProductHit"><span>hit</span></div>
    			<?php endif;?>
    		
    			<?php if( $new === "on" ): ?>
    			    <div class="cardProductMarked cardProductNew"><span>new</span></div>
    			<?php endif;?>
    			
    			<a href="<?php the_permalink();?>">
    				<?php the_post_thumbnail('blog_thumb', array( 'class' => 'img-responsive' )); ?>
    			</a>
    			
    		</div>
            
            <ul class="block-cardProductCharact">
                
                 <?=($article)   ? "<li>Артикул: ".$article."</li>" : ""; ?> 
                 <?=($trademark) ? "<li>ТМ: ".$trademark[0]."</li>" : ""; ?>
                 <?=($weight)    ? "<li>Вес: ".$weight." кг</li>" : ""; ?>
                
            </ul>
            
        </div>
        
        <a href="<?php the_permalink();?>" class="cardProductName">
            
            <?php the_title(); ?>
            
        </a>
        
        <?php if($price){ 
            
            if( check_saleSteps() && $productsShoppingCart ){
                
                $price = getActualPrice("cardProduct", $productsShoppingCart, $productID, $price, 1);
                
            }
            
             $unitProduct = get_field ('unit_product', get_the_ID()); 
							        
			if(!$unitProduct){
							            
				$unitProduct = "шт";
							            
			}else{
			    
			    $unitProductLength = mb_strlen($unitProduct, 'utf-8');
			    
			    if($unitProductLength > 13){
			        
			        $unitProduct = mb_substr($unitProduct, 0 , 13);
			        $unitProduct .= '..';
			        
			    }
			    
			}
            
        ?>
            
            <div class="cardProductPrice"> <span><?=$price ?></span> Р/<?=$unitProduct ?></div>
            
            <?php button_card_product($productID, true, true, $productsShoppingCart) ?>
            
        <?php } ?>
        
        
        
    </div>
    
    <?php
    
    
    //echo $new;
}