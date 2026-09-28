<?php


//Добавляем новые метабоксы таксономии 
 add_action( 'add_meta_boxes', 'orders_add_meta_box');
 
 function orders_add_meta_box() {

     add_meta_box( 'detailOrders_meta_box', 'Товары','detailOrders_metabox','shoporder' ,'normal','core');
     add_meta_box( 'detailBuyer_meta_box', 'Информация от заказчика','detailBuyer_metabox','shoporder' ,'normal','core');
     add_meta_box( 'statusOrders_meta_box', 'Статус заказа','statusOrders_metabox','shoporder' ,'side','core');
     
 }
 
 function statusOrders_metabox( $post ){
     
     $argumentStatusAddTerms = array(
		'taxonomy'       => 'statusorders',
		'hide_empty'   => false,
		'fields'         => 'id=>name',
		
	);
	
    $statusTerms = get_terms( $argumentStatusAddTerms );
    
    $currentStatus = get_the_terms( $post->ID, 'statusorders' );
    $currentStatus = $currentStatus[0]->term_id;
    
    foreach($statusTerms as $key => $value){
        
        $checked = ($key === $currentStatus) ? "checked='checked'" : ''; 
        echo "<label><input type='radio' name='status' value='$key' $checked />$value</label><br />";
        
    }
    
 }
 
 function detailOrders_metabox( $post ){
     
    $productsCart = get_post_meta($post->ID, '_productsCart',true);
    
    //print_r($productsCart);
    
     ?>
    
    <div class="orderAllProducts">
    
    <?php
    
    foreach($productsCart as $key => $value){
                        
        createTableProduct($key, $value);
        
    }
    
    ?>
    
    </div>
    
   <div class="blockAddOrderProduct">
        
        Добавить товар
        
        <input type="text" class="valueAddOrder" placeholder="название или артикул товара">
        <div class="addOrderResult"></div>
        
    </div>
    
    <div class="orderError"></div>
    
    <?php
    
 }
 
 
