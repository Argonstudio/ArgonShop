<?php
/**
 * Store settings retrieval and helper functions
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * WHAT IT DOES:
 * 1. Checks whether wholesale discounts are enabled
 * 2. Gets the IDs and URLs of the cart and account pages
 * 3. Outputs user form fields
 * 4. Store currency: code, symbol and price formatting (since 1.1.0)
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FUNCTION: Check whether wholesale discounts are enabled
 * 
 * @return bool true — discounts enabled, false — disabled
 */
function as_check_saleSteps() {
    
    $setting = get_option( 'settingShop' );
    
    if ( isset( $setting['typeSale'] ) && $setting['typeSale'] === 'wholesalePriceSteps' ) {
        return true;
    }
    
    return false;
}

/**
 * FUNCTION: Get the cart page ID
 * 
 * @return int Page ID or 0
 */
function as_get_cartPageID() {
    
    $val = get_option( 'settingShop' );
    $val = ( $val && isset( $val['shoppingCartPage'] ) ) ? absint( $val['shoppingCartPage'] ) : 0;
    
    return $val;
}

/**
 * FUNCTION: Get the cart page URL
 * 
 * @return string|false Page URL or false
 */
function as_get_cartPageURL() {
    
    $cartID = as_get_cartPageID();
    
    $cartURL = $cartID ? get_permalink( $cartID ) : false;
    
    return $cartURL;
}

/**
 * FUNCTION: Get the account page ID
 * 
 * @return int Page ID or 0
 */
function as_get_cabinetPageID() {
    
    $val = get_option( 'settingShop' );
    $val = ( $val && isset( $val['cabinetPage'] ) ) ? absint( $val['cabinetPage'] ) : 0;
    
    return $val;
}

/**
 * FUNCTION: Get the account page URL
 * 
 * @return string|false Page URL or false
 */
function as_get_cabinetPageURL() {
    
    $cabinetID = as_get_cabinetPageID();
    
    $cabinetURL = $cabinetID ? get_permalink( $cabinetID ) : false;
    
    return $cabinetURL;
}

/**
 * FUNCTION: Output user form fields
 * 
 * @param int|null    $userID      — user ID
 * @param string|null $type        — form type: quick, person, legalPerson
 * @param string|null $wrap        — wrapper: tr or div
 * @param string|null $class50     — CSS class for a 50% field
 * @param string|null $class100    — CSS class for a 100% field
 * @param string|null $classTitle  — title CSS class
 * @param string|null $classInput  — input CSS class
 * @param array|null  $required    — array of required fields
 * @param bool|null   $admin       — admin mode (the user sees their own data, the administrator sees an empty form to fill in)
 */
function as_show_user_fields($userID = null, $type = null, $wrap = null, $class50 = null, $class100 = null, $classTitle = null, $classInput = null, $required = null, $admin = null){
    
    $settingShop = get_option('settingShop');
    
    $userFildsSetting = ( !empty($settingShop[$type]) )?$settingShop[$type]:0;
    
    $userID = absint($userID);
    $accountData = get_user_meta($userID, "accountData",true);
    
    if( !is_array($accountData) ){
        $accountData = array();
    }
    
    if( !empty($accountData["accountLegalDetail"]["Название компании"]) ){
        echo esc_html( wptexturize($accountData["accountLegalDetail"]["Название компании"]) );
    }    
    
    $fieldsSaveKey = '';
    
    if($type == "quick"){
        $fieldsSaveKey = "accountQuick";
    }else if($type == "person"){
        $fieldsSaveKey = "accountDetail";
    }else if($type == "legalPerson"){
        $fieldsSaveKey = "accountLegalDetail";
    }
    
    $inputClass = ( !empty($fieldsSaveKey) )?$fieldsSaveKey:'';
    
    if($classInput){
        $inputClass .= " ".esc_attr($classInput);
    }else{
        $inputClass = esc_attr($inputClass);
    }
    
    if($wrap == "tr"){
        $subWrap = "td";
    }else{
        $subWrap = "div";
    }
    
    if($userFildsSetting){
        
        foreach($userFildsSetting as $key => $value){
        
            $class = '';
            $colspan = "";
            
            $size = isset($value["size"]) ? (int)$value["size"] : 100;
            
            if( $size == 50 ){
                $class = $class50;
            }else if( $size == 100 ){
                $class = $class100;
                $colspan = "2";
            }
            
            $class_esc = $class ? esc_attr($class) : '';
            $wrap_esc = $wrap ? esc_attr($wrap) : 'div';
            
            echo "<".$wrap_esc;
            echo " class='".$class_esc."'";
            echo ">";
            
            $subWrap_esc = esc_attr($subWrap);
            $classTitle_esc = $classTitle ? esc_attr($classTitle) : '';
            
            ?>
            
                <<?php echo $subWrap_esc; ?> class="<?php echo $classTitle_esc; ?>" <?php if($wrap == "tr" && $colspan !== ""){echo "colspan='".esc_attr($colspan)."'"; } ?> > 
                
                    <?php echo esc_html($value["name"]); ?>
            
                <?php echo ($colspan !== "" && $subWrap == "td") ? "" : "</".$subWrap_esc.">"; ?>
            
                <?php
            
                if($subWrap == "td"){ 
                    
                    if($colspan === ""){
                        echo "<td>";
                    }else{
                        echo "<p>";
                    }
                    
                }
                
                $inputValue = '';
                
                if(!$admin && !empty($accountData[$fieldsSaveKey][$key])){
                    $inputValue = $accountData[$fieldsSaveKey][$key];
                }
                
                $requiredInput = false;
                
                if($required && is_array($required)){
                    
                    foreach($required as $requiredKey){
                        
                        if($requiredKey == $key){
                            $requiredInput = true;
                        }
                    }
                }
                
                $inputClass_esc = esc_attr($inputClass);
                $key_esc = esc_attr($key);
                $placeholder_esc = isset($value["placeholder"]) ? esc_attr($value["placeholder"]) : '';
                $inputValue_esc = esc_attr($inputValue);
                $required_attr = $requiredInput ? "required='required'" : '';
                
                ?>
                
                <input type="text" class="<?php echo $inputClass_esc; ?>" name="<?php echo $key_esc; ?>" placeholder="<?php echo $placeholder_esc; ?>" value="<?php echo $inputValue_esc; ?>" <?php echo $required_attr; ?>>
            
                <?php
                
                if($subWrap == "td"){ 
                    
                    if($colspan === ""){
                        echo "</td>";
                    }else{
                        echo "</p></td>";
                    }
                }
                
                ?>
                
                <?php
            
            echo "</".$wrap_esc.">";
        }
    }
    
}
// ============================================
// STORE CURRENCY (since 1.1.0)
// ============================================

