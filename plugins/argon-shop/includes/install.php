<?php
/**
 * Plugin installation, data upgrades and deactivation
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 *
 * VERSIONS AND MIGRATIONS:
 * - Code version: the ARGON_SHOP_VERSION constant (argon-shop.php).
 * - Data version on the site: the argon_shop_version option.
 * - as_maybe_upgrade() compares them on every request and runs the
 *   migrations from as_get_migrations() in order, then stores the new version.
 *
 * HOW TO ADD A MIGRATION (for example, when the meta or cookie format changes):
 * 1. Bump ARGON_SHOP_VERSION and Version: in the argon-shop.php header.
 * 2. Add an item 'X.Y.Z' => 'as_migrate_X_Y_Z' to as_get_migrations().
 * 3. The migration must be idempotent (running it again breaks nothing).
 * 4. Describe the change in CHANGELOG.md.
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

register_activation_hook( ARGON_SHOP_FILE, 'as_activate' );
register_deactivation_hook( ARGON_SHOP_FILE, 'as_deactivate' );
add_action( 'init', 'as_maybe_upgrade', 1 );

/**
 * Activation: post types, default pages and settings, permalinks
 *
 * @since 1.1.0
 */
function as_activate() {

    as_create_post_type();
    as_install_defaults();
    as_maybe_upgrade();

    flush_rewrite_rules();
}

/**
 * Deactivation: flush the rewrite rules (data is not deleted)
 *
 * @since 1.1.0
 */
function as_deactivate() {
    flush_rewrite_rules();
}

/**
 * First install: cart and account pages, order form fields.
 * If settings already exist — changes nothing.
 *
 * @since 1.1.0
 */
function as_install_defaults() {

    if ( false !== get_option( 'settingShop' ) ) {
        return;
    }

    $settings = array(
        'shoppingCartPage' => as_install_page( __( 'Cart', 'argon-shop' ), 'shoppingCartPage.php' ),
        'cabinetPage'      => as_install_page( __( 'My account', 'argon-shop' ), 'myCabinetPage.php' ),
        'typeSale'         => 'noSale',
        'currency'         => 'USD',
        // Default form fields: name[Label,Placeholder,Max length].
        'quickLine'        => as_install_field_line( array(
            array( 'telefonUser', __( 'Phone', 'argon-shop' ), _x( '+7 (___) ___-__-__', 'phone placeholder', 'argon-shop' ), 50 ),
            array( 'addressUser', __( 'Delivery address', 'argon-shop' ), __( 'City, street, house', 'argon-shop' ), 50 ),
        ) ),
        'personLine'       => as_install_field_line( array(
            array( 'nameUser', __( 'Name', 'argon-shop' ), _x( 'John', 'name placeholder', 'argon-shop' ), 50 ),
            array( 'telefonUser', __( 'Phone', 'argon-shop' ), _x( '+7 (___) ___-__-__', 'phone placeholder', 'argon-shop' ), 50 ),
        ) ),
        'legalPersonLine'  => as_install_field_line( array(
            array( 'companyNameUser', __( 'Company name', 'argon-shop' ), _x( 'Example LLC', 'company placeholder', 'argon-shop' ), 100 ),
            array( 'innUser', _x( 'TIN', 'taxpayer identification number', 'argon-shop' ), '', 50 ),
            array( 'kppUser', _x( 'KPP', 'Russian tax registration reason code', 'argon-shop' ), '', 50 ),
            array( 'legalAddressUser', __( 'Legal address', 'argon-shop' ), '', 100 ),
        ) ),
    );

    add_option( 'settingShop', as_sanitize_settings( $settings ) );
}

/**
 * Finds a page with the template or creates it
 *
 * @param string $title    — title of the new page
 * @param string $template — theme template file
 *
 * @return int Page ID
 *
 * @since 1.1.0
 */
function as_install_page( $title, $template ) {

    $existing = get_posts( array(
        'post_type'   => 'page',
        'post_status' => array( 'publish', 'draft', 'private' ),
        'meta_key'    => '_wp_page_template',
        'meta_value'  => $template,
        'numberposts' => 1,
        'fields'      => 'ids',
    ) );

    if ( $existing ) {
        return (int) $existing[0];
    }

    $page_id = wp_insert_post( array(
        'post_type'     => 'page',
        'post_status'   => 'publish',
        'post_title'    => $title,
        'page_template' => $template,
    ) );

    return is_wp_error( $page_id ) ? 0 : (int) $page_id;
}

/**
 * List of data migrations: version => function
 *
 * @return array
 *
 * @since 1.1.0
 */
function as_get_migrations() {
    return array(
        '1.1.0' => 'as_migrate_1_1_0',
    );
}

/**
 * Runs the missing migrations
 *
 * @since 1.1.0
 */
function as_maybe_upgrade() {

    $installed = get_option( 'argon_shop_version', '1.0.0' );

    if ( version_compare( $installed, ARGON_SHOP_VERSION, '>=' ) ) {
        return;
    }

    foreach ( as_get_migrations() as $version => $callback ) {

        if ( version_compare( $installed, $version, '<' ) && version_compare( $version, ARGON_SHOP_VERSION, '<=' ) && is_callable( $callback ) ) {
            call_user_func( $callback );
            update_option( 'argon_shop_version', $version );
        }
    }

    update_option( 'argon_shop_version', ARGON_SHOP_VERSION );
}

/**
 * 1.1.0: order files are now stored in the private argon-shop-private folder.
 * Remove the leftovers of the old public uploads/order-files folder
 * (buyer files could have ended up there if sending the email was interrupted).
 *
 * @since 1.1.0
 */
function as_migrate_1_1_0() {

    $upload_dir = wp_upload_dir( null, false );

    if ( ! empty( $upload_dir['error'] ) ) {
        return;
    }

    $old_dir = trailingslashit( $upload_dir['basedir'] ) . 'order-files';

    if ( ! is_dir( $old_dir ) ) {
        return;
    }

    foreach ( (array) glob( $old_dir . '/*' ) as $path ) {
        if ( is_file( $path ) ) {
            @unlink( $path );
        }
    }

    @rmdir( $old_dir );
}

/**
 * Builds a form fields line in the settings format: name[Label,Placeholder,Max length].
 * Commas inside texts are replaced with the "/comma/" marker used by the settings parser.
 *
 * @since 1.1.0
 *
 * @param array $fields List of array( name, label, placeholder, max_length ).
 * @return string
 */
function as_install_field_line( $fields ) {
    $lines = array();
    foreach ( $fields as $field ) {
        list( $name, $label, $placeholder, $max ) = $field;
        $lines[] = $name . '[' . str_replace( ',', '/comma/', $label ) . ',' . str_replace( ',', '/comma/', $placeholder ) . ',' . (int) $max . ']';
    }
    return implode( "\n", $lines );
}
