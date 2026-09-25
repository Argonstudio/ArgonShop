<?php
/**
 * Работа с заказами пользователя
 *
 * @package Argon_Shop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ИСПОЛЬЗУЕТСЯ:
 * - includes/interface/cabinet/userOrders/ordersView.php — вывод заказов в ЛК
 * - includes/interface/cabinet/userOrders/ordersAjax.php — фильтр заказов
 * 
 * ДАННЫЕ, ВОЗВРАЩАЕМЫЕ get_user_orders():
 * 
 *   $orders['orders']        — массив WP_Post заказов
 *   $orders['orders_meta']   — мета-поля: [order_id => ['number', 'totalPrice', 'productsCart']]
 *   $orders['orders_status'] — статусы: [order_id => 'Название статуса']
 * 
 * Если заказов нет или данные некорректны — возвращается пустой массив.
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ФУНКЦИЯ: Получение статусов заказов
 * 
 * Возвращает все статусы таксономии statusorders.
 * Если передан $statuses_orders_user — оставляет только те,
 * что есть в этом массиве (пересечение).
 * 
 * @param array $statuses_orders_user — статусы конкретного пользователя
 * 
 * @return array Массив [term_id => name]
 */
function get_used_statuses( $statuses_orders_user = array() ) {
    
    $argumentStatusAddTerms = array(
        'taxonomy'   => 'statusorders',
        'hide_empty' => true,
        'fields'     => 'id=>name',
    );
    
    $statusTerms = get_terms( $argumentStatusAddTerms );
    
    // Проверка на WP_Error
    if ( is_wp_error( $statusTerms ) || ! is_array( $statusTerms ) ) {
        return array();
    }
    
    if ( ! empty( $statuses_orders_user ) && is_array( $statuses_orders_user ) ) {
        $statusTerms = array_intersect( $statusTerms, $statuses_orders_user );
    }
    
    return $statusTerms;
}


/**
 * ФУНКЦИЯ: Получение заказов пользователя
 * 
 * @param int   $user_ID — ID пользователя
 * @param array $filters — фильтры:
 *                         'orderStatus' => array|int — массив ID статусов или один ID
 * 
 * @return array {
 *     @type array $orders        — массив WP_Post
 *     @type array $orders_meta   — мета-данные заказов
 *     @type array $orders_status — статусы заказов
 * }
 * 
 * Или пустой массив, если заказов нет / данные некорректны.
 */
function get_user_orders( $user_ID, $filters = array() ) {
    
    // ============================================
    // ВАЛИДАЦИЯ ВХОДНЫХ ПАРАМЕТРОВ
    // ============================================
    
    if ( ! is_int( $user_ID ) ) {
        return array();
    }
    
    // $filters мог не прийти — приводим к массиву
    if ( ! is_array( $filters ) ) {
        $filters = array();
    }
    
    // Валидация фильтра по статусам
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
    
    // Фильтр по статусу
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
    
    // Если заказов нет — возвращаем пустой массив
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
        
        // Проверка: статус мог быть не установлен
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