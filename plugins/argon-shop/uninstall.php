<?php
/**
 * Plugin uninstall (Plugins → Delete)
 *
 * @package Argon_Shop
 *
 * DELETED: store settings, service options, temporary order files.
 * KEPT: products, orders, categories, characteristics, users and their data,
 * the order counter (counterOrders) — so numbering does not restart at 1 after reinstalling.
 * They can be removed manually in the admin or via WP-CLI.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

delete_option( 'settingShop' );
delete_option( 'argon_shop_version' );
delete_option( 'zg_lwp_widget' );

$argon_shop_upload_dir = wp_upload_dir( null, false );

if ( empty( $argon_shop_upload_dir['error'] ) ) {

    foreach ( array( 'argon-shop-private', 'order-files' ) as $argon_shop_folder ) {

        $argon_shop_dir = trailingslashit( $argon_shop_upload_dir['basedir'] ) . $argon_shop_folder;

        if ( ! is_dir( $argon_shop_dir ) ) {
            continue;
        }

        $argon_shop_items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator( $argon_shop_dir, FilesystemIterator::SKIP_DOTS ),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ( $argon_shop_items as $argon_shop_item ) {
            $argon_shop_item->isDir() ? @rmdir( $argon_shop_item->getPathname() ) : @unlink( $argon_shop_item->getPathname() );
        }

        @rmdir( $argon_shop_dir );
    }
}
