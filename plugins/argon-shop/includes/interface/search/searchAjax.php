<?php
/**
 * AJAX-поиск по сайту
 *
 * @package Argon_Shop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * 1. Поле поиска в шапке сайта → ввод текста → выпадающие результаты
 * 2. Страница поиска → строка поиска
 * 3. В панели администратора
 * 
 * ЧТО ДЕЛАЕТ:
 * 1. Ищет товары по названию и артикулу
 * 2. Ищет категории каталога
 * 3. Ищет записи (акции, статьи, новости)
 * 4. Исправляет раскладку клавиатуры (ghjlern → продукт)
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Регистрация AJAX-обработчиков
 * wp_ajax_ — для авторизованных
 * wp_ajax_nopriv_ — для гостей
 */
add_action( 'wp_ajax_getAjaxSearchResult', 'getAjaxSearchResult_callback' );
add_action( 'wp_ajax_nopriv_getAjaxSearchResult', 'getAjaxSearchResult_callback' );

/**
 * ОБРАБОТЧИК: Поиск по сайту
 * 
 * ГДЕ ТЕСТИРОВАТЬ: Поле поиска → ввод текста
 * ЧТО ДЕЛАЕТ:
 * 1. Получает текст поиска
 * 2. Вызывает queryManagerSearchResult() для поиска
 * 3. Если результатов нет — исправляет раскладку клавиатуры
 * 4. Выводит HTML с результатами
 */
function getAjaxSearchResult_callback() {
    
    // Проверка nonce
    check_ajax_referer( 'argon_shop_nonce', 'nonce' );
    
    // Проверка наличия данных
    if ( ! isset( $_POST['searchStr'] ) ) {
        wp_die( 'Недостаточно данных' );
    }
    
    // Очистка текста поиска
    $searchStr = sanitize_text_field( wp_unslash( $_POST['searchStr'] ) );
    
    // Получение параметров поиска
    $searchParameters = '';
    
    if ( isset( $_POST['dataAjax'] ) ) {
        $searchParameters = stripslashes( strip_tags( $_POST['dataAjax'] ) );
        $searchParameters = json_decode( $searchParameters );
    }
    
    // Если параметры корректны — выполняем поиск
    if ( gettype( $searchParameters ) === 'object' ) {
        
        $searchResult = queryManagerSearchResult( $searchStr, $searchParameters );
        
        // Если пусто и текст не число — пробуем исправить раскладку
        if ( empty( $searchResult ) && ! ctype_digit( $searchStr ) ) {
            
            $searchStrFix = fixKeyboardlayout( $searchStr );
            
            if ( $searchStrFix != $searchStr ) {
                $searchResult = queryManagerSearchResult( $searchStrFix, $searchParameters );
            }
        }
        
        // Вывод результатов
        if ( ! empty( $searchResult ) ) {
            showResult( $searchResult );
        } else {
            echo 'Нет подходящих результатов';
        }
    }
    
    wp_die();
}

/*
 ** Получение подходящих категорий и товаров из базы данных
*/

/**
 * ФУНКЦИЯ: Управляющая функция поиска
 * 
 * ГДЕ ВЫЗЫВАЕТСЯ: getAjaxSearchResult_callback()
 * ЧТО ДЕЛАЕТ:
 * 1. Очищает параметры поиска
 * 2. Ищет товары (если включено)
 * 3. Ищет категории каталога (если включено)
 * 4. Ищет записи (акции, статьи, новости)
 * 
 * @param string $searchStr       — текст поиска
 * @param object $searchParameters — параметры поиска (что искать)
 * 
 * @return array Массив результатов по типам: product, catalog, post
 */
