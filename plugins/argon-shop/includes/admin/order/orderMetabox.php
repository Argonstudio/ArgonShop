<?php
/**
 * Метабоксы заказа в админ-панели
 * 
 * @package Argon_Shop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * WordPress Админка → Заказы → Редактирование конкретного заказа
 * URL: /wp-admin/post.php?post=ID_ЗАКАЗА&action=edit
 * 
 * Метабоксы:
 * 1. "Товары" — список товаров в заказе + добавление новых
 * 2. "Информация от заказчика" — данные покупателя, доставка, оплата
 * 3. "Статус заказа" — смена статуса (Новый, В обработке, Выполнен)
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Добавляем новые метабоксы таксономии
add_action( 'add_meta_boxes', 'orders_add_meta_box' );

function orders_add_meta_box() {
    
    add_meta_box( 'detailOrders_meta_box', 'Товары', 'detailOrders_metabox', 'shoporder', 'normal', 'core' );
    add_meta_box( 'detailBuyer_meta_box', 'Информация от заказчика', 'detailBuyer_metabox', 'shoporder', 'normal', 'core' );
    add_meta_box( 'statusOrders_meta_box', 'Статус заказа', 'statusOrders_metabox', 'shoporder', 'side', 'core' );
}

/**
 * МЕТАБОКС: Статус заказа
 * 
 * ГДЕ ТЕСТИРОВАТЬ: Страница заказа → правая колонка → "Статус заказа"
 * ЧТО ДЕЛАЕТ: Выводит радио-кнопки для выбора статуса заказа
 *             (Новый / В обработке / Выполнен)
 */
function statusOrders_metabox( $post ) {
    
    $argumentStatusAddTerms = array(
        'taxonomy'   => 'statusorders',
        'hide_empty' => false,
        'fields'     => 'id=>name',
    );
    
    $statusTerms = get_terms( $argumentStatusAddTerms );
    
    $currentStatus = get_the_terms( $post->ID, 'statusorders' );
    $currentStatus = ! empty( $currentStatus ) && ! is_wp_error( $currentStatus ) ? $currentStatus[0]->term_id : 0;
    
    foreach ( $statusTerms as $key => $value ) {
        
        $checked = ( $key === $currentStatus ) ? "checked='checked'" : '';
        
        // Экранирование вывода
        $key_esc = esc_attr( $key );
        $value_esc = esc_html( $value );
        
        echo "<label><input type='radio' name='status' value='{$key_esc}' {$checked} />{$value_esc}</label><br />";
    }
}

/**
 * МЕТАБОКС: Товары
 * 
 * ГДЕ ТЕСТИРОВАТЬ: Страница заказа → основной блок "Товары"
 * ЧТО ДЕЛАЕТ: 
 * 1. Выводит список товаров, добавленных в заказ
 * 2. Поле для поиска и добавления новых товаров
 * 3. Кнопки "+" и "-" для изменения количества
 * 4. Кнопка удаления товара из заказа
 */
function detailOrders_metabox( $post ) {
    
    $productsCart = get_post_meta( $post->ID, '_productsCart', true );
    
    ?>
    
    <div class="orderAllProducts">
    
    <?php
    
    if ( ! empty( $productsCart ) && is_array( $productsCart ) ) {
        
        foreach ( $productsCart as $key => $value ) {
            
            $key = absint( $key );
            
            if ( $key > 0 ) {
                createTableProduct( $key, $value );
            }
        }
    }
    
    ?>
    
    </div>
    
    <div class="blockAddOrderProduct">
        
        Добавить товар
        
        <input type="text" class="valueAddOrder" placeholder="название или артикул товара">
        <div class="addOrderResult"></div>
        
    </div>
    
    <div class="orderError"></div>
    
    <?php
}

/**
 * ФУНКЦИЯ: Создание HTML-таблицы товара в заказе
 * 
 * ГДЕ ТЕСТИРОВАТЬ: Автоматически вызывается в метабоксе "Товары"
 * ЧТО ДЕЛАЕТ: Выводит таблицу с информацией о товаре:
 *             - Название и ссылка
 *             - Цена за штуку
 *             - Количество (с кнопками + и -)
 *             - Вес (если есть)
 *             - Итоговая цена
 * 
 * @param int   $idProduct   — ID товара
 * @param array $dataProduct — данные из сохранённого заказа (опционально)
 */
