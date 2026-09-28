<?php

add_filter( 'wp_nav_menu_objects', 'change_nav_menu_objects', 10, 2 );

//Замена метки as_homepage в меню на ссылку
function change_nav_menu_objects( $sorted_menu_items, $args ) {	
	
	foreach ( $sorted_menu_items as $index => $item ) {
		if ( stristr($item->url,"as_homepage") ) {
		    
		    $sorted_menu_items[$index]->url = str_replace("as_homepage",$_SERVER['SERVER_NAME'],$sorted_menu_items[$index]->url);
		}
	}
	

	return $sorted_menu_items;
}