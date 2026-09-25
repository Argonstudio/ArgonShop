<?php
/**
 * Форма настроек пользователя в личном кабинете (шаблон части)
 *
 * @package ArgonShopTheme
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ИСПОЛЬЗУЕТСЯ:
 * Подключается через get_template_part( 'template-parts/cabinet/setting' )
 * из шаблона myCabinetPage.php (страница личного кабинета).
 * 
 * ЧТО ВЫВОДИТ:
 * 1. Поле Email с кнопкой "Изменить"
 * 2. Поля смены пароля (старый, новый, подтверждение)
 * 3. Кнопку "Сохранить"
 * 4. Чекбокс согласия на обработку данных
 * 5. Ссылку "Выход"
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 * 
 * AJAX-ОБРАБОТЧИК:
 * 
 * cabinetEditEmailPassword_callback()
 *   Файл: includes/interface/cabinet/myCabinet.php
 *   Что делает:
 *     1. Проверяет nonce (argon_shop_nonce)
 *     2. Проверяет права — пользователь может менять только свой email/пароль
 *     3. Валидирует email через filter_var
 *     4. Проверяет, что email не занят другим пользователем
 *     5. Проверяет длину нового пароля (минимум 6 символов)
 *     6. Проверяет совпадение нового пароля и подтверждения
 *     7. Проверяет текущий пароль через wp_check_password()
 *     8. Обновляет email через wp_update_user()
 *     9. Обновляет пароль через wp_set_password()
 *     10. Автоматически логинит пользователя с новым паролем через wp_signon()
 * 
 * JS-ФУНКЦИЯ:
 * 
 * cabinetEditEmailPassword() (из assets/cabinet/setting/setting.js)
 *   Собирает данные формы, добавляет action=cabinetEditEmailPassword
 *   и nonce, отправляет на admin-ajax.php.
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

// Получение данных пользователя
$userdata = get_user_by( 'id', $user_ID );

$user_email = ( $userdata && isset( $userdata->user_email ) ) ? $userdata->user_email : '';

?>
    
    
        <div class="block-settingForm">
                    
            <div class="block-settingField50">
                
                <div class="settingFieldTitle">Email:</div>
                
                <div class="block-settingFieldEmail">
                    
                    <input type="email" class="settingFieldInput" id="editEmail" data-oldEmail="<?php echo esc_attr( $user_email ); ?>" value="<?php echo esc_attr( $user_email ); ?>" disabled/>
                    
                    <span id="editEmailActive">Изменить</span>
                    
                    <div class="editError editEmailError"> <span></span> </div>
                    
                </div>
                
            </div>
                        
            <div class="block-settingField50">
                
                <div class="settingFieldTitle">Старый пароль:</div>
                <input type="password" class="settingFieldInput" id="editOldPass" />
                
            </div>
                        
            <div class="editError editPassError"> <span></span> </div>
                        
            <div class="block-settingField50">
                
                <div class="settingFieldTitle">Новый пароль</div>
                <input type="password" class="settingFieldInput" id="editNewPass" />
                
            </div>
                        
            <div class="editError editNewPassError"> <span></span> </div>
                        
            <div class="block-settingField50">
                
                <div class="settingFieldTitle">Повторите новый пароль</div>
                <input type="password" class="settingFieldInput" id="editNewPassConfirm" />
                
            </div>
                        
            <div class="editError editNewPassConfirmError"> <span></span> </div>
            
        </div>
        
        <div class="block-settingSave">
            
            <input type="button" data-userid="<?php echo esc_attr( $user_ID ); ?>" id="editEmailPasswordButton" value="Сохранить" disabled>
            <div class="editResult"><span></span></div>
            
            <div class="checkboxCabinet">
                            
                <input type="checkbox" id="agree_checkbox_setting" data-consent-target="#editEmailPasswordButton" style="cursor: pointer;">
                            
                <label for="agree_checkbox_setting" style="cursor: pointer;">
                
                Устанавливая флажок, я выражаю своё согласие на обработку <a href="/konfidentsialnost-personalnoj-informatsii/" target="_blank">данных для демонстрационного стенда</a> включая email, пароль и технические файлы <strong>cookie</strong> в целях демонстрации и тестирования интерфейса сайта. Я понимаю, что данный сайт и все формы являются только симуляцией интернет-магазина, и не ввожу свои настоящие персональные данные. С условиями обработки ознакомлен(а).
                                
                </label>
                            
            </div>
            
        </div>   
        
        <a href="<?php echo esc_url( wp_logout_url() ); ?>" class="exitSite" title="Выход">Выход</a>
        