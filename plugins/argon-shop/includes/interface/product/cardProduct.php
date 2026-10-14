<?php
/**
 * Add-to-cart buttons (product card)
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * WHERE TO TEST:
 * Product card in the catalog, in the "Best sellers" block, etc.
 * 
 * WHAT IT DOES:
 * Outputs the "Add to cart" and "Buy" buttons or a link to the cart
 * depending on whether the product has been added
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FUNCTION: Output the add-to-cart buttons
 * 
 * @param int        $productID            — product ID
 * @param bool|mixed $cardInBascet         — show the "Add to cart" button
 * @param bool|mixed $cardBuy              — show the "Buy" button
 * @param array      $productsShoppingCart — products in the cart
 */
function as_button_card_product( $productID, $cardInBascet, $cardBuy, $productsShoppingCart ) {
    
    $productID = absint( $productID );
    $productID_esc = esc_attr( $productID );
    
    // Make sure the cart is an array
    if ( ! is_array( $productsShoppingCart ) ) {
        $productsShoppingCart = array();
    }
    
    // Is the product already in the cart?
    $in_cart = isset( $productsShoppingCart[ $productID ] ) && $productsShoppingCart[ $productID ];
    
    // Cart URL
    $cart_url = esc_url( as_get_cartPageURL() );
    
    ?>
    
    <!-- Add buttons -->
    <div class="block-cardProductBascet" style="<?php if ( $in_cart ) { ?> display:none <?php } ?>">
        
        <?php if ( $cardInBascet !== false ) { ?>
        
            <input type="button" data-productid="<?php echo $productID_esc; ?>" class="card-inBascet" value="<?php esc_attr_e( 'Add to cart', 'argon-shop' ); ?>">
        
        <?php } ?>
        
        <?php if ( $cardBuy !== false ) { ?>
        
            <input type="button" data-productid="<?php echo $productID_esc; ?>" class="card-buy" value="<?php esc_attr_e( 'Buy', 'argon-shop' ); ?>">
        
        <?php } ?> 
        
    </div>
    
    <!-- Product-in-cart block -->
    <div class="card-alreadyAdded" style="<?php if ( ! $in_cart ) { ?> display:none <?php } ?>">
        
        <?php 
        
        $cardalreadyAddedValue = __( 'Product added to cart', 'argon-shop' );
        
        $queried_object_id = get_queried_object_id();
        
        if ( $queried_object_id && as_get_cartPageID() === $queried_object_id ) {
            $cardalreadyAddedValue = __( 'Update cart', 'argon-shop' );
        }
        
        $cardalreadyAddedValue_esc = esc_attr( $cardalreadyAddedValue );
        
        ?>
        
        <a href="<?php echo $cart_url; ?>"><input type="button" value="<?php echo $cardalreadyAddedValue_esc; ?>"></a>
        
    </div>
    
    <!-- Error block -->
    <div class="card-addProductError"></div>
    
    <?php
}
