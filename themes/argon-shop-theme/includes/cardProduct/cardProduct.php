<?php
/**
 * Карточка товара (шаблон части)
 *
 * @package ArgonShopTheme
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ИСПОЛЬЗУЕТСЯ:
 * Подключается через require из functions.php темы.
 * Вызывается в шаблонах каталога, поиска, sidebar, главной:
 * 
 * - taxonomy-catalog.php
 * - search.php
 * - tag.php
 * - archive.php
 * - single-product.php (блок похожих товаров)
 * - sidebar.php (через view_products_list)
 * - history.php
 * 
 * ЧТО ВЫВОДИТ:
 * Карточку товара с изображением, характеристиками, ценой
 * и кнопками покупки.
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 * 
 * ФУНКЦИИ ПЛАГИНА:
 * 
 * 1. get_charact( $type, $value, $productID )
 *    Файл: includes/api/getData/admin/get_characteristics.php
 *    Возвращает значение характеристики товара.
 *    Для "id" — ищет по ID термина, для "name" — по имени.
 *    Если у характеристики есть дочерние — возвращает массив
 *    выбранных вариантов.
 * 
 * 2. check_saleSteps()
 *    Файл: includes/api/getSetting.php
 *    Проверяет, включена ли система оптовых скидок.
 * 
 * 3. getActualPrice( $type, $cart, $productID, $price, $amount )
 *    Файл: includes/api/getData/getData.php
 *    Возвращает актуальную цену с учётом оптовой скидки.
 *    Тип "cardProduct" — расчёт для карточки товара.
 * 
 * 4. button_card_product( $productID, $cardInBascet, $cardBuy, $productsShoppingCart )
 *    Файл: includes/interface/product/cardProduct.php
 *    Выводит кнопки "В корзину" / "Купить" или ссылку
 *    на корзину (если товар уже добавлен).
 * 
 * МЕТА-ПОЛЯ ТОВАРА:
 * 
 * - _price          → цена
 * - _article        → артикул
 * - _weight         → вес
 * - _hit            → "on" если хит продаж
 * - _newProduct     → "on" если новинка
 * 
 * ACF-ПОЛЯ:
 * 
 * - unit_product → единица измерения (шт, м², кг и т.д.)
 * 
 * ХАРАКТЕРИСТИКИ:
 * 
 * - ID 24 — "ТОРГОВАЯ МАРКА" (жёстко зашито в код)
 *   Используется для вывода бренда товара.
 * 
 * AJAX-ОБРАБОТЧИКИ КНОПОК:
 * 
 * - addProducts_shoppingCart  — добавить товар в корзину
 *   Файл: includes/interface/shoppingCart/shoppingCart.php
 * 
 * JS-ФАЙЛЫ:
 * 
 * - assets/interface/js/cardProduct.js — обработка кнопок карточки
 * - assets/interface/js/site.js        — общий функционал
 * 
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ФУНКЦИЯ: Вывод карточки товара
 * 
 * @param WP_Post     $post                  — объект поста (текущий товар в цикле)
 * @param array|false $productsShoppingCart — товары в корзине
 */
function generate_product_card( $post, $productsShoppingCart ) {
    
    $productID = get_the_ID();
    
    // Валидация: если пост некорректный — не выводим карточку
    if ( ! $productID ) {
        return;
    }
    
    // Мета-поля товара
    $price   = get_post_meta( $productID, '_price', true );
    $article = get_post_meta( $productID, '_article', true );
    $weight  = get_post_meta( $productID, '_weight', true );
    $hit     = get_post_meta( $productID, '_hit', true );
    $new     = get_post_meta( $productID, '_newProduct', true );
    
    // Торговая марка — характеристика с ID 24
    // Функция плагина Argon Shop (includes/api/getData/admin/get_characteristics.php)
    $trademark = function_exists( 'get_charact' ) 
        ? get_charact( 'id', 24, $productID ) 
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
                    <li>Артикул: <?php echo esc_html( $article ); ?></li>
                <?php } ?>
                
                <?php if ( $trademark && is_array( $trademark ) && ! empty( $trademark[0] ) ) { ?>
                    <li>ТМ: <?php echo esc_html( $trademark[0] ); ?></li>
                <?php } elseif ( $trademark && is_string( $trademark ) ) { ?>
                    <li>ТМ: <?php echo esc_html( $trademark ); ?></li>
                <?php } ?>
                
                <?php if ( $weight ) { ?>
                    <li>Вес: <?php echo esc_html( $weight ); ?> кг</li>
                <?php } ?>
                
            </ul>
            
        </div>
        
        <a href="<?php the_permalink(); ?>" class="cardProductName">
            
            <?php the_title(); ?>
            
        </a>
        
        <?php 
        
        if ( $price ) { 
            
            // Расчёт актуальной цены с учётом оптовой скидки
            // Функция плагина Argon Shop (includes/api/getData/getData.php)
            if ( function_exists( 'check_saleSteps' ) && function_exists( 'getActualPrice' ) ) {
                
                if ( check_saleSteps() && $productsShoppingCart && is_array( $productsShoppingCart ) ) {
                    
                    $price = getActualPrice( 'cardProduct', $productsShoppingCart, $productID, $price, 1 );
                }
            }
            
            // Единица измерения через ACF
            $unitProduct = 'шт';
            
            if ( function_exists( 'get_field' ) ) {
                
                $unit_field = get_field( 'unit_product', $productID );
                
                if ( $unit_field ) {
                    
                    $unitProduct = $unit_field;
                    
                    // Сокращение длинной единицы измерения
                    $unitProductLength = mb_strlen( $unitProduct, 'utf-8' );
                    
                    if ( $unitProductLength > 13 ) {
                        $unitProduct = mb_substr( $unitProduct, 0, 13 );
                        $unitProduct .= '..';
                    }
                }
            }
            
        ?>
            
            <div class="cardProductPrice"> <span><?php echo esc_html( $price ); ?></span> Р/<?php echo esc_html( $unitProduct ); ?></div>
            
            <?php 
            
            // Кнопки покупки
            // Функция плагина Argon Shop (includes/interface/product/cardProduct.php)
            if ( function_exists( 'button_card_product' ) ) {
                button_card_product( $productID, true, true, $productsShoppingCart );
            }
            
            ?>
            
        <?php } ?>
        
        
        
    </div>
    
    <?php
}