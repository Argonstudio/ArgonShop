<?php
/**
 * Order form for legal entities (template part)
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * WHERE IT IS USED:
 * Included via get_template_part( 'template-parts/cart/legalPerson' )
 * from the shoppingCartPage.php template (cart page).
 * 
 * WHAT IT OUTPUTS:
 * The checkout form for legal entities.
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
 * 
 *   ID[Name, Placeholder, class]
 * 
 * Where:
 * - ID          — field name (the HTML name attribute)
 * - Name        — field label (what the user sees)
 * - Placeholder — hint inside the field
 * - class       — 50 or 100 (field width: half or full form)
 * 
 * Fields for LEGAL ENTITIES ("Legal entities" textarea):
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
 * IMPORTANT: the separator is a comma. If a comma is needed in the text, use
 * the "/comma/" marker — it is replaced with a real comma on save (the old Russian marker still works).
 * Example: "New York/comma/ 5th Avenue" → "New York, 5th Avenue"
 * 
 * The format is parsed by as_sanitize_settings() in settingPage.php.
 * 
 * ============================================================
 * FIELD HANDLING IN THE PLUGIN
 * ============================================================
 * 
 * Fields added via the settings are handled automatically in the
 * as_submitCart_shoppingCart_callback() AJAX handler via the function
 * as_get_valueFieldsCart() (includes/api/getSetting.php).
 * 
 * It reads all fields from settingShop[typeForm] and collects their values
 * from $_POST. Fields with fixed names are also handled:
 * 
 *   - emailUser     — required email field
 *   - messageUser   — order comment
 *   - cartReg       — "Register" checkbox
 *   - loginUser     — login for registration
 * 
 * Fields specific to this form (shipping, payment, fileCart[])
 * are handled directly in as_submitCart_shoppingCart_callback().
 * 
 * ============================================================
 * VARIABLES FROM THE CART TEMPLATE
 * ============================================================
 * 
 * $user_ID
 *   Defined in shoppingCartPage.php via get_current_user_id().
 *   Used to get the user meta data.
 * 
 * ============================================================
 * AJAX FORM SUBMISSION
 * ============================================================
 * 
 * The cart JS module (assets/js/interface/modules/cart.js) collects
 * the form data, adds action=submitCart_shoppingCart and the nonce,
 * and sends it to admin-ajax.php.
 * 
 * Handler: as_submitCart_shoppingCart_callback()
 * File: includes/interface/shoppingCart/shoppingCart.php
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get the user data
$userdata    = get_user_by( 'id', $user_ID );
$accountData = get_user_meta( $user_ID, 'accountData', true );

if ( ! is_array( $accountData ) ) {
    $accountData = array();
}

$user_email = ( $userdata && isset( $userdata->user_email ) ) ? $userdata->user_email : '';
?>

<form enctype="multipart/form-data" method="post" data-type="legalPerson" class="form-cart form-cartlegalPerson">
    
    <?php 
    
    // Required fields for the representative (individual) form
    // The list of required fields comes from the plugin (the same one the server checks)
    $required = function_exists( 'as_get_required_fields' ) ? as_get_required_fields( 'legalPerson' ) : array( 'nameUser', 'telefonUser' );
    
    // Argon Shop plugin function (includes/api/getSetting.php)
    if ( function_exists( 'as_show_user_fields' ) ) {
        as_show_user_fields( $user_ID, 'person', 'div', 'cartFields50', 'cartFields100', 'cartFieldsTitle', 'cartFieldInput', $required );
    }
    
    ?>
    
    <div class="cartFields50 cartFieldsEmail-person">
        
        <div class="cartFieldsTitle">Email</div>
        <input type="text" class="cartFieldInput" name="emailUser" placeholder="mail@example.com" value="<?php echo esc_attr( $user_email ); ?>">
        
    </div>
    
    <?php 
    
    // Required fields for a legal entity
    // If the fields are changed in the admin, extra fields here will not cause an error
    // The list of required fields comes from the plugin (the same one the server checks)
    $required = function_exists( 'as_get_required_fields' ) ? as_get_required_fields( 'legalPerson' ) : array( 'companyNameUser', 'innUser', 'kppUser', 'legalAddressUser' );
    
    // Argon Shop plugin function (includes/api/getSetting.php)
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