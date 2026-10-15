<?php
/**
 * AJAX-поиск по сайту
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * 1. Поле поиска в шапке сайта → ввод текста → выпадающие результаты
 * 2. Страница поиска → строка поиска
 * 3. В админ-панели
 * 
 * ЧТО ОН ДЕЛАЕТ:
 * 1. Ищет товары по имени и артикулу
 * 2. Ищет категории каталога
 * 3. Ищет записи (акции, статьи, новости)
 * 4. Исправляет раскладку клавиатуры в зависимости от языка сайта (см. searchLanguage.php)
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Регистрация AJAX-обработчиков
 * wp_ajax_ — для вошедших пользователей
 * wp_ajax_nopriv_ — для гостей
 */
add_action( 'wp_ajax_getAjaxSearchResult', 'as_getAjaxSearchResult_callback' );
add_action( 'wp_ajax_nopriv_getAjaxSearchResult', 'as_getAjaxSearchResult_callback' );

/**
 * ОБРАБОТЧИК: поиск по сайту
 * 
 * ГДЕ ТЕСТИРОВАТЬ: поле поиска → ввод текста
 * ЧТО ОН ДЕЛАЕТ:
 * 1. Получает текст поиска
 * 2. Вызывает as_queryManagerSearchResult() для поиска
 * 3. Если результатов нет — исправляет раскладку клавиатуры
 * 4. Выводит HTML результатов
 */
function as_getAjaxSearchResult_callback() {
    
    // Проверка nonce
    check_ajax_referer( 'argon_shop_nonce', 'nonce' );
    
    // Проверяем, что данные есть
    if ( ! isset( $_POST['searchStr'] ) ) {
        wp_die( __( 'Not enough data', 'argon-shop' ) );
    }
    
    // Очищаем текст поиска
    $searchStr = sanitize_text_field( wp_unslash( $_POST['searchStr'] ) );
    
    // Получаем параметры поиска
    $searchParameters = '';
    
    if ( isset( $_POST['dataAjax'] ) ) {
        $searchParameters = stripslashes( strip_tags( $_POST['dataAjax'] ) );
        $searchParameters = json_decode( $searchParameters );
    }
    
    // Если параметры корректны — запускаем поиск
    if ( gettype( $searchParameters ) === 'object' ) {
        
        $searchResult = as_queryManagerSearchResult( $searchStr, $searchParameters );
        
        // Если пусто и текст не число — пробуем исправить раскладку клавиатуры
        if ( empty( $searchResult ) && ! ctype_digit( $searchStr ) ) {
            
            foreach ( as_search_layout_variants( $searchStr ) as $searchStrFix ) {
                
                $searchResult = as_queryManagerSearchResult( $searchStrFix, $searchParameters );
                
                if ( ! empty( $searchResult ) ) {
                    break;
                }
            }
        }
        
        // Выводим результаты
        if ( ! empty( $searchResult ) ) {
            as_showResult( $searchResult );
        } else {
            echo __( 'No matching results', 'argon-shop' );
        }
    }
    
    wp_die();
}

/*
 ** Получение подходящих категорий и товаров из базы данных
*/

/**
 * ФУНКЦИЯ: менеджер поиска
 * 
 * ВЫЗЫВАЕТСЯ ИЗ: as_getAjaxSearchResult_callback()
 * ЧТО ОН ДЕЛАЕТ:
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
function as_queryManagerSearchResult( $searchStr, $searchParameters ) {
    
    $searchResult = array();
    
    // Очищаем параметры
    $searchParameters = as_cleanParameters( $searchParameters );
    
    // Поиск товаров
    if ( empty( $searchParameters->productResult ) || ( $searchParameters->productResult ?? false ) === true ) {
        
        $searchResultProduct = as_queryAllocatorSearchResult( 'product', 10, $searchStr );
        
        if ( $searchResultProduct ) {
            
            $searchResult['product'] = array(
                'nameBlock'  => __( 'Matching products', 'argon-shop' ),
                'listResult' => as_prepProduct( $searchResultProduct ),
            );
        }
    }
    
    // Поиск категорий каталога
    if ( empty( $searchParameters->catalogResult ) || ( $searchParameters->catalogResult ?? false ) === true ) {
        
        $searchResultCatalog = as_queryAllocatorSearchResult( 'category', 10, $searchStr );
        
        if ( $searchResultCatalog ) {
            
            $searchResult['catalog'] = array(
                'nameBlock'  => __( 'Categories', 'argon-shop' ),
                'listResult' => as_prepCatalog( $searchResultCatalog ),
            );
        }
    }
    
    // Поиск записей (акции, статьи, новости)
    if ( ! empty( $searchParameters->postResult ) ) {
        
        foreach ( $searchParameters->postResult as $key => $value ) {
            
            $searchResultPost = as_queryAllocatorSearchResult( 'post', 10, $searchStr, $value );
            
            if ( $searchResultPost ) {
                
                $postListResult = array(
                    'nameBlock'  => $value->name,
                    'listResult' => as_prepPost( $searchResultPost ),
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
 * ФУНКЦИЯ: диспетчер запросов
 * 
 * ВЫЗЫВАЕТСЯ ИЗ: as_queryManagerSearchResult()
 * ЧТО ДЕЛАЕТ: направляет запрос в нужную функцию поиска
 * 
 * @param string $type       — 'product', 'post' или 'category'
 * @param int    $quantity   — количество результатов
 * @param string $searchStr  — текст поиска
 * @param object $parameters — дополнительные параметры
 * 
 * @return array Результат поиска
 */
