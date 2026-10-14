<?php

// Checks whether a post belongs to a term of the given taxonomy or to its child terms
// Inside the loop as_post_in_term([37,1], "category") can be called without a post ID

// The first parameter can be a single term
//    as_post_in_term(37, "category", $queried_object->ID) 
//    or several: as_post_in_term([37,1], "category", $queried_object->ID)

function as_post_in_term( $cats, $tax, $post ){
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