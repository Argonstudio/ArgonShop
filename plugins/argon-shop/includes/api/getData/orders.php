<?php

//Возвращает все статусы заказов или статусы заказов пользователя(параметр $statuses_orders_user)
function get_used_statuses($statuses_orders_user){
    
    $argumentStatusAddTerms = array(
		'taxonomy'       => 'statusorders',
		'hide_empty'   => true,
		'fields'         => 'id=>name',
		
	);
	
    $statusTerms = get_terms( $argumentStatusAddTerms );
    
    if( !empty($statuses_orders_user) ){
        
        $statusTerms = array_intersect($statusTerms, $statuses_orders_user);
        
    }
    
    return $statusTerms;
    
}


//Возвращает все заказы пользователя или заказы с определенным статусом($filters)

//Массив из $orders["orders"] = $ordersWP; данные записей заказов из WP
//$orders["orders_meta"] = $order_meta; мета поля заказов
//$orders["orders_status"] = $order_status; статусы заказов
//если запрос сделан неверно или заказов нет возвращает пустой массив
function get_user_orders( $user_ID, $filters ){
    
    if( gettype($user_ID ) !== "integer" ){
        
        return Array();
        
    }
    
    if( !empty($filters['orderStatus']) ){
        
        if( gettype($filters['orderStatus'] ) === 'array' ){
        
            foreach( $filters['orderStatus'] as $key){
                
                if(gettype( $key ) !== "integer" ){
            
                    return Array();
                    
                }
                
            }
        }else{
            
            if( gettype( $filters['orderStatus'] ) !== "integer" ){
            
                return Array();
                
            }
            
        }
        
    }
    
    
    
    $args = array(
    	'post_type'        => 'shoporder',
    	'posts_per_page'   => -1, 
    	'author'           => $user_ID
    );
    
    if( $filters['orderStatus'] ){
        
        $args['tax_query'] = array(
    		array(
    			'taxonomy' => 'statusorders',
    			'field'    => 'id',
    			'terms'    => $filters['orderStatus']
    		)
    	);
    	
    }
    
    $ordersWP = get_posts($args);
    
    $order_meta = Array();
    $order_status = Array();
    
    if( !empty($ordersWP) ){
        
        foreach($ordersWP as $key => $value){
            
            $currentStatus = get_the_terms( $value->ID, 'statusorders' );
            
            $order_meta[$value->ID] = Array(
                
                    "number"          => get_post_meta($value->ID, '_number',true),
                    "totalPrice"      => get_post_meta($value->ID, '_totalPrice',true),
                    "productsCart"    => get_post_meta($value->ID, '_productAmountCart',true),
                    
                
                );
            
            $order_status[$value->ID] = $currentStatus[0]->name;
        }
        
    }else{
        
        return $ordersWP;
        
    }
    
    
    $orders["orders"] = $ordersWP;
    $orders["orders_meta"] = $order_meta;
    $orders["orders_status"] = $order_status;
    
    return $orders;
    
}