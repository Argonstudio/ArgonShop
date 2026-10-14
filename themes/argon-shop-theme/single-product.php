<?php 
/**
 * Product page template (product)
 * 
 * Template for displaying a single catalog product
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * WHERE TO TEST:
 * A single product page
 * 
 * ============================================================
 * INTERACTION WITH THE ARGON SHOP PLUGIN
 * ============================================================
 * 
 * This template is one of the most tightly coupled with the plugin.
 * 
 * PLUGIN FUNCTIONS USED IN THE TEMPLATE:
 * 
 * 1. as_kama_breadcrumbs( ' » ' )
 *    File: includes/interface/breadcrumbs.php
 *    Outputs breadcrumbs like:
 *    Home » Catalog » Parent category » Product
 * 
 * 2. as_getCookie( 'productsShoppingCart' )
 *    File: includes/api/getData/getData.php
 *    Reads the cart cookies as an associative array:
 *    [product_id => ['amountProduct' => N, 'price' => X, ...], ...]
 *    Returns false if the cart is empty.
 * 
 * 3. as_check_saleSteps()
 *    File: includes/api/getSetting.php
 *    Checks whether the wholesale discount system is enabled
 *    (the "discount system type" setting = "Wholesale price steps").
 * 
 * 4. as_getActualStepDiscont( $product_id, $cart, $total_price )
 *    File: includes/api/getData/getData.php
 *    Determines the current wholesale discount step reached
 *    taking into account the total of products in the cart. Returns a number.
 * 
 * 5. as_getActualPrice( "pageProduct", $cart, $product_id, $price, $amount )
 *    File: includes/api/getData/getData.php
 *    Returns the current product price including the wholesale discount.
 *    The "pageProduct" type takes into account the quantity entered in the form
 *    but not yet added to the cart.
 * 
 * 6. as_get_cartPageURL()
 *    File: includes/api/getSetting.php
 *    Returns the cart page URL.
 * 
 * PRODUCT DATA (META FIELDS VIA get_post_meta FROM ArgonShop):
 * 
 * - _price                          → product price (number)
 * - _article                        → SKU (string)
 * - _wholesalePrice                 → array of wholesale prices [term_id => price]
 * - _characteristics_text_field     → text characteristics [term_id => value] Example: $characteristicsText = get_post_meta(get_the_ID(), '_characteristics_text_field',true);
 * - _characteristics_checkbox_field → checkbox characteristics [parent_id => [child_id => 'on']] Example: $characteristicsCheckbox = get_post_meta(get_the_ID(), '_characteristics_checkbox_field',true);
 * - slider_imgs                     → comma-separated list of image IDs
 * 
 * DATA FROM ACF (get_field function):
 * 
 * - unit_product → product unit of measure (default "pc", translated)
 * - application  → text of the "Application" tab block
 * 
 * HTML ELEMENTS HANDLED BY THE PLUGIN JS:
 * 
 * - #basePrice              → current unit price (updated via AJAX) < id="basePrice"> PRICE </ >
 * - #amountProduct          → quantity input <input type="text" id="amountProduct" value="1">
 * - .plusProduct-Page       → "+" button (increase quantity)
 * - .minusProduct-Page      → "-" button (decrease quantity)
 * - #addShoppingCart        → "Add to cart" button (data-productid = product ID) <input type="button" id="addShoppingCart" data-productid="product ID" value="Add to cart">
 * - #productPageCartBuy     → "Buy" button (add + open the cart)
 * - .totalPrice span        → total cost of the selected quantity <div class="totalPrice">total <span></span> (currency symbol from as_price_html())</div>
 * - #howManyProducts span   → how many units of the product are already in the cart <div id="howManyProducts"><span>number</span></div>
 * - #addProductError        → block for the AJAX add-to-cart error <div id="addProductError"></div>
 * 
 * JS FILES HANDLING THE ELEMENTS:
 * 
 * - assets/js/interface/modules/product.js (plugin)
 * - assets/js/interface/modules/slider-product.js (plugin)
 * - assets/js/interface/main.js (plugin, entry point)
 * 
 * CSS CLASSES STYLING THE BLOCKS:
 * 
 * - .block-productMain, .block-productSlider (product.css)
 * - .block-wholesale-price (for the wholesale price table)
 * - .actualStepDiscont (highlight of the active discount step)
 * - .block-productAbout (characteristics.css)
 * 
 * TEMPLATE PARTS (get_template_part):
 * 
 * - template-parts/productPage/characteristics →
 *   product characteristics block (uses the _characteristics_* meta fields)
 * 
 * EXTERNAL DEPENDENCIES:
 * 
 * - ACF (Advanced Custom Fields) — unit_product, application fields
 * - GLightbox — for full-screen image viewing (bundled with the plugin)
 * - Swiper — for the image slider (bundled with the plugin)
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
            if ( function_exists( 'as_kama_breadcrumbs' ) ) {
                AS_Kama_Breadcrumbs( ' » ' );
            }
            ?> 

            
            <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
                
                <?php 
                    
                    $product_id = get_the_ID();
                    
                    // Price — cast to a number.
                    // _price may contain "5 000", "5,000" or an empty string.
                    $price_raw = get_post_meta( $product_id, '_price', true );
                    $price     = function_exists( 'as_get_product_price' ) ? as_get_product_price( $product_id ) : ( function_exists( 'as_to_float' ) ? as_to_float( $price_raw ) : floatval( str_replace( array( ' ', ',' ), array( '', '.' ), (string) $price_raw ) ) );
                    $article     = get_post_meta( $product_id, '_article', true );
                    
                    $productsShoppingCart = function_exists( 'as_getCookie' ) ? as_getCookie( 'productsShoppingCart' ) : false;
                    
                    $actualStepDiscont = null;
                    
                    if ( function_exists( 'as_check_saleSteps' ) && function_exists( 'as_getActualStepDiscont' ) && function_exists( 'as_getActualPrice' ) ) {
                        
                        if ( as_check_saleSteps() && $productsShoppingCart ) {
                            
                            $actualStepDiscont = as_getActualStepDiscont( $product_id, $productsShoppingCart, 1 * $price );
                            $price = as_getActualPrice( 'pageProduct', $productsShoppingCart, $product_id, $price, 1 );
                        }
                    }
                    
                    
                    $price_display = ( $price === '' || $price === false || $price === null ) ? 'none' : $price;
                    
                    // Unit of measure
                    $unitProduct = __( 'pcs', 'argon-shop-theme' );
                    
                    if ( function_exists( 'get_field' ) ) {
                        $unit_field = get_field( 'unit_product', $product_id );
                        
                        if ( $unit_field ) {
                            $unitProduct = $unit_field;
                        }
                    }
                    
                    // "Application" block
                    $productUsage = '';
                    
                    if ( function_exists( 'get_field' ) ) {
                        $usage_field = get_field( 'application', $product_id );
                        
                        if ( $usage_field ) {
                            $productUsage = wpautop( $usage_field );
                        }
                    }
                    
                ?>
                
                <h1 class="title"> <?php the_title(); ?></h1>
                
                <?php if ( $article ) { ?>
                    
                    <div class="articlePost"><?php esc_html_e( 'item code:', 'argon-shop-theme' ); ?> <?php echo esc_html( $article ); ?></div>
                    
                <?php } ?>
            
            
                <div class="block-productMain">
                    
                    <div class="block-productSlider">
                        
                        <?php
                        // Product image slider.
                        // Output by the ArgonShop plugin function (Swiper).
                        // See includes/interface/slider/sliderProduct.php.
                        if ( function_exists( 'as_slider_product' ) ) {
                            as_slider_product( $product_id );
                        }
                        ?>
                        
                    </div>
                    
					<table class="product-table-details">
						<tbody>
							<tr>
								<td class="block-basePrice"> 
								    <?php if ( astheme_currency_position() === 'before' ) : ?><span class="currencySymbol"><?php echo esc_html( astheme_currency_symbol() ); ?></span><?php endif; ?><span id="basePrice"><?php echo esc_html( $price_display ); ?></span> 
								    <span class="unitMeasure"><?php echo esc_html( ( astheme_currency_position() === 'before' ? '' : astheme_currency_symbol() ) . '/' . $unitProduct ); ?></span>
								</td>
							</tr>
								
							<?php
								    
								$wholesalePrice = get_post_meta( $product_id, '_wholesalePrice', true );
								
								if ( $wholesalePrice && is_array( $wholesalePrice ) ) {
								?>
								<tr>
									<td class="block-wholesale-price">
										    
									    <div class='title-wholesale-price'><?php esc_html_e( 'WHOLESALE PRICE, from:', 'argon-shop-theme' ); ?></div>
										    
									    <table>
										        
									    <?php foreach ( $wholesalePrice as $key => $value ) { 
										        
        									    $term = get_term_by( 'id', absint( $key ), 'wholesalePrice' );
                        
                                                if ( ! $term || is_wp_error( $term ) ) {
                                                    continue;
                                                }
                                                
                                                $term_step = function_exists( 'as_to_float' ) ? as_to_float( $term->name ) : floatval( str_replace( ',', '.', (string) $term->name ) );
                                                $is_actual = ( $actualStepDiscont !== null && abs( (float) $actualStepDiscont - $term_step ) < 0.00001 );
										        
										 ?>
										        
    									    <tr class="<?php echo $is_actual ? 'actualStepDiscont' : ''; ?>">
    									        
            									<td class="step-discont-name"> <?php echo astheme_price_html( '<span>' . esc_html( $term->name ) . '</span>' ); ?> </td>
            									<td class="step-discont-value"> <?php echo astheme_price_html( '<span>' . esc_html( $value ) . '</span>', (string) $unitProduct ); ?> </td>
    										            
    										</tr>
										        
										 <?php } ?>
										        
									    </table>
									    
										<div class="clarify"><?php esc_html_e( 'based on the value of products in the cart', 'argon-shop-theme' ); ?></div>
								    </td>
								</tr>
							<?php } ?>
								
								<tr>
									<td>
										    
									<div class="block-pageAmountProduct">
										    
        								<input type="text" id="amountProduct" value="1">
        										    
        								<div class="block-pageChangeValue">
                                                            
                                            <input type="button" class="plusProduct-Page" value="+">
                                            <input type="button" class="minusProduct-Page" value="-">
                                                            
                                        </div>
    									
    									<?php 
    									
    									$disabledProduct = ( $price_display === 'none' ) ? "disabled=''" : '';
    									
    									?>
    										    
    									<input type="button" id="addShoppingCart" class="buttonPurchase" data-productid="<?php echo esc_attr( $product_id ); ?>" <?php echo $disabledProduct; ?> value="<?php esc_attr_e( 'Add to cart', 'argon-shop-theme' ); ?>">
										<input type="button" id="productPageCartBuy" class="buttonPurchase" data-productid="<?php echo esc_attr( $product_id ); ?>" <?php echo $disabledProduct; ?> value="<?php esc_attr_e( 'Buy', 'argon-shop-theme' ); ?>">
									
									    <div class="totalPrice"><?php esc_html_e( 'total', 'argon-shop-theme' ); ?> <?php echo astheme_price_html( '<span></span>' ); ?></div>
										    
									</div>
										    
										               
                                    <div id="howManyProducts" style="<?php if ( ! isset( $productsShoppingCart[ $product_id ] ) ) { ?> display:none <?php } ?>">
                                        
                                        <a href="<?php echo esc_url( as_get_cartPageURL() ); ?>"><input type="button" value="<?php esc_attr_e( 'Go to cart', 'argon-shop-theme' ); ?>"></a>
                                        
                                        <div class="howManyProductsText">
                                        
                                            <?php esc_html_e( 'added', 'argon-shop-theme' ); ?> 
                                                        
                                            <span>
                                                <?php 
                                                                    
                                                if ( isset( $productsShoppingCart[ $product_id ] ) && $productsShoppingCart[ $product_id ] ) {
                                                                        
                                                    $amountProduct = $productsShoppingCart[ $product_id ]['amountProduct'];
                                                    echo esc_html( $amountProduct ); 
                                                }    
                                                                
                                                ?>
                                            </span> 
                                                            
                                            <?php esc_html_e( 'units of this product', 'argon-shop-theme' ); ?><br/>
                                                
                                        </div>
                                                
                                    </div>
                                             
                                    <div class="ajaxError" id="addProductError"></div>    
										    
										    
								</td>
						    </tr>
						</tbody>
					</table>
						
                </div>
                
                <div class="block-productAbout">
                
                    <div class="pageControlPanel productPageControlPanel">
                        
                        <div class="itemControlPanel itemControlPanelActive" data-type="switch" name="productDescription"><?php esc_html_e( 'Description', 'argon-shop-theme' ); ?></div>
                        <div class="itemControlPanel" data-type="switch" name="productCharacteristics"><?php esc_html_e( 'Characteristics', 'argon-shop-theme' ); ?></div>
                        
                        <?php if ( $productUsage ) { ?>
                        
                            <div class="itemControlPanel" data-type="switch" name="productUsage"><?php esc_html_e( 'Usage', 'argon-shop-theme' ); ?></div>
                        
                        <?php } ?>
                        
                    </div>
                    
                    <div class="block-productDescription">
                        
                        <div class="mobileItemControlPanel" data-mobileWidth="720" data-type="openingList" name="productDescription"><?php esc_html_e( 'Description', 'argon-shop-theme' ); ?></div>
                        
                        <div class="blockItemPage" id="block_productDescription"><?php the_content(); ?></div>
                        
                    </div>
                    
                    <div class="block-productCharacteristics">
                        
                        <div class="mobileItemControlPanel" data-mobileWidth="720" data-type="openingList" name="productCharacteristics"><?php esc_html_e( 'Characteristics', 'argon-shop-theme' ); ?></div>
                        
                        <div class="blockItemPage" id="block_productCharacteristics"><?php get_template_part( 'template-parts/productPage/characteristics' ); ?></div>
                        
                    </div>
                    
                    <?php if ( $productUsage ) { ?>
                    
                        <div class="block-productUsage">
                            
                            <div class="mobileItemControlPanel" data-mobileWidth="720" data-type="openingList" name="productUsage"><?php esc_html_e( 'Usage', 'argon-shop-theme' ); ?></div>
                            
                            <div class="blockItemPage" id="block_productUsage"><?php echo $productUsage; ?></div>
                            
                        </div>
                    
                    <?php } ?>
				
				</div>
				
             <?php endwhile; endif; ?>
           
        </main>
        
    </div>
</section>

<?php get_footer(); ?>