function as_queryAllocatorSearchResult( $type = null, $quantity = null, $searchStr = null, $parameters = null ) {
    
    $searchResult = null;
    
    switch ( $type ) {
        
        case 'product':
            $searchResult = as_queryProductSearchResult( $searchStr, $quantity );
            break;
            
        case 'post':
            $searchResult = as_queryPostSearchResult( $searchStr, $quantity, $parameters );
            break;
            
        case 'category':
            $searchResult = as_queryTaxonomySearchResult( $searchStr, $quantity );
            break;
    }
    
    return $searchResult;
}

/*
 ** Запросы к базе данных
*/

/**
 * ФУНКЦИЯ: поиск по таксономии (категории каталога)
 * 
 * ВЫЗЫВАЕТСЯ ИЗ: as_queryAllocatorSearchResult()
 * ЧТО ДЕЛАЕТ: ищет термины таксономии каталога по имени
 * 
 * @param string $searchStr — текст поиска
 * @param int    $quantity  — максимальное количество результатов
 * 
 * @return array Список терминов id=>name
 */
function as_queryTaxonomySearchResult( $searchStr, $quantity ) {
    
    $argumentSearch = array(
        'taxonomy'     => 'catalog',
        'number'       => $quantity,
        'hierarchical' => true,
        'fields'       => 'id=>name',
        'search'       => $searchStr,
    );
    
    $querySearchTerms = get_terms( $argumentSearch );
    
    // Проверка WP_Error
    if ( is_wp_error( $querySearchTerms ) ) {
        return array();
    }
    
    return $querySearchTerms;
}

/**
 * ФУНКЦИЯ: поиск записей (статьи, акции)
 * 
 * ВЫЗЫВАЕТСЯ ИЗ: as_queryAllocatorSearchResult()
 * ЧТО ДЕЛАЕТ: ищет записи типа post в заданных категориях
 * 
 * @param string $searchStr  — текст поиска
 * @param int    $quantity   — количество результатов
 * @param object $parameters — параметры (категории для поиска)
 * 
 * @return array Массив найденных ID записей
 */
