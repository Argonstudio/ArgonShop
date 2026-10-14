<?php
/**
 * Cart page template (shoppingCart)
 * 
 * Template Name: Cart page
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * WHERE TO TEST:
 * The cart page
 * URL: /cart/
 * 
 * ============================================================
 * INTERACTION WITH THE ARGON SHOP PLUGIN
 * ============================================================
 * 
 * This is one of the theme pages most tightly coupled with the plugin.
 * The whole checkout cycle goes through this file.
 * 
 * PLUGIN FUNCTIONS USED IN THE TEMPLATE:
 * 
 * 1. as_kama_breadcrumbs( ' » ' )
 *    File: includes/interface/breadcrumbs.php
 *    Outputs the breadcrumbs: Home » Cart
 * 
 * 2. as_getCookie( 'productsShoppingCart' )
 *    File: includes/api/getData/getData.php
 *    Reads the cart cookies as an associative array:
 *    [product_id => ['amountProduct' => N, 'price' => X], ...]
 *    Returns false if the cart is empty.
 * 
 * 3. as_getActualPrice( "cart", $cart, $product_id )
 *    File: includes/api/getData/getData.php
 *    Returns the current product price including the wholesale discount.
 *    The "cart" type calculates the discount from the total of all products in the cart.
 * 
 * 4. as_getClassActiveItem( $user_ID, $type )
 *    File: includes/api/getSetting.php
 *    Determines the CSS class of the active checkout tab
 *    depending on how complete the user data is.
 * 
 * HTML ELEMENTS HANDLED BY THE PLUGIN JS:
 * 
 * - table.blockCartProduct[data-productid]  → product block in the cart, < data-productid="product id" class="blockCartProduct" >
 * - input.deleteProduct[data-productid]     → remove the product, <input type="button" data-productid="product id" class="deleteProduct">
 * - input.deleteAllProducts                 → clear the cart, <input type="button" class="deleteAllProducts" value="Clear cart">
 * - .errorDeleteProducts                    → clearing errors, <div class="errorDeleteProducts"></div>
 * - input.shoppingCartAmountProduct         → product quantity field, <input type="text" data-productid="<?php echo $key ?>" class="shoppingCartAmountProduct" value="<?php echo $value["amountProduct"]; ?>">
 * - input.plusProduct / .minusProduct       → change the quantity, <input type="button" data-productid="<?php echo $key ?>" class="plusProduct" value="+">
 * - .productPrice span                      → unit price, < class="productPrice"><span> PRICE </span> </ >
 * - .amountProductWeight span               → total product weight (taking the number of units into account), < class="amountProductWeight" style="<?php if(!$cartTotalWeight) echo "display:none"; ?>"><span> WEIGHT </span> </ >
 * - .amountProductPrice span                → product total (taking the number of units into account), < class="amountProductPrice"><span> PRICE </span> </ >
 * - .cartTotalWeight span                   → weight of the whole cart, < class="cartTotalWeight"> <span> WEIGHT </span> </ >
 * - .cartTotalPrice span                    → price of the whole cart, < class="cartTotalPrice"> <span> PRICE </span> </ >
 * - .shoppingCartError                      → error on the product card
 * - .pageControlPanel .itemControlPanel     → checkout form tabs
 * 
 * CHECKOUT FORMS (get_template_part):
 * 
 * - template-parts/cart/quickOrder   → short form
 * - template-parts/cart/person       → individual's form
 * - template-parts/cart/legalPerson  → legal entity form
 * 
 * These templates use the plugin's as_show_user_fields() function
 * (includes/api/getSetting.php) to output the form fields.
 * 
 * JS FILES HANDLING THE CART:
 * 
 * - assets/js/interface/modules/cart.js (plugin)
 * - assets/js/interface/modules/cart-form.js (plugin)
 * 
 * PLUGIN AJAX HANDLERS (shoppingCart.php):
 * 
 * - amountProducts_shoppingCart    → quantity change
 * - deleteProducts_shoppingCart    → product removal
 * - deleteAllProducts_shoppingCart → clearing the cart
 * - submitCart_shoppingCart        → placing the order
 * 
 * EXTERNAL DEPENDENCIES:
 * 
 * - ACF — unit_product field of the product
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content">
            
            <?php 
            // Argon Shop plugin function (includes/interface/breadcrumbs.php)
            if ( function_exists( 'as_kama_breadcrumbs' ) ) {
                AS_Kama_Breadcrumbs( ' » ' );
            }
            ?> 

            
            <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            
            <?php 
            
            $user_ID = get_current_user_id();
            ?>
            
            <div id="product_page" class="white_block">
                
                <h1 class="title" id="<?php echo esc_attr( get_the_ID() ); ?>"><?php the_title(); ?></h1>
                
                <div id="shoppingCart">
                
                <?php
                
                // Argon Shop plugin function (includes/api/getData/getData.php)
                $productsShoppingCart = function_exists( 'as_getCookie' ) ? as_getCookie( 'productsShoppingCart' ) : false;
                
                if ( $productsShoppingCart && is_array( $productsShoppingCart ) ) {
                
                    $cartTotalWeight = 0;
                    $cartTotalPrice  = 0;
                    
                    foreach ( $productsShoppingCart as $key => $value ) {
                        
                        $key = absint( $key );
                        
                        if ( 0 === $key ) {
                            continue;
                        }
                        
                        $post_weight = get_post_meta( $key, '_weight', true );
                        $post_weight = str_replace( ',', '.', $post_weight );
                        $post_weight = (float) $post_weight;
                        
                        // Argon Shop plugin function (includes/api/getData/getData.php)
                        $post_price = function_exists( 'as_getActualPrice' ) 
                            ? as_getActualPrice( 'cart', $productsShoppingCart, $key ) 
                            : ( function_exists( 'as_get_product_price' ) ? as_get_product_price( $key ) : floatval( str_replace( ',', '.', (string) get_post_meta( $key, '_price', true ) ) ) );
                        
                        $amount = isset( $value['amountProduct'] ) ? floatval( $value['amountProduct'] ) : 0;
                        
                        $cartTotalWeight += $post_weight * $amount;
                        $cartTotalPrice  += $post_price * $amount;
                    }
                    
                    ?>
                    <div class="block-cartProductsControl">
                        
                        <div class="block-totalResult">
                        
                            <div class="cartTotalPrice"><?php esc_html_e( 'Total price:', 'argon-shop-theme' ); ?> <?php echo astheme_price_html( '<span>' . esc_html( $cartTotalPrice ) . '</span>' ); ?> </div>
                            <div class="cartTotalWeight" style="<?php if ( ! $cartTotalWeight ) echo 'display:none'; ?>"><?php esc_html_e( 'Total weight:', 'argon-shop-theme' ); ?> <span><?php echo esc_html( $cartTotalWeight ); ?></span> <?php esc_html_e( 'kg', 'argon-shop-theme' ); ?> </div>
                            
                        </div>
                        
                        <div class="block-deleteAllProducts">
                        
                            <input type="button" class="deleteAllProducts" value="<?php esc_attr_e( 'Empty cart', 'argon-shop-theme' ); ?>">
                            <div class="errorDeleteProducts"></div>
                        
                        </div>
                        
                    </div>
                    <?php
                    
                    
                    foreach ( $productsShoppingCart as $key => $value ) {
                        
                        $key = absint( $key );
                        
                        if ( 0 === $key ) {
                            continue;
                        }
                        
                        $post_data = get_post( $key );
                        
                        // Skip if the product has been deleted
                        if ( ! $post_data ) {
                            continue;
                        }
                        
                        $post_img     = get_the_post_thumbnail_url( $key, 'thumbnail' );
                        $post_link    = get_post_permalink( $key );
                        $post_name    = $post_data->post_title;
                        $post_weight  = get_post_meta( $key, '_weight', true );
                        $post_article = get_post_meta( $key, '_article', true );
                        
                        // Argon Shop plugin function (includes/api/getData/getData.php)
                        $post_price = function_exists( 'as_getActualPrice' ) 
                            ? as_getActualPrice( 'cart', $productsShoppingCart, $key ) 
                            : ( function_exists( 'as_get_product_price' ) ? as_get_product_price( $key ) : floatval( str_replace( ',', '.', (string) get_post_meta( $key, '_price', true ) ) ) );
                        
                        $post_weight = str_replace( ',', '.', $post_weight );
                        $post_weight = (float) $post_weight;
                        
                        $amount = isset( $value['amountProduct'] ) ? floatval( $value['amountProduct'] ) : 0;
                        
                        // Unit of measure via ACF
                        $unitProduct = __( 'pcs', 'argon-shop-theme' );
                        
                        if ( function_exists( 'get_field' ) ) {
                            $unit_field = get_field( 'unit_product', $key );
                            
                            if ( $unit_field ) {
                                $unitProduct = $unit_field;
                            }
                        }
                        
                        ?>
                        
                        <table data-productid="<?php echo esc_attr( $key ); ?>" class="blockCartProduct">
                            <tr>
                                <td>
                                    
                                    <table class="block-bascetDescrProduct">
                                        <tr>
                                            
                                            <?php if ( $post_img ) { ?>
                                            
                                            <td class="block-bascetProductImg"> <img class="bascetProductImg" src="<?php echo esc_url( $post_img ); ?>" alt="<?php echo esc_attr( $post_name ); ?>"></td>
                                            
                                            <?php } ?>
                                
                                            <td class="block-bascetProductNameArticle">
                                                
                                                <a href="<?php echo esc_url( $post_link ); ?>"><?php echo esc_html( $post_name ); ?></a>
                                                
                                                <?php if ( $post_article ) { ?>
                                                    
                                                    <div class="block-bascetProductArticle"><?php esc_html_e( 'item code:', 'argon-shop-theme' ); ?> <?php echo esc_html( $post_article ); ?></div>
                                                
                                                <?php } ?>
                                                
                                            </td>
                                            
                                            <td class="block-bascetDeleteShoppingCart">
                                                <input type="button" data-productid="<?php echo esc_attr( $key ); ?>" class="deleteProduct">
                                            </td>
                                            
                                        </tr>
                                    </table>
                                    
                                </td>
                                
                            </tr>
                            
                            <tr>
                                <td>
                                    
                                    <table class="block-bascetCharactProduct">
                                        
                                        <tr class="block-bascetCharactProductTitle">
                                            <td class="productPriceTitle"><?php echo esc_html( astheme_currency_symbol() . '/' . $unitProduct ); ?></td>
                                            <td><?php esc_html_e( 'Quantity', 'argon-shop-theme' ); ?></td>
                                            
                                            <?php if ( $post_weight ) { ?>
                                            
                                                <td class="amountProductWeightTitle"><?php esc_html_e( 'Weight', 'argon-shop-theme' ); ?></td>
                                            
                                            <?php } ?>
                                            
                                            <td class="amountProductPriceTitle"><?php esc_html_e( 'Price', 'argon-shop-theme' ); ?></td>
                                            
                                            <td class="mobileBlock-amountProductTitle"><?php esc_html_e( 'Product', 'argon-shop-theme' ); ?></td>
                                        </tr>
                                        
                                        <tr class="block-bascetCharactProductContent">
                                            <td class="productPrice"><span><?php echo esc_html( $post_price ); ?></span> </td>
                                            
                                            <td class="block-bascetAmountProduct">
                                                
                                                <input type="text" data-productid="<?php echo esc_attr( $key ); ?>" class="shoppingCartAmountProduct" value="<?php echo esc_attr( $amount ); ?>">
                                                
                                                <div class="block-bascetChangeValue">
                                                    
                                                    <input type="button" data-productid="<?php echo esc_attr( $key ); ?>" class="plusProduct" value="+">
                                                    <input type="button" data-productid="<?php echo esc_attr( $key ); ?>" class="minusProduct" value="-">
                                                    
                                                </div>
                                                
                                            </td>
                                            
                                            <?php if ( $post_weight ) { ?>
                                            
                                                <td class="amountProductWeight"><span><?php echo esc_html( $post_weight * $amount ); ?></span> <?php esc_html_e( 'kg', 'argon-shop-theme' ); ?></td>
                                            
                                            <?php } ?>
                                            
                                            <td class="amountProductPrice"><?php echo astheme_price_html( '<span>' . esc_html( $post_price * $amount ) . '</span>' ); ?></td>
                                            
                                            <td class="mobileBlock-amountProduct">
                                                
                                                <div class="productPrice mobileBlock-productPrice"><?php echo esc_html( astheme_currency_symbol() . '/' . $unitProduct ); ?>: <span><?php echo esc_html( $post_price ); ?></span> </div>
                                                
                                                <?php if ( $post_weight ) { ?>
                                            
                                                <div class="amountProductWeight"><?php esc_html_e( 'Weight:', 'argon-shop-theme' ); ?> <span><?php echo esc_html( $post_weight * $amount ); ?></span> <?php esc_html_e( 'kg', 'argon-shop-theme' ); ?></div>
                                            
                                                <?php } ?>
                                                
                                                <div class="amountProductPrice"><?php esc_html_e( 'Price:', 'argon-shop-theme' ); ?> <?php echo astheme_price_html( '<span>' . esc_html( $post_price * $amount ) . '</span>' ); ?></div>
                                                
                                            </td>
                                        </tr>
                                        
                                    </table>
                                    
                                </td>
                            </tr>
                            
                            <tr><td class="shoppingCartError"><span></span></td></tr>
                        </table>
                        
                        <?php
                    }
                    
                    ?>
                    
                    <div class="block-cartProductsControl block-cartControlAfter">
                        
                        <div class="block-totalResult">
                        
                            <div class="cartTotalPrice"><?php esc_html_e( 'Total price:', 'argon-shop-theme' ); ?> <?php echo astheme_price_html( '<span>' . esc_html( $cartTotalPrice ) . '</span>' ); ?> </div>
                            <div class="cartTotalWeight" style="<?php if ( ! $cartTotalWeight ) echo 'display:none'; ?>"><?php esc_html_e( 'Total weight:', 'argon-shop-theme' ); ?> <span><?php echo esc_html( $cartTotalWeight ); ?></span> <?php esc_html_e( 'kg', 'argon-shop-theme' ); ?> </div>
                            
                        </div>
                        
                        <div class="block-deleteAllProducts">
                        
                            <input type="button" class="deleteAllProducts" value="<?php esc_attr_e( 'Empty cart', 'argon-shop-theme' ); ?>">
                            <div class="errorDeleteProducts"></div>
                        
                        </div>
                        
                    </div>
				
    				<div class="block-orderRegistration">
    				
        				<h1><?php esc_html_e( 'Checkout', 'argon-shop-theme' ); ?></h1>
        				
        				<?php 
        				    $accountData = get_user_meta( $user_ID, 'accountData', true );
        				?>
        				
        				
        				<div class="pageControlPanel cartControlPanel">
                            
                            <?php 
                            // Argon Shop plugin function (includes/api/getSetting.php)
                            // Returns the CSS class of the active tab depending on
                            // how complete the user data is.
                            
                            $class_quick  = function_exists( 'as_getClassActiveItem' ) ? as_getClassActiveItem( $user_ID, 'cartQuickOrder' ) : '';
                            $class_person = function_exists( 'as_getClassActiveItem' ) ? as_getClassActiveItem( $user_ID, 'cartPerson' ) : '';
                            $class_legal  = function_exists( 'as_getClassActiveItem' ) ? as_getClassActiveItem( $user_ID, 'cartLegalPerson' ) : '';
                            ?>
                            
                            <div class="itemControlPanel <?php echo esc_attr( $class_quick ); ?>" data-type="switch" name="cartQuickOrder"><?php esc_html_e( 'Quick checkout', 'argon-shop-theme' ); ?></div>
                            <div class="itemControlPanel <?php echo esc_attr( $class_person ); ?>" data-type="switch" name="cartPerson"><?php esc_html_e( 'Individuals', 'argon-shop-theme' ); ?></div>
                            <div class="itemControlPanel <?php echo esc_attr( $class_legal ); ?>" data-type="switch" name="cartLegalPerson"><?php esc_html_e( 'Legal entities', 'argon-shop-theme' ); ?></div>
                            
                        </div>
                        
                        <div class="pageControlPanel cartControlPanel-mobile">
                            
                            <div class="itemControlPanel <?php echo esc_attr( $class_quick ); ?>" data-type="switch" name="cartQuickOrder"><?php esc_html_e( 'Quick', 'argon-shop-theme' ); ?></div>
                            <div class="itemControlPanel <?php echo esc_attr( $class_person ); ?>" data-type="switch" name="cartPerson"><?php esc_html_e( 'Individuals (short)', 'argon-shop-theme' ); ?></div>
                            <div class="itemControlPanel <?php echo esc_attr( $class_legal ); ?>" data-type="switch" name="cartLegalPerson"><?php esc_html_e( 'Companies', 'argon-shop-theme' ); ?></div>
                            
                        </div>
                        
                        
                        
                        <div class="blockItemPage" id="block_cartQuickOrder">
                                
                            <?php get_template_part( 'template-parts/cart/quickOrder' ); ?>
                                
                        </div>
                            
                        <div class="blockItemPage" id="block_cartPerson">
                                
                            <?php get_template_part( 'template-parts/cart/person' ); ?>
                                
                        </div>
                            
                        <div class="blockItemPage" id="block_cartLegalPerson">
                                
                            <?php get_template_part( 'template-parts/cart/legalPerson' ); ?>
                                
                        </div>
                            
                        <div class="checkboxFormCart">
                            
                            <input type="checkbox" id="agree_checkbox" data-consent-target=".submitCart" style="cursor: pointer;">
                            
                            <label for="agree_checkbox" style="cursor: pointer;">
                                
                                
                                <?php esc_html_e( 'By checking this box, I consent to the processing of', 'argon-shop-theme' ); ?> <a href="/konfidentsialnost-personalnoj-informatsii/" target="_blank"><?php esc_html_e( 'data for the demo stand', 'argon-shop-theme' ); ?></a> <?php esc_html_e( '(including first name, last name, address, phone, email, TIN, KPP, legal address, username) and technical files', 'argon-shop-theme' ); ?> <strong>cookie</strong> <?php esc_html_e( 'for the purpose of demonstrating and testing the site interface. I understand that this site and all forms are only a simulation of an online store, and I do not enter my real personal data. I have read the processing terms.', 'argon-shop-theme' ); ?>
                                
                                
                            </label>
                            
                        </div>
                        
        				
        				<?php
        				
        				} else {
        				    
        				    echo __( 'Your cart is empty', 'argon-shop-theme' );
        				  
        				} 
        				
        				?>
    				
    				</div>
				</div> <!-- productsShoppingCart -->
            </div>
            <?php endwhile; endif; ?>
           
        </main>
        
         
         
    </div>
    
</section>

<?php get_footer(); ?>