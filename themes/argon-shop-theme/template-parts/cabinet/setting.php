<?php
/**
 * Форма настроек пользователя в кабинете (часть шаблона)
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * ГДЕ ИСПОЛЬЗУЕТСЯ:
 * Подключается через get_template_part( 'template-parts/cabinet/setting' )
 * из шаблона myCabinetPage.php (страница кабинета покупателя).
 * 
 * ЧТО ВЫВОДИТ:
 * 1. Поле Email с кнопкой «Изменить»
 * 2. Поля смены пароля (старый, новый, подтверждение)
 * 3. Кнопка «Сохранить»
 * 4. Чекбокс согласия на обработку данных
 * 5. Ссылка «Выйти»
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 * 
 * AJAX-ОБРАБОТЧИК:
 * 
 * as_cabinetEditEmailPassword_callback()
 *   File: includes/interface/cabinet/myCabinet.php
 *   Что делает:
 *     1. Проверяет nonce (argon_shop_nonce)
 *     2. Проверяет права — пользователь может менять только свой email/пароль
 *     3. Проверяет email через filter_var
 *     4. Проверяет, что email не занят другим пользователем
 *     5. Проверяет длину нового пароля (минимум 6 символов)
 *     6. Проверяет, что новый пароль и подтверждение совпадают
 *     7. Проверяет текущий пароль через wp_check_password()
 *     8. Обновляет email через wp_update_user()
 *     9. Обновляет пароль через wp_set_password()
 *     10. Автоматически входит под новым паролем через wp_signon()
 * 
 * JS-МОДУЛЬ:
 * 
 * assets/js/interface/modules/cabinet.js (plugin)
 *   Собирает данные формы, добавляет action=cabinetEditEmailPassword
 *   и nonce, и отправляет их на admin-ajax.php.
 * 
 *   Обрабатывает ответ:
 *     - response.errors.email          → показывает ошибку email
 *     - response.errors.pass           → показывает ошибку старого пароля
 *     - response.errors.newPass        → показывает ошибку нового пароля
 *     - response.errors.newPassConfirm → показывает ошибку подтверждения
 *     - response.edited.newEmail       → обновляет поле email
 *     - response.edited.newPass        → очищает поля пароля
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
$userdata = get_user_by( 'id', $user_ID );

$user_email = ( $userdata && isset( $userdata->user_email ) ) ? $userdata->user_email : '';

?>
    
    
        <div class="block-settingForm">
                    
            <div class="block-settingField50">
                
                <div class="settingFieldTitle">Email:</div>
                
                <div class="block-settingFieldEmail">
                    
                    <input type="email" class="settingFieldInput" id="editEmail" data-oldEmail="<?php echo esc_attr( $user_email ); ?>" value="<?php echo esc_attr( $user_email ); ?>" disabled/>
                    
                    <span id="editEmailActive"><?php esc_html_e( 'Change', 'argon-shop-theme' ); ?></span>
                    
                    <div class="editError editEmailError"> <span></span> </div>
                    
                </div>
                
            </div>
                        
            <div class="block-settingField50">
                
                <div class="settingFieldTitle"><?php esc_html_e( 'Old password:', 'argon-shop-theme' ); ?></div>
                <input type="password" class="settingFieldInput" id="editOldPass" />
                
            </div>
                        
            <div class="editError editPassError"> <span></span> </div>
                        
            <div class="block-settingField50">
                
                <div class="settingFieldTitle"><?php esc_html_e( 'New password', 'argon-shop-theme' ); ?></div>
                <input type="password" class="settingFieldInput" id="editNewPass" />
                
            </div>
                        
            <div class="editError editNewPassError"> <span></span> </div>
                        
            <div class="block-settingField50">
                
                <div class="settingFieldTitle"><?php esc_html_e( 'Repeat the new password', 'argon-shop-theme' ); ?></div>
                <input type="password" class="settingFieldInput" id="editNewPassConfirm" />
                
            </div>
                        
            <div class="editError editNewPassConfirmError"> <span></span> </div>
            
        </div>
        
        <div class="block-settingSave">
            
            <input type="button" data-userid="<?php echo esc_attr( $user_ID ); ?>" id="editEmailPasswordButton" value="<?php esc_attr_e( 'Save', 'argon-shop-theme' ); ?>" disabled>
            <div class="editResult"><span></span></div>
            
            <div class="checkboxCabinet">
                            
                <input type="checkbox" id="agree_checkbox_setting" data-consent-target="#editEmailPasswordButton" style="cursor: pointer;">
                            
                <label for="agree_checkbox_setting" style="cursor: pointer;">
                
                <?php esc_html_e( 'By checking this box, I consent to the processing of', 'argon-shop-theme' ); ?> <a href="/konfidentsialnost-personalnoj-informatsii/" target="_blank"><?php esc_html_e( 'data for the demo stand', 'argon-shop-theme' ); ?></a> <?php esc_html_e( 'including email, password and technical files', 'argon-shop-theme' ); ?> <strong>cookie</strong> <?php esc_html_e( 'for the purpose of demonstrating and testing the site interface. I understand that this site and all forms are only a simulation of an online store, and I do not enter my real personal data. I have read the processing terms.', 'argon-shop-theme' ); ?>
                                
                </label>
                            
            </div>
            
        </div>   
        
        <a href="<?php echo esc_url( wp_logout_url() ); ?>" class="exitSite" title="<?php esc_attr_e( 'Log out', 'argon-shop-theme' ); ?>"><?php esc_html_e( 'Log out', 'argon-shop-theme' ); ?></a>
        