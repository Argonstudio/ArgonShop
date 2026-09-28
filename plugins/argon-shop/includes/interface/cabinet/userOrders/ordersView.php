<?php
    
    function view_user_orders( $user_ID, $filters ){
        
        $orders = get_user_orders( $user_ID, $filters );
        
        $usedStatuses = get_used_statuses( $orders["orders_status"] );        
        
        if( !empty($orders) && !empty($usedStatuses) && count($usedStatuses) > 1 ){
            
            $usedStatusesID = implode(",", array_keys($usedStatuses) );
            
            ?>
            <div class="as-orderCabinet-blockFilter">
                
                <span class="as-orderCabinet-filterTitle">статус обработки заказа:</span>
    
                <select data-userid="<?=$user_ID ?>" class="as-orderCabinet-filterStatus">
                    
                    <option selected value="<?=$usedStatusesID ?>">все</option>
                    
                    <?php
                    
                    foreach($usedStatuses as $key => $value){
                    ?>
                    
                        <option value="<?=$key ?>"><?=$value ?></option>
                    
                    <?php
                        
                    }
                    ?>
                    
                </select>
                
            </div>
            
            <?php
            
        }
        
        echo '<div class="as-ordersCabinet-block">';
        
        create_user_ordersTable($orders);
        
        echo "</div>";    
        
    }
    
    function create_user_ordersTable($orders){
        
        if( !empty($orders) ){
            
            $ordersWP = $orders["orders"];
            $orders_meta = $orders["orders_meta"];
            $orders_status = $orders["orders_status"];
            
            ?> 
            
            <table class="as-ordersCabinet-table"> 
            
                <tr>
                    
                    <td class="as-orderCabinet-tdTitle">№</td>
                    <td class="as-orderCabinet-tdTitle as-orderCabinet-tdDate">Дата</td>
                    <td class="as-orderCabinet-tdTitle">Заказ</td>
                    
                </tr>
            
            <?php
            
            foreach($ordersWP as $key => $value){
                
                view_user_order($value, $orders_meta, $orders_status);
                
            }
            
            ?> </table> 
            
            
            <?php
            
        }else{
            
            echo "Вы ещё не оставляли заказов";
            
        }
        
    }
    
    function view_user_order($order, $orders_meta, $orders_status){
        
        $date = explode(" ", $order->post_date);
                
        ?>
                    
        <tr>
                    
            <td class="as-orderCabinet-tdNumber"><?=$orders_meta[$order->ID]["number"] ?></td>
            <td class="as-orderCabinet-tdDate"><?=$date[0] ?></td>
            <td class="as-orderCabinet-tdDetailOrder">
                <ul>
                    <li><b>Статус заказа: </b><?=$orders_status[$order->ID] ?></li>
                    <li class="as-orderCabinet-mobileDate">Дата: <?=$date[0] ?></li>
                    <li>Виды товара: <?=$orders_meta[$order->ID]["productsCart"] ?> </li>
                    <li>Стоимость: <?=$orders_meta[$order->ID]["totalPrice"] ?> ₽</li>
                    
                </ul>
            </td>
                    
        </tr>    
                
        <?php
        
    }
    
    