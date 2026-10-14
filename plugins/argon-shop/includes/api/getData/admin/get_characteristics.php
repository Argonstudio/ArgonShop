<?php
/**
 * Getting product characteristic values
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * WHERE TO TEST:
 * Called from theme templates when product characteristics are output.
 * For example: as_get_charact( 'id', 123, $product_id );
 * 
 * WHAT IT DOES:
 * 1. Finds a characteristic by name or ID
 * 2. If the characteristic has options (children) — returns an array of selected options
 * 3. If the characteristic is simple — returns the text value
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FUNCTION: Get a product characteristic value
 * 
 * @param string $typeField  — lookup type: 'name' (by name) or 'id' (by ID)
 * @param mixed  $valueField — lookup value (name or ID)
 * @param int    $productID  — product ID
 * 
 * @return array|string|false — Array of options, value string or false
 */
function as_get_charact( $typeField, $valueField, $productID ) {
    
    // Product ID validation
    $productID = absint( $productID );
    
    if ( 0 === $productID ) {
        return false;
    }
    
    // Find the characteristic
    if ( $typeField === 'name' ) {
        
        $valueField = sanitize_text_field( $valueField );
        $term = get_term_by( 'name', $valueField, 'characteristics' );
        
        if ( ! $term || is_wp_error( $term ) ) {
            return false;
        }
        
        $termID = absint( $term->term_id );
        
    } elseif ( $typeField === 'id' ) {
        
        $termID = absint( $valueField );
        
        if ( 0 === $termID ) {
            return false;
        }
        
    } else {
        
        return false;
    }
    
    // Check whether the characteristic has children (choice options)
    $termChildren = get_term_children( $termID, 'characteristics' );
    
    if ( is_wp_error( $termChildren ) ) {
        return false;
    }
    
    // If there are children — this is a characteristic with choice options (checkboxes)
    if ( ! empty( $termChildren ) ) {
        
        $characteristics = get_post_meta( $productID, '_characteristics_checkbox_field', true );
        
        if ( ! is_array( $characteristics ) ) {
            return false;
        }
        
        $charactValue = array();
        
        if ( isset( $characteristics[ $termID ] ) && $characteristics[ $termID ] ) {
            
            foreach ( $characteristics[ $termID ] as $key => $value ) {
                
                $key = absint( $key );
                $termOption = get_term_by( 'id', $key, 'characteristics' );
                
                if ( $termOption && ! is_wp_error( $termOption ) ) {
                    $charactValue[] = esc_html( $termOption->name );
                }
            }
            
            if ( empty( $charactValue ) ) {
                return false;
            }
            
        } else {
            
            return false;
        }
        
    } else {
        // Simple characteristic (text field)
        
        $characteristics = get_post_meta( $productID, '_characteristics_text_field', true );
        
        if ( ! is_array( $characteristics ) ) {
            return false;
        }
        
        if ( isset( $characteristics[ $termID ] ) && ! empty( $characteristics[ $termID ] ) ) {
            
            $charactValue = sanitize_text_field( $characteristics[ $termID ] );
            
        } else {
            
            return false;
        }
    }
    
    return $charactValue;
}