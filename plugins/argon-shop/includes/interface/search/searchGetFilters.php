<?php
/**
 * Фильтры и сортировка результатов поиска (GET-часть)
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Форма поиска → ввод запроса → страница /?s=query
 * URL: /?s=query&post_type[]=product&as_taxonomy[catalog]=all
 * 
 * ЧТО ОН ДЕЛАЕТ:
 * 1. Применяет фильтр по таксономии из GET-параметров
 *    (as_taxonomy[catalog]=all → поиск по всем категориям каталога)
 * 
 * 2. Применяет пользовательский поиск:
 *    - Разбивает запрос на слова (логика AND; языковые правила — searchLanguage.php)
 *    - Ищет по заголовку, анонсу, контенту, а также по артикулу (_article)
 * 
 * 3. Сортирует по релевантности:
 *    - Точное совпадение заголовка / артикула — высший приоритет
 *    - Заголовок, начинающийся с запроса
 *    - Слово, встречающееся в заголовке
 *    - Слово, встречающееся в артикуле
 * 
 * Связь с AJAX-поиском (searchAjax.php):
 * Использует ту же логику приоритетов, что и as_queryProductSearchResult().
 * Отличается использованием системы баллов вместо простой группировки.
 * 
 * JOIN по postmeta нужен для доступа к полю _article в WHERE и ORDER BY.
 * DISTINCT защищает от дубликатов, вызванных JOIN.
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ФУНКЦИЯ: проверяет, применяется ли пользовательский поиск к запросу
 *
 * Пользовательский поиск применяется только:
 * - на фронтенде
 * - к основному запросу
 * - только при поиске (is_search)
 * - если товары (product) есть среди типов записей
 *
 * @param WP_Query $query — объект запроса WordPress
 *
 * @return bool
 */
function as_search_is_custom_search( $query ) {
    
    if ( is_admin() || ! $query->is_main_query() ) {
        return false;
    }
    
    if ( ! $query->is_search ) {
        return false;
    }
    
    $s = $query->get( 's' );
    
    if ( empty( $s ) || ! is_string( $s ) ) {
        return false;
    }
    
    // Если post_type ограничен и это не product — не применяем
    $post_types = $query->get( 'post_type' );
    
    if ( ! empty( $post_types ) && $post_types !== 'any' ) {
        
        $post_types = (array) $post_types;
        
        if ( ! in_array( 'product', $post_types, true ) ) {
            return false;
        }
    }
    
    return true;
}

/**
 * ФУНКЦИЯ: исправление раскладки клавиатуры для GET-поиска
 * 
 * Если запрос ничего не нашёл и был набран в неверной
 * раскладке (например, «ghjlern» набран на русском сайте, или «super» набран в русской раскладке на английском) —
 * автоматически исправляет раскладку и заменяет параметр 's' в
 * основном запросе.
 * 
 * Работает как AJAX-поиск; варианты берутся из as_search_layout_variants()
 * (includes/interface/search/searchLanguage.php).
 * 
 * @param WP_Query $query — объект основного запроса WordPress
 */
function argon_search_fix_keyboard_layout( $query ) {
    
    if ( is_admin() || ! $query->is_main_query() || ! $query->is_search ) {
        return;
    }
    
    $s = $query->get( 's' );
    
    if ( empty( $s ) || ! is_string( $s ) || ctype_digit( $s ) ) {
        return;
    }
    
    // Проверяем, есть ли результаты у исходного запроса
    $test_query = new WP_Query( array(
        'post_type'      => 'any',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        's'              => $s,
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ) );
    
    if ( $test_query->have_posts() ) {
        return;
    }
    
    wp_reset_postdata();
    
    // Варианты зависят от языка сайта (searchLanguage.php)
    if ( ! function_exists( 'as_search_layout_variants' ) ) {
        return;
    }
    
    foreach ( as_search_layout_variants( $s ) as $fixed ) {
        
        // Проверяем, есть ли результаты у исправленного запроса
        $test_query2 = new WP_Query( array(
            'post_type'      => 'any',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            's'              => $fixed,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ) );
        
        if ( $test_query2->have_posts() ) {
            // Заменяем s в основном запросе
            $query->set( 's', $fixed );
            
            // Сохраняем исходный запрос, чтобы показать его пользователю
            $query->set( '_argon_original_s', $s );
            
            break;
        }
    }
    
    wp_reset_postdata();
}

add_action( 'pre_get_posts', 'argon_search_fix_keyboard_layout', 5 );

