<?php
/**
 * AJAX features of the order page in the admin panel
 * 
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * WHERE TO TEST:
 * WordPress Admin → Orders → Edit a specific order
 * URL: /wp-admin/post.php?post=ORDER_ID&action=edit
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Prints scripts in the admin footer
 * Creates the nonce and defines global JS functions:
 * - searchSuitableProduct()
 * - addProduct()
 * - addFields()
 */
add_action( 'admin_print_footer_scripts', 'as_ajax_order_products_scripts', 99 );
function as_ajax_order_products_scripts() {
    $nonce_order = wp_create_nonce( 'argon_shop_order_nonce' );
    ?>
    <script>
        (function(){
            
            // The nonce is declared globally — it is used in orders.js
            // and other scripts of the order page.
            window.argonShopOrderNonce = '<?php echo esc_js( $nonce_order ); ?>';
            
            'use strict';
            
            // ============================================
            // VARIABLES
            // ============================================
            
            const nonce   = '<?php echo esc_js( $nonce_order ); ?>';
            const ajaxUrl = ( typeof ajaxurl !== 'undefined' ) ? ajaxurl : '/wp-admin/admin-ajax.php';
            
            
            // ============================================
            // HELPER FUNCTIONS
            // ============================================
            
            /**
             * POST request via fetch.
             * Automatically serializes arrays in the WP format: key[]=val1&key[]=val2
             * 
             * @param {string}   action    — AJAX action name
             * @param {Object}   params    — request parameters
             * @param {Function} onSuccess — success callback (receives the response text)
             * @param {Function} onError   — error callback
             */
            const postAjax = ( action, params, onSuccess, onError ) => {
                
                const body = new URLSearchParams();
                
                body.append( 'action', action );
                body.append( 'nonce', nonce );
                
                Object.keys( params ).forEach( key => {
                    
                    const value = params[ key ];
                    
                    if ( Array.isArray( value ) ) {
                        value.forEach( item => body.append( key + '[]', item ) );
                    } else if ( value !== undefined && value !== null ) {
                        body.append( key, value );
                    }
                });
                
                fetch( ajaxUrl, {
                    method:      'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                    },
                    body: body
                })
                .then( response => {
                    if ( ! response.ok ) {
                        throw new Error( 'HTTP ' + response.status );
                    }
                    return response.text();
                })
                .then( onSuccess )
                .catch( error => {
                    console.error( 'AJAX error:', error );
                    if ( typeof onError === 'function' ) {
                        onError( error );
                    }
                });
            };
            
            
            /**
             * Converts a jQuery object or a native DOM element to a native element.
             * Needed for compatibility when the function is called from old code.
             * 
             * @param  {Element|jQuery|string} el
             * @return {Element|null}
             */
            const resolveElement = ( el ) => {
                
                if ( ! el ) {
                    return null;
                }
                
                if ( el instanceof Element ) {
                    return el;
                }
                
                // jQuery object
                if ( el[ 0 ] instanceof Element ) {
                    return el[ 0 ];
                }
                
                return null;
            };
            
            
            // ============================================
            // FUNCTIONS AVAILABLE FROM orders.js
            // ============================================
            
            /**
             * FUNCTION: Search for matching products
             * WHERE: "Add product" field → typing text
             * 
             * @param {string}          searchStr    — search text
             * @param {Element|jQuery}  blockResult  — block for the results
             * @param {Array}           addedProduct — IDs of products already added
             */
            window.searchSuitableProduct = ( searchStr, blockResult, addedProduct ) => {
                
                const targetBlock = resolveElement( blockResult );
                
                postAjax(
                    'as_searchSuitableProduct_order',
                    {
                        searchStr:    searchStr,
                        addedProduct: addedProduct || []
                    },
                    response => {
                        
                        if ( ! targetBlock ) return;
                        
                        targetBlock.style.display = 'block';
                        targetBlock.innerHTML     = response;
                    },
                    () => {
                        
                        if ( ! targetBlock ) return;
                        
                        targetBlock.style.display = 'block';
                        targetBlock.innerHTML     = '<?php echo esc_js( __( 'Server error', 'argon-shop' ) ); ?>';
                    }
                );
            };
            
            
            /**
             * FUNCTION: Add a product to the order
             * WHERE: Search results → "+" button
             * 
             * @param {Element} product — add button with data-productid
             */
            window.addProduct = ( product ) => {
                
                if ( ! product || ! product.getAttribute ) {
                    return;
                }
                
                const productID = product.getAttribute( 'data-productid' );
                
                const postIDField = document.getElementById( 'post_ID' ) || document.querySelector( 'input[name="post_ID"]' );
                const postID      = postIDField ? postIDField.value : '';
                
                if ( ! productID || ! postID ) {
                    return;
                }
                
                postAjax(
                    'as_addProduct_order',
                    {
                        productID: productID,
                        postID:    postID
                    },
                    response => {
                        
                        // Remove the product from the search results
                        const parentLi = product.closest( '.as-order-foundProduct' );
                        
                        if ( parentLi ) {
                            parentLi.remove();
                        }
                        
                        // Add the product HTML to the order list
                        const blockProducts = document.querySelector( '.orderAllProducts' );
                        
                        if ( blockProducts ) {
                            blockProducts.insertAdjacentHTML( 'beforeend', response );
                        }
                        
                        // Shared recalculation function (from orders.js)
                        if ( typeof window.recountOrderPrices === 'function' ) {
                            window.recountOrderPrices();
                        }
                    },
                    () => {
                        
                        const blockError = document.querySelector( '.orderError' );
                        
                        if ( blockError ) {
                            blockError.innerHTML = '<?php echo esc_js( __( 'Server error', 'argon-shop' ) ); ?>';
                        }
                    }
                );
            };
            
            
            /**
             * FUNCTION: Add form fields
             * WHERE: "Customer information" block → "Add"
             * 
             * @param {string}          type         — form type (person / legalPerson)
             * @param {Array}           fieldsInStock — fields that already exist
             * @param {Element|jQuery}  blockFields   — block to insert the fields into
             */
            window.addFields = ( type, fieldsInStock, blockFields ) => {
                
                const targetBlock = resolveElement( blockFields );
                
                postAjax(
                    'as_addFields_order',
                    {
                        type:         type,
                        fieldsInStock: fieldsInStock || []
                    },
                    response => {
                        
                        if ( targetBlock ) {
                            targetBlock.insertAdjacentHTML( 'beforeend', response );
                        }
                    },
                    () => {
                        
                        const errorBlock = document.querySelector( '.as-order-addFieldsError' );
                        
                        if ( errorBlock ) {
                            errorBlock.innerHTML = '<?php echo esc_js( __( 'Server error', 'argon-shop' ) ); ?>';
                        }
                    }
                );
            };
            
        })();
    </script>
    <?php
}

