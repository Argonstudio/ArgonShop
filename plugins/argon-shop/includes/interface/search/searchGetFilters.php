<?php
/**
 * Фильтры и сортировка результатов поиска (GET-часть)
 *
 * @package Argon_Shop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 2.0.0
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Форма поиска → ввод запроса → страница /?s=запрос
 * URL: /?s=запрос&post_type[]=product&as_taxonomy[catalog]=all
 * 
 * ЧТО ДЕЛАЕТ:
 * 1. Применяет фильтр по таксономиям из GET-параметров
 *    (as_taxonomy[catalog]=all → искать во всех категориях каталога)
 * 
 * 2. Применяет кастомный поиск:
 *    - Разбивает запрос на слова (AND-логика)
 *    - Ищет в title, excerpt, content, а также в артикуле (_article)
 * 
 * 3. Сортирует по релевантности:
 *    - Точное совпадение заголовка / артикула — максимальный приоритет
 *    - Начало заголовка с запроса
 *    - Вхождение слова в заголовок
 *    - Вхождение слова в артикул
 * 
 * Связь с AJAX-поиском (searchAjax.php):
 * Использует ту же логику приоритетов, что и queryProductSearchResult().
 * Отличается системой очков вместо простого деления на группы.
 * 
 * JOIN на postmeta нужен для доступа к полю _article в WHERE и ORDER BY.
 * DISTINCT защищает от дублирования при JOIN.
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ФУНКЦИЯ: Проверка, применяется ли кастомный поиск к запросу
 *
 * Кастомный поиск применяется только:
 * - на фронтенде
 * - к главному запросу
 * - только в поиске (is_search)
 * - если в post_type участвуют товары (product)
 *
 * @param WP_Query $query — объект запроса WordPress
 *
 * @return bool
 */