// ============================================
// ФИЛЬТР ПО ТАКСОНОМИИ (pre_get_posts)
// ============================================

/**
 * ФУНКЦИЯ: фильтрует основной запрос по таксономиям из GET
 *
 * Читает as_taxonomy[] из URL и добавляет tax_query.
 * Значение «all» заменяется списком всех терминов таксономии.
 * Массив ID терминов передаётся из формы через serialize().
 *
 * @param WP_Query $query — объект запроса WordPress
 */
function as_search_filter_taxonomy( $query ) {
    
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }
    
    if ( ! $query->is_search ) {
        return;
    }
    
    if ( ! isset( $_GET['post_type'] ) || ! isset( $_GET['as_taxonomy'] ) ) {
        return;
    }
    
    $taxonomy = $_GET['as_taxonomy'];
    
    if ( ! is_array( $taxonomy ) ) {
        return;
    }
    
    $tax_query = array( 'relation' => 'OR' );
    
    foreach ( $taxonomy as $key => $value ) {
        
        $key = sanitize_key( $key );
        
        if ( empty( $key ) || ! taxonomy_exists( $key ) ) {
            continue;
        }
        
        $taxonomy_query = array(
            'taxonomy' => $key,
            'field'    => 'id',
        );
        
        $taxonomy_terms = array();
        
        // «all» — берём все термины таксономии
        if ( $value === 'all' ) {
            
            $args = array(
                'taxonomy' => $key,
                'fields'   => 'ids',
                'get'      => 'all',
            );
            
            $taxonomy_terms = get_terms( $args );
            
            if ( is_wp_error( $taxonomy_terms ) ) {
                continue;
            }
            
        } else {
            // Массив ID терминов передаётся через serialize()
            $value = wp_unslash( $value );
            
            if ( strlen( $value ) > 10000 ) {
                continue;
            }
            
            $unserialized = @unserialize( $value, array( 'allowed_classes' => false ) );
            
            if ( is_array( $unserialized ) ) {
                $taxonomy_terms = array_map( 'absint', $unserialized );
            }
        }
        
        if ( empty( $taxonomy_terms ) ) {
            continue;
        }
        
        $taxonomy_query['terms'] = $taxonomy_terms;
        
        array_push( $tax_query, $taxonomy_query );
    }
    
    if ( count( $tax_query ) <= 1 ) {
        return;
    }
    
    $query->set( 'tax_query', $tax_query );
}

add_action( 'pre_get_posts', 'as_search_filter_taxonomy' );

// ============================================
// JOIN ПО POSTMETA ДЛЯ АРТИКУЛА
// ============================================

/**
 * ФУНКЦИЯ: JOIN с wp_postmeta для получения _article
 *
 * Применяется только к пользовательскому поиску. Позволяет
 * использовать as_article_meta.meta_value в WHERE и ORDER BY.
 *
 * @param string   $join  — текущий JOIN
 * @param WP_Query $query — объект запроса
 *
 * @return string
 */
function as_search_join_article( $join, $query ) {
    
    global $wpdb;
    
    if ( ! as_search_is_custom_search( $query ) ) {
        return $join;
    }
    
    $join .= " LEFT JOIN {$wpdb->postmeta} AS as_article_meta 
               ON ( {$wpdb->posts}.ID = as_article_meta.post_id 
                    AND as_article_meta.meta_key = '_article' ) ";
    
    return $join;
}

add_filter( 'posts_join', 'as_search_join_article', 10, 2 );

// ============================================
// DISTINCT — ЗАЩИТА ОТ ДУБЛИКАТОВ
// ============================================

/**
 * ФУНКЦИЯ: DISTINCT для запроса
 *
 * При JOIN с postmeta одна запись может дать несколько строк,
 * если у неё несколько записей _article (теоретически).
 * DISTINCT гарантирует уникальность записей в результатах.
 *
 * @param string   $distinct — текущее значение
 * @param WP_Query $query    — объект запроса
 *
 * @return string
 */
function as_search_distinct( $distinct, $query ) {
    
    if ( ! as_search_is_custom_search( $query ) ) {
        return $distinct;
    }
    
    return 'DISTINCT';
}

add_filter( 'posts_distinct', 'as_search_distinct', 10, 2 );

// ============================================
// WHERE — ПОЛЬЗОВАТЕЛЬСКИЙ ПОИСК
// ============================================