// ============================================
// AJAX HANDLER REGISTRATION
// ============================================

add_action( 'wp_ajax_as_searchSuitableProduct_order', 'as_search_suitable_product_order_callback' );
add_action( 'wp_ajax_as_addProduct_order', 'as_add_product_order_callback' );
add_action( 'wp_ajax_as_addFields_order', 'as_add_fields_order_callback' );

// Shared order recalculation handler
add_action( 'wp_ajax_as_admin_recount_order', 'as_admin_recount_order_callback' );

/**
 * HANDLER: Add form fields
 */
function as_add_fields_order_callback() {
    
    check_ajax_referer( 'argon_shop_order_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Insufficient permissions', 'argon-shop' ) );
    }
    
    if ( ! isset( $_POST['type'] ) || ! isset( $_POST['fieldsInStock'] ) ) {
        wp_die( __( 'Not enough data', 'argon-shop' ) );
    }
    
    $type = sanitize_text_field( wp_unslash( $_POST['type'] ) );
    
    $fieldsInStock = $_POST['fieldsInStock'];
    
    if ( ! is_array( $fieldsInStock ) ) {
        $fieldsInStock = array();
    }
    
    $fieldsInStock = array_map( 'sanitize_text_field', $fieldsInStock );
    
    $settingShop = get_option( 'settingShop' );
    
    if ( ! isset( $settingShop[ $type ] ) || ! is_array( $settingShop[ $type ] ) ) {
        wp_die( __( 'Settings not found', 'argon-shop' ) );
    }
    
    $userFildsSetting = $settingShop[ $type ];
    
    $userFildsSetting['emailUser']   = array( 'name' => 'Email' );
    $userFildsSetting['messageUser'] = array( 'name' => __( 'Message', 'argon-shop' ) );
    
    foreach ( $userFildsSetting as $keyField => $valueArrayField ) {
        
        if ( ! in_array( $keyField, $fieldsInStock, true ) ) {
            
            if ( $keyField === 'messageUser' ) {
                as_getHTMLField( 'textarea', $keyField, $valueArrayField );
            } else {
                as_getHTMLField( 'input', $keyField, $valueArrayField );
            }
        }
    }
    
    wp_die();
}