function queryManagerSearchResult( $searchStr, $searchParameters ) {
    
    $searchResult = array();
    
    // Очистка параметров
    $searchParameters = cleanParameters( $searchParameters );
    
    // Поиск товаров
    if ( empty( $searchParameters->productResult ) || ( $searchParameters->productResult ?? false ) === true ) {
        
        $searchResultProduct = queryAllocatorSearchResult( 'product', 10, $searchStr );
        
        if ( $searchResultProduct ) {
            
            $searchResult['product'] = array(
                'nameBlock'  => 'Подходящие товары',
                'listResult' => prepProduct( $searchResultProduct ),
            );
        }
    }
    
    // Поиск категорий каталога
    if ( empty( $searchParameters->catalogResult ) || ( $searchParameters->catalogResult ?? false ) === true ) {
        
        $searchResultCatalog = queryAllocatorSearchResult( 'category', 10, $searchStr );
        
        if ( $searchResultCatalog ) {
            
            $searchResult['catalog'] = array(
                'nameBlock'  => 'Категории',
                'listResult' => prepCatalog( $searchResultCatalog ),
            );
        }
    }
    
    // Поиск записей (акции, статьи, новости)
    if ( ! empty( $searchParameters->postResult ) ) {
        
        foreach ( $searchParameters->postResult as $key => $value ) {
            
            $searchResultPost = queryAllocatorSearchResult( 'post', 10, $searchStr, $value );
            
            if ( $searchResultPost ) {
                
                $postListResult = array(
                    'nameBlock'  => $value->name,
                    'listResult' => prepPost( $searchResultPost ),
                );
                
                if ( $value->class ) {
                    $postListResult['classBlock'] = $value->class;
                }
                
                $searchResult['post'][] = $postListResult;
            }
        }
    }
    
    return $searchResult;
}

/**
 * ФУНКЦИЯ: Распределитель запросов
 * 
 * ГДЕ ВЫЗЫВАЕТСЯ: queryManagerSearchResult()
 * ЧТО ДЕЛАЕТ: Направляет запрос к нужной функции поиска
 * 
 * @param string $type       — 'product', 'post' или 'category'
 * @param int    $quantity   — количество результатов
 * @param string $searchStr  — текст поиска
 * @param object $parameters — дополнительные параметры
 * 
 * @return array Результат поиска
 */
function queryAllocatorSearchResult( $type = null, $quantity = null, $searchStr = null, $parameters = null ) {
    
    $searchResult = null;
    
    switch ( $type ) {
        
        case 'product':
            $searchResult = queryProductSearchResult( $searchStr, $quantity );
            break;
            
        case 'post':
            $searchResult = queryPostSearchResult( $searchStr, $quantity, $parameters );
            break;
            
        case 'category':
            $searchResult = queryTaxonomySearchResult( $searchStr, $quantity );
            break;
    }
    
    return $searchResult;
}

/*
 ** Запросы в базе данных
*/

/**
 * ФУНКЦИЯ: Поиск по таксономии (категории каталога)
 * 
 * ГДЕ ВЫЗЫВАЕТСЯ: queryAllocatorSearchResult()
 * ЧТО ДЕЛАЕТ: Ищет термины таксономии catalog по названию
 * 
 * @param string $searchStr — текст поиска
 * @param int    $quantity  — максимальное количество результатов
 * 
 * @return array Список терминов id=>name
 */
function queryTaxonomySearchResult( $searchStr, $quantity ) {
    
    $argumentSearch = array(
        'taxonomy'     => 'catalog',
        'number'       => $quantity,
        'hierarchical' => true,
        'fields'       => 'id=>name',
        'search'       => $searchStr,
    );
    
    $querySearchTerms = get_terms( $argumentSearch );
    
    // Проверка на ошибку WP_Error
    if ( is_wp_error( $querySearchTerms ) ) {
        return array();
    }
    
    return $querySearchTerms;
}

