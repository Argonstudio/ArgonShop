<?php

add_action('wp_footer', 'shoppingCart', 99); // для фронта
function shoppingCart() {
	?>
	
	<script>
	
	
	    function getWholesalePrice(){
		    
		    var data = {
    			action: 'wholesalePrice_shoppingCart',
    			type: "get",
    			productID: $("#addShoppingCart").attr("data-productid"),
    			amountProduct: $("#amountProduct").val()
    		};
    		
		    $.ajax({
                url: myajax.url,
                data: data,
                type: 'POST',
                success: function(response) {
                    
                  if( $("#amountProduct").val() ){
                      
                        $(".totalPrice").css("display","block");
                        
                        var price = $("#basePrice").html();
                        price = price.replace(",",".");
                        price = parseFloat(price).toFixed(2);
                        
                        var totalPrice = $("#amountProduct").val() * price;
                        
                        if( !Number.isInteger(totalPrice) ){
                            totalPrice = totalPrice.toFixed(2)
                        }
                        
                        if(response != "none"){
        		            totalPrice = response;
        		        }
        		        
                        $(".totalPrice").children("span").text(totalPrice);
                    }else{
                        $(".totalPrice").css("display","none");
                    }
                  
                },
                error: function(data){
                    console.log(data);
                    $(".totalPrice").children("span").text("Ошибка на сервере");
                }
              });
		    
    		
		}
		
		function addProducts(productID,amountProduct,type,thisButton){
		    
		    var data = {
    			action: 'addProducts_shoppingCart',
    			productID: productID,
    			amountProduct: amountProduct
    		};
    		
		    $.ajax({
                url: myajax.url,
                data: data,
                type: 'POST',
                
                success: function(response) {
                  
                  var response = JSON.parse(response); 
                  
                  if(type==="cardBuy" || type==="productBuy"){
                      location.href = "<?=get_cartPageURL(); ?>";
                      return;
                  }
                  
                  //Добавление на странице товара(без перенаправления в корзину)
                  if($("#howManyProducts").length > 0 && $("#addShoppingCart").attr("data-productid") === productID){
                      
                      $("#howManyProducts").attr("style","display:block");
                      
                      $("#addProductError").css("display","none");
                      $("#addProductError").html("");
                      
		              $("#howManyProducts").find("span").html(response["amountProduct"]);
		              
                  }
                  
                  //Добавление товара из карточки товара(без перенаправления в корзину)
                  if(type==="cardInBascet"){
                      
                      thisButton.parents(".block-cardProductBascet").css("display","none");
                      
                      thisButton.parents(".block-cardProductBascet").siblings(".card-addProductError").css("display","none");
                      thisButton.parents(".block-cardProductBascet").siblings(".card-addProductError").html()
                      
                      thisButton.parents(".block-cardProductBascet").siblings(".card-alreadyAdded").css("display","block");
                      
                  }
		          
		          //Виджет корзины, число товаров
		          if(response["productAmountCart"]){
		              $(".viewBlock-amountProducts").children("span").addClass("viewBlock-productsStock");
		              $(".viewBlock-amountProducts").children("span").html(response["productAmountCart"]);
		          } 
                  
                  //Виджет корзины, цена
                  if(response["totalPrice"]){
                      
                      $(".viewBlock-priceProducts").css("display","block");
                      $(".viewBlock-priceProducts").children("span").html(response["totalPrice"]);
                      
                  }else{
                      $(".viewBlock-priceProducts").css("display","none");
                  } 
                },
                
                error: function(data){
                    console.log(data);
                    
                    if(type==="cardInBascet" || type==="cardBuy"){
                      
                      thisButton.parents(".block-cardProductBascet").siblings(".card-addProductError").css("display","block")  
                      thisButton.parents(".block-cardProductBascet").siblings(".card-addProductError").html("Ошибка при выполнении запроса");
                      
                    }else{
                        
                        if($("#howManyProducts").length > 0 && $("#addShoppingCart").attr("data-productid") === productID){
                            $("#addProductError").css("display","block");
                            $("#addProductError").html("Ошибка при выполнении запроса");
                        }
                        
                    }
                    
                }
              });
		    
    		
		}
		
		//Обновление значений в корзине при удалении или редактировании товара
		function cartAmountProducts(actualPrices){
		    
		    var totalPrice = 0;
		    var totalWeight = 0;
		    
		    $.each(actualPrices, function(key,value){
		        
		        var parentBlock = $(".blockCartProduct[data-productid='"+ key +"' ]");
		        
		        var productPriceBlock = parentBlock.find(".productPrice").children("span");
                var productAmountPriceBlock = parentBlock.find(".amountProductPrice").children("span");
                
                var productAmount = parentBlock.find(".shoppingCartAmountProduct").val();
                var productAmountWeight = +parentBlock.find(".amountProductWeight").first().children("span").html();
                
                productPriceBlock.html(value);
                
                //Получаем общую цену одного товара в корзине
                var productAmountPrice = value*productAmount;
                
                //Проверяем на int    
                if( !Number.isInteger(productAmountPrice) ){
                    //До 2-х знаков после запятой
                    productAmountPrice = productAmountPrice.toFixed(2)
                }
                
                productAmountPriceBlock.html( productAmountPrice );
                
                
                if(productAmountWeight) totalWeight += productAmountWeight;
		        totalPrice += value*productAmount;
		        
		    })
		    
		    if(totalWeight) {
		        
                if( !Number.isInteger(totalWeight) ){
                    totalWeight = totalWeight.toFixed(2)
                }
		        
		        $(".cartTotalWeight").children("span").html(totalWeight);
		        $(".cartTotalWeight").css("display","block");
		    }else{
		        $(".cartTotalWeight").css("display","none");
		    } 
		    
		    if( !Number.isInteger(totalPrice) ){
                totalPrice = totalPrice.toFixed(2)
            }
		    
		    $(".cartTotalPrice").children("span").html( totalPrice );
		    
		}
		
		//Изменение числа товаров в корзине
		function cartAmountProductsAjax(productID,productAmount,productPrice,productAmountWeightBlock,productAmountPriceBlock){
		    
		    var data = {
    			action: 'amountProducts_shoppingCart',
    			productAmount: productAmount,
    			productID: productID,
    			productPrice: productPrice
    		};
    		
    		$.ajax({
                url: myajax.url,
                data: data,
                type: 'POST',
                success: function(response) {
                  
                    var response = JSON.parse(response); 
                    
                    var productWeight = response["weight"]*productAmount;
                    
                    if( !Number.isInteger(productWeight) ){
                        productWeight = productWeight.toFixed(2)
                    }
                    
                    
    		        productAmountWeightBlock.html( productWeight );
    		         
    				cartAmountProducts( response["actualPrices"] ); 
    				
    				var parentBlock = $(".blockCartProduct[data-productid='"+ productID +"' ]");
    				parentBlock.find(".shoppingCartError").css("display","none");
                    parentBlock.find(".shoppingCartError").children("span").html("");
                  
                },
                error: function(data){
                    console.log(data);
                    var parentBlock = $(".blockCartProduct[data-productid='"+ productID +"' ]");
                    parentBlock.find(".shoppingCartError").css("display","block");
                    parentBlock.find(".shoppingCartError").children("span").html("Ошибка на сервере, изменения не приняты");
                }
              });
    		
		    
		}
		
		function deleteAllProductsShoppingCart(message){
		    
		    var data = {
    			action: 'deleteAllProducts_shoppingCart'
    		};
    		
    		$.ajax({
                url: myajax.url,
                data: data,
                type: 'POST',
                success: function(response) {
                  
                  if(!message){
        		      message = "Корзина пуста";  
        		    }
                  
                    $("#shoppingCart").html(message);
    				$(".errorDeleteProducts").css("display","none");
                  
                },
                error: function(data){
                    console.log(data);
                    $(".errorDeleteProducts").html("Ошибка на сервере, товары не удалены");
                    $(".errorDeleteProducts").css("display","block");
                }
              });
		    
		}
		
		function deleteProducts(productID,productParentBlock,productWeight,productPrice){
		    
		    var data = {
    			action: 'deleteProducts_shoppingCart',
    			productID: productID
    		};
    		
		    $.ajax({
                url: myajax.url,
                data: data,
                type: 'POST',
                success: function(response) {
                      
                    if(response != 0){
		            
    		            var response = JSON.parse(response); 
    		            
    		            productParentBlock.remove();
        		        
        		        cartAmountProducts( response ); 
    		            
    		            var parentBlock = $(".blockCartProduct[data-productid='"+ productID +"' ]");
                        parentBlock.find(".shoppingCartError").css("display","none");
    		            
    		        }else{
    		            
    		            $("#shoppingCart").html("Корзина пуста");
    		        }
    				
                  
                },
                error: function(data){
                    console.log(data);
                    var parentBlock = $(".blockCartProduct[data-productid='"+ productID +"' ]");
                    parentBlock.find(".shoppingCartError").css("display","block");
                    parentBlock.find(".shoppingCartError").children("span").html("Ошибка на сервере, товар не удален");
                }
              });
		    
		    
    		
		}
	
	function submitCart(dataForm){
	    
	    
	    dataForm.append("action","submitCart_shoppingCart");
    		
		$.ajax({
                url: myajax.url,
                data: dataForm,
                processData: false,
                contentType: false,
                type: 'POST',
                dataType: 'JSON',
                success: function(response) {
                    
                    //var response = JSON.parse(response);
                    var form = $(".form-cart"+response["typeForm"]);
                    
                    if(response["type"] == "error"){
                        
                        form.find(".submitError").css("display","block");
                        form.find(".submitError").children("span").html(response["message"]);
                        
                    }else if(response["type"] == "success"){
                        
                        form.find(".submitError").css("display","none");
                        deleteAllProductsShoppingCart(response["message"])
                        
                    }
                    
                },
                error: function(data){                    
                    
                    var form = $(".form-cart");
                    
                    form.find(".submitError").css("display","block");
                    form.find(".submitError").children("span").html("Ошибка на сервере: заказ не отправлен");
                    
                    console.log("Ошибка отправки формы");
                    console.log(data);
                }
              });    
	    
	}

	</script>
	<?php
}

