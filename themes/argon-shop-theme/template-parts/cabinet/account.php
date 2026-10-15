<?php
/**
 * Форма данных пользователя в кабинете (часть шаблона account)
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * ГДЕ ИСПОЛЬЗУЕТСЯ:
 * Подключается через get_template_part( 'template-parts/cabinet/account' )
 * из шаблона myCabinetPage.php (страница кабинета покупателя).
 * 
 * ЧТО ВЫВОДИТ:
 * 1. Таблица полей кабинета (данные физлица)
 * 2. Чекбокс «Юридическое лицо»
 * 3. Таблица полей юрлица (скрыта, пока чекбокс не отмечен)
 * 4. Кнопка «Сохранить»
 * 5. Чекбокс согласия на обработку данных
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
 * 
 * МЕТА-ПОЛЯ ПОЛЬЗОВАТЕЛЯ (get_user_meta):
 * 
 * accountData
 *   Структура:
 *     [
 *         'accountDetail'      => ['nameUser' => '...', 'telefonUser' => '...', ...],
 *         'accountLegalDetail' => ['companyNameUser' => '...', 'innUser' => '...', ...],
 *     ]
 *   Заполняется при сохранении формы через AJAX-действие cabinetAccountSave.
 * 
 * AJAX-ОБРАБОТЧИК:
 * 
 * as_cabinetAccountSave_callback()
 *   File: includes/interface/cabinet/myCabinet.php
 *   Получает данные формы из JS-модуля кабинета.
 *   Сохраняет их в мета-поле accountData.
 *   Проверяет, что user_ID совпадает с текущим пользователем.
 * 
 * JS-МОДУЛЬ:
 * 
 * assets/js/interface/modules/cabinet.js (plugin)
 *   Собирает данные формы, добавляет action=cabinetAccountSave и nonce,
 *   и отправляет их на admin-ajax.php.
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

// Получаем данные пользователя
$userdata    = get_user_by( 'id', $user_ID );
$accountData = get_user_meta( $user_ID, 'accountData', true );

if ( ! is_array( $accountData ) ) {
    $accountData = array();
}

// Проверяем, отмечен ли чекбокс юрлица
$legal_checked = ! empty( $accountData['accountLegalDetail'] );

?>
    
    <div class="account"></div>
    
        <table>
            
            <?php 
            // Функция плагина Argon Shop (includes/api/getSetting.php)
            // Выводит поля типа «person» (физлицо) таблицей.
            if ( function_exists( 'as_show_user_fields' ) ) {
                as_show_user_fields( $user_ID, 'person', 'tr', 'block-userField50', 'block-userField100', 'userFieldTitle', 'userFieldInput' );
            }
            ?>
                  
        </table>
        
        
        
        <div class="accountLegalBlock">
             
            <input type="checkbox" id="accountLegal" style="display:none" <?php echo $legal_checked ? 'checked' : ''; ?> >  
            <div class="accountCustomCheckbox"></div> 
            <span><?php esc_html_e( 'Legal entity', 'argon-shop-theme' ); ?></span> 
            
        </div>
        
        
        <table id="accountLegalBlock">
            
            <?php 
            // Функция плагина Argon Shop (includes/api/getSetting.php)
            // Выводит поля типа «legalPerson» (юрлицо) таблицей.
            if ( function_exists( 'as_show_user_fields' ) ) {
                as_show_user_fields( $user_ID, 'legalPerson', 'tr', 'block-userField50', 'block-userField100', 'userFieldTitle', 'userFieldInput' );
            }
            ?>
            
        </table>
        
        <div class="block-accountSave">
            
            <input type="button" data-userid="<?php echo esc_attr( $user_ID ); ?>" id="accountSaveButton" value="<?php esc_attr_e( 'Save', 'argon-shop-theme' ); ?>" disabled>
            <div class="accountResult"><span></span></div>
            
            <div class="checkboxCabinet">
                            
                <input type="checkbox" id="agree_checkbox_account" data-consent-target="#accountSaveButton" style="cursor: pointer;">
                            
                <label for="agree_checkbox_account" style="cursor: pointer;">
                
                <?php esc_html_e( 'By checking this box, I consent to the processing of', 'argon-shop-theme' ); ?> <a href="/konfidentsialnost-personalnoj-informatsii/" target="_blank"><?php esc_html_e( 'data for the demo stand', 'argon-shop-theme' ); ?></a> <?php esc_html_e( '(including first name, last name, address, phone, email, TIN, KPP, legal address) and technical files', 'argon-shop-theme' ); ?> <strong>cookie</strong> <?php esc_html_e( 'for the purpose of demonstrating and testing the site interface. I understand that this site and all forms are only a simulation of an online store, and I do not enter my real personal data. I have read the processing terms.', 'argon-shop-theme' ); ?>
                
                                
                </label>
                            
            </div>
            
        </div>