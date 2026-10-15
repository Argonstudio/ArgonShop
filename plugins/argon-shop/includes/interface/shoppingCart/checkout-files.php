<?php
/**
 * Оформление заказа — файлы, прикрепляемые покупателем
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 *
 * КАК ХРАНЯТСЯ ФАЙЛЫ:
 * - Приватная папка uploads/argon-shop-private/ (.htaccess + index.php запрещают доступ).
 * - Для каждого заказа — подпапка со случайным именем, файл под исходным
 *   именем внутри (чтобы вложением письма был «Scheme.png», а не phpUwOBxW.bin).
 * - После отправки писем подпапка удаляется (as_checkout_cleanup_files()).
 *
 * ПРОВЕРКИ (все ДО сохранения на диск):
 * - количество файлов, размер каждого и общий размер;
 * - расширение из списка разрешённых;
 * - реальный тип содержимого (wp_check_filetype_and_ext): PHP-скрипт,
 *   переименованный в .jpg, не пройдёт.
 *
 * ХУКИ:
 * - filter argon_shop_allowed_file_types ( array $mimes ) — [ 'ext|ext2' => 'mime/type' ]
 * - filter argon_shop_file_limits ( array $limits ) — max_files, max_file_size, max_total_size (bytes)
 *
 * Nginx не читает .htaccess: на nginx добавьте в конфиг
 *   location ^~ /wp-content/uploads/argon-shop-private/ { deny all; }
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ФУНКЦИЯ: разрешённые типы файлов
 *
 * @return array [ 'расширения через |' => 'MIME-тип' ]
 *
 * @since 1.1.0
 */
function as_get_allowed_file_types() {

    $mimes = array(
        'jpg|jpeg|jpe' => 'image/jpeg',
        'gif'          => 'image/gif',
        'png'          => 'image/png',
        'bmp'          => 'image/bmp',
        'pdf'          => 'application/pdf',
        'doc'          => 'application/msword',
        'docx'         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls'          => 'application/vnd.ms-excel',
        'xlsx'         => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    );

    return (array) apply_filters( 'argon_shop_allowed_file_types', $mimes );
}

/**
 * ФУНКЦИЯ: лимиты файлов заказа
 *
 * Они соответствуют data-sizefile="5" data-amountfiles="3" в шаблонах формы.
 *
 * @return array
 *
 * @since 1.1.0
 */
function as_get_file_limits() {

    $limits = array(
        'max_files'      => 3,
        'max_file_size'  => 5 * MB_IN_BYTES,
        'max_total_size' => 15 * MB_IN_BYTES,
    );

    return (array) apply_filters( 'argon_shop_file_limits', $limits );
}

/**
 * ФУНКЦИЯ: путь к приватной папке файлов заказа (создаёт её при необходимости)
 *
 * @return string|false Абсолютный путь без завершающего слэша или false
 *
 * @since 1.1.0
 */
function as_get_private_upload_dir() {

    $upload_dir = wp_upload_dir( null, false );

    if ( ! empty( $upload_dir['error'] ) ) {
        return false;
    }

    $dir = trailingslashit( $upload_dir['basedir'] ) . 'argon-shop-private';

    if ( ! file_exists( $dir ) && ! wp_mkdir_p( $dir ) ) {
        return false;
    }

    if ( ! file_exists( $dir . '/.htaccess' ) ) {
        @file_put_contents( $dir . '/.htaccess', "# ArgonShop: private order files\nRequire all denied\n<IfModule !mod_authz_core.c>\n    Deny from all\n</IfModule>\n" );
    }

    if ( ! file_exists( $dir . '/index.php' ) ) {
        @file_put_contents( $dir . '/index.php', "<?php\n// Silence is golden.\n" );
    }

    return $dir;
}

/**
 * ФУНКЦИЯ: проверяет и сохраняет файлы из $_FILES['fileCart']
 *
 * @return array|WP_Error [ 'paths' => string[], 'dir' => string|null ] или ошибка
 *
 * @since 1.1.0
 */
