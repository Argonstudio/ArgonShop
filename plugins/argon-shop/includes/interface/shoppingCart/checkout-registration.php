<?php
/**
 * Оформление заказа — регистрация покупателя
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 *
 * БЕЗОПАСНОСТЬ:
 * - Пароль генерируется случайно и НИКУДА не отправляется.
 * - Покупатель получает в письме ссылку на установку собственного пароля
 *   (стандартный механизм WordPress: wp-login.php?action=rp).
 * - Администратор видит только факт регистрации и логин.
 * - Пользователь сразу входит через wp_set_auth_cookie().
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ФУНКЦИЯ: регистрирует покупателя при оформлении заказа
 *
 * @param string $login        — желаемый логин
 * @param string $email        — email покупателя
 * @param array  $account_data — данные для user_meta accountData
 *
 * @return array|WP_Error [ user_id, login, password_url ] или ошибка
 *
 * @since 1.1.0
 */
function as_checkout_register_user( $login, $email, $account_data = array() ) {

    $login = sanitize_user( $login, true );

    if ( '' === $login ) {
        return new WP_Error( 'empty_login', __( 'Please enter a username.', 'argon-shop' ) );
    }

    if ( ! is_email( $email ) ) {
        return new WP_Error( 'bad_email', __( 'Please enter a valid email to register.', 'argon-shop' ) );
    }

    $user_id = wp_create_user( $login, wp_generate_password( 24, true, true ), $email );

    if ( is_wp_error( $user_id ) ) {
        return $user_id;
    }

    if ( ! empty( $account_data ) ) {
        add_user_meta( $user_id, 'accountData', $account_data );
    }

    $user = get_userdata( $user_id );
    $key  = get_password_reset_key( $user );

    $password_url = is_wp_error( $key )
        ? wp_lostpassword_url()
        : network_site_url( 'wp-login.php?action=rp&key=' . $key . '&login=' . rawurlencode( $user->user_login ), 'login' );

    // Автоматический вход
    wp_set_current_user( $user_id );
    wp_set_auth_cookie( $user_id, false );

    do_action( 'argon_shop_customer_registered', $user_id );

    return array(
        'user_id'      => $user_id,
        'login'        => $user->user_login,
        'password_url' => $password_url,
    );
}
