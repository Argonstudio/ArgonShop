<?php

//Получаем отсортированный список товаров
function get_sort_products($typeSort, $howSorting, $catId, $pageNum){
    
    $post_type = "product";
    $taxonomy = "catalog";
    
    $arg = array(
		'post_type'      => $post_type,
		'post_status'    => 'publish',
		'paged'          => $pageNum,     
		'posts_per_page' => get_option( 'posts_per_page', 12 ),
		'tax_query'      => array(
			array(
				'taxonomy' => $taxonomy,
				'field'    => 'id',
				'terms'    => $catId
			)
		),
		
		'order'          => $howSorting
		
	);
	
	switch($typeSort){
	    case "date":
	        $arg["orderby"] = "date";
	        break;
	    case "name":
	        $arg["orderby"] = "name";
	        break;
	    case "price":
	        $arg["orderby"] = "meta_value_num";
	        $arg['meta_key'] = '_price';
	        break;     
	}
	
	$productsList = new WP_Query( $arg );
	
	return $productsList;
    
}