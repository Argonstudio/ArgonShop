<?php

//Количество чисел 0-9 в строке
function countDigits( $str ){
    return preg_match_all( "/[0-9]/", $str );
}


//Проверка полей на заполнение(при сохранении в админке)
function cheak_detailed_fields($fields){
    
    $completeFields = [];
    
    foreach($fields as $key => $value){
        
        if( !empty($value) ){
            
            $completeFields[$key] = $value;
        }
        
    }
    
    if( count($completeFields) ){
        return $completeFields;
    }
    
    return false;
}

//Возвращает число и общую стоимость товаров в корзине с учетом скидки
function getDataCart($productsShoppingCart){
    
    $productAmountCart = 0;
    $totalPrice = 0;
    			            
    foreach($productsShoppingCart as $key=>$value){
    			                
    	$productAmountCart++;
    			                
    	$productPrice = getActualPrice("cart", $productsShoppingCart, $key, $value["price"]);
    	
    	$totalPrice += $productPrice * $value["amountProduct"];
    }
    
    if($productAmountCart == 0){
        return false;
    }
    			            
    $dataCart = array(
        "productAmountCart"=> $productAmountCart,
        "totalPrice"=> $totalPrice
        
        );
    
    return $dataCart;
    
}

//Возвращает массив с актуальными ценами(со скидкой) всех товаров в корзине
function getActualPriceAllProducts($productsShoppingCart){
    
    //$productsShoppingCart = getCookie("productsShoppingCart");
    
    if(!$productsShoppingCart){
        return false;
    }
    
    $productsActualPrices = [];
    
    foreach($productsShoppingCart as $key=>$value){
        
        $productsActualPrices[$key] = getActualPrice("cart", $productsShoppingCart, $key );
    }
    
    return $productsActualPrices;
}

//Получаем актуальную цену
function getActualPrice($type, $productsShoppingCart, $productID, $productPrice, $productAmount){
  
    $productDiscount = getDiscount($productID);
    $actualPrice;
    
    if($productPrice){
        $actualPrice = $productPrice;
    }else{
        $actualPrice = $productsShoppingCart[$productID]["price"];
    }
    
    //Проверка на активацию скидок
    if( !check_saleSteps() ){
        return $actualPrice;
    }
    
    $totalPriceProduct = $productPrice * $productAmount;
    
    $actualStepDiscont;
    
    if($type == "cart"){
        $actualStepDiscont = getActualStepDiscont($productID, $productsShoppingCart);
    }else{
        //При расчете на странице товара передаем дополнительно цену*количество товара введенного в форму, но ещё не добавленного в корзину
        $actualStepDiscont = getActualStepDiscont($productID, $productsShoppingCart, $totalPriceProduct);
    }
    
    if($actualStepDiscont){
        
        $termActualStep = get_term_by("name",$actualStepDiscont,"wholesalePrice");
        $actualPrice = $productDiscount[$termActualStep->term_id];
        
        /*
        foreach($productDiscount as $key=>$value){
            
            $term = get_term_by(id,$key,"wholesalePrice");
            
            if( (int)$term->name <= $actualStepDiscont ){
                $actualPrice = (int)$value;
                //break;
            }
            
        }*/
    }
    
    $actualPrice = str_replace(",",".",$actualPrice);
    $actualPrice = (float)$actualPrice;
    
    $actualPrice = round($actualPrice,2);
    
     return $actualPrice;
}
    
    

function getActualStepDiscont($productID, $productsShoppingCart, $totalPriceProduct){
    
    $totalPriceInShoppingCart = 0;
    $productDiscount = getDiscount($productID);
    
    if($totalPriceProduct){
        
        $totalPriceInShoppingCart = $totalPriceProduct;
        
    }
    
    
    if($productsShoppingCart){
            
            foreach($productsShoppingCart as $key=>$value){
                
                $productPriceSc = $value["price"];
                $amountProductSc = $value["amountProduct"];
                
                $productOFSPSum = $productPriceSc * $amountProductSc;
                
                $totalPriceInShoppingCart += $productOFSPSum;
            
            }
            
        }
    
    //Добавляем фильтр который преобразует name цен в числа для правильной сортировки
    add_filter('get_terms_orderby', 'sort_terms_clause', 10, 3);
    /*
    $args = array(
    	'taxonomy' => 'wholesalePrice',
    	'hide_empty' => false,
    );
    
    $wholesalePrice = get_terms( $args );
    //print_r($wholesalePrice);
    
    $actualStepDiscont;
    
    if( $wholesalePrice && ! is_wp_error($wholesalePrice) ){
        
        foreach($wholesalePrice as $term){
            
            $stepDiscount = $term->name;
            //echo $stepDiscount." ".$totalPriceInShoppingCart."<p></p>";
            if($totalPriceInShoppingCart >= $stepDiscount){
                $actualStepDiscont = $stepDiscount;
            }
        }
        
    }
    */
    
    $actualStepDiscont;
    
    foreach($productDiscount as $key=>$value){
            
        $term = get_term_by(id,$key,"wholesalePrice");
        
        $stepDiscount = $term->name;
        //echo $stepDiscount." ".$totalPriceInShoppingCart."<p></p>";
        if($totalPriceInShoppingCart >= $stepDiscount){
            $actualStepDiscont = $stepDiscount;
        }
            
    }
    
    
    // удаляем фильтр
    remove_filter('get_terms_orderby', 'sort_terms_clause', 10);  
    
    return $actualStepDiscont;
}

//Получаем массив с оптовыми шагами цен для товара(false если его нет)
function getDiscount($id){
    
    $wholesalePrice = get_post_meta($id, '_wholesalePrice',true);
    
    if($wholesalePrice) return $wholesalePrice;
    
    return false;
}

function getCookie($type){
    
    if( !isset( $_COOKIE[$type] ) ){
        return false;
    }
    
    //Получаем куки и удаляем экранирование символов
    $cookie = stripslashes( $_COOKIE[$type] ); 
				
	//Восстанавливаем массив из строки json			
    $productsShoppingCart = json_decode($cookie,true );
    
    return $productsShoppingCart;
}


function getClassActiveItem($userID,$type){
    
    $data = get_user_meta($userID, "accountData", true); 
    
    if($type == "cartQuickOrder" && !$data){
        return "itemControlPanelActive";
    }else if($type == "cartPerson" && $data[accountDetail] && !$data[accountLegalDetail]){
        return "itemControlPanelActive";
    }else if($type == "cartLegalPerson" && $data[accountLegalDetail]){
        return "itemControlPanelActive";
    }
    
}

