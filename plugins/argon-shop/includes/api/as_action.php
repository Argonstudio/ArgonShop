<?php

function as_update_meta($post_id, $key, $value = 0){
    
    if($value){
             
        update_post_meta($post_id, $key, $value);
             
    }else{
             
        delete_post_meta($post_id, $key);
             
    }
    
}

// Partially merges arrays:
// $quantity is the maximum number of items from $attachArray that may be appended to $summaryArray
function as_array_partial_merge($quantity, $summaryArray, $attachArray){
    
    if($quantity > 0){
        
        if( $quantity < count($attachArray) ){
            
            $attachArray = array_slice($attachArray, 0, $quantity); 
        }
            
        $summaryArray = array_merge($summaryArray, $attachArray);
    }    
    
    return $summaryArray;
        
}