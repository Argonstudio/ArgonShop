<?php
/**
 * Order meta boxes in the admin panel
 * 
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * WHERE TO TEST:
 * WordPress Admin → Orders → Edit a specific order
 * URL: /wp-admin/post.php?post=ORDER_ID&action=edit
 * 
 * Meta boxes:
 * 1. "Products" — list of products in the order + adding new ones
 * 2. "Customer information" — buyer data, delivery, payment
 * 3. "Order status" — status change (New, Processing, Completed)
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Add the new taxonomy meta boxes
add_action( 'add_meta_boxes', 'as_orders_add_meta_box' );

function as_orders_add_meta_box() {
    
    add_meta_box( 'detailOrders_meta_box', __( 'Products', 'argon-shop' ), 'as_detailOrders_metabox', 'shoporder', 'normal', 'core' );
    add_meta_box( 'detailBuyer_meta_box', __( 'Customer information', 'argon-shop' ), 'as_detailBuyer_metabox', 'shoporder', 'normal', 'core' );
    add_meta_box( 'statusOrders_meta_box', __( 'Order status', 'argon-shop' ), 'as_statusOrders_metabox', 'shoporder', 'side', 'core' );
}

/**
 * META BOX: Order status
 * 
 * WHERE TO TEST: Order page → right column → "Order status"
 * WHAT IT DOES: Outputs radio buttons for choosing the order status
 *             (New / Processing / Completed)
 */
function as_statusOrders_metabox( $post ) {
    
    $argumentStatusAddTerms = array(
        'taxonomy'   => 'statusorders',
        'hide_empty' => false,
        'fields'     => 'id=>name',
    );
    
    $statusTerms = get_terms( $argumentStatusAddTerms );
    
    $currentStatus = get_the_terms( $post->ID, 'statusorders' );
    $currentStatus = ! empty( $currentStatus ) && ! is_wp_error( $currentStatus ) ? $currentStatus[0]->term_id : 0;
    
    foreach ( $statusTerms as $key => $value ) {
        
        $checked = ( $key === $currentStatus ) ? "checked='checked'" : '';
        
        // Output escaping
        $key_esc = esc_attr( $key );
        $value_esc = esc_html( $value );
        
        echo "<label><input type='radio' name='status' value='{$key_esc}' {$checked} />{$value_esc}</label><br />";
    }
}

/**
 * META BOX: Products
 * 
 * WHERE TO TEST: Order page → main "Products" block
 * WHAT IT DOES:
 * 1. Outputs the list of products added to the order
 * 2. A field for searching and adding new products
 * 3. "+" and "-" buttons for changing the quantity
 * 4. A button for removing a product from the order
 */
function as_detailOrders_metabox( $post ) {
    
    $productsCart = get_post_meta( $post->ID, '_productsCart', true );
    
    ?>
    
    <div class="orderAllProducts">
    
    <?php
    
    if ( ! empty( $productsCart ) && is_array( $productsCart ) ) {
        
        foreach ( $productsCart as $key => $value ) {
            
            $key = absint( $key );
            
            if ( $key > 0 ) {
                as_createTableProduct( $key, $value );
            }
        }
    }
    
    ?>
    
    </div>
    
    <div class="blockAddOrderProduct">
        
        <?php esc_html_e( 'Add product', 'argon-shop' ); ?>
        
        <input type="text" class="valueAddOrder" placeholder="<?php esc_attr_e( 'product name or SKU', 'argon-shop' ); ?>">
        <div class="addOrderResult"></div>
        
    </div>
    
    <div class="orderError"></div>
    
    <?php
}

/**
 * FUNCTION: Build the HTML table of a product in the order
 * 
 * WHERE TO TEST: Called automatically in the "Products" meta box
 * WHAT IT DOES: Outputs a table with product information:
 *             - Name and link
 *             - Price per piece
 *             - Quantity (with + and - buttons)
 *             - Weight (if any)
 *             - Total price
 * 
 * @param int   $idProduct   — product ID
 * @param array $dataProduct — data from the saved order (optional)
 */
