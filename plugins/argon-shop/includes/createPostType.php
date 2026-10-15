<?php
/**
 * Регистрация типов записей и таксономий
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Админка WordPress → меню «Товары», «Заказы», «Слайдер», «Каталог», «Характеристики»
 * 
 * ЧТО РЕГИСТРИРУЕТ:
 * 1. Тип записи «slider» — Слайдер
 * 2. Тип записи «shoporder» — Заказы
 * 3. Тип записи «product» — Товары
 * 4. Таксономия «statusorders» — Статусы заказов
 * 5. Таксономия «catalog» — Каталог (иерархическая)
 * 6. Таксономия «wholesalePrice» — Ступени оптовых цен (когда скидки включены)
 * 7. Таксономия «characteristics» — Характеристики
 * 
 * PHP 8.5:
 * - $setting проверяется перед обращением к ключу
 * - проверки isset() для $_POST и опций
 * - Явные проверки типов
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'init', 'as_create_post_type' );

function as_create_post_type() {
    
    // ============================================
    // ТИП ЗАПИСИ: СЛАЙДЕР
    // ============================================
    
    register_post_type( 'slider',
        array(
            'labels' => array(
                'name'               => __( 'Slider', 'argon-shop' ),
                'singular_name'      => __( 'Slide', 'argon-shop' ),
                'add_new'            => __( 'Add slide', 'argon-shop' ),
                'add_new_item'       => __( 'Add slide', 'argon-shop' ),
                'edit'               => __( 'Edit', 'argon-shop' ),
                'edit_item'          => __( 'Edit slide', 'argon-shop' ),
                'new_item'           => __( 'New slide', 'argon-shop' ),
                'view'               => __( 'View', 'argon-shop' ),
                'view_item'          => __( 'View slide', 'argon-shop' ),
                'search_items'       => __( 'Search slides', 'argon-shop' ),
                'not_found'          => __( 'No slides yet', 'argon-shop' ),
                'not_found_in_trash' => __( 'No slides found in Trash', 'argon-shop' ),
                'parent'             => __( 'Parent slide', 'argon-shop' ),
            ),
            'public'              => true,
            'show_in_menu'        => true,
            'exclude_from_search' => true,
            'menu_icon'           => 'dashicons-images-alt',
            'menu_position'       => 13,
            'supports'            => array( 'title', 'editor', 'thumbnail' ),
            'has_archive'         => true,
            'query_posts'         => false,
        )
    );
    
    // ============================================
    // ТИП ЗАПИСИ: ЗАКАЗЫ
    // ============================================
    
    register_post_type( 'shoporder',
        array(
            'labels' => array(
                'name'               => __( 'Orders', 'argon-shop' ),
                'singular_name'      => __( 'Order', 'argon-shop' ),
                'add_new'            => __( 'Add order', 'argon-shop' ),
                'add_new_item'       => __( 'Add order', 'argon-shop' ),
                'edit'               => __( 'Edit', 'argon-shop' ),
                'edit_item'          => __( 'Edit order', 'argon-shop' ),
                'new_item'           => __( 'New order', 'argon-shop' ),
                'view'               => __( 'View', 'argon-shop' ),
                'view_item'          => __( 'View order', 'argon-shop' ),
                'search_items'       => __( 'Search orders', 'argon-shop' ),
                'not_found'          => __( 'No orders yet', 'argon-shop' ),
                'not_found_in_trash' => __( 'No orders found in Trash', 'argon-shop' ),
                'parent'             => __( 'Parent order', 'argon-shop' ),
            ),
            'public'              => true,
            'publicly_queryable'  => false,
            'show_in_menu'        => true,
            'exclude_from_search' => true,
            'menu_icon'           => 'dashicons-clipboard',
            'menu_position'       => 15,
            'supports'            => array( 'title' ),
            'has_archive'         => true,
            'query_posts'         => false,
        )
    );
    
    // ============================================
    // ТАКСОНОМИЯ: СТАТУСЫ ЗАКАЗОВ
    // ============================================
    
    $taxonomy_statusOrders_labels = array(
        'name'                       => __( 'Order statuses', 'argon-shop' ),
        'singular_name'              => __( 'Status', 'argon-shop' ),
        'menu_name'                  => __( 'Order statuses', 'argon-shop' ),
        'all_items'                  => __( 'All statuses', 'argon-shop' ),
        'parent_item'                => 'Parent Category',
        'parent_item_colon'          => 'Parent Category:',
        'new_item_name'              => 'New Category Name',
        'add_new_item'               => __( 'Add status', 'argon-shop' ),
        'edit_item'                  => __( 'Edit status', 'argon-shop' ),
        'update_item'                => 'Update Category',
        'separate_items_with_commas' => 'Separate categories with commas',
        'search_items'               => __( 'Search statuses', 'argon-shop' ),
        'add_or_remove_items'        => 'Add or remove categories',
        'choose_from_most_used'      => 'Choose from the most used categories',
    );
    
    $taxonomy_statusOrders_rewrite = array(
        'slug'         => 'statusorders',
        'with_front'   => false,
        'hierarchical' => false,
    );
    
    $taxonomy_statusOrders_args = array(
        'labels'            => $taxonomy_statusOrders_labels,
        'hierarchical'      => false,
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'show_tagcloud'     => true,
        'rewrite'           => $taxonomy_statusOrders_rewrite,
        'meta_box_cb'       => false,
    );
    
    register_taxonomy( 'statusorders', 'shoporder', $taxonomy_statusOrders_args );
    
    // ============================================
    // ТИП ЗАПИСИ: ТОВАРЫ
    // ============================================
    
    register_post_type( 'product',
        array(
            'labels' => array(
                'name'               => __( 'Products', 'argon-shop' ),
                'singular_name'      => __( 'Products', 'argon-shop' ),
                'add_new'            => __( 'Add product', 'argon-shop' ),
                'add_new_item'       => __( 'Add product', 'argon-shop' ),
                'edit'               => __( 'Edit', 'argon-shop' ),
                'edit_item'          => __( 'Edit product', 'argon-shop' ),
                'new_item'           => __( 'New product', 'argon-shop' ),
                'view'               => __( 'View', 'argon-shop' ),
                'view_item'          => __( 'View product', 'argon-shop' ),
                'search_items'       => __( 'Search products', 'argon-shop' ),
                'not_found'          => __( 'No products yet', 'argon-shop' ),
                'not_found_in_trash' => __( 'No products found in Trash', 'argon-shop' ),
                'parent'             => __( 'Parent product', 'argon-shop' ),
            ),
            'public'        => true,
            'menu_icon'     => 'dashicons-clipboard',
            'menu_position' => 14,
            'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
            'has_archive'   => true,
            'query_posts'   => false,
            'yarpp_support' => true,
        )
    );
    
    // ============================================
    // ТАКСОНОМИЯ: КАТАЛОГ
    // ============================================
    
    $taxonomy_labels = array(
        'name'                       => __( 'Catalog', 'argon-shop' ),
        'singular_name'              => __( 'Catalog', 'argon-shop' ),
        'menu_name'                  => __( 'Catalog', 'argon-shop' ),
        'all_items'                  => __( 'All categories', 'argon-shop' ),
        'parent_item'                => 'Parent Category',
        'parent_item_colon'          => 'Parent Category:',
        'new_item_name'              => 'New Category Name',
        'add_new_item'               => __( 'Add new category', 'argon-shop' ),
        'edit_item'                  => __( 'Edit category', 'argon-shop' ),
        'update_item'                => 'Update Category',
        'separate_items_with_commas' => 'Separate categories with commas',
        'search_items'               => __( 'Search categories', 'argon-shop' ),
        'add_or_remove_items'        => 'Add or remove categories',
        'choose_from_most_used'      => 'Choose from the most used categories',
    );
    
    $taxonomy_rewrite = array(
        'slug'         => 'catalog',
        'with_front'   => false,
        'hierarchical' => true,
    );
    
    $taxonomy_args = array(
        'labels'            => $taxonomy_labels,
        'hierarchical'      => true,
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'show_tagcloud'     => true,
        'rewrite'           => $taxonomy_rewrite,
    );
    
    register_taxonomy( 'catalog', 'product', $taxonomy_args );
    
    // ============================================
    // ТАКСОНОМИЯ: ОПТОВЫЕ ЦЕНЫ (только когда скидки включены)
    // ============================================
    
    // PHP 8.5: проверяем $setting перед обращением к ключу
    $setting = get_option( 'settingShop' );
    
    $type_sale = ( is_array( $setting ) && isset( $setting['typeSale'] ) ) 
        ? $setting['typeSale'] 
        : '';
    
    if ( $type_sale === 'wholesalePriceSteps' ) {
        
        $taxonomy_wholesalePrice_labels = array(
            'name'                       => __( 'Wholesale price steps', 'argon-shop' ),
            'singular_name'              => __( 'Wholesale price steps', 'argon-shop' ),
            'menu_name'                  => __( 'Wholesale price steps', 'argon-shop' ),
            'all_items'                  => __( 'All categories', 'argon-shop' ),
            'parent_item'                => 'Parent Category',
            'parent_item_colon'          => 'Parent Category:',
            'new_item_name'              => 'New Category Name',
            'add_new_item'               => __( 'Add step', 'argon-shop' ),
            'edit_item'                  => __( 'Edit step', 'argon-shop' ),
            'update_item'                => 'Update Category',
            'separate_items_with_commas' => 'Separate categories with commas',
            'search_items'               => __( 'Search steps', 'argon-shop' ),
            'add_or_remove_items'        => 'Add or remove categories',
            'choose_from_most_used'      => 'Choose from the most used categories',
        );
        
        $taxonomy_wholesalePrice_rewrite = array(
            'slug'         => 'wholesalePrice',
            'with_front'   => false,
            'hierarchical' => false,
        );
        
        $taxonomy_wholesalePrice_args = array(
            'labels'            => $taxonomy_wholesalePrice_labels,
            'hierarchical'      => false,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud'     => true,
            'rewrite'           => $taxonomy_wholesalePrice_rewrite,
        );
        
        register_taxonomy( 'wholesalePrice', 'product', $taxonomy_wholesalePrice_args );
    }
    
    // ============================================
    // ТАКСОНОМИЯ: ХАРАКТЕРИСТИКИ
    // ============================================
    
    $taxonomy_characteristics_labels = array(
        'name'                       => __( 'Characteristics', 'argon-shop' ),
        'singular_name'              => __( 'Characteristics', 'argon-shop' ),
        'menu_name'                  => __( 'Characteristics', 'argon-shop' ),
        'all_items'                  => __( 'All characteristics', 'argon-shop' ),
        'parent_item'                => __( 'Parent characteristic', 'argon-shop' ),
        'parent_item_colon'          => __( 'Parent characteristic:', 'argon-shop' ),
        'new_item_name'              => 'New Category Name',
        'add_new_item'               => __( 'Add new characteristic', 'argon-shop' ),
        'edit_item'                  => __( 'Edit characteristic', 'argon-shop' ),
        'update_item'                => 'Update Category',
        'separate_items_with_commas' => 'Separate categories with commas',
        'search_items'               => __( 'Search characteristics', 'argon-shop' ),
        'add_or_remove_items'        => 'Add or remove categories',
        'choose_from_most_used'      => 'Choose from the most used categories',
    );
    
    $taxonomy_characteristics_rewrite = array(
        'slug'         => 'characteristics',
        'with_front'   => false,
        'hierarchical' => false,
    );
    
    $taxonomy_characteristics_args = array(
        'labels'            => $taxonomy_characteristics_labels,
        'hierarchical'      => true,
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'show_tagcloud'     => true,
        'meta_box_cb'       => false,
        'rewrite'           => $taxonomy_characteristics_rewrite,
    );
    
    register_taxonomy( 'characteristics', 'product', $taxonomy_characteristics_args );
}