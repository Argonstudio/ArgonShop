<?php
/**
 * Дополнительные поля товара в админ-панели
 *
 * @package Argon_Shop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * WordPress Админка → Товары → Редактирование товара
 * URL: /wp-admin/post.php?post=ID_ТОВАРА&action=edit
 * 
 * ЧТО ДЕЛАЕТ:
 * 1. Добавляет метабокс "Основные параметры" (цена, вес, артикул)
 * 2. Добавляет метабокс "Цена со скидкой" (оптовые цены)
 * 3. Добавляет метабокс "Характеристики" (текстовые и чекбоксы)
 * 4. Сохраняет все поля при обновлении товара
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Удаляем стандартные метабоксы таксономий
add_action( 'admin_menu', 'detailedfields_remove_meta_box' );

function detailedfields_remove_meta_box() {
    
    remove_meta_box( 'tagsdiv-wholesalePrice', 'product', 'normal' );
    remove_meta_box( 'tagsdiv-characteristics', 'product', 'normal' );
}

// Добавляем новые метабоксы
add_action( 'add_meta_boxes', 'detailedfields_add_meta_box' );

function detailedfields_add_meta_box() {
    
    add_meta_box( 'mainParameters_meta_box', 'Основные параметры', 'mainParameters_metabox', 'product', 'normal', 'core' );
    
    if ( check_saleSteps() ) {
        add_meta_box( 'wholesalePrice_meta_box', 'Цена со скидкой', 'wholesalePrice_metabox', 'product', 'normal', 'core' );
    }
    
    add_meta_box( 'characteristics_meta_box', 'Характеристики', 'characteristics_metabox', 'product', 'normal', 'core' );
}

/**
 * МЕТАБОКС: Основные параметры
 * ГДЕ: Страница товара → основной блок
 */
function mainParameters_metabox( $post ) {
    
    wp_nonce_field( basename( __FILE__ ), 'detailed_fields' );
    
    $price = get_post_meta( $post->ID, '_price', true );
    $weight = get_post_meta( $post->ID, '_weight', true );
    $article = get_post_meta( $post->ID, '_article', true );
    $hitSales = get_post_meta( $post->ID, '_hit', true );
    $newProduct = get_post_meta( $post->ID, '_newProduct', true );
    
    // Экранирование
    $price_esc = esc_attr( $price );
    $weight_esc = esc_attr( $weight );
    $article_esc = esc_attr( $article );
    $checkedHit = ( $hitSales == 'on' ) ? "checked='checked'" : '';
    $checkedNew = ( $newProduct == 'on' ) ? "checked='checked'" : '';
    
    ?>
    
    <ul class='advanced-fields'>
        
        <li class="advanced-field"> 
            <div class="span-advanced-fields">Цена</div>
            <input type="text" name="_price" placeholder="Цена товара" value="<?php echo $price_esc; ?>"> ₽/шт
        </li>
        
        <li class="advanced-field"> 
            <div class="span-advanced-fields">Вес</div>
            <input type="text" name="_weight" placeholder="Вес товара" value="<?php echo $weight_esc; ?>"> кг
        </li>
        
        <li class="advanced-field"> 
            <div class="span-advanced-fields">Артикул</div>
            <input type="text" name="_article" placeholder="Артикул" value="<?php echo $article_esc; ?>">
        </li>
        
        <li class="advanced-field"> 
            <input name="_hit" type="checkbox" <?php echo $checkedHit; ?>> <span class="span-advanced-fields">Хит продаж</span>
        </li>
        
        <li class="advanced-field">                  
            <input name="_newProduct" type="checkbox" <?php echo $checkedNew; ?>> <span class="span-advanced-fields">Новинка</span>
        </li>
        
    </ul>
    
    <?php
}

/**
 * ФУНКЦИЯ: Получение родительских категорий товара
 * 
 * @param WP_Post  $post              — объект товара
 * @param array    $activeCategoryId  — массив ID выбранных категорий
 * 
 * @return array Массив ID всех родительских категорий
 */
