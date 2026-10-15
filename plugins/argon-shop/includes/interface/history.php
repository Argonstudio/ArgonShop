<?php
/**
 * История просмотра товаров (AJAX)
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Страница товара → просмотр товара → блок «Вы смотрели»
 * 
 * ЧТО ОН ДЕЛАЕТ:
 * 1. Сохраняет ID просмотренного товара в cookie AS_History
 * 2. Убирает дубликаты
 * 3. Добавляет новые товары в начало списка
 * 
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Регистрация AJAX-обработчиков
add_action( 'wp_ajax_addCookieHistory_history', 'as_addCookieHistory_history_callback' );
add_action( 'wp_ajax_nopriv_addCookieHistory_history', 'as_addCookieHistory_history_callback' );

/**
 * ОБРАБОТЧИК: добавление товара в историю просмотра
 * 
 * ГДЕ: страница товара → при загрузке
 * ЧТО ОН ДЕЛАЕТ:
 * 1. Получает ID товара из запроса
 * 2. Читает текущую историю из cookie
 * 3. Убирает дубликаты
 * 4. Добавляет ID в начало
 * 5. Сохраняет cookie
 */
function as_addCookieHistory_history_callback() {
    
    // Проверка nonce
    check_ajax_referer( 'argon_shop_nonce', 'nonce' );
    
    // Проверка productID
    $productID = isset( $_POST['productID'] ) ? absint( $_POST['productID'] ) : 0;
    
    if ( 0 === $productID ) {
        wp_die();
    }
    
    // Убеждаемся, что товар существует
    if ( ! get_post( $productID ) ) {
        wp_die();
    }
    
    $ASHistory = array();
    
    $cookie_data = as_getCookie( 'AS_History' );
    
    if ( is_array( $cookie_data ) && ! empty( $cookie_data ) ) {
        
        // Убираем дубликаты и проверяем
        $cleanHistory = array();
        
        foreach ( $cookie_data as $value ) {
            
            $value = absint( $value );
            
            if ( $value === 0 || $value === $productID ) {
                continue;
            }
            
            $cleanHistory[] = $value;
        }
        
        $ASHistory = $cleanHistory;
    }
    
    // Добавляем новый товар в начало
    array_unshift( $ASHistory, $productID );
    
    // Ограничиваем длину истории (защита от DoS)
    if ( count( $ASHistory ) > 100 ) {
        $ASHistory = array_slice( $ASHistory, 0, 100 );
    }
    
    $ASHistory = wp_json_encode( $ASHistory );
    
    // PHP 8.5: setcookie() с массивом опций
    $cookie_options = array(
        'expires'  => time() + 1209600,
        'path'     => COOKIEPATH ? COOKIEPATH : '/',
        'domain'   => COOKIE_DOMAIN ? COOKIE_DOMAIN : '',
        'secure'   => is_ssl(),
        'httponly' => false, // Должно быть false — читается из JS
        'samesite' => 'Lax',
    );
    
    setcookie( 'AS_History', $ASHistory, $cookie_options );
    
    wp_die();
}