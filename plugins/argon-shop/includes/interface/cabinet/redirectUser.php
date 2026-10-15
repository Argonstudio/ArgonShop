<?php
/**
 * Редиректы пользователя и контроль доступа
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * 1. Вход → редирект в кабинет
 * 2. Выход → редирект на главную
 * 3. Открытие кабинета без входа → редирект на страницу входа
 * 
 * ЧТО ОН ДЕЛАЕТ:
 * 1. Редиректит пользователей после входа
 * 2. Редиректит после выхода
 * 3. Скрывает админ-панель для не-администраторов
 * 4. Защищает страницу кабинета от гостей
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ФУНКЦИЯ: редирект пользователя после входа
 * 
 * @param string  $redirect_to — URL редиректа
 * @param string  $request     — запрошенный URL
 * @param WP_User $user        — объект пользователя
 * 
 * @return string URL редиректа
 */
function as_my_login_redirect( $redirect_to, $request, $user ) {
    
    // Проверяем, есть ли у пользователя роли
    if ( isset( $user->roles ) && is_array( $user->roles ) ) {
        
        // Администратор — редирект по умолчанию
        if ( in_array( 'administrator', $user->roles, true ) ) {
            return $redirect_to;
        }
        
        // Все остальные — в кабинет
        $cabinet_url = as_get_cabinetPageURL();
        
        if ( $cabinet_url ) {
            return $cabinet_url;
        }
        
        // Если страница кабинета не настроена — на главную
        return home_url();
    }
    
    return $redirect_to;
}

/**
 * ФУНКЦИЯ: редирект на главную после выхода
 * 
 * @param string $logout_url — URL выхода
 * @param string $redirect   — текущий редирект
 * 
 * @return string URL выхода с редиректом
 */
function as_redirect_after_logout( $logout_url, $redirect ) {
    
    if ( empty( $redirect ) ) {
        $logout_url = add_query_arg( 'redirect_to', urlencode( home_url() ), $logout_url );
    }
    
    return $logout_url;
}

/**
 * ФУНКЦИЯ: скрывает админ-панель для не-администраторов
 * 
 * @param bool $content — текущее состояние админ-панели
 * 
 * @return bool
 */
function as_my_function_admin_bar( $content ) {
    return current_user_can( 'administrator' ) ? $content : false;
}

add_filter( 'show_admin_bar', 'as_my_function_admin_bar' );
add_filter( 'logout_url', 'as_redirect_after_logout', 10, 2 );
add_filter( 'login_redirect', 'as_my_login_redirect', 10, 3 );

/**
 * ФУНКЦИЯ: защищает страницу кабинета от гостей
 * 
 * ГДЕ: когда страница кабинета открыта без входа
 * ЧТО ДЕЛАЕТ: редиректит гостя на страницу входа
 */
add_action( 'template_redirect', function() {
    
    if ( is_user_logged_in() ) {
        return;
    }
    
    $cabinet_id = as_get_cabinetPageID();
    
    // Если страница кабинета не настроена — выходим
    if ( ! $cabinet_id ) {
        return;
    }
    
    if ( is_page( $cabinet_id ) ) {
        wp_safe_redirect( wp_login_url() );
        exit;
    }
} );