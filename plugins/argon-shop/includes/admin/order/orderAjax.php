<?php
/**
 * AJAX-функционал страницы заказа в админ-панели
 * 
 * @package Argon_Shop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * WordPress Админка → Заказы → Редактирование конкретного заказа
 * URL: /wp-admin/post.php?post=ID_ЗАКАЗА&action=edit
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Подключение скриптов в футере админки
 * Создаёт nonce и определяет глобальные JS-функции:
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
            
            // Nonce объявлен глобально — используется в orders.js
            // и других скриптах страницы заказа.
            window.argonShopOrderNonce = '<?php echo esc_js( $nonce_order ); ?>';
            
            'use strict';
            
            // ============================================
            // ПЕРЕМЕННЫЕ
            // ============================================
            
            const nonce   = '<?php echo esc_js( $nonce_order ); ?>';
            const ajaxUrl = ( typeof ajaxurl !== 'undefined' ) ? ajaxurl : '/wp-admin/admin-ajax.php';
            
            
            // ============================================
            // ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
            // ============================================
            
            /**
             * Пост-запрос через fetch.
             * Автоматически сериализует массивы в формат WP: key[]=val1&key[]=val2
             * 
             * @param {string}   action    — имя AJAX-действия
             * @param {Object}   params    — параметры запроса
             * @param {Function} onSuccess — колбэк при успехе (принимает текст ответа)
             * @param {Function} onError   — колбэк при ошибке
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
             * Приводит jQuery-объект или нативный DOM-элемент к нативному.
             * Нужно для совместимости, если функция вызывается из старого кода.
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
                
                // jQuery-объект
                if ( el[ 0 ] instanceof Element ) {
                    return el[ 0 ];
                }
                
                return null;
            };
            
            
            // ============================================
            // ФУНКЦИИ, ДОСТУПНЫЕ ИЗ orders.js
            // ============================================
            
            /**
             * ФУНКЦИЯ: Поиск подходящих товаров
             * ГДЕ: Поле "Добавить товар" → ввод текста
             * 
             * @param {string}          searchStr    — текст поиска
             * @param {Element|jQuery}  blockResult  — блок для вывода результатов
             * @param {Array}           addedProduct — ID уже добавленных товаров
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
                        targetBlock.innerHTML     = 'Ошибка на сервере';
                    }
                );
            };
            
            
            /**
             * ФУНКЦИЯ: Добавление товара в заказ
             * ГДЕ: Результаты поиска → кнопка "+"
             * 
             * @param {Element} product — кнопка добавления с data-productid
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
                        
                        // Убираем товар из результатов поиска
                        const parentLi = product.closest( '.as-order-foundProduct' );
                        
                        if ( parentLi ) {
                            parentLi.remove();
                        }
                        
                        // Добавляем HTML товара в список заказа
                        const blockProducts = document.querySelector( '.orderAllProducts' );
                        
                        if ( blockProducts ) {
                            blockProducts.insertAdjacentHTML( 'beforeend', response );
                        }
                        
                        // Единая функция пересчёта (из orders.js)
                        if ( typeof window.recountOrderPrices === 'function' ) {
                            window.recountOrderPrices();
                        }
                    },
                    () => {
                        
                        const blockError = document.querySelector( '.orderError' );
                        
                        if ( blockError ) {
                            blockError.innerHTML = 'Ошибка на сервере';
                        }
                    }
                );
            };
            
            
            /**
             * ФУНКЦИЯ: Добавление полей формы
             * ГДЕ: Блок "Информация от заказчика" → "Добавить"
             * 
             * @param {string}          type         — тип формы (person / legalPerson)
             * @param {Array}           fieldsInStock — уже существующие поля
             * @param {Element|jQuery}  blockFields   — блок для вставки полей
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
                            errorBlock.innerHTML = 'Ошибка на сервере';
                        }
                    }
                );
            };
            
        })();
    </script>
    <?php
}

// ============================================
// РЕГИСТРАЦИЯ AJAX-ОБРАБОТЧИКОВ
// ============================================

add_action( 'wp_ajax_as_searchSuitableProduct_order', 'as_search_suitable_product_order_callback' );
add_action( 'wp_ajax_as_addProduct_order', 'as_add_product_order_callback' );
add_action( 'wp_ajax_as_addFields_order', 'as_add_fields_order_callback' );

// Единый обработчик пересчёта заказа
add_action( 'wp_ajax_as_admin_recount_order', 'as_admin_recount_order_callback' );

/**
 * ОБРАБОТЧИК: Добавление полей формы
 */