add_action('wp_ajax_wholesalePrice_shoppingCart', 'wholesalePrice_shoppingCart_callback');
add_action('wp_ajax_nopriv_wholesalePrice_shoppingCart', 'wholesalePrice_shoppingCart_callback');

add_action('wp_ajax_addProducts_shoppingCart', 'addProducts_shoppingCart_callback');
add_action('wp_ajax_nopriv_addProducts_shoppingCart', 'addProducts_shoppingCart_callback');

add_action('wp_ajax_amountProducts_shoppingCart', 'amountProducts_shoppingCart_callback');
add_action('wp_ajax_nopriv_amountProducts_shoppingCart', 'amountProducts_shoppingCart_callback');

add_action('wp_ajax_deleteProducts_shoppingCart', 'deleteProducts_shoppingCart_callback');
add_action('wp_ajax_nopriv_deleteProducts_shoppingCart', 'deleteProducts_shoppingCart_callback');

add_action('wp_ajax_deleteAllProducts_shoppingCart', 'deleteAllProducts_shoppingCart_callback');
add_action('wp_ajax_nopriv_deleteAllProducts_shoppingCart', 'deleteAllProducts_shoppingCart_callback');

add_action('wp_ajax_submitCart_shoppingCart', 'submitCart_shoppingCart_callback');
add_action('wp_ajax_nopriv_submitCart_shoppingCart', 'submitCart_shoppingCart_callback');


