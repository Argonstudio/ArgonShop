<?php
/**
 * Установка плагина, обновление данных и деактивация
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 *
 * ВЕРСИИ И МИГРАЦИИ:
 * - Версия кода: константа ARGON_SHOP_VERSION (argon-shop.php).
 * - Версия данных на сайте: опция argon_shop_version.
 * - as_maybe_upgrade() сравнивает их при каждом запросе и запускает
 *   миграции из as_get_migrations() по порядку, затем сохраняет новую версию.
 *
 * КАК ДОБАВИТЬ МИГРАЦИЮ (например, при изменении формата мета или cookie):
 * 1. Повысьте ARGON_SHOP_VERSION и Version: в шапке argon-shop.php.
 * 2. Добавьте запись 'X.Y.Z' => 'as_migrate_X_Y_Z' в as_get_migrations().
 * 3. Миграция должна быть идемпотентной (повторный запуск ничего не ломает).
 * 4. Опишите изменение в CHANGELOG.md.
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

register_activation_hook( ARGON_SHOP_FILE, 'as_activate' );
register_deactivation_hook( ARGON_SHOP_FILE, 'as_deactivate' );
add_action( 'init', 'as_maybe_upgrade', 1 );

/**
 * Активация: типы записей, стандартные страницы и настройки, ЧПУ
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
 * Деактивация: сброс правил перезаписи (данные не удаляются)
 *
 * @since 1.1.0
 */
function as_deactivate() {
    flush_rewrite_rules();
}

/**
 * Первая установка: страницы корзины и кабинета, поля формы заказа.
 * Если настройки уже есть — ничего не меняет.
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
        // Поля формы по умолчанию: имя[Подпись,Плейсхолдер,Макс. длина].
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
 * Находит страницу с шаблоном или создаёт её
 *
 * @param string $title    — заголовок новой страницы
 * @param string $template — файл шаблона темы
 *
 * @return int ID страницы
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
 * Список миграций данных: версия => функция
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
 * Запускает недостающие миграции
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
 * 1.1.0: файлы заказов теперь хранятся в приватной папке argon-shop-private.
 * Убираем остатки старой публичной папки uploads/order-files
 * (файлы покупателя могли там остаться, если отправка письма была прервана).
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
 * Собирает строку полей формы в формате настроек: имя[Подпись,Плейсхолдер,Макс. длина].
 * Запятые в текстах заменяются маркером «/comma/», который использует парсер настроек.
 *
 * @since 1.1.0
 *
 * @param array $fields Список array( name, label, placeholder, max_length ).
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
