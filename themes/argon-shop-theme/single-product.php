<?php 
/**
 * Шаблон страницы товара (product)
 * 
 * Шаблон вывода отдельного товара каталога
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Страница отдельного товара
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 * 
 * Этот шаблон — один из самых тесно связанных с плагином.
 * 
 * ФУНКЦИИ ПЛАГИНА, ИСПОЛЬЗУЕМЫЕ В ШАБЛОНЕ:
 * 
 * 1. as_kama_breadcrumbs( ' » ' )
 *    File: includes/interface/breadcrumbs.php
 *    Выводит хлебные крошки вида:
 *    Главная » Каталог » Родительская категория » Товар
 * 
 * 2. as_getCookie( 'productsShoppingCart' )
 *    File: includes/api/getData/getData.php
 *    Читает cookie корзины как ассоциативный массив:
 *    [product_id => ['amountProduct' => N, 'price' => X, ...], ...]
 *    Возвращает false, если корзина пуста.
 * 
 * 3. as_check_saleSteps()
 *    File: includes/api/getSetting.php
 *    Проверяет, включена ли система оптовых скидок
 *    (настройка «тип системы скидок» = «Ступени оптовых цен»).
 * 
 * 4. as_getActualStepDiscont( $product_id, $cart, $total_price )
 *    File: includes/api/getData/getData.php
 *    Определяет достигнутую текущую ступень оптовой скидки
 *    с учётом суммы товаров в корзине. Возвращает число.
 * 
 * 5. as_getActualPrice( "pageProduct", $cart, $product_id, $price, $amount )
 *    File: includes/api/getData/getData.php
 *    Возвращает текущую цену товара с учётом оптовой скидки.
 *    Тип «pageProduct» учитывает количество, введённое в форму,
 *    но ещё не добавленное в корзину.
 * 
 * 6. as_get_cartPageURL()
 *    File: includes/api/getSetting.php
 *    Возвращает URL страницы корзины.
 * 
 * ДАННЫЕ ТОВАРА (МЕТА-ПОЛЯ ЧЕРЕЗ get_post_meta ИЗ ArgonShop):
 * 
 * - _price                          → цена товара (число)
 * - _article                        → артикул (строка)
 * - _wholesalePrice                 → массив оптовых цен [term_id => price]
 * - _characteristics_text_field     → текстовые характеристики [term_id => value] Пример: $characteristicsText = get_post_meta(get_the_ID(), '_characteristics_text_field',true);
 * - _characteristics_checkbox_field → характеристики-чекбоксы [parent_id => [child_id => 'on']] Пример: $characteristicsCheckbox = get_post_meta(get_the_ID(), '_characteristics_checkbox_field',true);
 * - slider_imgs                     → список ID изображений через запятую
 * 
 * ДАННЫЕ ИЗ ACF (функция get_field):
 * 
 * - unit_product → единица измерения товара (по умолчанию «pc», переводится)
 * - application  → текст блока вкладки «Применение»
 * 
 * HTML-ЭЛЕМЕНТЫ, ОБРАБАТЫВАЕМЫЕ JS ПЛАГИНА:
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
 * JS-ФАЙЛЫ, ОБРАБАТЫВАЮЩИЕ ЭЛЕМЕНТЫ:
 * 
 * - assets/js/interface/modules/product.js (plugin)
 * - assets/js/interface/modules/slider-product.js (plugin)
 * - assets/js/interface/main.js (plugin, entry point)
 * 
 * CSS-КЛАССЫ, СТИЛИЗУЮЩИЕ БЛОКИ:
 * 
 * - .block-productMain, .block-productSlider (product.css)
 * - .block-wholesale-price (для таблицы оптовых цен)
 * - .actualStepDiscont (подсветка активной ступени скидки)
 * - .block-productAbout (characteristics.css)
 * 
 * ЧАСТИ ШАБЛОНА (get_template_part):
 * 
 * - template-parts/productPage/characteristics →
 *   блок характеристик товара (использует мета-поля _characteristics_*)
 * 
 * ВНЕШНИЕ ЗАВИСИМОСТИ:
 * 
 * - ACF (Advanced Custom Fields) — поля unit_product, application
 * - GLightbox — для просмотра изображений на весь экран (идёт в комплекте с плагином)
 * - Swiper — для слайдера изображений (идёт в комплекте с плагином)
 */

// Запрет прямого доступа к файлу
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
                    
                    // Цена — приводим к числу.
                    // _price может содержать «5 000», «5,000» или пустую строку.
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
                    
                    // Единица измерения
                    $unitProduct = __( 'pcs', 'argon-shop-theme' );
                    
                    if ( function_exists( 'get_field' ) ) {
                        $unit_field = get_field( 'unit_product', $product_id );
                        
                        if ( $unit_field ) {
                            $unitProduct = $unit_field;
                        }
                    }
                    
                    // Блок «Применение»
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
                        // Слайдер изображений товара.
                        // Выводится функцией плагина ArgonShop (Swiper).
                        // См. includes/interface/slider/sliderProduct.php.
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