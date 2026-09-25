<?php
/**
 * Дополнительные поля таксономии "Характеристики"
 *
 * @package Argon_Shop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * WordPress Админка → Характеристики → Добавить/Редактировать характеристику
 * URL: /wp-admin/term.php?taxonomy=characteristics
 * 
 * ИСПРАВЛЕНИЯ:
 * PHP 8.5:
 * - проверки is_object() / is_a() для WP_Term
 * - явное приведение типов
 * - защита от null при обращении к свойствам
 * - строгие сравнения (===) в критичных местах
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Поля при добавлении элемента таксономии
add_action( 'characteristics_add_form_fields', 'add_new_custom_fields' );
// Поля при редактировании элемента таксономии
add_action( 'characteristics_edit_form_fields', 'edit_new_custom_fields' );

// Сохранение при добавлении элемента таксономии
add_action( 'create_characteristics', 'save_custom_taxonomy_meta' );
// Сохранение при редактировании элемента таксономии
add_action( 'edited_characteristics', 'save_custom_taxonomy_meta' );

/**
 * ФУНКЦИЯ: Получение терминов каталога
 * 
 * @param int $parent — ID родительского термина (0 для корневых)
 * 
 * @return array Список терминов
 */
function get_terms_catalog( $parent ) {
    
    $parent = absint( $parent );
    
    $args = array(
        'taxonomy'   => 'catalog',
        'hide_empty' => false,
        'parent'     => $parent,
    );
    
    $terms = get_terms( $args );
    
    // PHP 8.5: get_terms может вернуть WP_Error или массив
    if ( is_wp_error( $terms ) || ! is_array( $terms ) ) {
        return array();
    }
    
    // Убираем всё, что не является терминами
    $terms = array_filter( $terms, function( $term ) {
        return is_object( $term ) && isset( $term->term_id );
    } );
    
    return array_values( $terms );
}

/**
 * ФУНКЦИЯ: Проверка, все ли категории отмечены
 * 
 * @param array $terms         — список терминов
 * @param array $checkCategory — сохранённые ID
 * 
 * @return bool true, если все отмечены
 */
function are_all_categories_checked( $terms, $checkCategory ) {
    
    if ( ! is_array( $terms ) || empty( $terms ) ) {
        return true;
    }
    
    if ( ! is_array( $checkCategory ) ) {
        return false;
    }
    
    foreach ( $terms as $term ) {
        
        // PHP 8.5: проверяем объект перед обращением к свойству
        if ( ! is_object( $term ) || ! isset( $term->term_id ) ) {
            continue;
        }
        
        $term_id = absint( $term->term_id );
        
        if ( $term_id === 0 ) {
            continue;
        }
        
        // Если конкретный ID не отмечен — уже не все
        if ( empty( $checkCategory[ $term_id ] ) ) {
            return false;
        }
        
        // Рекурсивно проверяем дочерние
        $childs = get_terms_catalog( $term_id );
        
        if ( ! empty( $childs ) && ! are_all_categories_checked( $childs, $checkCategory ) ) {
            return false;
        }
    }
    
    return true;
}

/**
 * ФУНКЦИЯ: Рекурсивный вывод дерева категорий с чекбоксами
 * 
 * @param array $terms         — список терминов
 * @param int   $level         — уровень вложенности
 * @param array $checkCategory — выбранные категории из базы
 * @param bool  $forceChecked  — если true, все чекбоксы отмечены принудительно
 * @param bool  $checkAll      — состояние чекбокса "Выбрать все"
 */
function view_terms( $terms, $level, $checkCategory = array(), $forceChecked = false, $checkAll = false ) {
    
    // PHP 8.5: нормализация типов
    $level = absint( $level );
    $forceChecked = (bool) $forceChecked;
    $checkAll = (bool) $checkAll;
    
    if ( ! is_array( $terms ) ) {
        return;
    }
    
    if ( ! is_array( $checkCategory ) ) {
        $checkCategory = array();
    }
    
    ?>
    
    <ul class="choiceCategory<?php if ( $level !== 0 ) { echo 'Child'; } ?>">
        
        <?php if ( $level === 0 ) { ?>
            <li class="checkAllCategory">
                <input name="checkCategory[all]" type="checkbox" <?php echo $checkAll ? 'checked' : ''; ?> />
                <b>Выбрать все / Снять выбор</b>
            </li>
        <?php } ?>
        
        <?php foreach ( $terms as $term ) { 
            
            // PHP 8.5: проверяем объект перед обращением к свойствам
            if ( ! is_object( $term ) || ! isset( $term->term_id ) ) {
                continue;
            }
            
            $term_id = absint( $term->term_id );
            
            if ( $term_id === 0 ) {
                continue;
            }
            
            $term_name = isset( $term->name ) ? esc_html( $term->name ) : '';
            
            // Принудительная отметка (новая характеристика)
            // Иначе — по конкретному ID из базы
            if ( $forceChecked ) {
                $checked = 'checked';
            } else {
                $checked = ! empty( $checkCategory[ $term_id ] ) ? 'checked' : '';
            }
            ?>
            
            <li class="checkCategory">
                <input name="checkCategory[<?php echo $term_id; ?>]" type="checkbox" <?php echo $checked; ?> />
                <?php echo $term_name; ?>
                
                <?php
                // Получаем дочерние термины
                $childs_term = get_terms_catalog( $term_id );
                
                if ( ! empty( $childs_term ) ) {
                    ?>
                    <input type="button" class="showHideButton" value="">
                    <?php
                    // Рекурсивно выводим дочерние
                    view_terms( $childs_term, $level + 1, $checkCategory, $forceChecked, $checkAll );
                }
                ?>
            </li>
            
        <?php } ?>
        
    </ul>
    
    <?php
}

