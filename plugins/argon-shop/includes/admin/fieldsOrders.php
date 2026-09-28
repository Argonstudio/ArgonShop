<?php

//add_action( 'admin_menu', 'detailedfields_remove_meta_box');

//function detailedfields_remove_meta_box(){
   
   //remove_meta_box('tagsdiv-wholesalePrice', 'product', 'normal');
   //remove_meta_box('tagsdiv-characteristics', 'product', 'normal');
//}

//Добавляем новые метабоксы таксономии 
 add_action( 'add_meta_boxes', 'orders_add_meta_box');
 
 function orders_add_meta_box() {
     //echo 1;
     add_meta_box( 'detailOrders_meta_box', 'Параметры заказа','detailOrders_metabox','shoporder' ,'normal','core');
     add_meta_box( 'detailBuyer_meta_box', 'Информация от заказчика','detailBuyer_metabox','shoporder' ,'normal','core');
     //if( check_saleSteps() ){
     
         //add_meta_box( 'wholesalePrice_meta_box', 'Цена со скидкой','wholesalePrice_metabox','product' ,'normal','core');
     
     //}
        
     
 }
 
 function detailOrders_metabox( $post ){
    
    wp_nonce_field( basename( __FILE__ ), 'detailOrders_fields' );
    
    $productsCart = get_post_meta($post->ID, 'productsCart',true);
    
    print_r($productsCart);
    
    ?>
    
    <div class="orderAllProducts">
    
    <?php
    
    foreach($productsCart as $key => $value){
                        
        createTableProduct($key, $value);
        
    }
    
    ?>
    
    </div>
    
    <div class="blockAddOrderProduct">
        
        Добавить товар по 
        
        <select class="typeAddOrder">
            <option selected value="article">артикулу</option>
            <option value="id">ID</option>
        </select>
        
        <input type="text" class="valueAddOrder">
        <input type="button" class="addOrder" value="Добавить">
        
    </div>
    
    <div class="orderError"></div>
    
    <?php
    //wp_die();
    
 }
 
 
function createTableProduct($idProduct, $dataProduct){
    
    $post_data = get_post( $idProduct );
                        
        //$post_img = get_the_post_thumbnail_url( $idProduct, "thumbnail" );
        //$post_link = $post_data->guid;
        $post_link = $dataProduct["link"];
        //$post_name = $post_data->post_title;
        $post_name = $dataProduct["name"];
        $post_weight = get_post_meta( $idProduct, '_weight', true );
        //$post_article = get_post_meta( $idProduct, '_article', true );
                        
        $post_price = $dataProduct["price"];
                        
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
                                                
                                <input type="text" data-productid="<?=$idProduct ?>" name="order_product[<?=$idProduct ?>]"class="orderAmountProduct" value="<?php echo $dataProduct["amountProduct"]; ?>">
                                                    
                                <div class="block-orderChangeValue">
                                                        
                                    <input type="button" data-productid="<?php echo $idProduct ?>" class="plusProduct" value="+">
                                    <input type="button" data-productid="<?php echo $idProduct ?>" class="minusProduct" value="-">
                                                        
                                </div>
                                                
                            </td>
                                            
                            <?php if($post_weight){ ?>
                                            
                                <td class="amountProductWeight"><span><?php echo $post_weight*$dataProduct["amountProduct"];  ?></span> кг</td>
                                            
                            <?php } ?>
                                            
                            <td class="amountProductPrice"><span><?php echo $post_price*$dataProduct["amountProduct"];  ?></span> ₽</td>
                        </tr>
                                        
                    </table>
                                    
                </td>
            </tr>
            
        </table>
                        
                        
        <?php
        //print_r($post_data);
    
}
 
 function detailBuyer_metabox( $post ){
    
    wp_nonce_field( basename( __FILE__ ), 'detailOrders_fields' );
    
    $fieldsCart = get_post_meta($post->ID, 'fieldsCart',true);
    $shipping = get_post_meta($post->ID, 'shipping',true);
    $payment = get_post_meta($post->ID, 'payment',true);
    
    print_r($fieldsCart);
    echo $shipping;
    echo $payment;
    
    //wp_die();
    
 }    