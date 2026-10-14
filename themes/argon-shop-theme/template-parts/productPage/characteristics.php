<?php
/**
 * Product characteristics block (template part)
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * WHERE IT IS USED:
 * Included via get_template_part( 'template-parts/productPage/characteristics' )
 * from the single-product.php template (product page).
 * 
 * WHAT IT OUTPUTS:
 * A table with the product characteristics:
 * 1. Product weight (_weight meta field)
 * 2. Text characteristics (_characteristics_text_field meta field)
 * 3. Checkbox characteristics (_characteristics_checkbox_field meta field)
 * 
 * If there are no characteristics, it outputs "No characteristics" (translated).
 * 
 * ============================================================
 * INTERACTION WITH THE ARGON SHOP PLUGIN
 * ============================================================
 * 
 * PRODUCT META FIELDS (created in fieldsProduct.php):
 * 
 * _weight
 *   Product weight (a number, a string with a comma or a dot).
 *   Edited in the "Main parameters" meta box.
 * 
 * _characteristics_text_field
 *   Associative array:
 *     [ term_id => 'value' ]
 *   Created in the "Characteristics" meta box for characteristics
 *   without children (text input).
 * 
 * _characteristics_checkbox_field
 *   Two-level associative array:
 *     [ parent_term_id => [ child_term_id => 'on' ] ]
 *   Created in the "Characteristics" meta box for characteristics
 *   with children (choice options).
 * 
 * TAXONOMY:
 * 
 * characteristics
 *   Hierarchical taxonomy of characteristics. The top level is
 *   headings (for example, "Color"), the children are options
 *   (for example, "Red", "Blue").
 * 
 *   The term->description field is used for the characteristic's
 *   popup description (opened by a click).
 * 
 * ============================================================
 * EXTERNAL DEPENDENCIES
 * ============================================================
 *  * 
 * - The "Characteristics" meta box in fieldsProduct.php —
 *   the data source for this table.
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get the product meta fields
$weight = get_post_meta( get_the_ID(), '_weight', true );

$characteristicsText     = get_post_meta( get_the_ID(), '_characteristics_text_field', true );
$characteristicsCheckbox = get_post_meta( get_the_ID(), '_characteristics_checkbox_field', true );

if ( $characteristicsText || $characteristicsCheckbox ) { 
    ?>
    
    <table class="detailed-fields">
        
        <?php if ( $weight ) { ?>
        <tr>
            
            <td class="detailed-fields-key"> 
                <div class="fieldsName"><span><?php esc_html_e( 'WEIGHT', 'argon-shop-theme' ); ?></span></div>
            </td>
            
            <td class="detailed-fields-value"> <?php echo esc_html( $weight ); ?> <?php esc_html_e( 'kg', 'argon-shop-theme' ); ?> </td>
                                
        </tr>
        <?php } ?>
        
        <?php 
        
        // ============================================
        // TEXT CHARACTERISTICS
        // ============================================
        // 
        // _characteristics_text_field: [ term_id => 'value' ]
        // For each characteristic a term is looked up in the
        // characteristics taxonomy. If the term has a description, it
        // is output into the popup block.
        
        if ( is_array( $characteristicsText ) ) {
            
            foreach ( $characteristicsText as $term_id => $value ) { 
                
                $term_id = absint( $term_id );
                
                if ( 0 === $term_id ) {
                    continue;
                }
                
                $term = get_term_by( 'id', $term_id, 'characteristics' );
                
                if ( ! $term || is_wp_error( $term ) ) {
                    continue;
                }
                
                $productDescr = isset( $term->description ) ? $term->description : '';
                
                ?>
                
                <tr>
                    
                    <td class="detailed-fields-key"> 
                        <div class="fieldsName"><span><?php echo esc_html( $term->name ); ?></span></div>
                    </td>
                    
                    <td class="detailed-fields-value"> <?php echo esc_html( $value ); ?> </td>
                                
                </tr>
                
                <?php 
            }
        }
        
        // ============================================
        // CHECKBOX CHARACTERISTICS
        // ============================================
        // 
        // _characteristics_checkbox_field: [ parent_id => [ child_id => 'on' ] ]
        // Output as a comma-separated list of the selected options.
        
        if ( isset( $characteristicsCheckbox ) && $characteristicsCheckbox ) {
            
            foreach ( $characteristicsCheckbox as $parent_id => $characteristicsValues ) { 
                
                $parent_id = absint( $parent_id );
                
                if ( 0 === $parent_id ) {
                    continue;
                }
                
                $term = get_term_by( 'id', $parent_id, 'characteristics' );
                
                if ( ! $term || is_wp_error( $term ) ) {
                    continue;
                }
                
                if ( ! is_array( $characteristicsValues ) ) {
                    continue;
                }
                
                $productDescr = isset( $term->description ) ? $term->description : '';
                
                ?>
                
                <tr>
                    
                    <td class="detailed-fields-key"> 
                        <div class="fieldsName"><span><?php echo esc_html( $term->name ); ?></span></div>
                    </td>
                    
                    <td class="detailed-fields-value"> 
                    
                        <?php 
                        
                        // Collect the names of the selected options into an array,
                        // then output them comma-separated. This avoids
                        // problems with next() and mutating the array pointer.
                        
                        $selected_names = array();
                        
                        foreach ( $characteristicsValues as $child_id => $value ) { 
                            
                            $child_id = absint( $child_id );
                            
                            if ( 0 === $child_id ) {
                                continue;
                            }
                            
                            $child_term = get_term_by( 'id', $child_id, 'characteristics' );
                            
                            if ( ! $child_term || is_wp_error( $child_term ) ) {
                                continue;
                            }
                            
                            $selected_names[] = esc_html( $child_term->name );
                        }
                        
                        echo implode( ', ', $selected_names );
                        
                        ?>
                    
                    </td>
                                
                </tr>
                
                <?php 
            } 
        }   
        ?>
    
    </table>

<?php
} else {
    ?>
    
    <p><?php esc_html_e( 'No characteristics', 'argon-shop-theme' ); ?></p>
    
    <?php
}