<?php
/**
 * Quick checkout form (template part)
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * WHERE IT IS USED:
 * Included via get_template_part( 'template-parts/cart/quickOrder' )
 * from the shoppingCartPage.php template (cart page).
 * 
 * WHAT IT OUTPUTS:
 * A short checkout form — the minimum of fields for a quick
 * order. Used as the default form for unauthorized
 * users.
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
 *   For the "quick" type — the minimal set of quick order fields.
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
 * Example fields for a quick order:
 *   telefonUser[Phone,+1 (555) xxx-xxxx,100]
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
 * as_submitCart_shoppingCart_callback() via as_get_valueFieldsCart()
 * with the "quick" form type.
 * 
 * Fixed fields handled by the plugin separately:
 *   - messageUser   — comment *
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
?>

<form enctype="multipart/form-data" method="post" data-type="quick" class="form-cart form-cartquick">

    <?php 
    
    // The list of required fields comes from the plugin (the same one the server checks)
    $required = function_exists( 'as_get_required_fields' ) ? as_get_required_fields( 'quick' ) : array( 'telefonUser', 'addressUser' );
    
    // Argon Shop plugin function (includes/api/getSetting.php)
    if ( function_exists( 'as_show_user_fields' ) ) {
        as_show_user_fields( $user_ID, 'quick', 'div', 'cartFields50', 'cartFields100', 'cartFieldsTitle', 'cartFieldInput', $required );
    }
    
    ?>
    
    <div class="cartFields100">
        
        <div class="cartFieldsTitle"><?php esc_html_e( 'Comment:', 'argon-shop-theme' ); ?></div>
        <textarea name="messageUser" placeholder="<?php esc_attr_e( 'Your comment', 'argon-shop-theme' ); ?>"></textarea>
        
    </div>
    
				
    <input value="<?php esc_attr_e( 'PLACE ORDER', 'argon-shop-theme' ); ?>" type="submit" class="submitCart" disabled>
    <div class="submitError"><span></span></div>
                    
</form>