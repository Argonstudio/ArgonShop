<?php 
/**
 * Шаблон страницы товара (product)
 * 
 * Шаблон для вывода отдельного товара каталога
 *
 * @package ArgonShopTheme
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Страница отдельного товара
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 * 
 * Этот шаблон — один из наиболее тесно связанных с плагином.
 * 
 * ФУНКЦИИ ПЛАГИНА, ИСПОЛЬЗУЕМЫЕ В ШАБЛОНЕ:
 * 
 * 1. kama_breadcrumbs( ' » ' )
 *    Файл: includes/interface/breadcrumbs.php
 *    Выводит хлебные крошки вида:
 *    Главная » Каталог » Родительская категория » Товар
 * 
 * 2. getCookie( 'productsShoppingCart' )
 *    Файл: includes/api/getData/getData.php
 *    Читает куки корзины в виде ассоциативного массива:
 *    [product_id => ['amountProduct' => N, 'price' => X, ...], ...]
 *    Возвращает false, если корзина пуста.
 * 
 * 3. check_saleSteps()
 *    Файл: includes/api/getSetting.php
 *    Проверяет, активирована ли система оптовых скидок
 *    (настройка "тип системы скидок" = "Шаги оптовых цен").
 * 
 * 4. getActualStepDiscont( $product_id, $cart, $total_price )
 *    Файл: includes/api/getData/getData.php
 *    Определяет текущий шаг оптовой скидки, достигнутый
 *    с учётом суммы товаров в корзине. Возвращает число.
 * 
 * 5. getActualPrice( "pageProduct", $cart, $product_id, $price, $amount )
 *    Файл: includes/api/getData/getData.php
 *    Возвращает актуальную цену товара с учётом оптовой скидки.
 *    Тип "pageProduct" учитывает количество, введённое в форму,
 *    но ещё не добавленное в корзину.
 * 
 * 6. get_cartPageURL()
 *    Файл: includes/api/getSetting.php
 *    Возвращает URL страницы корзины.
 * 
 * ДАННЫЕ ТОВАРА (МЕТА-ПОЛЯ ЧЕРЕЗ get_post_meta из ArgonShop):
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
 * - unit_product → единица измерения товара (по умолчанию "шт")
 * - application  → текст блока "Применение" для таба
 * 
 * HTML-ЭЛЕМЕНТЫ, ОБРАБАТЫВАЕМЫЕ JS ПЛАГИНА:
 * 
 * - #basePrice              → текущая цена за единицу (обновляется AJAX) < id="basePrice"> ЦЕНА </ >
 * - #amountProduct          → поле ввода количества <input type="text" id="amountProduct" value="1">
 * - .plusProduct-Page       → кнопка "+" (увеличить количество)
 * - .minusProduct-Page      → кнопка "-" (уменьшить количество)
 * - #addShoppingCart        → кнопка "В корзину" (data-productid = ID товара) <input type="button" id="addShoppingCart" data-productid="ID товара" value="В корзину">
 * - #productPageCartBuy     → кнопка "Купить" (добавить + открыть корзину)
 * - .totalPrice span        → итоговая стоимость выбранного количества <div class="totalPrice">сумма <span></span> руб.</div>
 * - #howManyProducts span   → сколько единиц товара уже в корзине <div id="howManyProducts"><span>число</span></div>
 * - #addProductError        → блок для вывода ошибки добавления в корзину AJAX <div id="addProductError"></div>  
 * 
 * JS-ФАЙЛЫ, ОБРАБАТЫВАЮЩИЕ ЭЛЕМЕНТЫ:
 * 
 * - assets/interface/js/product.js (из темы)
 * - assets/interface/js/site.js (из темы)
 * - assets/interface/js/jquery.js (из темы)
 * 
 * CSS-КЛАССЫ, СТИЛИЗУЮЩИЕ БЛОКИ:
 * 
 * - .block-productMain, .block-productSlider (product.css)
 * - .block-wholesale-price (для таблицы оптовых цен)
 * - .actualStepDiscont (подсветка активного шага скидки)
 * - .block-productAbout (characteristics.css)
 * 
 * ШАБЛОНЫ ЧАСТЕЙ (get_template_part):
 * 
 * - template-parts/productPage/characteristics →
 *   блок характеристик товара (использует _characteristics_* мета-поля)
 * 
 * ВНЕШНИЕ ЗАВИСИМОСТИ:
 * 
 * - ACF (Advanced Custom Fields) — поля unit_product, application
 * - Fancybox — для просмотра изображений на весь экран
 * - Slick — для слайдера изображений
 */

