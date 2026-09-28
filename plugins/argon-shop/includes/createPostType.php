<?php

add_action( 'init', 'as_create_post_type' );
	
	function as_create_post_type(){
	    
	    register_post_type( 'slider',
	        array(
	            'labels' => array(
	                'name' => 'Слайдер',
	                'singular_name' => 'Слайд',
	                'add_new' => 'Добавить слайд',
	                'add_new_item' => 'Добавить слайд',
	                'edit' => 'Редактировать',
	                'edit_item' => 'Редактировать слайд',
	                'new_item' => 'Новый слайд',
	                'view' => 'Просмотр',
	                'view_item' => 'Посмотреть слайд',
	                'search_items' => 'Поиск слайда',
	                'not_found' => 'Слайдов пока нет',
	                'not_found_in_trash' => 'слайды не найдены в корзине',
	                'parent' => 'Parent слайд'
	            ),
	            'public' => true,
	            'show_in_menu' => true,
	            'exclude_from_search' => true,
	            'menu_icon' => 'dashicons-images-alt',
	            'menu_position' => 13,
	            'supports' => array( 'title', 'editor', 'thumbnail'),
	            'taxonomies' => array( 'post' ),
	            'has_archive' => true,
	            'query_posts' => false
	        )
	    );
	    
	    register_post_type( 'shoporder',
	        array(
	            'labels' => array(
	                'name' => 'Заказы',
	                'singular_name' => 'Заказ',
	                'add_new' => 'Добавить заказ',
	                'add_new_item' => 'Добавить заказ',
	                'edit' => 'Редактировать',
	                'edit_item' => 'Редактировать заказ',
	                'new_item' => 'Новый заказ',
	                'view' => 'Просмотр',
	                'view_item' => 'Посмотреть заказ',
	                'search_items' => 'Поиск заказа',
	                'not_found' => 'Заказов пока нет',
	                'not_found_in_trash' => 'заказы не найдены в корзине',
	                'parent' => 'Parent заказы'
	            ),
	            'public' => true,
	            'publicly_queryable' => false,
	            'show_in_menu' => true,
	            'exclude_from_search' => true, //не учитывается при поиске по сайту
	            'menu_icon' => 'dashicons-clipboard',
	            'menu_position' => 15,
	            'supports' => array( 'title'),
	            'taxonomies' => array( 'post' ),
	            'has_archive' => true,
	            'query_posts' => false
	            
	        )
	    );


/* ------
 СТАТУСЫ ЗАКАЗОВ
----*/
        $taxonomy_statusOrders_labels                = array(
          	'name'                        => 'Статусы заказов',
          	'singular_name'               => 'Статус заказов',
          	'menu_name'                   => 'Статусы заказов',
          	'all_items'                   => 'Все статусы',
          	'parent_item'                 => 'Parent Category',
          	'parent_item_colon'           => 'Parent Category:',
          	'new_item_name'               => 'New Category Name',
          	'add_new_item'                => 'Добавить статус',
          	'edit_item'                   => 'Редактировать статус',
          	'update_item'                 => 'Update Category',
          	'separate_items_with_commas'  => 'Separate categories with commas',
          	'search_items'                => 'Поиск статуса',
          	'add_or_remove_items'         => 'Add or remove categories',
          	'choose_from_most_used'       => 'Choose from the most used categories',
        );

        $taxonomy_statusOrders_rewrite         = array(
          	'slug'                  => 'statusorders',
          	'with_front'            => false,
          	'hierarchical'          => false,
        );

        $taxonomy_statusOrders_args          = array(
          	'labels'              => $taxonomy_statusOrders_labels,
          	'hierarchical'        => false,
	      	'public'              => true,
	      	'show_ui'             => true,
	      	'show_admin_column'   => true,
	      	'show_in_nav_menus'   => true,
	      	'show_tagcloud'       => true,
          	'rewrite'             => $taxonomy_statusOrders_rewrite,
          	'meta_box_cb'         => false
        );
        register_taxonomy( 'statusorders', 'shoporder', $taxonomy_statusOrders_args );	    
	    

	    register_post_type( 'product',
	        array(
	            'labels' => array(
	                'name' => 'Товары',
	                'singular_name' => 'Товары',
	                'add_new' => 'Добавить товар',
	                'add_new_item' => 'Добавить товар',
	                'edit' => 'Редактировать',
	                'edit_item' => 'Редактировать товар',
	                'new_item' => 'Новый товар',
	                'view' => 'Просмотр',
	                'view_item' => 'Посмотреть товар',
	                'search_items' => 'Поиск товара',
	                'not_found' => 'Товары пока нет',
	                'not_found_in_trash' => 'Товары не найдены в корзине',
	                'parent' => 'Parent товары'
	            ),
	            'public' => true,
	            'menu_icon' => 'dashicons-clipboard',
	            'menu_position' => 14,
	            'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail'),
	            'taxonomies' => array( 'post' ),
	            'has_archive' => true,
	            'query_posts' => false,
				'yarpp_support' => true
	        )
	    );

		$taxonomy_labels                = array(
          	'name'                        => 'Каталог',
          	'singular_name'               => 'Каталог',
          	'menu_name'                   => 'Каталог',
          	'all_items'                   => 'Все категории',
          	'parent_item'                 => 'Parent Category',
          	'parent_item_colon'           => 'Parent Category:',
          	'new_item_name'               => 'New Category Name',
          	'add_new_item'                => 'Добавить новую категорию',
          	'edit_item'                   => 'Редактировать категорию',
          	'update_item'                 => 'Update Category',
          	'separate_items_with_commas'  => 'Separate categories with commas',
          	'search_items'                => 'Поиск категории',
          	'add_or_remove_items'         => 'Add or remove categories',
          	'choose_from_most_used'       => 'Choose from the most used categories',
        );

        $taxonomy_rewrite         = array(
          	'slug'                  => 'catalog',
          	'with_front'            => false,
          	'hierarchical'          => true,
        );

        $taxonomy_args          = array(
          	'labels'              => $taxonomy_labels,
          	'hierarchical'        => true,
	      	'public'              => true,
	      	'show_ui'             => true,
	      	'show_admin_column'   => true,
	      	'show_in_nav_menus'   => true,
	      	'show_tagcloud'       => true,
          	'rewrite'             => $taxonomy_rewrite,
        );
        register_taxonomy( 'catalog', 'product', $taxonomy_args );
        
        
