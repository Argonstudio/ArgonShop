<?php
/**
 * Вывод заказов пользователя в личном кабинете
 *
 * @package Argon_Shop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Личный кабинет → блок "Мои заказы"
 * URL: /cabinet/
 * 
 * ЧТО ДЕЛАЕТ:
 * 1. Выводит таблицу с заказами пользователя
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ФУНКЦИЯ: Вывод заказов пользователя с фильтром
 * 
 * @param int|null   $user_ID — ID пользователя
 * @param array|null $filters — фильтры для выборки заказов
 */
function view_user_orders( $user_ID = null, $filters = null ) {
    
    $user_ID = absint( $user_ID );
    
    $orders = get_user_orders( $user_ID, $filters );
    
    $usedStatuses = array();
    
    if ( ! empty( $orders['orders_status'] ) && is_array( $orders['orders_status'] ) ) {
        $usedStatuses = get_used_statuses( $orders['orders_status'] );
    }
    
    if ( ! empty( $orders ) && ! empty( $usedStatuses ) && count( $usedStatuses ) > 1 ) {
        
        $usedStatusesID = implode( ',', array_keys( $usedStatuses ) );
        
        ?>
        <div class="as-orderCabinet-blockFilter">
            
            <span class="as-orderCabinet-filterTitle">статус обработки заказа:</span>

            <select data-userid="<?php echo esc_attr( $user_ID ); ?>" class="as-orderCabinet-filterStatus">
                
                <option selected value="<?php echo esc_attr( $usedStatusesID ); ?>">все</option>
                
                <?php foreach ( $usedStatuses as $key => $value ) { ?>
                
                    <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
                
                <?php } ?>
                
            </select>
            
        </div>
        
        <?php
    }
    
    echo '<div class="as-ordersCabinet-block">';
    
    create_user_ordersTable( $orders );
    
    echo '</div>';
}

/**
 * ФУНКЦИЯ: Создание HTML-таблицы заказов
 * 
 * @param array $orders — массив с заказами и мета-данными
 */
function create_user_ordersTable( $orders ) {
    
    if ( empty( $orders ) || ! is_array( $orders ) ) {
        echo 'Вы ещё не оставляли заказов';
        return;
    }
    
    $ordersWP = isset( $orders['orders'] ) && is_array( $orders['orders'] ) ? $orders['orders'] : array();
    $orders_meta = isset( $orders['orders_meta'] ) && is_array( $orders['orders_meta'] ) ? $orders['orders_meta'] : array();
    $orders_status = isset( $orders['orders_status'] ) && is_array( $orders['orders_status'] ) ? $orders['orders_status'] : array();
    
    if ( empty( $ordersWP ) ) {
        echo 'Вы ещё не оставляли заказов';
        return;
    }
    
    ?> 
    
    <table class="as-ordersCabinet-table"> 

        <tr>
            <td class="as-orderCabinet-tdTitle">№</td>
            <td class="as-orderCabinet-tdTitle as-orderCabinet-tdDate">Дата</td>
            <td class="as-orderCabinet-tdTitle">Заказ</td>
        </tr>
    
    <?php
    
    foreach ( $ordersWP as $key => $value ) {
        view_user_order( $value, $orders_meta, $orders_status );
    }
    
    ?> 
    </table> 
    
    <?php
}

/**
 * ФУНКЦИЯ: Вывод одной строки заказа
 * 
 * @param WP_Post $order         — объект заказа
 * @param array   $orders_meta   — мета-данные всех заказов
 * @param array   $orders_status — статусы всех заказов
 */
function view_user_order( $order, $orders_meta, $orders_status ) {
    
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
                <li><b>Статус заказа: </b><?php echo esc_html( $status ); ?></li>
                <li class="as-orderCabinet-mobileDate">Дата: <?php echo esc_html( $date_display ); ?></li>
                <li>Виды товара: <?php echo esc_html( $products_cart ); ?></li>
                <li>Стоимость: <?php echo esc_html( $total_price ); ?> ₽</li>
            </ul>
        </td>
    </tr>    
    
    <?php
}