function get_product_parentterms( $post, $activeCategoryId ) {
    
    $product_parentterms = array();
    $product_terms = array();
    
    if ( ! $activeCategoryId ) {
        
        $product_terms = get_the_terms( $post->ID, 'catalog' );
        
        if ( ! $product_terms || is_wp_error( $product_terms ) ) {
            return $product_parentterms;
        }
        
    } else {
        
        foreach ( $activeCategoryId as $category_id ) {
            
            $category_id = absint( $category_id );
            $term = get_term( $category_id );
            
            if ( $term && ! is_wp_error( $term ) ) {
                array_push( $product_terms, $term );
            }
        }
    }
    
    foreach ( $product_terms as $product_term ) {
        
        if ( ! $product_term || is_wp_error( $product_term ) ) {
            continue;
        }
        
        if ( $product_term->parent == 0 ) {
            
            array_push( $product_parentterms, $product_term->term_id );
            
        } else {
            
            array_push( $product_parentterms, $product_term->term_id );
            $parent_id = $product_term->parent;
            
            while ( $parent_id ) {
                
                $parent_term = get_term_by( 'id', $parent_id, $product_term->taxonomy );
                
                if ( ! $parent_term || is_wp_error( $parent_term ) ) {
                    break;
                }
                
                $parent_id = $parent_term->parent;
            }
            
            if ( $parent_term && ! is_wp_error( $parent_term ) ) {
                array_push( $product_parentterms, $parent_term->term_id );
            }
        }
    }
    
    return $product_parentterms;
}

/**
 * МЕТАБОКС: Цена со скидкой (оптовые цены)
 * ГДЕ: Страница товара → основной блок
 */
function wholesalePrice_metabox( $post ) {
    
    add_filter( 'get_terms_orderby', 'sort_terms_clause', 10, 3 );
    
    $args = array(
        'taxonomy'   => 'wholesalePrice',
        'hide_empty' => false,
    );
    
    $terms = get_terms( $args );
    
    if ( $terms && ! is_wp_error( $terms ) ) {
        
        wp_nonce_field( basename( __FILE__ ), 'detailed_fields' );
        $wholesalePrice = get_post_meta( $post->ID, '_wholesalePrice', true );
        
        echo "<ul class='advanced-fields'>";
        
        foreach ( $terms as $term ) {
            
            $term_id = absint( $term->term_id );
            $term_name = esc_html( $term->name );
            $value = isset( $wholesalePrice[ $term_id ] ) ? esc_attr( $wholesalePrice[ $term_id ] ) : '';
            
            ?>
            
            <li class="advanced-field"> 
                <div class="span-advanced-fields">от <?php echo $term_name; ?></div>
                <input type="text" name="wholesalePrice[<?php echo $term_id; ?>]" placeholder="Цена по скидке" value="<?php echo $value; ?>">
            </li>
            
            <?php
        }
        
        echo '</ul>';
    }
    
    remove_filter( 'get_terms_orderby', 'sort_terms_clause', 10 );
}

/**
 * МЕТАБОКС: Характеристики
 * ГДЕ: Страница товара → основной блок
 */
