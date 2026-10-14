<?php
/**
 * Order form for individuals (template part)
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * WHERE IT IS USED:
 * Included via get_template_part( 'template-parts/cart/person' )
 * from the shoppingCartPage.php template (cart page).
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
 *   The $required parameter is an array of required fields.
 * 
 * ============================================================
 * DYNAMIC FORM FIELDS
 * ============================================================
 * 
 * Some form fields are defined by the administrator in the store settings:
 * 
 * WordPress Admin → Settings → Store → User data
 * 
 * Line format for each field:
 *   ID[Name, Placeholder, class]
 * 
 * Where:
 * - ID          — the HTML name attribute
 * - Name        — field label
 * - Placeholder — hint inside the field
 * - class       — 50 or 100 (field width)
 * 
 * Example fields for individuals:
 *   nameUser[Name,John,50]
 *   lastnameUser[Last name,Smith,50]
 *   telefonUser[Phone,+1 (555) xxx-xxxx,50]
 *   addressUser[Address,New York/comma/ 5th Avenue,100]
 * 
 * The separator is a comma. For a comma inside the text use
 * the "/comma/" marker.
 * 
 * ============================================================
 * FIELD HANDLING IN THE PLUGIN
 * ============================================================
 * 
 * All fields from the settings are collected automatically in the AJAX handler
 * as_submitCart_shoppingCart_callback() via as_get_valueFieldsCart().
 * 
 * Fixed fields handled by the plugin separately:
 *   - emailUser     — customer email
 *   - messageUser   — comment
 *   - cartReg       — registration checkbox
 *   - loginUser     — login
 *   - shipping      — delivery method
 *   - payment       — payment method
 *   - fileCart[]    — attached files
 * 
 * ============================================================
 * VARIABLES FROM THE CART TEMPLATE
 * ============================================================
 * 
 * $user_ID — current user ID (get_current_user_id())
 */

// Prevent direct access to the file
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
    
    // The list of required fields comes from the plugin (the same one the server checks)
    $required = function_exists( 'as_get_required_fields' ) ? as_get_required_fields( 'person' ) : array( 'nameUser', 'telefonUser' );
    
    // Argon Shop plugin function (includes/api/getSetting.php)
    if ( function_exists( 'as_show_user_fields' ) ) {
        as_show_user_fields( $user_ID, 'person', 'div', 'cartFields50', 'cartFields100', 'cartFieldsTitle', 'cartFieldInput', $required );
    }
    
    ?>
    
    <div class="cartFields50 cartFieldsEmail-person">
        
        <div class="cartFieldsTitle">Email</div>
        <input type="text" class="cartFieldInput" name="emailUser" placeholder="mail@example.com" value="<?php echo esc_attr( $user_email ); ?>">
        
    </div>
    
    <div class="cartFields100">
        
        <div class="cartFieldsTitle"><?php esc_html_e( 'Comment:', 'argon-shop-theme' ); ?></div>
        <textarea name="messageUser" placeholder="<?php esc_attr_e( 'Your comment', 'argon-shop-theme' ); ?>"></textarea>
        
    </div>
    
    <label for="personFile" class="labelAddFile"><span id="foto"><?php esc_html_e( 'Attach files', 'argon-shop-theme' ); ?></span></label>
    
    <div class="block-fileCartMessage">
        
        <div class="fileCartMessage"></div>
        <div class="loadedFiles"></div>
        
    </div>
            
    <div class="infoFileCart"><?php esc_html_e( 'Allowed file formats: doc, docx, xls, xlsx, pdf, jpg, jpeg, png, bmp', 'argon-shop-theme' ); ?></div>
    <div class="infoFileCart"><?php esc_html_e( 'Attach up to 3 files, up to 5 MB each', 'argon-shop-theme' ); ?> </div>
    <div class="infoFileCart"><?php esc_html_e( 'Hold Ctrl to select several files (Windows)', 'argon-shop-theme' ); ?></div>
            
            
    <input type="file" multiple="" data-sizeFile="5" data-amountFiles="3" name="fileCart[]" id="personFile" accept="image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
    
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