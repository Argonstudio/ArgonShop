<?php


add_action('wp_footer', 'ajaxHistory', 99); 
function ajaxHistory() {
	?>
	
	<script>
	
	
	    function addCookieHistory(productID){
		    
		    var data = {
    			action: 'addCookieHistory_history',
    			productID: productID
    		};
    		
		    $.ajax({
                url: myajax.url,
                data: data,
                type: 'POST',
                success: function(response) {
                  
                  console.log(response);
                  
                },
                error: function(data){
                    console.log(data);
                    //$(".totalPrice").children("span").text("Ошибка на сервере");
                }
              });
		    
    		
		}
		

	</script>
	<?php
}



add_action('wp_ajax_addCookieHistory_history', 'addCookieHistory_history_callback');
add_action('wp_ajax_nopriv_addCookieHistory_history', 'addCookieHistory_history_callback');

function addCookieHistory_history_callback(){    
    
	$productID =  (int)$_POST['productID'];    
	
	$ASHistory = Array();
	
	if(getCookie("AS_History")){
	    
	   $ASHistory = getCookie("AS_History");
	    
	   foreach($ASHistory as $key => $value){
    	   if($value == $productID){
    	       unset($ASHistory[$key]);
    	   }
    	}
	    
	}
	
	array_unshift($ASHistory, $productID);
	
	$ASHistory = wp_json_encode($ASHistory);
	
    setcookie( "AS_History", $ASHistory, time()+1209600, COOKIEPATH, COOKIE_DOMAIN);
      
    wp_die();
}