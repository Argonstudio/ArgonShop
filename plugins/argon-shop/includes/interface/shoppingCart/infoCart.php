<?php
/**
 * Виджет корзины — отображение количества и стоимости товаров
 * 
 * @package Argon_Shop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * На всех страницах сайта (кроме страницы корзины) — виджет корзины в шапке или сайдбаре.
 * 
 * ЧТО ДЕЛАЕТ:
 * 1. Получает данные корзины из кук
 * 2. Выводит количество товаров и общую стоимость
 * 3. Ссылается на страницу корзины
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ФУНКЦИЯ: Вывод виджета корзины
 * 
 * ГДЕ ВЫЗЫВАЕТСЯ: В шаблоне темы (например, в header.php)
 * КАК ВЫЗВАТЬ: as_infocart();
 */
function as_infocart() {
    
    // Получаем ID страницы, товара или категории которую смотрим
    // Вернёт 0, если для просматриваемой страницы не предусмотрен ID
    $pageID = get_queried_object_id();
    
    // Не показываем виджет на странице корзины
    if ( $pageID !== get_cartPageID() ) {
        
        $productsShoppingCart = getCookie( 'productsShoppingCart' );
        
        // Проверяем, что куки содержат массив
        if ( ! is_array( $productsShoppingCart ) ) {
            $productsShoppingCart = array();
        }
        
        $dataCart = getDataCart( $productsShoppingCart );
        
        // Экранирование URL корзины
        $cart_url = esc_url( get_cartPageURL() );
        
        ?>
        <a href="<?php echo $cart_url; ?>">
        <?php
        
        if ( $dataCart ) {
            
            // Экранирование данных
            $product_amount = isset( $dataCart['productAmountCart'] ) ? esc_html( $dataCart['productAmountCart'] ) : '0';
            $total_price = isset( $dataCart['totalPrice'] ) ? esc_html( $dataCart['totalPrice'] ) : '0';
            
            ?>
            <div class="viewBlock-shoppingCart">
                <div class="viewBlock-amountProducts"><span class="viewBlock-productsStock"><?php echo $product_amount; ?></span></div>
                <div class="viewBlock-priceProducts"><span><?php echo $total_price; ?></span> Р </div>
            </div>
            <?php
            
        } else {
            ?>
            <div class="viewBlock-shoppingCart">
                <div class="viewBlock-amountProducts"><span></span></div>
                <div class="viewBlock-priceProducts" style="display:none"><span></span> Р </div>
            </div>
            <?php
        }
        
        ?>
        </a>
        <?php
    }
}