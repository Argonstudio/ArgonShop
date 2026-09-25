<?php
/**
 * Получение терминов каталога и формирование меню
 *
 * @package Argon_Shop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * 1. Меню каталога на главной
 * 2. Выпадающее мега-меню
 * 
 * ЧТО ДЕЛАЕТ:
 * 1. Получает термины каталога с учётом иерархии
 * 2. Формирует структуру меню с дочерними элементами
 * 3. Разбивает меню на столбцы
 * 4. Выводит HTML меню
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ФУНКЦИЯ: Получение терминов каталога (для меню) с доп. данными
 * 
 * @return array Иерархический массив меню
 */
function get_catalog_terms() {
    
    $catalogArgs = array(
        'hierarchical' => 1,
        'taxonomy'     => 'catalog',
        'hide_empty'   => 0,
    );
    
    $catalogTerms = get_categories( $catalogArgs );
    
    if ( is_wp_error( $catalogTerms ) ) {
        return array();
    }
    
    $menuCatalog = array();
    
    foreach ( $catalogTerms as $key => $value ) {
        
        $term_id = absint( $value->term_id );
        
        // Пропускаем элементы-запросы
        if ( get_option( 'catalog_' . $term_id . '_catalog_type' ) === 'request' ) {
            continue;
        }
        
        $menuCatalog[ $term_id ] = array(
            'data' => array(
                'name'             => esc_html( $value->name ),
                'slug'             => esc_attr( $value->slug ),
                'term_id'          => $term_id,
                'parent'           => absint( $value->parent ),
                'class'            => 'menuCatalog-' . esc_attr( $value->slug . $term_id ),
                'firstLevelChild'  => 0,
                'secondLevelChild' => 0,
            ),
        );
    }
    
    // Добавляем дочерние элементы к родителям
    foreach ( $menuCatalog as $key => $value ) {
        
        if ( $value['data']['parent'] !== 0 ) {
            
            $parentID = absint( $value['data']['parent'] );
            
            if ( isset( $menuCatalog[ $parentID ] ) ) {
                $menuCatalog[ $parentID ]['children'][ $key ] = $value;
                $menuCatalog[ $parentID ]['data']['firstLevelChild'] += 1;
            }
        }
    }
    
    // Рекурсивно переносим дочерние элементы
    $settingLevelsMenu = function( &$element ) use ( &$settingLevelsMenu, &$menuCatalog ) {
        
        if ( ! isset( $element['children'] ) || ! is_array( $element['children'] ) ) {
            return;
        }
        
        foreach ( $element['children'] as $keyChild => $valueChild ) {
            
            if ( isset( $menuCatalog[ $keyChild ] ) ) {
                
                $element['children'][ $keyChild ] = $menuCatalog[ $keyChild ];
                $element['data']['secondLevelChild'] += $menuCatalog[ $keyChild ]['data']['firstLevelChild'];
                
                unset( $menuCatalog[ $keyChild ] );
                
                if ( isset( $element['children'][ $keyChild ]['children'] ) && $element['children'][ $keyChild ]['children'] ) {
                    $settingLevelsMenu( $element['children'][ $keyChild ] );
                }
            }
        }
    };
    
    foreach ( $menuCatalog as $key => &$value ) {
        
        if ( isset( $value['children'] ) && $value['children'] ) {
            $settingLevelsMenu( $menuCatalog[ $key ] );
        }
    }
    
    unset( $value );
    
    return $menuCatalog;
}

/**
 * ФУНКЦИЯ: Получение терминов каталога (объекты WP_Term)
 * 
 * @return array Иерархический массив меню
 */
