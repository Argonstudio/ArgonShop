<?php
/**
 * Форма заказа для физических лиц (шаблон части)
 *
 * @package ArgonShopTheme
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ИСПОЛЬЗУЕТСЯ:
 * Подключается через get_template_part( 'template-parts/cart/person' )
 * из шаблона shoppingCartPage.php (страница корзины).
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 * 
 * ФУНКЦИИ ПЛАГИНА:
 * 
 * show_user_fields( $user_ID, $type, $wrap, $class50, $class100, $classTitle, $classInput, $required )
 *   Файл: includes/api/getSetting.php
 *   Выводит поля формы из настроек магазина (settingShop).
 *   Для типа "person" — данные физлица.
 *   Параметр $required — массив обязательных полей.
 * 
 * ============================================================
 * ДИНАМИЧЕСКИЕ ПОЛЯ ФОРМЫ
 * ============================================================
 * 
 * Часть полей формы задаётся администратором в настройках магазина:
 * 
 * WordPress Админка → Настройки → Магазин → Данные пользователя
 * 
 * Формат строки для каждого поля:
 *   ID[Имя, Placeholder, class]
 * 
 * Где:
 * - ID          — атрибут name в HTML
 * - Имя         — заголовок поля
 * - Placeholder — подсказка внутри поля
 * - class       — 50 или 100 (ширина поля)
 * 
 * Пример полей для физических лиц:
 *   nameUser[Имя,Иван,50]
 *   lastnameUser[Фамилия,Александров,50]
 *   telefonUser[Телефон,+7 (922) xxx-xx-xx,50]
 *   addressUser[Адрес,Московская область/запятая/ ДНП Янтарь,100]
 * 
 * Разделитель — запятая. Для запятой внутри текста используется
 * метка "/запятая/".
 * 
 * ============================================================
 * ОБРАБОТКА ПОЛЕЙ В ПЛАГИНЕ
 * ============================================================
 * 
 * Все поля из настроек автоматически собираются в AJAX-обработчике
 * submitCart_shoppingCart_callback() через get_valueFieldsCart().
 * 
 * Фиксированные поля, обрабатываемые плагином отдельно:
 *   - emailUser     — email покупателя
 *   - messageUser   — комментарий
 *   - cartReg       — галочка регистрации
 *   - loginUser     — логин
 *   - shipping      — способ доставки
 *   - payment       — способ оплаты
 *   - fileCart[]    — прикреплённые файлы
 * 
 * ============================================================
 * ПЕРЕМЕННЫЕ ИЗ ШАБЛОНА КОРЗИНЫ
 * ============================================================
 * 
 * $user_ID — ID текущего пользователя (get_current_user_id())
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$userdata = get_user_by( 'id', $user_ID );

$accountData = get_user_meta( $user_ID, 'accountData', true );

if ( ! is_array( $accountData ) ) {
    $accountData = array();
}

$user_email = ( $userdata && isset( $userdata->user_email ) ) ? $userdata->user_email : '';
?>

<form enctype="multipart/form-data" method="post" data-type="person" class="form-cart form-cartperson">
    
    <?php 
    
    $required = array( 'nameUser', 'telefonUser' );
    
    // Функция плагина Argon Shop (includes/api/getSetting.php)
    if ( function_exists( 'show_user_fields' ) ) {
        show_user_fields( $user_ID, 'person', 'div', 'cartFields50', 'cartFields100', 'cartFieldsTitle', 'cartFieldInput', $required );
    }
    
    ?>
    
    <div class="cartFields50 cartFieldsEmail-person">
        
        <div class="cartFieldsTitle">Email</div>
        <input type="text" class="cartFieldInput" name="emailUser" placeholder="mail@example.com" value="<?php echo esc_attr( $user_email ); ?>">
        
    </div>
    
    <div class="cartFields100">
        
        <div class="cartFieldsTitle">Комментарий:</div>
        <textarea name="messageUser" placeholder="Ваш комментарий"></textarea>
        
    </div>
    
    <label for="personFile" class="labelAddFile"><span id="foto">Прикрепить файлы</span></label>
    
    <div class="block-fileCartMessage">
        
        <div class="fileCartMessage"></div>
        <div class="loadedFiles"></div>
        
    </div>
            
    <div class="infoFileCart">Допустимые форматы файлов: doc, docx, xls, xlsx, pdf, jpg, jpeg, png, bmp</div>
    <div class="infoFileCart">Прикрепите до 3-х файлов, до 5 mb один файл </div>
    <div class="infoFileCart">Зажмите ctrl для выбора нескольких файлов(windows)</div>
            
            
    <input type="file" multiple="" data-sizeFile="5" data-amountFiles="3" name="fileCart[]" id="personFile" accept="image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
    
   <div class="cartRegistration" <?php if ( is_user_logged_in() ) { echo 'style="display:none"'; } ?> >
        
        <div class="block-regQuestion">
            
            <input type="checkbox" name="cartReg" class="cartReg"> 
            <span>Зарегистрировать вас?</span>
            
        </div>
        
        <div class="cartLogin">
        
            <div class="cartFieldsTitle">Логин</div>
            <input type="text" class="cartFieldInput" name="loginUser">
            
        </div>
        
    
    </div>
    
   <div class="block-shippingPayment">
    
        <div class="block-shippingMethod">
                            
            <div class="shippingyMethodTitle">Способ доставки:</div>
             
            <div class="block-shippingMethodTypes">  
            
                <div class="shippingMethodType"><input type="radio" name="shipping" value="selfExport"> Самовывоз</div>
                <div class="shippingMethodType"><input type="radio" name="shipping" value="shippingToAddress" checked> Доставка по адресу</div>
            
            </div>
                            
        </div>
    
        <div class="block-paymentMethod">
                            
            <div class="paymentMethodTitle">Способ оплаты:</div>
            
            <div class="block-paymentMethodTypes">
                            
                <div class="paymentMethodType"><input type="radio" name="payment" value="paimentUponReceipt" checked> Оплата при получении</div>
            
            </div>  
            
        </div>
    
    </div>
	
    <input value="ОТПРАВИТЬ ЗАКАЗ" type="submit" class="submitCart" disabled>
    <div class="submitError"><span></span></div>
                    
</form>