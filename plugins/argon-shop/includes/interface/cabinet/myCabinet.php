<?php
/**
 * AJAX-функции кабинета
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Кабинет → редактирование данных, смена email/пароля
 * URL: /cabinet/
 * 
 * ЧТО ОН ДЕЛАЕТ:
 * 1. Сохраняет данные пользователя (accountData)
 * 2. Меняет email и пароль
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Регистрация AJAX-обработчиков
add_action( 'wp_ajax_cabinetEditEmailPassword', 'as_cabinetEditEmailPassword_callback' );
add_action( 'wp_ajax_nopriv_cabinetEditEmailPassword', 'as_cabinetEditEmailPassword_callback' );

add_action( 'wp_ajax_cabinetAccountSave', 'as_cabinetAccountSave_callback' );
add_action( 'wp_ajax_nopriv_cabinetAccountSave', 'as_cabinetAccountSave_callback' );

/**
 * ОБРАБОТЧИК: сохранение данных пользователя
 * 
 * ГДЕ: Кабинет → «Сохранить»
 */
function as_cabinetAccountSave_callback() {
    
    // Проверка nonce
    check_ajax_referer( 'argon_shop_nonce', 'nonce' );
    
    // Проверка userID
    $userID = isset( $_POST['userID'] ) ? absint( $_POST['userID'] ) : 0;
    
    // Проверка: пользователь может сохранять только свои данные
    if ( 0 === $userID || $userID !== get_current_user_id() ) {
        wp_die( __( 'Insufficient permissions', 'argon-shop' ) );
    }
    
    if ( ! isset( $_POST['forBack'] ) ) {
        wp_die( __( 'Not enough data', 'argon-shop' ) );
    }
    
    // Убираем экранирование символов
    $data = stripslashes( $_POST['forBack'] );
    
    // Убираем PHP- и HTML-теги
    $data = strip_tags( $data );
    
    $data = json_decode( $data, true );
    
    if ( ! is_array( $data ) ) {
        wp_die( __( 'Error: invalid data', 'argon-shop' ) );
    }
    
    // Очищаем все поля
    foreach ( $data as $dataType => $dataTypeValue ) {
        
        if ( ! is_array( $dataTypeValue ) ) {
            continue;
        }
        
        foreach ( $data[ $dataType ] as $key => $value ) {
            $data[ $dataType ][ $key ] = sanitize_text_field( wptexturize( $value ) );
        }
    }
    
    // Экранирование для SQL
    $data = wp_slash( $data );
    
    // Проверка: были ли изменения
    $old_data = get_user_meta( $userID, 'accountData', true );
    
    if ( $old_data == $data ) {
        wp_die( __( 'Did you forget to make changes? The new data is the same as the old', 'argon-shop' ) );
    }
    
    // Сохраняем
    update_user_meta( $userID, 'accountData', $data );
    
    // Убеждаемся, что данные сохранены
    if ( get_user_meta( $userID, 'accountData', true ) != $data ) {
        wp_die( __( 'Error: the new data was not saved', 'argon-shop' ) );
    }
    
    echo __( 'Information saved successfully', 'argon-shop' );
    
    wp_die();
}

/**
 * ОБРАБОТЧИК: смена email и пароля
 * 
 * ГДЕ: Кабинет → блок редактирования
 * 
 */
