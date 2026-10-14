<?php
/**
 * Data retrieval helper functions
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * WHAT IT DOES:
 * 1. Counts digits in a string
 * 2. Checks fields for being filled in
 * 3. Gets product prices and wholesale discounts
 * 4. Reads cookies
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FUNCTION: Count the digits 0-9 in a string
 * 
 * @param string $str — input string
 * 
 * @return int Number of digits
 */
function as_countDigits( $str ) {
    
    $str = (string) $str;
    
    return preg_match_all( '/[0-9]/', $str );
}

/**
 * Parse a complete human-entered number; malformed/non-finite values return 0.0.
 * A single separator is decimal; repeated separators must group three digits.
 * With both separators, the last is decimal. Scientific notation is supported.
 * Keep this contract in sync with shared/format.js and tests/number-cases.json.
 *
 * @param mixed $value Number or string.
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
 * FUNCTION: Check fields for being filled in (when saving in the admin)
 * 
 * @param array $fields — array of fields
 * 
 * @return array|false Array of filled fields or false
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
 * FUNCTION: Get the quantity and total cost of the given product array
 * 
 * Works with any product array: cart, admin order, etc.
 * 
 * @param array $productsShoppingCart — product array (cart, order)
 * 
 * @return array|false Array with the quantity and total or false
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
 * FUNCTION: Get current prices for the given product array
 * 
 * Works with any product array: cart, admin order, etc.
 * 
 * @param array $productsShoppingCart — product array (cart, order)
 * 
 * @return array|false Array of current prices or false
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
 * FUNCTION: Get the current product price including the discount
 * 
 * Works with any given product array
 * 
 * @param string $type                 — 'cart' or 'productPage'
 * @param array  $productsShoppingCart — product array (cart, order)
 * @param int    $productID            — product ID
 * @param float  $productPrice         — product price
 * @param float  $productAmount        — quantity
 * 
 * @return float Current price
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
    
    // Check whether discounts are enabled
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
        
        // Find the step by its NUMERIC value, not by the name string: the term name may be
        // written as "1000,50" or "1 000", while $actualStepDiscont is already a float.
        // The step price is read with as_to_float(): floatval( "60,33" ) would give 60.
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
 * FUNCTION: Get the current discount step
 * 
 * @param int   $productID            — product ID
 * @param array $productsShoppingCart — products in the cart
 * @param float $totalPriceProduct    — extra amount (for the product page)
 * 
 * @return float Current discount step
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
            
            // The step name may contain a decimal comma ("1000,50") or spaces ("1 000")
            $stepDiscount = as_to_float( $term->name );
            
            // The biggest reached step wins: the order of the stored array does not matter
            if ( $totalPriceInShoppingCart >= $stepDiscount && $stepDiscount > $actualStepDiscont ) {
                $actualStepDiscont = $stepDiscount;
            }
        }
    }
    
    remove_filter( 'get_terms_orderby', 'as_sort_terms_clause', 10 );
    
    return $actualStepDiscont;
}

/**
 * FUNCTION: Get the array of wholesale prices of a product
 * 
 * @param int $id — product ID
 * 
 * @return array|false Array of wholesale prices or false
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
 * FUNCTION: Read a cookie
 * 
 * @param string $type — cookie name
 * 
 * @return array|false Array from JSON or false
 */
function as_getCookie( $type ) {
    
    if ( ! isset( $_COOKIE[ $type ] ) ) {
        return false;
    }
    
    // Remove character escaping
    $cookie = stripslashes( $_COOKIE[ $type ] );
    
    // Restore the array from JSON
    $productsShoppingCart = json_decode( $cookie, true );
    
    if ( ! is_array( $productsShoppingCart ) ) {
        return false;
    }
    
    // Cart: the cookie is edited by the buyer, so we trust
    // only product IDs and quantities. Prices always come from the database.
    if ( 'productsShoppingCart' === $type ) {
        return as_sanitize_cart( $productsShoppingCart );
    }
    
    return $productsShoppingCart;
}

/**
 * FUNCTION: Product price from the database (the _price meta field)
 * 
 * @param int $productID — product ID
 * 
 * @return float Product price
 *
 * @since 1.1.0
 */
function as_get_product_price( $productID ) {
    
    // _price is typed by hand: "60,33", "1 000,5" and "1,234.56" are all read correctly
    $price = as_to_float( get_post_meta( absint( $productID ), '_price', true ) );
    
    // Hook for modules (currency, personal prices, etc.)
    return (float) apply_filters( 'argon_shop_product_price', (float) $price, absint( $productID ) );
}

/**
 * FUNCTION: Clean the cart that came from the client
 * 
 * Keeps only published products with a positive quantity.
 * The price field is ALWAYS overwritten with the price from the database — the price from the cookie
 * is ignored so that it cannot be tampered with in the browser.
 * Wholesale discount recalculations (getActualStepDiscont) also go through here,
 * so the total used for the discount step cannot be faked either.
 * 
 * @param array $productsShoppingCart — product array from the cookie
 * 
 * Hook: filter argon_shop_cart ( array $cleanCart, array $rawCookieCart )
 * 
 * @return array Cleaned array [ ID => [ amountProduct, price ] ]
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
 * FUNCTION: Get the CSS class of the active menu item
 * 
 * @param int    $userID — user ID
 * @param string $type   — form type
 * 
 * @return string|null CSS class or null
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