function as_add_fields_order_callback() {
    
    check_ajax_referer( 'argon_shop_order_nonce', 'nonce' );
    
    if ( ! isset( $_POST['type'] ) || ! isset( $_POST['fieldsInStock'] ) ) {
        wp_die( 'Недостаточно данных' );
    }
    
    $type = sanitize_text_field( wp_unslash( $_POST['type'] ) );
    
    $fieldsInStock = $_POST['fieldsInStock'];
    
    if ( ! is_array( $fieldsInStock ) ) {
        $fieldsInStock = array();
    }
    
    $fieldsInStock = array_map( 'sanitize_text_field', $fieldsInStock );
    
    $settingShop = get_option( 'settingShop' );
    
    if ( ! isset( $settingShop[ $type ] ) || ! is_array( $settingShop[ $type ] ) ) {
        wp_die( 'Настройки не найдены' );
    }
    
    $userFildsSetting = $settingShop[ $type ];
    
    $userFildsSetting['emailUser']   = array( 'name' => 'Email' );
    $userFildsSetting['messageUser'] = array( 'name' => 'Сообщение' );
    
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
 * ОБРАБОТЧИК: Добавление товара в заказ
 */
function as_add_product_order_callback() {
    
    check_ajax_referer( 'argon_shop_order_nonce', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Недостаточно прав' );
    }
    
    if ( ! isset( $_POST['productID'] ) || ! isset( $_POST['postID'] ) ) {
        wp_die( 'Недостаточно данных' );
    }
    
    $productID = absint( $_POST['productID'] );
    $postID    = absint( $_POST['postID'] );
    
    if ( 0 === $productID ) {
        wp_die( 'Неверный ID товара' );
    }
    
    if ( 0 === $postID ) {
        wp_die( 'Неверный ID заказа' );
    }
    
    // Получаем текущие товары заказа
    $orderProducts = get_post_meta( $postID, '_productsCart', true );
    
    if ( ! is_array( $orderProducts ) ) {
        $orderProducts = array();
    }
    
    // Если товар уже есть — увеличиваем количество
    if ( isset( $orderProducts[ $productID ] ) ) {
        $orderProducts[ $productID ]['amountProduct'] = floatval( $orderProducts[ $productID ]['amountProduct'] ) + 1;
    } else {
        // Иначе — добавляем новый товар
        $post_data = get_post( $productID );
        
        if ( ! $post_data ) {
            wp_die( 'Товар не найден' );
        }
        
        $orderProducts[ $productID ] = array(
            'amountProduct' => 1,
            'price'         => floatval( get_post_meta( $productID, '_price', true ) ),
            'name'          => $post_data->post_title,
            'link'          => get_post_permalink( $productID ),
        );
    }
    
    // Сохраняем обновлённый массив в мета-поле
    update_post_meta( $postID, '_productsCart', $orderProducts );
    
    // Выводим HTML таблицы товара
    createTableProduct( $productID, $orderProducts[ $productID ] );
    
    wp_die();
}

/**
 * ОБРАБОТЧИК: Поиск подходящих товаров
 */
function as_search_suitable_product_order_callback() {
    
    check_ajax_referer( 'argon_shop_order_nonce', 'nonce' );
    
    if ( ! isset( $_POST['searchStr'] ) ) {
        wp_die( 'Недостаточно данных' );
    }
    
    $searchStr = sanitize_text_field( wp_unslash( $_POST['searchStr'] ) );
    
    $addedProduct = array();
    
    if ( isset( $_POST['addedProduct'] ) && is_array( $_POST['addedProduct'] ) ) {
        $addedProduct = array_map( 'absint', $_POST['addedProduct'] );
    }
    
    $searchParameters = (object) array(
        'postResult' => true,
    );
    
    $searchResult = queryManagerSearchResult( $searchStr, $searchParameters );
    
    if ( empty( $searchResult ) && ! ctype_digit( $searchStr ) ) {
        
        $searchStrFix = fixKeyboardlayout( $searchStr );
        
        if ( $searchStrFix !== $searchStr ) {
            $searchResult = queryManagerSearchResult( $searchStrFix, $searchParameters );
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
        echo 'Нет подходящих результатов';
    }
    
    wp_die();
}

/**
 * ФУНКЦИЯ: Вывод результатов поиска
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
 * ЕДИНЫЙ ОБРАБОТЧИК: Пересчёт заказа
 */
function as_admin_recount_order_callback() {
    
    check_ajax_referer( 'argon_shop_order_nonce', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Недостаточно прав' );
    }
    
    $postID = isset( $_POST['postID'] ) ? absint( $_POST['postID'] ) : 0;
    
    if ( 0 === $postID ) {
        wp_die( 'Неверный ID заказа' );
    }
    
    // Получаем товары со страницы
    $orderProductsFromPage = isset( $_POST['orderProducts'] ) ? $_POST['orderProducts'] : array();
    
    if ( ! is_array( $orderProductsFromPage ) ) {
        $orderProductsFromPage = array();
    }
    
    // Формируем полный массив товаров
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
            'price'         => floatval( get_post_meta( $productID, '_price', true ) ),
            'name'          => $post_data->post_title,
            'link'          => get_post_permalink( $productID ),
        );
        
        $weights[ $productID ] = floatval( get_post_meta( $productID, '_weight', true ) );
    }
    
    // ============================================
    // Пересчитываем скидки через API плагина
    // ============================================
    
    $actualPriceProducts = array();
    
    if ( ! empty( $orderProducts ) ) {
        
        foreach ( $orderProducts as $productID => $data ) {
            
            $actualPriceProducts[ $productID ] = getActualPrice(
                'cart',
                $orderProducts,
                $productID,
                $data['price']
            );
        }
    }
    
    // Итоговая сумма
    $totalPrice = 0;
    foreach ( $actualPriceProducts as $productID => $price ) {
        if ( isset( $orderProducts[ $productID ] ) ) {
            $totalPrice += floatval( $price ) * $orderProducts[ $productID ]['amountProduct'];
        }
    }
    
    // Итоговый вес
    $totalWeight = 0;
    foreach ( $weights as $productID => $weight ) {
        if ( isset( $orderProducts[ $productID ] ) ) {
            $totalWeight += $weight * $orderProducts[ $productID ]['amountProduct'];
        }
    }
    
    // Формируем ответ
    $forFront = array(
        'actualPrices' => $actualPriceProducts,
        'weights'      => $weights,
        'totalPrice'   => $totalPrice,
        'totalWeight'  => $totalWeight,
    );
    
    echo wp_json_encode( $forFront );
    
    wp_die();
}