function as_cabinetEditEmailPassword_callback() {
    
    // Проверка nonce
    check_ajax_referer( 'argon_shop_nonce', 'nonce' );
    
    // Проверка userID
    $userID = isset( $_POST['userID'] ) ? absint( $_POST['userID'] ) : 0;
    
    // Проверка: пользователь может менять только свои данные
    if ( 0 === $userID || $userID !== get_current_user_id() ) {
        wp_die( __( 'Insufficient permissions', 'argon-shop' ) );
    }
    
    $userdata = get_user_by( 'id', $userID );
    
    if ( ! $userdata ) {
        wp_die( __( 'User not found', 'argon-shop' ) );
    }
    
    // Проверка ввода
    $email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
    $oldEmail = isset( $_POST['oldEmail'] ) ? sanitize_email( wp_unslash( $_POST['oldEmail'] ) ) : '';
    // Пароли НЕ пропускаем через sanitize_text_field() и другие санитайзеры: она вырезает <теги>
    // и последовательности %xx и схлопывает пробелы, а пароли, которые генерирует WordPress,
    // содержат именно такие символы (< > % и пробелы). Пароли только очищаются от слешей.
    $pass = ( isset( $_POST['pass'] ) && is_string( $_POST['pass'] ) ) ? wp_unslash( $_POST['pass'] ) : '';
    $newPass = ( isset( $_POST['newPass'] ) && is_string( $_POST['newPass'] ) ) ? wp_unslash( $_POST['newPass'] ) : '';
    $newPassConfirm = ( isset( $_POST['newPassConfirm'] ) && is_string( $_POST['newPassConfirm'] ) ) ? wp_unslash( $_POST['newPassConfirm'] ) : '';
    
    $forFront = array(
        'edited' => array(),
        'errors' => array(),
    );
    
    // Проверка текущего пароля
    if ( '' === $pass ) {
        $forFront['errors']['pass'] = __( 'Enter your password', 'argon-shop' );
    } elseif ( ! wp_check_password( $pass, $userdata->data->user_pass, $userID ) ) {
        $forFront['errors']['pass'] = __( 'Incorrect password', 'argon-shop' );
    }
    
    // Проверка нового пароля
    if ( '' !== $newPass ) {
        
        if ( mb_strlen( $newPass, 'UTF-8' ) < 6 ) {
            $forFront['errors']['newPass'] = __( 'The password is too short, enter at least 6 characters', 'argon-shop' );
        }
        
        if ( $newPass !== $newPassConfirm ) {
            $forFront['errors']['newPassConfirm'] = __( 'The new password and its confirmation do not match', 'argon-shop' );
        }
    }
    
    // Проверка email
    if ( $email !== $oldEmail ) {
        
        if ( ! is_email( $email ) ) {
            $forFront['errors']['email'] = __( 'Invalid email', 'argon-shop' );
        } elseif ( email_exists( $email ) && $email !== $userdata->user_email ) {
            $forFront['errors']['email'] = __( 'This email is already registered', 'argon-shop' );
        }
    }
    
    // Если есть ошибки — возвращаем их
    if ( ! empty( $forFront['errors'] ) ) {
        echo wp_json_encode( $forFront );
        wp_die();
    }
    
    // Меняем email
    if ( $email !== $oldEmail ) {
        
        wp_update_user( array(
            'ID'         => $userID,
            'user_email' => $email,
        ) );
        
        $forFront['edited']['newEmail'] = __( 'Email changed', 'argon-shop' );
    }
    
    if ( '' !== $newPass ) {
        
        wp_set_password( $newPass, $userID );
        
        $creds = array(
            'user_login'    => $userdata->user_login,
            'user_password' => $newPass,
            'remember'      => false,
        );
        
        wp_signon( $creds, false );
        
        $forFront['edited']['newPass'] = __( 'Password changed, you are logged in with the new password', 'argon-shop' );
        
        // ============================================
        // НОВЫЙ NONCE ПОСЛЕ СМЕНЫ ПАРОЛЯ
        // ============================================
        // 
        // wp_set_password() аннулирует все сессии пользователя,
        // поэтому старый myajax.nonce становится недействительным.
        // Возвращаем новый nonce, чтобы JS обновил myajax.nonce
        // и последующие AJAX-запросы продолжали работать.
        
        $forFront['newNonce'] = wp_create_nonce( 'argon_shop_nonce' );
        
    }
    
    echo wp_json_encode( $forFront );
    wp_die();
}