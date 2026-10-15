<?php
/**
 * Виджет корзины — показывает количество и стоимость товаров
 * 
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * На каждой странице сайта (кроме страницы корзины) — виджет корзины в шапке или сайдбаре.
 * 
 * ЧТО ОН ДЕЛАЕТ:
 * 1. Получает данные корзины из cookie
 * 2. Выводит количество товаров и итоговую стоимость
 * 3. Ссылается на страницу корзины
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ФУНКЦИЯ: выводит виджет корзины
 * 
 * ВЫЗЫВАЕТСЯ ИЗ: шаблона темы (например, header.php)
 * HOW TO CALL: as_infocart();
 */
function as_infocart() {
    
    // Получаем ID просматриваемой страницы, товара или категории
    // Возвращает 0, если у просматриваемой страницы нет ID
    $pageID = get_queried_object_id();
    
    // Не показываем виджет на странице корзины
    if ( $pageID !== as_get_cartPageID() ) {
        
        $productsShoppingCart = as_getCookie( 'productsShoppingCart' );
        
        // Убеждаемся, что cookie содержит массив
        if ( ! is_array( $productsShoppingCart ) ) {
            $productsShoppingCart = array();
        }
        
        $dataCart = as_getDataCart( $productsShoppingCart );
        
        // Экранируем URL корзины
        $cart_url = esc_url( as_get_cartPageURL() );
        
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