/**
 * HANDLER: Add a product to the order
 */
function as_add_product_order_callback() {
    
    check_ajax_referer( 'argon_shop_order_nonce', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Insufficient permissions', 'argon-shop' ) );
    }
    
    if ( ! isset( $_POST['productID'] ) || ! isset( $_POST['postID'] ) ) {
        wp_die( __( 'Not enough data', 'argon-shop' ) );
    }
    
    $productID = absint( $_POST['productID'] );
    $postID    = absint( $_POST['postID'] );
    
    if ( 0 === $productID ) {
        wp_die( __( 'Invalid product ID', 'argon-shop' ) );
    }
    
    if ( 0 === $postID ) {
        wp_die( __( 'Invalid order ID', 'argon-shop' ) );
    }
    
    // Get the current order products
    $orderProducts = get_post_meta( $postID, '_productsCart', true );
    
    if ( ! is_array( $orderProducts ) ) {
        $orderProducts = array();
    }
    
    // If the product is already there — increase the quantity
    if ( isset( $orderProducts[ $productID ] ) ) {
        $orderProducts[ $productID ]['amountProduct'] = floatval( $orderProducts[ $productID ]['amountProduct'] ) + 1;
    } else {
        // Otherwise — add a new product
        $post_data = get_post( $productID );
        
        if ( ! $post_data ) {
            wp_die( __( 'Product not found', 'argon-shop' ) );
        }
        
        $orderProducts[ $productID ] = array(
            'amountProduct' => 1,
            'price'         => as_get_product_price( $productID ),
            'name'          => $post_data->post_title,
            'link'          => get_post_permalink( $productID ),
        );
    }
    
    // Save the updated array to the meta field
    update_post_meta( $postID, '_productsCart', $orderProducts );
    
    // Output the product table HTML
    as_createTableProduct( $productID, $orderProducts[ $productID ] );
    
    wp_die();
}

/**
 * HANDLER: Search for matching products
 */
function as_search_suitable_product_order_callback() {
    
    check_ajax_referer( 'argon_shop_order_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Insufficient permissions', 'argon-shop' ) );
    }
    
    if ( ! isset( $_POST['searchStr'] ) ) {
        wp_die( __( 'Not enough data', 'argon-shop' ) );
    }
    
    $searchStr = sanitize_text_field( wp_unslash( $_POST['searchStr'] ) );
    
    $addedProduct = array();
    
    if ( isset( $_POST['addedProduct'] ) && is_array( $_POST['addedProduct'] ) ) {
        $addedProduct = array_map( 'absint', $_POST['addedProduct'] );
    }
    
    $searchParameters = (object) array(
        'postResult' => true,
    );
    
    $searchResult = as_queryManagerSearchResult( $searchStr, $searchParameters );
    
    if ( empty( $searchResult ) && ! ctype_digit( $searchStr ) ) {
        
        foreach ( as_search_layout_variants( $searchStr ) as $searchStrFix ) {
            
            $searchResult = as_queryManagerSearchResult( $searchStrFix, $searchParameters );
            
            if ( ! empty( $searchResult ) ) {
                break;
            }
        }
    }
    
    $listResult = array();
    
    if ( ! empty( $searchResult ) && isset( $searchResult['product']['listResult'] ) ) {
        
        $listResult = $searchResult['product']['listResult'];
        
        foreach ( $listResult as $key => $value ) {
            
            if ( in_array( $key, $addedProduct, true ) ) {
                unset( $listResult[ $key ] );
            }
        }
    }
    
    if ( ! empty( $listResult ) ) {
        as_order_showSearchResult( $listResult );
    } else {
        echo __( 'No matching results', 'argon-shop' );
    }
    
    wp_die();
}

