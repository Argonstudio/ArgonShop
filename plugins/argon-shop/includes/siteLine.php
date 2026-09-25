<?php
/**
 * Замена метки as_homepage в меню на домен сайта
 *
 * @package Argon_Shop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * 1. Меню сайта (шапка, футер)
 * 2. Ссылка с меткой as_homepage должна заменяться на текущий домен
 * 
 * ЧТО ДЕЛАЕТ:
 * Заменяет в URL пунктов меню метку "as_homepage" на домен текущего сайта(в админке при редактировании меню ссылка http://as_homepage).
 * Используется для мультирегиональности — один пункт меню работает
 * как ссылка на главную для разных доменов.
 * 
 * PHP 8.5:
 * - проверка $_SERVER['SERVER_NAME'] на существование и тип
 * - валидация домена через регулярное выражение (защита от подделки Host)
 * - проверка $item->url на null перед stristr()
 * - fallback на home_url(), если SERVER_NAME пустой или некорректный
 * 
 * БЕЗОПАСНОСТЬ:
 * - $_SERVER['SERVER_NAME'] может быть подделан через HTTP-заголовок Host,
 *   сделаны валидация и fallback.
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_filter( 'wp_nav_menu_objects', 'change_nav_menu_objects', 10, 2 );

/**
 * ФУНКЦИЯ: Замена метки as_homepage в URL меню на домен сайта
 * 
 * @param array    $sorted_menu_items — массив объектов пунктов меню
 * @param stdClass $args              — аргументы wp_nav_menu()
 * 
 * @return array Изменённый массив пунктов меню
 */
function change_nav_menu_objects( $sorted_menu_items, $args ) {
    
    
    
    // Выполняем только на фронтенде (не в админке и не в AJAX)
    if ( is_admin() || wp_doing_ajax() ) {
        return $sorted_menu_items;
    }
    
    if ( ! is_array( $sorted_menu_items ) ) {
        return $sorted_menu_items;
    }
    
    // Получаем безопасное имя домена
    $server_name = get_safe_server_name();
    
    // Если не удалось получить домен — возвращаем как есть
    if ( empty( $server_name ) ) {
        return $sorted_menu_items;
    }
    
    foreach ( $sorted_menu_items as $index => $item ) {
        
        // PHP 8.5: проверяем, что $item — объект и есть url
        if ( ! is_object( $item ) || ! isset( $item->url ) ) {
            continue;
        }
        
        // PHP 8.5: $item->url может быть null — приводим к строке
        $url = (string) $item->url;
        
        if ( $url === '' ) {
            continue;
        }
        
        // Ищем метку as_homepage
        if ( stripos( $url, 'as_homepage' ) === false ) {
            continue;
        }
        
        // Заменяем метку на домен
        $new_url = str_ireplace( 'as_homepage', $server_name, $url );
        
        // Экранируем URL
        $sorted_menu_items[ $index ]->url = esc_url_raw( $new_url );
    }
    
    return $sorted_menu_items;
}

/**
 * ФУНКЦИЯ: Безопасное получение имени домена
 * 
 * Приоритет:
 * 1. $_SERVER['SERVER_NAME'] — если существует и валиден
 * 2. home_url() — как fallback (для случаев, когда SERVER_NAME отсутствует)
 * 
 * Валидация SERVER_NAME обязательна, так как значение приходит из
 * HTTP-заголовка Host, который может быть подделан злоумышленником.
 * 
 * @return string Домен (например, "example.com") или пустая строка
 */
function get_safe_server_name() {
    
    $server_name = '';
    
    // Пытаемся получить SERVER_NAME
    if ( isset( $_SERVER['SERVER_NAME'] ) && is_string( $_SERVER['SERVER_NAME'] ) ) {
        
        // Убираем экранирование и очищаем
        $raw = wp_unslash( $_SERVER['SERVER_NAME'] );
        $raw = sanitize_text_field( $raw );
        
        // Валидация: допустимые символы для домена — буквы, цифры, точки, дефисы
        // Также допускаем порт (:)
        if ( preg_match( '/^[a-z0-9.\-]+(:\d+)?$/i', $raw ) ) {
            $server_name = $raw;
        }
    }
    
    // Fallback — home_url() из настроек WordPress
    if ( empty( $server_name ) ) {
        
        $home_host = wp_parse_url( home_url(), PHP_URL_HOST );
        
        if ( is_string( $home_host ) && $home_host !== '' ) {
            $server_name = $home_host;
        }
    }
    
    return $server_name;
}
