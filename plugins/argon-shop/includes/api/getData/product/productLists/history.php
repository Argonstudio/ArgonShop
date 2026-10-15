<?php
/**
 * История просмотренных товаров
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Страница товара → просмотр товара → блок «Вы смотрели»
 * Страница «История просмотров» (/history/)
 * 
 * ЧТО ОН ДЕЛАЕТ:
 * Возвращает список просмотренных товаров из cookie AS_History
 * 
 * PHP 8.5:
 * - 'paged' и 'posts_per_page' теперь передаются в WP_Query
 * - это нужно для корректной работы пагинации
 * - защита от пустого массива
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ФУНКЦИЯ: получает просмотренные товары
 * 
 * @param int|string $count — сколько товаров вернуть (по умолчанию «all»)
 * 
 * @return WP_Query|false — объект с товарами или false, если истории нет
 */
function as_get_history_products( $count = 'all' ) {
    
    $post_type = 'product';
    $history = as_getCookie( 'AS_History' );
    
    if ( ! $history || ! is_array( $history ) ) {
        return false;
    }
    
    // Убираем из массива нечисловые значения
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
    // WordPress использует 'paged' (для архивов) или 'page'
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
    
    // Товаров на странице
    if ( $count !== 'all' ) {
        
        $count = absint( $count );
        
        if ( $count > 0 ) {
            $history = array_slice( $history, 0, $count );
        }
        
        // С ограничением — одна страница
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