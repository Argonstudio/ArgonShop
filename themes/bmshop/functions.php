<?php 

/*
Theme Name:        BMShop
Theme URI:         https://github.com/Argonstudio/ArgonShop/tree/historic/php56-jquery
Author:            Ivan Voitkov
Author URI:        http://argon-studio.ru
Description:       Архивная тема для интернет-магазина на плагине ArgonShop, версия 2018 года
Version:           1.0.0
License:           GNU General Public License v2.0 or later
License URI:       http://gnu.org
Text Domain:       bmshop
Requires at least: 4.9
Tested up to:      6.2
Requires PHP:      5.6

BMShop WordPress Theme, Copyright 2018 Ivan Voitkov.
BMShop is distributed under the terms of the GNU General Public License v2.0 or later.

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <https://gnu.org>.
*/

define( 'AUTOMATIC_UPDATER_DISABLED', true );

ini_set('display_errors','off');	
	add_action('after_setup_theme','bito_after_setup');
	function bito_after_setup(){
		
		register_nav_menus( array(
			'top_menu' => __( 'Верхнее меню', 'bito' ),
		) );
		
		register_nav_menus( array(
			'bottom_menu' => __( 'Нижнее меню', 'bito' ),
		) );
		
		register_nav_menus( array(
			'bottom_menu2' => __( 'Нижнее меню2', 'bito' ),
		) );
		
		add_theme_support('post-thumbnails');
		add_theme_support('automatic-feed-links');
		add_theme_support('custom-background');
		add_theme_support('custom-header');
		add_theme_support('title-tag');
	}
	add_image_size('slider_thumb', 880, 305, true);
	add_image_size('blog_thumb', 260, 200, true);
	add_image_size('product_thumb', 280, 130, true);
	add_image_size('obj_thumb', 180, 120, true);
	add_image_size('obj_about_thumb', 260, 200, true);
	
	/*!! запросов не много при условии сжатия кода */
	function bmshop_scripts(){
	    
	    /** БИБЛИОТЕКИ */
	    
		wp_enqueue_style('fancybox-css', esc_url( get_template_directory_uri() ).'/libs/fancybox/jquery.fancybox.min.css');
		wp_enqueue_script('fancybox-js', esc_url( get_template_directory_uri() ).'/libs/fancybox/jquery.fancybox.min.js', '', '', true);
		
		wp_enqueue_style('slick-css', esc_url( get_template_directory_uri() ).'/libs/slick/slick.css');
		wp_enqueue_script('slick-js', esc_url( get_template_directory_uri() ).'/libs/slick/slick.min.js', '', '', true);
		
		/** КЛЮЧЕВЫЕ СТИЛИ */
	    
	    wp_enqueue_style( 'main-style', esc_url( get_template_directory_uri() ).'/assets/main.css');
	    wp_enqueue_style( 'main-styleControlPanels', esc_url( get_template_directory_uri() ).'/assets/controlPanels.css');
	    wp_enqueue_style( 'main-styleForm', esc_url( get_template_directory_uri() ).'/assets/form.css');
		
		/** TOP BLOCK */
		
		wp_enqueue_style( 'topBlockStyle-main', esc_url( get_template_directory_uri() ).'/assets/topBlock/topBlock.css');
		wp_enqueue_style( 'topBlockStyle-shoppingCart', esc_url( get_template_directory_uri() ).'/assets/topBlock/shoppingCart.css');
		
		wp_enqueue_style('topBlockStyle-siteMenu', esc_url( get_template_directory_uri() ).'/assets/topBlock/siteMenu/siteMenu.css');
		wp_enqueue_script('topBlockScripts-siteMenu', esc_url( get_template_directory_uri() ).'/assets/topBlock/siteMenu/siteMenu.js', '', '', true);
		
		wp_enqueue_style('topBlockStyle-search', esc_url( get_template_directory_uri() ).'/assets/topBlock/search/topSearch.css');
		wp_enqueue_script('topBlockScripts-search', esc_url( get_template_directory_uri() ).'/assets/topBlock/search/topSearch.js', '', '', true);
		
		/** HEADER */
		
		wp_enqueue_style( 'headerStyle-main', esc_url( get_template_directory_uri() ).'/assets/header/header.css');
		wp_enqueue_script('headerScripts', esc_url( get_template_directory_uri() ).'/assets/header/header.js', '', '', true);
		
		wp_enqueue_style( 'headerStyle-contactForm', esc_url( get_template_directory_uri() ).'/assets/header/contactForm/contactForm.css');
		wp_enqueue_script('headerScripts-contactForm', esc_url( get_template_directory_uri() ).'/assets/header/contactForm/contactForm.js', '', '', true);
		
		/** SITEBAR */
		
		wp_enqueue_style( 'sitebarStyle-main', esc_url( get_template_directory_uri() ).'/assets/sitebar/sitebar.css');
		
		/* menu */
		
		wp_enqueue_style( 'sitebarStyle-menuCatalog', esc_url( get_template_directory_uri() ).'/assets/sitebar/menuCatalog/menuCatalog.css');
		wp_enqueue_script('sitebarScripts-menuCatalog', esc_url( get_template_directory_uri() ).'/assets/sitebar/menuCatalog/menuCatalog.js', '', '', true);
		
		/* markedProducts */
		
		wp_enqueue_style( 'sitebarStyle-markedProducts', esc_url( get_template_directory_uri() ).'/assets/sitebar/markedProducts/markedProducts.css');
		
		/* entryTags */ 
		
		wp_enqueue_style( 'sitebarStyle-entryTags', esc_url( get_template_directory_uri() ).'/assets/sitebar/entryTags/entryTags.css');
		
		//
		
		/** CABINET */
		
		wp_enqueue_style( 'cabinetStyle-main', esc_url( get_template_directory_uri() ).'/assets/cabinet/cabinet.css');
		wp_enqueue_style( 'cabinetStyle-controlPanel', esc_url( get_template_directory_uri() ).'/assets/cabinet/controlPanel.css');
		
		/* account */
		
		wp_enqueue_style( 'cabinetStyle-account', esc_url( get_template_directory_uri() ).'/assets/cabinet/account/account.css');
		wp_enqueue_script('cabinetScripts-account', esc_url( get_template_directory_uri() ).'/assets/cabinet/account/account.js', '', '', true);
		
		/* setting */
		
		wp_enqueue_style( 'cabinetStyle-setting', esc_url( get_template_directory_uri() ).'/assets/cabinet/setting/setting.css');
		
		/* orders */
		
		wp_enqueue_style( 'cabinetStyle-orders', esc_url( get_template_directory_uri() ).'/assets/cabinet/orders/orders.css');
		
		
		/** CART */
		
		wp_enqueue_style( 'cartStyle-main', esc_url( get_template_directory_uri() ).'/assets/cart/cart.css');
		
		wp_enqueue_style( 'cartStyle-controlPanel', esc_url( get_template_directory_uri() ).'/assets/cart/controlPanel.css');
		wp_enqueue_style( 'cartStyle-cartListProducts', esc_url( get_template_directory_uri() ).'/assets/cart/cartListProducts.css');
		wp_enqueue_style( 'cartStyle-totalValue', esc_url( get_template_directory_uri() ).'/assets/cart/totalValue.css');
		
		/* forms */
		
		wp_enqueue_style( 'cartStyle-forms', esc_url( get_template_directory_uri() ).'/assets/cart/forms/forms.css');
		//wp_enqueue_script('cartScripts-forms', esc_url( get_template_directory_uri() ).'/assets/cart/forms/forms.js', '', '', true);
		
		/** homePage */
		
		wp_enqueue_style( 'homePageStyle-main', esc_url( get_template_directory_uri() ).'/assets/homePage/homePage.css');
		
		/* слайдер */
		
		wp_enqueue_style( 'homePageStyle-slick-slider', esc_url( get_template_directory_uri() ).'/assets/homePage/slick-slider/homePageSlider.css');
		wp_enqueue_script('homePageScripts-slick-slider', esc_url( get_template_directory_uri() ).'/assets/homePage/slick-slider/settingSliders.js', '', '', true);
		
		/* наши преимущества */
		
		wp_enqueue_style( 'homePageStyle-advantages', esc_url( get_template_directory_uri() ).'/assets/homePage/advantages.css');
		
		/* каталог на главной */
		
		wp_enqueue_style( 'homePageStyle-catalog', esc_url( get_template_directory_uri() ).'/assets/homePage/catalog.css');
		
		
		/** PRODUCT */
		
		/* singleProduct */
		
		wp_enqueue_style( 'singleProductStyle-main', esc_url( get_template_directory_uri() ).'/assets/product/singleProduct/product.css');
		
		wp_enqueue_style( 'singleProductStyle-singleProductSlider', esc_url( get_template_directory_uri() ).'/assets/product/singleProduct/singleProductSlider/singleProductSlider.css');
		wp_enqueue_script('singleProductScripts-singleProductSlider', esc_url( get_template_directory_uri() ).'/assets/product/singleProduct/singleProductSlider/singleProductSlider.js', '', '', true);
		
		//Информация о товаре(описание и прочее)
		wp_enqueue_style( 'singleProductStyle-aboutProducts', esc_url( get_template_directory_uri() ).'/assets/product/singleProduct/aboutProduct/aboutProducts.css');
		
		//Характеристики
		wp_enqueue_style( 'singleProductStyle-aboutProductsСharacteristics', esc_url( get_template_directory_uri() ).'/assets/product/singleProduct/aboutProduct/characteristics/characteristics.css');
		wp_enqueue_script('singleProductScripts-aboutProductsСharacteristics', esc_url( get_template_directory_uri() ).'/assets/product/singleProduct/aboutProduct/characteristics/characteristics.js', '', '', true);
		
		/* cardProduct */
		
		wp_enqueue_style( 'cardProductStyle-main', esc_url( get_template_directory_uri() ).'/assets/product/cardProduct/cardProduct.css');
		
		/** О КОМПАНИИ */
		
		wp_enqueue_style( 'aboutCompanyStyle-main', esc_url( get_template_directory_uri() ).'/assets/aboutCompany/aboutCompany.css');
		wp_enqueue_script('aboutCompanyScripts-main', esc_url( get_template_directory_uri() ).'/assets/aboutCompany/aboutCompany.js', '', '', true);
		
		
		/** Акции, статьи, новости */
		
		wp_enqueue_style( 'rubricsStyle-main', esc_url( get_template_directory_uri() ).'/assets/rubrics/rubrics.css');
		
		/* categoryRubrics */
		
		wp_enqueue_style( 'rubricsStyle-categoryRubrics', esc_url( get_template_directory_uri() ).'/assets/rubrics/categoryRubrics/categoryRubrics.css');
		
		
		/** CATALOG */
		
		/* catalogPage */
		
		wp_enqueue_style( 'catalogStyle-catalogPage', esc_url( get_template_directory_uri() ).'/assets/catalog/catalogPage/catalogPage.css');
		wp_enqueue_script('catalogScripts-catalogPage', esc_url( get_template_directory_uri() ).'/assets/catalog/catalogPage/catalogPage.js', '', '', true);
		
		//Популярные запросы
		wp_enqueue_style( 'catalogStyle-catalogPageRequest', esc_url( get_template_directory_uri() ).'/assets/catalog/catalogPage/request/request.css');
		wp_enqueue_script('catalogScripts-catalogPageRequest', esc_url( get_template_directory_uri() ).'/assets/catalog/catalogPage/request/request.js', '', '', true);
		
		//Сортировка
		wp_enqueue_style( 'catalogStyle-catalogPageSort', esc_url( get_template_directory_uri() ).'/assets/catalog/catalogPage/sort/sort.css');
		
		//Список товаров
		wp_enqueue_style( 'catalogStyle-catalogPageProductsList', esc_url( get_template_directory_uri() ).'/assets/catalog/catalogPage/productsList/productsList.css');

		/** SEARCH */
		
		wp_enqueue_style('searchStyle-searchForm', esc_url( get_template_directory_uri() ).'/assets/search/searchForm.css');

		/** FOOTER */
		
		wp_enqueue_style( 'footerStyle-main', esc_url( get_template_directory_uri() ).'/assets/footer/footer.css');
	    
	}
	
	add_action('wp_enqueue_scripts','bmshop_scripts');
	
	function bito_header_menu(){
		if ( has_nav_menu( 'aside_menu' ) ){
			$settings = array(
				'theme_location' => 'aside_menu',
				'container'      => '',
				'container_class'=> '',
				'container_id'   => 'menu_aside',
				'menu_class'     => '',
				'items_wrap'      => '<menu id="%1$s" class="%2$s">%3$s</menu>',
				'walker'        => new bito_walker_nav_menu
			);
			wp_nav_menu($settings);
		} else {
			echo '<span class="no-menu">Пожалуйста создайте меню в шапке сайта, <a href="' . esc_url(admin_url('nav-menus.php')) . '" target="_blank">Внешний вид &gt; Меню</a></span>';
		}
	}
	
	add_action( 'init', 'bmshop_create_post_type' );
	
	function bmshop_create_post_type(){
	    
	    register_post_type( 'ourclients',
	        array(
	            'labels' => array(
	                'name' => 'Наши клиенты',
	                'singular_name' => 'Клиент',
	                'add_new' => 'Добавить клиента',
	                'add_new_item' => 'Добавить клиента',
	                'edit' => 'Редактировать',
	                'edit_item' => 'Редактировать клиента',
	                'new_item' => 'Новый клиент',
	                'view' => 'Просмотр',
	                'view_item' => 'Посмотреть клиента',
	                'search_items' => 'Поиск клиента',
	                'not_found' => 'Клиентов пока нет',
	                'not_found_in_trash' => 'клиенты не найдены в корзине',
	                'parent' => 'Parent клиент'
	            ),
	            'public' => true,
	            'show_in_menu' => true,
	            'exclude_from_search' => true,
	            'menu_icon' => 'dashicons-images-alt',
	            'menu_position' => 13,
	            'supports' => array( 'title' , 'thumbnail'),
	            'taxonomies' => array( 'post' ),
	            'has_archive' => true,
	            'query_posts' => false
	        )
	    );
	    
	    register_post_type( 'sertificates',
	        array(
	            'labels' => array(
	                'name' => 'Сертификаты',
	                'singular_name' => 'Сертификат',
	                'add_new' => 'Добавить сертификат',
	                'add_new_item' => 'Добавить сертификат',
	                'edit' => 'Редактировать',
	                'edit_item' => 'Редактировать сертификат',
	                'new_item' => 'Новый сертификат',
	                'view' => 'Просмотр',
	                'view_item' => 'Посмотреть сертификат',
	                'search_items' => 'Поиск сертификата',
	                'not_found' => 'Сертификатов пока нет',
	                'not_found_in_trash' => 'сертификаты не найдены в корзине',
	                'parent' => 'Parent сертификат'
	            ),
	            'public' => true,
	            'show_in_menu' => true,
	            'exclude_from_search' => true,
	            'menu_icon' => 'dashicons-images-alt',
	            'menu_position' => 13,
	            'supports' => array( 'title', 'thumbnail'),
	            'taxonomies' => array( 'post' ),
	            'has_archive' => true,
	            'query_posts' => false
	        )
	    );
	}
	
	function post_is_in_descendant_category( $cats, $_post = null ){
    	foreach ( (array) $cats as $cat ) {
    		// get_term_children() accepts integer ID only
    		$descendants = get_term_children( (int) $cat, 'category');
    		if( $descendants && in_category( $descendants, $_post ) )
    			return true;
    	}
    	return false;
    }
	
	
	function bito_top_menu(){
		if ( has_nav_menu( 'top_menu' ) ){
			$settings = array(
				'theme_location' => 'top_menu',
				'container'      => '',
				'container_class'=> '',
				'container_id'   => '',
				'menu_class'     => 'menu_top',
				'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
				'walker'        => new bito_walker_nav_topmenu
			);
			wp_nav_menu($settings);
		} else {
			echo '<span class="no-menu">Пожалуйста создайте меню в шапке сайта, <a href="' . esc_url(admin_url('nav-menus.php')) . '" target="_blank">Внешний вид &gt; Меню</a></span>';
		}
	}
	
	function bito_category_menu(){
		if ( has_nav_menu( 'top_menu' ) ){
			$settings = array(
				'theme_location' => 'aside_menu',
				'container'      => '',
				'container_class'=> '',
				'container_id'   => '',
				'menu_class'     => 'mobile-category-menu',
				'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
				'walker'        => new bito_walker_nav_topmenu
			);
			wp_nav_menu($settings);
		} else {
			echo '<span class="no-menu">Пожалуйста создайте меню в шапке сайта, <a href="' . esc_url(admin_url('nav-menus.php')) . '" target="_blank">Внешний вид &gt; Меню</a></span>';
		}
	}

	class bito_walker_nav_menu extends Walker_Nav_Menu {
  
		// add classes to ul sub-menus
		function start_lvl( &$output, $depth=0, $args=array() ) {
		  	$indent = str_repeat("\t", $depth);
		    $display_depth = ( $depth + 1);
		    if($display_depth == 1){
		    	$output .= "<ul class='sub_menu_out'>\n";
		    }
		    if($display_depth == 2){
		    	$output .= "<ul class='sub sub_menu_out'>\n";
		    }
		}
		function end_lvl(&$output, $depth=0, $args=array()) {
	        $output .= "</ul>\n";
	    }

	    function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
			global $wp_query;           
			$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
	 		$display_depth = ( $depth + 1);
			$class_names = $value = '';
			$classes = empty( $item->classes ) ? array() : (array) $item->classes;
			$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );
			//$class_names = ' class="' . esc_attr( $class_names ) . '"';
			//print_r($classes);
	 		if($display_depth == 3){
		    	$output .= "<li class='$class_names'>\n";
		    }
		    if($display_depth == 2){
		    	if ( in_array ('menu-item-has-children', $classes) ) {
			    	$output .= "<li class='sub_menu $class_names'>\n";
			    	$icon_arrow = '';
			    } else{
			    	$output .= "<li>";
			    	$icon_arrow = ''; 
			    }
		    }
		    if($display_depth == 1){
		    	if ( in_array ('menu-item-has-children', $classes) ) {
			    	$output .= "<li class='sub_menu $class_names'>\n";
			    } else {
			    	$output .="<li class='$class_names'>";
			    }
		    }
	 
			$attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
			$attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
			$attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
			$attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';

			$item_output = $args->before;
			$item_output .= '<a'. $attributes .'>';
			$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
			$item_output .= '</a>';
			$item_output .= $args->after;

	 		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		}
	 
	}
	
	class bito_walker_nav_topmenu extends Walker_Nav_Menu {
  
		// add classes to ul sub-menus
		function start_lvl( &$output, $depth=0, $args=array() ) {
		  	$indent = str_repeat("\t", $depth);
		    $display_depth = ( $depth + 1);
		    if($display_depth == 1){
		    	$output .= "<ul class='sub-menu'>\n";
		    }
		    if($display_depth == 2){
		    	$output .= "<ul class='sub-sub sub-menu'>\n";
		    }
		}
		function end_lvl(&$output, $depth=0, $args=array()) {
	        $output .= "</ul>\n";
	    }

	    function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
			global $wp_query;           
			$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
	 		$display_depth = ( $depth + 1);
			$class_names = $value = '';
			$classes = empty( $item->classes ) ? array() : (array) $item->classes;
			$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );
			//$class_names = ' class="' . esc_attr( $class_names ) . '"';
			//print_r($classes);
	 		if($display_depth == 3){
		    	$output .= "<li class='$class_names'>\n";
		    }
		    if($display_depth == 2){
		    	if ( in_array ('menu-item-parent', $classes) ) {
			    	$output .= "<li class='sub-menu $class_names'>\n";
			    	$icon_arrow = '';
			    } else{
			    	$output .= "<li>";
			    	$icon_arrow = ''; 
			    }
		    }
		    if($display_depth == 1){
		    	if ( in_array ('menu-item-parent', $classes) ) {
			    	$output .= "<li class='sub-menu $class_names'>\n";
			    } else {
			    	$output .="<li class='$class_names'>";
			    }
		    }
	 
			$attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
			$attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
			$attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
			$attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';

			$item_output = $args->before;
			$item_output .= '<a'. $attributes .'>';
			$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
			$item_output .= '</a>';
			$item_output .= $args->after;

	 		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		}
	 
	}

	function bito_mobile_menu(){
		if ( has_nav_menu( 'aside_menu'/*,'mobile_menu'*/ ) ){
			$settings = array(
				'theme_location' => 'aside_menu',
				'container'      => '',
				'container_class'=> '',
				'container_id'   => 'menu_mobile_out',
				'menu_class'     => '',
				'items_wrap'      => '<menu id="%1$s" class="%2$s">%3$s</menu>',
				'walker'        => new bitomobile_walker_nav_menu
			);
			wp_nav_menu($settings);
		} else {
			echo '<span class="no-menu">Пожалуйста создайте меню, <a href="' . esc_url(admin_url('nav-menus.php')) . '" target="_blank">Внешний вид &gt; Меню</a></span>';
		}
	}

	class bitomobile_walker_nav_menu extends Walker_Nav_Menu {
  
		// add classes to ul sub-menus
		function start_lvl( &$output, $depth=0, $args=array() ) {
		  	$indent = str_repeat("\t", $depth);
		    $display_depth = ( $depth + 1);
		    if($display_depth == 1){
		    	$output .= "<ul class='menu_mobile_sub_menu_out'>\n";
		    }
		}
		function end_lvl(&$output, $depth=0, $args=array()) {
	        $output .= "</ul>\n";
	    }

	    function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
			global $wp_query;           
			$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
	 		$display_depth = ( $depth + 1);
			$class_names = $value = '';
			$classes = empty( $item->classes ) ? array() : (array) $item->classes;
			if ( in_array ('current-menu-item', $classes) ){
				$classes[] = 'active';
			}
			$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );
			//$class_names = ' class="' . esc_attr( $class_names ) . '"';
			//print_r($classes);
	 		if($display_depth == 3){
		    	$output .= "<li class='$class_names'>\n";
		    }
		    if($display_depth == 2){
		    	if ( in_array ('menu-item-has-children', $classes) ) {
			    	$output .= "<li class='sub_menu $class_names'>\n";
			    	$icon_arrow = '';
			    } else{
			    	$output .= "<li>";
			    	$icon_arrow = ''; 
			    }
		    }
		    if($display_depth == 1){
		    	if ( in_array ('menu-item-has-children', $classes) ) {
			    	$output .= "<li class='menu_mobile_sub_menu $class_names'>\n";
			    } else {
			    	$output .="<li class='$class_names'>";
			    }
		    }
	 
			$attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
			$attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
			$attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
			$attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';

			$item_output = $args->before;
			$item_output .= '<a'. $attributes .'>';
			$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
			$item_output .= '</a>';
			$item_output .= $args->after;

	 		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		}
	 
	}
	
	function bito_mobile_topmenu(){
		if ( has_nav_menu( 'top_menu'/*,'mobile_menu'*/ ) ){
			$settings = array(
				'theme_location' => 'top_menu',
				'container'      => '',
				'container_class'=> '',
				'container_id'   => 'topmenu_mobile_out',
				'menu_class'     => '',
				'items_wrap'      => '<menu id="%1$s" class="%2$s">%3$s</menu>',
				'walker'        => new bitomobile_walker_nav_topmenu
			);
			wp_nav_menu($settings);
		} else {
			echo '<span class="no-menu">Пожалуйста создайте меню, <a href="' . esc_url(admin_url('nav-menus.php')) . '" target="_blank">Внешний вид &gt; Меню</a></span>';
		}
	}

	class bitomobile_walker_nav_topmenu extends Walker_Nav_Menu {
  
		// add classes to ul sub-menus
		function start_lvl( &$output, $depth=0, $args=array() ) {
		  	$indent = str_repeat("\t", $depth);
		    $display_depth = ( $depth + 1);
		    if($display_depth == 1){
		    	$output .= "<ul class='menu_mobile_sub_menu_out'>\n";
		    }
			if($display_depth == 2){
		    	$output .= "<ul class='menu_mobile_sub-sub_menu_out'>\n";
		    }
		}
		function end_lvl(&$output, $depth=0, $args=array()) {
	        $output .= "</ul>\n";
	    }

	    function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
			global $wp_query;           
			$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
	 		$display_depth = ( $depth + 1);
			$class_names = $value = '';
			$classes = empty( $item->classes ) ? array() : (array) $item->classes;
			if ( in_array ('current-menu-item', $classes) ){
				$classes[] = 'active';
			}
			$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );
			//$class_names = ' class="' . esc_attr( $class_names ) . '"';
			//print_r($classes);
	 		if($display_depth == 3){
		    	$output .= "<li class='$class_names'>\n";
		    }
		    if($display_depth == 2){
		    	if ( in_array ('menu-item-has-children', $classes) ) {
			    	$output .= "<li class='sub_menu $class_names'>\n";
			    	$icon_arrow = '';
			    } else{
			    	$output .= "<li>";
			    	$icon_arrow = ''; 
			    }
		    }
		    if($display_depth == 1){
		    	if ( in_array ('menu-item-has-children', $classes) ) {
			    	$output .= "<li class='menu_mobile_sub_menu $class_names'>\n";
			    } else {
			    	$output .="<li class='$class_names'>";
			    }
		    }
	 
			$attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
			$attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
			$attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
			$attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';

			$item_output = $args->before;
			$item_output .= '<a'. $attributes .'>';
			$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
			$item_output .= '</a>';
			$item_output .= $args->after;

	 		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		}
	 
	}



