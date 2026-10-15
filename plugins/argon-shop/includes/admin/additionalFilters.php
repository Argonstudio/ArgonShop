<?php
/**
 * Дополнительные фильтры заказов в админ-панели
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Админка WordPress → Заказы → Список заказов
 * URL: /wp-admin/edit.php?post_type=shoporder
 * 
 * ЧТО ОН ДЕЛАЕТ:
 * 1. Добавляет выпадающий список для фильтрации заказов по статусу
 * 2. Применяет выбранный фильтр к запросу
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ФУНКЦИЯ: выводит выпадающий список фильтра по статусу заказа
 * 
 * ГДЕ: страница списка заказов → над таблицей
 * ЧТО ДЕЛАЕТ: выводит select с таксономией statusorders
 */
function as_restrict_posts_by_money() {
    
    global $typenow;
    $post_type = 'shoporder';
    $taxonomy = 'statusorders';
    
    $countPosts = wp_count_posts( 'shoporder' );
    
    if ( $countPosts->publish ) {
        
        if ( $typenow == $post_type ) {
            
            $selected = isset( $_GET[ $taxonomy ] ) ? absint( $_GET[ $taxonomy ] ) : '';
            
            $info_taxonomy = get_taxonomy( $taxonomy );
            
            wp_dropdown_categories( array(
                'show_option_all' => __( 'All statuses', 'argon-shop' ),
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

add_action( 'restrict_manage_posts', 'as_restrict_posts_by_money' );

/**
 * ФУНКЦИЯ: преобразует ID термина в слаг для фильтрации
 * 
 * ГДЕ: автоматически при запросе списка заказов
 * ЧТО ДЕЛАЕТ: когда пользователь выбирает статус из списка,
 *             WordPress передаёт ID термина. Эта функция преобразует
 *             ID в слаг, чтобы WP_Query работал корректно.
 */
function as_convert_id_to_term_in_query( $query ) {
    
    global $pagenow;
    
    $post_type = 'shoporder';
    $taxonomy = 'statusorders';
    
    $q_vars = &$query->query_vars;
    
    if ( $pagenow == 'edit.php' && isset( $q_vars['post_type'] ) && $q_vars['post_type'] == $post_type && isset( $q_vars[ $taxonomy ] ) && is_numeric( $q_vars[ $taxonomy ] ) && $q_vars[ $taxonomy ] != 0 ) {
        
        $term_id = absint( $q_vars[ $taxonomy ] );
        $term = get_term_by( 'id', $term_id, $taxonomy );
        
        // Убеждаемся, что термин найден и не является ошибкой
        if ( $term && ! is_wp_error( $term ) ) {
            $q_vars[ $taxonomy ] = $term->slug;
        }
    }
}

add_filter( 'parse_query', 'as_convert_id_to_term_in_query' );