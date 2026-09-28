<?php

function save_data_order( $post_id ) {
          
     // проверяем, пришёл ли запрос со страницы с метабоксом
     if ( !isset( $_POST['detailOrders_protect_name'] ) 
     || !wp_verify_nonce( $_POST['detailOrders_protect_name'], 'detailOrders_protect_action' ) )
            return $post_id;
      
     // проверяем, является ли запрос автосохранением
     if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
     return $post_id;
     
     // проверяем права пользователя, может ли он редактировать записи
     if ( !current_user_can( 'edit_post', $post_id ) )
     return $post_id;
     
     // теперь также проверим тип записи 
     $post = get_post($post_id);
     
     
     if ($post->post_type == 'shoporder') { 
           
        $postProduct = $_POST['order_product'];
        //$completeOrderProduct = cheak_detailed_fields($orderProduct);
        
        $orderProducts = Array();
        
        foreach($postProduct as $key => $value){
            
            $post = get_post( $key );
            $title = $post->post_title;
            
            $orderProducts[$key] = Array(
                
                "amountProduct" => $value,
                "price"         => get_post_meta($key, '_price', true), 
                "name"          => $title,
                "link"          => get_post_permalink($key)
                );
            
        }
        
        if( check_saleSteps() ){
            
            $discountPriceProducts = getActualPriceAllProducts($orderProducts);
            
            foreach($discountPriceProducts as $key => $value){
                
                $orderProducts[$key]["price"] = $value;
                
            }
        }
        
        $numberPost = get_post_meta($post_id, '_number',true);
        $amount = getDataCart($orderProducts);
        
        $post_data = array(
        	'post_title'    => "Заказ №".$numberPost." Товаров в заказе: ".$amount["productAmountCart"].", на сумму: ".$amount["totalPrice"],
        	
        );
        
        if ( ! wp_is_post_revision( $post_id ) ){
    		// удаляем этот хук, чтобы он не создавал бесконечного цикла
    		remove_action('save_post', 'save_data_order');
    
    		// обновляем пост, когда снова вызовется хук save_post
    		wp_update_post( $post_data );
    
    		// снова вешаем хук
    		add_action('save_post', 'save_data_order');
    	}
        
        update_post_meta($post_id, '_productAmountCart', $amount["productAmountCart"]);
        update_post_meta($post_id, '_totalPrice', $amount["totalPrice"]);
        update_post_meta($post_id, '_productsCart', $orderProducts);
        
        $infoValue = $_POST['_fieldValue'];
        $infoName = $_POST['_fieldName'];
        
        $userInfo = Array();
        
        foreach($infoValue as $key => $value){
            
            $userInfo[$key] = Array(
                
                "name"  => $infoName[$key],
                "value" => $value
                
                );
            
        }
        
        update_post_meta($post_id, '_fieldsCart', $userInfo);
        
        $typeShipping = $_POST['shipping'];
        $typePayment = $_POST['payment'];
        $statusID = $_POST['status'];
        
        update_post_meta($post_id, '_shipping', $typeShipping);
        update_post_meta($post_id, '_payment', $typePayment);
        
        wp_set_object_terms( $post_id, NULL, 'statusorders' );
        wp_set_object_terms( $post_id, (int)$statusID, 'statusorders' );
        
        //print_r($orderProducts);
        //wp_die();     
        //as_update_meta($post_id, '_wholesalePrice', $completeWholesalePrice);
             
             
         
         /*
         $price = $_POST['_price'];
         as_update_meta($post_id, '_price', $price);
         
         $weight = $_POST['_weight'];
         as_update_meta($post_id, '_weight', $weight);
         
         $article = $_POST['_article'];
         as_update_meta($post_id, '_article', $article);
         
         
         
         $characteristicsTextField = $_POST['characteristics_text_field'];
         $completeCharacteristicsTextField = cheak_detailed_fields($characteristicsTextField);
         
         as_update_meta($post_id, '_characteristics_text_field', $completeCharacteristicsTextField);
         */
         
     }
     
     return $post_id;
     
}
 
add_action('save_post','save_data_order');