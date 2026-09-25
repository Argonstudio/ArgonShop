<?php
/**
 * История просмотренных товаров
 *
 * @package Argon_Shop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Страница товара → просмотр товара → блок "Вы смотрели"
 * Страница "История просмотров" (/history/)
 * 
 * ЧТО ДЕЛАЕТ:
 * Возвращает список просмотренных товаров из кук AS_History
 * 
 * PHP 8.5:
 * - добавлена передача 'paged' и 'posts_per_page' в WP_Query
 * - это нужно для корректной работы пагинации
 * - защита от пустого массива
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ФУНКЦИЯ: Получение просмотренных товаров
 * 
 * @param int|string $count — сколько товаров вернуть (по умолчанию "all" — все)
 * 
 * @return WP_Query|false — Объект с товарами или false, если истории нет
 */
function get_history_products( $count = 'all' ) {
    
    $post_type = 'product';
    $history = getCookie( 'AS_History' );
    
    if ( ! $history || ! is_array( $history ) ) {
        return false;
    }
    
    // Очищаем массив от нечисловых значений
    $history = array_map( 'absint', $history );
    $history = array_filter( $history );
    $history = array_values( $history );
    
    if ( empty( $history ) ) {
        return false;
    }
    
    // ============================================
    // ПАГИНАЦИЯ
    // ============================================
    // 
    // PHP 8.5: получаем текущую страницу пагинации из глобального запроса.
    // WordPress использует либо 'paged' (для архивов), либо 'page' 
    // (для статических страниц с пагинацией).
    
    $paged = 1;
    
    if ( $count === 'all' ) {
        
        $paged_from_query = get_query_var( 'paged' );
        
        if ( ! $paged_from_query ) {
            $paged_from_query = get_query_var( 'page' );
        }
        
        if ( $paged_from_query ) {
            $paged = absint( $paged_from_query );
        }
        
        if ( $paged < 1 ) {
            $paged = 1;
        }
    }
    
    // Количество товаров на страницу
    if ( $count !== 'all' ) {
        
        $count = absint( $count );
        
        if ( $count > 0 ) {
            $history = array_slice( $history, 0, $count );
        }
        
        // При ограничении — одна страница
        $posts_per_page = $count > 0 ? $count : 12;
        $paged = 1;
        
    } else {
        
        $posts_per_page = absint( get_option( 'posts_per_page', 12 ) );
        
        if ( $posts_per_page < 1 ) {
            $posts_per_page = 12;
        }
    }
    
    // ============================================
    // ЗАПРОС
    // ============================================
    
    $arg = array(
        'post_type'      => $post_type,
        'post__in'       => $history,
        'orderby'        => 'post__in',
        'posts_per_page' => $posts_per_page,
        'paged'          => $paged,
    );
    
    $history_products = new WP_Query( $arg );
    
    return $history_products;
}