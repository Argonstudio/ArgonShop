<?php
/**
 * Удаление плагина (Плагины → Удалить)
 *
 * @package Argon_Shop
 *
 * УДАЛЯЕТСЯ: настройки магазина, служебные опции, временные файлы заказов.
 * СОХРАНЯЕТСЯ: товары, заказы, категории, характеристики, пользователи и их данные,
 * счётчик заказов (counterOrders) — чтобы нумерация не начиналась с 1 после переустановки.
 * Их можно удалить вручную в админке или через WP-CLI.
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
