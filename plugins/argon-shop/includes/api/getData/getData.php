<?php
/**
 * Вспомогательные функции получения данных
 *
 * @package Argon_Shop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ЧТО ДЕЛАЕТ:
 * 1. Подсчёт цифр в строке
 * 2. Проверка полей на заполнение
 * 3. Получение цен на товары и оптовых скидок
 * 4. Получение кук
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ФУНКЦИЯ: Подсчёт количества цифр 0-9 в строке
 * 
 * @param string $str — входная строка
 * 
 * @return int Количество цифр
 */
function countDigits( $str ) {
    
    $str = (string) $str;
    
    return preg_match_all( '/[0-9]/', $str );
}

/**
 * ФУНКЦИЯ: Проверка полей на заполнение (при сохранении в админке)
 * 
 * @param array $fields — массив полей
 * 
 * @return array|false Массив заполненных полей или false
 */
function cheak_detailed_fields( $fields ) {
    
    if ( ! is_array( $fields ) ) {
        return false;
    }
    
    $completeFields = array();
    
    foreach ( $fields as $key => $value ) {
        
        if ( ! empty( $value ) ) {
            $completeFields[ $key ] = $value;
        }
    }
    
    if ( count( $completeFields ) ) {
        return $completeFields;
    }
    
    return false;
}



/**
 * ФУНКЦИЯ: Получение количества и общей стоимости для переданного массива товаров
 * 
 * Работает с любым массивом товаров: корзина, заказ из админки, и т.д.
 * 
 * @param array $productsShoppingCart — массив товаров (корзина, заказ)
 * 
 * @return array|false Массив с количеством и суммой или false
 */
function getDataCart( $productsShoppingCart ) {
    
    $productAmountCart = 0;
    $totalPrice = 0;
    
    if ( is_array( $productsShoppingCart ) && ! empty( $productsShoppingCart ) ) {
        
        foreach ( $productsShoppingCart as $key => $value ) {
            
            $key = absint( $key );
            
            if ( 0 === $key ) {
                continue;
            }
            
            $productAmountCart++;
            
            $productPrice = isset( $value['price'] ) ? floatval( $value['price'] ) : 0;
            $productAmount = isset( $value['amountProduct'] ) ? floatval( $value['amountProduct'] ) : 0;
            
            $actualPrice = getActualPrice( 'cart', $productsShoppingCart, $key, $productPrice );
            
            $totalPrice += $actualPrice * $productAmount;
        }
    }
    
    if ( $productAmountCart == 0 ) {
        return false;
    }
    
    $dataCart = array(
        'productAmountCart' => $productAmountCart,
        'totalPrice'        => $totalPrice,
    );
    
    return $dataCart;
}

/**
 * ФУНКЦИЯ: Получение актуальных цен для переданного массива товаров
 * 
 * Работает с любым массивом товаров: корзина, заказ из админки, и т.д.
 * 
 * @param array $productsShoppingCart — массив товаров (корзина, заказ)
 * 
 * @return array|false Массив актуальных цен или false
 */
function getActualPriceAllProducts( $productsShoppingCart ) {
    
    if ( ! is_array( $productsShoppingCart ) || empty( $productsShoppingCart ) ) {
        return false;
    }
    
    $productsActualPrices = array();
    
    foreach ( $productsShoppingCart as $key => $value ) {
        
        $key = absint( $key );
        
        if ( 0 === $key ) {
            continue;
        }
        
        $productsActualPrices[ $key ] = getActualPrice( 'cart', $productsShoppingCart, $key );
    }
    
    return $productsActualPrices;
}

/**
 * ФУНКЦИЯ: Получение актуальной цены товара с учётом скидки
 * 
 * Работает с любым переданным массивом товаров
 * 
 * @param string $type                 — 'cart' или 'productPage'
 * @param array  $productsShoppingCart — массив товаров (корзина, заказ)
 * @param int    $productID            — ID товара
 * @param float  $productPrice         — цена товара
 * @param float  $productAmount        — количество
 * 
 * @return float Актуальная цена
 */
function getActualPrice( $type, $productsShoppingCart, $productID, $productPrice = 0, $productAmount = 0 ) {
    
    $productID = absint( $productID );
    $productPrice = floatval( $productPrice );
    $productAmount = floatval( $productAmount );
    
    $productDiscount = getDiscount( $productID );
    $actualPrice = 0;
    
    if ( $productPrice !== 0.0 ) {
        $actualPrice = $productPrice;
    } elseif ( isset( $productsShoppingCart[ $productID ]['price'] ) ) {
        $actualPrice = floatval( $productsShoppingCart[ $productID ]['price'] );
    }
    
    // Проверка на активацию скидок
    if ( ! check_saleSteps() ) {
        return round( $actualPrice, 2 );
    }
    
    $totalPriceProduct = $productPrice * $productAmount;
    
    $actualStepDiscont = 0;
    
    if ( $type === 'cart' ) {
        $actualStepDiscont = getActualStepDiscont( $productID, $productsShoppingCart );
    } else {
        $actualStepDiscont = getActualStepDiscont( $productID, $productsShoppingCart, $totalPriceProduct );
    }
    
    if ( $actualStepDiscont && is_array( $productDiscount ) ) {
        
        $termActualStep = get_term_by( 'name', $actualStepDiscont, 'wholesalePrice' );
        
        if ( $termActualStep && ! is_wp_error( $termActualStep ) && isset( $productDiscount[ $termActualStep->term_id ] ) ) {
            $actualPrice = floatval( $productDiscount[ $termActualStep->term_id ] );
        }
    }
    
    $actualPrice = str_replace( ',', '.', (string) $actualPrice );
    $actualPrice = (float) $actualPrice;
    
    return round( $actualPrice, 2 );
}