/**
 * FUNCTION: Output the search results
 */
function as_order_showSearchResult( $searchResult ) {
    ?>
    <ul>
    <?php
    
    foreach ( $searchResult as $key => $value ) {
        
        $post_url   = isset( $value['post_url'] ) ? esc_url( $value['post_url'] ) : '#';
        $post_title = isset( $value['post_title'] ) ? esc_html( $value['post_title'] ) : '';
        $product_id = absint( $key );
        
        ?>
        
        <li class="as-order-foundProduct"> 
        
            <a href="<?php echo $post_url; ?>" target="_blank"><?php echo $post_title; ?></a>
            <input type="button" data-productid="<?php echo esc_attr( $product_id ); ?>" value="" onclick="addProduct(this)" class="as-addProduct">
        
        </li>
        
        <?php
    }
    
    ?>
    </ul>
    <?php
}

/**
 * SHARED HANDLER: Order recalculation
 */
function as_admin_recount_order_callback() {
    
    check_ajax_referer( 'argon_shop_order_nonce', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Insufficient permissions', 'argon-shop' ) );
    }
    
    $postID = isset( $_POST['postID'] ) ? absint( $_POST['postID'] ) : 0;
    
    if ( 0 === $postID ) {
        wp_die( __( 'Invalid order ID', 'argon-shop' ) );
    }
    
    // Get the products from the page
    $orderProductsFromPage = isset( $_POST['orderProducts'] ) ? $_POST['orderProducts'] : array();
    
    if ( ! is_array( $orderProductsFromPage ) ) {
        $orderProductsFromPage = array();
    }
    
    // Build the full product array
    $orderProducts = array();
    $weights       = array();
    
    foreach ( $orderProductsFromPage as $productID => $amount ) {
        
        $productID = absint( $productID );
        $amount    = floatval( $amount );
        
        if ( 0 === $productID || $amount <= 0 ) {
            continue;
        }
        
        $post_data = get_post( $productID );
        
        if ( ! $post_data ) {
            continue;
        }
        
        $orderProducts[ $productID ] = array(
            'amountProduct' => $amount,
            'price'         => as_get_product_price( $productID ),
            'name'          => $post_data->post_title,
            'link'          => get_post_permalink( $productID ),
        );
        
        $weights[ $productID ] = floatval( get_post_meta( $productID, '_weight', true ) );
    }
    
    // ============================================
    // Recalculate discounts via the plugin API
    // ============================================
    
    $actualPriceProducts = array();
    
    if ( ! empty( $orderProducts ) ) {
        
        foreach ( $orderProducts as $productID => $data ) {
            
            $actualPriceProducts[ $productID ] = as_getActualPrice(
                'cart',
                $orderProducts,
                $productID,
                $data['price']
            );
        }
    }
    
    // Order total
    $totalPrice = 0;
    foreach ( $actualPriceProducts as $productID => $price ) {
        if ( isset( $orderProducts[ $productID ] ) ) {
            $totalPrice += floatval( $price ) * $orderProducts[ $productID ]['amountProduct'];
        }
    }
    
    // Total weight
    $totalWeight = 0;
    foreach ( $weights as $productID => $weight ) {
        if ( isset( $orderProducts[ $productID ] ) ) {
            $totalWeight += $weight * $orderProducts[ $productID ]['amountProduct'];
        }
    }
    
    // Build the response
    $forFront = array(
        'actualPrices' => $actualPriceProducts,
        'weights'      => $weights,
        'totalPrice'   => $totalPrice,
        'totalWeight'  => $totalWeight,
    );
    
    echo wp_json_encode( $forFront );
    
    wp_die();
}
