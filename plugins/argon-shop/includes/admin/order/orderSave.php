<?php
/**
 * Saving order data
 * 
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * WHERE TO TEST:
 * WordPress Admin → Orders → Edit order → "Update" button
 * URL: /wp-admin/post.php?post=ORDER_ID&action=edit
 * 
 * WHAT IT DOES:
 * 1. Saves the order products (quantity, prices including discounts)
 * 2. Saves the buyer data (form fields)
 * 3. Saves the delivery and payment method
 * 4. Updates the order status
 * 5. Updates the order title (number, quantity, total)
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function as_save_data_order( $post_id ) {
    
    // Check the nonce — protection against CSRF attacks
    if ( ! isset( $_POST['detailOrders_protect_name'] ) 
        || ! wp_verify_nonce( $_POST['detailOrders_protect_name'], 'detailOrders_protect_action' ) ) {
        return $post_id;
    }
    
    // Check whether the request is an autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return $post_id;
    }
    
    // Check the user's capabilities
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return $post_id;
    }
    
    // Check the post type
    $post = get_post( $post_id );
    
    if ( ! $post || $post->post_type !== 'shoporder' ) {
        return $post_id;
    }
    
    // ============================================
    // SAVING ORDER PRODUCTS
    // ============================================
    
    $orderProducts = array();
    
    if ( isset( $_POST['order_product'] ) && is_array( $_POST['order_product'] ) ) {
        
        $postProduct = $_POST['order_product'];
        
        foreach ( $postProduct as $key => $value ) {
            
            // Product ID validation
            $key = absint( $key );
            
            if ( 0 === $key ) {
                continue;
            }
            
            // Quantity validation
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
    
    // Apply wholesale discounts
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
    
    // Get the order number and total
    $numberPost = get_post_meta( $post_id, '_number', true );
    $amount = as_getDataCart( $orderProducts );
    
    // Update the order title
    $post_title = sprintf(
        /* translators: 1: order number, 2: number of items, 3: order total. */
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
        
        // Remove the hook to avoid an infinite loop
        remove_action( 'save_post', 'as_save_data_order' );
        
        // Update the post
        wp_update_post( $post_data );
        
        // Restore the hook
        add_action( 'save_post', 'as_save_data_order' );
    }
    
    // Save the product meta fields
    update_post_meta( $post_id, '_productAmountCart', $amount['productAmountCart'] );
    update_post_meta( $post_id, '_totalPrice', $amount['totalPrice'] );
    update_post_meta( $post_id, '_productsCart', $orderProducts );
    
    // ============================================
    // SAVING BUYER DATA
    // ============================================
    
    $userInfo = array();
    
    if ( isset( $_POST['_fieldValue'] ) && is_array( $_POST['_fieldValue'] ) ) {
        
        $infoValue = $_POST['_fieldValue'];
        $infoName = isset( $_POST['_fieldName'] ) && is_array( $_POST['_fieldName'] ) ? $_POST['_fieldName'] : array();
        
        foreach ( $infoValue as $key => $value ) {
            
            // Key validation
            $key = sanitize_text_field( wp_unslash( $key ) );
            
            if ( empty( $key ) ) {
                continue;
            }
            
            // Value validation
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
    // SAVING DELIVERY, PAYMENT AND STATUS
    // ============================================
    
    // Delivery
    if ( isset( $_POST['shipping'] ) ) {
        $typeShipping = sanitize_text_field( wp_unslash( $_POST['shipping'] ) );
        update_post_meta( $post_id, '_shipping', $typeShipping );
    }
    
    // Payment
    if ( isset( $_POST['payment'] ) ) {
        $typePayment = sanitize_text_field( wp_unslash( $_POST['payment'] ) );
        update_post_meta( $post_id, '_payment', $typePayment );
    }
    
    // Order status
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