function submitCart_shoppingCart_callback(){
    
    global $user_ID;
    
    $typeForm = wp_strip_all_tags( $_POST["typeForm"] );
    
    $counterOrders = get_option('counterOrders');
    
    if(!$counterOrders){
        $counterOrders = 0;
    }
    
    $fieldsArrays = get_valueFieldsCart($typeForm,$_POST);
    
    $fieldsCart = $fieldsArrays["fieldsCart"];    
    
    //Удаляем теги из строки
    $email = wp_strip_all_tags( $_POST["emailUser"] );
    
    //Получаем данные о товарах в корзине(страница)
    $productsCart = stripslashes($_POST["productsCart"]);
    $productsCart = json_decode( $productsCart, true );
    
    //Получаем товары в корзине из кук
    $productsCartCookie = getCookie("productsShoppingCart");
    
    $shippingType = wp_strip_all_tags($_POST["shipping"]);
    
    $shipping;
    
    switch($shippingType){
        case "selfExport":
            $shipping = "Самовывоз";
            break;
        case "shippingToAddress":
            $shipping = "Доставка по адресу";
            break;
    }
    
    $paymentType = wp_strip_all_tags($_POST["payment"]);
    $payment;
    
    switch($paymentType){
        case "paimentUponReceipt":
            $payment = "Оплата при получении";
            break;
    }
    
    $to = get_option('admin_email');
    $from = $email;
    
    $subject = "Получен заказ";
    $subjectUser = "Ваш заказ успешно добавлен";
    
    $result = Array(
        
            "type"=>"error",
            "typeForm"=>$typeForm
        
        );   
            
   
    $clearCartCookie = Array();
    
    foreach($productsCartCookie as $key => $value){
        
        if(!$productsCart[$key] || $value["amountProduct"] != $productsCart[$key]["amountProduct"]){
            
            $result["message"] .= "Ошибка: Число или наименования товаров в корзине и на странице не совпадают, попробуйте изменить число любого из товаров или обновите страницу(возможна потеря данных введенных в форму)";
            
            echo json_encode($result);
            wp_die();
            
        }
        
        $clearCartCookie[wp_strip_all_tags($key)] = Array();
        
        foreach($value as $nameDataProduct => $valueDataProduct){
            
            $clearCartCookie[$key][wp_strip_all_tags($nameDataProduct)] = wp_strip_all_tags($valueDataProduct);
            
        }
        
    }
    
    $productsCart = $clearCartCookie;
    $actualPriceProducts = getActualPriceAllProducts($productsCart);
    
    $totalPriseAndAmount = getDataCart($productsCart);
    
    $totalPrice = $totalPriseAndAmount["totalPrice"];
    $productAmountCart = $totalPriseAndAmount["productAmountCart"];
    
    
    foreach($productsCart as $key => $value){
        
        $post_data = get_post( $key );
        
        $post_name = $post_data->post_title;
        $post_link = get_post_permalink($key);
        $post_actualPrice = $actualPriceProducts[$key];
        
        $productsCart[$key]["name"] = $post_name;
        $productsCart[$key]["link"] = $post_link;
        $productsCart[$key]["price"] = $post_actualPrice;
        
        
    }
    
    /*
    ** Настройка email
    */
    
    $boundary = md5(date('r', time()));
    $filesize = '';
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "From: " . $from . "\r\n";
    $headers .= "Reply-To: " . $from . "\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";
    
    $headersUser = "MIME-Version: 1.0\r\n";
    $headersUser .= "From: " . $to . "\r\n";
    $headersUser .= "Reply-To: " . $to . "\r\n";
    $headersUser .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";
    
     
    /*
    ** Обработка файлов
    */
    
    
    $allowed_filetypes = array('.doc', '.docx', '.xls', '.xlsx','.jpg','.jpeg','.gif','.bmp','.png','.pdf'); // Здесь мы перечисляем допустимые типы файлов
  
  $messageFile;
  $messageForUserFile;
  
  for($i=0;$i<count($_FILES['fileCart']['name']);$i++) {
     if(is_uploaded_file($_FILES['fileCart']['tmp_name'][$i])) {
         $attachment = chunk_split(base64_encode(file_get_contents($_FILES['fileCart']['tmp_name'][$i])));
         $filename = $_FILES['fileCart']['name'][$i];
         $filetype = $_FILES['fileCart']['type'][$i];
         $filesize += $_FILES['fileCart']['size'][$i];
         
         $messageFile.="
--$boundary
Content-Type: \"$filetype\"; name=\"$filename\"
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename=\"$filename\"

$attachment";

         $messageForUserFile.="

--$boundary
Content-Type: \"$filetype\"; name=\"$filename\"
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename=\"$filename\"

$attachment";

    $ext = substr($filename, strrpos($filename,'.'), strlen($filename)-1); // В переменную $ext заносим расширение загруженного файла.


    if(!in_array($ext,$allowed_filetypes)) {
        
        $result["message"] = 'Допустимые форматы файлов: '.implode( (", ") , $allowed_filetypes);
        
        echo json_encode($result);
        wp_die();
        
    }

    

     }
   }
   
    
    if($filesize >= 15000000){ 
        
        $result["message"] = 'Общий размер файлов не должен превышать 15 МБ.';
        
        echo json_encode($result);
        wp_die();
        
    }
    
    /*
    ** Регистрация пользователя
    */
    
    $messageForUser;
    
    $cartReg = wp_strip_all_tags( $_POST["cartReg"] );
    
    if($cartReg){
        
        $user_name = wp_strip_all_tags( $_POST["loginUser"] );
        $random_password = wp_generate_password( 12 );
        
        $user_ID = wp_create_user( $user_name, $random_password, $email );

        if ( is_wp_error( $user_ID ) ) {
            
            $result["message"] .= "Ошибка при регистрации нового пользователя: ".$user_ID->get_error_message();
            echo json_encode($result);
            wp_die();
            
        }
        else {
            
            $messageForUser .= "<b style='font-size:18px;color:#808080;'>Вы успешно зарегистрированы:</b>"."<p></p>";
            $messageForUser .= "<p></p><b>Логин: </b> ".$user_name."<br />";
            $messageForUser .= "<b>Пароль: </b>".$random_password."<p></p>";
            
            
            $nameUser = wp_strip_all_tags($_POST["nameUser"]);
        
            if($nameUser){
                $result["message"] .=  $nameUser.', ';
            }
            
        	$result["message"] .= "Вы успешно зарегистрированы, пароль отправлен на email: ".$email."<p></p>";
        	
        	add_user_meta($user_ID, "accountData", $fieldsArrays["accountData"]);
        	
        	$creds['user_login'] = $user_name;
		    $creds['user_password'] = $random_password;
		    $creds['remember'] = false;
		    
		    $userSignon = wp_signon( $creds, false );
        	
        	if ( !is_wp_error($userSignon) ) {
               $result["message"] .= "Произведен вход на сайт, ";
               
               if( get_cabinetPageURL() ){
                   $result["message"] .= "Вы можете <a href='".get_cabinetPageURL()."'>перейти в личный кабинет</a> для редактирования личных данных и отслеживания заказов<p></p>";
               }
               
            }
        	
        }
    }
    
    /*
    ** Добавление заказа в базу данных
    */
    
    // Создаем массив данных новой записи
    $post_data = array(
    	'post_title'    => "Заказ №".($counterOrders+1)." Товаров в заказе: ".$productAmountCart.", на сумму: ".$totalPrice,
    	'post_type'     => 'shoporder',
    	'post_status'   => 'publish',
    	'post_author'   => $user_ID,
    	'tax_input'      => array( 'statusorders' => array( 'neworder' ) )
    );
    
    // Вставляем запись в базу данных
    $post_id = wp_insert_post( $post_data );
    
    
    
    if( is_wp_error($post_id) ){
        
    	$result["message"] .= "Ошибка при записи заказа в базу данных: ".$post_id->get_error_message();
        echo json_encode($result);
        wp_die();
    }    
    
    add_post_meta( $post_id, '_productsCart', wp_slash( $productsCart ) );
    add_post_meta( $post_id, '_fieldsCart', wp_slash( $fieldsCart ) );
    
    add_post_meta( $post_id, '_number', $counterOrders+1 );
    add_post_meta( $post_id, '_productAmountCart', $productAmountCart );
    add_post_meta( $post_id, '_totalPrice', $totalPrice );
    
    add_post_meta( $post_id, '_shipping', wp_slash( $shippingType ) );
    add_post_meta( $post_id, '_payment', wp_slash( $paymentType ) );
    
    wp_set_object_terms( $post_id, 'neworder', 'statusorders' );
    
    /*
    ** Создание текстовых сообщений
    */
        
    $messageForUser .= "<b style='font-size:18px;color:#808080;'>Детали заказа:</b>"."<p></p>";
    $messageForUser .= "<p></p><b>Номер вашего заказа: </b> ".($counterOrders+1)."<br />";
    $messageForUser .= "<p></p><b>Наименований товаров: </b> ".$productAmountCart."<br />";
    $messageForUser .= "<b>На сумму: </b>".$totalPrice."<p></p>";
    
    $message .= "<b style='font-size:18px;color:#808080;'>Детали заказа:</b>"."<p></p>";
    $message .= "<p></p><b>Номер заказа: </b> ".($counterOrders+1)."<br />";
    $message .= "<p></p><b>Наименований товаров: </b> ".$productAmountCart."<br />";
    $message .= "<b>На сумму: </b>".$totalPrice."<p></p>";
    
    if($shipping){
        
        $messageForUser .= "<p></p><b>Способ доставки: </b> ".$shipping."<br />";
        $message .= "<p></p><b>Способ доставки: </b> ".$shipping."<br />";
    }
    
    if($payment){
        
        $messageForUser .= "<b>Способ оплаты: </b>".$payment."<p></p>";
        $message .= "<b>Способ оплаты: </b>".$payment."<p></p>";
    }
    
    $messageForUser .= "<p></p><b style='font-size:18px;color:#808080;'>Перечень товаров: </b> <p></p>"; 
    $message .= "<p></p><b style='font-size:18px;color:#808080;'>Перечень товаров: </b> <p></p>";
    
    foreach($productsCart as $key => $value){
        
        $messageForUser .= "<p></p><b>Название:</b> <a href='".$value['link']."'>".$value['name']."</a><p></p>";
        
        $messageForUser .= "Заказано: ".$value['amountProduct']." шт<br />";
        $messageForUser .= "Цена за единицу: ".$value['price']." ₽/шт<br />";
        $messageForUser .= "Итого: ".$value['price']*$value['amountProduct']." ₽<p></p>";
        
        $message .= "<p></p><b>Название:</b> <a href='".$value['link']."'>".$value['name']."</a><p></p>";
        
        $productArticle = get_post_meta($key, '_article',true);
        
        if($productArticle) $message .= "Артикул: ".$productArticle." <p></p>";
        
        $message .= "Заказано: ".$value['amountProduct']." шт<br />";
        $message .= "Цена за единицу: ".$value['price']." ₽/шт<br />";
        $message .= "Итого: ".$value['price']*$value['amountProduct']." ₽<p></p>";
    }
    
    $messageForUser .= "<b style='font-size:18px;color:#808080'>Оставленная Вами информация:</b>"."<p></p>";
    $message .= "<b style='font-size:18px;color:#808080'>Информация от заказчика:</b>"."<p></p>";
    
    foreach($fieldsCart as $key => $value){
        
        if($value['value']){
            
            $messageForUser .= "<p></p>".$value['name'].": ".$value['value']."<p></p>";
            $message .= "<p></p>".$value['name'].": ".$value['value']."<p></p>";
        }
        
    }
    
    $messageForUser .= "<p></p>Благодарим за ваш заказ! Наш менеджер свяжется с Вами в ближайшее время.<p></p>";
    $messageForUser .= "<p></p>Для изменения деталей заказа свяжитесь с менеджером магазина по телефону указанному на сайте.";
    
    
     /*
    ** Подготовка к отправке
    */
    
    $message="Content-Type: multipart/mixed; boundary=\"$boundary\"

--$boundary
Content-Type: text/html; charset=\"utf-8\"
Content-Transfer-Encoding: 7bit

$message";


$messageForUser="Content-Type: multipart/mixed; boundary=\"$boundary\"

--$boundary
Content-Type: text/html; charset=\"utf-8\"
Content-Transfer-Encoding: 7bit

$messageForUser";
    
    $message .= $messageFile;
    $messageForUser .= $messageForUserFile;
    
    //в качестве резделителя частей используется --boundary а в конце --boundary-- 
    $message.="
--$boundary--";

   $messageForUser.="
--$boundary--";
    
    
    /*
    ** Отправка сообщений
    */
   
      
    mail($to, $subject, $message, $headers);
    mail($from, $subjectUser, $messageForUser, $headersUser);
    
    
    $result["type"] = "success";
    $result["message"] .=  'Благодарим за ваш заказ! Для изменения и отслеживания заказа запомните его номер: <b>'.($counterOrders+1).'</b><p></p> Наш менеджер свяжется с Вами в ближайшее время';
    
    update_option("counterOrders",$counterOrders+1);
    
    echo json_encode($result);
    
    
    wp_die();
}

