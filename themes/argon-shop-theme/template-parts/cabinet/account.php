<?php
/**
 * User account form in the customer account (account template part)
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * WHERE IT IS USED:
 * Included via get_template_part( 'template-parts/cabinet/account' )
 * from the myCabinetPage.php template (customer account page).
 * 
 * WHAT IT OUTPUTS:
 * 1. The table of account fields (individual's data)
 * 2. The "Legal entity" checkbox
 * 3. The table of legal entity fields (hidden until the checkbox is checked)
 * 4. The "Save" button
 * 5. The consent to data processing checkbox
 * 
 * ============================================================
 * INTERACTION WITH THE ARGON SHOP PLUGIN
 * ============================================================
 * 
 * PLUGIN FUNCTIONS:
 * 
 * as_show_user_fields( $user_ID, $type, $wrap, $class50, $class100, $classTitle, $classInput, $required )
 *   File: includes/api/getSetting.php
 *   Outputs the form fields from the store settings (settingShop).
 *   For the "person" type — individual's data.
 *   For the "legalPerson" type — legal entity data.
 * 
 * USER META FIELDS (get_user_meta):
 * 
 * accountData
 *   Structure:
 *     [
 *         'accountDetail'      => ['nameUser' => '...', 'telefonUser' => '...', ...],
 *         'accountLegalDetail' => ['companyNameUser' => '...', 'innUser' => '...', ...],
 *     ]
 *   Filled when the form is saved via the cabinetAccountSave AJAX action.
 * 
 * AJAX HANDLER:
 * 
 * as_cabinetAccountSave_callback()
 *   File: includes/interface/cabinet/myCabinet.php
 *   Receives the form data from the cabinet JS module.
 *   Saves it to the accountData meta field.
 *   Checks that user_ID matches the current user.
 * 
 * JS MODULE:
 * 
 * assets/js/interface/modules/cabinet.js (plugin)
 *   Collects the form data, adds action=cabinetAccountSave and the nonce,
 *   and sends it to admin-ajax.php.
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
$userdata    = get_user_by( 'id', $user_ID );
$accountData = get_user_meta( $user_ID, 'accountData', true );

if ( ! is_array( $accountData ) ) {
    $accountData = array();
}

// Check whether the legal entity checkbox is checked
$legal_checked = ! empty( $accountData['accountLegalDetail'] );

?>
    
    <div class="account"></div>
    
        <table>
            
            <?php 
            // Argon Shop plugin function (includes/api/getSetting.php)
            // Outputs the fields of the "person" type (individual) as a table.
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
            // Argon Shop plugin function (includes/api/getSetting.php)
            // Outputs the fields of the "legalPerson" type (legal entity) as a table.
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