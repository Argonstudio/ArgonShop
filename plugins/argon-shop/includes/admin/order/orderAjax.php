<?php

add_action('admin_print_footer_scripts', 'asAjaxOrderProducts', 99); // для фронта
function asAjaxOrderProducts() {
	?>
	
	<script>
	
	
	    function searchSuitableProduct(searchStr, blockResult, addedProduct){
		    
		    //console.log(searchStr);
		    //console.log(blockResult);
		    //console.log(blockError);
		    
		    var data = {
    			action: 'as_searchSuitableProduct_order',
    			searchStr: searchStr,
    			addedProduct: addedProduct
    		};
    		
		    $.ajax({
                url: ajaxurl,
                data: data,
                type: 'POST',
                success: function(response) {
                  
                  blockResult.css("display","block");
                  blockResult.html(response);
                  
                },
                error: function(data){
                    console.log(data);
                    blockResult.css("display","block");
                    blockResult.html("Ошибка на сервере");
                }
              });
		    
    		
		}
		
		function addProduct(product){
		    
		    var productID = $(product).attr("data-productid");
		    var blockProducts = $(".orderAllProducts");
            var blockError = jQuery(".orderError");
		    
		    
		    var data = {
    			action: 'as_addProduct_order',
    			productID: productID
    		};
    		
		    $.ajax({
                url: ajaxurl,
                data: data,
                type: 'POST',
                success: function(response) {
                  
                  $(product).parent(".as-order-foundProduct").remove();
                  
                  blockProducts.append(response);
                  
                },
                error: function(data){
                    console.log(data);
                    blockError.html("Ошибка на сервере");
                }
              });
		    
		}
		
		function addFields(type, fieldsInStock, blockFields){
		    
		    var data = {
    			action: 'as_addFields_order',
    			type: type,
    			fieldsInStock: fieldsInStock
    		};
    		
		    $.ajax({
                url: ajaxurl,
                data: data,
                type: 'POST',
                success: function(response) {
                  
                  //$(product).parent(".as-order-foundProduct").remove();
                  
                  blockFields.append(response);
                  
                },
                error: function(data){
                    console.log(data);
                    $(".as-order-addFieldsError").html("Ошибка на сервере");
                }
              });
	        
	    }

	</script>
	<?php
}



add_action('wp_ajax_as_searchSuitableProduct_order', 'as_searchSuitableProduct_order_callback');
add_action('wp_ajax_nopriv_as_searchSuitableProduct_order', 'as_searchSuitableProduct_order_callback');

add_action('wp_ajax_as_addProduct_order', 'as_addProduct_order_callback');
add_action('wp_ajax_nopriv_as_addProduct_order', 'as_addProduct_order_callback');

add_action('wp_ajax_as_addFields_order', 'as_addFields_order_callback');
add_action('wp_ajax_nopriv_as_addFields_order', 'as_addFields_order_callback');

function as_addFields_order_callback(){
    
    $type = $_POST["type"];
    $fieldsInStock = $_POST["fieldsInStock"];
    
    $settingShop = get_option('settingShop');
    $userFildsSetting = $settingShop[$type];
    
    $userFildsSetting["emailUser"] = Array("name" => "Email");
    $userFildsSetting["messageUser"] = Array("name" => "Сообщение");
    
    foreach($userFildsSetting as $keyField => $valueArrayField){
        
        if( array_search($keyField, $fieldsInStock) === false ){
            
            if($keyField === "messageUser"){
                
                as_getHTMLField("textarea", $keyField, $valueArrayField);
                
            }else{
                
                as_getHTMLField("input", $keyField, $valueArrayField);
                
            }
            
        }
        
    }
    
    wp_die();
    
}

function as_addProduct_order_callback(){
    
    $productID = $_POST["productID"];
    
    createTableProduct($productID);
    
    wp_die();
}

function as_searchSuitableProduct_order_callback(){
    
    $searchStr = $_POST["searchStr"];
    $addedProduct = $_POST["addedProduct"];
    
    $searchParameters = (object)[];
    $searchParameters->postResult = true;
    
    //Обращение к функциям поиска searchAjax.php
    $searchResult = queryManagerSearchResult($searchStr, $searchParameters);
    
    if( empty($searchResult) && !ctype_digit($searchStr) ){
            
        $searchStrFix = fixKeyboardlayout($searchStr);
             
        if($searchStrFix != $searchStr){
                 
            $searchResult = queryManagerSearchResult($searchStrFix, $searchParameters);
                 
        }
             
    }
    
    $listResult;
    
    if( !empty($searchResult) ){
        
        $listResult = $searchResult["product"]["listResult"];
        
        foreach($listResult as $key => $value){
            
            if( array_search($key, $addedProduct) !== false ){
                
                unset($listResult[$key]);
            }
            
        }
    }
        
    if( !empty($listResult) ){
             
        as_order_showSearchResult($listResult);
        //print_r($searchResult);
        
    }else{
             
        echo "Нет подходящих результатов";
             
    }
    
    wp_die();
    
}

function as_order_showSearchResult($searchResult, $addedProduct){
    
    ?>
    <ul>
    <?php
    
    foreach($searchResult as $key => $value){
        
        //if( array_search($key, $addedProduct) === false ){
            
            //print_r($addedProduct)."<p></p>";
            //echo array_search($key, $addedProduct)."<p></p>";
            //echo $key."<p></p>";
        
        ?>
        
        <li class="as-order-foundProduct"> 
        
            <a href="<?=$value['post_url'] ?>" target="_blank"><?=$value["post_title"] ?></a>
            <input type="button" data-productid="<?=$key ?>" value="" onclick="addProduct(this)" class="as-addProduct">
        
        </li>
        
        <?php
        //}
    }
    
    ?>
    </ul>
    <?php
    
}