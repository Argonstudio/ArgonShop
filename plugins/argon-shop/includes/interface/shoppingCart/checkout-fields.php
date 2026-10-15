<?php
/**
 * Оформление заказа — поля формы и обязательные поля
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 *
 * ХУКИ:
 * - filter argon_shop_required_fields ( array $fields, string $typeForm )
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * ФУНКЦИЯ: получает и проверяет данные формы заказа
 * 
 * ГДЕ ТЕСТИРОВАТЬ: автоматически при оформлении заказа
 * ЧТО ОН ДЕЛАЕТ:
 * 1. Получает данные из формы заказа
 * 2. Проверяет обязательные поля
 * 3. Проверяет email и телефон
 * 4. Собирает массивы для письма и для сохранения в базу данных
 * 
 * @param string $typeForm — тип формы: quick, person, legalPerson
 * @param array  $POST     — данные из $_POST
 * 
 * @return array Массив с полями формы и данными кабинета
 */
function as_get_valueFieldsCart( $typeForm, $POST ) {
    
    $settingShop = get_option( 'settingShop' );
    
    $cartReg = ( ! empty( $POST['cartReg'] ) ) ? $POST['cartReg'] : 0;
    
    // Список обязательных полей определяет сервер, а не браузер.
    // $_POST['required'] (отправляемый cart.js) намеренно игнорируется:
    // его можно убрать из запроса, чтобы обойти проверку.
    $required = array();
    
    foreach ( as_get_required_fields( $typeForm ) as $requiredKey ) {
        $required[ $requiredKey ] = true;
    }
    
    $fieldsCart = array();
    $accountData = array();
    
    /**
     * Внутренние функции заполнения массивов данных формы
     * $fieldsCart — отправляется письмом
     * $accountData — данные пользователя, записываемые в базу при регистрации
     */
    
    // Передаём параметры внешней функции в $get_valueByType, а $fieldsCart по ссылке, чтобы его можно было менять
    $get_valueByType = function( $typeForm ) use ( $settingShop, $POST, &$required, &$accountData, &$fieldsCart ) {
        
        if ( ! isset( $settingShop[ $typeForm ] ) || ! is_array( $settingShop[ $typeForm ] ) ) {
            return;
        }
        
        foreach ( $settingShop[ $typeForm ] as $key => $value ) {
            
            $valueField = isset( $POST[ $key ] ) ? sanitize_text_field( stripslashes( $POST[ $key ] ) ) : '';
            
            if ( ! empty( $POST['cartReg'] ) ) {
                
                $typeData = '';
                
                if ( $typeForm === 'person' ) {
                    $typeData = 'accountDetail';
                } elseif ( $typeForm === 'legalPerson' ) {
                    $typeData = 'accountLegalDetail';
                }
                
                if ( ! empty( $typeData ) && empty( $accountData[ $typeData ][ $key ] ) ) {
                    $accountData[ $typeData ][ $key ] = $valueField;
                }
            }
            
            if ( ! empty( $valueField ) ) {
                
                $fieldsCart[ $key ] = array(
                    'name'  => ( ! empty( $value['name'] ) ) ? $value['name'] : '',
                    'value' => $valueField,
                );
                
            } elseif ( ! empty( $required[ $key ] ) && $required[ $key ] === true ) {
                
                $required[ $key ] = ( ! empty( $value['name'] ) ) ? $value['name'] : '';
            }
        }
    };
    
    $get_valueByFieldname = function( $fieldName, $nameInArray ) use ( $POST, &$required, &$fieldsCart ) {
        
        $fieldsCart[ $fieldName ] = array(
            'name'  => $nameInArray,
            'value' => ( ! empty( $POST[ $fieldName ] ) ) ? sanitize_text_field( stripslashes( $POST[ $fieldName ] ) ) : '',
        );
        
        // Заполняем имя для письма на случай, если показана ошибка пустого поля
        if ( ! empty( $required[ $fieldName ] ) && $required[ $fieldName ] === true ) {
            $required[ $fieldName ] = $nameInArray;
        }
    };
    
    /**
     * Собираем массив данных для письма в зависимости от типа формы
     */
    
    if ( $typeForm !== 'legalPerson' ) {
        
        $get_valueByType( $typeForm );
        
        if ( $typeForm !== 'quick' ) {
            $get_valueByFieldname( 'emailUser', 'Email' );
        }
        
    } else {
        
        $get_valueByType( 'person' );
        $get_valueByFieldname( 'emailUser', 'Email' );
        $get_valueByType( $typeForm );
    }
    
    $get_valueByFieldname( 'messageUser', __( 'Message', 'argon-shop' ) );
    
    /**
     * Повторно проверяем заполненность полей
     */
    
    // Логин обрабатываем отдельно
    if ( ! empty( $POST['cartReg'] ) ) {
        
        if ( empty( $POST['loginUser'] ) ) {
            $required['loginUser'] = __( 'Username', 'argon-shop' );
        } else {
            unset( $required['loginUser'] );
        }
    }
    
    $blankRequired = array_diff_key( $required, $fieldsCart );
    
    // Для регистрации нужен email: на него отправляется ссылка установки пароля
    if ( ! empty( $POST['cartReg'] ) && empty( $POST['emailUser'] ) ) {
        $blankRequired['emailUser'] = 'Email';
    }
    
    if ( $blankRequired ) {
        
        $result = array(
            'type'     => 'error',
            'typeForm' => $typeForm,
            'message'  => __( 'Please fill in the fields: ', 'argon-shop' ),
        );
        
        foreach ( $blankRequired as $key => $value ) {
            $result['message'] .= $value . ', ';
        }
        
        $result['message'] = rtrim( $result['message'], ', ' );
        
        echo json_encode( $result );
        wp_die();
    }
    
    /**
     * Проверяем корректность значений
     */
    
    if ( ! empty( $fieldsCart['emailUser']['value'] ) ) {
        
        $user_mail = $fieldsCart['emailUser']['value'];
        $correctEmail = filter_var( $user_mail, FILTER_VALIDATE_EMAIL );
        
        if ( ! $correctEmail ) {
            
            $result = array(
                'type'     => 'error',
                'typeForm' => $typeForm,
                'message'  => __( 'The email is incorrect', 'argon-shop' ),
            );
            
            echo json_encode( $result );
            wp_die();
        }
    }
    
    if ( ! empty( $fieldsCart['telefonUser']['value'] ) ) {
        
        $user_phone = $fieldsCart['telefonUser']['value'];
        
        $result = array(
            'type'     => 'error',
            'typeForm' => $typeForm,
        );
        
        if ( as_countDigits( $user_phone ) < 11 ) {
            
            $result['message'] = __( 'The phone number has fewer than 11 characters', 'argon-shop' );
            
            echo json_encode( $result );
            wp_die();
        }
    }
    
    /**
     * Дополнительная обработка, формирование ответа
     */
    
    foreach ( $fieldsCart as $fieldsType => $fieldsTypeValue ) {
        
        foreach ( $fieldsCart[ $fieldsType ] as $key => $value ) {
            
            // Заменяем кавычки и другие символы
            $fieldsCart[ $fieldsType ][ $key ] = wptexturize( $value );
        }
    }
    
    $fieldsArrays = array(
        'fieldsCart'  => $fieldsCart,
        'accountData' => array(),
    );
    
    if ( $cartReg ) {
        
        foreach ( $accountData as $fieldsType => $fieldsTypeValue ) {
            
            foreach ( $accountData[ $fieldsType ] as $key => $value ) {
                
                // Заменяем кавычки и другие символы
                $accountData[ $fieldsType ][ $key ] = wptexturize( $value );
            }
        }
        
        $fieldsArrays['accountData'] = $accountData;
    }
    
    return $fieldsArrays;
}

