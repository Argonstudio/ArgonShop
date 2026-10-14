<?php
/**
 * Getting catalog terms and building the menu
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * WHERE TO TEST:
 * 1. Catalog menu on the home page
 * 2. Dropdown mega menu
 * 
 * WHAT IT DOES:
 * 1. Gets catalog terms respecting the hierarchy
 * 2. Builds the menu structure with child items
 * 3. Splits the menu into columns
 * 4. Outputs the menu HTML
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FUNCTION: Get catalog terms (for the menu) with extra data
 * 
 * @return array Hierarchical menu array
 */
function as_get_catalog_terms() {
    
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
        
        // Skip query items
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
    
    // Attach child items to their parents
    foreach ( $menuCatalog as $key => $value ) {
        
        if ( $value['data']['parent'] !== 0 ) {
            
            $parentID = absint( $value['data']['parent'] );
            
            if ( isset( $menuCatalog[ $parentID ] ) ) {
                $menuCatalog[ $parentID ]['children'][ $key ] = $value;
                $menuCatalog[ $parentID ]['data']['firstLevelChild'] += 1;
            }
        }
    }
    
    // Recursively move child items
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
 * FUNCTION: Get catalog terms (WP_Term objects)
 * 
 * @return array Hierarchical menu array
 */
function as_get_catalog_termsWP() {
    
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
 * FUNCTION: Split the menu into columns
 * 
 * @param array $menuElements             — menu items
 * @param int   $countResult              — number of columns
 * @param int   $catalogFirstLevelChild   — number of headings
 * @param int   $catalogSecondLevelChild  — number of items
 * @param int   $heightFirstRelativeSecond— heading-to-item height ratio
 * @param int   $adjustment               — adjustment
 * @param int   $additionalElementLength  — length of the extra item
 * 
 * @return array [result, counters]
 */
function as_splitMenu( $menuElements, $countResult, $catalogFirstLevelChild, $catalogSecondLevelChild, $heightFirstRelativeSecond = 1, $adjustment = 5, $additionalElementLength = 0 ) {
    
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
 * FUNCTION: Recursive menu output
 * 
 * @param array  $menuElements — menu items
 * @param string $classUL      — CSS class for <ul>
 * @param int    $howManylevels— how many levels to output
 */
function as_view_menu_elements( $menuElements, $classUL, $howManylevels ) {
    
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
            as_view_menu_elements( $value['children'], '', $howManylevels - 1 );
        }
        
        echo '</li>';
    }
    
    echo '</ul>';
}