/* ------
 ОПТОВЫЕ ЦЕНЫ
----*/

        $setting = get_option('settingShop');
        
        if($setting["typeSale"] == "wholesalePriceSteps"){
            
            $taxonomy_wholesalePrice_labels                = array(
              	'name'                        => 'Шаги оптовых цен',
              	'singular_name'               => 'Шаги оптовых цен',
              	'menu_name'                   => 'Шаги оптовых цен',
              	'all_items'                   => 'Все категории',
              	'parent_item'                 => 'Parent Category',
              	'parent_item_colon'           => 'Parent Category:',
              	'new_item_name'               => 'New Category Name',
              	'add_new_item'                => 'Добавить шаг',
              	'edit_item'                   => 'Редактировать шаг',
              	'update_item'                 => 'Update Category',
              	'separate_items_with_commas'  => 'Separate categories with commas',
              	'search_items'                => 'Поиск шага',
              	'add_or_remove_items'         => 'Add or remove categories',
              	'choose_from_most_used'       => 'Choose from the most used categories',
            );
    
            $taxonomy_wholesalePrice_rewrite         = array(
              	'slug'                  => 'wholesalePrice',
              	'with_front'            => false,
              	'hierarchical'          => false,
            );
    
            $taxonomy_wholesalePrice_args          = array(
              	'labels'              => $taxonomy_wholesalePrice_labels,
              	'hierarchical'        => false,
    	      	'public'              => true,
    	      	'show_ui'             => true,
    	      	'show_admin_column'   => true,
    	      	'show_in_nav_menus'   => true,
    	      	'show_tagcloud'       => true,
              	'rewrite'             => $taxonomy_wholesalePrice_rewrite,
            );
            register_taxonomy( 'wholesalePrice', 'product', $taxonomy_wholesalePrice_args );
            
        }

        


/* ------
 Характеристики
----*/
        $taxonomy_characteristics_labels                = array(
          	'name'                        => 'Характеристики',
          	'singular_name'               => 'Характеристики',
          	'menu_name'                   => 'Характеристики',
          	'all_items'                   => 'Все характеристики',
          	'parent_item'                 => 'Родительская характеристика',
          	'parent_item_colon'           => 'Родительская характеристика:',
          	'new_item_name'               => 'New Category Name',
          	'add_new_item'                => 'Добавить новую характеристику',
          	'edit_item'                   => 'Редактировать характеристику',
          	'update_item'                 => 'Update Category',
          	'separate_items_with_commas'  => 'Separate categories with commas',
          	'search_items'                => 'Поиск характеристики',
          	'add_or_remove_items'         => 'Add or remove categories',
          	'choose_from_most_used'       => 'Choose from the most used categories',
        );

        $taxonomy_characteristics_rewrite         = array(
          	'slug'                  => 'characteristics',
          	'with_front'            => false,
          	'hierarchical'          => false,
        );

        $taxonomy_characteristics_args          = array(
          	'labels'              => $taxonomy_characteristics_labels,
          	'hierarchical'        => true,
	      	'public'              => true,
	      	'show_ui'             => true,
	      	'show_admin_column'   => true,
	      	'show_in_nav_menus'   => true,
	      	'show_tagcloud'       => true,
	      	'meta_box_cb'         => false,
          	'rewrite'             => $taxonomy_characteristics_rewrite,
        );
        register_taxonomy( 'characteristics', 'product', $taxonomy_characteristics_args );    
  
	}
	
	
	
	

	


