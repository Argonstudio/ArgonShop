<?php
/**
 * Форма поиска по сайту
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * 1. Форма поиска в шапке сайта
 * 
 * ЧТО ОН ДЕЛАЕТ:
 * 1. Выводит форму поиска с её настройками
 * 2. Поддерживает AJAX-поиск
 * 3. Выводит скрытые поля для типов записей и таксономий
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ФУНКЦИЯ: выводит форму поиска
 * 
 * ГДЕ ИСПОЛЬЗУЕТСЯ: в шаблонах темы для вывода формы поиска
 * 
 * ПОДДЕРЖИВАЕТ:
 * 1. Обычный поиск (GET-отправка на /?s=query)
 * 2. AJAX-поиск (результаты без перезагрузки страницы)
 * 3. Поиск по нескольким типам записей (товары, статьи, страницы)
 * 4. Фильтрация по таксономиям (каталог, категории)
 * 5. Несколько блоков результатов (акции, новости, блог)
 * 
 * ВАЖНО: поиск по записям и категориям записей работает «из коробки»,
 *        без дополнительных изменений плагина. Если нет
 *        товаров или таксономии каталога — соответствующие блоки
 *        просто не появляются в результатах (без ошибок).
 * 
 * СТРУКТУРА ПАРАМЕТРОВ:
 * 
 * @param array $searchParameters {
 *     Массив параметров формы поиска.
 * 
 *     @type array  $post_type      Массив типов записей для поиска.
 *                                  Для записей и категорий: array('post').
 *                                  Для товаров: array('product').
 *                                  Можно комбинировать: array('product', 'post').
 *                                  По умолчанию: все типы.
 * 
 *     @type array  $taxonomy       Массив таксономий для фильтрации.
 *                                  Для категорий записей: array('category' => 'all')
 *                                  или array('category' => array(36, 37)).
 *                                  Для каталога: array('catalog' => 'all').
 *                                  По умолчанию: все таксономии.
 * 
 *     @type string $classForm      CSS-класс для <form>.
 *                                  По умолчанию: 'asSearchForm'.
 * 
 *     @type string $classInput     CSS-класс для <input type="text">.
 *                                  По умолчанию: 'asInputSearchForm'.
 * 
 *     @type string $classSubmit    CSS-класс для <input type="submit">.
 *                                  По умолчанию: 'asSubmitSearchForm'.
 * 
 *     @type string $valueSubmit    Текст кнопки поиска.
 *                                  По умолчанию: 'Search' (переводится).
 * 
 *     @type array  $ajax {
 *         Настройки AJAX-поиска. Если отсутствует — обычный поиск.
 * 
 *         @type string       $classBlockResult CSS-класс контейнера результатов.
 *         @type string       $positionResult   Позиция контейнера: 'top' или 'bottom'.
 *                                              По умолчанию: 'bottom'.
 * 
 *         @type bool         $productResult    Искать ли товары.
 *                                              Если товаров нет — блок не появляется.
 *                                              По умолчанию: не передаётся.
 * 
 *         @type bool         $catalogResult    Искать ли категории каталога.
 *                                              Если таксономии каталога нет — блок не появляется.
 *                                              По умолчанию: не передаётся.
 * 
 *         @type array|bool   $postResult {
 *             Массив дополнительных блоков поиска по записям.
 *             Работает независимо от productResult и catalogResult.
 * 
 *             @type array $item {
 *                 @type string $name     Заголовок блока (например, «Акции и статьи»).
 *                 @type string $class    CSS-класс блока.
 *                 @type array  $category Массив ID категорий записей для поиска.
 *             }
 *         }
 *     }
 * }
 * 
 * ПРИМЕРЫ ВЫЗОВА:
 * 
 * 1. Простой поиск (без AJAX):
 * 
 *     as_search( array(
 *         'post_type'   => array( 'post' ),
 *         'taxonomy'    => array( 'category' => 'all' ),
 *         'valueSubmit' => 'Find',
 *     ) );
 * 
 * 2. AJAX-поиск по записям и категориям (работает без изменений):
 * 
 *     as_search( array(
 *         'post_type' => array( 'post' ),
 *         'taxonomy'  => array( 'category' => 'all' ),
 *         'ajax'      => array(
 *             'classBlockResult' => 'topSearchBlockResult',
 *             'positionResult'   => 'bottom',
 *             'postResult'       => array(
 *                 array(
 *                     'name'     => 'Articles',
 *                     'class'    => 'resultEntry',
 *                     'category' => array( 36, 37 ),
 *                 ),
 *                 array(
 *                     'name'     => 'News',
 *                     'class'    => 'resultEntry',
 *                     'category' => array( 1 ),
 *                 ),
 *             ),
 *         ),
 *     ) );
 * 
 * 3. Полный AJAX-поиск (товары + записи, требуется каталог):
 * 
 *     as_search( array(
 *         'post_type' => array( 'product', 'post' ),
 *         'taxonomy'  => array( 'catalog' => 'all' ),
 *         'ajax'      => array(
 *             'classBlockResult' => 'topSearchBlockResult',
 *             'positionResult'   => 'bottom',
 *             'productResult'    => true,
 *             'catalogResult'    => true,
 *             'postResult'       => array(
 *                 array(
 *                     'name'     => 'Promotions and articles',
 *                     'class'    => 'resultEntry',
 *                     'category' => array( 36, 37 ),
 *                 ),
 *             ),
 *         ),
 *     ) );
 * 
 * ВОЗВРАЩАЕТ: ничего. Выводит HTML формы поиска.
 * 
 * @see as_ajaxBlock() — вывод контейнера AJAX-результатов
 */
