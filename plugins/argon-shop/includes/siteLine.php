<?php
/**
 * Замена маркера as_homepage в меню на домен сайта
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * 1. Меню сайта (шапка, футер)
 * 2. Ссылка с маркером as_homepage должна заменяться на текущий домен
 * 
 * ЧТО ОН ДЕЛАЕТ:
 * Заменяет маркер «as_homepage» в URL пунктов меню на текущий домен сайта (в редакторе меню админки ссылка выглядит как http://as_homepage).
 * Используется для мультирегиональных сайтов — один пункт меню работает
 * как ссылка на главную страницу для разных доменов.
 * 
 * PHP 8.5:
 * - $_SERVER['SERVER_NAME'] проверяется на существование и тип
 * - домен проверяется регулярным выражением (защита от подмены Host)
 * - $item->url проверяется на null перед stristr()
 * - откат к home_url(), если SERVER_NAME пуст или некорректен
 * 
 * БЕЗОПАСНОСТЬ:
 * - $_SERVER['SERVER_NAME'] можно подменить через HTTP-заголовок Host,
 *   поэтому добавлены проверка и откат.
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_filter( 'wp_nav_menu_objects', 'as_change_nav_menu_objects', 10, 2 );

/**
 * ФУНКЦИЯ: заменяет маркер as_homepage в URL меню на домен сайта
 * 
 * @param array    $sorted_menu_items — массив объектов пунктов меню
 * @param stdClass $args              — аргументы wp_nav_menu()
 * 
 * @return array Изменённый массив пунктов меню
 */
function as_change_nav_menu_objects( $sorted_menu_items, $args ) {
    
    
    
    // Выполняется только на фронтенде (не в админке и не в AJAX)
    if ( is_admin() || wp_doing_ajax() ) {
        return $sorted_menu_items;
    }
    
    if ( ! is_array( $sorted_menu_items ) ) {
        return $sorted_menu_items;
    }
    
    // Получаем безопасное имя домена
    $server_name = as_get_safe_server_name();
    
    // Если домен получить не удалось — возвращаем как есть
    if ( empty( $server_name ) ) {
        return $sorted_menu_items;
    }
    
    foreach ( $sorted_menu_items as $index => $item ) {
        
        // PHP 8.5: убеждаемся, что $item — объект и имеет url
        if ( ! is_object( $item ) || ! isset( $item->url ) ) {
            continue;
        }
        
        // PHP 8.5: $item->url может быть null — приводим к строке
        $url = (string) $item->url;
        
        if ( $url === '' ) {
            continue;
        }
        
        // Ищем маркер as_homepage
        if ( stripos( $url, 'as_homepage' ) === false ) {
            continue;
        }
        
        // Заменяем маркер доменом
        $new_url = str_ireplace( 'as_homepage', $server_name, $url );
        
        // Экранируем URL
        $sorted_menu_items[ $index ]->url = esc_url_raw( $new_url );
    }
    
    return $sorted_menu_items;
}

/**
 * ФУНКЦИЯ: безопасное получение имени домена
 * 
 * Приоритет:
 * 1. $_SERVER['SERVER_NAME'] — если существует и корректен
 * 2. home_url() — как откат (когда SERVER_NAME отсутствует)
 * 
 * Проверка SERVER_NAME обязательна, потому что значение приходит из
 * HTTP-заголовка Host, который может подменить злоумышленник.
 * 
 * @return string Домен (например, «example.com») или пустая строка
 */
function as_get_safe_server_name() {
    
    $server_name = '';
    
    // Пробуем получить SERVER_NAME
    if ( isset( $_SERVER['SERVER_NAME'] ) && is_string( $_SERVER['SERVER_NAME'] ) ) {
        
        // Убираем экранирование и очищаем
        $raw = wp_unslash( $_SERVER['SERVER_NAME'] );
        $raw = sanitize_text_field( $raw );
        
        // Проверка: допустимые символы домена — буквы, цифры, точки, дефисы
        // Порт (:) также допустим
        if ( preg_match( '/^[a-z0-9.\-]+(:\d+)?$/i', $raw ) ) {
            $server_name = $raw;
        }
    }
    
    // Откат — home_url() из настроек WordPress
    if ( empty( $server_name ) ) {
        
        $home_host = wp_parse_url( home_url(), PHP_URL_HOST );
        
        if ( is_string( $home_host ) && $home_host !== '' ) {
            $server_name = $home_host;
        }
    }
    
    return $server_name;
}