/**
 * ФУНКЦИЯ: пользовательский WHERE для поиска
 *
 * Заменяет стандартный поиск WP на многословный:
 * - AND между словами
 * - OR внутри слова (заголовок, анонс, контент, артикул, варианты написания)
 *
 * Пример для запроса «moisture resistant drywall»:
 *   (title LIKE '%moisture%' OR ... OR article LIKE '%moisture%')
 *   AND
 *   (title LIKE '%drywall%' OR ... OR article LIKE '%drywall%')
 *
 * @param string   $search — текущий WHERE поиска
 * @param WP_Query $query  — объект запроса
 *
 * @return string
 */
function as_search_custom_where( $search, $query ) {
    
    if ( ! as_search_is_custom_search( $query ) ) {
        return $search;
    }
    
    // SQL строится функцией as_search_where_sql() (searchLanguage.php)
    $where = as_search_where_sql( $query->get( 's' ) );
    
    return '' === $where ? $search : $where;
}

add_filter( 'posts_search', 'as_search_custom_where', 10, 2 );

// ============================================
// ORDER BY — СОРТИРОВКА ПО РЕЛЕВАНТНОСТИ
// ============================================

/**
 * ФУНКЦИЯ: сортирует результаты по релевантности
 *
 * Строит SQL-выражение с суммой баллов для каждой записи.
 * Чем больше баллов, тем выше запись в результатах.
 *
 * Баллы:
 *   1000 — точное совпадение заголовка = полная фраза
 *    800 — точное совпадение артикула = полная фраза
 *    500 — заголовок начинается с полной фразы
 *    400 — артикул начинается с полной фразы
 *     50 — слово запроса в начале заголовка
 *     30 — слово запроса после пробела в заголовке
 *     10 — слово запроса в заголовке (в любом месте)
 *      5 — слово запроса в артикуле
 *
 * При равных баллах — сортировка по дате DESC.
 *
 * @param string   $orderby — текущий ORDER BY
 * @param WP_Query $query   — объект запроса
 *
 * @return string
 */
function as_search_custom_orderby( $orderby, $query ) {
    
    global $wpdb;
    
    if ( ! as_search_is_custom_search( $query ) ) {
        return $orderby;
    }
    
    $s = $query->get( 's' );
    $words = as_search_get_words( $s );
    
    if ( empty( $words ) ) {
        return $orderby;
    }
    
    // Полная фраза
    $full_trim = trim( $s );
    $full_exact = $full_trim;
    $full_start = $wpdb->esc_like( $full_trim ) . '%';
    
    $score_parts = array();
    
    // Приоритеты полной фразы
    $score_parts[] = $wpdb->prepare( "CASE WHEN {$wpdb->posts}.post_title = %s THEN 1000 ELSE 0 END", $full_exact );
    $score_parts[] = $wpdb->prepare( "CASE WHEN as_article_meta.meta_value = %s THEN 800 ELSE 0 END", $full_exact );
    $score_parts[] = $wpdb->prepare( "CASE WHEN {$wpdb->posts}.post_title LIKE %s THEN 500 ELSE 0 END", $full_start );
    $score_parts[] = $wpdb->prepare( "CASE WHEN as_article_meta.meta_value LIKE %s THEN 400 ELSE 0 END", $full_start );
    
    // Приоритеты отдельных слов
    foreach ( $words as $word ) {
        
        $word_start       = $wpdb->esc_like( $word ) . '%';
        $word_like        = '%' . $wpdb->esc_like( $word ) . '%';
        $word_after_space = '% ' . $wpdb->esc_like( $word ) . '%';
        
        // Слово в начале заголовка
        $score_parts[] = $wpdb->prepare( "CASE WHEN {$wpdb->posts}.post_title LIKE %s THEN 50 ELSE 0 END", $word_start );
        
        // Слово после пробела в заголовке
        $score_parts[] = $wpdb->prepare( "CASE WHEN {$wpdb->posts}.post_title LIKE %s THEN 30 ELSE 0 END", $word_after_space );
        
        // Слово в заголовке (в любом месте)
        $score_parts[] = $wpdb->prepare( "CASE WHEN {$wpdb->posts}.post_title LIKE %s THEN 10 ELSE 0 END", $word_like );
        
        // Слово в артикуле
        $score_parts[] = $wpdb->prepare( "CASE WHEN as_article_meta.meta_value LIKE %s THEN 5 ELSE 0 END", $word_like );
    }
    
    $score_sql = '( ' . implode( ' + ', $score_parts ) . ' )';
    
    return "{$score_sql} DESC, {$wpdb->posts}.post_date DESC, {$wpdb->posts}.ID DESC";
}

add_filter( 'posts_orderby', 'as_search_custom_orderby', 10, 2 );