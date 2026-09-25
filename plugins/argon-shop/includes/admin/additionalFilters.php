<?php
/**
 * Дополнительные фильтры для заказов в админ-панели
 *
 * @package Argon_Shop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * WordPress Админка → Заказы → Список заказов
 * URL: /wp-admin/edit.php?post_type=shoporder
 * 
 * ЧТО ДЕЛАЕТ:
 * 1. Добавляет выпадающий список для фильтрации заказов по статусу
 * 2. Обрабатывает выбранный фильтр при запросе
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ФУНКЦИЯ: Вывод выпадающего списка фильтра по статусам заказов
 * 
 * ГДЕ: Страница списка заказов → над таблицей
 * ЧТО ДЕЛАЕТ: Выводит select с таксономией statusorders
 */
function restrict_posts_by_money() {
    
    global $typenow;
    $post_type = 'shoporder';
    $taxonomy = 'statusorders';
    
    $countPosts = wp_count_posts( 'shoporder' );
    
    if ( $countPosts->publish ) {
        
        if ( $typenow == $post_type ) {
            
            $selected = isset( $_GET[ $taxonomy ] ) ? absint( $_GET[ $taxonomy ] ) : '';
            
            $info_taxonomy = get_taxonomy( $taxonomy );
            
            wp_dropdown_categories( array(
                'show_option_all' => __( 'Все статусы' ),
                'taxonomy'        => $taxonomy,
                'name'            => $taxonomy,
                'orderby'         => 'name',
                'selected'        => $selected,
                'show_count'      => true,
                'hide_empty'      => true,
            ) );
        }
    }
}

add_action( 'restrict_manage_posts', 'restrict_posts_by_money' );

/**
 * ФУНКЦИЯ: Преобразование ID термина в slug для фильтрации
 * 
 * ГДЕ: Автоматически при запросе списка заказов
 * ЧТО ДЕЛАЕТ: Когда пользователь выбирает статус из выпадающего списка,
 *             WordPress передаёт ID термина. Эта функция преобразует
 *             ID в slug для корректной работы WP_Query.
 */
function convert_id_to_term_in_query( $query ) {
    
    global $pagenow;
    
    $post_type = 'shoporder';
    $taxonomy = 'statusorders';
    
    $q_vars = &$query->query_vars;
    
    if ( $pagenow == 'edit.php' && isset( $q_vars['post_type'] ) && $q_vars['post_type'] == $post_type && isset( $q_vars[ $taxonomy ] ) && is_numeric( $q_vars[ $taxonomy ] ) && $q_vars[ $taxonomy ] != 0 ) {
        
        $term_id = absint( $q_vars[ $taxonomy ] );
        $term = get_term_by( 'id', $term_id, $taxonomy );
        
        // Проверяем, что термин найден и не является ошибкой
        if ( $term && ! is_wp_error( $term ) ) {
            $q_vars[ $taxonomy ] = $term->slug;
        }
    }
}

add_filter( 'parse_query', 'convert_id_to_term_in_query' );