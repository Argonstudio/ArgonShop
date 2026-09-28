<?php

function view_products_list($productsList){
    
    $productsShoppingCart = getCookie("productsShoppingCart");
    
    while ( $productsList  -> have_posts() ) : $productsList  -> the_post();
    						
    	generate_product_card($post, $productsShoppingCart);
    						
    endwhile;
    
    wp_reset_postdata();
    
}