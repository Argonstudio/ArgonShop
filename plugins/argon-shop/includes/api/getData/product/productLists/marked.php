<?php
/**
 * Получение отмеченных товаров (хиты продаж, новинки)
 *
 * @package Argon_Shop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * 1. Главная страница → блоки "Хиты продаж" и "Новинки"
 * 2. Страница категории → отмеченные товары категории
 * 3. Страница товара → отмеченные товары (без текущего)
 * 
 * ЧТО ДЕЛАЕТ:
 * Возвращает товары с мета-полем _hit или _newProduct
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ФУНКЦИЯ: Получение отмеченных товаров
 * 
 * @param int    $count — количество товаров
 * @param string $type  — тип мета-поля: '_hit' или '_newProduct'
 * 
 * @return WP_Query Объект с товарами
 */
function get_marked_products( $count, $type ) {
    
    $post_type = 'product';
    $taxonomy = 'catalog';
    
    // Валидация
    $count = absint( $count );
    
    if ( $count < 1 ) {
        $count = 12;
    }
    
    $type = sanitize_key( $type );
    
    $arg = array(
        'post_type'      => $post_type,
        'posts_per_page' => $count,
        'meta_query'     => array(
            array(
                'key'     => $type,
                'value'   => '',
                'compare' => '!=',
            ),
        ),
        'tax_query' => get_tax_query_marked_products( $post_type, $type, $taxonomy ),
    );
    
    // На странице товара не выводим его карточку
    if ( is_singular( $post_type ) ) {
        $current_id = get_the_ID();
        
        if ( $current_id ) {
            $arg['post__not_in'] = array( absint( $current_id ) );
        }
    }
    
    // На странице корзины исключаем уже добавленные товары
    if ( get_cartPageID() === get_queried_object_id() ) {
        
        $productsShoppingCart = getCookie( 'productsShoppingCart' );
        
        if ( isset( $productsShoppingCart ) && is_array( $productsShoppingCart ) && ! empty( $productsShoppingCart ) ) {
            
            $postNotIn = array();
            
            foreach ( $productsShoppingCart as $key => $value ) {
                $key_clean = absint( $key );
                
                if ( $key_clean > 0 ) {
                    $postNotIn[] = $key_clean;
                }
            }
            
            if ( ! empty( $postNotIn ) ) {
                $arg['post__not_in'] = $postNotIn;
            }
        }
    }
    
    $marked_products = new WP_Query( $arg );
    
    return $marked_products;
}

/**
 * ФУНКЦИЯ: Формирование tax_query для отмеченных товаров
 * 
 * @param string $post_type — тип записи
 * @param string $type      — тип мета-поля
 * @param string $taxonomy  — таксономия
 * 
 * @return array|string Массив tax_query или пустая строка
 */
function get_tax_query_marked_products( $post_type, $type, $taxonomy ) {
    
    $tax_query = '';
    
    $term_ids = get_terms_ids_marked_products( $post_type, $type, $taxonomy );
    
    if ( ! empty( $term_ids ) ) {
        $tax_query = array(
            array(
                'taxonomy' => $taxonomy,
                'field'    => 'id',
                'terms'    => $term_ids,
            ),
        );
    }
    
    return $tax_query;
}

/**
 * ФУНКЦИЯ: Формирование списка ID терминов
 * 
 * @param string $post_type — тип записи
 * @param string $type      — тип мета-поля
 * @param string $taxonomy  — таксономия
 * 
 * @return array|string Массив ID терминов или пустая строка
 */
function get_terms_ids_marked_products( $post_type, $type, $taxonomy ) {
    
    if ( is_singular( $post_type ) ) {
        
        $terms = get_the_terms( get_the_ID(), $taxonomy );
        
        if ( ! $terms || is_wp_error( $terms ) ) {
            return '';
        }
        
        $ids = array();
        
        foreach ( $terms as $term ) {
            $ids[] = absint( $term->term_id );
        }
        
        $term_ids = $ids;
        
    } elseif ( is_tax( $taxonomy ) ) {
        
        $queried_object = get_queried_object();
        
        if ( ! $queried_object || ! isset( $queried_object->term_id ) ) {
            return '';
        }
        
        $term_ids = array( absint( $queried_object->term_id ) );
        
        $arg = array(
            'post_type'      => $post_type,
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'     => $type,
                    'value'   => '',
                    'compare' => '!=',
                ),
            ),
            'tax_query' => array(
                array(
                    'taxonomy' => $taxonomy,
                    'field'    => 'id',
                    'terms'    => $term_ids,
                ),
            ),
        );
        
        $markedProducts = new WP_Query( $arg );
        $found_markedProducts = $markedProducts->found_posts;
        
        if ( $found_markedProducts == 0 ) {
            $term_ids = '';
        }
        
    } else {
        $term_ids = '';
    }
    
    return $term_ids;
}