<?php
/**
 * Форма заказа для юридических лиц (шаблон части)
 *
 * @package ArgonShopTheme
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ИСПОЛЬЗУЕТСЯ:
 * Подключается через get_template_part( 'template-parts/cart/legalPerson' )
 * из шаблона shoppingCartPage.php (страница корзины).
 * 
 * ЧТО ВЫВОДИТ:
 * Форму оформления заказа для юридических лиц.
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
 *   Для типа "legalPerson" — данные юрлица.
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
 * 
 *   ID[Имя, Placeholder, class]
 * 
 * Где:
 * - ID          — название поля (атрибут name в HTML)
 * - Имя         — заголовок поля (то, что видит пользователь)
 * - Placeholder — подсказка внутри поля
 * - class       — 50 или 100 (ширина поля: половина или вся форма)
 * 
 * Поля для ЮРИДИЧЕСКИХ лиц (textarea "Юридические лица"):
 * 
 *   companyNameUser[Название компании,Компания,50]
 *   innUser[ИНН,ххххххх,50]
 *   kppUser[КПП,ххххххх,50]
 *   legalAddressUser[Юридический адрес,Московская область/запятая/ ДНП Янтарь,100]
 * 
 * Поля для ФИЗИЧЕСКИХ лиц (textarea "Физические лица"):
 * 
 *   nameUser[Имя,Иван,50] 
 *   lastnameUser[Фамилия,Александров,50]
 *   telefonUser[Телефон,+7 (922) xxx-xx-xx,50]
 *   addressUser[Адрес,Московская область/запятая/ ДНП Янтарь,100]
 * 
 * Поля для БЫСТРОГО заказа (textarea "Быстрый заказ"):
 * 
 *   telefonUser[Телефон,+7 (922) xxx-xx-xx,100]
 *   addressUser[Адрес,Московская область/запятая/ ДНП Янтарь,100]
 * 
 * ВАЖНО: разделитель — запятая. Если в тексте нужна запятая, используется
 * метка "/запятая/" — при сохранении она заменяется на реальную запятую.
 * Пример: "Московская область/запятая/ ДНП Янтарь" → "Московская область, ДНП Янтарь"
 * 
 * Формат разбирается в функции sanitize_callback() из settingPage.php.
 * 
 * ============================================================
 * ОБРАБОТКА ПОЛЕЙ В ПЛАГИНЕ
 * ============================================================
 * 
 * Поля, добавленные через настройки, автоматически обрабатываются в 
 * AJAX-обработчике submitCart_shoppingCart_callback() через функцию
 * get_valueFieldsCart() (includes/api/getSetting.php).
 * 
 * Она читает все поля из settingShop[typeForm] и собирает их значения
 * из $_POST. Дополнительно обрабатываются поля с фиксированными именами:
 * 
 *   - emailUser     — обязательное поле email
 *   - messageUser   — комментарий к заказу
 *   - cartReg       — галочка "Зарегистрировать"
 *   - loginUser     — логин для регистрации
 * 
 * Поля, специфичные для этой формы (shipping, payment, fileCart[]),
 * обрабатываются напрямую в submitCart_shoppingCart_callback().
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
 * JS-функция submitCart() (из assets/interface/js/cart.js) собирает
 * данные формы, добавляет action=submitCart_shoppingCart и nonce,
 * отправляет на admin-ajax.php.
 * 
 * Обработчик: submitCart_shoppingCart_callback()
 * Файл: includes/interface/shoppingCart/shoppingCart.php
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Получение данных пользователя
$userdata    = get_user_by( 'id', $user_ID );
$accountData = get_user_meta( $user_ID, 'accountData', true );

if ( ! is_array( $accountData ) ) {
    $accountData = array();
}

$user_email = ( $userdata && isset( $userdata->user_email ) ) ? $userdata->user_email : '';
?>

<form enctype="multipart/form-data" method="post" data-type="legalPerson" class="form-cart form-cartlegalPerson">
    
    <?php 
    
    // Обязательные поля для формы физлица-представителя
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
    
    <?php 
    
    // Обязательные поля для юрлица
    // Если поля в админке поменяются, то наличие лишних полей тут ошибку не вызовет
    $required = array( 'companyNameUser', 'innUser', 'kppUser', 'legalAddressUser' );
    
    // Функция плагина Argon Shop (includes/api/getSetting.php)
    if ( function_exists( 'show_user_fields' ) ) {
        show_user_fields( $user_ID, 'legalPerson', 'div', 'cartFields50 fields50legalPerson', 'cartFields100', 'cartFieldsTitle', 'cartFieldInput', $required );
    }
    
    ?>
    
    <div class="cartFields100">
        
        <div class="cartFieldsTitle">Комментарий:</div>
        <textarea name="messageUser" placeholder="Ваш комментарий"></textarea>
        
    </div>
    
    <label for="legalPersonFile" class="labelAddFile"><span id="foto">Прикрепить файлы</span></label>
    
    <div class="block-fileCartMessage">
        
        <div class="fileCartMessage"></div>
        <div class="loadedFiles"></div>
        
    </div>
            
    <div class="infoFileCart">Допустимые форматы файлов: doc, docx, xls, xlsx, pdf, jpg, jpeg, png, bmp</div>
    <div class="infoFileCart">Прикрепите до 3-х файлов, до 5 mb один файл </div>
    <div class="infoFileCart">Зажмите ctrl для выбора нескольких файлов(windows)</div>
            
            
    <input type="file" multiple="" data-sizefile="5" data-amountfiles="3" name="fileCart[]" id="legalPersonFile" accept="image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
    
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