function get_valueFieldsCart($typeForm,$POST){
    
    $settingShop = get_option('settingShop');
    
    $cartReg = $POST["cartReg"];
    
    $required = stripslashes($_POST["required"]);
    $required = json_decode( $required, true );
    
    $requredClean = Array();
    
    foreach($required as $key => $value){
        $requredClean[wp_strip_all_tags($key)] = $value;
    }
    
    $required = $requredClean;
    
    
    $fieldsCart = Array();
    $accountData = Array();
      
    /**
     * Внутренние функции для заполнения массивов с данными формы
     * $fieldsCart отправка на почту
     * $accountData данные пользователя записываемые в базу при регистрации
    */
      
    
    //Передаем $get_valueByType параметры из внешней функции, fieldsCart ссылкой для возможности его изменения
    $get_valueByType = function($typeForm) use ($settingShop, $POST, &$required, &$accountData, &$fieldsCart){
        
        foreach($settingShop[$typeForm] as $key => $value){
                
                $valueField = sanitize_text_field( stripslashes($POST[$key]) );
                
                if($POST["cartReg"]){
                    
                    $typeData;
                    
                    if($typeForm == "person"){
                        
                        $typeData = "accountDetail";
                        
                    }else if($typeForm == "legalPerson"){
                        
                        $typeData = "accountLegalDetail";
                    }
                    
                    if( !$accountData[$typeData][$key] ){
                        $accountData[$typeData][$key] = $valueField;
                    }
                    
                    
                    
                }
                
                
                if($valueField){
                    
                    $fieldsCart[$key] = Array(
                    
                        "name" => $value["name"],
                        "value" => $valueField
                    
                    );
                    
                }elseif($required[$key] === true){
                    
                   $required[$key] = $value["name"];
                   
                    
                }
                
                
            }
        
    };
    
    $get_valueByFieldname = function($fieldName,$nameInArray) use ($POST, &$required, &$fieldsCart){
        
        $fieldsCart[$fieldName] = Array(
            
            "name" => $nameInArray,
            "value" => sanitize_text_field( stripslashes($POST[$fieldName]) )
            
            );
        
        //Заполняем имя для email(и других отдельных от $settingShop полей) на случай вывода ошибки о незаполненности поля    
        if($required[$fieldName] === true){
            $required[$fieldName] = $nameInArray;
        }
    };
    
    
    /**
     * Формируем массив с данными для отправки на почту в зависимости от типа формы
    */
    
    if($typeForm !== "legalPerson"){
        
        $get_valueByType($typeForm);
        
        
        if($typeForm !== "quick"){
            
            $get_valueByFieldname("emailUser","Email");
            
        }
        
    }else{
        
        $get_valueByType("person");
        
        $get_valueByFieldname("emailUser","Email");
        
        $get_valueByType($typeForm);
        
    }
    
    $get_valueByFieldname("messageUser","Сообщение");
    
    
    /**
     * Перепроверка заполненности полей(на случай не обработки required браузером пользователя)
    */
    
    //Отдельно отбрабатываем логин
    //Причина: не заполненные поля находим путем сравнения с массивом содержащим введенные в форму данные(которые отправляются на почту)
    //Логин не входит в этот массив
    if($POST["cartReg"]){
        
        if(!$POST["loginUser"]){
            $required["loginUser"] = "Логин";
        }else{
            unset($required["loginUser"]);
        }
        
    } 
        
    $blankRequired = array_diff_key($required,$fieldsCart);
    
    if($blankRequired){
        
        $result = Array(
        
            "type" => "error",
            "typeForm" => $typeForm,
            "message" => "Заполните поля: "
        
        );
        
        foreach($blankRequired as $key => $value){
            $result["message"] .= $value.", ";
        }
        
        $result["message"] = rtrim($result["message"], ", ");

        
        echo json_encode($result);
        wp_die();
    }   
    
    /**
     * Проверка правильности заполнения   */
    
    if($fieldsCart["emailUser"]["value"]){
        
        $user_mail = $fieldsCart["emailUser"]["value"];
        $correctEmail = filter_var($user_mail, FILTER_VALIDATE_EMAIL);
        
        if(!$correctEmail){
            
            $result = Array(
            
                "type" => "error",
                "typeForm" => $typeForm,
                "message" => "Email введен не верно"
            
            );
            
            echo json_encode($result);
            wp_die();
            
        }
    }
    
    if($fieldsCart["telefonUser"]["value"]){
        
        $user_phone = $fieldsCart["telefonUser"]["value"];
        $countDigitsTelefon = countDigits( $user_telefon );
        
        $result = Array(
            
                "type" => "error",
                "typeForm" => $typeForm,
            
            );
        
        if(countDigits( $user_phone ) < 11){
            
            $result["message"] = "Телефон содержит менее 11 символов";
            
            echo json_encode($result);
            wp_die();
            
        }
        
    }    
    
    
    /**
     * Дополнительная обработка, сборка ответа и его отправка функции вызова
    */
    
    foreach($fieldsCart as $fieldsType => $fieldsTypeValue){
        
        foreach( $fieldsCart[$fieldsType] as $key => $value ){
            
            //Меняем кавычки и другие символы
            $fieldsCart[$fieldsType][$key] = wptexturize($value);
            
        }
    }    
    
    $fieldsArrays = Array(
           "fieldsCart" => $fieldsCart,
        
        );
    
    
    
    if($cartReg){
        
        foreach($accountData as $fieldsType => $fieldsTypeValue){
        
            foreach( $accountData[$fieldsType] as $key => $value ){
                
                //Меняем кавычки и другие символы
                $accountData[$fieldsType][$key] = wptexturize($value);
                
            }
        }
        
        $fieldsArrays["accountData"] = $accountData;
        
    }
      
            
    return $fieldsArrays;        
}