/**
 * ФУНКЦИЯ: Получение текущего шага скидки
 * 
 * @param int   $productID            — ID товара
 * @param array $productsShoppingCart — товары в корзине
 * @param float $totalPriceProduct    — доп. сумма (для страницы товара)
 * 
 * @return float Текущий шаг скидки
 */
function getActualStepDiscont( $productID, $productsShoppingCart, $totalPriceProduct = 0 ) {
    
    $productID = absint( $productID );
    $totalPriceProduct = floatval( $totalPriceProduct );
    
    $totalPriceInShoppingCart = 0;
    $productDiscount = getDiscount( $productID );
    
    if ( $totalPriceProduct > 0 ) {
        $totalPriceInShoppingCart = $totalPriceProduct;
    }
    
    if ( is_array( $productsShoppingCart ) && ! empty( $productsShoppingCart ) ) {
        
        foreach ( $productsShoppingCart as $key => $value ) {
            
            $key = absint( $key );
            
            $productPriceSc = isset( $value['price'] ) ? floatval( $value['price'] ) : 0;
            $amountProductSc = isset( $value['amountProduct'] ) ? floatval( $value['amountProduct'] ) : 0;
            
            $productOFSPSum = $productPriceSc * $amountProductSc;
            
            $totalPriceInShoppingCart += $productOFSPSum;
        }
    }
    
    add_filter( 'get_terms_orderby', 'sort_terms_clause', 10, 3 );
    
    $actualStepDiscont = 0;
    
    if ( is_array( $productDiscount ) ) {
        
        foreach ( $productDiscount as $key => $value ) {
            
            $key = absint( $key );
            $term = get_term_by( 'id', $key, 'wholesalePrice' );
            
            if ( ! $term || is_wp_error( $term ) ) {
                continue;
            }
            
            $stepDiscount = floatval( $term->name );
            
            if ( $totalPriceInShoppingCart >= $stepDiscount ) {
                $actualStepDiscont = $stepDiscount;
            }
        }
    }
    
    remove_filter( 'get_terms_orderby', 'sort_terms_clause', 10 );
    
    return $actualStepDiscont;
}

/**
 * ФУНКЦИЯ: Получение массива оптовых цен товара
 * 
 * @param int $id — ID товара
 * 
 * @return array|false Массив оптовых цен или false
 */
function getDiscount( $id ) {
    
    $id = absint( $id );
    
    $wholesalePrice = get_post_meta( $id, '_wholesalePrice', true );
    
    if ( $wholesalePrice && is_array( $wholesalePrice ) ) {
        return $wholesalePrice;
    }
    
    return false;
}

/**
 * ФУНКЦИЯ: Получение кук
 * 
 * @param string $type — имя куки
 * 
 * @return array|false Массив из JSON или false
 */
function getCookie( $type ) {
    
    if ( ! isset( $_COOKIE[ $type ] ) ) {
        return false;
    }
    
    // Удаляем экранирование символов
    $cookie = stripslashes( $_COOKIE[ $type ] );
    
    // Восстанавливаем массив из JSON
    $productsShoppingCart = json_decode( $cookie, true );
    
    if ( ! is_array( $productsShoppingCart ) ) {
        return false;
    }
    
    return $productsShoppingCart;
}

/**
 * ФУНКЦИЯ: Получение CSS-класса активного пункта меню
 * 
 * @param int    $userID — ID пользователя
 * @param string $type   — тип формы
 * 
 * @return string|null CSS-класс или null
 */
function getClassActiveItem( $userID, $type ) {
    
    $userID = absint( $userID );
    $data = get_user_meta( $userID, 'accountData', true );
    
    if ( $type === 'cartQuickOrder' && ! $data ) {
        return 'itemControlPanelActive';
    } elseif ( $type === 'cartPerson' && ! empty( $data['accountDetail'] ) && empty( $data['accountLegalDetail'] ) ) {
        return 'itemControlPanelActive';
    } elseif ( $type === 'cartLegalPerson' && ! empty( $data['accountLegalDetail'] ) ) {
        return 'itemControlPanelActive';
    }
    
    return null;
}