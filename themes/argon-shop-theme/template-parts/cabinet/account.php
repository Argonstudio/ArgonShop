<?php
/**
 * Форма аккаунта пользователя в личном кабинете (шаблон части кабинета)
 *
 * @package ArgonShopTheme
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ИСПОЛЬЗУЕТСЯ:
 * Подключается через get_template_part( 'template-parts/cabinet/account' )
 * из шаблона myCabinetPage.php (страница личного кабинета).
 * 
 * ЧТО ВЫВОДИТ:
 * 1. Таблицу полей аккаунта (данные физлица)
 * 2. Чекбокс "Юридическое лицо"
 * 3. Таблицу полей юрлица (скрыта, пока не отмечен чекбокс)
 * 4. Кнопку "Сохранить"
 * 5. Чекбокс согласия на обработку данных
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
 * 
 * МЕТА-ПОЛЯ ПОЛЬЗОВАТЕЛЯ (get_user_meta):
 * 
 * accountData
 *   Структура:
 *     [
 *         'accountDetail'      => ['nameUser' => '...', 'telefonUser' => '...', ...],
 *         'accountLegalDetail' => ['companyNameUser' => '...', 'innUser' => '...', ...],
 *     ]
 *   Заполняется при сохранении формы через AJAX cabinetAccountSave.
 * 
 * AJAX-ОБРАБОТЧИК:
 * 
 * cabinetAccountSave_callback()
 *   Файл: includes/interface/cabinet/myCabinet.php
 *   Принимает данные формы через JS-функцию accountSave().
 *   Сохраняет в мета-поле accountData.
 *   Проверяет, что user_ID совпадает с текущим пользователем.
 * 
 * JS-ФУНКЦИЯ:
 * 
 * accountSave() (из assets/cabinet/account/account.js)
 *   Собирает данные формы, добавляет action=cabinetAccountSave и nonce,
 *   отправляет на admin-ajax.php.
 * 
 * ============================================================
 * ПЕРЕМЕННЫЕ ИЗ ШАБЛОНА КАБИНЕТА
 * ============================================================
 * 
 * $user_ID — ID текущего пользователя (get_current_user_id())
 */

if ( ! defined( 'ABSPATH' ) ) {
    
    exit;
}

// Получение данных пользователя
$userdata    = get_user_by( 'id', $user_ID );
$accountData = get_user_meta( $user_ID, 'accountData', true );

if ( ! is_array( $accountData ) ) {
    $accountData = array();
}

// Проверка, отмечен ли чекбокс юрлица
$legal_checked = ! empty( $accountData['accountLegalDetail'] );

?>
    
    <div class="account"></div>
    
        <table>
            
            <?php 
            // Функция плагина Argon Shop (includes/api/getSetting.php)
            // Выводит поля для типа "person" (физлицо) в виде таблицы.
            if ( function_exists( 'show_user_fields' ) ) {
                show_user_fields( $user_ID, 'person', 'tr', 'block-userField50', 'block-userField100', 'userFieldTitle', 'userFieldInput' );
            }
            ?>
                  
        </table>
        
        
        
        <div class="accountLegalBlock">
             
            <input type="checkbox" id="accountLegal" style="display:none" <?php echo $legal_checked ? 'checked' : ''; ?> >  
            <div class="accountCustomCheckbox"></div> 
            <span>Юридическое лицо1</span> 
            
        </div>
        
        
        <table id="accountLegalBlock">
            
            <?php 
            // Функция плагина Argon Shop (includes/api/getSetting.php)
            // Выводит поля для типа "legalPerson" (юрлицо) в виде таблицы.
            if ( function_exists( 'show_user_fields' ) ) {
                show_user_fields( $user_ID, 'legalPerson', 'tr', 'block-userField50', 'block-userField100', 'userFieldTitle', 'userFieldInput' );
            }
            ?>
            
        </table>
        
        <div class="block-accountSave">
            
            <input type="button" data-userid="<?php echo esc_attr( $user_ID ); ?>" id="accountSaveButton" value="Сохранить" disabled>
            <div class="accountResult"><span></span></div>
            
            <div class="checkboxCabinet">
                            
                <input type="checkbox" id="agree_checkbox_account" data-consent-target="#accountSaveButton" style="cursor: pointer;">
                            
                <label for="agree_checkbox_account" style="cursor: pointer;">
                
                Устанавливая флажок, я выражаю своё согласие на обработку <a href="/konfidentsialnost-personalnoj-informatsii/" target="_blank">данных для демонстрационного стенда</a> (включая имя, фамилию, адрес, телефон, email, ИНН, КПП, юридический адрес) и технических файлов <strong>cookie</strong> в целях демонстрации и тестирования интерфейса сайта. Я понимаю, что данный сайт и все формы являются только симуляцией интернет-магазина, и не ввожу свои настоящие персональные данные. С условиями обработки ознакомлен(а).
                
                                
                </label>
                            
            </div>
            
        </div>