<?php
/**
 * Checkout — form fields and required fields
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 *
 * HOOKS:
 * - filter argon_shop_required_fields ( array $fields, string $typeForm )
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * FUNCTION: Get and validate the order form data
 * 
 * WHERE TO TEST: Automatically on checkout
 * WHAT IT DOES:
 * 1. Gets the data from the order form
 * 2. Checks the required fields
 * 3. Validates the email and phone
 * 4. Builds arrays for the email and for saving to the database
 * 
 * @param string $typeForm — form type: quick, person, legalPerson
 * @param array  $POST     — data from $_POST
 * 
 * @return array Array with the form fields and the account data
 */
function as_get_valueFieldsCart( $typeForm, $POST ) {
    
    $settingShop = get_option( 'settingShop' );
    
    $cartReg = ( ! empty( $POST['cartReg'] ) ) ? $POST['cartReg'] : 0;
    
    // The list of required fields is defined by the server, not by the browser.
    // $_POST['required'] (sent by cart.js) is ignored on purpose:
    // it could be removed from the request to bypass the check.
    $required = array();
    
    foreach ( as_get_required_fields( $typeForm ) as $requiredKey ) {
        $required[ $requiredKey ] = true;
    }
    
    $fieldsCart = array();
    $accountData = array();
    
    /**
     * Internal functions for filling the form data arrays
     * $fieldsCart — sent by email
     * $accountData — user data written to the database on registration
     */
    
    // Pass the outer function parameters to $get_valueByType, and $fieldsCart by reference so it can be modified
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
        
        // Fill in the name for the email in case an empty-field error is shown
        if ( ! empty( $required[ $fieldName ] ) && $required[ $fieldName ] === true ) {
            $required[ $fieldName ] = $nameInArray;
        }
    };
    
    /**
     * Build the array of data for the email depending on the form type
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
     * Re-check that the fields are filled in
     */
    
    // Handle the login separately
    if ( ! empty( $POST['cartReg'] ) ) {
        
        if ( empty( $POST['loginUser'] ) ) {
            $required['loginUser'] = __( 'Username', 'argon-shop' );
        } else {
            unset( $required['loginUser'] );
        }
    }
    
    $blankRequired = array_diff_key( $required, $fieldsCart );
    
    // Registration needs an email: the password setup link is sent to it
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
     * Check that the values are valid
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
     * Additional processing, building the response
     */
    
    foreach ( $fieldsCart as $fieldsType => $fieldsTypeValue ) {
        
        foreach ( $fieldsCart[ $fieldsType ] as $key => $value ) {
            
            // Replace quotes and other characters
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
                
                // Replace quotes and other characters
                $accountData[ $fieldsType ][ $key ] = wptexturize( $value );
            }
        }
        
        $fieldsArrays['accountData'] = $accountData;
    }
    
    return $fieldsArrays;
}

/**
 * FUNCTION: Required fields of the checkout form
 * 
 * Single source of truth: used both by the server when checking the order
 * and by the theme templates (template-parts/cart/*.php) for the required attribute.
 * Fields that are not in the store settings (settingShop) are dropped,
 * so that a field removed from the settings does not block checkout.
 * 
 * The list can be changed with a filter in the theme:
 * add_filter( 'argon_shop_required_fields', function( $fields, $typeForm ) { ... }, 10, 2 );
 * 
 * @param string $typeForm — 'quick', 'person' or 'legalPerson'
 * 
 * @return array Keys of the required fields
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
    
    // The legal entity form outputs fields from two settings groups: person and legalPerson
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
