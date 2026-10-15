<?php
/**
 * Работа с заказами пользователя
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * ГДЕ ИСПОЛЬЗУЕТСЯ:
 * - includes/interface/cabinet/userOrders/ordersView.php — вывод заказов в кабинете
 * - includes/interface/cabinet/userOrders/ordersAjax.php — фильтр заказов
 * 
 * ДАННЫЕ, ВОЗВРАЩАЕМЫЕ as_get_user_orders():
 * 
 *   $orders['orders']        — массив объектов WP_Post заказов
 *   $orders['orders_meta']   — мета-поля: [order_id => ['number', 'totalPrice', 'productsCart']]
 *   $orders['orders_status'] — статусы: [order_id => 'Название статуса']
 * 
 * Если заказов нет или данные некорректны — возвращается пустой массив.
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ФУНКЦИЯ: получает статусы заказов
 * 
 * Возвращает все статусы таксономии statusorders.
 * Если передан $statuses_orders_user — оставляет только те,
 * которые есть в этом массиве (пересечение).
 * 
 * @param array $statuses_orders_user — статусы конкретного пользователя
 * 
 * @return array Массив [term_id => name]
 */
function as_get_used_statuses( $statuses_orders_user = array() ) {
    
    $argumentStatusAddTerms = array(
        'taxonomy'   => 'statusorders',
        'hide_empty' => true,
        'fields'     => 'id=>name',
    );
    
    $statusTerms = get_terms( $argumentStatusAddTerms );
    
    // Проверка WP_Error
    if ( is_wp_error( $statusTerms ) || ! is_array( $statusTerms ) ) {
        return array();
    }
    
    if ( ! empty( $statuses_orders_user ) && is_array( $statuses_orders_user ) ) {
        $statusTerms = array_intersect( $statusTerms, $statuses_orders_user );
    }
    
    return $statusTerms;
}


/**
 * ФУНКЦИЯ: получает заказы пользователя
 * 
 * @param int   $user_ID — ID пользователя
 * @param array $filters — фильтры:
 *                         'orderStatus' => array|int — массив ID статусов или один ID
 * 
 * @return array {
 *     @type array $orders        — массив WP_Post
 *     @type array $orders_meta   — мета-данные заказа
 *     @type array $orders_status — статусы заказов
 * }
 * 
 * Или пустой массив, если заказов нет / данные некорректны.
 */
function as_get_user_orders( $user_ID, $filters = array() ) {
    
    // ============================================
    // ПРОВЕРКА ВХОДНЫХ ДАННЫХ
    // ============================================
    
    if ( ! is_int( $user_ID ) ) {
        return array();
    }
    
    // $filters может отсутствовать — приводим его к массиву
    if ( ! is_array( $filters ) ) {
        $filters = array();
    }
    
    // Проверка фильтра статуса
    if ( ! empty( $filters['orderStatus'] ) ) {
        
        if ( is_array( $filters['orderStatus'] ) ) {
            
            foreach ( $filters['orderStatus'] as $key ) {
                
                if ( ! is_int( $key ) ) {
                    return array();
                }
            }
            
        } elseif ( ! is_int( $filters['orderStatus'] ) ) {
            
            return array();
        }
    }
    
    // ============================================
    // ЗАПРОС ЗАКАЗОВ
    // ============================================
    
    $args = array(
        'post_type'      => 'shoporder',
        'posts_per_page' => -1,
        'author'         => $user_ID,
    );
    
    // Фильтр статуса
    if ( ! empty( $filters['orderStatus'] ) ) {
        
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'statusorders',
                'field'    => 'id',
                'terms'    => $filters['orderStatus'],
            ),
        );
    }
    
    $ordersWP = get_posts( $args );
    
    // Заказов нет — возвращаем пустой массив
    if ( empty( $ordersWP ) ) {
        return array();
    }
    
    // ============================================
    // СБОР МЕТА-ДАННЫХ И СТАТУСОВ
    // ============================================
    
    $order_meta   = array();
    $order_status = array();
    
    foreach ( $ordersWP as $value ) {
        
        if ( ! is_object( $value ) || ! isset( $value->ID ) ) {
            continue;
        }
        
        $order_id = absint( $value->ID );
        
        if ( 0 === $order_id ) {
            continue;
        }
        
        // Мета-поля заказа
        $order_meta[ $order_id ] = array(
            'number'       => get_post_meta( $order_id, '_number', true ),
            'totalPrice'   => get_post_meta( $order_id, '_totalPrice', true ),
            'productsCart' => get_post_meta( $order_id, '_productAmountCart', true ),
        );
        
        // Статус заказа
        $currentStatus = get_the_terms( $order_id, 'statusorders' );
        
        // Проверка: статус может быть не задан
        if ( ! empty( $currentStatus ) && ! is_wp_error( $currentStatus ) && isset( $currentStatus[0]->name ) ) {
            $order_status[ $order_id ] = $currentStatus[0]->name;
        } else {
            $order_status[ $order_id ] = '';
        }
    }
    
    // ============================================
    // ФОРМИРОВАНИЕ РЕЗУЛЬТАТА
    // ============================================
    
    $orders = array(
        'orders'        => $ordersWP,
        'orders_meta'   => $order_meta,
        'orders_status' => $order_status,
    );
    
    return $orders;
}