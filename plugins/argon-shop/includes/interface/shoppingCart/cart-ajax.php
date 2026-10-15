<?php
/**
 * Корзина — AJAX-обработчики (добавление, количество, удаление, оптовая цена)
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 *
 * ГДЕ ТЕСТИРОВАТЬ:
 * 1. Страница товара → кнопка «В корзину» (addProducts)
 * 2. Страница товара → смена количества → пересчёт цены (wholesalePrice)
 * 3. Корзина → смена количества → пересчёт цены (amountProducts)
 * 4. Корзина → удаление товара (deleteProducts)
 * 5. Корзина → очистка корзины (deleteAllProducts)
 *
 * Автотест: tests/smoke-test.php, раздел «1. Корзина».
 *
 * Корзина хранится в cookie productsShoppingCart. Каждое чтение идёт через
 * as_getCookie( 'productsShoppingCart' ) → as_sanitize_cart(): цены из cookie
 * игнорируются и берутся из базы данных.
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_ajax_wholesalePrice_shoppingCart', 'as_wholesalePrice_shoppingCart_callback' );
add_action( 'wp_ajax_nopriv_wholesalePrice_shoppingCart', 'as_wholesalePrice_shoppingCart_callback' );

add_action( 'wp_ajax_addProducts_shoppingCart', 'as_addProducts_shoppingCart_callback' );
add_action( 'wp_ajax_nopriv_addProducts_shoppingCart', 'as_addProducts_shoppingCart_callback' );

add_action( 'wp_ajax_amountProducts_shoppingCart', 'as_amountProducts_shoppingCart_callback' );
add_action( 'wp_ajax_nopriv_amountProducts_shoppingCart', 'as_amountProducts_shoppingCart_callback' );

add_action( 'wp_ajax_deleteProducts_shoppingCart', 'as_deleteProducts_shoppingCart_callback' );
add_action( 'wp_ajax_nopriv_deleteProducts_shoppingCart', 'as_deleteProducts_shoppingCart_callback' );

add_action( 'wp_ajax_deleteAllProducts_shoppingCart', 'as_deleteAllProducts_shoppingCart_callback' );
add_action( 'wp_ajax_nopriv_deleteAllProducts_shoppingCart', 'as_deleteAllProducts_shoppingCart_callback' );

/**
 * ОБРАБОТЧИК: оптовая цена
 * ГДЕ: страница товара → смена количества
 */
function as_wholesalePrice_shoppingCart_callback() {
    
    check_ajax_referer( 'argon_shop_nonce', 'nonce' );
    
    $type = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
    $productID = isset( $_POST['productID'] ) ? absint( $_POST['productID'] ) : 0;
    $productAmount = isset( $_POST['amountProduct'] ) ? floatval( $_POST['amountProduct'] ) : 0;
    
    if ( 0 === $productID ) {
        wp_die( __( 'Invalid product ID', 'argon-shop' ) );
    }
    
    $productDiscount = as_getDiscount( $productID );
    
    if ( $type === 'get' && ! $productDiscount ) {
        echo 'none';
        wp_die();
    }
    
    $productsShoppingCart = as_getCookie( 'productsShoppingCart' );
    
    $productPrice = as_get_product_price( $productID );
    
    $productPrice = as_getActualPrice( 'productPage', $productsShoppingCart, $productID, $productPrice, $productAmount );
    
    $totalPriceProduct = $productPrice * $productAmount;
    
    echo esc_html( $totalPriceProduct );
    
    wp_die();
}

/**
 * ОБРАБОТЧИК: смена количества товара в корзине
 * ГДЕ: страница корзины → смена количества
 */
function as_amountProducts_shoppingCart_callback() {
    
    check_ajax_referer( 'argon_shop_nonce', 'nonce' );
    
    $productID = isset( $_POST['productID'] ) ? absint( $_POST['productID'] ) : 0;
    $productAmount = isset( $_POST['productAmount'] ) ? floatval( $_POST['productAmount'] ) : 0;
    
    if ( 0 === $productID ) {
        wp_die( __( 'Invalid product ID', 'argon-shop' ) );
    }
    
    if ( $productAmount <= 0 ) {
        $productAmount = 1;
    }
    
    $productsShoppingCart = as_getCookie( 'productsShoppingCart' );
    
    if ( ! is_array( $productsShoppingCart ) ) {
        $productsShoppingCart = array();
    }
    
    $productsShoppingCart[ $productID ]['amountProduct'] = $productAmount;
    
    $productsShoppingCartJson = wp_json_encode( $productsShoppingCart );
    
    setcookie( 'productsShoppingCart', $productsShoppingCartJson, time() + 1209600, COOKIEPATH, COOKIE_DOMAIN );
    
    $weight = get_post_meta( $productID, '_weight', true );
    $weight = str_replace( ',', '.', $weight );
    $weight = (float) $weight;
    
    $actualPriceAllProducts = as_getActualPriceAllProducts( $productsShoppingCart );
    
    $forFront = array();
    
    if ( $weight ) $forFront['weight'] = $weight;
    if ( $actualPriceAllProducts ) $forFront['actualPrices'] = $actualPriceAllProducts;
    $forFront['productsShoppingCart'] = $productsShoppingCart;
    
    $forFront = wp_json_encode( $forFront );
    
    echo $forFront;
    
    wp_die();
}