function as_createTableProduct( $idProduct, $dataProduct = '' ) {
    
    // ID validation
    $idProduct = absint( $idProduct );
    
    if ( 0 === $idProduct ) {
        return;
    }
    
    $post_data = get_post( $idProduct );
    
    if ( ! $post_data ) {
        return;
    }
    
    $post_name;
    $amountProduct;
    $post_link;
    $post_price;
    
    if ( $dataProduct ) {
        
        $post_link = isset( $dataProduct['link'] ) ? esc_url( $dataProduct['link'] ) : '';
        $amountProduct = isset( $dataProduct['amountProduct'] ) ? floatval( $dataProduct['amountProduct'] ) : 1;
        $post_name = isset( $dataProduct['name'] ) ? esc_html( $dataProduct['name'] ) : '';
        $post_price = isset( $dataProduct['price'] ) ? floatval( $dataProduct['price'] ) : 0;
        
    } else {
        
        $amountProduct = 1;
        $post_name = esc_html( $post_data->post_title );
        $post_link = esc_url( get_post_permalink( $idProduct ) );
        $post_price = as_get_product_price( $idProduct );
    }
    
    $post_weight = floatval( get_post_meta( $idProduct, '_weight', true ) );
    
    ?>
    
    <table data-productid="<?php echo esc_attr( $idProduct ); ?>" class="blockOrderProduct">
        <tr>
            <td>
                <a class="orderProductTitle" href="<?php echo $post_link; ?>"><?php echo $post_name; ?></a> 
                <input type="button" data-productid="<?php echo esc_attr( $idProduct ); ?>" class="deleteProduct">                
            </td>
        </tr>
        
        <tr>
            <td>
                <table class="block-orderCharactProduct">
                    
                    <tr class="block-orderCharactProductTitle">
                        <td><?php echo esc_html( as_get_currency_per_piece() ); ?></td>
                        <td><?php esc_html_e( 'Quantity', 'argon-shop' ); ?></td>
                        
                        <?php if ( $post_weight ) { ?>
                            <td><?php esc_html_e( 'Weight', 'argon-shop' ); ?></td>
                        <?php } ?>
                        
                        <td><?php esc_html_e( 'Price', 'argon-shop' ); ?></td>
                    </tr>
                    
                    <tr class="block-orderCharactProductContent">
                        <td class="productPrice"><span><?php echo esc_html( $post_price ); ?></span></td>
                        
                        <td class="block-orderAmountProduct">
                            <input type="text" data-productid="<?php echo esc_attr( $idProduct ); ?>" name="order_product[<?php echo esc_attr( $idProduct ); ?>]" class="orderAmountProduct" value="<?php echo esc_attr( $amountProduct ); ?>">
                            
                            <div class="block-orderChangeValue">
                                <input type="button" data-productid="<?php echo esc_attr( $idProduct ); ?>" class="plusProduct" value="+">
                                <input type="button" data-productid="<?php echo esc_attr( $idProduct ); ?>" class="minusProduct" value="-">
                            </div>
                        </td>
                        
                        <?php if ( $post_weight ) { ?>
                            <td class="amountProductWeight"><span><?php echo esc_html( $post_weight * $amountProduct ); ?></span> <?php esc_html_e( 'kg', 'argon-shop' ); ?></td>
                        <?php } ?>
                        
                        <td class="amountProductPrice"><?php echo as_price_html( '<span>' . esc_html( $post_price * $amountProduct ) . '</span>' ); ?></td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
    
    <?php
}

/**
 * META BOX: Customer information
 * 
 * WHERE TO TEST: Order page → main "Customer information" block
 * WHAT IT DOES:
 * 1. Outputs the fields filled in by the buyer (name, phone, email, etc.)
 * 2. A dropdown for adding missing fields
 * 3. Delivery method radio buttons
 * 4. Payment method radio buttons
 */
