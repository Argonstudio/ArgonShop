<?php
/**
 * Получение значений характеристик товара
 *
 * @package Argon_Shop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Вызывается из шаблонов темы при выводе характеристик товара.
 * Например: get_charact( 'id', 123, $product_id );
 * 
 * ЧТО ДЕЛАЕТ:
 * 1. Находит характеристику по имени или ID
 * 2. Если характеристика имеет варианты (дочерние) — возвращает массив выбранных опций
 * 3. Если характеристика простая — возвращает текстовое значение
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ФУНКЦИЯ: Получение значения характеристики товара
 * 
 * @param string $typeField  — тип поиска: 'name' (по имени) или 'id' (по ID)
 * @param mixed  $valueField — значение для поиска (имя или ID)
 * @param int    $productID  — ID товара
 * 
 * @return array|string|false — Массив опций, строка значения или false
 */
function get_charact( $typeField, $valueField, $productID ) {
    
    // Валидация ID товара
    $productID = absint( $productID );
    
    if ( 0 === $productID ) {
        return false;
    }
    
    // Поиск характеристики
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
    
    // Проверяем, есть ли у характеристики дочерние элементы (варианты выбора)
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