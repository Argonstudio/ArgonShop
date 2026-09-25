<?php
/**
 * AJAX-фильтр заказов в личном кабинете
 *
 * @package Argon_Shop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Личный кабинет → вкладка "Заказы" → фильтр по статусу
 * URL: /lichnyj-kabinet/
 * 
 * ЧТО ДЕЛАЕТ:
 * 1. Принимает userID и список статусов
 * 2. Проверяет права (пользователь видит только свои заказы)
 * 3. Возвращает HTML-таблицу отфильтрованных заказов
 * 
 * ЗАВИСИМОСТИ:
 * - get_user_orders()          — includes/interface/cabinet/userOrders/ordersView.php
 * - create_user_ordersTable()  — includes/interface/cabinet/userOrders/ordersView.php
 * 
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Регистрация AJAX-обработчиков
add_action( 'wp_ajax_ordersFiltersStatus', 'ordersFiltersStatus_callback' );
add_action( 'wp_ajax_nopriv_ordersFiltersStatus', 'ordersFiltersStatus_callback' );

/**
 * ОБРАБОТЧИК: Фильтрация заказов по статусу
 * 
 * ГДЕ: Личный кабинет → изменение фильтра
 * ЧТО ДЕЛАЕТ:
 * 1. Проверяет, что пользователь запрашивает свои заказы
 * 2. Возвращает HTML-таблицу с отфильтрованными заказами
 */
function ordersFiltersStatus_callback() {
    
    // Валидация userID
    $userID = isset( $_POST['userID'] ) ? absint( $_POST['userID'] ) : 0;
    
    // Проверка прав: пользователь видит только свои заказы.
    // Исключение: администраторы могут смотреть любые заказы.
    $current_user_id = get_current_user_id();
    
    if ( $userID !== $current_user_id && ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Недостаточно прав' );
    }
    
    // Валидация статусов
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
        
        $orders = get_user_orders( $userID, $filters );
        
        if ( ! empty( $orders ) ) {
            create_user_ordersTable( $orders );
        } else {
            echo 'Нет подходящих постов';
        }
        
    } else {
        echo 'Некорректные данные запроса';
    }
    
    wp_die();
}