<?php

// Проверяет, принадлежит ли запись термину заданной таксономии или его дочерним терминам
// Внутри цикла as_post_in_term([37,1], «category») можно вызывать без ID записи

// Первый параметр может быть одним термином
//    as_post_in_term(37, "category", $queried_object->ID) 
//    or several: as_post_in_term([37,1], "category", $queried_object->ID)

function as_post_in_term( $cats, $tax, $post ){
    //$_post = null
	foreach ( (array) $cats as $cat ) {
	    
		// get_term_children() принимает только целочисленный ID
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