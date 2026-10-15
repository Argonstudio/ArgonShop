<?php
/**
 * Сохранение данных заказа
 * 
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Админка WordPress → Заказы → Редактирование заказа → кнопка «Обновить»
 * URL: /wp-admin/post.php?post=ORDER_ID&action=edit
 * 
 * ЧТО ОН ДЕЛАЕТ:
 * 1. Сохраняет товары заказа (количество, цены с учётом скидок)
 * 2. Сохраняет данные покупателя (поля формы)
 * 3. Сохраняет способ доставки и оплаты
 * 4. Обновляет статус заказа
 * 5. Обновляет заголовок заказа (номер, количество, итог)
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function as_save_data_order( $post_id ) {
    
    // Проверяем nonce — защита от CSRF-атак
    if ( ! isset( $_POST['detailOrders_protect_name'] ) 
        || ! wp_verify_nonce( $_POST['detailOrders_protect_name'], 'detailOrders_protect_action' ) ) {
        return $post_id;
    }
    
    // Проверяем, является ли запрос автосохранением
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return $post_id;
    }
    
    // Проверяем права пользователя
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return $post_id;
    }
    
    // Проверяем тип записи
    $post = get_post( $post_id );
    
    if ( ! $post || $post->post_type !== 'shoporder' ) {
        return $post_id;
    }
    
    // ============================================
    // СОХРАНЕНИЕ ТОВАРОВ ЗАКАЗА
    // ============================================
    
    $orderProducts = array();
    
    if ( isset( $_POST['order_product'] ) && is_array( $_POST['order_product'] ) ) {
        
        $postProduct = $_POST['order_product'];
        
        foreach ( $postProduct as $key => $value ) {
            
            // Проверка ID товара
            $key = absint( $key );
            
            if ( 0 === $key ) {
                continue;
            }
            
            // Проверка количества
            $value = floatval( $value );
            
            if ( $value <= 0 ) {
                continue;
            }
            
            $product_post = get_post( $key );
            
            if ( ! $product_post ) {
                continue;
            }
            
            $title = $product_post->post_title;
            
            $orderProducts[ $key ] = array(
                'amountProduct' => $value,
                'price'         => as_get_product_price( $key ),
                'name'          => $title,
                'link'          => get_post_permalink( $key ),
            );
        }
    }
    
    // Применяем оптовые скидки
    if ( as_check_saleSteps() && ! empty( $orderProducts ) ) {
        
        $discountPriceProducts = as_getActualPriceAllProducts( $orderProducts );
        
        if ( is_array( $discountPriceProducts ) ) {
            
            foreach ( $discountPriceProducts as $key => $value ) {
                
                $key = absint( $key );
                
                if ( isset( $orderProducts[ $key ] ) ) {
                    $orderProducts[ $key ]['price'] = floatval( $value );
                }
            }
        }
    }
    
    // Получаем номер заказа и итог
    $numberPost = get_post_meta( $post_id, '_number', true );
    $amount = as_getDataCart( $orderProducts );
    
    // Обновляем заголовок заказа
    $post_title = sprintf(
        /* translators: 1: номер заказа, 2: количество товаров, 3: итог заказа. */
        __( 'Order #%1$s. Items in order: %2$s, total: %3$s', 'argon-shop' ),
        $numberPost,
        $amount['productAmountCart'],
        $amount['totalPrice']
    );
    
    $post_data = array(
        'ID'         => $post_id,
        'post_title' => $post_title,
    );
    
    if ( ! wp_is_post_revision( $post_id ) ) {
        
        // Снимаем хук, чтобы избежать бесконечного цикла
        remove_action( 'save_post', 'as_save_data_order' );
        
        // Обновляем запись
        wp_update_post( $post_data );
        
        // Восстанавливаем хук
        add_action( 'save_post', 'as_save_data_order' );
    }
    
    // Сохраняем мета-поля товара
    update_post_meta( $post_id, '_productAmountCart', $amount['productAmountCart'] );
    update_post_meta( $post_id, '_totalPrice', $amount['totalPrice'] );
    update_post_meta( $post_id, '_productsCart', $orderProducts );
    
    // ============================================
    // СОХРАНЕНИЕ ДАННЫХ ПОКУПАТЕЛЯ
    // ============================================
    
    $userInfo = array();
    
    if ( isset( $_POST['_fieldValue'] ) && is_array( $_POST['_fieldValue'] ) ) {
        
        $infoValue = $_POST['_fieldValue'];
        $infoName = isset( $_POST['_fieldName'] ) && is_array( $_POST['_fieldName'] ) ? $_POST['_fieldName'] : array();
        
        foreach ( $infoValue as $key => $value ) {
            
            // Проверка ключа
            $key = sanitize_text_field( wp_unslash( $key ) );
            
            if ( empty( $key ) ) {
                continue;
            }
            
            // Проверка значения
            $name = isset( $infoName[ $key ] ) ? sanitize_text_field( wp_unslash( $infoName[ $key ] ) ) : '';
            $value = sanitize_textarea_field( wp_unslash( $value ) );
            
            $userInfo[ $key ] = array(
                'name'  => $name,
                'value' => $value,
            );
        }
    }
    
    update_post_meta( $post_id, '_fieldsCart', $userInfo );
    
    // ============================================
    // СОХРАНЕНИЕ ДОСТАВКИ, ОПЛАТЫ И СТАТУСА
    // ============================================
    
    // Доставка
    if ( isset( $_POST['shipping'] ) ) {
        $typeShipping = sanitize_text_field( wp_unslash( $_POST['shipping'] ) );
        update_post_meta( $post_id, '_shipping', $typeShipping );
    }
    
    // Оплата
    if ( isset( $_POST['payment'] ) ) {
        $typePayment = sanitize_text_field( wp_unslash( $_POST['payment'] ) );
        update_post_meta( $post_id, '_payment', $typePayment );
    }
    
    // Статус заказа
    if ( isset( $_POST['status'] ) ) {
        $statusID = absint( $_POST['status'] );
        
        wp_set_object_terms( $post_id, null, 'statusorders' );
        
        if ( $statusID > 0 ) {
            wp_set_object_terms( $post_id, $statusID, 'statusorders' );
        }
    }
    
    return $post_id;
}

add_action( 'save_post', 'as_save_data_order' );