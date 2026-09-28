<?php

<?php
/*
Plugin Name:       Argon Shop
Plugin URI:        https://github.com/Argonstudio/ArgonShop/tree/historic/php56-jquery
Description:       Плагин интернет-магазина. Историческая версия 2018 года с поддержкой большого количества товаров, версткой корзины и личного кабинета прямо в теме.
Version:           0.8.0
Author:            Иван Войтков (Argon Studio)
Author URI:        http://argon-studio.ru
License:           GNU General Public License v2.0 or later
License URI:       http://gnu.org
Text Domain:       argon-shop
Requires at least: 4.9
Requires PHP:      5.6

Argon Shop WordPress Plugin, Copyright 2018 Ivan Voitkov.
Argon Shop is distributed under the terms of the GNU General Public License v2.0 or later.

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

// Запрет прямого доступа к файлу плагина (Good Practice)
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//Получаем путь к плагину
$directory = plugin_dir_path( __FILE__ );

//Функция для фильтра преобразующего name цен в числа для правильной сортировки
function sort_terms_clause( $orderby, $args, $taxonomies ){
    	return 't.name+0';
    }


//Страница настроек магазина
require_once $directory . 'settingPage.php';


/*
** API
*/

//Функции действия
require_once $directory . '/includes/api/as_action.php';

//Функции проверки
require_once $directory . '/includes/api/check.php';

//Функции получения данных различными модулями
require_once $directory . '/includes/api/getData/getData.php';
require_once $directory . '/includes/api/getData/orders.php';
require_once $directory . '/includes/api/getData/catalog.php';

require_once $directory . '/includes/api/getData/product/productLists/marked.php';
require_once $directory . '/includes/api/getData/product/productLists/catalog.php';
require_once $directory . '/includes/api/getData/product/productLists/history.php';

require_once $directory . '/includes/api/getData/admin/get_characteristics.php';

//Функции получения настроек
require_once $directory . '/includes/api/getSetting.php';	


/*
** VIEW
*/

require_once $directory . '/includes/api/view/product/productsLists/productsLists.php';
require_once $directory . '/includes/api/view/catalog/catalogPage.php';

function add_admin_styleScript(){
    wp_enqueue_style("style-admin", plugins_url('/assets/admin/css/fields-catalog.css', __FILE__) );
    wp_enqueue_style("order-style-admin", plugins_url('/assets/admin/css/order.css', __FILE__) );
    wp_enqueue_script('script-admin', plugins_url('/assets/admin/js/admin-script.js', __FILE__), '', '', true);
    wp_enqueue_script('orders-admin', plugins_url('/assets/admin/js/orders.js', __FILE__), '', '', true);
}
        
add_action('admin_head', 'add_admin_styleScript');

//Меняем "роль нового пользователя" на подписчика
add_filter('pre_option_default_role', function($default_role){
    return 'subscriber'; 
});

function add_interface_styleScript(){
    wp_enqueue_style("style-interface", plugins_url('/assets/interface/css/interface-style.css', __FILE__) );
    
    wp_deregister_script('jquery');
    wp_enqueue_script('jquery', plugins_url('/assets/interface/js/jquery.js', __FILE__), '', '', true);
    wp_enqueue_script('site-script', plugins_url('/assets/interface/js/site.js', __FILE__), '', '', true);
    
    wp_localize_script('site-script', 'myajax', 
    		array(
    			'url' => admin_url('admin-ajax.php')
    		)
    	); 
    
    //wp_enqueue_script('jquery', plugins_url( '/assets/interface/js/jquery.js', __FILE__), '', '', true);

    if( get_post_type( get_the_ID() ) == "product" ){
        
        wp_enqueue_script('product-script', plugins_url('/assets/interface/js/product.js', __FILE__), '', '', true);
        
    }else if( get_the_ID() == get_cabinetPageID() ){

        wp_enqueue_script('cabinet-script', plugins_url('/assets/interface/js/cabinet.js', __FILE__), '', '', true);
        
    }else if( get_the_ID() == get_cartPageID() ){

        wp_enqueue_script('cart-script', plugins_url('/assets/interface/js/cart.js', __FILE__), '', '', true);
        
    }
    
    wp_enqueue_script('catalogSort', plugins_url('/assets/interface/js/catalog/sort.js', __FILE__), '', '', true);
    wp_enqueue_script('cardProduct', plugins_url('/assets/interface/js/cardProduct.js', __FILE__), '', '', true);
    wp_enqueue_script('controlPanels', plugins_url('/assets/interface/js/controlPanels.js', __FILE__), '', '', true);
		
}


add_action('wp_enqueue_scripts','add_interface_styleScript');



//Создаем типы постов и таксономии
require_once $directory . '/includes/createPostType.php';

/*
** МОДУЛИ
*/
	
//Дополнительные фильтры
require_once $directory . '/includes/admin/additionalFilters.php';
    
//Дополнительные поля при редактировании терминов таксономии
require_once $directory . '/includes/admin/fieldsTaxonomy.php';
    
//Дополнительные поля при редактировании товаров
require_once $directory . '/includes/admin/fieldsProduct.php';
	
    

        
//Корзина покупок
require_once $directory . '/includes/interface/shoppingCart/shoppingCart.php';

//Виджет корзины 
require_once $directory . '/includes/interface/shoppingCart/infoCart.php';

//Карточки товаров 
require_once $directory . '/includes/interface/product/cardProduct.php';
       
//Сортировка каталога
require_once $directory . '/includes/interface/catalog/sort.php';       
       
//Личный кабинет
require_once $directory . '/includes/interface/cabinet/myCabinet.php';

//Вывод заказов для кабинета
require_once $directory . '/includes/interface/cabinet/userOrders/ordersView.php';

//Ajax фильтры заказов
require_once $directory . '/includes/interface/cabinet/userOrders/ordersAjax.php';
    
//Пути по сайту
require_once $directory . '/includes/interface/breadcrumbs.php';
    
//Переключатели страниц
require_once $directory . '/includes/interface/pagenavi.php'; 

//Вывод формы поиска
require_once $directory . '/includes/interface/search/searchForm.php'; 
    
//Ajax события поиска
require_once $directory . '/includes/interface/search/searchAjax.php'; 

//Фильтр результатов поиска при выводе на отдельной странице /?s=
require_once $directory . '/includes/interface/search/searchGetFilters.php'; 
    
//История просмотров
require_once $directory . '/includes/interface/history.php';

//Вывод заказа в админке
require_once $directory . '/includes/admin/order/orderMetabox.php';	

//ajax функционал на странице заказа
require_once $directory . '/includes/admin/order/orderAjax.php';

//Страница заказа сохранение
require_once $directory . '/includes/admin/order/orderSave.php';

//Переадресация пользователя при входе
require_once $directory . '/includes/interface/cabinet/redirectUser.php';

//Сеть сайтов
require_once $directory . '/includes/siteLine.php'; 


 