function createTableProduct($idProduct, $dataProduct){
    
        $post_data = get_post( $idProduct );
        
        $post_name;
        $amountProduct;
        $post_link;
        $post_price;
        
        if($dataProduct){
            
            $post_link = $dataProduct["link"];
            $amountProduct = $dataProduct["amountProduct"];
            $post_name = $dataProduct["name"];
            $post_price = $dataProduct["price"];
            
        }else{
            
            $amountProduct = 1;
            $post_name = $post_data->post_title;
            $post_link = get_post_permalink($idProduct);
            $post_price = get_post_meta( $idProduct, '_price', true );
        }
        
        
        $post_weight = get_post_meta( $idProduct, '_weight', true );
        //$post_article = get_post_meta( $idProduct, '_article', true );
                        
        
                        
        //$cartTotalWeight += $post_weight*$dataProduct["amountProduct"];
        //$cartTotalPrice += $post_price*$dataProduct["amountProduct"];
                        
                        
        ?>
                        
        <table data-productid="<?php echo $idProduct ?>" class="blockOrderProduct">
            <tr>
                <td>
                    <a class="orderProductTitle" href="<?php echo $post_link;  ?>"><?php echo $post_name; ?></a> 
                    <input type="button" data-productid="<?php echo $idProduct ?>" class="deleteProduct">                
                </td>
                                
            </tr>
                            
            <tr>
                <td>
                                    
                    <table class="block-orderCharactProduct">
                                        
                        <tr class="block-orderCharactProductTitle">
                            <td>₽/шт</td>
                            <td>Количество</td>
                                            
                            <?php if($post_weight){ ?>
                                            
                            <td>Вес</td>
                                            
                            <?php } ?>
                                            
                            <td>Цена</td>
                        </tr>
                                        
                        <tr class="block-orderCharactProductContent">
                            <td class="productPrice"><span><?php echo $post_price;  ?></span> </td>
                                            
                            <td class="block-orderAmountProduct">
                                                
                                <input type="text" data-productid="<?=$idProduct ?>" name="order_product[<?=$idProduct ?>]"class="orderAmountProduct" value="<?php echo $amountProduct; ?>">
                                                    
                                <div class="block-orderChangeValue">
                                                        
                                    <input type="button" data-productid="<?php echo $idProduct ?>" class="plusProduct" value="+">
                                    <input type="button" data-productid="<?php echo $idProduct ?>" class="minusProduct" value="-">
                                                        
                                </div>
                                                
                            </td>
                                            
                            <?php if($post_weight){ ?>
                                            
                                <td class="amountProductWeight"><span><?php echo $post_weight*$amountProduct;  ?></span> кг</td>
                                            
                            <?php } ?>
                                            
                            <td class="amountProductPrice"><span><?php echo $post_price*$amountProduct;  ?></span> ₽</td>
                        </tr>
                                        
                    </table>
                                    
                </td>
            </tr>
                            
        </table>
                        
                        
        <?php
        //print_r($post_data);
    
}
 
 function detailBuyer_metabox( $post ){
     
    //Проверочный input (уникальный идентификатор, параметр атрибута name) 
    wp_nonce_field( 'detailOrders_protect_action', 'detailOrders_protect_name' );
    
    $fieldsCart = get_post_meta($post->ID, '_fieldsCart',true);
    $shipping = get_post_meta($post->ID, '_shipping',true);
    $payment = get_post_meta($post->ID, '_payment',true);
    
    ?>
    
    <ul class='advanced-fields'>
    
    <?php
    
    foreach($fieldsCart as $keyField => $valueArrayField){
        
        if( !empty($valueArrayField["value"]) ){
            
            if($keyField === "messageUser"){
                
                as_getHTMLField("textarea", $keyField, $valueArrayField);
                
            }else{
                
                as_getHTMLField("input", $keyField, $valueArrayField);
                
            }
            
        }
        
    }
    
    ?>
    
    </ul>
    
    <div class="as-order-itemTitle">Добавить отсутствующие поля из:</div>
    
    <select class="as-order-addFieldsSelect">
        <option selected value="person">Физические лица</option>
        <option value="legalPerson">Юридические лица</option>
    </select>
    
    <input type="button" class="as-order-addFieldsSend" value="Добавить">
    
    <div class="as-order-addFieldsError"></div>
    <p></p>
    
    <div class="as-order-itemTitle">Способ доставки:</div>
    
    <?php
    
    $typeShipping = Array(
        
        "selfExport" => "Самовывоз",
        "shippingToAddress" => "Доставка по адресу"
        
        );
    
    foreach($typeShipping as $key => $value){
        
        $checked = ($key === $shipping) ? "checked='checked'" : '';
        echo "<label><input type='radio' name='shipping' value='$key' $checked />$value</label><br />";
    }
    
    ?>
        
    <div class="as-order-itemTitle">Способ оплаты:</div>
    
    <?php    
    
    $typePaiment = Array(
        
        "paimentUponReceipt" => "Оплата при получении"
        
        );
    
    foreach($typePaiment as $key => $value){
        
        $checked = ($key === $payment) ? "checked='checked'" : ''; 
        echo "<label><input type='radio' name='payment' value='$key' $checked />$value</label><br />";
    }
    
    //print_r($fieldsCart);
    //echo $shipping;
    //echo $payment;
    
 }
 
 function as_getHTMLField($type, $keyField, $valueArrayField){
     
     $class = "advanced-field";
     
     if($keyField === "addressUser" || $keyField === "legalAddressUser"){
         
         $class .= " as-order-fieldAddress";
         
     }
     
     ?>
     
    <li class="<?=$class ?>" data-keyfield="<?=$keyField ?>"> 
     
        <div class="span-advanced-fields"><?=$valueArrayField["name"] ?></div>
        
        <?php
        
        if($type === "input"){
        ?>
        
            <input type="text" name="_fieldValue[<?=$keyField ?>]" value="<?=$valueArrayField['value'] ?>">
        
        <?php    
        }elseif($type === "textarea"){
        ?>    
            
            <textarea name="_fieldValue[<?=$keyField ?>]"><?=$valueArrayField["value"] ?></textarea>
            
        <?php    
        }
        
        ?>
        
        
        
        <input type="hidden" name="_fieldName[<?=$keyField ?>]" value="<?=$valueArrayField["name"] ?>"> 
        
    </li>
        
     <?php
 }