/**
 * ФУНКЦИЯ: Поля при редактировании характеристики
 * 
 * Логика:
 * - Если мета отсутствует (новая характеристика) → все отмечены, "Выбрать все" включён
 * - Иначе → отображаем по базе, "Выбрать все" — динамически
 * 
 * @param WP_Term $term — объект термина характеристики
 */
function edit_new_custom_fields( $term ) {
    
    // PHP 8.5: проверяем, что передан объект термина
    if ( ! is_object( $term ) || ! isset( $term->term_id ) ) {
        return;
    }
    
    $term_id = absint( $term->term_id );
    
    if ( $term_id === 0 ) {
        return;
    }
    
    $checkCategory = get_term_meta( $term_id, 'checkCategory', true );
    
    // Определяем, новая ли это характеристика:
    // мета ещё ни разу не сохранялась → get_term_meta() вернёт '' или false
    // PHP 8.5: явная проверка всех вариантов
    $is_new = (
        $checkCategory === ''
        || $checkCategory === false
        || $checkCategory === null
    );
    
    if ( ! is_array( $checkCategory ) ) {
        $checkCategory = array();
    }
    
    // PHP 8.5: убираем нечисловые ключи
    $cleanCategory = array();
    
    foreach ( $checkCategory as $key => $value ) {
        $key_clean = absint( $key );
        
        if ( $key_clean > 0 && ! empty( $value ) ) {
            $cleanCategory[ $key_clean ] = 'on';
        }
    }
    
    $checkCategory = $cleanCategory;
    
    $terms = get_terms_catalog( 0 );
    
    // Определяем состояние чекбокса "Выбрать все"
    if ( $is_new ) {
        // Новая характеристика — по умолчанию все выбраны
        $force_checked = true;
        $check_all = true;
    } else {
        // Сохранённая — считаем, все ли отмечены
        $force_checked = false;
        $check_all = are_all_categories_checked( $terms, $checkCategory );
    }
    
    ?>
    
    <tr class="form-field">
        <th scope="row" valign="top"><label>Относится к категориям:</label></th>
        <td>
            <?php
            view_terms( $terms, 0, $checkCategory, $force_checked, $check_all );
            ?>
        </td>
    </tr>
    
    <?php
}

/**
 * ФУНКЦИЯ: Поля при добавлении новой характеристики
 * 
 * Все чекбоксы отмечены принудительно, база не читается
 * 
 * @param string $taxonomy_slug — слаг таксономии
 */
function add_new_custom_fields( $taxonomy_slug ) {
    
    $terms = get_terms_catalog( 0 );
    ?>
    
    <div class="form-field">
        <label>Относится к категориям:</label>
        <?php
        // forceChecked = true, checkAll = true
        view_terms( $terms, 0, array(), true, true );
        ?>
    </div>
    
    <?php
}

/**
 * СОХРАНЕНИЕ: Выбранных категорий характеристики
 * 
 * Сохраняются только конкретные ID категорий.
 * Ключ "all" не сохраняется — он вычисляется при отображении.
 * 
 * @param int $term_id — ID сохраняемой характеристики
 * 
 * @return int|void
 */
function save_custom_taxonomy_meta( $term_id ) {
    
    $term_id = absint( $term_id );
    
    if ( $term_id === 0 ) {
        return;
    }
    
    // Проверка прав
    if ( ! current_user_can( 'edit_term', $term_id ) ) {
        return;
    }
    
    // PHP 8.5: проверка nonce для редактирования
    $nonce_update = false;
    
    if ( isset( $_POST['_wpnonce'] ) && is_string( $_POST['_wpnonce'] ) ) {
        $nonce_update = wp_verify_nonce( $_POST['_wpnonce'], "update-tag_$term_id" );
    }
    
    // PHP 8.5: проверка nonce для добавления
    $nonce_add = false;
    
    if ( isset( $_POST['_wpnonce_add-tag'] ) && is_string( $_POST['_wpnonce_add-tag'] ) ) {
        $nonce_add = wp_verify_nonce( $_POST['_wpnonce_add-tag'], 'add-tag' );
    }
    
    // Если ни один nonce не подошёл — выходим
    if ( ! $nonce_update && ! $nonce_add ) {
        return;
    }
    
    // Если чекбоксы не переданы — сохраняем пустой массив
    if ( ! isset( $_POST['checkCategory'] ) || ! is_array( $_POST['checkCategory'] ) ) {
        update_term_meta( $term_id, 'checkCategory', array() );
        return $term_id;
    }
    
    // Получаем и очищаем чекбоксы
    $checkCategory = wp_unslash( $_POST['checkCategory'] );
    
    if ( ! is_array( $checkCategory ) ) {
        $checkCategory = array();
    }
    
    // Валидация и сохранение
    $cleanCheckCategory = array();
    
    foreach ( $checkCategory as $key => $val ) {
        
        // Ключ "all" не сохраняем — это UI-элемент
        if ( $key === 'all' ) {
            continue;
        }
        
        // Ключ должен быть числом (ID категории)
        $key_clean = absint( $key );
        
        if ( $key_clean > 0 ) {
            // Сохраняем только отмеченные категории
            if ( ! empty( $val ) ) {
                $cleanCheckCategory[ $key_clean ] = 'on';
            }
        }
    }
    
    // Сохраняем очищенный массив
    update_term_meta( $term_id, 'checkCategory', $cleanCheckCategory );
    
    return $term_id;
}