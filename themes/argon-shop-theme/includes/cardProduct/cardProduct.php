<?php
/**
 * Product card (template part)
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * WHERE IT IS USED:
 * Included via require from the theme's functions.php.
 * Called in the catalog, search, sidebar and home page templates:
 * 
 * - taxonomy-catalog.php
 * - search.php
 * - tag.php
 * - archive.php
 * - single-product.php (similar products block)
 * - sidebar.php (via view_products_list)
 * - history.php
 * 
 * WHAT IT OUTPUTS:
 * A product card with an image, characteristics, price
 * and purchase buttons.
 * 
 * ============================================================
 * INTERACTION WITH THE ARGON SHOP PLUGIN
 * ============================================================
 * 
 * PLUGIN FUNCTIONS:
 * 
 * 1. as_get_charact( $type, $value, $productID )
 *    File: includes/api/getData/admin/get_characteristics.php
 *    Returns the value of a product characteristic.
 *    For "id" it searches by term ID, for "name" — by name.
 *    If the characteristic has children, it returns an array
 *    of the selected options.
 * 
 * 2. as_check_saleSteps()
 *    File: includes/api/getSetting.php
 *    Checks whether the wholesale discount system is enabled.
 * 
 * 3. as_getActualPrice( $type, $cart, $productID, $price, $amount )
 *    File: includes/api/getData/getData.php
 *    Returns the current price including the wholesale discount.
 *    The "cardProduct" type is the calculation for the product card.
 * 
 * 4. as_button_card_product( $productID, $cardInBascet, $cardBuy, $productsShoppingCart )
 *    File: includes/interface/product/cardProduct.php
 *    Outputs the "Add to cart" / "Buy" buttons or a link
 *    to the cart (if the product has already been added).
 * 
 * PRODUCT META FIELDS:
 * 
 * - _price          → price
 * - _article        → SKU
 * - _weight         → weight
 * - _hit            → "on" if it is a bestseller
 * - _newProduct     → "on" if it is a new product
 * 
 * ACF FIELDS:
 * 
 * - unit_product → unit of measure (pc, m², kg, etc.)
 * 
 * CHARACTERISTICS:
 * 
 * - ID 24 — "BRAND" (hardcoded)
 *   Used to output the product brand.
 * 
 * BUTTON AJAX HANDLERS:
 * 
 * - addProducts_shoppingCart  — add the product to the cart
 *   File: includes/interface/shoppingCart/shoppingCart.php
 * 
 * JS FILES (plugin):
 * 
 * - assets/js/interface/modules/card-product.js — card button handling
 * - assets/js/interface/main.js                 — entry point
 * 
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FUNCTION: Outputs a product card
 * 
 * @param WP_Post     $post                  — post object (current product in the loop)
 * @param array|false $productsShoppingCart — products in the cart
 */
function generate_product_card( $post, $productsShoppingCart ) {
    
    $productID = get_the_ID();
    
    // Validation: if the post is invalid, do not output the card
    if ( ! $productID ) {
        return;
    }
    
    // Product meta fields
    $price   = function_exists( 'as_get_product_price' ) ? as_get_product_price( $productID ) : get_post_meta( $productID, '_price', true );
    $article = get_post_meta( $productID, '_article', true );
    $weight  = get_post_meta( $productID, '_weight', true );
    $hit     = get_post_meta( $productID, '_hit', true );
    $new     = get_post_meta( $productID, '_newProduct', true );
    
    // Brand — the characteristic with ID 24
    // Argon Shop plugin function (includes/api/getData/admin/get_characteristics.php)
    $trademark = function_exists( 'as_get_charact' ) 
        ? as_get_charact( 'id', 24, $productID ) 
        : false;
    
    ?>
    
    <div class="block-productCard">
        
        <div class="cardHeader">
            
            <div class="block-cardProductImage">
            
    			<?php if ( $hit === 'on' ) : ?>
    			    <div class="cardProductMarked cardProductHit"><span>hit</span></div>
    			<?php endif; ?>
    		
    			<?php if ( $new === 'on' ) : ?>
    			    <div class="cardProductMarked cardProductNew"><span>new</span></div>
    			<?php endif; ?>
    			
    			<a href="<?php the_permalink(); ?>">
    				<?php the_post_thumbnail( 'blog_thumb', array( 'class' => 'img-responsive' ) ); ?>
    			</a>
    			
    		</div>
            
            <ul class="block-cardProductCharact">
                
                <?php if ( $article ) { ?>
                    <li><?php esc_html_e( 'SKU:', 'argon-shop-theme' ); ?> <?php echo esc_html( $article ); ?></li>
                <?php } ?>
                
                <?php if ( $trademark && is_array( $trademark ) && ! empty( $trademark[0] ) ) { ?>
                    <li><?php esc_html_e( 'Brand:', 'argon-shop-theme' ); ?> <?php echo esc_html( $trademark[0] ); ?></li>
                <?php } elseif ( $trademark && is_string( $trademark ) ) { ?>
                    <li><?php esc_html_e( 'Brand:', 'argon-shop-theme' ); ?> <?php echo esc_html( $trademark ); ?></li>
                <?php } ?>
                
                <?php if ( $weight ) { ?>
                    <li><?php esc_html_e( 'Weight:', 'argon-shop-theme' ); ?> <?php echo esc_html( $weight ); ?> <?php esc_html_e( 'kg', 'argon-shop-theme' ); ?></li>
                <?php } ?>
                
            </ul>
            
        </div>
        
        <a href="<?php the_permalink(); ?>" class="cardProductName">
            
            <?php the_title(); ?>
            
        </a>
        
        <?php 
        
        if ( $price ) { 
            
            // Calculate the current price including the wholesale discount
            // Argon Shop plugin function (includes/api/getData/getData.php)
            if ( function_exists( 'as_check_saleSteps' ) && function_exists( 'as_getActualPrice' ) ) {
                
                if ( as_check_saleSteps() && $productsShoppingCart && is_array( $productsShoppingCart ) ) {
                    
                    $price = as_getActualPrice( 'cardProduct', $productsShoppingCart, $productID, $price, 1 );
                }
            }
            
            // Unit of measure via ACF
            $unitProduct = __( 'pcs', 'argon-shop-theme' );
            
            if ( function_exists( 'get_field' ) ) {
                
                $unit_field = get_field( 'unit_product', $productID );
                
                if ( $unit_field ) {
                    
                    $unitProduct = $unit_field;
                    
                    // Shorten a long unit of measure
                    $unitProductLength = mb_strlen( $unitProduct, 'utf-8' );
                    
                    if ( $unitProductLength > 13 ) {
                        $unitProduct = mb_substr( $unitProduct, 0, 13 );
                        $unitProduct .= '..';
                    }
                }
            }
            
        ?>
            
            <div class="cardProductPrice"> <?php echo astheme_price_html( '<span>' . esc_html( $price ) . '</span>', (string) $unitProduct ); ?></div>
            
            <?php 
            
            // Purchase buttons
            // Argon Shop plugin function (includes/interface/product/cardProduct.php)
            if ( function_exists( 'as_button_card_product' ) ) {
                as_button_card_product( $productID, true, true, $productsShoppingCart );
            }
            
            ?>
            
        <?php } ?>
        
        
        
    </div>
    
    <?php
}