function search_is_custom_search( $query ) {
    
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
 * ФУНКЦИЯ: Разбивает поисковый запрос на слова
 *
 * Максимум 10 слов — защита от чрезмерно длинных запросов,
 * которые могут породить тяжёлый SQL.
 *
 * @param string $s — поисковый запрос
 *
 * @return array Массив слов
 */
function search_get_words( $s ) {
    
    $s = trim( (string) $s );
    
    if ( $s === '' ) {
        return array();
    }
    
    $words = preg_split( '/\s+/', $s );
    $words = array_filter( $words );
    $words = array_slice( $words, 0, 10 );
    
    return array_values( $words );
}

/**
 * ФУНКЦИЯ: Исправление раскладки клавиатуры для GET-поиска
 * 
 * Если по поисковому запросу ничего не найдено и запрос набран в
 * неправильной раскладке (например, "ghjlern" вместо "продукт") —
 * автоматически исправляет раскладку и подменяет параметр 's' в
 * основном запросе.
 * 
 * Работает аналогично fixKeyboardlayout() в AJAX-поиске
 * (includes/interface/search/searchAjax.php).
 * 
 * @param WP_Query $query — объект главного запроса WordPress
 */
function argon_search_fix_keyboard_layout( $query ) {
    
    if ( is_admin() || ! $query->is_main_query() || ! $query->is_search ) {
        return;
    }
    
    $s = $query->get( 's' );
    
    if ( empty( $s ) || ! is_string( $s ) || ctype_digit( $s ) ) {
        return;
    }
    
    // Проверяем, есть ли результаты по оригинальному запросу
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
    
    // Функция fixKeyboardlayout() из searchAjax.php
    if ( ! function_exists( 'fixKeyboardlayout' ) ) {
        return;
    }
    
    $fixed = fixKeyboardlayout( $s );
    
    if ( $fixed === $s || empty( $fixed ) ) {
        return;
    }
    
    // Проверяем, есть ли результаты по исправленному запросу
    $test_query2 = new WP_Query( array(
        'post_type'      => 'any',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        's'              => $fixed,
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ) );
    
    if ( $test_query2->have_posts() ) {
        // Подменяем s в основном запросе
        $query->set( 's', $fixed );
        
        // Сохраняем оригинальный запрос для отображения пользователю
        $query->set( '_argon_original_s', $s );
    }
    
    wp_reset_postdata();
}

add_action( 'pre_get_posts', 'argon_search_fix_keyboard_layout', 5 );

// ============================================
// ФИЛЬТР ПО ТАКСОНОМИЯМ (pre_get_posts)
// ============================================

/**
 * ФУНКЦИЯ: Фильтр главного запроса по таксономиям из GET
 *
 * Читает as_taxonomy[] из URL и добавляет tax_query.
 * Значение "all" заменяется на список всех терминов таксономии.
 * Массив ID терминов передаётся из формы через serialize().
 *
 * @param WP_Query $query — объект запроса WordPress
 */
function search_filter_taxonomy( $query ) {
    
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
        
        // "all" — берём все термины таксономии
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
            // Массив ID терминов передан через serialize()
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

add_action( 'pre_get_posts', 'search_filter_taxonomy' );

// ============================================
// JOIN НА POSTMETA ДЛЯ АРТИКУЛА
// ============================================

/**
 * ФУНКЦИЯ: JOIN на wp_postmeta для получения _article
 *
 * Применяется только на кастомном поиске. Позволяет
 * использовать as_article_meta.meta_value в WHERE и ORDER BY.
 *
 * @param string   $join  — текущий JOIN
 * @param WP_Query $query — объект запроса
 *
 * @return string
 */
function search_join_article( $join, $query ) {
    
    global $wpdb;
    
    if ( ! search_is_custom_search( $query ) ) {
        return $join;
    }
    
    $join .= " LEFT JOIN {$wpdb->postmeta} AS as_article_meta 
               ON ( {$wpdb->posts}.ID = as_article_meta.post_id 
                    AND as_article_meta.meta_key = '_article' ) ";
    
    return $join;
}

add_filter( 'posts_join', 'search_join_article', 10, 2 );

// ============================================
// DISTINCT — ЗАЩИТА ОТ ДУБЛИРОВАНИЯ
// ============================================

/**
 * ФУНКЦИЯ: DISTINCT для запроса
 *
 * При JOIN на postmeta один пост может дать несколько строк,
 * если у него несколько записей _article (теоретически).
 * DISTINCT гарантирует уникальность постов в результатах.
 *
 * @param string   $distinct — текущее значение
 * @param WP_Query $query    — объект запроса
 *
 * @return string
 */
function search_distinct( $distinct, $query ) {
    
    if ( ! search_is_custom_search( $query ) ) {
        return $distinct;
    }
    
    return 'DISTINCT';
}

add_filter( 'posts_distinct', 'search_distinct', 10, 2 );

// ============================================
// WHERE — КАСТОМНЫЙ ПОИСК
// ============================================

/**
 * ФУНКЦИЯ: Кастомный WHERE для поиска
 *
 * Заменяет стандартный WP-поиск на многословный:
 * - AND между словами
 * - OR внутри слова (title, excerpt, content, артикул)
 *
 * Пример для запроса "гипсокартон влагостойкий":
 *   (title LIKE '%гипсокартон%' OR ... OR article LIKE '%гипсокартон%')
 *   AND
 *   (title LIKE '%влагостойкий%' OR ... OR article LIKE '%влагостойкий%')
 *
 * @param string   $search — текущий WHERE для поиска
 * @param WP_Query $query  — объект запроса
 *
 * @return string
 */
function search_custom_where( $search, $query ) {
    
    global $wpdb;
    
    if ( ! search_is_custom_search( $query ) ) {
        return $search;
    }
    
    $s = $query->get( 's' );
    $words = search_get_words( $s );
    
    if ( empty( $words ) ) {
        return $search;
    }
    
    $where_parts = array();
    
    foreach ( $words as $word ) {
        
        $like = '%' . $wpdb->esc_like( $word ) . '%';
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

add_filter( 'posts_search', 'search_custom_where', 10, 2 );

// ============================================
// ORDER BY — СОРТИРОВКА ПО РЕЛЕВАНТНОСТИ
// ============================================

/**
 * ФУНКЦИЯ: Сортировка результатов по релевантности
 *
 * Формирует SQL-выражение с суммой очков для каждого поста.
 * Чем больше очков — тем выше пост в результатах.
 *
 * Очки:
 *   1000 — точное совпадение title = полная фраза
 *    800 — точное совпадение артикула = полная фраза
 *    500 — title начинается с полной фразы
 *    400 — артикул начинается с полной фразы
 *     50 — слово запроса в начале title
 *     30 — слово запроса после пробела в title
 *     10 — слово запроса в title (в любом месте)
 *      5 — слово запроса в артикуле
 *
 * При равенстве очков — сортировка по дате DESC.
 *
 * @param string   $orderby — текущий ORDER BY
 * @param WP_Query $query   — объект запроса
 *
 * @return string
 */
function search_custom_orderby( $orderby, $query ) {
    
    global $wpdb;
    
    if ( ! search_is_custom_search( $query ) ) {
        return $orderby;
    }
    
    $s = $query->get( 's' );
    $words = search_get_words( $s );
    
    if ( empty( $words ) ) {
        return $orderby;
    }
    
    // Полная фраза
    $full_trim = trim( $s );
    $full_exact = esc_sql( $full_trim );
    $full_start = esc_sql( $wpdb->esc_like( $full_trim ) . '%' );
    
    $score_parts = array();
    
    // Приоритеты по полной фразе
    $score_parts[] = "CASE WHEN {$wpdb->posts}.post_title = '{$full_exact}' THEN 1000 ELSE 0 END";
    $score_parts[] = "CASE WHEN as_article_meta.meta_value = '{$full_exact}' THEN 800 ELSE 0 END";
    $score_parts[] = "CASE WHEN {$wpdb->posts}.post_title LIKE '{$full_start}' THEN 500 ELSE 0 END";
    $score_parts[] = "CASE WHEN as_article_meta.meta_value LIKE '{$full_start}' THEN 400 ELSE 0 END";
    
    // Приоритеты по каждому слову
    foreach ( $words as $word ) {
        
        $word_start = esc_sql( $wpdb->esc_like( $word ) . '%' );
        $word_like  = esc_sql( '%' . $wpdb->esc_like( $word ) . '%' );
        $word_after_space = esc_sql( '% ' . $wpdb->esc_like( $word ) . '%' );
        
        // Слово в начале title
        $score_parts[] = "CASE WHEN {$wpdb->posts}.post_title LIKE '{$word_start}' THEN 50 ELSE 0 END";
        
        // Слово после пробела в title
        $score_parts[] = "CASE WHEN {$wpdb->posts}.post_title LIKE '{$word_after_space}' THEN 30 ELSE 0 END";
        
        // Слово в title (в любом месте)
        $score_parts[] = "CASE WHEN {$wpdb->posts}.post_title LIKE '{$word_like}' THEN 10 ELSE 0 END";
        
        // Слово в артикуле
        $score_parts[] = "CASE WHEN as_article_meta.meta_value LIKE '{$word_like}' THEN 5 ELSE 0 END";
    }
    
    $score_sql = '( ' . implode( ' + ', $score_parts ) . ' )';
    
    return "{$score_sql} DESC, {$wpdb->posts}.post_date DESC, {$wpdb->posts}.ID DESC";
}

add_filter( 'posts_orderby', 'search_custom_orderby', 10, 2 );