/**
 * ОБРАБОТЧИК: очистка корзины
 * ГДЕ: страница корзины → кнопка «Очистить корзину»
 */
function as_deleteAllProducts_shoppingCart_callback() {
    
    check_ajax_referer( 'argon_shop_nonce', 'nonce' );
    
    setcookie( 'productsShoppingCart', '', time() - 1209600, COOKIEPATH, COOKIE_DOMAIN );
    
    wp_die();
}

/**
 * ОБРАБОТЧИК: удаление товара из корзины
 * ГДЕ: страница корзины → кнопка удаления товара
 */
function as_deleteProducts_shoppingCart_callback() {
    
    check_ajax_referer( 'argon_shop_nonce', 'nonce' );
    
    $productID = isset( $_POST['productID'] ) ? absint( $_POST['productID'] ) : 0;
    
    if ( 0 === $productID ) {
        wp_die( __( 'Invalid product ID', 'argon-shop' ) );
    }
    
    $productsShoppingCart = as_getCookie( 'productsShoppingCart' );
    
    if ( ! is_array( $productsShoppingCart ) ) {
        $productsShoppingCart = array();
    }
    
    unset( $productsShoppingCart[ $productID ] );
    
    $productsShoppingCartJson = wp_json_encode( $productsShoppingCart );
    
    setcookie( 'productsShoppingCart', $productsShoppingCartJson, time() + 1209600, COOKIEPATH, COOKIE_DOMAIN );
    
    if ( ! $productsShoppingCart ) {
        echo 'false';
        wp_die();
    }
    
    $actualPriceAllProducts = as_getActualPriceAllProducts( $productsShoppingCart );
    
    $actualPriceAllProducts = wp_json_encode( $actualPriceAllProducts );
    
    echo $actualPriceAllProducts;
    
    wp_die();
}

/**
 * ОБРАБОТЧИК: добавление товара в корзину
 * ГДЕ: страница товара → кнопка «В корзину» / «Купить»
 */
function as_addProducts_shoppingCart_callback() {
    
    check_ajax_referer( 'argon_shop_nonce', 'nonce' );
    
    $productID = isset( $_POST['productID'] ) ? absint( $_POST['productID'] ) : 0;
    $amountProduct = isset( $_POST['amountProduct'] ) ? absint( $_POST['amountProduct'] ) : 1;
    
    if ( 0 === $productID ) {
        wp_die( __( 'Invalid product ID', 'argon-shop' ) );
    }
    
    if ( $amountProduct <= 0 ) {
        $amountProduct = 1;
    }
    
    $productPrice = as_get_product_price( $productID );
    
    $data = array(
        'amountProduct' => $amountProduct,
        'price'         => $productPrice,
    );
    
    $productsShoppingCart = array();
    
    if ( as_getCookie( 'productsShoppingCart' ) ) {
        $productsShoppingCart = as_getCookie( 'productsShoppingCart' );
        
        if ( ! is_array( $productsShoppingCart ) ) {
            $productsShoppingCart = array();
        }
    }
    
    if ( isset( $productsShoppingCart[ $productID ] ) && $productsShoppingCart[ $productID ] ) {
        $data['amountProduct'] += $productsShoppingCart[ $productID ]['amountProduct'];
    }
    
    $productsShoppingCart[ $productID ] = $data;
    
    $productsShoppingCartJson = wp_json_encode( $productsShoppingCart );
    
    setcookie( 'productsShoppingCart', $productsShoppingCartJson, time() + 1209600, COOKIEPATH, COOKIE_DOMAIN );
    
    $dataCart = as_getDataCart( $productsShoppingCart );
    
    $forFront = array(
        'amountProduct' => $data['amountProduct'],
    );
    
    if ( $dataCart ) {
        $forFront['productAmountCart'] = $dataCart['productAmountCart'];
        $forFront['totalPrice'] = $dataCart['totalPrice'];
    }
    
    $forFront = wp_json_encode( $forFront );
    
    echo $forFront;
    
    wp_die();
}
