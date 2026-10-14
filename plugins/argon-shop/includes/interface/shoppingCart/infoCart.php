<?php
/**
 * Cart widget — shows the number and cost of products
 * 
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * WHERE TO TEST:
 * On every page of the site (except the cart page) — the cart widget in the header or sidebar.
 * 
 * WHAT IT DOES:
 * 1. Gets the cart data from the cookie
 * 2. Outputs the number of products and the total cost
 * 3. Links to the cart page
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FUNCTION: Output the cart widget
 * 
 * CALLED FROM: A theme template (for example, header.php)
 * HOW TO CALL: as_infocart();
 */
function as_infocart() {
    
    // Get the ID of the page, product or category being viewed
    // Returns 0 if the viewed page has no ID
    $pageID = get_queried_object_id();
    
    // Do not show the widget on the cart page
    if ( $pageID !== as_get_cartPageID() ) {
        
        $productsShoppingCart = as_getCookie( 'productsShoppingCart' );
        
        // Make sure the cookie contains an array
        if ( ! is_array( $productsShoppingCart ) ) {
            $productsShoppingCart = array();
        }
        
        $dataCart = as_getDataCart( $productsShoppingCart );
        
        // Escape the cart URL
        $cart_url = esc_url( as_get_cartPageURL() );
        
        ?>
        <a href="<?php echo $cart_url; ?>">
        <?php
        
        if ( $dataCart ) {
            
            // Data escaping
            $product_amount = isset( $dataCart['productAmountCart'] ) ? esc_html( $dataCart['productAmountCart'] ) : '0';
            $total_price = isset( $dataCart['totalPrice'] ) ? esc_html( $dataCart['totalPrice'] ) : '0';
            
            ?>
            <div class="viewBlock-shoppingCart">
                <div class="viewBlock-amountProducts"><span class="viewBlock-productsStock"><?php echo $product_amount; ?></span></div>
                <div class="viewBlock-priceProducts"><?php echo as_price_html( '<span>' . $total_price . '</span>' ); ?> </div>
            </div>
            <?php
            
        } else {
            ?>
            <div class="viewBlock-shoppingCart">
                <div class="viewBlock-amountProducts"><span></span></div>
                <div class="viewBlock-priceProducts" style="display:none"><?php echo as_price_html( '<span></span>' ); ?> </div>
            </div>
            <?php
        }
        
        ?>
        </a>
        <?php
    }
}