//По умолчанию длина по которой обрезается краткое содержание записи 55 символов
//Меняем это значение на 100
	/* Excerpt lenght */
	function bito_excerpt_length() {
		$length = 100;
		return $length;
	}
	add_filter( 'excerpt_length', 'bito_excerpt_length', 999 );

//Изменяем текст после обрезанного краткого содержания	
/* Excerpt more */
	function bito_excerpt_more() {
		return '...';
	}
	add_filter('excerpt_more', 'bito_excerpt_more');


	function bito_comments($comment, $args, $depth){
		$GLOBALS['comment'] = $comment;
		?>
			<div class="commentary-item clearfix">
                <div class="commentary-post clearfix">
                	<div class="time-name">
                        <span class="user-name">
                            <?php echo get_comment_author(); ?>
                        </span>
                        <span class="data-time">
                            <i class="time-ico"></i>
                            <span><?php echo get_comment_time('H:i'); ?></span> <span><?php echo get_comment_date('d.m.Y'); ?></span>
                        </span>
                    </div>
                    <div class="commentary-text">
                        <?php comment_text(); ?>
                    </div>
                    <div class="reply-block"><i class="fa fa-reply-all" aria-hidden="true"></i>
                    	<?php comment_reply_link( array_merge( $args, array( 'reply_text' => __( 'Ответить', 'bito' ), 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
                    </div>
                    
                </div>                            
            </div>
		<?php 
	}

	add_filter('comment_reply_link', 'replace_reply_link_class');


	function replace_reply_link_class($class){
	    $class = str_replace("class='comment-reply-link", "class='reply", $class);
	    return $class;
	}

	add_filter('comment_form_fields', 'bito_reorder_comment_fields' );
	function bito_reorder_comment_fields( $fields ){

		$new_fields = array();

		$myorder = array('author','email','comment');

		foreach( $myorder as $key ){
			$new_fields[ $key ] = $fields[ $key ];
			unset( $fields[ $key ] );
		}

		// если остались еще какие-то поля добавим их в конец
		if( $fields )
			foreach( $fields as $key => $val )
				$new_fields[ $key ] = $val;

		return $new_fields;
	}



/**
 * Registers a widget area.
 */
function zbk_widgets_init() {
	
	register_sidebar( array(
		'name'          => 'Подвал 1',
		'id'            => 'footer-1',
		'description'   => '',
		'before_widget' => '',
		'after_widget'  => '',
		'before_title'  => '',
		'after_title'   => '',
	) );

	register_sidebar( array(
		'name'          => 'Подвал 2',
		'id'            => 'footer-2',
		'description'   => '',
		'before_widget' => '',
		'after_widget'  => '',
		'before_title'  => '',
		'after_title'   => '',
	) );
	
	register_sidebar( array(
		'name'          => 'Подвал 3',
		'id'            => 'footer-3',
		'description'   => '',
		'before_widget' => '',
		'after_widget'  => '',
		'before_title'  => '',
		'after_title'   => '',
	) );
	
	register_sidebar( array(
		'name'          => 'Подвал 4',
		'id'            => 'footer-4',
		'description'   => '',
		'before_widget' => '',
		'after_widget'  => '',
		'before_title'  => '',
		'after_title'   => '',
	) );
	
}
add_action( 'widgets_init', 'zbk_widgets_init' );

// Display three posts per page is home
function custom_posts_per_page_home($query) {
	if ($query->is_home() && $query->is_main_query()) {
		$query->set('posts_per_page', '4');
	}
	/*
	// if is taxonomy page and main query
	if ( $query->is_tax() && $query->is_main_query() ) {
		$query->set( 'orderby', 'rand' );
	}
	*/
}
add_action('pre_get_posts', 'custom_posts_per_page_home');

function hide_hit_class(){
	if(is_tax('catalog') || is_singular('product')){
	} else {
		echo 'hide_hit_class';
	}
};


/*** 
 * API All in one seo pack
 * 
*/

function changeDescription($desc){
    
    $Path=$_SERVER['REQUEST_URI'];

    if( strpos($Path,"page") || strpos($Path,"price_from") ){
        
        return false;
    }
    
    if(get_post_type($post_id) === "product" && get_queried_object()->ID ){
        
        $desc = get_queried_object()->post_title." — по оптимальной цене";
    }
        
    return $desc;
}

function changeKeywords($keywords){
    
    $Path=$_SERVER['REQUEST_URI'];

    if( strpos($Path,"page") || strpos($Path,"price_from") ){
        
        return false;
    }
    
    if(get_post_type($post_id) === "product" && get_queried_object()->ID ){
        
        $keywords = get_queried_object()->post_title." купить заказать Москва Истра";
    }
        
    return $keywords;
}


function changeTitle($title){
    
    $Path=$_SERVER['REQUEST_URI'];
    
    if ($title{strlen($title)-1} == '|') {
        
        $title = substr($title,0,-2);
        
    }
    
    if( strpos($Path,"price_from") ){
        
        $filters = explode("&",$Path);
        
        $filters[0] = substr( $filters[0],strpos($filters[0],"?")+1 );
        
        $price_from = $price_to = $floor = $area_from = $area_to = $type = $balkon = false;
        
        $sizes = array();
        
        for($i=0; $i < count($filters); $i++){
            
            if( $price_from === false && strpos($filters[$i],"price_from") !== false ){  
                
                $price_from = substr( $filters[$i],strpos($filters[$i],"=")+1 );
                
                $title .= " | Цена от ".$price_from;
            }
            
            if( $price_to === false && strpos($filters[$i],"price_to") !== false ){  
                
                $price_to = substr( $filters[$i],strpos($filters[$i],"=")+1 );
                
                $title .= " до ".$price_to;
            }
            
            if( $floor === false && strpos($filters[$i],"floor") !== false ){  
                
                $floor = substr( $filters[$i],strpos($filters[$i],"=")+1 );
                
                $title .= " Этажность: ".$floor;
            }
            
            if( $area_from === false && strpos($filters[$i],"area_from") !== false ){  
                
                $area_from = substr( $filters[$i],strpos($filters[$i],"=")+1 );
                
                $title .= " Площадь, м²: от ".$area_from;
            }
            
            if( $area_to === false && strpos($filters[$i],"area_to") !== false ){  
                
                $area_to = substr( $filters[$i],strpos($filters[$i],"=")+1 );
                
                $title .= " до ".$area_to;
            }
            
            if( $type === false && strpos($filters[$i],"type") !== false ){  
                
                $type = str_replace("+"," ", rawurldecode(substr( $filters[$i],strpos($filters[$i],"=")+1 ) ) );
                
                $title .= " Тип: ".$type;
            }
            
            if( strpos($filters[$i],"sizes") !== false ){ 
                
                $sizes[] = substr( $filters[$i],strpos($filters[$i],"=")+1 );
                
            }
        }
        
        $product_features = get_terms( 'features', array( 'hide_empty' => false ) );
        $featuresAvailability = false;
        
        for($i=0; $i < count($product_features); $i++){
            
            if( strpos($Path, $product_features[$i]->slug) ){
                
                if(!$featuresAvailability){
                    
                    $title .= " Особенности:";
                    $featuresAvailability = true;
                }
                
                $title .= " ".$product_features[$i]->name;
            }
        }
        
        $terms_size = get_terms( array('sizes'), array('hide_empty' => false, "fields"=>"id=>name")  );
        $sizeAvailability = false;
        
        if($sizes[0]){
            
            for($i=0; $i < count($sizes); $i++){
                    
                if(!$sizeAvailability){
                        
                    $title .= " Размеры:";
                    $sizeAvailability = true;
                }
                    
                $title .= " ".$terms_size[$sizes[$i]];
                
            }
            
        }
        
    }
    
    
    if(get_post_type($post_id) === "product" && get_queried_object()->ID ){
        
        $title = get_queried_object()->post_title." — купить по доступной цене";
    }
    
    
    return $title;
}

function change_canonical_url( $url ){
    
    $Path=$_SERVER['REQUEST_URI'];
    
    if( strpos($Path,"price_from") ){
        
        $url = site_url().substr( $Path,0,strpos($Path,"?") );
    }
    
    return $url;
}


add_filter( 'aioseop_description', 'changeDescription', 10, 1);
add_filter( 'aioseop_keywords', 'changeKeywords', 10, 1);
add_filter( 'aioseop_title', 'changeTitle', 10, 1);
add_filter('aioseop_canonical_url','change_canonical_url', 10, 1);

add_action('template_redirect', 'true_catalog_redirect');
 
function true_catalog_redirect() {
 
	$taxonomy_name = 'catalog'; 
 
	if( strpos( $_SERVER['REQUEST_URI'], $taxonomy_name ) === false ) 
		return;
		
	if( strpos( $_SERVER['REQUEST_URI'], "price_from" ) !== false ) 
		return;
    
    $term = get_term_by( 'slug', get_query_var( 'term' ), $taxonomy_name );
    
    if( $term && $term->parent !== 0){
        
        $term_parent = get_term_by( 'term_taxonomy_id', $term->parent, $taxonomy_name );
        
        if( strpos( $_SERVER['REQUEST_URI'], $term_parent->slug ) === false ){
            
            wp_redirect( site_url() . '/' . $taxonomy_name . '/' . $term_parent->slug . '/' . $term->slug, 301 );
            
        }
        
    }
 
}


/*** 
 * ДОБАВЛЕНИЕ ПОЛЕЙ
 * 
*/


function html_detailed_fields($taxonomy, $fields, $fields_promo, $type){
    
    
    echo "<table class='detailed-fields'>";
    
    if( $taxonomy=="boilers" ){
        
        
        $args = array(
        	'taxonomy' => 'boilers',
        	'hide_empty' => false,
        );
        
        $terms = get_terms( $args );
        
        
        
        foreach($terms as $term){
            //echo $term->name;
            
            $price = get_field('price_boilers',$taxonomy."_".$term->term_id);
            $price_promo = get_field('price_promotion',$taxonomy."_".$term->term_id);
            $total_price = $price;
            
            if($price_promo){
                $total_price = $price_promo;
            }
            
            echo "<tr>";
            
                    
            
                echo "<td class='detailed-fields-key'><a target='_blank' title='Узнать подробнее, печь ".$term->name."' href='".get_term_link($term->name, $taxonomy)."'>".$term->name."</a></td>";
            
                    
            if($price_promo){
                        
                echo "<td class='detailed-fields-value'><span class='old_price'>+".$price."</span> <span class='new_price'>+".$price_promo." руб</span>  </td>";

            }else if($price){
                        
                echo "<td class='detailed-fields detailed-fields-value'>+".$price." руб </td>";
                
            }else{
                        
                echo "<td class='detailed-fields detailed-fields-value'> ? руб </td>";
                
            }
            
            echo "</tr>";
            
            //echo " ".$price." ".$price_promo;
            
        }
        
        echo "</table>";
        
        return;
    }
    
    foreach($fields as $key=>$value){
        
            if($value){
                
                echo "<tr>";   
                
                if($key==191){
                    $key = 195;
                }
                
                $term = get_term_by(id,$key,$taxonomy);
                
                if(!$term){
                    continue;
                }
                
                $global_promo = get_field('price_promotion',$taxonomy."_".$key);
                
                $total_price = $value;
            
                if( !empty( $fields_promo[$key] ) ){
                    
                    $total_price = $fields_promo[$key];
                    
                }else if($global_promo){
                    
                    $total_price = $global_promo;
                    
                }
                
                if( $taxonomy=="walls" ){
                    
                    if($type == "calculator"){ ?>
                        
                        <td class='detailed-fields-key'>
                            <label class="filter-switch-calculator"> 
                                
                                <input checked data-value="<?php echo $total_price; ?>" data-label="<?php echo $term->name.' см'; ?>" type="radio" class="filter-switch-input" name="<?php echo $taxonomy; ?>" onchange="editPrice( $(this) )">
                                <div class="filter-switch-slider filter-switch-slider-calculator"></div>
                                
                                 <span class="filter-switch-label-calculator"> <?php echo $term->name." см"; ?> </span> 
                            
                            </label>
                        </td>
                        
                    <?php }else{
                    
                        echo "<td class='detailed-fields-key'>".$term->name."</a> см</td>";
                    
                        }
                    
                    if( $total_price != $value ){
                    
                       echo "<td class='detailed-fields-value'> <span class='old_price'>".$value."</span> <span class='new_price'>".$total_price." руб</span>  </td>";
                   
                    }else{
                        
                        echo "<td class='detailed-fields detailed-fields-value'>".$value." руб </td>";
                    }
                    
                }else{
                    
                    if($type == "calculator"){ ?>
                        
                        <td class='detailed-fields-key'>
                            <label class="filter-switch-calculator"> 
                                
                                <?php if( $taxonomy=="foundation" ){ ?>
                                    
                                    
                                    <?php //Фундамент, проверка на наличие текста в блоке с ценой по скидке
                                        if( is_numeric($total_price) ){ ?>
                                
                                        <input type="radio" data-value="<?php echo $total_price; ?>" data-label="<?php echo $term->name; ?>" class="filter-switch-input" name="<?php echo $taxonomy; ?>" onchange="editPrice( $(this) )">
                                        <div class="filter-switch-slider filter-switch-slider-calculator"></div>
                                        
                                    <?php }else{ ?>
                                    
                                        <input disabled type="radio" data-value="<?php echo $total_price; ?>" data-label="<?php echo $term->name; ?>" class="filter-switch-input" name="<?php echo $taxonomy; ?>" onchange="editPrice( $(this) )">
                                        <div class="filter-switch-slider filter-switch-slider-calculator filter-switch-slider-disabled"></div>
                                        
                                    <?php } ?>
                                                    
                                    
                                <?php }else{ ?>
                                
                                    <?php //Услуги, проверка на наличие текста в блоке с ценой по скидке
                                        if( is_numeric($total_price) ){ ?>
                                
                                        <input type="checkbox" data-value="<?php echo $total_price; ?>" data-label="<?php echo $term->name; ?>" class="filter-switch-input" name="<?php echo $taxonomy; ?>" onchange="editPrice( $(this) )">
                                        <div class="filter-switch-slider filter-switch-slider-calculator"></div> 
                                        
                                    <?php }else{ ?>
                                    
                                        <input disabled type="checkbox" data-value="<?php echo $total_price; ?>" data-label="<?php echo $term->name; ?>" class="filter-switch-input" name="<?php echo $taxonomy; ?>" onchange="editPrice( $(this) )">
                                        <div class="filter-switch-slider filter-switch-slider-calculator filter-switch-slider-disabled"></div>
                                        
                                    <?php } ?>
                                
                                    
                                <?php } ?>
                                
                                <span class="filter-switch-label-calculator"> <a target='_blank' title='Узнать подробнее, "<?php echo $term->name; ?>"' href='<?php echo get_term_link($key, $taxonomy); ?>'> <?php echo $term->name; ?> </a>  </span> 
                            
                            </label>
                        </td>
                        
                    <?php }else{
                    
                        echo "<td class='detailed-fields-key'><a target='_blank' title='Узнать подробнее, ".$term->name."' href='".get_term_link($key, $taxonomy)."'>".$term->name."</a></td>";
                    
                    }
                    if( $total_price != $value ){
                        
                        if( is_numeric($total_price) ){
                            echo "<td class='detailed-fields-value'> <span class='old_price'>+".$value."</span> <span class='new_price'>+".$total_price." руб</span>  </td>";
                        }else{
                            echo "<td class='detailed-fields-value'> <span class='old_price'>+".$value."</span> <span class='new_price'> ".$total_price."</span>  </td>";
                        }
                       
                    }else{
                        
                        echo "<td class='detailed-fields detailed-fields-value'>+".$value." руб </td>";
                    }
                    
                }
                
                
                
                
                echo "</tr>";
            
            }
            
        }
        
        echo "</table>";
}





/* --------------------------------------------------------------------------
 * Отключаем Emojii
 * -------------------------------------------------------------------------- */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_styles', 'print_emoji_styles' ); 
remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
remove_filter( 'comment_text_rss', 'wp_staticize_emoji' ); 
remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
add_filter( 'tiny_mce_plugins', 'disable_wp_emojis_in_tinymce' );
function disable_wp_emojis_in_tinymce( $plugins ) {
    if ( is_array( $plugins ) ) {
        return array_diff( $plugins, array( 'wpemoji' ) );
    } else {
        return array();
    }
}
/* --------------------------------------------------------------------------- */


require get_template_directory() . '/includes/cardProduct/cardProduct.php';
?>