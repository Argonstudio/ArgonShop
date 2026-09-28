<?php

function search_filter($query) {    
    
    if ( ! is_admin() && $query->is_main_query() ) {
        
        if ($query->is_search) {
            
            if($_GET["post_type"]){
                
                $postType = $_GET["post_type"];
                $taxonomy = $_GET["as_taxonomy"];
                
                
                $tax_query = array(
            		'relation' => 'OR',
            	);
                
                foreach($taxonomy as $key => $value){
                    
                    $taxonomy_query = Array(
                        
                        'taxonomy' => $key,
                        'field'    => 'id',
                        
                        );
                        
                    $taxonomy_terms;
                    
                    if($value == "all"){
                        
                        $args = array(
                        	'taxonomy' => $key,
                        	'fields'   => "ids",
                        	'get'      => 'all'
                        );
                        $taxonomy_terms = get_terms( $args );
                        
                    }else{
                        $taxonomy_terms = unserialize($value);
                    }
                    
                    $taxonomy_query['terms'] = $taxonomy_terms;
                    
                    array_push($tax_query, $taxonomy_query);
                    
                };
                
                
                $query->set('tax_query', $tax_query);
            }            
    	  
    	}
    	
    }
    
}
add_action( 'pre_get_posts', 'search_filter' );