/**
 * ФУНКЦИЯ: Поиск по записям (статьи, акции)
 * 
 * ГДЕ ВЫЗЫВАЕТСЯ: queryAllocatorSearchResult()
 * ЧТО ДЕЛАЕТ: Ищет записи типа post в указанных категориях
 * 
 * @param string $searchStr  — текст поиска
 * @param int    $quantity   — количество результатов
 * @param object $parameters — параметры (категории для поиска)
 * 
 * @return array Массив ID найденных записей
 */
function queryPostSearchResult( $searchStr, $quantity, $parameters ) {
    
    // Получаем категории для поиска
    $categories = array();
    
    if ( isset( $parameters->category ) && is_array( $parameters->category ) ) {
        $categories = array_map( 'absint', $parameters->category );
    }
    
    $argumentSearch = array(
        'post_type'      => 'post',
        'posts_per_page' => $quantity,
        'fields'         => 'ids',
        's'              => $searchStr,
        'tax_query'      => array(
            array(
                'taxonomy' => 'category',
                'field'    => 'id',
                'terms'    => $categories,
            ),
        ),
    );
    
    $querySearchStr = new WP_Query( $argumentSearch );
    $querySearchStr = $querySearchStr->posts;
    
    return $querySearchStr;
}

/**
 * ФУНКЦИЯ: Поиск по товарам
 * 
 * Использует ту же логику, что и GET-поиск (searchGetFilters.php):
 * - Многословный AND-поиск по title, excerpt, content, артикулу
 * - Система очков по релевантности
 * 
 * Дополнительно подключает фильтры searchGetFilters.php на время
 * запроса, чтобы AJAX и GET давали одинаковый результат.
 * 
 * @param string $searchStr — текст поиска
 * @param int    $quantity  — количество результатов
 * 
 * @return array Массив объектов WP_Post
 */
function queryProductSearchResult( $searchStr, $quantity ) {
    
    $quantity = absint( $quantity );
    
    if ( empty( $searchStr ) || $quantity < 1 ) {
        return array();
    }
    
    // Подключаем фильтры из searchGetFilters.php.
    // Они активируются только если функция search_is_custom_search() 
    // вернёт true. Для AJAX-запроса она вернёт false (нет is_main_query),
    // поэтому используем отдельные фильтры-обёртки с флагом _argon_search.
    
    add_filter( 'posts_search',  'argon_search_ajax_where', 10, 2 );
    add_filter( 'posts_join',    'argon_search_ajax_join', 10, 2 );
    add_filter( 'posts_distinct', 'argon_search_ajax_distinct', 10, 2 );
    add_filter( 'posts_orderby', 'argon_search_ajax_orderby', 10, 2 );
    
    $query = new WP_Query( array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => $quantity,
        's'              => $searchStr,
        '_argon_search'  => true,
    ) );
    
    remove_filter( 'posts_search',  'argon_search_ajax_where', 10 );
    remove_filter( 'posts_join',    'argon_search_ajax_join', 10 );
    remove_filter( 'posts_distinct', 'argon_search_ajax_distinct', 10 );
    remove_filter( 'posts_orderby', 'argon_search_ajax_orderby', 10 );
    
    if ( ! $query instanceof WP_Query || empty( $query->posts ) ) {
        return array();
    }
    
    return $query->posts;
}

// ============================================
// ФИЛЬТРЫ ДЛЯ AJAX-ПОИСКА ТОВАРОВ
// ============================================
// 
// Дублируют логику searchGetFilters.php, но применяются только к
// запросам с флагом _argon_search (AJAX-поиск). Основной поиск 
// WordPress на /?s= не затрагивается.

/**
 * Проверка, нужно ли применять AJAX-фильтры
 */
function argon_search_ajax_needed( $query ) {
    return (bool) $query->get( '_argon_search' );
}

/**
 * ФИЛЬТР: WHERE — многословный AND-поиск
 */
