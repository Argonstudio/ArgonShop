<?php
/**
 * Получение значений характеристик товара
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Вызывается из шаблонов темы при выводе характеристик товара.
 * For example: as_get_charact( 'id', 123, $product_id );
 * 
 * ЧТО ОН ДЕЛАЕТ:
 * 1. Находит характеристику по имени или ID
 * 2. Если у характеристики есть варианты (дочерние) — возвращает массив выбранных вариантов
 * 3. Если характеристика простая — возвращает текстовое значение
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ФУНКЦИЯ: получает значение характеристики товара
 * 
 * @param string $typeField  — тип поиска: 'name' (по имени) или 'id' (по ID)
 * @param mixed  $valueField — искомое значение (имя или ID)
 * @param int    $productID  — ID товара
 * 
 * @return array|string|false — массив вариантов, строка значения или false
 */
function as_get_charact( $typeField, $valueField, $productID ) {
    
    // Проверка ID товара
    $productID = absint( $productID );
    
    if ( 0 === $productID ) {
        return false;
    }
    
    // Находим характеристику
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
    
    // Проверяем, есть ли у характеристики дочерние (варианты выбора)
    $termChildren = get_term_children( $termID, 'characteristics' );
    
    if ( is_wp_error( $termChildren ) ) {
        return false;
    }
    
    // Если есть дочерние — это характеристика с вариантами выбора (чекбоксы)
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
        // Простая характеристика (текстовое поле)
        
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