<?php
/**
 * Кнопки добавления в корзину (карточка товара)
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Карточка товара в каталоге, в блоке «Хиты продаж» и т. д.
 * 
 * ЧТО ОН ДЕЛАЕТ:
 * Выводит кнопки «В корзину» и «Купить» или ссылку на корзину
 * в зависимости от того, добавлен ли товар
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ФУНКЦИЯ: выводит кнопки добавления в корзину
 * 
 * @param int        $productID            — ID товара
 * @param bool|mixed $cardInBascet         — показывать кнопку «В корзину»
 * @param bool|mixed $cardBuy              — показывать кнопку «Купить»
 * @param array      $productsShoppingCart — товары в корзине
 */
function as_button_card_product( $productID, $cardInBascet, $cardBuy, $productsShoppingCart ) {
    
    $productID = absint( $productID );
    $productID_esc = esc_attr( $productID );
    
    // Убеждаемся, что корзина — массив
    if ( ! is_array( $productsShoppingCart ) ) {
        $productsShoppingCart = array();
    }
    
    // Товар уже в корзине?
    $in_cart = isset( $productsShoppingCart[ $productID ] ) && $productsShoppingCart[ $productID ];
    
    // URL корзины
    $cart_url = esc_url( as_get_cartPageURL() );
    
    ?>
    
    <!-- Кнопки добавления -->
    <div class="block-cardProductBascet" style="<?php if ( $in_cart ) { ?> display:none <?php } ?>">
        
        <?php if ( $cardInBascet !== false ) { ?>
        
            <input type="button" data-productid="<?php echo $productID_esc; ?>" class="card-inBascet" value="<?php esc_attr_e( 'Add to cart', 'argon-shop' ); ?>">
        
        <?php } ?>
        
        <?php if ( $cardBuy !== false ) { ?>
        
            <input type="button" data-productid="<?php echo $productID_esc; ?>" class="card-buy" value="<?php esc_attr_e( 'Buy', 'argon-shop' ); ?>">
        
        <?php } ?> 
        
    </div>
    
    <!-- Блок «товар в корзине» -->
    <div class="card-alreadyAdded" style="<?php if ( ! $in_cart ) { ?> display:none <?php } ?>">
        
        <?php 
        
        $cardalreadyAddedValue = __( 'Product added to cart', 'argon-shop' );
        
        $queried_object_id = get_queried_object_id();
        
        if ( $queried_object_id && as_get_cartPageID() === $queried_object_id ) {
            $cardalreadyAddedValue = __( 'Update cart', 'argon-shop' );
        }
        
        $cardalreadyAddedValue_esc = esc_attr( $cardalreadyAddedValue );
        
        ?>
        
        <a href="<?php echo $cart_url; ?>"><input type="button" value="<?php echo $cardalreadyAddedValue_esc; ?>"></a>
        
    </div>
    
    <!-- Блок ошибки -->
    <div class="card-addProductError"></div>
    
    <?php
}