/**
 * FUNCTION: Supported store currencies
 *
 * Code => label for the "Currency" field in Settings → Store.
 *
 * @since 1.1.0
 * @return array<string, string>
 */
function as_get_currencies() {

    $currencies = array(
        'USD' => __( 'US dollar ($)', 'argon-shop' ),
        'EUR' => __( 'Euro (€)', 'argon-shop' ),
        'RUB' => __( 'Russian ruble (₽)', 'argon-shop' ),
    );

    /**
     * Filters the list of currencies offered in the store settings.
     * A new code also needs a symbol (argon_shop_currency_symbol).
     *
     * @since 1.1.0
     * @param array<string, string> $currencies Code => label.
     */
    return apply_filters( 'argon_shop_currencies', $currencies );
}

/**
 * FUNCTION: Current store currency code
 *
 * Read from the "currency" key of the settingShop option.
 * If the setting is missing or invalid, USD is used.
 *
 * @since 1.1.0
 * @return string Currency code: USD, EUR or RUB
 */
function as_get_currency() {

    $setting  = get_option( 'settingShop' );
    $currency = ( is_array( $setting ) && isset( $setting['currency'] ) ) ? strtoupper( (string) $setting['currency'] ) : '';

    if ( ! array_key_exists( $currency, as_get_currencies() ) ) {
        $currency = 'USD';
    }

    /**
     * Filters the store currency code.
     *
     * @since 1.1.0
     * @param string $currency Currency code.
     */
    return (string) apply_filters( 'argon_shop_currency', $currency );
}

/**
 * FUNCTION: Currency symbol
 *
 * The ruble symbol goes through the translation (msgid "₽"):
 * the Russian translation prints the Cyrillic letter "Er", as in previous versions.
 *
 * @since 1.1.0
 * @param string $currency Currency code. Empty — the store currency.
 * @return string Symbol (not escaped)
 */
function as_get_currency_symbol( $currency = '' ) {

    $currency = $currency ? strtoupper( $currency ) : as_get_currency();

    switch ( $currency ) {
        case 'RUB':
            $symbol = __( '₽', 'argon-shop' );
            break;
        case 'EUR':
            $symbol = '€';
            break;
        case 'USD':
            $symbol = '$';
            break;
        default:
            $symbol = $currency;
    }

    /**
     * Filters the currency symbol.
     *
     * @since 1.1.0
     * @param string $symbol   Currency symbol.
     * @param string $currency Currency code.
     */
    return (string) apply_filters( 'argon_shop_currency_symbol', $symbol, $currency );
}

/**
 * FUNCTION: Symbol position relative to the amount
 *
 * USD: before the amount ($10). EUR and RUB: after the amount (10 €, 10 ₽).
 *
 * @since 1.1.0
 * @param string $currency Currency code. Empty — the store currency.
 * @return string 'before' or 'after'
 */
function as_get_currency_position( $currency = '' ) {

    $currency = $currency ? strtoupper( $currency ) : as_get_currency();
    $position = ( $currency === 'USD' ) ? 'before' : 'after';

    /**
     * Filters the currency symbol position.
     *
     * @since 1.1.0
     * @param string $position 'before' or 'after'.
     * @param string $currency Currency code.
     */
    $position = apply_filters( 'argon_shop_currency_position', $position, $currency );

    return ( $position === 'before' ) ? 'before' : 'after';
}

/**
 * FUNCTION: Amount with the currency symbol
 *
 * $amount_html is inserted as is, so the amount can stay inside
 * a <span> that the frontend JS updates (for example "<span>120</span>").
 * Escape plain values before passing them.
 *
 * Examples (unit "pc"):
 *   USD → $<span>120</span>/pc
 *   EUR → <span>120</span> €/pc
 *   RUB → <span>120</span> ₽/pc (or the Russian translation of the symbol and unit)
 *
 * @since 1.1.0
 * @param string $amount_html Escaped amount or HTML with the amount.
 * @param string $unit        Unit of measure (not escaped), optional.
 * @return string HTML
 */
function as_price_html( $amount_html, $unit = '' ) {

    $symbol = esc_html( as_get_currency_symbol() );
    $unit   = ( $unit !== '' ) ? '/' . esc_html( $unit ) : '';

    if ( as_get_currency_position() === 'before' ) {
        return $symbol . $amount_html . $unit;
    }

    return $amount_html . ' ' . $symbol . $unit;
}

/**
 * FUNCTION: Currency label "per piece" for admin fields
 *
 * @since 1.1.0
 * @return string For example "$/pc", "€/pc" or "₽/pc" (not escaped)
 */
function as_get_currency_per_piece() {
    /* translators: %s: currency symbol. */
    return sprintf( __( '%s/pc', 'argon-shop' ), as_get_currency_symbol() );
}
