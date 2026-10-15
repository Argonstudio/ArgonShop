<?php
/**
 * AJAX-фильтр заказов в кабинете
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Кабинет → вкладка «Заказы» → фильтр по статусу
 * URL: /lichnyj-kabinet/
 * 
 * ЧТО ОН ДЕЛАЕТ:
 * 1. Принимает userID и список статусов
 * 2. Проверяет права (пользователь видит только свои заказы)
 * 3. Возвращает HTML-таблицу отфильтрованных заказов
 * 
 * ЗАВИСИМОСТИ:
 * - as_get_user_orders()          — includes/interface/cabinet/userOrders/ordersView.php
 * - as_create_user_ordersTable()  — includes/interface/cabinet/userOrders/ordersView.php
 * 
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Регистрация AJAX-обработчиков
add_action( 'wp_ajax_ordersFiltersStatus', 'as_ordersFiltersStatus_callback' );
add_action( 'wp_ajax_nopriv_ordersFiltersStatus', 'as_ordersFiltersStatus_callback' );

/**
 * ОБРАБОТЧИК: фильтр заказов по статусу
 * 
 * ГДЕ: Кабинет → смена фильтра
 * ЧТО ОН ДЕЛАЕТ:
 * 1. Проверяет, что пользователь запрашивает свои заказы
 * 2. Возвращает HTML-таблицу с отфильтрованными заказами
 */
function as_ordersFiltersStatus_callback() {
    
    check_ajax_referer( 'argon_shop_nonce', 'nonce' );
    
    // Проверка userID
    $userID = isset( $_POST['userID'] ) ? absint( $_POST['userID'] ) : 0;
    
    // Проверка прав: пользователь видит только свои заказы.
    // Исключение: администраторы могут видеть любые заказы.
    $current_user_id = get_current_user_id();
    
    if ( $userID !== $current_user_id && ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Insufficient permissions', 'argon-shop' ) );
    }
    
    // Проверка статуса
    $status_raw = isset( $_POST['status'] ) 
        ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) 
        : '';
    
    $status = explode( ',', $status_raw );
    
    foreach ( $status as $key => $value ) {
        $status[ $key ] = absint( $value );
    }
    
    // Убираем нулевые значения
    $status = array_filter( $status );
    
    if ( $userID !== 0 ) {
        
        $filters = array();
        $filters['orderStatus'] = $status;
        
        $orders = as_get_user_orders( $userID, $filters );
        
        if ( ! empty( $orders ) ) {
            as_create_user_ordersTable( $orders );
        } else {
            echo __( 'No matching posts', 'argon-shop' );
        }
        
    } else {
        echo __( 'Invalid request data', 'argon-shop' );
    }
    
    wp_die();
}