<?php

//Фильтры для заказов
function restrict_posts_by_money() {
    
    
    
    global $typenow;
    $post_type = 'shoporder';
    $taxonomy = 'statusorders';
    
    $countPosts = wp_count_posts('shoporder');
    
    if($countPosts->publish){
    
        if ($typenow == $post_type) {
            
            $selected = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
            $info_taxonomy = get_taxonomy($taxonomy);
            
            wp_dropdown_categories(array(
                'show_option_all' => __("Все статусы"),
                'taxonomy' => $taxonomy,
                'name' => $taxonomy,
                'orderby' => 'name',
                'selected' => $selected,
                'show_count' => true,
                'hide_empty' => true,
                
            ));
            
        };
    
    }

}


add_action('restrict_manage_posts', 'restrict_posts_by_money');


function convert_id_to_term_in_query($query) {
    
    global $pagenow;
    
    $post_type = 'shoporder';
    $taxonomy = 'statusorders';
    
    $q_vars = &$query->query_vars;
    
    if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0) {
            $term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
            $q_vars[$taxonomy] = $term->slug;
            
        }

}

add_filter('parse_query', 'convert_id_to_term_in_query');

/*
function add_taxonomy_filters() {
    
    global $typenow;
    $taxonomies = array('statusorders');

    if( $typenow == 'shoporder' ){ 
        
        foreach ($taxonomies as $tax_slug) {
            
            $tax_obj = get_taxonomy($tax_slug);
            
            $tax_name = $tax_obj->labels->name;
            
            $terms = get_terms($tax_slug);
            
            if(count($terms) > 0) {
                
                echo "";
                echo "Показать все $tax_name";
                foreach ($terms as $term) { 
                
                echo ''.$_GET[$tax_slug] == $term->slug ? ' selected="selected"' : '','>' . $term->name .' (' . $term->count .')'; 
                }
                echo "";
                
            }
            
        }
        
    }
    
}
add_action( 'restrict_manage_posts', 'add_taxonomy_filters' );
*/