/**
 * ФУНКЦИЯ: обязательные поля формы оформления заказа
 * 
 * Единый источник истины: используется и сервером при проверке заказа,
 * и шаблонами темы (template-parts/cart/*.php) для атрибута required.
 * Поля, которых нет в настройках магазина (settingShop), отбрасываются,
 * чтобы поле, удалённое из настроек, не блокировало оформление заказа.
 * 
 * Список можно изменить фильтром в теме:
 * add_filter( 'argon_shop_required_fields', function( $fields, $typeForm ) { ... }, 10, 2 );
 * 
 * @param string $typeForm — 'quick', 'person' или 'legalPerson'
 * 
 * @return array Ключи обязательных полей
 *
 * @since 1.1.0
 */
function as_get_required_fields( $typeForm ) {
    
    $defaults = array(
        'quick'       => array( 'telefonUser', 'addressUser' ),
        'person'      => array( 'nameUser', 'telefonUser' ),
        'legalPerson' => array( 'nameUser', 'telefonUser', 'companyNameUser', 'innUser', 'kppUser', 'legalAddressUser' ),
    );
    
    $fields = isset( $defaults[ $typeForm ] ) ? $defaults[ $typeForm ] : array();
    $fields = (array) apply_filters( 'argon_shop_required_fields', $fields, $typeForm );
    
    // Форма юрлица выводит поля из двух групп настроек: person и legalPerson
    $settingShop = get_option( 'settingShop' );
    $groups = ( 'legalPerson' === $typeForm ) ? array( 'person', 'legalPerson' ) : array( $typeForm );
    $existingFields = array();
    
    foreach ( $groups as $group ) {
        if ( ! empty( $settingShop[ $group ] ) && is_array( $settingShop[ $group ] ) ) {
            $existingFields = array_merge( $existingFields, array_keys( $settingShop[ $group ] ) );
        }
    }
    
    return array_values( array_intersect( $fields, $existingFields ) );
}
