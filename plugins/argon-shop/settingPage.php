<?php
/**
 * Store settings page
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * WHERE TO TEST:
 * WordPress Admin → Settings → Store
 * URL: /wp-admin/options-general.php?page=settings-shop
 * 
 * WHAT IT DOES:
 * 1. Registers the settings page in the admin
 * 2. Outputs fields: cart ID, account ID, discount type, currency, order forms
 * 3. Validates and saves the settings
 * 
 * PHP 8.5:
 * - extract() removed
 * - isset() and type checks
 * - protection against undefined index
 * - output escaping
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ============================================
// SETTINGS PAGE REGISTRATION
// ============================================

add_action( 'admin_menu', 'as_setting_register_admin_page' );

function as_setting_register_admin_page() {
    
    add_options_page(
        __( 'Store settings', 'argon-shop' ),           // Page title
        __( 'Store', 'argon-shop' ),                      // Menu item title
        'manage_options',               // Capability
        'settings-shop',                // Page slug
        'as_settings_shop_rendering'       // Render function
    );
}

/**
 * FUNCTION: Render the settings page
 * 
 * WHERE: WordPress Admin → Settings → Store
 */
function as_settings_shop_rendering() {
    ?>
    <div class="wrap">
        <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

        <form action="options.php" class="settingShopForm" method="POST">
            <?php
            // Hidden security fields
            settings_fields( 'option_group' );
            
            // Settings sections
            do_settings_sections( 'settingShopPage' );
            
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// ============================================
// SETTINGS REGISTRATION
// ============================================

add_action( 'admin_init', 'as_plugin_settings' );

function as_plugin_settings() {
    
    // Register the option group
    register_setting( 'option_group', 'settingShop', 'as_sanitize_settings' );
    
    // ============================================
    // SECTION: GENERAL SETTINGS
    // ============================================
    
    add_settings_section(
        'mainSettingShop',
        __( 'General settings', 'argon-shop' ),
        '',
        'settingShopPage'
    );
    
    add_settings_field(
        'shoppingCartPage',
        __( 'Cart page ID', 'argon-shop' ),
        'as_shoppingCartPage_callback',
        'settingShopPage',
        'mainSettingShop'
    );
    
    add_settings_field(
        'cabinetPage',
        __( 'Account page ID', 'argon-shop' ),
        'as_cabinetPage_callback',
        'settingShopPage',
        'mainSettingShop'
    );
    
    add_settings_field(
        'currency',
        __( 'Currency', 'argon-shop' ),
        'as_currency_callback',
        'settingShopPage',
        'mainSettingShop'
    );
    
    // ============================================
    // SECTION: DISCOUNTS
    // ============================================
    
    add_settings_section(
        'discountsSettingShop',
        __( 'Discounts', 'argon-shop' ),
        '',
        'settingShopPage'
    );
    
    $saleParemeters = array(
        'typeSale' => array(
            'noSale'              => __( 'No discounts', 'argon-shop' ),
            'wholesalePriceSteps' => __( 'Wholesale price steps', 'argon-shop' ),
        ),
    );
    
    add_settings_field(
        'typeSale',
        __( 'Discount system type', 'argon-shop' ),
        'as_typeSale_callback',
        'settingShopPage',
        'discountsSettingShop',
        $saleParemeters
    );
    
    // ============================================
    // SECTION: USER DATA
    // ============================================
    
    add_settings_section(
        'userDataShop',
        __( 'User data', 'argon-shop' ),
        '',
        'settingShopPage'
    );
    
    add_settings_field(
        'quickLine',
        __( 'Quick order', 'argon-shop' ),
        'as_quick_callback',
        'settingShopPage',
        'userDataShop'
    );
    
    add_settings_field(
        'personLine',
        __( 'Individuals', 'argon-shop' ),
        'as_person_callback',
        'settingShopPage',
        'userDataShop'
    );
    
    add_settings_field(
        'legalPersonLine',
        __( 'Legal entities', 'argon-shop' ),
        'as_legalPerson_callback',
        'settingShopPage',
        'userDataShop'
    );
}

// ============================================
// FIELD CALLBACKS
// ============================================

/**
 * CALLBACK: Discount type selector (radio)
 * 
 * PHP 8.5: extract() removed, $arguments['typeSale'] is accessed explicitly
 * 
 * @param array $arguments — array of field parameters
 */
function as_typeSale_callback( $arguments ) {
    
    // PHP 8.5: make sure an array with the key was passed
    if ( ! is_array( $arguments ) || ! isset( $arguments['typeSale'] ) ) {
        return;
    }
    
    $typeSale = $arguments['typeSale'];
    
    if ( ! is_array( $typeSale ) ) {
        return;
    }
    
    $setting = get_option( 'settingShop' );
    
    if ( ! is_array( $setting ) ) {
        $setting = array();
    }
    
    $option_name = 'settingShop';
    $id = 'typeSale';
    
    $current_value = isset( $setting[ $id ] ) ? $setting[ $id ] : '';
    
    echo '<fieldset>';
    
    foreach ( $typeSale as $key => $value ) {
        
        $key_esc = esc_attr( $key );
        $value_esc = esc_html( $value );
        $option_name_esc = esc_attr( $option_name );
        $id_esc = esc_attr( $id );
        
        // PHP 8.5: strict comparisons
        $is_checked = (
            ( empty( $current_value ) && $key === 'noSale' )
            || ( $current_value === $key )
        );
        
        $checked = $is_checked ? "checked='checked'" : '';
        
        echo "<label><input type='radio' name='{$option_name_esc}[{$id_esc}]' value='{$key_esc}' {$checked} />{$value_esc}</label><br />";
    }
    
    echo '</fieldset>';
}

/**
 * CALLBACK: Store currency (select)
 *
 * USD is selected when the setting has not been saved yet.
 *
 * @since 1.1.0
 */
function as_currency_callback() {
    
    $current = as_get_currency();
    
    echo '<select name="settingShop[currency]" id="settingShop-currency">';
    
    foreach ( as_get_currencies() as $code => $label ) {
        printf(
            '<option value="%1$s"%2$s>%3$s</option>',
            esc_attr( $code ),
            selected( $current, $code, false ),
            esc_html( $label )
        );
    }
    
    echo '</select>';
    echo '<p class="description">' . esc_html__( 'Used for all prices in the store, the cart, orders and emails.', 'argon-shop' ) . '</p>';
}

/**
 * CALLBACK: Cart page ID
 */
function as_shoppingCartPage_callback() {
    
    $setting = get_option( 'settingShop' );
    
    $val = ( is_array( $setting ) && isset( $setting['shoppingCartPage'] ) ) 
        ? $setting['shoppingCartPage'] 
        : '';
    
    ?>
    <input type="text" name="settingShop[shoppingCartPage]" value="<?php echo esc_attr( $val ); ?>" />
    <?php
}

/**
 * CALLBACK: Account page ID
 */
function as_cabinetPage_callback() {
    
    $setting = get_option( 'settingShop' );
    
    $val = ( is_array( $setting ) && isset( $setting['cabinetPage'] ) ) 
        ? $setting['cabinetPage'] 
        : '';
    
    ?>
    <input type="text" name="settingShop[cabinetPage]" value="<?php echo esc_attr( $val ); ?>" />
    <?php
}

/**
 * CALLBACK: Quick order
 */
function as_quick_callback() {
    
    $setting = get_option( 'settingShop' );
    
    $val = ( is_array( $setting ) && isset( $setting['quickLine'] ) ) 
        ? $setting['quickLine'] 
        : '';
    
    ?>
    <textarea class="large-text code" name="settingShop[quickLine]"><?php echo esc_textarea( $val ); ?></textarea>
    <?php
}

/**
 * CALLBACK: Individuals
 */
function as_person_callback() {
    
    $setting = get_option( 'settingShop' );
    
    $val = ( is_array( $setting ) && isset( $setting['personLine'] ) ) 
        ? $setting['personLine'] 
        : '';
    
    ?>
    <textarea class="large-text code" name="settingShop[personLine]"><?php echo esc_textarea( $val ); ?></textarea>
    <?php
}

/**
 * CALLBACK: Legal entities
 */
function as_legalPerson_callback() {
    
    $setting = get_option( 'settingShop' );
    
    $val = ( is_array( $setting ) && isset( $setting['legalPersonLine'] ) ) 
        ? $setting['legalPersonLine'] 
        : '';
    
    ?>
    <textarea class="large-text code" name="settingShop[legalPersonLine]"><?php echo esc_textarea( $val ); ?></textarea>
    <?php
}

// ============================================
// VALIDATION AND SAVING
// ============================================

/**
 * CALLBACK: Clean and transform values before saving
 * 
 * @param mixed $options — raw values from the form
 * 
 * @return array Cleaned settings array
 */
function as_sanitize_settings( $options ) {
    
    if ( ! is_array( $options ) ) {
        return array();
    }
    
    foreach ( $options as $name => $value ) {
        
        // Already parsed values (for example "quick" when saved settings are
        // sanitized again) are rebuilt from their "...Line" source below.
        if ( ! is_scalar( $value ) ) {
            continue;
        }
        
        // Strip HTML tags
        $options[ $name ] = strip_tags( $value );
        
        // ============================================
        // MULTI-LINE FORM FIELD PROCESSING
        // ============================================
        
        if ( in_array( $name, array( 'quickLine', 'personLine', 'legalPersonLine' ), true ) ) {
            
            $nameArray = str_replace( 'Line', '', $name );
            
            $options[ $nameArray ] = explode( "\n", $value );
            
            $newValue = array();
            
            foreach ( $options[ $nameArray ] as $numberLine => $undividedLine ) {
                
                // Skip empty lines
                if ( trim( $undividedLine ) === '' ) {
                    continue;
                }
                
                // PHP 8.5: make sure the "[" separator is present
                $separationKeyValue = explode( '[', $undividedLine );
                
                if ( ! isset( $separationKeyValue[1] ) ) {
                    continue;
                }
                
                $separationKeyValue[1] = str_replace( ']', '', $separationKeyValue[1] );
                
                // The field key must not be empty
                if ( empty( $separationKeyValue[0] ) ) {
                    continue;
                }
                
                // ============================================
                // VALUE PARSING
                // Line format: key[Name,placeholder,size]
                // ============================================
                
                $separationValueOfLine = explode( ',', $separationKeyValue[1] );
                
                $field_name = isset( $separationValueOfLine[0] ) 
                    ? trim( $separationValueOfLine[0] ) 
                    : '';
                
                $field_placeholder = isset( $separationValueOfLine[1] ) 
                    ? trim( $separationValueOfLine[1] ) 
                    : '';
                
                $field_size = isset( $separationValueOfLine[2] ) 
                    ? trim( $separationValueOfLine[2] ) 
                    : '100';
                
                // Replace the special marker with a comma (/comma/; the old Russian marker is still accepted)
                $field_placeholder = str_replace( array( '/comma/', '/запятая/' ), ',', $field_placeholder );
                
                $keyField = trim( $separationKeyValue[0] );
                
                $transformInArray = array(
                    'name'        => $field_name,
                    'placeholder' => $field_placeholder,
                    'size'        => $field_size,
                );
                
                $newValue[ $keyField ] = $transformInArray;
            }
            
            $options[ $nameArray ] = $newValue;
        }
        
        // ============================================
        // WHOLESALE PRICES CHECKBOX PROCESSING
        // ============================================
        
        if ( $name === 'wholesalePriceSteps' ) {
            $options[ $name ] = intval( $value );
        }
        
        // ============================================
        // CURRENCY: only the supported codes (since 1.1.0)
        // ============================================
        
        if ( $name === 'currency' ) {
            $currency = strtoupper( trim( (string) $value ) );
            $options[ $name ] = array_key_exists( $currency, as_get_currencies() ) ? $currency : 'USD';
        }
    }
    
    return $options;
}