function as_queryPostSearchResult( $searchStr, $quantity, $parameters ) {
    
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
 * ФУНКЦИЯ: поиск товаров
 * 
 * Использует ту же логику, что и GET-поиск (searchGetFilters.php):
 * - Поиск по нескольким словам через AND по заголовку, анонсу, контенту, артикулу
 * - Система оценки релевантности
 * 
 * Дополнительно подключает фильтры searchGetFilters.php на время
 * запроса, чтобы AJAX и GET возвращали одинаковый результат.
 * 
 * @param string $searchStr — текст поиска
 * @param int    $quantity  — количество результатов
 * 
 * @return array Массив объектов WP_Post
 */
function as_queryProductSearchResult( $searchStr, $quantity ) {
    
    $quantity = absint( $quantity );
    
    if ( empty( $searchStr ) || $quantity < 1 ) {
        return array();
    }
    
    // Подключаем фильтры из searchGetFilters.php.
    // Они активны только если as_search_is_custom_search()
    // возвращает true. Для AJAX-запроса он возвращает false (нет is_main_query),
    // поэтому используются отдельные фильтры-обёртки с флагом _argon_search.
    
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
// ФИЛЬТРЫ AJAX-ПОИСКА ТОВАРОВ
// ============================================
// 
// Они дублируют логику searchGetFilters.php, но применяются только к
// запросам с флагом _argon_search (AJAX-поиск). Основной поиск WordPress
// по /?s= не затрагивается.

/**
 * Проверяем, нужно ли применять AJAX-фильтры
 */
function argon_search_ajax_needed( $query ) {
    return (bool) $query->get( '_argon_search' );
}

/**
 * ФИЛЬТР: WHERE — поиск по нескольким словам через AND
 */
function argon_search_ajax_where( $search, $query ) {
    
    if ( ! argon_search_ajax_needed( $query ) ) {
        return $search;
    }
    
    // SQL строится функцией as_search_where_sql() (searchLanguage.php)
    $where = as_search_where_sql( $query->get( 's' ) );
    
    return '' === $where ? $search : $where;
}

/**
 * ФИЛЬТР: JOIN по postmeta для артикула
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
 * ФИЛЬТР: DISTINCT — защита от дубликатов
 */
function argon_search_ajax_distinct( $distinct, $query ) {
    
    if ( ! argon_search_ajax_needed( $query ) ) {
        return $distinct;
    }
    
    return 'DISTINCT';
}

/**
 * ФИЛЬТР: ORDER BY — сортировка по оценке релевантности
 */
function argon_search_ajax_orderby( $orderby, $query ) {
    
    global $wpdb;
    
    if ( ! argon_search_ajax_needed( $query ) ) {
        return $orderby;
    }
    
    $s     = $query->get( 's' );
    $words = function_exists( 'as_search_get_words' ) ? as_search_get_words( $s ) : array();
    
    if ( empty( $words ) ) {
        return $orderby;
    }
    
    // Полная фраза
    $full_trim  = trim( $s );
    $full_exact = $full_trim;
    $full_start = $wpdb->esc_like( $full_trim ) . '%';
    
    $score_parts = array();
    
    $score_parts[] = $wpdb->prepare( "CASE WHEN {$wpdb->posts}.post_title = %s THEN 1000 ELSE 0 END", $full_exact );
    $score_parts[] = $wpdb->prepare( "CASE WHEN as_article_meta.meta_value = %s THEN 800 ELSE 0 END", $full_exact );
    $score_parts[] = $wpdb->prepare( "CASE WHEN {$wpdb->posts}.post_title LIKE %s THEN 500 ELSE 0 END", $full_start );
    $score_parts[] = $wpdb->prepare( "CASE WHEN as_article_meta.meta_value LIKE %s THEN 400 ELSE 0 END", $full_start );
    
    foreach ( $words as $word ) {
        
        $word_start       = $wpdb->esc_like( $word ) . '%';
        $word_like        = '%' . $wpdb->esc_like( $word ) . '%';
        $word_after_space = '% ' . $wpdb->esc_like( $word ) . '%';
        
        $score_parts[] = $wpdb->prepare( "CASE WHEN {$wpdb->posts}.post_title LIKE %s THEN 50 ELSE 0 END", $word_start );
        $score_parts[] = $wpdb->prepare( "CASE WHEN {$wpdb->posts}.post_title LIKE %s THEN 30 ELSE 0 END", $word_after_space );
        $score_parts[] = $wpdb->prepare( "CASE WHEN {$wpdb->posts}.post_title LIKE %s THEN 10 ELSE 0 END", $word_like );
        $score_parts[] = $wpdb->prepare( "CASE WHEN as_article_meta.meta_value LIKE %s THEN 5 ELSE 0 END", $word_like );
    }
    
    $score_sql = '( ' . implode( ' + ', $score_parts ) . ' )';
    
    return "{$score_sql} DESC, {$wpdb->posts}.post_date DESC, {$wpdb->posts}.ID DESC";
}

/*
 ** Построение массивов с информацией для вывода (url/title)
*/

/**
 * ФУНКЦИЯ: подготавливает данные категорий для вывода
 * 
 * ВЫЗЫВАЕТСЯ ИЗ: as_queryManagerSearchResult()
 * ЧТО ДЕЛАЕТ: преобразует термины в массив с заголовком и url
 * 
 * @param array $searchResultCatalog — термины таксономии
 * 
 * @return array Массив с post_title и post_url
 */
function as_prepCatalog( $searchResultCatalog ) {
    
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
 * ФУНКЦИЯ: подготавливает данные записей для вывода
 * 
 * ВЫЗЫВАЕТСЯ ИЗ: as_queryManagerSearchResult()
 * ЧТО ДЕЛАЕТ: преобразует ID записей в массив с заголовком и url
 * 
 * @param array $searchResultPost — массив ID записей
 * 
 * @return array Массив с post_title и post_url
 */
function as_prepPost( $searchResultPost ) {
    
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
 * ФУНКЦИЯ: подготавливает данные товаров для вывода
 * 
 * ВЫЗЫВАЕТСЯ ИЗ: as_queryManagerSearchResult()
 * ЧТО ОН ДЕЛАЕТ:
 * 1. Получает имя и URL товара
 * 2. Получает категорию товара (если есть)
 * 
 * @param array $searchResultProduct — массив объектов WP_Post
 * 
 * @return array Массив с post_title, post_url, category_name, category_url
 */
function as_prepProduct( $searchResultProduct ) {
    
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
 ** Построение HTML-представления результата
*/

/**
 * ФУНКЦИЯ: вывод результатов поиска
 * 
 * ВЫЗЫВАЕТСЯ ИЗ: as_getAjaxSearchResult_callback()
 * ЧТО ДЕЛАЕТ: перебирает все типы результатов и вызывает as_generateResultHTML()
 * 
 * @param array $searchResult — результаты поиска
 */
function as_showResult( $searchResult ) {
    
    foreach ( $searchResult as $keyEntirelyResult => $valueEntirelyResult ) {
        
        // Для записей (несколько блоков)
        if ( $keyEntirelyResult === 'post' ) {
            
            foreach ( $valueEntirelyResult as $keyOneTypeResults => $valueOneTypeResults ) {
                as_generateResultHTML( $keyEntirelyResult, $valueOneTypeResults );
            }
            
        } else {
            // Для товаров и категорий (один блок)
            as_generateResultHTML( $keyEntirelyResult, $valueEntirelyResult );
        }
    }
}

/**
 * ФУНКЦИЯ: генерирует HTML одного блока результатов
 * 
 * ВЫЗЫВАЕТСЯ ИЗ: as_showResult()
 * ЧТО ДЕЛАЕТ: выводит заголовок блока и список найденных элементов
 * 
 * @param string $type                — тип результата
 * @param array  $valueEntirelyResult — данные блока (nameBlock, listResult, classBlock)
 */
function as_generateResultHTML( $type, $valueEntirelyResult ) {
    
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
 * ФУНКЦИЯ: объединяет массивы результатов
 * 
 * ВЫЗЫВАЕТСЯ ИЗ: as_queryProductSearchResult()
 * ЧТО ДЕЛАЕТ: объединяет несколько массивов, отдавая приоритет первым
 * 
 * @param array $typesResultArray — массив массивов для объединения
 * @param int   $quantity         — итоговое количество элементов
 * 
 * @return array Объединённый массив
 */
function as_merge_search_result( $typesResultArray, $quantity ) {
    
    $quantityResults = (int) $quantity;
    $searchStrResult = array();
    
    foreach ( $typesResultArray as $key => $value ) {
        
        if ( isset( $value ) && is_array( $value ) ) {
            
            $searchStrResult = as_array_partial_merge( $quantityResults, $searchStrResult, $value );
            $quantityResults -= count( $value );
        }
    }
    
    return $searchStrResult;
}

/**
 * ФУНКЦИЯ: очищает параметры поиска
 * 
 * ВЫЗЫВАЕТСЯ ИЗ: as_queryManagerSearchResult()
 * ЧТО ДЕЛАЕТ: проверяет входящие параметры, оставляя только корректные
 * 
 * @param object $searchParameters — исходные параметры
 * 
 * @return object Очищенные параметры
 */
function as_cleanParameters( $searchParameters ) {
    
    $cleanSearchParameters = (object) array();
    
    // Очищаем параметр productResult (булев)
    if ( isset( $searchParameters->productResult ) && gettype( $searchParameters->productResult ) === 'boolean' ) {
        $cleanSearchParameters->productResult = $searchParameters->productResult;
    }
    
    // Очищаем параметр catalogResult (булев)
    if ( isset( $searchParameters->catalogResult ) && gettype( $searchParameters->catalogResult ) === 'boolean' ) {
        $cleanSearchParameters->catalogResult = $searchParameters->catalogResult;
    }
    
    // Очищаем параметр postResult (массив)
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
 * ФУНКЦИЯ: исправление раскладки клавиатуры (сохранена для совместимости с темами и старым кодом)
 *
 * Возвращает первый вариант из as_search_layout_variants() (searchLanguage.php),
 * зависящий от языка сайта: на русском сайте «ghjlern» превратится в
 * русское слово «товар», на английском сайте кириллический ввод станет QWERTY,
 * на французском — AZERTY.
 *
 * @param string $searchStr — текст, набранный в неверной раскладке
 *
 * @return string Исправленный текст (или исходный, если исправлять нечего)
 */
function as_fixKeyboardlayout( $searchStr ) {
    
    $variants = as_search_layout_variants( $searchStr );
    
    return $variants ? $variants[0] : (string) $searchStr;
}
