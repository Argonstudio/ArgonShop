<?php

add_action('wp_footer', 'asOrderCabinet', 99); // для фронта
function asOrderCabinet() {
	?>
	
	<script>
	
	
	    function ordersFiltersStatus(userID, status, blockResult){		    
		    
		    var data = {
    			action: 'ordersFiltersStatus',
    			userID: userID,
    			status: status
    		};
    		
		    $.ajax({
                url: myajax.url,
                data: data,
                type: 'POST',
                success: function(response) {
                  
                  //var response = JSON.parse(response); 
                  
                  blockResult.html(response);
                  
                  console.log(response);
                  
                },
                error: function(data){
                    
                    console.log(data);
                    blockResult.html("Ошибка на сервере");
                    
                }
              });
		    
    		
		}
		

	</script>
	<?php
}

add_action('wp_ajax_ordersFiltersStatus', 'ordersFiltersStatus_callback');
add_action('wp_ajax_nopriv_ordersFiltersStatus', 'ordersFiltersStatus_callback');



function ordersFiltersStatus_callback(){
    
    $userID = (int)$_POST["userID"];
    $status = $_POST["status"];
    
    $status = explode(",",$status);
    
    foreach($status as $key => $value){
        $status[$key] = (int)$value;
    }
    
    if( $userID !== 0 ){
        
        $filters['orderStatus'] = $status;
        $orders = get_user_orders( $userID, $filters );
        
        //print_r($orders);
        
        if( !empty($orders) ){
            
            create_user_ordersTable($orders);
            
        }else{
            
            echo "Нет подходящих постов";
            print_r($status);
        }
        
    }else{
        
        echo "Некорректные данные запроса";
        
    }    
    
    wp_die();
}