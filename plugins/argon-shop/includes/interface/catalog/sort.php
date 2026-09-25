<?php
/**
 * AJAX-сортировка каталога товаров
 *
 * @package Argon_Shop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Страница категории каталога → выбор сортировки (дата, имя, цена)
 * URL: /catalog/категория/
 * 
 * ЧТО ДЕЛАЕТ:
 * 1. Сохраняет выбранный тип сортировки в куки
 * 2. Возвращает отсортированный список товаров
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_ajax_sortingCatalog', 'sortingCatalog_callback' );
add_action( 'wp_ajax_nopriv_sortingCatalog', 'sortingCatalog_callback' );

/**
 * ОБРАБОТЧИК: Сортировка каталога
 * 
 * ГДЕ: Страница категории → изменение сортировки
 * ЧТО ДЕЛАЕТ:
 * 1. Сохраняет выбранную сортировку в куки
 * 2. Возвращает отсортированные товары
 */
function sortingCatalog_callback() {
    
    // Проверка nonce
    check_ajax_referer( 'argon_shop_nonce', 'nonce' );
    
    // Валидация данных
    $typeSort = isset( $_POST['typeSort'] ) ? sanitize_text_field( wp_unslash( $_POST['typeSort'] ) ) : 'date';
    $howSorting = isset( $_POST['howSorting'] ) ? sanitize_text_field( wp_unslash( $_POST['howSorting'] ) ) : 'DESC';
    $catId = isset( $_POST['catId'] ) ? absint( $_POST['catId'] ) : 0;
    $pageNum = isset( $_POST['pageNum'] ) ? absint( $_POST['pageNum'] ) : 1;
    
    // Проверка допустимых значений
    if ( ! in_array( $typeSort, array( 'date', 'name', 'price' ), true ) ) {
        $typeSort = 'date';
    }
    
    $howSorting = strtoupper( $howSorting );
    
    if ( ! in_array( $howSorting, array( 'ASC', 'DESC' ), true ) ) {
        $howSorting = 'DESC';
    }
    
    if ( $pageNum < 1 ) {
        $pageNum = 1;
    }
    
    // Сохраняем сортировку в куки
    $cookieCatalogSort = array(
        'typeSort' => $typeSort,
        'howSort'  => $howSorting,
    );
    
    $cookieCatalogSort = wp_json_encode( $cookieCatalogSort );
    
    setcookie( 'AS_CatalogSorting', $cookieCatalogSort, time() + 1209600, COOKIEPATH, COOKIE_DOMAIN );
    
    // Получаем отсортированный список товаров
    // api/product/productList/catalog.php
    $productsList = get_sort_products( $typeSort, $howSorting, $catId, $pageNum );
    
    view_products_list( $productsList );
    
    wp_die();
}