<?php

//Получаем списки отмеченных товаров(хиты, новинки)
function get_marked_products($count,$type){
    
    $post_type = "product";
    $taxonomy = "catalog";
    
    $arg = array(
		'post_type' => $post_type,
		'posts_per_page' => $count,
		
		'meta_query'	=> array(
    		array(
    			'key'	 	=> $type,
    			'value'	  	=> '',
    			'compare' 	=> '!=',
    			),
		),
		
	    'tax_query' => get_tax_query_marked_products($post_type, $type, $taxonomy),
	    //'post__not_in' => array(53)
	);
	
	//Находясь на странице товара не выводим его карточку в хитах(и подобных блоках)
	if( is_singular($post_type) ){
	    $arg["post__not_in"] = array( get_the_ID() );
	}
	
	if( get_cartPageID() == get_the_ID() ){
	    
	    $productsShoppingCart = getCookie("productsShoppingCart");
	    
	    $postNotIn = Array();
	    
	    foreach($productsShoppingCart  as $key => $value){
	        array_push($postNotIn, $key);
	    }
	    
	    $arg["post__not_in"] = $postNotIn;
	}
	
	$marked_products = new WP_Query( $arg );
	
	return $marked_products;
    
}

//Получаем маркированные товары только для открытой категории
//Товары Гипсокартон1, Гипсокартон2 для категории Гипсокартон

//Формируем запрос для получения товаров только из открытой категории
function get_tax_query_marked_products($post_type,$type,$taxonomy){
    
    $tax_query = '';
    
    $term_ids = get_terms_ids_marked_products($post_type,$type,$taxonomy);
    
    if(!empty($term_ids)){
		$tax_query = array(
			array(
				'taxonomy' => $taxonomy,
				'field'    => 'id',
				'terms'    => $term_ids
			)
		);
	}
	
	return $tax_query;
    
}

//Формируем список терминов из которых получить товары
function get_terms_ids_marked_products($post_type,$type,$taxonomy){
    
    if( is_singular($post_type) ){
        
		$terms = get_the_terms( get_the_ID(), $taxonomy );
		$ids = array();
		
		foreach($terms as $term){
			$ids[] = $term->term_id;
		}
		
		$term_ids = $ids;
		
	} elseif ( is_tax($taxonomy) ){
	    
		$queried_object = get_queried_object();
		$term_ids = array($queried_object->term_id);
		
		$arg = array(
			'post_type' => $post_type,
			'posts_per_page' => -1,
			'meta_query'	=> array(
				array(
					'key'	 	=> $type,
					'value'	  	=> '',
					'compare' 	=> '!=',
				),
			),
			'tax_query' => array(
				array(
					'taxonomy' => $taxonomy,
					'field'    => 'id',
					'terms'    => $term_ids
				)
			)
		);
		
		$markedProducts = new WP_Query( $arg );
		$found_markedProducts = $markedProducts->found_posts;
		
		if($found_markedProducts == 0){
			$term_ids = '';
		}
		
	} else {
		$term_ids = '';
	}
	
	return $term_ids;
    
}