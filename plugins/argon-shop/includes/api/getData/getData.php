<?php
/**
 * Вспомогательные функции получения данных
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * ЧТО ОН ДЕЛАЕТ:
 * 1. Считает цифры в строке
 * 2. Проверяет заполненность полей
 * 3. Получает цены товаров и оптовые скидки
 * 4. Читает cookie
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ФУНКЦИЯ: считает цифры 0-9 в строке
 * 
 * @param string $str — входная строка
 * 
 * @return int Количество цифр
 */
function as_countDigits( $str ) {
    
    $str = (string) $str;
    
    return preg_match_all( '/[0-9]/', $str );
}

/**
 * Читает число целиком; некорректные и бесконечные значения возвращают 0.0.
 * Одиночный разделитель — десятичный; повторяющиеся должны группировать по три цифры.
 * Если есть оба разделителя, последний — десятичный. Поддерживается научная запись.
 * Синхронизируйте контракт с shared/format.js и tests/number-cases.json.
 *
 * @param mixed $value Число или строка.
 * @return float
 * @since 1.1.0
 */
function as_to_float( $value ) {
    if ( is_int( $value ) || is_float( $value ) ) {
        return is_finite( (float) $value ) ? (float) $value : 0.0;
    }
    if ( ! is_string( $value ) ) {
        return 0.0;
    }
    $value = preg_replace( '/[\x{0009}-\x{000D}\x{0020}\x{00A0}\x{1680}\x{2000}-\x{200A}\x{2028}\x{2029}\x{202F}\x{205F}\x{3000}\x{FEFF}]+/u', '', $value );
    if ( ! is_string( $value ) || ! preg_match( '/^([+-]?)([0-9.,]+)([eE][+-]?[0-9]+)?$/D', $value, $parts ) ) {
        return 0.0;
    }
    $mantissa = $parts[2];
    $commas = substr_count( $mantissa, ',' );
    $dots = substr_count( $mantissa, '.' );
    if ( $commas && $dots ) {
        $decimal = strrpos( $mantissa, ',' ) > strrpos( $mantissa, '.' ) ? ',' : '.';
        $group = ',' === $decimal ? '.' : ',';
        $pos = strrpos( $mantissa, $decimal );
        $integer = substr( $mantissa, 0, $pos );
        $fraction = substr( $mantissa, $pos + 1 );
        if ( ! preg_match( '/^[0-9]{1,3}(?:' . preg_quote( $group, '/' ) . '[0-9]{3})+$/D', $integer ) || ! preg_match( '/^[0-9]*$/D', $fraction ) ) {
            return 0.0;
        }
        $mantissa = str_replace( $group, '', $integer ) . '.' . $fraction;
    } elseif ( $commas > 1 || $dots > 1 ) {
        $group = $commas > 1 ? ',' : '.';
        if ( ! preg_match( '/^[0-9]{1,3}(?:' . preg_quote( $group, '/' ) . '[0-9]{3})+$/D', $mantissa ) ) {
            return 0.0;
        }
        $mantissa = str_replace( $group, '', $mantissa );
    } else {
        $mantissa = str_replace( ',', '.', $mantissa );
    }
    $normalized = $parts[1] . $mantissa . ( $parts[3] ?? '' );
    if ( ! preg_match( '/^[+-]?(?:[0-9]+(?:\.[0-9]*)?|\.[0-9]+)(?:[eE][+-]?[0-9]+)?$/D', $normalized ) ) {
        return 0.0;
    }
    $number = (float) $normalized;
    return is_finite( $number ) ? $number : 0.0;
}

/**
 * ФУНКЦИЯ: проверяет заполненность полей (при сохранении в админке)
 * 
 * @param array $fields — массив полей
 * 
 * @return array|false Массив заполненных полей или false
 */