function createTableProduct( $idProduct, $dataProduct = '' ) {
    
    // Валидация ID
    $idProduct = absint( $idProduct );
    
    if ( 0 === $idProduct ) {
        return;
    }
    
    $post_data = get_post( $idProduct );
    
    if ( ! $post_data ) {
        return;
    }
    
    $post_name;
    $amountProduct;
    $post_link;
    $post_price;
    
    if ( $dataProduct ) {
        
        $post_link = isset( $dataProduct['link'] ) ? esc_url( $dataProduct['link'] ) : '';
        $amountProduct = isset( $dataProduct['amountProduct'] ) ? floatval( $dataProduct['amountProduct'] ) : 1;
        $post_name = isset( $dataProduct['name'] ) ? esc_html( $dataProduct['name'] ) : '';
        $post_price = isset( $dataProduct['price'] ) ? floatval( $dataProduct['price'] ) : 0;
        
    } else {
        
        $amountProduct = 1;
        $post_name = esc_html( $post_data->post_title );
        $post_link = esc_url( get_post_permalink( $idProduct ) );
        $post_price = floatval( get_post_meta( $idProduct, '_price', true ) );
    }
    
    $post_weight = floatval( get_post_meta( $idProduct, '_weight', true ) );
    
    ?>
    
    <table data-productid="<?php echo esc_attr( $idProduct ); ?>" class="blockOrderProduct">
        <tr>
            <td>
                <a class="orderProductTitle" href="<?php echo $post_link; ?>"><?php echo $post_name; ?></a> 
                <input type="button" data-productid="<?php echo esc_attr( $idProduct ); ?>" class="deleteProduct">                
            </td>
        </tr>
        
        <tr>
            <td>
                <table class="block-orderCharactProduct">
                    
                    <tr class="block-orderCharactProductTitle">
                        <td>₽/шт</td>
                        <td>Количество</td>
                        
                        <?php if ( $post_weight ) { ?>
                            <td>Вес</td>
                        <?php } ?>
                        
                        <td>Цена</td>
                    </tr>
                    
                    <tr class="block-orderCharactProductContent">
                        <td class="productPrice"><span><?php echo esc_html( $post_price ); ?></span></td>
                        
                        <td class="block-orderAmountProduct">
                            <input type="text" data-productid="<?php echo esc_attr( $idProduct ); ?>" name="order_product[<?php echo esc_attr( $idProduct ); ?>]" class="orderAmountProduct" value="<?php echo esc_attr( $amountProduct ); ?>">
                            
                            <div class="block-orderChangeValue">
                                <input type="button" data-productid="<?php echo esc_attr( $idProduct ); ?>" class="plusProduct" value="+">
                                <input type="button" data-productid="<?php echo esc_attr( $idProduct ); ?>" class="minusProduct" value="-">
                            </div>
                        </td>
                        
                        <?php if ( $post_weight ) { ?>
                            <td class="amountProductWeight"><span><?php echo esc_html( $post_weight * $amountProduct ); ?></span> кг</td>
                        <?php } ?>
                        
                        <td class="amountProductPrice"><span><?php echo esc_html( $post_price * $amountProduct ); ?></span> ₽</td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
    
    <?php
}

/**
 * МЕТАБОКС: Информация от заказчика
 * 
 * ГДЕ ТЕСТИРОВАТЬ: Страница заказа → основной блок "Информация от заказчика"
 * ЧТО ДЕЛАЕТ:
 * 1. Выводит заполненные покупателем поля (имя, телефон, email и др.)
 * 2. Выпадающий список для добавления недостающих полей
 * 3. Радио-кнопки способа доставки
 * 4. Радио-кнопки способа оплаты
 */
