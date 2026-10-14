<?php
/**
 * Additional fields of the "Characteristics" taxonomy
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * WHERE TO TEST:
 * WordPress Admin → Characteristics → Add/Edit characteristic
 * URL: /wp-admin/term.php?taxonomy=characteristics
 * 
 * FIXES:
 * PHP 8.5:
 * - is_object() / is_a() checks for WP_Term
 * - explicit type casting
 * - protection against null when accessing properties
 * - strict comparisons (===) in critical places
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Fields when adding a taxonomy term
add_action( 'characteristics_add_form_fields', 'as_add_new_custom_fields' );
// Fields when editing a taxonomy term
add_action( 'characteristics_edit_form_fields', 'as_edit_new_custom_fields' );

// Save when adding a taxonomy term
add_action( 'create_characteristics', 'as_save_custom_taxonomy_meta' );
// Save when editing a taxonomy term
add_action( 'edited_characteristics', 'as_save_custom_taxonomy_meta' );

/**
 * FUNCTION: Get catalog terms
 * 
 * @param int $parent — parent term ID (0 for top-level terms)
 * 
 * @return array List of terms
 */
function as_get_terms_catalog( $parent ) {
    
    $parent = absint( $parent );
    
    $args = array(
        'taxonomy'   => 'catalog',
        'hide_empty' => false,
        'parent'     => $parent,
    );
    
    $terms = get_terms( $args );
    
    // PHP 8.5: get_terms may return WP_Error or an array
    if ( is_wp_error( $terms ) || ! is_array( $terms ) ) {
        return array();
    }
    
    // Drop everything that is not a term
    $terms = array_filter( $terms, function( $term ) {
        return is_object( $term ) && isset( $term->term_id );
    } );
    
    return array_values( $terms );
}

/**
 * FUNCTION: Check whether all categories are checked
 * 
 * @param array $terms         — list of terms
 * @param array $checkCategory — saved IDs
 * 
 * @return bool true if all are checked
 */
function as_are_all_categories_checked( $terms, $checkCategory ) {
    
    if ( ! is_array( $terms ) || empty( $terms ) ) {
        return true;
    }
    
    if ( ! is_array( $checkCategory ) ) {
        return false;
    }
    
    foreach ( $terms as $term ) {
        
        // PHP 8.5: check the object before accessing a property
        if ( ! is_object( $term ) || ! isset( $term->term_id ) ) {
            continue;
        }
        
        $term_id = absint( $term->term_id );
        
        if ( $term_id === 0 ) {
            continue;
        }
        
        // If a specific ID is not checked — not all are checked
        if ( empty( $checkCategory[ $term_id ] ) ) {
            return false;
        }
        
        // Recursively check the children
        $childs = as_get_terms_catalog( $term_id );
        
        if ( ! empty( $childs ) && ! as_are_all_categories_checked( $childs, $checkCategory ) ) {
            return false;
        }
    }
    
    return true;
}

/**
 * FUNCTION: Recursive output of the category tree with checkboxes
 * 
 * @param array $terms         — list of terms
 * @param int   $level         — nesting level
 * @param array $checkCategory — selected categories from the database
 * @param bool  $forceChecked  — if true, all checkboxes are forcibly checked
 * @param bool  $checkAll      — state of the "Select all" checkbox
 */
function as_view_terms( $terms, $level, $checkCategory = array(), $forceChecked = false, $checkAll = false ) {
    
    // PHP 8.5: type normalization
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
                <b><?php esc_html_e( 'Select all / Deselect', 'argon-shop' ); ?></b>
            </li>
        <?php } ?>
        
        <?php foreach ( $terms as $term ) { 
            
            // PHP 8.5: check the object before accessing properties
            if ( ! is_object( $term ) || ! isset( $term->term_id ) ) {
                continue;
            }
            
            $term_id = absint( $term->term_id );
            
            if ( $term_id === 0 ) {
                continue;
            }
            
            $term_name = isset( $term->name ) ? esc_html( $term->name ) : '';
            
            // Forced check (new characteristic)
            // Otherwise — by the specific ID from the database
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
                // Get the child terms
                $childs_term = as_get_terms_catalog( $term_id );
                
                if ( ! empty( $childs_term ) ) {
                    ?>
                    <input type="button" class="showHideButton" value="">
                    <?php
                    // Recursively output the children
                    as_view_terms( $childs_term, $level + 1, $checkCategory, $forceChecked, $checkAll );
                }
                ?>
            </li>
            
        <?php } ?>
        
    </ul>
    
    <?php
}

/**
 * FUNCTION: Fields when editing a characteristic
 * 
 * Logic:
 * - If the meta is missing (new characteristic) → all checked, "Select all" on
 * - Otherwise → shown from the database, "Select all" is computed dynamically
 * 
 * @param WP_Term $term — characteristic term object
 */