function as_checkout_handle_files() {

    $result = array( 'paths' => array(), 'dir' => null );

    if ( empty( $_FILES['fileCart'] ) || empty( $_FILES['fileCart']['name'] ) || ! is_array( $_FILES['fileCart']['name'] ) ) {
        return $result;
    }

    $limits = as_get_file_limits();
    $mimes  = as_get_allowed_file_types();
    $files  = array();
    $total  = 0;

    // --- 1. Проверяем всё, ничего не сохраняя ---

    foreach ( $_FILES['fileCart']['name'] as $i => $raw_name ) {

        $error = isset( $_FILES['fileCart']['error'][ $i ] ) ? (int) $_FILES['fileCart']['error'][ $i ] : UPLOAD_ERR_NO_FILE;

        if ( UPLOAD_ERR_NO_FILE === $error ) {
            continue;
        }

        if ( UPLOAD_ERR_INI_SIZE === $error || UPLOAD_ERR_FORM_SIZE === $error ) {
            return new WP_Error( 'file_size', sprintf( /* translators: %s: максимальный размер файла. */ __( 'File size must not exceed %s.', 'argon-shop' ), size_format( $limits['max_file_size'] ) ) );
        }

        $tmp_name = isset( $_FILES['fileCart']['tmp_name'][ $i ] ) ? $_FILES['fileCart']['tmp_name'][ $i ] : '';

        if ( UPLOAD_ERR_OK !== $error || ! is_uploaded_file( $tmp_name ) ) {
            return new WP_Error( 'file_upload', __( 'Could not upload the file, please try again.', 'argon-shop' ) );
        }

        $name = sanitize_file_name( wp_unslash( $raw_name ) );
        $size = (int) filesize( $tmp_name );

        if ( $size > $limits['max_file_size'] ) {
            return new WP_Error( 'file_size', sprintf( /* translators: 1: имя файла, 2: максимальный размер файла. */ __( 'File “%1$s” is larger than %2$s.', 'argon-shop' ), $name, size_format( $limits['max_file_size'] ) ) );
        }

        // Реальный тип по содержимому, а не только по расширению
        $check = wp_check_filetype_and_ext( $tmp_name, $name, $mimes );

        if ( empty( $check['ext'] ) || empty( $check['type'] ) ) {
            $exts = implode( ', ', explode( '|', implode( '|', array_keys( $mimes ) ) ) );
            return new WP_Error( 'file_type', sprintf( /* translators: 1: имя файла, 2: список разрешённых расширений. */ __( 'File “%1$s” is not allowed. Allowed formats: %2$s.', 'argon-shop' ), $name, $exts ) );
        }

        // WordPress может исправить расширение (например, .jpg → .png по содержимому)
        if ( ! empty( $check['proper_filename'] ) ) {
            $name = $check['proper_filename'];
        }

        $total  += $size;
        $files[] = array( 'tmp' => $tmp_name, 'name' => $name );
    }

    if ( count( $files ) > $limits['max_files'] ) {
        return new WP_Error( 'file_count', sprintf( /* translators: %d: максимальное количество файлов. */ __( 'You can attach no more than %d files.', 'argon-shop' ), (int) $limits['max_files'] ) );
    }

    if ( $total > $limits['max_total_size'] ) {
        return new WP_Error( 'file_total', sprintf( /* translators: %s: максимальный общий размер. */ __( 'Total file size must not exceed %s.', 'argon-shop' ), size_format( $limits['max_total_size'] ) ) );
    }

    if ( ! $files ) {
        return $result;
    }

    // --- 2. Сохраняем в приватную папку ---

    $base = as_get_private_upload_dir();

    if ( ! $base ) {
        return new WP_Error( 'file_dir', __( 'Could not save the files. Try placing the order without files or contact us.', 'argon-shop' ) );
    }

    $dir = $base . '/' . wp_generate_password( 24, false, false );

    if ( ! wp_mkdir_p( $dir ) ) {
        return new WP_Error( 'file_dir', __( 'Could not save the files. Try placing the order without files or contact us.', 'argon-shop' ) );
    }

    $result['dir'] = $dir;

    foreach ( $files as $file ) {

        $target = $dir . '/' . wp_unique_filename( $dir, $file['name'] );

        if ( ! move_uploaded_file( $file['tmp'], $target ) ) {
            as_checkout_cleanup_files( $result );
            return new WP_Error( 'file_move', __( 'Could not save the files. Please try again.', 'argon-shop' ) );
        }

        $result['paths'][] = $target;
    }

    return $result;
}

/**
 * ФУНКЦИЯ: удаляет файлы заказа после отправки писем
 *
 * @param array $files — результат as_checkout_handle_files()
 *
 * @since 1.1.0
 */
function as_checkout_cleanup_files( $files ) {

    if ( empty( $files['dir'] ) || ! is_dir( $files['dir'] ) ) {
        return;
    }

    $base = as_get_private_upload_dir();

    // Удаляем только внутри приватной папки
    if ( ! $base || 0 !== strpos( wp_normalize_path( $files['dir'] ), wp_normalize_path( $base ) . '/' ) ) {
        return;
    }

    foreach ( (array) glob( $files['dir'] . '/*' ) as $path ) {
        if ( is_file( $path ) ) {
            @unlink( $path );
        }
    }

    @rmdir( $files['dir'] );
}
