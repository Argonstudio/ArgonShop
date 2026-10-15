<?php
/**
 * Форма заказа для юрлиц (часть шаблона)
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * ГДЕ ИСПОЛЬЗУЕТСЯ:
 * Подключается через get_template_part( 'template-parts/cart/legalPerson' )
 * из шаблона shoppingCartPage.php (страница корзины).
 * 
 * ЧТО ВЫВОДИТ:
 * Форма оформления заказа для юрлиц.
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 * 
 * ФУНКЦИИ ПЛАГИНА:
 * 
 * as_show_user_fields( $user_ID, $type, $wrap, $class50, $class100, $classTitle, $classInput, $required )
 *   File: includes/api/getSetting.php
 *   Выводит поля формы из настроек магазина (settingShop).
 *   Для типа «person» — данные физлица.
 *   Для типа «legalPerson» — данные юрлица.
 *   Параметр $required — массив обязательных полей.
 * 
 * ============================================================
 * ДИНАМИЧЕСКИЕ ПОЛЯ ФОРМЫ
 * ============================================================
 * 
 * Некоторые поля формы задаёт администратор в настройках магазина:
 * 
 * Админка WordPress → Настройки → Магазин → Данные пользователя
 * 
 * Формат строки для каждого поля:
 * 
 *   ID[Название, Плейсхолдер, class]
 * 
 * Где:
 * - ID          — имя поля (HTML-атрибут name)
 * - Name        — подпись поля (что видит пользователь)
 * - Placeholder — подсказка внутри поля
 * - class       — 50 или 100 (ширина поля: половина или вся форма)
 * 
 * Поля для ЮРЛИЦ (текстареа «Юридические лица»):
 * 
 *   companyNameUser[Company name,Company,50]
 *   innUser[TIN,xxxxxxx,50]
 *   kppUser[KPP,xxxxxxx,50]
 *   legalAddressUser[Legal address,New York/comma/ 5th Avenue,100]
 * 
 * Fields for INDIVIDUALS ("Individuals" textarea):
 * 
 *   nameUser[Name,John,50]
 *   lastnameUser[Last name,Smith,50]
 *   telefonUser[Phone,+1 (555) xxx-xxxx,50]
 *   addressUser[Address,New York/comma/ 5th Avenue,100]
 * 
 * Fields for the QUICK order ("Quick order" textarea):
 * 
 *   telefonUser[Phone,+1 (555) xxx-xxxx,100]
 *   addressUser[Address,New York/comma/ 5th Avenue,100]
 * 
 * ВАЖНО: разделитель — запятая. Если в тексте нужна запятая, используйте
 * маркер «/comma/» — при сохранении он заменяется настоящей запятой (старый русский маркер тоже работает).
 * Пример: «New York/comma/ 5th Avenue» → «New York, 5th Avenue»
 * 
 * Формат разбирается функцией as_sanitize_settings() в settingPage.php.
 * 
 * ============================================================
 * ОБРАБОТКА ПОЛЕЙ В ПЛАГИНЕ
 * ============================================================
 * 
 * Поля, добавленные через настройки, обрабатываются автоматически в
 * AJAX-обработчике as_submitCart_shoppingCart_callback() через функцию
 * as_get_valueFieldsCart() (includes/api/getSetting.php).
 * 
 * Она читает все поля из settingShop[typeForm] и собирает их значения
 * из $_POST. Поля с фиксированными именами тоже обрабатываются:
 * 
 *   - emailUser     — обязательное поле email
 *   - messageUser   — комментарий к заказу
 *   - cartReg       — чекбокс «Зарегистрировать»
 *   - loginUser     — логин для регистрации
 * 
 * Поля, специфичные для этой формы (shipping, payment, fileCart[])
 * обрабатываются напрямую в as_submitCart_shoppingCart_callback().
 * 
 * ============================================================
 * ПЕРЕМЕННЫЕ ИЗ ШАБЛОНА КОРЗИНЫ
 * ============================================================
 * 
 * $user_ID
 *   Определяется в shoppingCartPage.php через get_current_user_id().
 *   Используется для получения мета-данных пользователя.
 * 
 * ============================================================
 * AJAX-ОТПРАВКА ФОРМЫ
 * ============================================================
 * 
 * JS-модуль корзины (assets/js/interface/modules/cart.js) собирает
 * данные формы, добавляет action=submitCart_shoppingCart и nonce,
 * и отправляет их на admin-ajax.php.
 * 
 * Обработчик: as_submitCart_shoppingCart_callback()
 * File: includes/interface/shoppingCart/shoppingCart.php
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Получаем данные пользователя
$userdata    = get_user_by( 'id', $user_ID );
$accountData = get_user_meta( $user_ID, 'accountData', true );

if ( ! is_array( $accountData ) ) {
    $accountData = array();
}

$user_email = ( $userdata && isset( $userdata->user_email ) ) ? $userdata->user_email : '';
?>

<form enctype="multipart/form-data" method="post" data-type="legalPerson" class="form-cart form-cartlegalPerson">
    
    <?php 
    
    // Обязательные поля формы представителя (физлица)
    // Список обязательных полей приходит из плагина (тот же, что проверяет сервер)
    $required = function_exists( 'as_get_required_fields' ) ? as_get_required_fields( 'legalPerson' ) : array( 'nameUser', 'telefonUser' );
    
    // Функция плагина Argon Shop (includes/api/getSetting.php)
    if ( function_exists( 'as_show_user_fields' ) ) {
        as_show_user_fields( $user_ID, 'person', 'div', 'cartFields50', 'cartFields100', 'cartFieldsTitle', 'cartFieldInput', $required );
    }
    
    ?>
    
    <div class="cartFields50 cartFieldsEmail-person">
        
        <div class="cartFieldsTitle">Email</div>
        <input type="text" class="cartFieldInput" name="emailUser" placeholder="mail@example.com" value="<?php echo esc_attr( $user_email ); ?>">
        
    </div>
    
    <?php 
    
    // Обязательные поля для юрлица
    // Если поля изменены в админке, лишние поля здесь не вызовут ошибку
    // Список обязательных полей приходит из плагина (тот же, что проверяет сервер)
    $required = function_exists( 'as_get_required_fields' ) ? as_get_required_fields( 'legalPerson' ) : array( 'companyNameUser', 'innUser', 'kppUser', 'legalAddressUser' );
    
    // Функция плагина Argon Shop (includes/api/getSetting.php)
    if ( function_exists( 'as_show_user_fields' ) ) {
        as_show_user_fields( $user_ID, 'legalPerson', 'div', 'cartFields50 fields50legalPerson', 'cartFields100', 'cartFieldsTitle', 'cartFieldInput', $required );
    }
    
    ?>
    
    <div class="cartFields100">
        
        <div class="cartFieldsTitle"><?php esc_html_e( 'Comment:', 'argon-shop-theme' ); ?></div>
        <textarea name="messageUser" placeholder="<?php esc_attr_e( 'Your comment', 'argon-shop-theme' ); ?>"></textarea>
        
    </div>
    
    <label for="legalPersonFile" class="labelAddFile"><span id="foto"><?php esc_html_e( 'Attach files', 'argon-shop-theme' ); ?></span></label>
    
    <div class="block-fileCartMessage">
        
        <div class="fileCartMessage"></div>
        <div class="loadedFiles"></div>
        
    </div>
            
    <div class="infoFileCart"><?php esc_html_e( 'Allowed file formats: doc, docx, xls, xlsx, pdf, jpg, jpeg, png, bmp', 'argon-shop-theme' ); ?></div>
    <div class="infoFileCart"><?php esc_html_e( 'Attach up to 3 files, up to 5 MB each', 'argon-shop-theme' ); ?> </div>
    <div class="infoFileCart"><?php esc_html_e( 'Hold Ctrl to select several files (Windows)', 'argon-shop-theme' ); ?></div>
            
            
    <input type="file" multiple="" data-sizefile="5" data-amountfiles="3" name="fileCart[]" id="legalPersonFile" accept="image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
    
    <div class="cartRegistration" <?php if ( is_user_logged_in() ) { echo 'style="display:none"'; } ?> >
        
        <div class="block-regQuestion">
            
            <input type="checkbox" name="cartReg" class="cartReg"> 
            <span><?php esc_html_e( 'Register an account?', 'argon-shop-theme' ); ?></span>
            
        </div>
        
        <div class="cartLogin">
        
            <div class="cartFieldsTitle"><?php esc_html_e( 'Username', 'argon-shop-theme' ); ?></div>
            <input type="text" class="cartFieldInput" name="loginUser">
            
        </div>
    
    </div>
    
    <div class="block-shippingPayment">
    
        <div class="block-shippingMethod">
                            
            <div class="shippingyMethodTitle"><?php esc_html_e( 'Shipping method:', 'argon-shop-theme' ); ?></div>
             
            <div class="block-shippingMethodTypes">  
            
                <div class="shippingMethodType"><input type="radio" name="shipping" value="selfExport"> <?php esc_html_e( 'Pickup', 'argon-shop-theme' ); ?></div>
                <div class="shippingMethodType"><input type="radio" name="shipping" value="shippingToAddress" checked> <?php esc_html_e( 'Delivery to address', 'argon-shop-theme' ); ?></div>
            
            </div>
                            
        </div>
    
        <div class="block-paymentMethod">
                            
            <div class="paymentMethodTitle"><?php esc_html_e( 'Payment method:', 'argon-shop-theme' ); ?></div>
            
            <div class="block-paymentMethodTypes">
                            
                <div class="paymentMethodType"><input type="radio" name="payment" value="paimentUponReceipt" checked> <?php esc_html_e( 'Payment on delivery', 'argon-shop-theme' ); ?></div>
            
            </div>  
            
        </div>
    
    </div>
				
    <input value="<?php esc_attr_e( 'PLACE ORDER', 'argon-shop-theme' ); ?>" type="submit" class="submitCart" disabled>
    <div class="submitError"><span></span></div>
                    
</form>