// Защита от прямого вызова файла
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
            if ( function_exists( 'kama_breadcrumbs' ) ) {
                kama_breadcrumbs( ' » ' );
            }
            ?> 

            
            <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
                
                <?php 
                    
                    $product_id = get_the_ID();
                    
                    // Цена — приводим к числу.
                    // _price может содержать "5 000", "5,000" или пустую строку.
                    $price_raw = get_post_meta( $product_id, '_price', true );
                    $price     = floatval( str_replace( array( ' ', ',' ), array( '', '.' ), (string) $price_raw ) );
                    $price     = floatval( str_replace( array( ' ', ',' ), array( '', '.' ), (string) $price_raw ) );
                    $article     = get_post_meta( $product_id, '_article', true );
                    
                    $productsShoppingCart = function_exists( 'getCookie' ) ? getCookie( 'productsShoppingCart' ) : false;
                    
                    $actualStepDiscont = null;
                    
                    if ( function_exists( 'check_saleSteps' ) && function_exists( 'getActualStepDiscont' ) && function_exists( 'getActualPrice' ) ) {
                        
                        if ( check_saleSteps() && $productsShoppingCart ) {
                            
                            $actualStepDiscont = getActualStepDiscont( $product_id, $productsShoppingCart, 1 * $price );
                            $price = getActualPrice( 'pageProduct', $productsShoppingCart, $product_id, $price, 1 );
                        }
                    }
                    
                    
                    $price_display = ( $price === '' || $price === false || $price === null ) ? 'none' : $price;
                    
                    // Единица измерения
                    $unitProduct = 'шт';
                    
                    if ( function_exists( 'get_field' ) ) {
                        $unit_field = get_field( 'unit_product', $product_id );
                        
                        if ( $unit_field ) {
                            $unitProduct = $unit_field;
                        }
                    }
                    
                    // Блок "Применение"
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
                    
                    <div class="articlePost">артикул: <?php echo esc_html( $article ); ?></div>
                    
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
								    <span id="basePrice"><?php echo esc_html( $price_display ); ?></span> 
								    <span class="unitMeasure">Р/<?php echo esc_html( $unitProduct ); ?></span>
								</td>
							</tr>
								
							<?php
								    
								$wholesalePrice = get_post_meta( $product_id, '_wholesalePrice', true );
								
								if ( $wholesalePrice && is_array( $wholesalePrice ) ) {
								?>
								<tr>
									<td class="block-wholesale-price">
										    
									    <div class='title-wholesale-price'>ОПТОВАЯ ЦЕНА, от:</div>
										    
									    <table>
										        
									    <?php foreach ( $wholesalePrice as $key => $value ) { 
										        
        									    $term = get_term_by( 'id', absint( $key ), 'wholesalePrice' );
                        
                                                if ( ! $term || is_wp_error( $term ) ) {
                                                    continue;
                                                }
                                                
                                                $is_actual = ( $actualStepDiscont !== null && $actualStepDiscont == $term->name );
										        
										 ?>
										        
    									    <tr class="<?php echo $is_actual ? 'actualStepDiscont' : ''; ?>">
    									        
            									<td class="step-discont-name"> <span><?php echo esc_html( $term->name ); ?></span> Р </td>
            									<td class="step-discont-value"> <span><?php echo esc_html( $value ); ?></span> Р/<?php echo esc_html( $unitProduct ); ?> </td>
    										            
    										</tr>
										        
										 <?php } ?>
										        
									    </table>
									    
										<div class="clarify">учитывается стоимость товаров в корзине</div>
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
    										    
    									<input type="button" id="addShoppingCart" class="buttonPurchase" data-productid="<?php echo esc_attr( $product_id ); ?>" <?php echo $disabledProduct; ?> value="В корзину">
										<input type="button" id="productPageCartBuy" class="buttonPurchase" data-productid="<?php echo esc_attr( $product_id ); ?>" <?php echo $disabledProduct; ?> value="Купить">
									
									    <div class="totalPrice">сумма <span></span> руб.</div>
										    
									</div>
										    
										               
                                    <div id="howManyProducts" style="<?php if ( ! isset( $productsShoppingCart[ $product_id ] ) ) { ?> display:none <?php } ?>">
                                        
                                        <a href="<?php echo esc_url( get_cartPageURL() ); ?>"><input type="button" value="Перейти в корзину"></a>
                                        
                                        <div class="howManyProductsText">
                                        
                                            добавлено 
                                                        
                                            <span>
                                                <?php 
                                                                    
                                                if ( isset( $productsShoppingCart[ $product_id ] ) && $productsShoppingCart[ $product_id ] ) {
                                                                        
                                                    $amountProduct = $productsShoppingCart[ $product_id ]['amountProduct'];
                                                    echo esc_html( $amountProduct ); 
                                                }    
                                                                
                                                ?>
                                            </span> 
                                                            
                                            ед. данного товара<br/>
                                                
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
                        
                        <div class="itemControlPanel itemControlPanelActive" data-type="switch" name="productDescription">Описание</div>
                        <div class="itemControlPanel" data-type="switch" name="productСharacteristics">Характеристики</div>
                        
                        <?php if ( $productUsage ) { ?>
                        
                            <div class="itemControlPanel" data-type="switch" name="productUsage">Применение</div>
                        
                        <?php } ?>
                        
                    </div>
                    
                    <div class="block-productDescription">
                        
                        <div class="mobileItemControlPanel" data-mobileWidth="720" data-type="openingList" name="productDescription">Описание</div>
                        
                        <div class="blockItemPage" id="block_productDescription"><?php the_content(); ?></div>
                        
                    </div>
                    
                    <div class="block-productCharacteristics">
                        
                        <div class="mobileItemControlPanel" data-mobileWidth="720" data-type="openingList" name="productСharacteristics">Характеристики</div>
                        
                        <div class="blockItemPage" id="block_productСharacteristics"><?php get_template_part( 'template-parts/productPage/characteristics' ); ?></div>
                        
                    </div>
                    
                    <?php if ( $productUsage ) { ?>
                    
                        <div class="block-productUsage">
                            
                            <div class="mobileItemControlPanel" data-mobileWidth="720" data-type="openingList" name="productUsage">Применение</div>
                            
                            <div class="blockItemPage" id="block_productUsage"><?php echo $productUsage; ?></div>
                            
                        </div>
                    
                    <?php } ?>
				
				</div>
				
             <?php endwhile; endif; ?>
           
        </main>
        
    </div>
</section>

<?php get_footer(); ?>