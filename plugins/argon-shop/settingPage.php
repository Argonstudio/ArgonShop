<?php
add_action('admin_menu', 'setting_register_admin_page');
function setting_register_admin_page() {

    //Добавляем страницу настроек магазина
    //Заголовок страницы, Название пункта меню, права для доступа к странице, слаг страницы, функция вывода настроек
    add_options_page( 'Настройки магазина', 'Магазин', 'manage_options', 'settings-shop', 'settings_shop_rendering' );
}

function settings_shop_rendering() {
    ?>
	<div class="wrap">
		<h2><?php echo get_admin_page_title() ?></h2>

		<form action="options.php" class="settingShopForm" method="POST">
			<?php
			
			    //Выводим защитные поля
			    //Параметром передам название группы опций
				settings_fields( 'option_group' );  
				
				//Выводим секции с настройками
				//Параметром передаем название страницы с настройками
				//Название используется только здесь и при создании опций(в данном случае)
				do_settings_sections( 'settingShopPage' ); 
				
				submit_button();
			?>
		</form>
	</div>
	<?php
}


/**
 * Регистрируем настройки.
 * Настройки будут храниться в массиве, а не одна настройка = одна опция.
 */
add_action('admin_init', 'plugin_settings');
function plugin_settings(){
    
	// Регистрируем новую группу опций
	//Название группы опций, имя под которым опции будут сохраняться в базу данных, функция обработки значений перед сохранением в базу
	register_setting( 'option_group', 'settingShop', 'sanitize_callback' );
    
    //Добавляем секцию с настройками, опциями
	// параметры: id секции, название секции, здесь может быть функция, название страницы настроек
	add_settings_section( 'mainSettingShop', 'Основные настройки', '', 'settingShopPage' ); 

	// параметры: id опции, выводимое название, функция вывода опции, название страницы со всеми опциями, id секции
	add_settings_field('shoppingCartPage', 'ID страницы корзины', 'shoppingCartPage_callback', 'settingShopPage', 'mainSettingShop' );
	add_settings_field('cabinetPage', 'ID страницы кабинета', 'cabinetPage_callback', 'settingShopPage', 'mainSettingShop' );
	
	
	//Добавляем секцию с настройками, опциями
	// параметры: id секции, название секции, здесь может быть функция, название страницы настроек
	add_settings_section( 'discountsSettingShop', 'Скидки', '', 'settingShopPage' ); 
	
	$saleParemeters = Array(
	    
	    "typeSale"=>Array(
	        
	        "noSale"=>"Без скидок",
	        "wholesalePriceSteps" => "Шаги оптовых цен"
	        
	        )
	    );

	// параметры: id опции, выводимое название, функция вывода опции, название страницы со всеми опциями, id секции, параметры которые получит функция вывода опции

	add_settings_field('typeSale', 'Тип системы скидок', 'typeSale_callback', 'settingShopPage', 'discountsSettingShop',$saleParemeters );
	//add_settings_field('wholesalePriceSteps', 'Шаги оптовых цен', 'wholesalePriceSteps_callback', 'settingShopPage', 'discountsSettingShop' );
	
	
	// параметры: $id, $title, $callback, $page
	add_settings_section( 'userDataShop', 'Данные пользователя', '', 'settingShopPage' ); 
	
	
	// параметры: $id, $title, $callback, $page, $section, $args
	add_settings_field('quickLine', 'Быстрый заказ', 'quick_callback', 'settingShopPage', 'userDataShop' );

	// параметры: $id, $title, $callback, $page, $section, $args
	add_settings_field('personLine', 'Физические лица', 'person_callback', 'settingShopPage', 'userDataShop' );
	add_settings_field('legalPersonLine', 'Юридические лица', 'legalPerson_callback', 'settingShopPage', 'userDataShop' );
}

function typeSale_callback($arguments){
    
    //Создает переменные из ключей массива, $typeSale в данном случае
    extract( $arguments );
    
    $setting = get_option('settingShop');
    $option_name = "settingShop";
    $id = "typeSale";
    
    echo "<fieldset>";
    
    foreach($typeSale as $key => $value){
        
		$checked = ( (!$setting[$id] && $key == "noSale") || $setting[$id] == $key ) ? "checked='checked'" : '';  
		echo "<label><input type='radio' name='{$option_name}[$id]' value='$key' $checked />$value</label><br />";
        
    }
    
    echo "</fieldset>"; 
     
	?>
	<!-- <input type='checkbox' id='wholesalePriceSteps' name="settingShop[wholesalePriceSteps]" <?//=$checked ?> /> -->
	<?php
}

## Заполняем опцию 1
function shoppingCartPage_callback(){
	$val = get_option('settingShop');
	
	$val = $val ? $val['shoppingCartPage'] : null;
	?>
	<input type="text" name="settingShop[shoppingCartPage]" value="<?php echo esc_attr( $val ) ?>" />
	<?php
}

## Заполняем опцию 2
function cabinetPage_callback(){
	$val = get_option('settingShop');
	$val = $val ? $val['cabinetPage'] : null;
	?>
	<input type="text" name="settingShop[cabinetPage]" value="<?php echo esc_attr( $val ) ?>" />
	<?php
}

function quick_callback(){
	$val = get_option('settingShop');
	$val = $val ? $val['quickLine'] : null;
	?>
	<textarea class="large-text code" name="settingShop[quickLine]"><?php echo esc_attr( $val ) ?></textarea>
	<?php
}

function person_callback(){
	$val = get_option('settingShop');
	$val = $val ? $val['personLine'] : null;
	?>
	<textarea class="large-text code" name="settingShop[personLine]"><?php echo esc_attr( $val ) ?></textarea>
	<?php
}


function legalPerson_callback(){
	$val = get_option('settingShop');
	$val = $val ? $val['legalPersonLine'] : null;
	?>
	<textarea class="large-text code" name="settingShop[legalPersonLine]"><?php echo esc_attr( $val ) ?></textarea>
	<?php
}

## Очистка данных
function sanitize_callback( $options ){ 
	// очищаем
	foreach( $options as $name => $value ){
		
			$options[$name] = strip_tags( $value );
			
			if($name == "quickLine" || $name == "personLine" || $name == "legalPersonLine"){
			    
			    
			    $nameArray = str_replace("Line","",$name);
			    
			    $options[$nameArray] = explode("\n",$value);
			    
			    $newValue = Array();
			    
			    foreach( $options[$nameArray] as $numberLine => $undividedLine){
			        
			        $separationKeyValue = explode("[",$undividedLine);
			        
			        $separationKeyValue[1] = str_replace("]","",$separationKeyValue[1]);
			        
			        if($separationKeyValue[0]){
			            
			            $separationValueOfLine = explode(",",$separationKeyValue[1]);
			            
			            $transformInArray = Array(
			                       "name" => $separationValueOfLine[0],
			                       "placeholder" => str_replace("/запятая/",",",$separationValueOfLine[1]),
			                       "size" => $separationValueOfLine[2]
			                
			                );
			                           
			            
			            $newValue[$separationKeyValue[0]] = $transformInArray;
			        }	        
			        
			    }
			    
			    $options[$nameArray] = $newValue;
			    
			}
			

		if( $name == 'wholesalePriceSteps' )
			$val = intval( $val );
	}

	return $options;
}