function as_search( $searchParameters ) {
    
    if ( ! is_array( $searchParameters ) ) {
        $searchParameters = array();
    }
    
    $classForm = 'asSearchForm';
    $classInputForm = 'asInputSearchForm';
    $classButtonForm = 'asSubmitSearchForm';
    
    if ( ! empty( $searchParameters['classForm'] ) ) {
        $classForm .= ' ' . esc_attr( $searchParameters['classForm'] );
    }
    
    if ( ! empty( $searchParameters['classInput'] ) ) {
        $classInputForm .= ' ' . esc_attr( $searchParameters['classInput'] );
    }
    
    if ( ! empty( $searchParameters['classSubmit'] ) ) {
        $classButtonForm .= ' ' . esc_attr( $searchParameters['classSubmit'] );
    }
    
    // Значение кнопки
    if ( isset( $searchParameters['valueSubmit'] ) ) {
        $valueSubmit = $searchParameters['valueSubmit'];
    } else {
        $valueSubmit = __( 'Search', 'argon-shop' );
    }
    
    $valueSubmit_esc = esc_attr( $valueSubmit );
    $classForm_esc = esc_attr( $classForm );
    $classInputForm_esc = esc_attr( $classInputForm );
    $classButtonForm_esc = esc_attr( $classButtonForm );
    
    $dataAjax = array();
    $ajaxPositionResult = 'bottom';
    
    // Собираем AJAX-параметры
    if ( isset( $searchParameters['ajax'] ) ) {
        
        if ( ! empty( $searchParameters['ajax']['productResult'] ) ) {
            $dataAjax['productResult'] = $searchParameters['ajax']['productResult'];
        }
        
        if ( ! empty( $searchParameters['ajax']['catalogResult'] ) ) {
            $dataAjax['catalogResult'] = $searchParameters['ajax']['catalogResult'];
        }
        
        if ( ! empty( $searchParameters['ajax']['postResult'] ) ) {
            $dataAjax['postResult'] = $searchParameters['ajax']['postResult'];
        }
        
        // Заменяем кавычки для корректного вывода HTML
        $dataAjax = str_replace( "'", '"', json_encode( $dataAjax ) );
        
        if ( ! empty( $searchParameters['ajax']['positionResult'] ) ) {
            $ajaxPositionResult = $searchParameters['ajax']['positionResult'];
        }
    }
    
    // Выводим блок результатов (сверху)
    if ( isset( $searchParameters['ajax'] ) && $ajaxPositionResult === 'top' ) {
        
        $classBlockResult = isset( $searchParameters['ajax']['classBlockResult'] ) ? $searchParameters['ajax']['classBlockResult'] : '';
        as_ajaxBlock( $classBlockResult );
    }
    
    // атрибут data-blockresult
    $linkResultBlock = '';
    
    if ( isset( $searchParameters['ajax']['classBlockResult'] ) && $searchParameters['ajax']['classBlockResult'] ) {
        $linkResultBlock = "data-blockresult='" . esc_attr( $searchParameters['ajax']['classBlockResult'] ) . "'";
    }
    
    ?>
    
    <form role="search" method="get" class="<?php echo $classForm_esc; ?>" action="<?php echo esc_url( home_url( '/' ) ); ?>">
        <div>
            <input type="text" <?php echo $linkResultBlock; ?> <?php echo ( $dataAjax ) ? "data-ajax='" . esc_attr( $dataAjax ) . "'" : ""; ?> class="<?php echo $classInputForm_esc; ?>" placeholder="<?php esc_attr_e( 'Search', 'argon-shop' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" autocomplete="off" />
            
            <?php 
            
            if ( ! empty( $searchParameters['post_type'] ) ) {
                
                // Скрытые поля для типов записей
                if ( is_array( $searchParameters['post_type'] ) ) {
                    
                    foreach ( $searchParameters['post_type'] as $key ) {
                        
                        $postType = 'post_type[]';
                        $key_esc = esc_attr( $key );
                        
                        ?>
                        
                        <input type="hidden" value="<?php echo $key_esc; ?>" name="<?php echo esc_attr( $postType ); ?>" />
                        
                        <?php
                    }
                }
                
                // Скрытые поля для таксономий
                if ( ! empty( $searchParameters['taxonomy'] ) && is_array( $searchParameters['taxonomy'] ) ) {
                    
                    foreach ( $searchParameters['taxonomy'] as $key => $value ) {
                        
                        if ( $value !== 'all' ) {
                            $value = serialize( $value );
                        }
                        
                        $nameGet = 'as_taxonomy[' . $key . ']';
                        
                        ?>
                        
                        <input type="hidden" value="<?php echo esc_attr( $value ); ?>" name="<?php echo esc_attr( $nameGet ); ?>" />
                        
                        <?php
                    }
                }
            }
            
            ?>
            
            <input type="submit" class="<?php echo $classButtonForm_esc; ?>" value="<?php echo $valueSubmit_esc; ?>" />
        </div>
    </form>
    
    <?php
    
    // Выводим блок результатов (снизу)
    if ( isset( $searchParameters['ajax'] ) && $ajaxPositionResult === 'bottom' ) {
        
        $classBlockResult = isset( $searchParameters['ajax']['classBlockResult'] ) ? $searchParameters['ajax']['classBlockResult'] : '';
        as_ajaxBlock( $classBlockResult );
    }
}

/**
 * ФУНКЦИЯ: выводит контейнер AJAX-результатов
 * 
 * @param string $class — дополнительный CSS-класс
 */
function as_ajaxBlock( $class ) {
    
    $ajaxBlockClass = 'searchAjaxResult';
    
    if ( $class ) {
        $ajaxBlockClass .= ' ' . esc_attr( $class );
    }
    
    $ajaxBlockClass_esc = esc_attr( $ajaxBlockClass );
    
    ?>
    
    <div class="<?php echo $ajaxBlockClass_esc; ?>" style="display:none"></div>
    
    <?php
}