function as_detailBuyer_metabox( $post ) {
    
    // Nonce field for protection on save
    wp_nonce_field( 'detailOrders_protect_action', 'detailOrders_protect_name' );
    
    $fieldsCart = get_post_meta( $post->ID, '_fieldsCart', true );
    $shipping = get_post_meta( $post->ID, '_shipping', true );
    $payment = get_post_meta( $post->ID, '_payment', true );
    
    ?>
    
    <ul class='advanced-fields'>
    
    <?php
    
    if ( ! empty( $fieldsCart ) && is_array( $fieldsCart ) ) {
        
        foreach ( $fieldsCart as $keyField => $valueArrayField ) {
            
            if ( ! empty( $valueArrayField['value'] ) ) {
                
                if ( $keyField === 'messageUser' ) {
                    as_getHTMLField( 'textarea', $keyField, $valueArrayField );
                } else {
                    as_getHTMLField( 'input', $keyField, $valueArrayField );
                }
            }
        }
    }
    
    ?>
    
    </ul>
    
    <div class="as-order-itemTitle"><?php esc_html_e( 'Add missing fields from:', 'argon-shop' ); ?></div>
    
    <select class="as-order-addFieldsSelect">
        <option selected value="person"><?php esc_html_e( 'Individuals', 'argon-shop' ); ?></option>
        <option value="legalPerson"><?php esc_html_e( 'Legal entities', 'argon-shop' ); ?></option>
    </select>
    
    <input type="button" class="as-order-addFieldsSend" value="<?php esc_attr_e( 'Add', 'argon-shop' ); ?>">
    
    <div class="as-order-addFieldsError"></div>
    <p></p>
    
    <div class="as-order-itemTitle"><?php esc_html_e( 'Shipping method:', 'argon-shop' ); ?></div>
    
    <?php
    
    $typeShipping = array(
        'selfExport'        => __( 'Pickup', 'argon-shop' ),
        'shippingToAddress' => __( 'Delivery to address', 'argon-shop' ),
    );
    
    foreach ( $typeShipping as $key => $value ) {
        
        $checked = ( $key === $shipping ) ? "checked='checked'" : '';
        
        // Escaping
        $key_esc = esc_attr( $key );
        $value_esc = esc_html( $value );
        
        echo "<label><input type='radio' name='shipping' value='{$key_esc}' {$checked} />{$value_esc}</label><br />";
    }
    
    ?>
    
    <div class="as-order-itemTitle"><?php esc_html_e( 'Payment method:', 'argon-shop' ); ?></div>
    
    <?php
    
    $typePaiment = array(
        'paimentUponReceipt' => __( 'Payment on delivery', 'argon-shop' ),
    );
    
    foreach ( $typePaiment as $key => $value ) {
        
        $checked = ( $key === $payment ) ? "checked='checked'" : '';
        
        // Escaping
        $key_esc = esc_attr( $key );
        $value_esc = esc_html( $value );
        
        echo "<label><input type='radio' name='payment' value='{$key_esc}' {$checked} />{$value_esc}</label><br />";
    }
}

/**
 * FUNCTION: Output an HTML form field
 * 
 * WHERE TO TEST: Called automatically in the "Customer information" meta box
 * WHAT IT DOES: Outputs a field with a label, input/textarea and a hidden field with the name
 * 
 * @param string $type            — 'input' or 'textarea'
 * @param string $keyField        — field key (for example, addressUser)
 * @param array  $valueArrayField — data array (name, value)
 */
function as_getHTMLField( $type, $keyField, $valueArrayField ) {
    
    // Data escaping
    $keyField_esc = esc_attr( $keyField );
    $fieldName = isset( $valueArrayField['name'] ) ? esc_html( $valueArrayField['name'] ) : '';
    $fieldValue = isset( $valueArrayField['value'] ) ? esc_html( $valueArrayField['value'] ) : '';
    
    $class = 'advanced-field';
    
    if ( $keyField === 'addressUser' || $keyField === 'legalAddressUser' ) {
        $class .= ' as-order-fieldAddress';
    }
    
    ?>
    
    <li class="<?php echo esc_attr( $class ); ?>" data-keyfield="<?php echo $keyField_esc; ?>"> 
        
        <div class="span-advanced-fields"><?php echo $fieldName; ?></div>
        
        <?php if ( $type === 'input' ) { ?>
        
            <input type="text" name="_fieldValue[<?php echo $keyField_esc; ?>]" value="<?php echo $fieldValue; ?>">
        
        <?php } elseif ( $type === 'textarea' ) { ?>
        
            <textarea name="_fieldValue[<?php echo $keyField_esc; ?>]"><?php echo $fieldValue; ?></textarea>
        
        <?php } ?>
        
        <input type="hidden" name="_fieldName[<?php echo $keyField_esc; ?>]" value="<?php echo $fieldName; ?>"> 
        
    </li>
    
    <?php
}