function as_edit_new_custom_fields( $term ) {
    
    // PHP 8.5: make sure a term object was passed
    if ( ! is_object( $term ) || ! isset( $term->term_id ) ) {
        return;
    }
    
    $term_id = absint( $term->term_id );
    
    if ( $term_id === 0 ) {
        return;
    }
    
    $checkCategory = get_term_meta( $term_id, 'checkCategory', true );
    
    // Determine whether this is a new characteristic:
    // the meta has never been saved → get_term_meta() returns '' or false
    // PHP 8.5: explicit check of all variants
    $is_new = (
        $checkCategory === ''
        || $checkCategory === false
        || $checkCategory === null
    );
    
    if ( ! is_array( $checkCategory ) ) {
        $checkCategory = array();
    }
    
    // PHP 8.5: drop non-numeric keys
    $cleanCategory = array();
    
    foreach ( $checkCategory as $key => $value ) {
        $key_clean = absint( $key );
        
        if ( $key_clean > 0 && ! empty( $value ) ) {
            $cleanCategory[ $key_clean ] = 'on';
        }
    }
    
    $checkCategory = $cleanCategory;
    
    $terms = as_get_terms_catalog( 0 );
    
    // Determine the state of the "Select all" checkbox
    if ( $is_new ) {
        // New characteristic — everything is selected by default
        $force_checked = true;
        $check_all = true;
    } else {
        // Saved one — check whether all are checked
        $force_checked = false;
        $check_all = as_are_all_categories_checked( $terms, $checkCategory );
    }
    
    ?>
    
    <tr class="form-field">
        <th scope="row" valign="top"><label><?php esc_html_e( 'Belongs to categories:', 'argon-shop' ); ?></label></th>
        <td>
            <?php
            as_view_terms( $terms, 0, $checkCategory, $force_checked, $check_all );
            ?>
        </td>
    </tr>
    
    <?php
}

/**
 * FUNCTION: Fields when adding a new characteristic
 * 
 * All checkboxes are forcibly checked, the database is not read
 * 
 * @param string $taxonomy_slug — taxonomy slug
 */
function as_add_new_custom_fields( $taxonomy_slug ) {
    
    $terms = as_get_terms_catalog( 0 );
    ?>
    
    <div class="form-field">
        <label><?php esc_html_e( 'Belongs to categories:', 'argon-shop' ); ?></label>
        <?php
        // forceChecked = true, checkAll = true
        as_view_terms( $terms, 0, array(), true, true );
        ?>
    </div>
    
    <?php
}

/**
 * SAVE: Selected categories of a characteristic
 * 
 * Only specific category IDs are saved.
 * The "all" key is not saved — it is computed on display.
 * 
 * @param int $term_id — ID of the characteristic being saved
 * 
 * @return int|void
 */
function as_save_custom_taxonomy_meta( $term_id ) {
    
    $term_id = absint( $term_id );
    
    if ( $term_id === 0 ) {
        return;
    }
    
    // Capability check
    if ( ! current_user_can( 'edit_term', $term_id ) ) {
        return;
    }
    
    // PHP 8.5: nonce check for editing
    $nonce_update = false;
    
    if ( isset( $_POST['_wpnonce'] ) && is_string( $_POST['_wpnonce'] ) ) {
        $nonce_update = wp_verify_nonce( $_POST['_wpnonce'], "update-tag_$term_id" );
    }
    
    // PHP 8.5: nonce check for adding
    $nonce_add = false;
    
    if ( isset( $_POST['_wpnonce_add-tag'] ) && is_string( $_POST['_wpnonce_add-tag'] ) ) {
        $nonce_add = wp_verify_nonce( $_POST['_wpnonce_add-tag'], 'add-tag' );
    }
    
    // If no nonce matched — exit
    if ( ! $nonce_update && ! $nonce_add ) {
        return;
    }
    
    // If no checkboxes were sent — save an empty array
    if ( ! isset( $_POST['checkCategory'] ) || ! is_array( $_POST['checkCategory'] ) ) {
        update_term_meta( $term_id, 'checkCategory', array() );
        return $term_id;
    }
    
    // Get and clean the checkboxes
    $checkCategory = wp_unslash( $_POST['checkCategory'] );
    
    if ( ! is_array( $checkCategory ) ) {
        $checkCategory = array();
    }
    
    // Validate and save
    $cleanCheckCategory = array();
    
    foreach ( $checkCategory as $key => $val ) {
        
        // The "all" key is not saved — it is a UI element
        if ( $key === 'all' ) {
            continue;
        }
        
        // The key must be a number (category ID)
        $key_clean = absint( $key );
        
        if ( $key_clean > 0 ) {
            // Save only the checked categories
            if ( ! empty( $val ) ) {
                $cleanCheckCategory[ $key_clean ] = 'on';
            }
        }
    }
    
    // Save the cleaned array
    update_term_meta( $term_id, 'checkCategory', $cleanCheckCategory );
    
    return $term_id;
}