function wholesalePrice_shoppingCart_callback(){
    
    $type = $_POST['type'];
    $productID =  (int)$_POST['productID'];
    $productAmount =  (double)$_POST['amountProduct'];
    
    $productDiscount = getDiscount($productID);
    
    if( $type == "get" && !$productDiscount ){
        echo "none";
        wp_die();
    }
    
    $productsShoppingCart = getCookie("productsShoppingCart");
    
    $productPrice = get_post_meta($productID, '_price',true);
    
    $productPrice = str_replace(",",".",$productPrice);
    
    $productPrice = (float)$productPrice;
    
    $productPrice = getActualPrice("productPage", $productsShoppingCart, $productID, $productPrice, $productAmount);
    
    $totalPriceProduct = $productPrice * $productAmount;
    
    echo $totalPriceProduct;
    
    wp_die();
}

function amountProducts_shoppingCart_callback(){
    
    $productID =  (int)$_POST['productID'];
    
    $productAmount =  (int)$_POST['productAmount'];    
    
    $productsShoppingCart = getCookie("productsShoppingCart");
        
    $productsShoppingCart[$productID]["amountProduct"] = $productAmount;
    
    $productsShoppingCartJson = wp_json_encode($productsShoppingCart);
    
    setcookie( "productsShoppingCart", $productsShoppingCartJson, time()+1209600, COOKIEPATH, COOKIE_DOMAIN);
    
    $weight = get_post_meta($productID, '_weight', true);
    
    $weight = str_replace(",",".",$weight);
    $weight = (float)$weight;    
    
    $actualPriceAllProducts = getActualPriceAllProducts($productsShoppingCart);
    
    $forFront = [];
        
    if($weight) $forFront["weight"] = $weight;
    if($actualPriceAllProducts) $forFront["actualPrices"] = $actualPriceAllProducts;
    $forFront["productsShoppingCart"] = $productsShoppingCart;
    
    $forFront = wp_json_encode($forFront);
    
    echo $forFront;
    
    wp_die();
    
}

