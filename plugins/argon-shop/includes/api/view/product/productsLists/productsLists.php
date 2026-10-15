<?php

function as_view_products_list($productsList){
    
    $productsShoppingCart = as_getCookie("productsShoppingCart");
    
    while ( $productsList  -> have_posts() ) : $productsList  -> the_post();
    	
    	$post = get_post(get_the_ID());
    						
    	generate_product_card($post, $productsShoppingCart);
    						
    endwhile;
    
    wp_reset_postdata();
    
}