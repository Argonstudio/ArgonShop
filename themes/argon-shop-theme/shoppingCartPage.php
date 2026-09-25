<?php
/**
 * Шаблон страницы корзины (shoppingCart)
 * 
 * Template Name: shoppingCart
 *
 * @package ArgonShopTheme
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Страница корзины
 * URL: /korzina/
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 * 
 * Это одна из наиболее тесно связанных с плагином страниц темы.
 * Через этот файл проходит весь цикл оформления заказа.
 * 
 * ФУНКЦИИ ПЛАГИНА, ИСПОЛЬЗУЕМЫЕ В ШАБЛОНЕ:
 * 
 * 1. kama_breadcrumbs( ' » ' )
 *    Файл: includes/interface/breadcrumbs.php
 *    Выводит хлебные крошки: Главная » Корзина
 * 
 * 2. getCookie( 'productsShoppingCart' )
 *    Файл: includes/api/getData/getData.php
 *    Читает куки корзины в виде ассоциативного массива:
 *    [product_id => ['amountProduct' => N, 'price' => X], ...]
 *    Возвращает false, если корзина пуста.
 * 
 * 3. getActualPrice( "cart", $cart, $product_id )
 *    Файл: includes/api/getData/getData.php
 *    Возвращает актуальную цену товара с учётом оптовой скидки.
 *    Тип "cart" считает скидку от всей суммы товаров в корзине.
 * 
 * 4. getClassActiveItem( $user_ID, $type )
 *    Файл: includes/api/getSetting.php
 *    Определяет CSS-класс активного таба оформления заказа
 *    в зависимости от заполненности данных пользователя.
 * 
 * HTML-ЭЛЕМЕНТЫ, ОБРАБАТЫВАЕМЫЕ JS ПЛАГИНА:
 * 
 * - table.blockCartProduct[data-productid]  → блок товара в корзине, < data-productid="id товара" class="blockCartProduct" >
 * - input.deleteProduct[data-productid]     → удалить товар, <input type="button" data-productid="id товара" class="deleteProduct">
 * - input.deleteAllProducts                 → очистить корзину, <input type="button" class="deleteAllProducts" value="Очистить корзину">
 * - .errorDeleteProducts                    → ошибки очистки, <div class="errorDeleteProducts"></div>
 * - input.shoppingCartAmountProduct         → поле количества товара, <input type="text" data-productid="<?php echo $key ?>" class="shoppingCartAmountProduct" value="<?php echo $value["amountProduct"]; ?>">
 * - input.plusProduct / .minusProduct       → изменить количество, <input type="button" data-productid="<?php echo $key ?>" class="plusProduct" value="+">
 * - .productPrice span                      → цена за единицу, < class="productPrice"><span> ЦЕНА </span> </ >
 * - .amountProductWeight span               → общий вес товара(с учетом числа единиц товара), < class="amountProductWeight" style="<?php if(!$cartTotalWeight) echo "display:none"; ?>"><span> ВЕС </span> </ > 
 * - .amountProductPrice span                → сумма за товар(с учетом числа единиц товара), < class="amountProductPrice"><span> ЦЕНА </span> </ >
 * - .cartTotalWeight span                   → вес всей корзины, < class="cartTotalWeight"> <span> ВЕС </span> </ >
 * - .cartTotalPrice span                    → цена всей корзины, < class="cartTotalPrice"> <span> ЦЕНА </span> </ >
 * - .shoppingCartError                      → ошибка на карточке товара
 * - .pageControlPanel .itemControlPanel     → табы форм оформления
 * 
 * ФОРМЫ ОФОРМЛЕНИЯ (get_template_part):
 * 
 * - template-parts/cart/quickOrder   → краткая форма
 * - template-parts/cart/person       → форма физлица
 * - template-parts/cart/legalPerson  → форма юрлица
 * 
 * Эти шаблоны внутри используют функцию show_user_fields() из
 * плагина (includes/api/getSetting.php) для вывода полей форм.
 * 
 * JS-ФАЙЛЫ, ОБРАБАТЫВАЮЩИЕ КОРЗИНУ:
 * 
 * - assets/interface/js/cart.js (из темы)
 * - assets/interface/js/site.js (из темы)
 * 
 * AJAX-ОБРАБОТЧИКИ ПЛАГИНА (shoppingCart.php):
 * 
 * - amountProducts_shoppingCart    → изменение количества
 * - deleteProducts_shoppingCart    → удаление товара
 * - deleteAllProducts_shoppingCart → очистка корзины
 * - submitCart_shoppingCart        → оформление заказа
 * 
 * ВНЕШНИЕ ЗАВИСИМОСТИ:
 * 
 * - ACF — поле unit_product у товара
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
            // Функция плагина Argon Shop (includes/interface/breadcrumbs.php)
            if ( function_exists( 'kama_breadcrumbs' ) ) {
                kama_breadcrumbs( ' » ' );
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
                
                // Функция плагина Argon Shop (includes/api/getData/getData.php)
                $productsShoppingCart = function_exists( 'getCookie' ) ? getCookie( 'productsShoppingCart' ) : false;
                
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
                        
                        // Функция плагина Argon Shop (includes/api/getData/getData.php)
                        $post_price = function_exists( 'getActualPrice' ) 
                            ? getActualPrice( 'cart', $productsShoppingCart, $key ) 
                            : floatval( get_post_meta( $key, '_price', true ) );
                        
                        $amount = isset( $value['amountProduct'] ) ? floatval( $value['amountProduct'] ) : 0;
                        
                        $cartTotalWeight += $post_weight * $amount;
                        $cartTotalPrice  += $post_price * $amount;
                    }
                    
                    ?>
                    <div class="block-cartProductsControl">
                        
                        <div class="block-totalResult">
                        
                            <div class="cartTotalPrice">Итоговая цена: <span><?php echo esc_html( $cartTotalPrice ); ?></span> Р </div>
                            <div class="cartTotalWeight" style="<?php if ( ! $cartTotalWeight ) echo 'display:none'; ?>">Итоговый вес: <span><?php echo esc_html( $cartTotalWeight ); ?></span> кг </div>
                            
                        </div>
                        
                        <div class="block-deleteAllProducts">
                        
                            <input type="button" class="deleteAllProducts" value="Очистить корзину">
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
                        
                        // Пропускаем, если товар удалён
                        if ( ! $post_data ) {
                            continue;
                        }
                        
                        $post_img     = get_the_post_thumbnail_url( $key, 'thumbnail' );
                        $post_link    = get_post_permalink( $key );
                        $post_name    = $post_data->post_title;
                        $post_weight  = get_post_meta( $key, '_weight', true );
                        $post_article = get_post_meta( $key, '_article', true );
                        
                        // Функция плагина Argon Shop (includes/api/getData/getData.php)
                        $post_price = function_exists( 'getActualPrice' ) 
                            ? getActualPrice( 'cart', $productsShoppingCart, $key ) 
                            : floatval( get_post_meta( $key, '_price', true ) );
                        
                        $post_weight = str_replace( ',', '.', $post_weight );
                        $post_weight = (float) $post_weight;
                        
                        $amount = isset( $value['amountProduct'] ) ? floatval( $value['amountProduct'] ) : 0;
                        
                        // Единица измерения через ACF
                        $unitProduct = 'шт';
                        
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
                                                    
                                                    <div class="block-bascetProductArticle">артикул: <?php echo esc_html( $post_article ); ?></div>
                                                
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
                                            <td class="productPriceTitle">Р/<?php echo esc_html( $unitProduct ); ?></td>
                                            <td>Количество</td>
                                            
                                            <?php if ( $post_weight ) { ?>
                                            
                                                <td class="amountProductWeightTitle">Вес</td>
                                            
                                            <?php } ?>
                                            
                                            <td class="amountProductPriceTitle">Цена</td>
                                            
                                            <td class="mobileBlock-amountProductTitle">Товар</td>
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
                                            
                                                <td class="amountProductWeight"><span><?php echo esc_html( $post_weight * $amount ); ?></span> кг</td>
                                            
                                            <?php } ?>
                                            
                                            <td class="amountProductPrice"><span><?php echo esc_html( $post_price * $amount ); ?></span> Р</td>
                                            
                                            <td class="mobileBlock-amountProduct">
                                                
                                                <div class="productPrice mobileBlock-productPrice">Р/<?php echo esc_html( $unitProduct ); ?>: <span><?php echo esc_html( $post_price ); ?></span> </div>
                                                
                                                <?php if ( $post_weight ) { ?>
                                            
                                                <div class="amountProductWeight">Вес: <span><?php echo esc_html( $post_weight * $amount ); ?></span> кг</div>
                                            
                                                <?php } ?>
                                                
                                                <div class="amountProductPrice">Цена: <span><?php echo esc_html( $post_price * $amount ); ?></span> Р</div>
                                                
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
                        
                            <div class="cartTotalPrice">Итоговая цена: <span><?php echo esc_html( $cartTotalPrice ); ?></span> Р </div>
                            <div class="cartTotalWeight" style="<?php if ( ! $cartTotalWeight ) echo 'display:none'; ?>">Итоговый вес: <span><?php echo esc_html( $cartTotalWeight ); ?></span> кг </div>
                            
                        </div>
                        
                        <div class="block-deleteAllProducts">
                        
                            <input type="button" class="deleteAllProducts" value="Очистить корзину">
                            <div class="errorDeleteProducts"></div>
                        
                        </div>
                        
                    </div>
				
    				<div class="block-orderRegistration">
    				
        				<h1>Оформление заказа</h1>
        				
        				<?php 
        				    $accountData = get_user_meta( $user_ID, 'accountData', true );
        				?>
        				
        				
        				<div class="pageControlPanel cartControlPanel">
                            
                            <?php 
                            // Функция плагина Argon Shop (includes/api/getSetting.php)
                            // Возвращает CSS-класс активного таба в зависимости от
                            // заполненности данных пользователя.
                            
                            $class_quick  = function_exists( 'getClassActiveItem' ) ? getClassActiveItem( $user_ID, 'cartQuickOrder' ) : '';
                            $class_person = function_exists( 'getClassActiveItem' ) ? getClassActiveItem( $user_ID, 'cartPerson' ) : '';
                            $class_legal  = function_exists( 'getClassActiveItem' ) ? getClassActiveItem( $user_ID, 'cartLegalPerson' ) : '';
                            ?>
                            
                            <div class="itemControlPanel <?php echo esc_attr( $class_quick ); ?>" data-type="switch" name="cartQuickOrder">Краткое оформление</div>
                            <div class="itemControlPanel <?php echo esc_attr( $class_person ); ?>" data-type="switch" name="cartPerson">Физические лица</div>
                            <div class="itemControlPanel <?php echo esc_attr( $class_legal ); ?>" data-type="switch" name="cartLegalPerson">Юридические лица</div>
                            
                        </div>
                        
                        <div class="pageControlPanel cartControlPanel-mobile">
                            
                            <div class="itemControlPanel <?php echo esc_attr( $class_quick ); ?>" data-type="switch" name="cartQuickOrder">Быстро</div>
                            <div class="itemControlPanel <?php echo esc_attr( $class_person ); ?>" data-type="switch" name="cartPerson">Физлица</div>
                            <div class="itemControlPanel <?php echo esc_attr( $class_legal ); ?>" data-type="switch" name="cartLegalPerson">Юрлица</div>
                            
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
                                
                                
                                Устанавливая флажок, я выражаю своё согласие на обработку <a href="/konfidentsialnost-personalnoj-informatsii/" target="_blank">данных для демонстрационного стенда</a> (включая имя, фамилию, адрес, телефон, email, ИНН, КПП, юридический адрес, логин) и технических файлов <strong>cookie</strong> в целях демонстрации и тестирования интерфейса сайта. Я понимаю, что данный сайт и все формы являются только симуляцией интернет-магазина, и не ввожу свои настоящие персональные данные. С условиями обработки ознакомлен(а).
                                
                                
                            </label>
                            
                        </div>
                        
        				
        				<?php
        				
        				} else {
        				    
        				    echo 'Корзина пуста';
        				  
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