function characteristics_metabox( $post, $taxonomy, $activeCategoryId = 0, $characteristicsTextField = 0, $characteristicsCheckboxField = 0 ) {
    
    $characteristics_in_stock = false;
    
    if ( $activeCategoryId !== 'no categories' ) {
        
        $product_parentterms = get_product_parentterms( $post, $activeCategoryId );
        
    } else {
        
        echo 'Нет подходящих характеристик</br>Выберите категорию в каталоге';
        return;
    }
    
    $args = array(
        'taxonomy'   => 'characteristics',
        'hide_empty' => false,
    );
    
    $terms = get_terms( $args );
    
    if ( $terms && ! is_wp_error( $terms ) ) {
        
        wp_nonce_field( basename( __FILE__ ), 'detailed_fields' );
        
        if ( ! $characteristicsTextField ) {
            $characteristicsTextField = get_post_meta( $post->ID, '_characteristics_text_field', true );
        }
        
        if ( ! $characteristicsCheckboxField ) {
            $characteristicsCheckboxField = get_post_meta( $post->ID, '_characteristics_checkbox_field', true );
        }
        
        echo "<ul class='advanced-fields'>";
        
        $characterictics_price = array();
        $characterictics_list = array();
        
        // Делим характеристики на два типа
        foreach ( $terms as $term ) {
            
            if ( $term->parent == 0 ) {
                
                $termChildren = get_term_children( $term->term_id, 'characteristics' );
                
                if ( empty( $termChildren ) ) {
                    array_push( $characterictics_price, $term );
                } else {
                    $termList = array( $term, $termChildren );
                    array_push( $characterictics_list, $termList );
                }
            }
        }
        
        /**
         * ФУНКЦИЯ: Получение выбранных категорий для характеристики
         */
        function searchFieldsTerm( $term ) {
            $filds_term = array();
            $filds = get_term_meta( $term->term_id, 'checkCategory', 1 );
            
            if ( $filds && is_array( $filds ) ) {
                
                foreach ( $filds as $key => $value ) {
                    $filds_term[] = absint( $key );
                }
            }
            
            return $filds_term;
        }
        
        // Характеристики с вводом значения
        foreach ( $characterictics_price as $term ) {
            
            $filds_term = searchFieldsTerm( $term );
            
            if ( array_intersect( $product_parentterms, $filds_term ) ) {
                
                if ( $characteristics_in_stock == false ) {
                    $characteristics_in_stock = true;
                }
                
                $term_id = absint( $term->term_id );
                $term_name = esc_html( $term->name );
                $textFieldValue = isset( $characteristicsTextField[ $term_id ] ) ? esc_attr( $characteristicsTextField[ $term_id ] ) : '';
                
                ?>
                
                <li class="advanced-field"> 
                    <div class="span-advanced-fields"><?php echo $term_name; ?></div>
                    <input type="text" name="characteristics_text_field[<?php echo $term_id; ?>]" placeholder="Значение" value="<?php echo $textFieldValue; ?>">
                </li>
                
                <?php
            }
        }
        
        // Характеристики с выбором (чекбоксы)
        foreach ( $characterictics_list as $term ) {
            
            $filds_term = searchFieldsTerm( $term[0] );
            
            if ( array_intersect( $product_parentterms, $filds_term ) ) {
                
                if ( $characteristics_in_stock == false ) {
                    $characteristics_in_stock = true;
                }
                
                $parent_term_id = absint( $term[0]->term_id );
                $parent_term_name = esc_html( $term[0]->name );
                
                ?>
                
                <li class="advanced-field"> 
                    <div class="span-advanced-fields"><?php echo $parent_term_name; ?></div>
                    
                    <?php foreach ( $term[1] as $characterictics_id ) { 
                        
                        $characterictics_id = absint( $characterictics_id );
                        $characterictics_term = get_term( $characterictics_id, 'characteristics' );
                        
                        if ( ! $characterictics_term || is_wp_error( $characterictics_term ) ) {
                            continue;
                        }
                        
                        $filds_term = searchFieldsTerm( $characterictics_term );
                        
                        if ( array_intersect( $product_parentterms, $filds_term ) ) {
                            
                            if ( $characteristics_in_stock == false ) {
                                $characteristics_in_stock = true;
                            }
                            
                            $checked = '';
                            
                            if ( isset( $characteristicsCheckboxField[ $parent_term_id ][ $characterictics_id ] ) ) {
                                $checked = 'checked';
                            }
                            
                            $child_name = esc_html( $characterictics_term->name );
                            
                            ?>
                            
                            <div class="checkbox-advanced-fields" data-parentid="<?php echo $parent_term_id; ?>">
                                <input name="characteristics_checkbox_field[<?php echo $characterictics_id; ?>]" type="checkbox" <?php echo $checked; ?> /> 
                                <?php echo $child_name; ?>
                            </div>
                            
                            <?php
                        }
                        ?>
                        
                    <?php } ?>
                    
                </li>
                
                <?php
            }
        }
        
        echo '</ul>';
        
        if ( $characteristics_in_stock == false ) {
            echo 'Нет подходящих характеристик</br>Выберите категорию в каталоге';
        }
    }
}

/**
 * СОХРАНЕНИЕ: Всех полей товара
 * ГДЕ: Срабатывает при нажатии "Обновить" на странице товара
 */
