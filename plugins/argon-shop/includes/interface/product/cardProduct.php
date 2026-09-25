<?php
/**
 * Кнопки добавления товара в корзину (карточка товара)
 *
 * @package Argon_Shop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Карточка товара в каталоге, в блоке "Хиты продаж" и тд
 * 
 * ЧТО ДЕЛАЕТ:
 * Выводит кнопки "В корзину", "Купить" или ссылку на корзину
 * в зависимости от того, добавлен ли товар
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ФУНКЦИЯ: Вывод кнопок добавления товара
 * 
 * @param int        $productID            — ID товара
 * @param bool|mixed $cardInBascet         — показывать кнопку "В корзину"
 * @param bool|mixed $cardBuy              — показывать кнопку "Купить"
 * @param array      $productsShoppingCart — товары в корзине
 */
function button_card_product( $productID, $cardInBascet, $cardBuy, $productsShoppingCart ) {
    
    $productID = absint( $productID );
    $productID_esc = esc_attr( $productID );
    
    // Проверяем, что корзина — массив
    if ( ! is_array( $productsShoppingCart ) ) {
        $productsShoppingCart = array();
    }
    
    // Товар уже в корзине?
    $in_cart = isset( $productsShoppingCart[ $productID ] ) && $productsShoppingCart[ $productID ];
    
    // URL корзины
    $cart_url = esc_url( get_cartPageURL() );
    
    ?>
    
    <!-- Кнопки добавления -->
    <div class="block-cardProductBascet" style="<?php if ( $in_cart ) { ?> display:none <?php } ?>">
        
        <?php if ( $cardInBascet !== false ) { ?>
        
            <input type="button" data-productid="<?php echo $productID_esc; ?>" class="card-inBascet" value="В корзину">
        
        <?php } ?>
        
        <?php if ( $cardBuy !== false ) { ?>
        
            <input type="button" data-productid="<?php echo $productID_esc; ?>" class="card-buy" value="Купить">
        
        <?php } ?> 
        
    </div>
    
    <!-- Блок товар в корзине -->
    <div class="card-alreadyAdded" style="<?php if ( ! $in_cart ) { ?> display:none <?php } ?>">
        
        <?php 
        
        $cardalreadyAddedValue = 'Товар добавлен в корзину';
        
        $queried_object_id = get_queried_object_id();
        
        if ( $queried_object_id && get_cartPageID() === $queried_object_id ) {
            $cardalreadyAddedValue = 'Обновить корзину';
        }
        
        $cardalreadyAddedValue_esc = esc_attr( $cardalreadyAddedValue );
        
        ?>
        
        <a href="<?php echo $cart_url; ?>"><input type="button" value="<?php echo $cardalreadyAddedValue_esc; ?>"></a>
        
    </div>
    
    <!-- Блок для ошибок -->                                             
    <div class="card-addProductError"></div>
    
    <?php
}