function argon_search_ajax_where( $search, $query ) {
    
    global $wpdb;
    
    if ( ! argon_search_ajax_needed( $query ) ) {
        return $search;
    }
    
    $s     = $query->get( 's' );
    $words = function_exists( 'search_get_words' ) ? search_get_words( $s ) : array();
    
    if ( empty( $words ) ) {
        return $search;
    }
    
    $where_parts = array();
    
    foreach ( $words as $word ) {
        
        $like     = '%' . $wpdb->esc_like( $word ) . '%';
        $like_esc = esc_sql( $like );
        
        $where_parts[] = "( {$wpdb->posts}.post_title LIKE '{$like_esc}' 
                          OR {$wpdb->posts}.post_excerpt LIKE '{$like_esc}' 
                          OR {$wpdb->posts}.post_content LIKE '{$like_esc}' 
                          OR as_article_meta.meta_value LIKE '{$like_esc}' )";
    }
    
    if ( empty( $where_parts ) ) {
        return $search;
    }
    
    return ' AND (' . implode( ' AND ', $where_parts ) . ') ';
}

/**
 * ФИЛЬТР: JOIN на postmeta для артикула
 */
function argon_search_ajax_join( $join, $query ) {
    
    global $wpdb;
    
    if ( ! argon_search_ajax_needed( $query ) ) {
        return $join;
    }
    
    $join .= " LEFT JOIN {$wpdb->postmeta} AS as_article_meta 
               ON ( {$wpdb->posts}.ID = as_article_meta.post_id 
                    AND as_article_meta.meta_key = '_article' ) ";
    
    return $join;
}

/**
 * ФИЛЬТР: DISTINCT — защита от дублирования
 */
function argon_search_ajax_distinct( $distinct, $query ) {
    
    if ( ! argon_search_ajax_needed( $query ) ) {
        return $distinct;
    }
    
    return 'DISTINCT';
}

/**
 * ФИЛЬТР: ORDER BY — сортировка по очкам релевантности
 */
function argon_search_ajax_orderby( $orderby, $query ) {
    
    global $wpdb;
    
    if ( ! argon_search_ajax_needed( $query ) ) {
        return $orderby;
    }
    
    $s     = $query->get( 's' );
    $words = function_exists( 'search_get_words' ) ? search_get_words( $s ) : array();
    
    if ( empty( $words ) ) {
        return $orderby;
    }
    
    // Полная фраза
    $full_trim  = trim( $s );
    $full_exact = esc_sql( $full_trim );
    $full_start = esc_sql( $wpdb->esc_like( $full_trim ) . '%' );
    
    $score_parts = array();
    
    $score_parts[] = "CASE WHEN {$wpdb->posts}.post_title = '{$full_exact}' THEN 1000 ELSE 0 END";
    $score_parts[] = "CASE WHEN as_article_meta.meta_value = '{$full_exact}' THEN 800 ELSE 0 END";
    $score_parts[] = "CASE WHEN {$wpdb->posts}.post_title LIKE '{$full_start}' THEN 500 ELSE 0 END";
    $score_parts[] = "CASE WHEN as_article_meta.meta_value LIKE '{$full_start}' THEN 400 ELSE 0 END";
    
    foreach ( $words as $word ) {
        
        $word_start       = esc_sql( $wpdb->esc_like( $word ) . '%' );
        $word_like        = esc_sql( '%' . $wpdb->esc_like( $word ) . '%' );
        $word_after_space = esc_sql( '% ' . $wpdb->esc_like( $word ) . '%' );
        
        $score_parts[] = "CASE WHEN {$wpdb->posts}.post_title LIKE '{$word_start}' THEN 50 ELSE 0 END";
        $score_parts[] = "CASE WHEN {$wpdb->posts}.post_title LIKE '{$word_after_space}' THEN 30 ELSE 0 END";
        $score_parts[] = "CASE WHEN {$wpdb->posts}.post_title LIKE '{$word_like}' THEN 10 ELSE 0 END";
        $score_parts[] = "CASE WHEN as_article_meta.meta_value LIKE '{$word_like}' THEN 5 ELSE 0 END";
    }
    
    $score_sql = '( ' . implode( ' + ', $score_parts ) . ' )';
    
    return "{$score_sql} DESC, {$wpdb->posts}.post_date DESC, {$wpdb->posts}.ID DESC";
}