function as_cheak_detailed_fields( $fields ) {
    
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
 * ФУНКЦИЯ: получает количество и итоговую стоимость массива товаров
 * 
 * Работает с любым массивом товаров: корзина, заказ в админке и т. д.
 * 
 * @param array $productsShoppingCart — массив товаров (корзина, заказ)
 * 
 * @return array|false Массив с количеством и итогом или false
 */
function as_getDataCart( $productsShoppingCart ) {
    
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
            
            $actualPrice = as_getActualPrice( 'cart', $productsShoppingCart, $key, $productPrice );
            
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
 * ФУНКЦИЯ: получает текущие цены для массива товаров
 * 
 * Работает с любым массивом товаров: корзина, заказ в админке и т. д.
 * 
 * @param array $productsShoppingCart — массив товаров (корзина, заказ)
 * 
 * @return array|false Массив текущих цен или false
 */
function as_getActualPriceAllProducts( $productsShoppingCart ) {
    
    if ( ! is_array( $productsShoppingCart ) || empty( $productsShoppingCart ) ) {
        return false;
    }
    
    $productsActualPrices = array();
    
    foreach ( $productsShoppingCart as $key => $value ) {
        
        $key = absint( $key );
        
        if ( 0 === $key ) {
            continue;
        }
        
        $productsActualPrices[ $key ] = as_getActualPrice( 'cart', $productsShoppingCart, $key );
    }
    
    return $productsActualPrices;
}

/**
 * ФУНКЦИЯ: получает текущую цену товара с учётом скидки
 * 
 * Работает с любым переданным массивом товаров
 * 
 * @param string $type                 — 'cart' или 'productPage'
 * @param array  $productsShoppingCart — массив товаров (корзина, заказ)
 * @param int    $productID            — ID товара
 * @param float  $productPrice         — цена товара
 * @param float  $productAmount        — количество
 * 
 * @return float Текущая цена
 */
function as_getActualPrice( $type, $productsShoppingCart, $productID, $productPrice = 0, $productAmount = 0 ) {
    
    $productID = absint( $productID );
    $productPrice = as_to_float( $productPrice );
    $productAmount = as_to_float( $productAmount );
    
    $productDiscount = as_getDiscount( $productID );
    $actualPrice = 0;
    
    if ( $productPrice !== 0.0 ) {
        $actualPrice = $productPrice;
    } elseif ( isset( $productsShoppingCart[ $productID ]['price'] ) ) {
        $actualPrice = as_to_float( $productsShoppingCart[ $productID ]['price'] );
    }
    
    // Проверяем, включены ли скидки
    if ( ! as_check_saleSteps() ) {
        return round( $actualPrice, 2 );
    }
    
    $totalPriceProduct = $productPrice * $productAmount;
    
    $actualStepDiscont = 0;
    
    if ( $type === 'cart' ) {
        $actualStepDiscont = as_getActualStepDiscont( $productID, $productsShoppingCart );
    } else {
        $actualStepDiscont = as_getActualStepDiscont( $productID, $productsShoppingCart, $totalPriceProduct );
    }
    
    if ( $actualStepDiscont && is_array( $productDiscount ) ) {
        
        // Ищем ступень по ЧИСЛОВОМУ значению, а не по строке названия: название термина может быть
        // записано как "1000,50" или "1 000", а $actualStepDiscont уже число с плавающей точкой.
        // Цена ступени читается через as_to_float(): floatval( "60,33" ) дал бы 60.
        foreach ( $productDiscount as $termId => $stepPrice ) {
            
            $term = get_term( absint( $termId ), 'wholesalePrice' );
            
            if ( ! $term || is_wp_error( $term ) ) {
                continue;
            }
            
            if ( abs( as_to_float( $term->name ) - (float) $actualStepDiscont ) < 0.00001 ) {
                $actualPrice = as_to_float( $stepPrice );
                break;
            }
        }
    }
    
    return round( $actualPrice, 2 );
}

/**
 * ФУНКЦИЯ: получает текущую ступень скидки
 * 
 * @param int   $productID            — ID товара
 * @param array $productsShoppingCart — товары в корзине
 * @param float $totalPriceProduct    — дополнительная сумма (для страницы товара)
 * 
 * @return float Текущая ступень скидки
 */
function as_getActualStepDiscont( $productID, $productsShoppingCart, $totalPriceProduct = 0 ) {
    
    $productID = absint( $productID );
    $totalPriceProduct = as_to_float( $totalPriceProduct );
    
    $totalPriceInShoppingCart = 0;
    $productDiscount = as_getDiscount( $productID );
    
    if ( $totalPriceProduct > 0 ) {
        $totalPriceInShoppingCart = $totalPriceProduct;
    }
    
    if ( is_array( $productsShoppingCart ) && ! empty( $productsShoppingCart ) ) {
        
        foreach ( $productsShoppingCart as $key => $value ) {
            
            $key = absint( $key );
            
            $productPriceSc = isset( $value['price'] ) ? as_to_float( $value['price'] ) : 0;
            $amountProductSc = isset( $value['amountProduct'] ) ? as_to_float( $value['amountProduct'] ) : 0;
            
            $productOFSPSum = $productPriceSc * $amountProductSc;
            
            $totalPriceInShoppingCart += $productOFSPSum;
        }
    }
    
    add_filter( 'get_terms_orderby', 'as_sort_terms_clause', 10, 3 );
    
    $actualStepDiscont = 0;
    
    if ( is_array( $productDiscount ) ) {
        
        foreach ( $productDiscount as $key => $value ) {
            
            $key = absint( $key );
            $term = get_term_by( 'id', $key, 'wholesalePrice' );
            
            if ( ! $term || is_wp_error( $term ) ) {
                continue;
            }
            
            // Название ступени может содержать десятичную запятую ("1000,50") или пробелы ("1 000")
            $stepDiscount = as_to_float( $term->name );
            
            // Побеждает наибольшая достигнутая ступень: порядок элементов в сохранённом массиве не важен
            if ( $totalPriceInShoppingCart >= $stepDiscount && $stepDiscount > $actualStepDiscont ) {
                $actualStepDiscont = $stepDiscount;
            }
        }
    }
    
    remove_filter( 'get_terms_orderby', 'as_sort_terms_clause', 10 );
    
    return $actualStepDiscont;
}

/**
 * ФУНКЦИЯ: получает массив оптовых цен товара
 * 
 * @param int $id — ID товара
 * 
 * @return array|false Массив оптовых цен или false
 */
function as_getDiscount( $id ) {
    
    $id = absint( $id );
    
    $wholesalePrice = get_post_meta( $id, '_wholesalePrice', true );
    
    if ( $wholesalePrice && is_array( $wholesalePrice ) ) {
        return $wholesalePrice;
    }
    
    return false;
}

/**
 * ФУНКЦИЯ: читает cookie
 * 
 * @param string $type — имя cookie
 * 
 * @return array|false Массив из JSON или false
 */
function as_getCookie( $type ) {
    
    if ( ! isset( $_COOKIE[ $type ] ) ) {
        return false;
    }
    
    // Убираем экранирование символов
    $cookie = stripslashes( $_COOKIE[ $type ] );
    
    // Восстанавливаем массив из JSON
    $productsShoppingCart = json_decode( $cookie, true );
    
    if ( ! is_array( $productsShoppingCart ) ) {
        return false;
    }
    
    // Корзина: cookie редактируется покупателем, поэтому мы доверяем
    // только ID товаров и количеству. Цены всегда берутся из базы данных.
    if ( 'productsShoppingCart' === $type ) {
        return as_sanitize_cart( $productsShoppingCart );
    }
    
    return $productsShoppingCart;
}

/**
 * ФУНКЦИЯ: цена товара из базы данных (мета-поле _price)
 * 
 * @param int $productID — ID товара
 * 
 * @return float Цена товара
 *
 * @since 1.1.0
 */
function as_get_product_price( $productID ) {
    
    // _price вводят вручную: "60,33", "1 000,5" и "1,234.56" читаются правильно
    $price = as_to_float( get_post_meta( absint( $productID ), '_price', true ) );
    
    // Хук для модулей (валюта, персональные цены и т. д.)
    return (float) apply_filters( 'argon_shop_product_price', (float) $price, absint( $productID ) );
}

/**
 * ФУНКЦИЯ: очищает корзину, пришедшую от клиента
 * 
 * Оставляет только опубликованные товары с положительным количеством.
 * Поле цены ВСЕГДА перезаписывается ценой из базы данных — цена из cookie
 * игнорируется, чтобы её нельзя было подменить в браузере.
 * Пересчёты оптовой скидки (getActualStepDiscont) тоже проходят здесь,
 * поэтому итог, используемый для ступени скидки, тоже нельзя подделать.
 * 
 * @param array $productsShoppingCart — массив товаров из cookie
 * 
 * Хук: фильтр argon_shop_cart ( array $cleanCart, array $rawCookieCart )
 * 
 * @return array Очищенный массив [ ID => [ amountProduct, price ] ]
 *
 * @since 1.1.0
 */
function as_sanitize_cart( $productsShoppingCart ) {
    
    $cleanCart = array();
    
    if ( ! is_array( $productsShoppingCart ) ) {
        return $cleanCart;
    }
    
    foreach ( $productsShoppingCart as $productID => $value ) {
        
        $productID = absint( $productID );
        
        if ( 0 === $productID || ! is_array( $value ) ) {
            continue;
        }
        
        $post = get_post( $productID );
        
        if ( ! $post || 'product' !== $post->post_type || 'publish' !== $post->post_status ) {
            continue;
        }
        
        $amountProduct = isset( $value['amountProduct'] ) ? floatval( $value['amountProduct'] ) : 0;
        
        if ( $amountProduct <= 0 ) {
            continue;
        }
        
        $cleanCart[ $productID ] = array(
            'amountProduct' => $amountProduct,
            'price'         => as_get_product_price( $productID ),
        );
    }
    
    return (array) apply_filters( 'argon_shop_cart', $cleanCart, $productsShoppingCart );
}

/**
 * ФУНКЦИЯ: получает CSS-класс активного пункта меню
 * 
 * @param int    $userID — ID пользователя
 * @param string $type   — тип формы
 * 
 * @return string|null CSS-класс или null
 */
function as_getClassActiveItem( $userID, $type ) {
    
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