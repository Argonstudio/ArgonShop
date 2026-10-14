<?php
/**
 * Breadcrumbs for WordPress
 *
 * Original author: Kama (Timur Kamaev)
 * Source: https://wp-kama.ru/
 * Original version: 3.3.1
 *
 * @package Argon_Shop
 * @author  Kama (original author)
 * @author  Ivan Voitkov — security fixes and PHP 8.5 compatibility
 * The version of this copy = the plugin version (ARGON_SHOP_VERSION).
 * 
 * ARGON SHOP CHANGES:
 * 1. Added protection against direct file access (ABSPATH)
 * 2. Safe retrieval of the search query via get_search_query()
 *     instead of direct access to $GLOBALS['s']
 * 3. Added null / WP_Error / false checks for:
 *     - get_queried_object()
 *     - get_taxonomy()
 *     - get_post()
 *     - get_term()
 *     - get_term_link()
 *     - get_permalink()
 *     - get_the_terms()
 * 4. Fixed PHP 8.1+ Deprecated warnings:
 *     - strpos() with null as the first argument
 *     - array_shift() on an empty array
 *     - accessing properties of null objects
 * 5. PHP 8.5 compatibility:
 *     - explicit type checks
 *     - safe array access
 *     - strict comparisons where possible
 *
 * @param  string [$sep  = '']      Separator. Default ' » '
 * @param  array  [$l10n = array()] For localization. See the $default_l10n variable.
 * @param  array  [$args = array()] Options. See the $def_args variable
 * @return string Prints the HTML code
 */

