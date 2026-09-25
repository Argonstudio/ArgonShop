<?php
/**
 * AJAX-функционал личного кабинета
 *
 * @package Argon_Shop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Личный кабинет → редактирование данных, смена email/пароля
 * URL: /cabinet/
 * 
 * ЧТО ДЕЛАЕТ:
 * 1. Сохранение данных пользователя (accountData)
 * 2. Изменение email и пароля
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Регистрация AJAX-обработчиков
add_action( 'wp_ajax_cabinetEditEmailPassword', 'cabinetEditEmailPassword_callback' );
add_action( 'wp_ajax_nopriv_cabinetEditEmailPassword', 'cabinetEditEmailPassword_callback' );

add_action( 'wp_ajax_cabinetAccountSave', 'cabinetAccountSave_callback' );
add_action( 'wp_ajax_nopriv_cabinetAccountSave', 'cabinetAccountSave_callback' );

/**
 * ОБРАБОТЧИК: Сохранение данных пользователя
 * 
 * ГДЕ: Личный кабинет → "Сохранить"
 */
function cabinetAccountSave_callback() {
    
    // Проверка nonce
    check_ajax_referer( 'argon_shop_nonce', 'nonce' );
    
    // Валидация userID
    $userID = isset( $_POST['userID'] ) ? absint( $_POST['userID'] ) : 0;
    
    // Проверка: пользователь может сохранять только свои данные
    if ( $userID !== get_current_user_id() ) {
        wp_die( 'Недостаточно прав' );
    }
    
    if ( ! isset( $_POST['forBack'] ) ) {
        wp_die( 'Недостаточно данных' );
    }
    
    // Удаляем экранирование символов
    $data = stripslashes( $_POST['forBack'] );
    
    // Удаляем php и html теги
    $data = strip_tags( $data );
    
    $data = json_decode( $data, true );
    
    if ( ! is_array( $data ) ) {
        wp_die( 'Ошибка: некорректные данные' );
    }
    
    // Санитизация всех полей
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
        wp_die( 'Вы не забыли внести изменения? Новые данные соответствуют старым' );
    }
    
    // Сохранение
    update_user_meta( $userID, 'accountData', $data );
    
    // Проверка, что данные сохранились
    if ( get_user_meta( $userID, 'accountData', true ) != $data ) {
        wp_die( 'Ошибка: новые данные не были сохранены' );
    }
    
    echo 'Информация успешно сохранена';
    
    wp_die();
}

/**
 * ОБРАБОТЧИК: Изменение email и пароля
 * 
 * ГДЕ: Личный кабинет → блок редактирования
 * 
 */
function cabinetEditEmailPassword_callback() {
    
    // Проверка nonce
    check_ajax_referer( 'argon_shop_nonce', 'nonce' );
    
    // Валидация userID
    $userID = isset( $_POST['userID'] ) ? absint( $_POST['userID'] ) : 0;
    
    // Проверка: пользователь может менять только свои данные
    if ( $userID !== get_current_user_id() ) {
        wp_die( 'Недостаточно прав' );
    }
    
    $userdata = get_user_by( 'id', $userID );
    
    if ( ! $userdata ) {
        wp_die( 'Пользователь не найден' );
    }
    
    // Валидация входных данных
    $email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
    $oldEmail = isset( $_POST['oldEmail'] ) ? sanitize_email( wp_unslash( $_POST['oldEmail'] ) ) : '';
    $pass = isset( $_POST['pass'] ) ? sanitize_text_field( wp_unslash( $_POST['pass'] ) ) : '';
    $newPass = isset( $_POST['newPass'] ) ? sanitize_text_field( wp_unslash( $_POST['newPass'] ) ) : '';
    $newPassConfirm = isset( $_POST['newPassConfirm'] ) ? sanitize_text_field( wp_unslash( $_POST['newPassConfirm'] ) ) : '';
    
    $forFront = array(
        'edited' => array(),
        'errors' => array(),
    );
    
    // Проверка текущего пароля
    if ( empty( $pass ) ) {
        $forFront['errors']['pass'] = 'Введите пароль';
    } elseif ( ! wp_check_password( $pass, $userdata->data->user_pass, $userID ) ) {
        $forFront['errors']['pass'] = 'Пароль указан неверно';
    }
    
    // Проверка нового пароля
    if ( ! empty( $newPass ) ) {
        
        if ( strlen( $newPass ) < 6 ) {
            $forFront['errors']['newPass'] = 'Пароль слишком короткий, введите от 6 символов';
        }
        
        if ( $newPass !== $newPassConfirm ) {
            $forFront['errors']['newPassConfirm'] = 'Новый пароль и его подтверждение не совпадают';
        }
    }
    
    // Проверка email
    if ( $email !== $oldEmail ) {
        
        if ( ! is_email( $email ) ) {
            $forFront['errors']['email'] = 'Некорректный email';
        } elseif ( email_exists( $email ) && $email !== $userdata->user_email ) {
            $forFront['errors']['email'] = 'Такой email уже зарегистрирован';
        }
    }
    
    // Если есть ошибки — возвращаем
    if ( ! empty( $forFront['errors'] ) ) {
        echo wp_json_encode( $forFront );
        wp_die();
    }
    
    // Изменение email
    if ( $email !== $oldEmail ) {
        
        wp_update_user( array(
            'ID'         => $userID,
            'user_email' => $email,
        ) );
        
        $forFront['edited']['newEmail'] = 'Email изменен';
    }
    
        if ( ! empty( $newPass ) ) {
        
        wp_set_password( $newPass, $userID );
        
        $creds = array(
            'user_login'    => $userdata->user_login,
            'user_password' => $newPass,
            'remember'      => false,
        );
        
        wp_signon( $creds, false );
        
        $forFront['edited']['newPass'] = 'Пароль изменен, произведена авторизация с новым паролем';
        
        // ============================================
        // НОВЫЙ NONCE ПОСЛЕ СМЕНЫ ПАРОЛЯ
        // ============================================
        // 
        // wp_set_password() инвалидирует все сессии пользователя,
        // старый myajax.nonce становится недействительным.
        // Возвращаем новый nonce, чтобы JS обновил myajax.nonce
        // и последующие AJAX-запросы работали.
        
        $forFront['newNonce'] = wp_create_nonce( 'argon_shop_nonce' );
        
    }
    
    echo wp_json_encode( $forFront );
    wp_die();
}