function get_catalog_termsWP() {
    
    $catalogArgs = array(
        'hierarchical' => 1,
        'taxonomy'     => 'catalog',
        'hide_empty'   => 0,
    );
    
    $catalogTerms = get_categories( $catalogArgs );
    
    if ( is_wp_error( $catalogTerms ) ) {
        return array();
    }
    
    $menuCatalog = array();
    
    foreach ( $catalogTerms as $key => $value ) {
        
        $term_id = absint( $value->term_id );
        
        if ( get_option( 'catalog_' . $term_id . '_catalog_type' ) === 'request' ) {
            continue;
        }
        
        $menuCatalog[ $term_id ] = array(
            'data' => $value,
        );
    }
    
    foreach ( $menuCatalog as $key => $value ) {
        
        if ( $value['data']->parent !== 0 ) {
            
            $parentID = absint( $value['data']->parent );
            
            if ( isset( $menuCatalog[ $parentID ] ) ) {
                $menuCatalog[ $parentID ]['children'][ $key ] = $value;
            }
        }
    }
    
    $settingLevelsMenu = function( &$element ) use ( &$settingLevelsMenu, &$menuCatalog ) {
        
        if ( ! isset( $element['children'] ) || ! is_array( $element['children'] ) ) {
            return;
        }
        
        foreach ( $element['children'] as $keyChild => $valueChild ) {
            
            if ( isset( $menuCatalog[ $keyChild ] ) ) {
                
                $element['children'][ $keyChild ] = $menuCatalog[ $keyChild ];
                
                unset( $menuCatalog[ $keyChild ] );
                
                if ( isset( $element['children'][ $keyChild ]['children'] ) && $element['children'][ $keyChild ]['children'] ) {
                    $settingLevelsMenu( $element['children'][ $keyChild ] );
                }
            }
        }
    };
    
    foreach ( $menuCatalog as $key => &$value ) {
        
        if ( isset( $value['children'] ) && $value['children'] ) {
            $settingLevelsMenu( $menuCatalog[ $key ] );
        }
    }
    
    unset( $value );
    
    return $menuCatalog;
}

/**
 * ФУНКЦИЯ: Разбивка меню на столбцы
 * 
 * @param array $menuElements             — элементы меню
 * @param int   $countResult              — на сколько столбцов разбить
 * @param int   $catalogFirstLevelChild   — количество заголовков
 * @param int   $catalogSecondLevelChild  — количество пунктов
 * @param int   $heightFirstRelativeSecond— соотношение высоты заголовка к пункту
 * @param int   $adjustment               — корректировка
 * @param int   $additionalElementLength  — длина доп. элемента
 * 
 * @return array [результат, счётчики]
 */
function splitMenu( $menuElements, $countResult, $catalogFirstLevelChild, $catalogSecondLevelChild, $heightFirstRelativeSecond = 1, $adjustment = 5, $additionalElementLength = 0 ) {
    
    $catalogFirstLevelChild = absint( $catalogFirstLevelChild );
    $catalogSecondLevelChild = absint( $catalogSecondLevelChild );
    $countResult = absint( $countResult );
    $heightFirstRelativeSecond = absint( $heightFirstRelativeSecond );
    $adjustment = absint( $adjustment );
    $additionalElementLength = absint( $additionalElementLength );
    
    if ( $countResult < 1 ) {
        $countResult = 1;
    }
    
    $sumElements = $catalogFirstLevelChild * $heightFirstRelativeSecond + $catalogSecondLevelChild;
    
    if ( $additionalElementLength !== 0 ) {
        $sumElements += $additionalElementLength;
    }
    
    $childsOneResult = round( $sumElements / $countResult );
    
    $result = array();
    $resultCountsList = array();
    
    for ( $i = 0; $i < $countResult; $i++ ) {
        $result[ $i ] = array();
        $resultCountsList[ $i ] = 0;
    }
    
    foreach ( $result as $key => $value ) {
        
        $keyResult = 0;
        $numberElement = 0;
        
        foreach ( $menuElements as $menuElementKey => $menuElementValue ) {
            
            $firstLevelChild = isset( $menuElements[ $menuElementKey ]['data']['firstLevelChild'] ) ? absint( $menuElements[ $menuElementKey ]['data']['firstLevelChild'] ) : 0;
            
            $add = ( $numberElement === 0 || round( $childsOneResult + $childsOneResult / $adjustment ) >= $keyResult + 1 * $heightFirstRelativeSecond + $firstLevelChild ) ? true : false;
            
            if ( $add ) {
                
                $result[ $key ][ $menuElementKey ] = $menuElements[ $menuElementKey ];
                $resultCountsList[ $key ] += 1 * $heightFirstRelativeSecond + $firstLevelChild;
                
                $keyResult += 1 * $heightFirstRelativeSecond + $firstLevelChild;
                $numberElement++;
                
                unset( $menuElements[ $menuElementKey ] );
            }
        }
    }
    
    if ( ! empty( $menuElements ) ) {
        
        $numberResult = 0;
        
        foreach ( $menuElements as $menuElementKey => $menuElementValue ) {
            
            $result[ $numberResult ][ $menuElementKey ] = $menuElements[ $menuElementKey ];
            
            if ( $numberResult < $countResult - 1 ) {
                $numberResult++;
            } else {
                $numberResult = 0;
            }
        }
    }
    
    if ( $additionalElementLength ) {
        
        $mostList = array_keys( $resultCountsList, max( $resultCountsList ) );
        $leastList = array_keys( $resultCountsList, min( $resultCountsList ) )[0];
        
        if ( in_array( $countResult - 1, $mostList, true ) ) {
            
            $minValue = null;
            $minValueID = null;
            $numberIteration = 0;
            
            foreach ( $result[ $countResult - 1 ] as $key => $value ) {
                
                $firstLevelChild = isset( $value['data']['firstLevelChild'] ) ? absint( $value['data']['firstLevelChild'] ) : 0;
                
                if ( $numberIteration === 0 ) {
                    $minValue = $firstLevelChild;
                    $minValueID = $key;
                    $numberIteration++;
                    continue;
                }
                
                if ( $minValue > $firstLevelChild ) {
                    $minValue = $firstLevelChild;
                    $minValueID = $key;
                }
                
                $numberIteration++;
            }
            
            if ( $minValueID !== null ) {
                
                $lastListValue = $resultCountsList[ $countResult - 1 ];
                $leastListValue = $resultCountsList[ $leastList ];
                
                if ( ( $leastListValue + $minValue ) < ( $lastListValue - $minValue + $additionalElementLength ) * 1.5 ) {
                    
                    $result[ $leastList ][ $minValueID ] = $result[ $countResult - 1 ][ $minValueID ];
                    
                    $resultCountsList[ $leastList ] += 1 * $heightFirstRelativeSecond + $result[ $countResult - 1 ][ $minValueID ]['data']['firstLevelChild'];
                    $resultCountsList[ $countResult - 1 ] -= 1 * $heightFirstRelativeSecond + $result[ $countResult - 1 ][ $minValueID ]['data']['firstLevelChild'];
                    
                    unset( $result[ $countResult - 1 ][ $minValueID ] );
                }
            }
        }
    }
    
    $result = array( $result, $resultCountsList );
    
    return $result;
}

