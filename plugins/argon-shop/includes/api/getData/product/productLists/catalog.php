<?php
/**
 * Получение отсортированного списка товаров каталога
 *
 * @package Argon_Shop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Каталог на сайте → страница категории → сортировка товаров
 * URL: /catalog/категория/
 * 
 * ЧТО ДЕЛАЕТ:
 * Возвращает объект WP_Query с товарами, отсортированными по дате, имени или цене
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ФУНКЦИЯ: Получение отсортированного списка товаров
 * 
 * @param string $typeSort   — тип сортировки: 'date', 'name', 'price'
 * @param string $howSorting — направление: 'ASC' или 'DESC'
 * @param int    $catId      — ID категории каталога
 * @param int    $pageNum    — номер страницы пагинации
 * 
 * @return WP_Query Объект с результатами
 */
function get_sort_products( $typeSort, $howSorting, $catId, $pageNum ) {
    
    $post_type = 'product';
    $taxonomy = 'catalog';
    
    // Валидация
    $catId = absint( $catId );
    $pageNum = absint( $pageNum );
    
    if ( $pageNum < 1 ) {
        $pageNum = 1;
    }
    
    // Валидация направления сортировки
    $howSorting = strtoupper( $howSorting );
    
    if ( ! in_array( $howSorting, array( 'ASC', 'DESC' ), true ) ) {
        $howSorting = 'DESC';
    }
    
    $arg = array(
        'post_type'      => $post_type,
        'post_status'    => 'publish',
        'paged'          => $pageNum,
        'posts_per_page' => get_option( 'posts_per_page', 12 ),
        'tax_query'      => array(
            array(
                'taxonomy' => $taxonomy,
                'field'    => 'id',
                'terms'    => $catId,
            ),
        ),
        'order'          => $howSorting,
    );
    
    // Валидация типа сортировки
    switch($typeSort){
	    
	    case "date":
	        // Сортировка по дате + ID как вторичный критерий.
	        // Фиксирует порядок товаров с одинаковой датой добавления.
	        $arg["orderby"] = array(
	            'date' => $howSorting,
	            'ID'   => 'DESC',
	        );
	        break;
	        
	    case "name":
	        // Сортировка по названию + ID как вторичный критерий.
	        $arg["orderby"] = array(
	            'title' => $howSorting,
	            'ID'   => 'DESC',
	        );
	        break;
	        
	    case "price":
	        // Сортировка по цене. Направление приходит из общего 'order'.
	        $arg["orderby"]  = "meta_value_num";
	        $arg['meta_key'] = '_price';
	        break;
	}
    
    $productsList = new WP_Query( $arg );
    
    return $productsList;
}