/*
 ** Создаем массивы с информацией для вывода(url/title)
*/

/**
 * ФУНКЦИЯ: Подготовка данных категорий для вывода
 * 
 * ГДЕ ВЫЗЫВАЕТСЯ: queryManagerSearchResult()
 * ЧТО ДЕЛАЕТ: Преобразует термины в массив с title и url
 * 
 * @param array $searchResultCatalog — термины таксономии
 * 
 * @return array Массив с post_title и post_url
 */
function prepCatalog( $searchResultCatalog ) {
    
    $prepCatalog = array();
    
    foreach ( $searchResultCatalog as $key => $value ) {
        
        $key = absint( $key );
        
        $prepCatalog[ $key ] = array(
            'post_title' => esc_html( $value ),
            'post_url'   => esc_url( get_category_link( $key ) ),
        );
    }
    
    return $prepCatalog;
}

/**
 * ФУНКЦИЯ: Подготовка данных записей для вывода
 * 
 * ГДЕ ВЫЗЫВАЕТСЯ: queryManagerSearchResult()
 * ЧТО ДЕЛАЕТ: Преобразует ID записей в массив с title и url
 * 
 * @param array $searchResultPost — массив ID записей
 * 
 * @return array Массив с post_title и post_url
 */
function prepPost( $searchResultPost ) {
    
    $prepPost = array();
    
    foreach ( $searchResultPost as $key => $value ) {
        
        $value = absint( $value );
        
        $prepPost[ $value ] = array(
            'post_title' => esc_html( get_the_title( $value ) ),
            'post_url'   => esc_url( get_permalink( $value ) ),
        );
    }
    
    return $prepPost;
}

/**
 * ФУНКЦИЯ: Подготовка данных товаров для вывода
 * 
 * ГДЕ ВЫЗЫВАЕТСЯ: queryManagerSearchResult()
 * ЧТО ДЕЛАЕТ:
 * 1. Получает название и URL товара
 * 2. Получает категорию товара (если есть)
 * 
 * @param array $searchResultProduct — массив объектов WP_Post
 * 
 * @return array Массив с post_title, post_url, category_name, category_url
 */
function prepProduct( $searchResultProduct ) {
    
    $prepProduct = array();
    
    foreach ( $searchResultProduct as $key => $value ) {
        
        $product_id = absint( $value->ID );
        
        $prepProduct[ $product_id ] = array(
            'post_title' => esc_html( $value->post_title ),
            'post_url'   => esc_url( get_permalink( $product_id ) ),
        );
        
        // Получаем категорию товара
        $categoryProduct = get_the_terms( $product_id, 'catalog' );
        
        if ( ! empty( $categoryProduct ) && ! is_wp_error( $categoryProduct ) ) {
            
            $prepProduct[ $product_id ]['category_name'] = esc_html( $categoryProduct[0]->name );
            $prepProduct[ $product_id ]['category_url'] = esc_url( get_category_link( $categoryProduct[0]->term_id ) );
        }
    }
    
    return $prepProduct;
}

/*
 ** Создаем html вид результата
*/

/**
 * ФУНКЦИЯ: Вывод результатов поиска
 * 
 * ГДЕ ВЫЗЫВАЕТСЯ: getAjaxSearchResult_callback()
 * ЧТО ДЕЛАЕТ: Перебирает все типы результатов и вызывает generateResultHTML()
 * 
 * @param array $searchResult — результаты поиска
 */