function save_detailed_fields( $post_id ) {
    
    if ( ! isset( $_POST['detailed_fields'] )
        || ! wp_verify_nonce( $_POST['detailed_fields'], basename( __FILE__ ) ) ) {
        return $post_id;
    }
    
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return $post_id;
    }
    
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return $post_id;
    }
    
    $post = get_post( $post_id );
    
    if ( ! $post || $post->post_type !== 'product' ) {
        return $post_id;
    }
    
    // Сохранение характеристик-чекбоксов
    if ( isset( $_POST['characteristics_checkbox_field'] ) && $_POST['characteristics_checkbox_field'] ) {
        
        $characteristicsCheckbox = array();
        
        foreach ( $_POST['characteristics_checkbox_field'] as $key => $value ) {
            
            $key = absint( $key );
            $characteristic = get_term( $key, 'characteristics' );
            
            if ( $characteristic && ! is_wp_error( $characteristic ) ) {
                $characteristicsCheckbox[ $characteristic->parent ][ $key ] = sanitize_text_field( $value );
            }
        }
        
        update_post_meta( $post_id, '_characteristics_checkbox_field', $characteristicsCheckbox );
        
    } else {
        
        delete_post_meta( $post_id, '_characteristics_checkbox_field' );
    }
    
    // Сохранение оптовых цен
    if ( check_saleSteps() ) {
        
        if ( isset( $_POST['wholesalePrice'] ) && is_array( $_POST['wholesalePrice'] ) ) {
            
            $wholesalePrice = array_map( 'sanitize_text_field', $_POST['wholesalePrice'] );
            $completeWholesalePrice = cheak_detailed_fields( $wholesalePrice );
            
            as_update_meta( $post_id, '_wholesalePrice', $completeWholesalePrice );
        }
    }
    
    // Сохранение цены
    if ( isset( $_POST['_price'] ) ) {
        $price = sanitize_text_field( wp_unslash( $_POST['_price'] ) );
        as_update_meta( $post_id, '_price', $price );
    }
    
    // Сохранение веса
    if ( isset( $_POST['_weight'] ) ) {
        $weight = sanitize_text_field( wp_unslash( $_POST['_weight'] ) );
        as_update_meta( $post_id, '_weight', $weight );
    }
    
    // Сохранение артикула
    if ( isset( $_POST['_article'] ) ) {
        $article = sanitize_text_field( wp_unslash( $_POST['_article'] ) );
        as_update_meta( $post_id, '_article', $article );
    }
    
    // Сохранение "Хит продаж"
    if ( isset( $_POST['_hit'] ) ) {
        $hit = sanitize_text_field( wp_unslash( $_POST['_hit'] ) );
        as_update_meta( $post_id, '_hit', $hit );
    } else {
        as_update_meta( $post_id, '_hit' );
    }
    
    // Сохранение "Новинка"
    if ( isset( $_POST['_newProduct'] ) ) {
        $newProduct = sanitize_text_field( wp_unslash( $_POST['_newProduct'] ) );
        as_update_meta( $post_id, '_newProduct', $newProduct );
    } else {
        as_update_meta( $post_id, '_newProduct' );
    }
    
    // Сохранение текстовых характеристик
    if ( isset( $_POST['characteristics_text_field'] ) && is_array( $_POST['characteristics_text_field'] ) ) {
        
        $characteristicsTextField = array_map( 'sanitize_text_field', $_POST['characteristics_text_field'] );
        $completeCharacteristicsTextField = cheak_detailed_fields( $characteristicsTextField );
        
        as_update_meta( $post_id, '_characteristics_text_field', $completeCharacteristicsTextField );
    }
    
    return $post_id;
}

add_action( 'save_post', 'save_detailed_fields' );

// AJAX обновление характеристик
add_action( 'admin_print_footer_scripts', 'updateCharacteristicsAjax', 99 );

function updateCharacteristicsAjax() {
    ?>
    <script>
        function updateCharacteristics( activeCategoryId, characteristicsTextField, characteristicsCheckboxField ) {
            
            var data = {
                action: 'update_characteristics',
                activeCategoryId: activeCategoryId,
                characteristicsTextField: characteristicsTextField,
                characteristicsCheckboxField: characteristicsCheckboxField,
                nonce: argonShopOrderNonce
            };
            
            jQuery.post( ajaxurl, data, function( response ) {
                jQuery( '#characteristics_meta_box' ).children( '.inside' ).html( response );
            });
        }
    </script>
    <?php
}

add_action( 'wp_ajax_update_characteristics', 'updateCharacteristicsFunction' );

function updateCharacteristicsFunction() {
    
    check_ajax_referer( 'argon_shop_order_nonce', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Недостаточно прав' );
    }
    
    $activeCategoryId = isset( $_POST['activeCategoryId'] ) ? array_map( 'absint', (array) $_POST['activeCategoryId'] ) : 0;
    
    $characteristicsTextField = isset( $_POST['characteristicsTextField'] ) ? $_POST['characteristicsTextField'] : 0;
    $characteristicsCheckboxField = isset( $_POST['characteristicsCheckboxField'] ) ? $_POST['characteristicsCheckboxField'] : 0;
    
    $url = wp_get_referer();
    $postIdUrl = preg_match( '/post=(\d+)/s', $url, $matches ) ? absint( $matches[1] ) : 0;
    
    if ( 0 === $postIdUrl ) {
        wp_die( 'Неверный ID товара' );
    }
    
    characteristics_metabox( get_post( $postIdUrl ), '', $activeCategoryId, $characteristicsTextField, $characteristicsCheckboxField );
    
    wp_die();
}