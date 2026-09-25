<?php
/**
 * Сохранение данных заказа
 * 
 * @package Argon_Shop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * WordPress Админка → Заказы → Редактирование заказа → Кнопка "Обновить"
 * URL: /wp-admin/post.php?post=ID_ЗАКАЗА&action=edit
 * 
 * ЧТО ДЕЛАЕТ:
 * 1. Сохраняет товары заказа (количество, цены с учётом скидок)
 * 2. Сохраняет данные покупателя (поля формы)
 * 3. Сохраняет способ доставки и оплаты
 * 4. Обновляет статус заказа
 * 5. Обновляет заголовок заказа (номер, количество, сумма)
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function save_data_order( $post_id ) {
    
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
            
            // Валидация ID товара
            $key = absint( $key );
            
            if ( 0 === $key ) {
                continue;
            }
            
            // Валидация количества
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
                'price'         => floatval( get_post_meta( $key, '_price', true ) ),
                'name'          => $title,
                'link'          => get_post_permalink( $key ),
            );
        }
    }
    
    // Применение оптовых скидок
    if ( check_saleSteps() && ! empty( $orderProducts ) ) {
        
        $discountPriceProducts = getActualPriceAllProducts( $orderProducts );
        
        if ( is_array( $discountPriceProducts ) ) {
            
            foreach ( $discountPriceProducts as $key => $value ) {
                
                $key = absint( $key );
                
                if ( isset( $orderProducts[ $key ] ) ) {
                    $orderProducts[ $key ]['price'] = floatval( $value );
                }
            }
        }
    }
    
    // Получаем номер заказа и сумму
    $numberPost = get_post_meta( $post_id, '_number', true );
    $amount = getDataCart( $orderProducts );
    
    // Обновляем заголовок заказа
    $post_title = 'Заказ №' . $numberPost . ' Товаров в заказе: ' . $amount['productAmountCart'] . ', на сумму: ' . $amount['totalPrice'];
    
    $post_data = array(
        'ID'         => $post_id,
        'post_title' => $post_title,
    );
    
    if ( ! wp_is_post_revision( $post_id ) ) {
        
        // Удаляем хук, чтобы избежать бесконечного цикла
        remove_action( 'save_post', 'save_data_order' );
        
        // Обновляем пост
        wp_update_post( $post_data );
        
        // Возвращаем хук
        add_action( 'save_post', 'save_data_order' );
    }
    
    // Сохраняем мета-поля товаров
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
            
            // Валидация ключа
            $key = sanitize_text_field( wp_unslash( $key ) );
            
            if ( empty( $key ) ) {
                continue;
            }
            
            // Валидация значений
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

add_action( 'save_post', 'save_data_order' );