/**
 * ФУНКЦИЯ: Рекурсивный вывод меню
 * 
 * @param array  $menuElements — элементы меню
 * @param string $classUL      — CSS-класс для <ul>
 * @param int    $howManylevels— сколько уровней выводить
 */
function view_menu_elements( $menuElements, $classUL, $howManylevels ) {
    
    $howManylevels = absint( $howManylevels );
    
    $classUL_esc = $classUL ? esc_attr( $classUL ) : '';
    
    echo ( $classUL_esc ) ? "<ul class='{$classUL_esc}'>" : '<ul>';
    
    foreach ( $menuElements as $key => $value ) {
        
        $class = '';
        
        if ( isset( $value['children'] ) && $value['children'] ) {
            $class = 'itemHasChildren';
        }
        
        if ( isset( $value['data']['class'] ) && $value['data']['class'] ) {
            
            $data_class = esc_attr( $value['data']['class'] );
            
            if ( ! $class ) {
                $class = $data_class;
            } else {
                $class .= ' ' . $data_class;
            }
        }
        
        $class_esc = $class ? esc_attr( $class ) : '';
        
        ?>
        
        <li class="<?php echo $class_esc; ?>" style="list-style-type:none">
        
        <?php
        
        $url = '';
        
        if ( isset( $value['data']['term_id'] ) && $value['data']['term_id'] ) {
            $url = get_category_link( absint( $value['data']['term_id'] ) );
        } elseif ( isset( $value['data']['post_id'] ) && $value['data']['post_id'] ) {
            $url = get_permalink( absint( $value['data']['post_id'] ) );
        }
        
        $url_esc = esc_url( $url );
        $name = isset( $value['data']['name'] ) ? esc_html( $value['data']['name'] ) : '';
        
        ?>
        
        <a href="<?php echo $url_esc; ?>"><span><?php echo $name; ?></span></a>
        
        <?php
        
        if ( isset( $value['children'] ) && $value['children'] && $howManylevels > 1 ) {
            view_menu_elements( $value['children'], '', $howManylevels - 1 );
        }
        
        echo '</li>';
    }
    
    echo '</ul>';
}