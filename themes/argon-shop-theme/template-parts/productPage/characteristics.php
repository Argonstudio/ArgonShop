<?php
/**
 * Блок характеристик товара (шаблон части)
 *
 * @package ArgonShopTheme
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ИСПОЛЬЗУЕТСЯ:
 * Подключается через get_template_part( 'template-parts/productPage/characteristics' )
 * из шаблона single-product.php (страница товара).
 * 
 * ЧТО ВЫВОДИТ:
 * Таблицу с характеристиками товара:
 * 1. Вес товара (мета-поле _weight)
 * 2. Текстовые характеристики (мета-поле _characteristics_text_field)
 * 3. Характеристики-чекбоксы (мета-поле _characteristics_checkbox_field)
 * 
 * Если характеристик нет — выводит "Характеристик нет".
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 * 
 * МЕТА-ПОЛЯ ТОВАРА (создаются в fieldsProduct.php):
 * 
 * _weight
 *   Вес товара (число, строка с запятой или точкой).
 *   Редактируется в метабоксе "Основные параметры".
 * 
 * _characteristics_text_field
 *   Ассоциативный массив:
 *     [ term_id => 'значение' ]
 *   Создаётся в метабоксе "Характеристики" для характеристик
 *   без дочерних элементов (текстовый ввод).
 * 
 * _characteristics_checkbox_field
 *   Двухуровневый ассоциативный массив:
 *     [ parent_term_id => [ child_term_id => 'on' ] ]
 *   Создаётся в метабоксе "Характеристики" для характеристик
 *   с дочерними элементами (варианты выбора).
 * 
 * ТАКСОНОМИЯ:
 * 
 * characteristics
 *   Иерархическая таксономия характеристик. Верхний уровень —
 *   заголовки (например, "Цвет"), дочерние — варианты
 *   (например, "Красный", "Синий").
 * 
 *   Поле term->description используется для всплывающего описания
 *   характеристики (открывается через Fancybox по клику).
 * 
 * ============================================================
 * ВНЕШНИЕ ЗАВИСИМОСТИ
 * ============================================================
 * 
 * - Fancybox — для показа всплывающего описания характеристики
 *   Атрибуты: data-fancybox data-src="#descr{term_id}"
 * 
 * - Метабокс "Характеристики" в fieldsProduct.php —
 *   источник данных для этой таблицы.
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Получение мета-полей товара
$weight = get_post_meta( get_the_ID(), '_weight', true );

$characteristicsText     = get_post_meta( get_the_ID(), '_characteristics_text_field', true );
$characteristicsCheckbox = get_post_meta( get_the_ID(), '_characteristics_checkbox_field', true );

if ( $characteristicsText || $characteristicsCheckbox ) { 
    ?>
    
    <table class="detailed-fields">
        
        <?php if ( $weight ) { ?>
        <tr>
            
            <td class="detailed-fields-key"> 
                <div class="fieldsName"><span>ВЕС</span></div>
            </td>
            
            <td class="detailed-fields-value"> <?php echo esc_html( $weight ); ?> кг </td>
                                
        </tr>
        <?php } ?>
        
        <?php 
        
        // ============================================
        // ТЕКСТОВЫЕ ХАРАКТЕРИСТИКИ
        // ============================================
        // 
        // _characteristics_text_field: [ term_id => 'значение' ]
        // Для каждой характеристики ищется термин в таксономии
        // characteristics. Если у термина есть описание — оно
        // выводится в Fancybox-блок.
        
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
                $fancybox     = 'data-fancybox data-src="#descr' . absint( $term->term_id ) . '"';
                
                ?>
                
                <tr>
                    
                    <td class="detailed-fields-key<?php echo $productDescr ? ' detailed-fields-keyDescr' : ''; ?>"> 
                        <div <?php echo $productDescr ? $fancybox : ''; ?> class="fieldsName"><span><?php echo esc_html( $term->name ); ?></span></div>
                        
                        <?php if ( $productDescr ) { ?>
                            
                            <div id="descr<?php echo absint( $term->term_id ); ?>" class="fieldDescription"><?php echo $productDescr; ?></div>
                            
                        <?php } ?>
                        
                    </td>
                    
                    <td class="detailed-fields-value"> <?php echo esc_html( $value ); ?> </td>
                                
                </tr>
                
                <?php 
            }
        }
        
        // ============================================
        // ХАРАКТЕРИСТИКИ-ЧЕКБОКСЫ
        // ============================================
        // 
        // _characteristics_checkbox_field: [ parent_id => [ child_id => 'on' ] ]
        // Выводятся как список выбранных вариантов через запятую.
        
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
                $fancybox     = 'data-fancybox data-src="#descr' . absint( $term->term_id ) . '"';
                
                ?>
                
                <tr>
                    
                    <td class="detailed-fields-key<?php echo $productDescr ? ' detailed-fields-keyDescr' : ''; ?>"> 
                        <div <?php echo $productDescr ? $fancybox : ''; ?> class="fieldsName"><span><?php echo esc_html( $term->name ); ?></span></div>
                        
                        <?php if ( $productDescr ) { ?>
                            
                            <div id="descr<?php echo absint( $term->term_id ); ?>" class="fieldDescription"><?php echo $productDescr; ?></div>
                            
                        <?php } ?>
                        
                    </td>
                    
                    <td class="detailed-fields-value"> 
                    
                        <?php 
                        
                        // Собираем названия выбранных вариантов в массив,
                        // затем выводим через запятую. Это позволяет избежать
                        // проблемы с next() и мутацией указателя массива.
                        
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
    
    <p>Характеристик нет</p>
    
    <?php
}