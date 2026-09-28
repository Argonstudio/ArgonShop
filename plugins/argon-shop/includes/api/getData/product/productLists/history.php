<?php

function get_history_products($count){
    
    $post_type = "product";
    $history = getCookie('AS_History');
    
    if(!$history){
        return false;
    }
    
    if($count && $count !== -1 && $count !== "all"){
        $history = array_slice($history, 0, $count);
    }
    
    $arg = array(
		'post_type' => $post_type,
		'post__in' => $history,
		'orderby'  => 'post__in'
	);
    
    $history_products = new WP_Query( $arg );
    
    return $history_products;
    
}