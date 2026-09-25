<?php
/**
 * История просмотров товаров (AJAX)
 *
 * @package Argon_Shop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Страница товара → просмотр товара → блок "Вы смотрели"
 * 
 * ЧТО ДЕЛАЕТ:
 * 1. Сохраняет ID просмотренного товара в куки AS_History
 * 2. Убирает дубликаты
 * 3. Новые товары добавляет в начало списка
 * 
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Регистрация AJAX-обработчиков
add_action( 'wp_ajax_addCookieHistory_history', 'addCookieHistory_history_callback' );
add_action( 'wp_ajax_nopriv_addCookieHistory_history', 'addCookieHistory_history_callback' );

/**
 * ОБРАБОТЧИК: Добавление товара в историю просмотров
 * 
 * ГДЕ: Страница товара → при загрузке
 * ЧТО ДЕЛАЕТ:
 * 1. Получает ID товара из запроса
 * 2. Читает текущую историю из куки
 * 3. Удаляет дубликаты
 * 4. Добавляет ID в начало
 * 5. Сохраняет куки
 */
function addCookieHistory_history_callback() {
    
    // Проверка nonce
    check_ajax_referer( 'argon_shop_nonce', 'nonce' );
    
    // Валидация productID
    $productID = isset( $_POST['productID'] ) ? absint( $_POST['productID'] ) : 0;
    
    if ( 0 === $productID ) {
        wp_die();
    }
    
    // Проверяем, что товар существует
    if ( ! get_post( $productID ) ) {
        wp_die();
    }
    
    $ASHistory = array();
    
    $cookie_data = getCookie( 'AS_History' );
    
    if ( is_array( $cookie_data ) && ! empty( $cookie_data ) ) {
        
        // Убираем дубликаты и валидируем
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