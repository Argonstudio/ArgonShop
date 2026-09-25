<?php

//Проверяем принадлежность поста к категории указанной таксономии и её дочерним терминам
//Вызовы в цикле post_in_term([37,1], "category") можно делать не указывая ID поста

//Первым параметром можно передавать один термин 
//    post_in_term(37, "category", $queried_object->ID) 
//    или несколько post_in_term([37,1], "category", $queried_object->ID)

function post_in_term( $cats, $tax, $post ){
    //$_post = null
	foreach ( (array) $cats as $cat ) {
	    
		// get_term_children() accepts integer ID only
		if( has_term( $cat, $tax, $post ) ){
		    return true;
		}
		
		$descendants = get_term_children( (int) $cat, $tax );
		
		if ( $descendants && has_term( $descendants, $tax, $post ) ){
		    
		    return true;
		    
		}
			
	}
	
	return false;
}