<?php
/**
 * Вывод заказов пользователя в кабинете
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Кабинет → блок «Мои заказы»
 * URL: /cabinet/
 * 
 * ЧТО ОН ДЕЛАЕТ:
 * 1. Выводит таблицу с заказами пользователя
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ФУНКЦИЯ: выводит заказы пользователя с фильтром
 * 
 * @param int|null   $user_ID — ID пользователя
 * @param array|null $filters — фильтры выбора заказов
 */
function as_view_user_orders( $user_ID = null, $filters = null ) {
    
    $user_ID = absint( $user_ID );
    
    $orders = as_get_user_orders( $user_ID, $filters );
    
    $usedStatuses = array();
    
    if ( ! empty( $orders['orders_status'] ) && is_array( $orders['orders_status'] ) ) {
        $usedStatuses = as_get_used_statuses( $orders['orders_status'] );
    }
    
    if ( ! empty( $orders ) && ! empty( $usedStatuses ) && count( $usedStatuses ) > 1 ) {
        
        $usedStatusesID = implode( ',', array_keys( $usedStatuses ) );
        
        ?>
        <div class="as-orderCabinet-blockFilter">
            
            <span class="as-orderCabinet-filterTitle"><?php esc_html_e( 'order processing status:', 'argon-shop' ); ?></span>

            <select data-userid="<?php echo esc_attr( $user_ID ); ?>" class="as-orderCabinet-filterStatus">
                
                <option selected value="<?php echo esc_attr( $usedStatusesID ); ?>"><?php esc_html_e( 'all', 'argon-shop' ); ?></option>
                
                <?php foreach ( $usedStatuses as $key => $value ) { ?>
                
                    <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
                
                <?php } ?>
                
            </select>
            
        </div>
        
        <?php
    }
    
    echo '<div class="as-ordersCabinet-block">';
    
    as_create_user_ordersTable( $orders );
    
    echo '</div>';
}

/**
 * ФУНКЦИЯ: собирает HTML-таблицу заказов
 * 
 * @param array $orders — массив с заказами и мета-данными
 */
function as_create_user_ordersTable( $orders ) {
    
    if ( empty( $orders ) || ! is_array( $orders ) ) {
        echo __( 'You have not placed any orders yet', 'argon-shop' );
        return;
    }
    
    $ordersWP = isset( $orders['orders'] ) && is_array( $orders['orders'] ) ? $orders['orders'] : array();
    $orders_meta = isset( $orders['orders_meta'] ) && is_array( $orders['orders_meta'] ) ? $orders['orders_meta'] : array();
    $orders_status = isset( $orders['orders_status'] ) && is_array( $orders['orders_status'] ) ? $orders['orders_status'] : array();
    
    if ( empty( $ordersWP ) ) {
        echo __( 'You have not placed any orders yet', 'argon-shop' );
        return;
    }
    
    ?> 
    
    <table class="as-ordersCabinet-table"> 

        <tr>
            <td class="as-orderCabinet-tdTitle">№</td>
            <td class="as-orderCabinet-tdTitle as-orderCabinet-tdDate"><?php esc_html_e( 'Date', 'argon-shop' ); ?></td>
            <td class="as-orderCabinet-tdTitle"><?php esc_html_e( 'Order', 'argon-shop' ); ?></td>
        </tr>
    
    <?php
    
    foreach ( $ordersWP as $key => $value ) {
        as_view_user_order( $value, $orders_meta, $orders_status );
    }
    
    ?> 
    </table> 
    
    <?php
}

/**
 * ФУНКЦИЯ: выводит строку одного заказа
 * 
 * @param WP_Post $order         — объект заказа
 * @param array   $orders_meta   — мета-данные всех заказов
 * @param array   $orders_status — статусы всех заказов
 */
function as_view_user_order( $order, $orders_meta, $orders_status ) {
    
    if ( ! $order || ! isset( $order->ID ) ) {
        return;
    }
    
    $order_id = absint( $order->ID );
    
    $date = explode( ' ', $order->post_date );
    $date_display = isset( $date[0] ) ? $date[0] : '';
    
    $number = isset( $orders_meta[ $order_id ]['number'] ) ? $orders_meta[ $order_id ]['number'] : '';
    $status = isset( $orders_status[ $order_id ] ) ? $orders_status[ $order_id ] : '';
    $products_cart = isset( $orders_meta[ $order_id ]['productsCart'] ) ? $orders_meta[ $order_id ]['productsCart'] : '';
    $total_price = isset( $orders_meta[ $order_id ]['totalPrice'] ) ? $orders_meta[ $order_id ]['totalPrice'] : '';
    
    ?>
    
    <tr>
        <td class="as-orderCabinet-tdNumber"><?php echo esc_html( $number ); ?></td>
        <td class="as-orderCabinet-tdDate"><?php echo esc_html( $date_display ); ?></td>
        <td class="as-orderCabinet-tdDetailOrder">
            <ul>
                <li><b><?php esc_html_e( 'Order status:', 'argon-shop' ); ?> </b><?php echo esc_html( $status ); ?></li>
                <li class="as-orderCabinet-mobileDate"><?php esc_html_e( 'Date:', 'argon-shop' ); ?> <?php echo esc_html( $date_display ); ?></li>
                <li><?php esc_html_e( 'Product types:', 'argon-shop' ); ?> <?php echo esc_html( $products_cart ); ?></li>
                <li><?php esc_html_e( 'Cost:', 'argon-shop' ); ?> <?php echo as_price_html( esc_html( $total_price ) ); ?></li>
            </ul>
        </td>
    </tr>    
    
    <?php
}