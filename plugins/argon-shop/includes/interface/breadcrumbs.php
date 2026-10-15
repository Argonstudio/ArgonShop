<?php
/**
 * Хлебные крошки для WordPress
 *
 * Оригинальный автор: Kama (Тимур Камаев)
 * Source: https://wp-kama.ru/
 * Original version: 3.3.1
 *
 * @package Argon_Shop
 * @author  Kama (original author)
 * @author  Ivan Voitkov — security fixes and PHP 8.5 compatibility
 * Версия этой копии = версия плагина (ARGON_SHOP_VERSION).
 * 
 * ИЗМЕНЕНИЯ ARGON SHOP:
 * 1. Добавлена защита от прямого доступа к файлу (ABSPATH)
 * 2. Безопасное получение поискового запроса через get_search_query()
 *     вместо прямого обращения к $GLOBALS['s']
 * 3. Добавлены проверки null / WP_Error / false для:
 *     - get_queried_object()
 *     - get_taxonomy()
 *     - get_post()
 *     - get_term()
 *     - get_term_link()
 *     - get_permalink()
 *     - get_the_terms()
 * 4. Исправлены предупреждения Deprecated в PHP 8.1+:
 *     - strpos() с null в качестве первого аргумента
 *     - array_shift() на пустом массиве
 *     - обращение к свойствам null-объектов
 * 5. Совместимость с PHP 8.5:
 *     - явные проверки типов
 *     - безопасный доступ к массивам
 *     - строгие сравнения, где возможно
 *
 * @param  string [$sep  = '']      Разделитель. По умолчанию ' » '
 * @param  array  [$l10n = array()] Для локализации. См. переменную $default_l10n.
 * @param  array  [$args = array()] Параметры. См. переменную $def_args
 * @return string Выводит HTML-код
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function AS_Kama_Breadcrumbs( $sep = ' » ', $l10n = array(), $args = array() ){
    $kb = new AS_Kama_Breadcrumbs;
    
    $breadCrumbs = $kb->get_crumbs( $sep, $l10n, $args );

    echo $breadCrumbs;
}

class AS_Kama_Breadcrumbs {

    public $arg;

    // Локализация. Строки переводятся в рантайме (статическое свойство не может вызывать __()).
    static $l10n = array();

    /**
     * Строки перевода по умолчанию.
     *
     * @since 1.1.0
     * @return array
     */
    static function default_l10n() {
        return array(
            'home'       => __( 'Home', 'argon-shop' ),
            /* translators: %d: номер страницы. */
            'paged'      => __( 'Page %d', 'argon-shop' ),
            '_404'       => __( 'Error 404', 'argon-shop' ),
            /* translators: %s: поисковый запрос. */
            'search'     => __( 'Search results for - <b>%s</b>', 'argon-shop' ),
            'search2'    => __( 'Search', 'argon-shop' ),
            /* translators: %s: имя автора. */
            'author'     => __( 'Author archive: <b>%s</b>', 'argon-shop' ),
            /* translators: %d: год. */
            'year'       => __( 'Archive for <b>%d</b>', 'argon-shop' ),
            /* translators: %s: месяц и год. */
            'month'      => __( 'Archive for: <b>%s</b>', 'argon-shop' ),
            'day'        => '',
            /* translators: %s: заголовок вложения. */
            'attachment' => __( 'Media: %s', 'argon-shop' ),
            /* translators: %s: имя тега. */
            'tag'        => __( 'Posts tagged: <b>%s</b>', 'argon-shop' ),
            /* translators: 1: подпись типа записи, 2: термин таксономии, 3: тег. */
            'tax_tag'    => __( '%1$s from "%2$s" tagged: <b>%3$s</b>', 'argon-shop' ),
        );
    }

    // Параметры по умолчанию
    static $args = array(
        'on_front_page'   => true,
        'show_post_title' => true,
        'show_term_title' => true,
        'title_patt'      => '<span class="kb_title">%s</span>',
        'last_sep'        => true,
        'markup'          => 'schema.org',
        'priority_tax'    => array('category'),
        'priority_terms'  => array(),
        'nofollow' => false,

        // внутреннее
        'sep'             => '',
        'linkpatt'        => '',
        'pg_end'          => '',
    );

    function get_crumbs( $sep, $l10n, $args ){
        
        global $post, $wp_query, $wp_post_types;

        self::$args['sep'] = $sep;

        // Фильтруем значения по умолчанию и объединяем
        $loc = (object) array_merge( apply_filters('kama_breadcrumbs_default_loc', array_merge( self::default_l10n(), self::$l10n ) ), $l10n );
        $arg = (object) array_merge( apply_filters('kama_breadcrumbs_default_args', self::$args ), $args );
        
        $arg->sep = '<span class="kb_sep">'. $arg->sep .'</span>';

        // упрощение
        $sep = & $arg->sep;
        $this->arg = & $arg;

        // microdata ---
        if(1){
            $mark = & $arg->markup;

            // Разметка по умолчанию
            if( ! $mark ) $mark = array(
                'wrappatt'  => '<div class="kama_breadcrumbs">%s</div>',
                'linkpatt'  => '<a href="%s">%s</a>',
                'sep_after' => '',
            );
            // rdf
            elseif( $mark === 'rdf.data-vocabulary.org' ) $mark = array(
                'wrappatt'   => '<div class="kama_breadcrumbs" prefix="v: http://rdf.data-vocabulary.org/#">%s</div>',
                'linkpatt'   => '<span typeof="v:Breadcrumb"><a href="%s" rel="v:url" property="v:title">%s</a>',
                'sep_after'  => '</span>',
            );
            // schema.org
            elseif( $mark === 'schema.org' ) $mark = array(
                'wrappatt'   => '<div class="kama_breadcrumbs" itemscope itemtype="http://schema.org/BreadcrumbList">%s</div>',
                'linkpatt'   => '<span itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a href="%s" itemprop="item"><span itemprop="name">%s</span></a></span>',
                'sep_after'  => '',
            );

            elseif( ! is_array($mark) )
                die( __CLASS__ .': "markup" parameter must be array...');

            $wrappatt  = $mark['wrappatt'];
            $arg->linkpatt  = $arg->nofollow ? str_replace('<a ','<a rel="nofollow"', $mark['linkpatt']) : $mark['linkpatt'];
            $arg->sep      .= $mark['sep_after']."\n";
        }

        $linkpatt = $arg->linkpatt;

        $q_obj = get_queried_object();

        // возможно, это архив пустой таксономии?
        $ptype = null;
        if( empty($post) ){
            if( isset($q_obj->taxonomy) ){
                // PHP 8.5: проверяем результат get_taxonomy() и наличие object_type
                $tax_obj = get_taxonomy( $q_obj->taxonomy );
                
                if ( $tax_obj && ! empty( $tax_obj->object_type[0] ) && isset( $wp_post_types[ $tax_obj->object_type[0] ] ) ) {
                    $ptype = & $wp_post_types[ $tax_obj->object_type[0] ];
                }
            }
        }
        elseif( isset( $post->post_type ) && isset( $wp_post_types[ $post->post_type ] ) ) {
            $ptype = & $wp_post_types[ $post->post_type ];
        }

        // paged
        $arg->pg_end = '';
        $paged_num = get_query_var('paged') ?: get_query_var('page');
        
        if( $paged_num ) {
            $arg->pg_end = $sep . sprintf( $loc->paged, (int) $paged_num );
        }

        $pg_end = $arg->pg_end;

        // поехали...
        $out = '';

        if( is_front_page() ){
            return $arg->on_front_page ? sprintf( $wrappatt, ( $paged_num ? sprintf($linkpatt, get_home_url(), $loc->home) . $pg_end : $loc->home ) ) : '';
        }
        // страница записей, когда главной страницей назначена отдельная страница.
        elseif( is_home() ) {
            // PHP 8.5: убеждаемся, что $q_obj существует
            if ( ! $q_obj || ! isset( $q_obj->post_title ) ) {
                return '';
            }
            
            $out = $paged_num ? ( sprintf( $linkpatt, get_permalink($q_obj), esc_html($q_obj->post_title) ) . $pg_end ) : esc_html($q_obj->post_title);
        }
        elseif( is_404() ){
            $out = $loc->_404;
        }
        elseif( is_search() ){
            // PHP 8.5: безопасное получение поискового запроса
            $search_query = get_search_query();
            
            if( $search_query ){
                $out = sprintf( $loc->search, esc_html( $search_query ) );
            } else {
                $out = $loc->search2;
            }
        }
        elseif( is_author() ){
            // PHP 8.5: проверяем $q_obj
            if ( ! $q_obj || ! isset( $q_obj->display_name ) ) {
                return '';
            }
            
            $tit = sprintf( $loc->author, esc_html($q_obj->display_name) );
            $out = ( $paged_num ? sprintf( $linkpatt, get_author_posts_url( $q_obj->ID, $q_obj->user_nicename ) . $pg_end, $tit ) : $tit );
        }
        elseif( is_year() || is_month() || is_day() ){
            $y_url  = get_year_link( $year = get_the_time('Y') );

            if( is_year() ){
                $tit = sprintf( $loc->year, $year );
                $out = ( $paged_num ? sprintf($linkpatt, $y_url, $tit) . $pg_end : $tit );
            }
            // месяц день
            else {
                $y_link = sprintf( $linkpatt, $y_url, $year);
                $m_url  = get_month_link( $year, get_the_time('m') );

                if( is_month() ){
                    $tit = sprintf( $loc->month, get_the_time('F') );
                    $out = $y_link . $sep . ( $paged_num ? sprintf( $linkpatt, $m_url, $tit ) . $pg_end : $tit );
                }
                elseif( is_day() ){
                    $m_link = sprintf( $linkpatt, $m_url, get_the_time('F'));
                    $out = $y_link . $sep . $m_link . $sep . get_the_time('l');
                }
            }
        }
        
        // Иерархические записи
        // PHP 8.5: проверяем $ptype перед обращением
        elseif( is_singular() && $ptype && ! empty( $ptype->hierarchical ) ){
            $out = $this->_add_title( $this->_page_crumbs($post), $post );
        }
        // Таксономии, плоские записи и вложения
        else {
            $term = $q_obj;

            // определяем термин для записей (включая вложения)
            if( is_singular() ){
                
                // PHP 8.5: проверяем $post
                if ( ! $post ) {
                    return '';
                }
                
                // меняем $post, чтобы определить термин родителя вложения
                if( is_attachment() && ! empty( $post->post_parent ) ){
                    $save_post = $post;
                    $post = get_post($post->post_parent);
                    
                    if ( ! $post ) {
                        $post = $save_post;
                        unset( $save_post );
                    }
                }

                // учитывает вложения, прикреплённые к иерархическим таксономиям
                $taxonomies = get_object_taxonomies( $post->post_type );
                $taxonomies = array_intersect( $taxonomies, get_taxonomies( array('hierarchical' => true, 'public' => true) ) );

                if( $taxonomies ){
                    // сортируем по приоритету
                    if( ! empty($arg->priority_tax) ){
                        usort( $taxonomies, function($a,$b)use($arg){
                            $a_index = array_search($a, $arg->priority_tax);
                            if( $a_index === false ) $a_index = 9999999;

                            $b_index = array_search($b, $arg->priority_tax);
                            if( $b_index === false ) $b_index = 9999999;

                            return ( $b_index === $a_index ) ? 0 : ( $b_index < $a_index ? 1 : -1 );
                        } );
                    }

                    // пытаемся получить термины в порядке приоритета таксономий
                    foreach( $taxonomies as $taxname ){
                        
                        $terms = get_the_terms( $post->ID, $taxname );
                        
                        // PHP 8.5: проверяем WP_Error и пустой массив
                        if ( ! $terms || is_wp_error( $terms ) || empty( $terms ) ) {
                            continue;
                        }
                        
                        // PHP 8.5: проверяем существование ключа перед обращением
                        $prior_terms = isset( $arg->priority_terms[ $taxname ] ) ? $arg->priority_terms[ $taxname ] : null;
                        
                        if( $prior_terms && count($terms) > 2 ){
                            foreach( (array) $prior_terms as $term_id ){
                                $filter_field = is_numeric($term_id) ? 'term_id' : 'slug';
                                $_terms = wp_list_filter( $terms, array($filter_field=>$term_id) );

                                if( $_terms && ! empty( $_terms ) ){
                                    $term = array_shift( $_terms );
                                    break;
                                }
                            }
                        }
                        else {
                            // PHP 8.5: array_shift только если массив не пуст
                            if ( ! empty( $terms ) ) {
                                $term = array_shift( $terms );
                            }
                        }

                        break;
                    }
                }

                if( isset($save_post) ) $post = $save_post;
            }

            // вывод
            // все виды записей с терминами, или термины
            if( $term && isset($term->term_id) ){
                $term = apply_filters('kama_breadcrumbs_term', $term );

                // вложение
                if( is_attachment() ){
                    if( empty( $post->post_parent ) )
                        $out = sprintf( $loc->attachment, esc_html($post->post_title) );
                    else {
                        if( ! $out = apply_filters('attachment_tax_crumbs', '', $term, $this ) ){
                            $_crumbs    = $this->_tax_crumbs( $term, 'self' );
                            $parent_tit = sprintf( $linkpatt, get_permalink($post->post_parent), get_the_title($post->post_parent) );
                            $_out = implode( $sep, array($_crumbs, $parent_tit) );
                            $out = $this->_add_title( $_out, $post );
                        }
                    }
                }
                // одиночная запись
                elseif( is_single() ){
                    if( ! $out = apply_filters('post_tax_crumbs', '', $term, $this ) ){
                        $_crumbs = $this->_tax_crumbs( $term, 'self' );
                        $out = $this->_add_title( $_crumbs, $post );
                    }
                }
                // неиерархическая таксономия (теги)
                elseif( ! is_taxonomy_hierarchical($term->taxonomy) ){
                    // тег
                    if( is_tag() )
                        $out = $this->_add_title('', $term, sprintf( $loc->tag, esc_html($term->name) ) );
                    // таксономия
                    elseif( is_tax() ){
                        $post_label = $ptype && ! empty( $ptype->labels->name ) ? $ptype->labels->name : '';
                        $tax_label = isset( $GLOBALS['wp_taxonomies'][ $term->taxonomy ]->labels->name ) ? $GLOBALS['wp_taxonomies'][ $term->taxonomy ]->labels->name : '';
                        $out = $this->_add_title('', $term, sprintf( $loc->tax_tag, $post_label, $tax_label, esc_html($term->name) ) );
                    }
                }
                // иерархическая таксономия (категории)
                else {
                    if( ! $out = apply_filters('term_tax_crumbs', '', $term, $this ) ){
                        $_crumbs = $this->_tax_crumbs( $term, 'parent' );
                        $out = $this->_add_title( $_crumbs, $term, esc_html($term->name) );
                    }
                }
            }
            // вложения записи без терминов
            elseif( is_attachment() ){
                $parent = get_post($post->post_parent);
                
                // PHP 8.5: проверяем $parent
                if ( ! $parent ) {
                    return '';
                }
                
                $parent_link = sprintf( $linkpatt, get_permalink($parent), esc_html($parent->post_title) );
                $_out = $parent_link;

                // вложение записи иерархического типа
                if( is_post_type_hierarchical($parent->post_type) ){
                    $parent_crumbs = $this->_page_crumbs($parent);
                    $_out = implode( $sep, array( $parent_crumbs, $parent_link ) );
                }

                $out = $this->_add_title( $_out, $post );
            }
            // записи без терминов
            elseif( is_singular() ){
                $out = $this->_add_title( '', $post );
            }
        }

        // заменяем ссылку страницей архива типа записи
        $home_after = apply_filters('kama_breadcrumbs_home_after', '', $linkpatt, $sep, $ptype );

        if( '' === $home_after ){
            // Ссылка на страницу архива типа записи
            // PHP 8.5: проверяем $term перед обращением к его свойствам
            $is_tax_with_ptype = is_tax() && $term && isset( $term->taxonomy ) && ! empty( $ptype->taxonomies ) && in_array( $term->taxonomy, $ptype->taxonomies );
            
            if( $ptype && ! empty( $ptype->has_archive ) && ! in_array( $ptype->name, array('post','page','attachment'), true )
                && ( is_post_type_archive() || is_singular() || $is_tax_with_ptype )
            ){
                $pt_title = ! empty( $ptype->labels->name ) ? $ptype->labels->name : '';

                // первая страница архива типа записи
                if( is_post_type_archive() && ! $paged_num )
                    $home_after = $pt_title;
                // singular, paged post_type_archive, tax
                else{
                    $home_after = sprintf( $linkpatt, get_post_type_archive_link($ptype->name), $pt_title );

                    $home_after .= ( ($paged_num && ! is_tax()) ? $pg_end : $sep );
                }
            }
        }
        
        // PHP 8.5: strpos с null вызывает Deprecated, поэтому сначала проверяем
        $home_after_string = $home_after ? $home_after : '';
        
        // Убираем ссылку на страницу товаров со страниц записей
        if( strpos( $home_after_string, __( 'Products', 'argon-shop' ) ) !== false ){
            
            $before_out = sprintf( $linkpatt, home_url(), $loc->home ) . ( $out ? $sep : '' );
            
        }else{
            
            $before_out = sprintf( $linkpatt, home_url(), $loc->home ) . ( $home_after_string ? $sep.$home_after_string : ($out ? $sep : '') );
        }
        
        $out = apply_filters('kama_breadcrumbs_pre_out', $out, $sep, $loc, $arg );

        $out = sprintf( $wrappatt, $before_out . $out );

        return apply_filters('kama_breadcrumbs', $out, $sep, $loc, $arg );
    }

    function _page_crumbs( $post ){
        
        // PHP 8.5: проверяем $post
        if ( ! $post || ! isset( $post->post_parent ) ) {
            return '';
        }
        
        $parent = $post->post_parent;

        $crumbs = array();
        while( $parent ){
            $page = get_post( $parent );
            
            // PHP 8.5: проверяем результат get_post()
            if ( ! $page ) {
                break;
            }
            
            $crumbs[] = sprintf( $this->arg->linkpatt, get_permalink($page), esc_html($page->post_title) );
            $parent = $page->post_parent;
        }

        return implode( $this->arg->sep, array_reverse($crumbs) );
    }

    function _tax_crumbs( $term, $start_from = 'self' ){
        $termlinks = array();
        $term_id = ($start_from === 'parent') ? $term->parent : $term->term_id;
        
        while( $term_id ){
            $term = get_term( $term_id, $term->taxonomy );
            
            // PHP 8.5: проверяем результат get_term()
            if ( ! $term || is_wp_error( $term ) ) {
                break;
            }
            
            $term_link = get_term_link( $term );
            
            // PHP 8.5: проверяем get_term_link()
            if ( is_wp_error( $term_link ) ) {
                $term_id = $term->parent;
                continue;
            }
            
            $termlinks[] = sprintf( $this->arg->linkpatt, $term_link, esc_html($term->name) );
            $term_id    = $term->parent;
        }

        if( $termlinks )
            return implode( $this->arg->sep, array_reverse($termlinks) );
        return '';
    }

    // добавляет заголовок к переданному тексту с учётом всех параметров.
    function _add_title( $add_to, $obj, $term_title = '' ){
        $arg = & $this->arg;
        
        // PHP 8.5: проверяем $obj перед обращением к свойству
        if ( $term_title ) {
            $title = $term_title;
        } elseif ( $obj && isset( $obj->post_title ) ) {
            $title = esc_html($obj->post_title);
        } else {
            $title = '';
        }
        
        $show_title = $term_title ? $arg->show_term_title : $arg->show_post_title;

        // пагинация
        if( $arg->pg_end ){
            $link = $term_title ? get_term_link($obj) : get_permalink($obj);
            
            // PHP 8.5: проверяем get_term_link / get_permalink
            if ( is_wp_error( $link ) ) {
                $link = '';
            }
            
            $add_to .= ($add_to ? $arg->sep : '') . sprintf( $arg->linkpatt, $link, $title ) . $arg->pg_end;
        }
        // добавляем — вставляем sep
        elseif( $add_to ){
            if( $show_title )
                $add_to .= $arg->sep . sprintf( $arg->title_patt, $title );
            elseif( $arg->last_sep )
                $add_to .= $arg->sep;
        }
        // sep появится позже...
        elseif( $show_title )
            $add_to = sprintf( $arg->title_patt, $title );

        return $add_to;
    }

}