function deleteAllProducts_shoppingCart_callback(){
    setcookie( "productsShoppingCart", "", time()-1209600, COOKIEPATH, COOKIE_DOMAIN);
}

function deleteProducts_shoppingCart_callback(){
    
    $productID =  (int)$_POST['productID'];
    
    $productsShoppingCart = getCookie("productsShoppingCart");
    
    //Удаляем товар из кук
    unset($productsShoppingCart[$productID]);
    
    $productsShoppingCartJson = wp_json_encode($productsShoppingCart);
    
    setcookie( "productsShoppingCart", $productsShoppingCartJson, time()+1209600, COOKIEPATH, COOKIE_DOMAIN);
    
    
    
    //Возвращаем true если куки остались
    if(!$productsShoppingCart){
        echo false;
        wp_die();
    }
    
    $actualPriceAllProducts = getActualPriceAllProducts($productsShoppingCart);
        
    $actualPriceAllProducts = wp_json_encode($actualPriceAllProducts);
        
    echo $actualPriceAllProducts;
    
    wp_die();
    
}

function addProducts_shoppingCart_callback(){
    
    $productID =  (int)$_POST['productID'];
    
    $productPrice = get_post_meta($productID, '_price', true);
    
    $productPrice = str_replace(",",".",$productPrice);
    $productPrice = (float)$productPrice;
    
    $data = array(
        
        "amountProduct" => (int)$_POST['amountProduct'],
        "price" => $productPrice
        
        );
    
    $productsShoppingCart = [];
    
    if( getCookie("productsShoppingCart") ) {
        
        $productsShoppingCart = getCookie("productsShoppingCart");
        
    }
    
    if( $productsShoppingCart[$productID]){
        
        $data["amountProduct"] += $productsShoppingCart[$productID]["amountProduct"];
        
    }
    
    $productsShoppingCart[$productID] = $data;
    
    $productsShoppingCartJson = wp_json_encode($productsShoppingCart);
    
    setcookie( "productsShoppingCart", $productsShoppingCartJson, time()+1209600, COOKIEPATH, COOKIE_DOMAIN);
    
    //Получаем число и общую стоимость товаров в корзине с учетом скидки
    $dataCart = getDataCart($productsShoppingCart);
    
    $forFront = array(
        "amountProduct" => $data["amountProduct"]
        );
    
    //Передаем число и общую стоимость товаров в корзине на фронтенд
    if($dataCart){
        $forFront["productAmountCart"] = $dataCart["productAmountCart"];
        $forFront["totalPrice"] = $dataCart["totalPrice"];
    }
    
    $forFront = wp_json_encode($forFront);
    
    echo $forFront; 
    
    wp_die();
}


