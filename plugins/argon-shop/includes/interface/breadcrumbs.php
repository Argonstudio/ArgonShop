<?php
/**
 * Хлебные крошки для WordPress (breadcrumbs)
 *
 * Оригинальный автор: Kama (Тимур Камаев)
 * Источник: https://wp-kama.ru/
 * Оригинальная версия: 3.3.1
 *
 * @package Argon_Shop
 * @author  Kama (оригинальный автор)
 * @author  Иван Войтков (Ivan Voitkov) — правки безопасности и совместимости с PHP 8.5
 * @version 3.3.2-argon
 * 
 * ИЗМЕНЕНИЯ ОТ ARGON SHOP:
 * 1. Добавлена защита от прямого вызова файла (ABSPATH)
 * 2. Безопасное получение поискового запроса через get_search_query()
 *     вместо прямого доступа к $GLOBALS['s']
 * 3. Добавлены проверки на null / WP_Error / false для:
 *     - get_queried_object()
 *     - get_taxonomy()
 *     - get_post()
 *     - get_term()
 *     - get_term_link()
 *     - get_permalink()
 *     - get_the_terms()
 * 4. Устранены Deprecated-предупреждения PHP 8.1+:
 *     - strpos() с null в первом аргументе
 *     - array_shift() на пустом массиве
 *     - доступ к свойствам null-объектов
 * 5. Совместимость с PHP 8.5:
 *     - явные проверки типов
 *     - безопасные обращения к массивам
 *     - строгие сравнения где это возможно
 *
 * @param  string [$sep  = '']      Разделитель. По умолчанию ' » '
 * @param  array  [$l10n = array()] Для локализации. См. переменную $default_l10n.
 * @param  array  [$args = array()] Опции. См. переменную $def_args
 * @return string Выводит на экран HTML код
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function kama_breadcrumbs( $sep = ' » ', $l10n = array(), $args = array() ){
    $kb = new Kama_Breadcrumbs;
    
    $breadCrumbs = $kb->get_crumbs( $sep, $l10n, $args );

    echo $breadCrumbs;
}

class Kama_Breadcrumbs {

    public $arg;

    // Локализация
    static $l10n = array(
        'home'       => 'Главная',
        'paged'      => 'Страница %d',
        '_404'       => 'Ошибка 404',
        'search'     => 'Результаты поиска по запросу - <b>%s</b>',
        'search2'     => 'Поиск',
        'author'     => 'Архив автора: <b>%s</b>',
        'year'       => 'Архив за <b>%d</b> год',
        'month'      => 'Архив за: <b>%s</b>',
        'day'        => '',
        'attachment' => 'Медиа: %s',
        'tag'        => 'Записи по метке: <b>%s</b>',
        'tax_tag'    => '%1$s из "%2$s" по тегу: <b>%3$s</b>',
    );

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

        // служебные
        'sep'             => '',
        'linkpatt'        => '',
        'pg_end'          => '',
    );

    function get_crumbs( $sep, $l10n, $args ){
        
        global $post, $wp_query, $wp_post_types;

        self::$args['sep'] = $sep;

        // Фильтрует дефолты и сливает
        $loc = (object) array_merge( apply_filters('kama_breadcrumbs_default_loc', self::$l10n ), $l10n );
        $arg = (object) array_merge( apply_filters('kama_breadcrumbs_default_args', self::$args ), $args );
        
        $arg->sep = '<span class="kb_sep">'. $arg->sep .'</span>';

        // упростим
        $sep = & $arg->sep;
        $this->arg = & $arg;

        // микроразметка ---
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

        // может это архив пустой таксы?
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

        // ну, с богом...
        $out = '';

        if( is_front_page() ){
            return $arg->on_front_page ? sprintf( $wrappatt, ( $paged_num ? sprintf($linkpatt, get_home_url(), $loc->home) . $pg_end : $loc->home ) ) : '';
        }
        // страница записей, когда для главной установлена отдельная страница.
        elseif( is_home() ) {
            // PHP 8.5: проверяем, что $q_obj существует
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
            // month day
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
        
        // Древовидные записи
        // PHP 8.5: проверяем $ptype перед обращением
        elseif( is_singular() && $ptype && ! empty( $ptype->hierarchical ) ){
            $out = $this->_add_title( $this->_page_crumbs($post), $post );
        }
        // Таксы, плоские записи и вложения
        else {
            $term = $q_obj;

            // определяем термин для записей (включая вложения attachments)
            if( is_singular() ){
                
                // PHP 8.5: проверяем $post
                if ( ! $post ) {
                    return '';
                }
                
                // изменим $post, чтобы определить термин родителя вложения
                if( is_attachment() && ! empty( $post->post_parent ) ){
                    $save_post = $post;
                    $post = get_post($post->post_parent);
                    
                    if ( ! $post ) {
                        $post = $save_post;
                        unset( $save_post );
                    }
                }

                // учитывает если вложения прикрепляются к таксам древовидным
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

                    // пробуем получить термины, в порядке приоритета такс
                    foreach( $taxonomies as $taxname ){
                        
                        $terms = get_the_terms( $post->ID, $taxname );
                        
                        // PHP 8.5: проверяем на WP_Error и пустой массив
                        if ( ! $terms || is_wp_error( $terms ) || empty( $terms ) ) {
                            continue;
                        }
                        
                        // PHP 8.5: проверяем наличие ключа перед обращением
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
                            // PHP 8.5: array_shift только если массив не пустой
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
            // все виды записей с терминами или термины
            if( $term && isset($term->term_id) ){
                $term = apply_filters('kama_breadcrumbs_term', $term );

                // attachment
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
                // single
                elseif( is_single() ){
                    if( ! $out = apply_filters('post_tax_crumbs', '', $term, $this ) ){
                        $_crumbs = $this->_tax_crumbs( $term, 'self' );
                        $out = $this->_add_title( $_crumbs, $post );
                    }
                }
                // не древовидная такса (метки)
                elseif( ! is_taxonomy_hierarchical($term->taxonomy) ){
                    // метка
                    if( is_tag() )
                        $out = $this->_add_title('', $term, sprintf( $loc->tag, esc_html($term->name) ) );
                    // такса
                    elseif( is_tax() ){
                        $post_label = $ptype && ! empty( $ptype->labels->name ) ? $ptype->labels->name : '';
                        $tax_label = isset( $GLOBALS['wp_taxonomies'][ $term->taxonomy ]->labels->name ) ? $GLOBALS['wp_taxonomies'][ $term->taxonomy ]->labels->name : '';
                        $out = $this->_add_title('', $term, sprintf( $loc->tax_tag, $post_label, $tax_label, esc_html($term->name) ) );
                    }
                }
                // древовидная такса (рубрики)
                else {
                    if( ! $out = apply_filters('term_tax_crumbs', '', $term, $this ) ){
                        $_crumbs = $this->_tax_crumbs( $term, 'parent' );
                        $out = $this->_add_title( $_crumbs, $term, esc_html($term->name) );
                    }
                }
            }
            // вложения от записи без терминов
            elseif( is_attachment() ){
                $parent = get_post($post->post_parent);
                
                // PHP 8.5: проверяем $parent
                if ( ! $parent ) {
                    return '';
                }
                
                $parent_link = sprintf( $linkpatt, get_permalink($parent), esc_html($parent->post_title) );
                $_out = $parent_link;

                // вложение от записи древовидного типа записи
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

        // замена ссылки на архивную страницу для типа записи
        $home_after = apply_filters('kama_breadcrumbs_home_after', '', $linkpatt, $sep, $ptype );

        if( '' === $home_after ){
            // Ссылка на архивную страницу типа записи
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
        
        // PHP 8.5: strpos с null вызывает Deprecated, проверяем
        $home_after_string = $home_after ? $home_after : '';
        
        //Убираем ссылку на страницу товаров со страниц записей
        if( strpos( $home_after_string, 'Товары' ) !== false ){
            
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

    // добавляет заголовок к переданному тексту, с учетом всех опций.
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
        // дополняем - ставим sep
        elseif( $add_to ){
            if( $show_title )
                $add_to .= $arg->sep . sprintf( $arg->title_patt, $title );
            elseif( $arg->last_sep )
                $add_to .= $arg->sep;
        }
        // sep будет потом...
        elseif( $show_title )
            $add_to = sprintf( $arg->title_patt, $title );

        return $add_to;
    }

}

/**
 * Изменения:
 * 3.3 - новые хуки: attachment_tax_crumbs, post_tax_crumbs, term_tax_crumbs. Позволяют дополнить крошки таксономий.
 * 3.2 - баг с разделителем, с отключенным 'show_term_title'. Стабилизировал логику.
 * 3.1 - баг с esc_html() для заголовка терминов - с тегами получалось криво...
 * 3.0 - Обернул в класс. Добавил опции: 'title_patt', 'last_sep'. Доработал код. Добавил пагинацию для постов.
 * 2.5 - ADD: Опция 'show_term_title'
 * 2.4 - Мелкие правки кода
 * 2.3 - ADD: Страница записей, когда для главной установлена отделенная страница.
 * 2.2 - ADD: Link to post type archive on taxonomies page
 * 2.1 - ADD: $sep, $loc, $args params to hooks
 * 2.0 - ADD: в фильтр 'kama_breadcrumbs_home_after' добавлен четвертый аргумент $ptype
 * 1.9 - ADD: фильтр 'kama_breadcrumbs_default_loc' для изменения локализации по умолчанию
 * 1.8 - FIX: заметки, когда в рубрике нет записей
 * 1.7 - Улучшена работа с приоритетными таксономиями.
 * 
 * Argon Shop (3.3.2-argon):
 * - Добавлена защита ABSPATH
 * - Совместимость с PHP 8.5
 * - Проверки null / WP_Error / false
 */