/**
 * Изменения:
 * 3.3 — новые хуки: attachment_tax_crumbs, post_tax_crumbs, term_tax_crumbs. Они позволяют расширять крошки таксономий.
 * 3.2 — исправлена ошибка разделителя при выключенном 'show_term_title'. Стабилизирована логика.
 * 3.1 — ошибка esc_html() для заголовков терминов — теги отображались некорректно...
 * 3.0 — обёрнуто в класс. Добавлены опции: 'title_patt', 'last_sep'. Улучшен код. Добавлена пагинация для записей.
 * 2.5 — ДОБАВЛЕНО: опция 'show_term_title'
 * 2.4 — Мелкие исправления кода
 * 2.3 — ДОБАВЛЕНО: страница записей, когда главной страницей назначена отдельная страница.
 * 2.2 — ДОБАВЛЕНО: ссылка на архив типа записи на странице таксономии
 * 2.1 — ДОБАВЛЕНО: параметры $sep, $loc, $args в хуки
 * 2.0 — ДОБАВЛЕНО: четвёртый аргумент $ptype добавлен в фильтр 'kama_breadcrumbs_home_after'
 * 1.9 — ДОБАВЛЕНО: фильтр 'kama_breadcrumbs_default_loc' для смены локализации по умолчанию
 * 1.8 — ИСПРАВЛЕНО: заметки, когда в категории нет записей
 * 1.7 — Улучшена обработка приоритетных таксономий.
 * 
 * Argon Shop (3.3.2-argon):
 * - Добавлена защита ABSPATH
 * - Совместимость с PHP 8.5
 * - проверки null / WP_Error / false
 */