function showResult( $searchResult ) {
    
    foreach ( $searchResult as $keyEntirelyResult => $valueEntirelyResult ) {
        
        // Для записей (несколько блоков)
        if ( $keyEntirelyResult === 'post' ) {
            
            foreach ( $valueEntirelyResult as $keyOneTypeResults => $valueOneTypeResults ) {
                generateResultHTML( $keyEntirelyResult, $valueOneTypeResults );
            }
            
        } else {
            // Для товаров и категорий (один блок)
            generateResultHTML( $keyEntirelyResult, $valueEntirelyResult );
        }
    }
}

/**
 * ФУНКЦИЯ: Генерация HTML для одного блока результатов
 * 
 * ГДЕ ВЫЗЫВАЕТСЯ: showResult()
 * ЧТО ДЕЛАЕТ: Выводит заголовок блока и список найденных элементов
 * 
 * @param string $type                — тип результата
 * @param array  $valueEntirelyResult — данные блока (nameBlock, listResult, classBlock)
 */
function generateResultHTML( $type, $valueEntirelyResult ) {
    
    $classBlockOneTypeResult = 'blockOneTypeResult';
    
    if ( ! empty( $valueEntirelyResult['classBlock'] ) ) {
        $classBlockOneTypeResult .= ' ' . sanitize_html_class( $valueEntirelyResult['classBlock'] );
    }
    
    $classBlockOneTypeResult_esc = esc_attr( $classBlockOneTypeResult );
    $nameBlock = isset( $valueEntirelyResult['nameBlock'] ) ? esc_html( $valueEntirelyResult['nameBlock'] ) : '';
    
    ?>
    
    <div class="<?php echo $classBlockOneTypeResult_esc; ?>">
        
        <div class="titleOneTypeResult">
            <?php echo $nameBlock; ?>
        </div>
        
        <?php
        
        $classBlockProduct = 'resultProduct';
        
        if ( ! empty( $valueEntirelyResult['listResult'] ) && is_array( $valueEntirelyResult['listResult'] ) ) {
            
            foreach ( $valueEntirelyResult['listResult'] as $idOneResult => $valueOneResult ) {
                
                $category_name = isset( $valueOneResult['category_name'] ) ? esc_html( $valueOneResult['category_name'] ) : '';
                $category_url = isset( $valueOneResult['category_url'] ) ? esc_url( $valueOneResult['category_url'] ) : '';
                $post_url = isset( $valueOneResult['post_url'] ) ? esc_url( $valueOneResult['post_url'] ) : '';
                $post_title = isset( $valueOneResult['post_title'] ) ? esc_html( $valueOneResult['post_title'] ) : '';
                
                ?>
                
                <div class="<?php echo esc_attr( $classBlockProduct ); ?>">
                    
                    <?php if ( ! empty( $category_name ) ) { ?>
                        
                        <span><a href="<?php echo $category_url; ?>"><?php echo $category_name; ?></a></span> → 
                        
                    <?php } ?>
                    
                    <a href="<?php echo $post_url; ?>"><?php echo $post_title; ?></a>
                    
                </div>
                
                <?php
            }
        }
        ?>
        
    </div>
    <?php
}

/*
 ** Вспомогательные функции
*/

/**
 * ФУНКЦИЯ: Объединение массивов результатов
 * 
 * ГДЕ ВЫЗЫВАЕТСЯ: queryProductSearchResult()
 * ЧТО ДЕЛАЕТ: Объединяет несколько массивов с приоритетом первых
 * 
 * @param array $typesResultArray — массив массивов для объединения
 * @param int   $quantity         — итоговое количество элементов
 * 
 * @return array Объединённый массив
 */
function merge_search_result( $typesResultArray, $quantity ) {
    
    $quantityResults = (int) $quantity;
    $searchStrResult = array();
    
    foreach ( $typesResultArray as $key => $value ) {
        
        if ( isset( $value ) && is_array( $value ) ) {
            
            $searchStrResult = array_partial_merge( $quantityResults, $searchStrResult, $value );
            $quantityResults -= count( $value );
        }
    }
    
    return $searchStrResult;
}

