<?php
/**
 * User settings form in the customer account (template part)
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * WHERE IT IS USED:
 * Included via get_template_part( 'template-parts/cabinet/setting' )
 * from the myCabinetPage.php template (customer account page).
 * 
 * WHAT IT OUTPUTS:
 * 1. The Email field with the "Change" button
 * 2. Password change fields (old, new, confirmation)
 * 3. The "Save" button
 * 4. The consent to data processing checkbox
 * 5. The "Log out" link
 * 
 * ============================================================
 * INTERACTION WITH THE ARGON SHOP PLUGIN
 * ============================================================
 * 
 * AJAX HANDLER:
 * 
 * as_cabinetEditEmailPassword_callback()
 *   File: includes/interface/cabinet/myCabinet.php
 *   What it does:
 *     1. Checks the nonce (argon_shop_nonce)
 *     2. Checks permissions — a user can change only their own email/password
 *     3. Validates the email via filter_var
 *     4. Checks that the email is not taken by another user
 *     5. Checks the new password length (at least 6 characters)
 *     6. Checks that the new password and the confirmation match
 *     7. Checks the current password via wp_check_password()
 *     8. Updates the email via wp_update_user()
 *     9. Updates the password via wp_set_password()
 *     10. Automatically logs the user in with the new password via wp_signon()
 * 
 * JS MODULE:
 * 
 * assets/js/interface/modules/cabinet.js (plugin)
 *   Collects the form data, adds action=cabinetEditEmailPassword
 *   and the nonce, and sends it to admin-ajax.php.
 * 
 *   Handles the response:
 *     - response.errors.email          → shows the email error
 *     - response.errors.pass           → shows the old password error
 *     - response.errors.newPass        → shows the new password error
 *     - response.errors.newPassConfirm → shows the confirmation error
 *     - response.edited.newEmail       → updates the email field
 *     - response.edited.newPass        → clears the password fields
 * 
 * ============================================================
 * VARIABLES FROM THE ACCOUNT TEMPLATE
 * ============================================================
 * 
 * $user_ID — current user ID (get_current_user_id())
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get the user data
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
        