function detailBuyer_metabox( $post ) {
    
    // Nonce-поле для защиты при сохранении
    wp_nonce_field( 'detailOrders_protect_action', 'detailOrders_protect_name' );
    
    $fieldsCart = get_post_meta( $post->ID, '_fieldsCart', true );
    $shipping = get_post_meta( $post->ID, '_shipping', true );
    $payment = get_post_meta( $post->ID, '_payment', true );
    
    ?>
    
    <ul class='advanced-fields'>
    
    <?php
    
    if ( ! empty( $fieldsCart ) && is_array( $fieldsCart ) ) {
        
        foreach ( $fieldsCart as $keyField => $valueArrayField ) {
            
            if ( ! empty( $valueArrayField['value'] ) ) {
                
                if ( $keyField === 'messageUser' ) {
                    as_getHTMLField( 'textarea', $keyField, $valueArrayField );
                } else {
                    as_getHTMLField( 'input', $keyField, $valueArrayField );
                }
            }
        }
    }
    
    ?>
    
    </ul>
    
    <div class="as-order-itemTitle">Добавить отсутствующие поля из:</div>
    
    <select class="as-order-addFieldsSelect">
        <option selected value="person">Физические лица</option>
        <option value="legalPerson">Юридические лица</option>
    </select>
    
    <input type="button" class="as-order-addFieldsSend" value="Добавить">
    
    <div class="as-order-addFieldsError"></div>
    <p></p>
    
    <div class="as-order-itemTitle">Способ доставки:</div>
    
    <?php
    
    $typeShipping = array(
        'selfExport'        => 'Самовывоз',
        'shippingToAddress' => 'Доставка по адресу',
    );
    
    foreach ( $typeShipping as $key => $value ) {
        
        $checked = ( $key === $shipping ) ? "checked='checked'" : '';
        
        // Экранирование
        $key_esc = esc_attr( $key );
        $value_esc = esc_html( $value );
        
        echo "<label><input type='radio' name='shipping' value='{$key_esc}' {$checked} />{$value_esc}</label><br />";
    }
    
    ?>
    
    <div class="as-order-itemTitle">Способ оплаты:</div>
    
    <?php
    
    $typePaiment = array(
        'paimentUponReceipt' => 'Оплата при получении',
    );
    
    foreach ( $typePaiment as $key => $value ) {
        
        $checked = ( $key === $payment ) ? "checked='checked'" : '';
        
        // Экранирование
        $key_esc = esc_attr( $key );
        $value_esc = esc_html( $value );
        
        echo "<label><input type='radio' name='payment' value='{$key_esc}' {$checked} />{$value_esc}</label><br />";
    }
}

/**
 * ФУНКЦИЯ: Вывод HTML-поля формы
 * 
 * ГДЕ ТЕСТИРОВАТЬ: Автоматически вызывается в метабоксе "Информация от заказчика"
 * ЧТО ДЕЛАЕТ: Выводит поле с label, input/textarea и скрытым полем с названием
 * 
 * @param string $type            — 'input' или 'textarea'
 * @param string $keyField        — ключ поля (например, addressUser)
 * @param array  $valueArrayField — массив с данными (name, value)
 */
function as_getHTMLField( $type, $keyField, $valueArrayField ) {
    
    // Экранирование данных
    $keyField_esc = esc_attr( $keyField );
    $fieldName = isset( $valueArrayField['name'] ) ? esc_html( $valueArrayField['name'] ) : '';
    $fieldValue = isset( $valueArrayField['value'] ) ? esc_html( $valueArrayField['value'] ) : '';
    
    $class = 'advanced-field';
    
    if ( $keyField === 'addressUser' || $keyField === 'legalAddressUser' ) {
        $class .= ' as-order-fieldAddress';
    }
    
    ?>
    
    <li class="<?php echo esc_attr( $class ); ?>" data-keyfield="<?php echo $keyField_esc; ?>"> 
        
        <div class="span-advanced-fields"><?php echo $fieldName; ?></div>
        
        <?php if ( $type === 'input' ) { ?>
        
            <input type="text" name="_fieldValue[<?php echo $keyField_esc; ?>]" value="<?php echo $fieldValue; ?>">
        
        <?php } elseif ( $type === 'textarea' ) { ?>
        
            <textarea name="_fieldValue[<?php echo $keyField_esc; ?>]"><?php echo $fieldValue; ?></textarea>
        
        <?php } ?>
        
        <input type="hidden" name="_fieldName[<?php echo $keyField_esc; ?>]" value="<?php echo $fieldName; ?>"> 
        
    </li>
    
    <?php
}