/**
 * ФУНКЦИЯ: Очистка параметров поиска
 * 
 * ГДЕ ВЫЗЫВАЕТСЯ: queryManagerSearchResult()
 * ЧТО ДЕЛАЕТ: Валидирует входящие параметры, оставляя только корректные
 * 
 * @param object $searchParameters — сырые параметры
 * 
 * @return object Очищенные параметры
 */
function cleanParameters( $searchParameters ) {
    
    $cleanSearchParameters = (object) array();
    
    // Очистка параметра productResult (boolean)
    if ( isset( $searchParameters->productResult ) && gettype( $searchParameters->productResult ) === 'boolean' ) {
        $cleanSearchParameters->productResult = $searchParameters->productResult;
    }
    
    // Очистка параметра catalogResult (boolean)
    if ( isset( $searchParameters->catalogResult ) && gettype( $searchParameters->catalogResult ) === 'boolean' ) {
        $cleanSearchParameters->catalogResult = $searchParameters->catalogResult;
    }
    
    // Очистка параметра postResult (array)
    if ( ! empty( $searchParameters->postResult ) && gettype( $searchParameters->postResult ) === 'array' ) {
        
        foreach ( $searchParameters->postResult as $key => $value ) {
            
            $cleanValue = (object) array();
            
            if ( gettype( $value ) === 'object' ) {
                
                if ( ! empty( $value->name ) && gettype( $value->name ) === 'string' ) {
                    $cleanValue->name = sanitize_text_field( $value->name );
                }
                
                if ( ! empty( $value->class ) && gettype( $value->class ) === 'string' ) {
                    $cleanValue->class = sanitize_text_field( $value->class );
                }
                
                if ( ! empty( $value->category ) && gettype( $value->category ) === 'array' ) {
                    
                    $cleanCategory = array();
                    
                    foreach ( $value->category as $idCategory ) {
                        
                        if ( gettype( $idCategory ) === 'integer' ) {
                            $cleanCategory[] = $idCategory;
                        }
                    }
                    
                    if ( ! empty( $cleanCategory ) ) {
                        $cleanValue->category = $cleanCategory;
                    }
                }
            }
            
            $cleanSearchParameters->postResult[] = $cleanValue;
        }
    }
    
    return $cleanSearchParameters;
}

/**
 * ФУНКЦИЯ: Исправление раскладки клавиатуры
 * 
 * ГДЕ ВЫЗЫВАЕТСЯ: getAjaxSearchResult_callback()
 * ЧТО ДЕЛАЕТ: Преобразует латинские буквы в русские (ghjlern → продукт)
 * 
 * @param string $searchStr — текст с неправильной раскладкой
 * 
 * @return string Исправленный текст
 */
function fixKeyboardlayout( $searchStr ) {
    
    $LangEn = array( "&","q","w","e","r","t","y","u","i","o","p","[","]",
                   "a","s","d","f","g","h","j","k","l",";","'",
                   "z","x","c","v","b","n","m",",",".","/",
                   "Q","W","E","R","T","Y","U","I","O","P","[","]",
                   "A","S","D","F","G","H","J","K","L",";","'",
                   "Z","X","C","V","B","N","M",",","/" );
    $LangRu = array( "?","й","ц","у","к","е","н","г","ш","щ","з","х","ъ",
                   "ф","ы","в","а","п","р","о","л","д","ж","э",
                   "я","ч","с","м","и","т","ь","б","ю",".",
                   "Й","Ц","У","К","Е","Н","Г","Ш","Щ","З","Х","Ъ",
                   "Ф","Ы","В","А","П","Р","О","Л","Д","Ж","Э",
                   "Я","Ч","С","М","И","Т","Ь","Б","Ю","." );
    
    $searchStr = str_replace( $LangEn, $LangRu, $searchStr );
    
    return $searchStr;
}