// Prevent direct access to the file
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

    // Localization. Strings are translated at runtime (a static property cannot call __()).
    static $l10n = array();

    /**
     * Default translated strings.
     *
     * @since 1.1.0
     * @return array
     */
    static function default_l10n() {
        return array(
            'home'       => __( 'Home', 'argon-shop' ),
            /* translators: %d: page number. */
            'paged'      => __( 'Page %d', 'argon-shop' ),
            '_404'       => __( 'Error 404', 'argon-shop' ),
            /* translators: %s: search query. */
            'search'     => __( 'Search results for - <b>%s</b>', 'argon-shop' ),
            'search2'    => __( 'Search', 'argon-shop' ),
            /* translators: %s: author name. */
            'author'     => __( 'Author archive: <b>%s</b>', 'argon-shop' ),
            /* translators: %d: year. */
            'year'       => __( 'Archive for <b>%d</b>', 'argon-shop' ),
            /* translators: %s: month and year. */
            'month'      => __( 'Archive for: <b>%s</b>', 'argon-shop' ),
            'day'        => '',
            /* translators: %s: attachment title. */
            'attachment' => __( 'Media: %s', 'argon-shop' ),
            /* translators: %s: tag name. */
            'tag'        => __( 'Posts tagged: <b>%s</b>', 'argon-shop' ),
            /* translators: 1: post type label, 2: taxonomy term, 3: tag. */
            'tax_tag'    => __( '%1$s from "%2$s" tagged: <b>%3$s</b>', 'argon-shop' ),
        );
    }

    // Default parameters
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

        // internal
        'sep'             => '',
        'linkpatt'        => '',
        'pg_end'          => '',
    );

    function get_crumbs( $sep, $l10n, $args ){
        
        global $post, $wp_query, $wp_post_types;

        self::$args['sep'] = $sep;

        // Filter the defaults and merge
        $loc = (object) array_merge( apply_filters('kama_breadcrumbs_default_loc', array_merge( self::default_l10n(), self::$l10n ) ), $l10n );
        $arg = (object) array_merge( apply_filters('kama_breadcrumbs_default_args', self::$args ), $args );
        
        $arg->sep = '<span class="kb_sep">'. $arg->sep .'</span>';

        // simplify
        $sep = & $arg->sep;
        $this->arg = & $arg;

        // microdata ---
        if(1){
            $mark = & $arg->markup;

            // Default markup
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

        // maybe this is an archive of an empty taxonomy?
        $ptype = null;
        if( empty($post) ){
            if( isset($q_obj->taxonomy) ){
                // PHP 8.5: check the get_taxonomy() result and the presence of object_type
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

        // here we go...
        $out = '';

        if( is_front_page() ){
            return $arg->on_front_page ? sprintf( $wrappatt, ( $paged_num ? sprintf($linkpatt, get_home_url(), $loc->home) . $pg_end : $loc->home ) ) : '';
        }
        // posts page, when a separate page is set as the front page.
        elseif( is_home() ) {
            // PHP 8.5: make sure $q_obj exists
            if ( ! $q_obj || ! isset( $q_obj->post_title ) ) {
                return '';
            }
            
            $out = $paged_num ? ( sprintf( $linkpatt, get_permalink($q_obj), esc_html($q_obj->post_title) ) . $pg_end ) : esc_html($q_obj->post_title);
        }
        elseif( is_404() ){
            $out = $loc->_404;
        }
        elseif( is_search() ){
            // PHP 8.5: safe retrieval of the search query
            $search_query = get_search_query();
            
            if( $search_query ){
                $out = sprintf( $loc->search, esc_html( $search_query ) );
            } else {
                $out = $loc->search2;
            }
        }
        elseif( is_author() ){
            // PHP 8.5: check $q_obj
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
        
        // Hierarchical posts
        // PHP 8.5: check $ptype before accessing it
        elseif( is_singular() && $ptype && ! empty( $ptype->hierarchical ) ){
            $out = $this->_add_title( $this->_page_crumbs($post), $post );
        }
        // Taxonomies, flat posts and attachments
        else {
            $term = $q_obj;

            // determine the term for posts (including attachments)
            if( is_singular() ){
                
                // PHP 8.5: check $post
                if ( ! $post ) {
                    return '';
                }
                
                // change $post to determine the term of the attachment's parent
                if( is_attachment() && ! empty( $post->post_parent ) ){
                    $save_post = $post;
                    $post = get_post($post->post_parent);
                    
                    if ( ! $post ) {
                        $post = $save_post;
                        unset( $save_post );
                    }
                }

                // takes into account attachments attached to hierarchical taxonomies
                $taxonomies = get_object_taxonomies( $post->post_type );
                $taxonomies = array_intersect( $taxonomies, get_taxonomies( array('hierarchical' => true, 'public' => true) ) );

                if( $taxonomies ){
                    // sort by priority
                    if( ! empty($arg->priority_tax) ){
                        usort( $taxonomies, function($a,$b)use($arg){
                            $a_index = array_search($a, $arg->priority_tax);
                            if( $a_index === false ) $a_index = 9999999;

                            $b_index = array_search($b, $arg->priority_tax);
                            if( $b_index === false ) $b_index = 9999999;

                            return ( $b_index === $a_index ) ? 0 : ( $b_index < $a_index ? 1 : -1 );
                        } );
                    }

                    // try to get terms in the order of taxonomy priority
                    foreach( $taxonomies as $taxname ){
                        
                        $terms = get_the_terms( $post->ID, $taxname );
                        
                        // PHP 8.5: check for WP_Error and an empty array
                        if ( ! $terms || is_wp_error( $terms ) || empty( $terms ) ) {
                            continue;
                        }
                        
                        // PHP 8.5: check that the key exists before accessing it
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
                            // PHP 8.5: array_shift only if the array is not empty
                            if ( ! empty( $terms ) ) {
                                $term = array_shift( $terms );
                            }
                        }

                        break;
                    }
                }

                if( isset($save_post) ) $post = $save_post;
            }

            // output
            // all kinds of posts with terms, or terms
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
                // non-hierarchical taxonomy (tags)
                elseif( ! is_taxonomy_hierarchical($term->taxonomy) ){
                    // tag
                    if( is_tag() )
                        $out = $this->_add_title('', $term, sprintf( $loc->tag, esc_html($term->name) ) );
                    // taxonomy
                    elseif( is_tax() ){
                        $post_label = $ptype && ! empty( $ptype->labels->name ) ? $ptype->labels->name : '';
                        $tax_label = isset( $GLOBALS['wp_taxonomies'][ $term->taxonomy ]->labels->name ) ? $GLOBALS['wp_taxonomies'][ $term->taxonomy ]->labels->name : '';
                        $out = $this->_add_title('', $term, sprintf( $loc->tax_tag, $post_label, $tax_label, esc_html($term->name) ) );
                    }
                }
                // hierarchical taxonomy (categories)
                else {
                    if( ! $out = apply_filters('term_tax_crumbs', '', $term, $this ) ){
                        $_crumbs = $this->_tax_crumbs( $term, 'parent' );
                        $out = $this->_add_title( $_crumbs, $term, esc_html($term->name) );
                    }
                }
            }
            // attachments of a post without terms
            elseif( is_attachment() ){
                $parent = get_post($post->post_parent);
                
                // PHP 8.5: check $parent
                if ( ! $parent ) {
                    return '';
                }
                
                $parent_link = sprintf( $linkpatt, get_permalink($parent), esc_html($parent->post_title) );
                $_out = $parent_link;

                // attachment of a post of a hierarchical post type
                if( is_post_type_hierarchical($parent->post_type) ){
                    $parent_crumbs = $this->_page_crumbs($parent);
                    $_out = implode( $sep, array( $parent_crumbs, $parent_link ) );
                }

                $out = $this->_add_title( $_out, $post );
            }
            // posts without terms
            elseif( is_singular() ){
                $out = $this->_add_title( '', $post );
            }
        }

        // replace the link with the post type archive page
        $home_after = apply_filters('kama_breadcrumbs_home_after', '', $linkpatt, $sep, $ptype );

        if( '' === $home_after ){
            // Link to the post type archive page
            // PHP 8.5: check $term before accessing its properties
            $is_tax_with_ptype = is_tax() && $term && isset( $term->taxonomy ) && ! empty( $ptype->taxonomies ) && in_array( $term->taxonomy, $ptype->taxonomies );
            
            if( $ptype && ! empty( $ptype->has_archive ) && ! in_array( $ptype->name, array('post','page','attachment'), true )
                && ( is_post_type_archive() || is_singular() || $is_tax_with_ptype )
            ){
                $pt_title = ! empty( $ptype->labels->name ) ? $ptype->labels->name : '';

                // first page of the post type archive
                if( is_post_type_archive() && ! $paged_num )
                    $home_after = $pt_title;
                // singular, paged post_type_archive, tax
                else{
                    $home_after = sprintf( $linkpatt, get_post_type_archive_link($ptype->name), $pt_title );

                    $home_after .= ( ($paged_num && ! is_tax()) ? $pg_end : $sep );
                }
            }
        }
        
        // PHP 8.5: strpos with null triggers Deprecated, so check first
        $home_after_string = $home_after ? $home_after : '';
        
        // Remove the link to the products page from post pages
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
        
        // PHP 8.5: check $post
        if ( ! $post || ! isset( $post->post_parent ) ) {
            return '';
        }
        
        $parent = $post->post_parent;

        $crumbs = array();
        while( $parent ){
            $page = get_post( $parent );
            
            // PHP 8.5: check the get_post() result
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
            
            // PHP 8.5: check the get_term() result
            if ( ! $term || is_wp_error( $term ) ) {
                break;
            }
            
            $term_link = get_term_link( $term );
            
            // PHP 8.5: check get_term_link()
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

    // adds a title to the given text, taking all options into account.
    function _add_title( $add_to, $obj, $term_title = '' ){
        $arg = & $this->arg;
        
        // PHP 8.5: check $obj before accessing a property
        if ( $term_title ) {
            $title = $term_title;
        } elseif ( $obj && isset( $obj->post_title ) ) {
            $title = esc_html($obj->post_title);
        } else {
            $title = '';
        }
        
        $show_title = $term_title ? $arg->show_term_title : $arg->show_post_title;

        // pagination
        if( $arg->pg_end ){
            $link = $term_title ? get_term_link($obj) : get_permalink($obj);
            
            // PHP 8.5: check get_term_link / get_permalink
            if ( is_wp_error( $link ) ) {
                $link = '';
            }
            
            $add_to .= ($add_to ? $arg->sep : '') . sprintf( $arg->linkpatt, $link, $title ) . $arg->pg_end;
        }
        // append — add sep
        elseif( $add_to ){
            if( $show_title )
                $add_to .= $arg->sep . sprintf( $arg->title_patt, $title );
            elseif( $arg->last_sep )
                $add_to .= $arg->sep;
        }
        // sep will come later...
        elseif( $show_title )
            $add_to = sprintf( $arg->title_patt, $title );

        return $add_to;
    }

}

/**
 * Changes:
 * 3.3 - new hooks: attachment_tax_crumbs, post_tax_crumbs, term_tax_crumbs. They allow extending taxonomy crumbs.
 * 3.2 - separator bug with 'show_term_title' disabled. Stabilized the logic.
 * 3.1 - esc_html() bug for term titles - tags were rendered incorrectly...
 * 3.0 - Wrapped in a class. Added options: 'title_patt', 'last_sep'. Improved the code. Added pagination for posts.
 * 2.5 - ADD: 'show_term_title' option
 * 2.4 - Minor code fixes
 * 2.3 - ADD: Posts page, when a separate page is set as the front page.
 * 2.2 - ADD: Link to post type archive on taxonomies page
 * 2.1 - ADD: $sep, $loc, $args params to hooks
 * 2.0 - ADD: a fourth argument $ptype was added to the 'kama_breadcrumbs_home_after' filter
 * 1.9 - ADD: 'kama_breadcrumbs_default_loc' filter for changing the default localization
 * 1.8 - FIX: notices when a category has no posts
 * 1.7 - Improved handling of priority taxonomies.
 * 
 * Argon Shop (3.3.2-argon):
 * - Added ABSPATH protection
 * - PHP 8.5 compatibility
 * - null / WP_Error / false checks
 */