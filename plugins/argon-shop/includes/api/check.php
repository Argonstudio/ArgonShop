<?php

//Проверяем принадлежность поста к категории указанной таксономии и её дочерним терминам
function post_in_term( $cats, $tax, $_post = null ){
    
	foreach ( (array) $cats as $cat ) {
	    
		// get_term_children() accepts integer ID only
		if( has_term( $cat, $tax, $post ) ){
		    return true;
		}
		
		$descendants = get_term_children( (int) $cat, $tax );
		
		if ( $descendants && has_term( $descendants, $tax, $_post ) ){
		    
		    return true;
		    
		}
			
	}
	
	return false;
}