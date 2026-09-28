<?php

//Получаем значение характеристики
function get_charact($typeField, $valueField, $productID ){
    
    if( $typeField === "name" ){
        
        $term = get_term_by('name', $valueField, "characteristics");
        $termID = $term->term_id;
        
    }else if( $typeField === "id" ){
        
        $termID = $valueField;
        
    }else{
        
        return false;
        
    }
    
    
    $termChildren = get_term_children( $termID, "characteristics" );
    
    if( !empty($termChildren) ){
       
       $characteristics = get_post_meta($productID, '_characteristics_checkbox_field',true);
       
       $charactValue = Array();
       
       if($characteristics[$termID]){
           
           foreach($characteristics[$termID] as $key => $value){
           
               $termOption = get_term_by(id,$key,"characteristics");
               $nameOption = $termOption->name;
               
               array_push($charactValue, $nameOption);
               
           }
           
       }else{
           
           return false;
           
       }
       
       
       
    }else{
        
        $characteristics = get_post_meta($productID, '_characteristics_text_field',true);
        
        if($characteristics[$termID]){
            
            $charactValue = $characteristics[$termID];
            
        }else{
            
            return false;
            
        }
        
    }
    
    return $charactValue;
    
}