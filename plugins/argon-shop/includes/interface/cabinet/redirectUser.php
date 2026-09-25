<?php
/**
 * Перенаправление пользователей и управление доступом
 *
 * @package Argon_Shop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * 1. Вход в систему → перенаправление в кабинет
 * 2. Выход из системы → перенаправление на главную
 * 3. Заход в кабинет без авторизации → редирект на login
 * 
 * ЧТО ДЕЛАЕТ:
 * 1. Перенаправляет пользователей после входа
 * 2. Перенаправляет после выхода
 * 3. Скрывает админ-бар для не-администраторов
 * 4. Защищает страницу кабинета от гостей
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ФУНКЦИЯ: Перенаправление пользователя после входа
 * 
 * @param string  $redirect_to — URL для перенаправления
 * @param string  $request     — запрошенный URL
 * @param WP_User $user        — объект пользователя
 * 
 * @return string URL для перенаправления
 */
function my_login_redirect( $redirect_to, $request, $user ) {
    
    // Проверяем, есть ли у пользователя роли
    if ( isset( $user->roles ) && is_array( $user->roles ) ) {
        
        // Администратор — стандартный редирект
        if ( in_array( 'administrator', $user->roles, true ) ) {
            return $redirect_to;
        }
        
        // Остальные — в личный кабинет
        $cabinet_url = get_cabinetPageURL();
        
        if ( $cabinet_url ) {
            return $cabinet_url;
        }
        
        // Если страница кабинета не настроена — на главную
        return home_url();
    }
    
    return $redirect_to;
}

/**
 * ФУНКЦИЯ: Переадресация на главную после выхода
 * 
 * @param string $logout_url — URL выхода
 * @param string $redirect   — текущий редирект
 * 
 * @return string URL выхода с редиректом
 */
function redirect_after_logout( $logout_url, $redirect ) {
    
    if ( empty( $redirect ) ) {
        $logout_url = add_query_arg( 'redirect_to', urlencode( home_url() ), $logout_url );
    }
    
    return $logout_url;
}

/**
 * ФУНКЦИЯ: Скрытие админ-бара для не-администраторов
 * 
 * @param bool $content — текущее состояние админ-бара
 * 
 * @return bool
 */
function my_function_admin_bar( $content ) {
    return current_user_can( 'administrator' ) ? $content : false;
}

add_filter( 'show_admin_bar', 'my_function_admin_bar' );
add_filter( 'logout_url', 'redirect_after_logout', 10, 2 );
add_filter( 'login_redirect', 'my_login_redirect', 10, 3 );

/**
 * ФУНКЦИЯ: Защита страницы кабинета от гостей
 * 
 * ГДЕ: При заходе на страницу кабинета без авторизации
 * ЧТО ДЕЛАЕТ: Перенаправляет гостя на страницу входа
 */
add_action( 'template_redirect', function() {
    
    if ( is_user_logged_in() ) {
        return;
    }
    
    $cabinet_id = get_cabinetPageID();
    
    // Если страница кабинета не настроена — выходим
    if ( ! $cabinet_id ) {
        return;
    }
    
    if ( is_page( $cabinet_id ) ) {
        wp_safe_redirect( wp_login_url() );
        exit;
    }
} );