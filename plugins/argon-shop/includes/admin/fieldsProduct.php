<?php

add_action( 'admin_menu', 'detailedfields_remove_meta_box');

function detailedfields_remove_meta_box(){
   
   remove_meta_box('tagsdiv-wholesalePrice', 'product', 'normal');
   remove_meta_box('tagsdiv-characteristics', 'product', 'normal');
}

//Добавляем новые метабоксы таксономии 
 add_action( 'add_meta_boxes', 'detailedfields_add_meta_box');
 
 function detailedfields_add_meta_box() {
     
     add_meta_box( 'mainParameters_meta_box', 'Основные параметры','mainParameters_metabox','product' ,'normal','core');
     
     if( check_saleSteps() ){
     
         add_meta_box( 'wholesalePrice_meta_box', 'Цена со скидкой','wholesalePrice_metabox','product' ,'normal','core');
     
     }
        
     add_meta_box( 'characteristics_meta_box', 'Характеристики','characteristics_metabox','product' ,'normal','core');
     
     
     

     
 }

function mainParameters_metabox( $post ){
    
    wp_nonce_field( basename( __FILE__ ), 'detailed_fields' );
    
    $price = get_post_meta($post->ID, '_price',true);
    $weight = get_post_meta($post->ID, '_weight',true);
    $article = get_post_meta($post->ID, '_article',true);
    $hitSales = get_post_meta($post->ID, '_hit',true);
    $newProduct = get_post_meta($post->ID, '_newProduct',true);
    
    ?>
    
    <ul class='advanced-fields'>
        
        <li class="advanced-field"> 
            <div class="span-advanced-fields">Цена</div>
            <input type="text" name="_price" placeholder="Цена товара" value="<?php echo $price; ?>"> ₽/шт
                         
        </li>
        
        <li class="advanced-field"> 
            <div class="span-advanced-fields">Вес</div>
            <input type="text" name="_weight" placeholder="Вес товара" value="<?php echo $weight; ?>"> кг
                         
        </li>
        
        <li class="advanced-field"> 
            <div class="span-advanced-fields">Артикул</div>
            <input type="text" name="_article" placeholder="Артикул" value="<?php echo $article; ?>">
                         
        </li>
        
        <li class="advanced-field"> 
        
            <?php $checkedHit = ( $hitSales == "on" ) ? "checked='checked'" : ''; ?>
        
            <input name="_hit" type="checkbox" <?=$checkedHit ?>> <span class="span-advanced-fields">Хит продаж</span>
            
        
        </li>
        
        <li class="advanced-field">                  
        
            <?php $checkedNew = ( $newProduct == "on" ) ? "checked='checked'" : ''; ?>
        
            <input name="_newProduct" type="checkbox" <?=$checkedNew ?>> <span class="span-advanced-fields">Новинка</span>
                
                         
        </li>
        
        
        
    </ul>
    
    <?php

}
 
 function get_product_parentterms($post, $activeCategoryId){
     
    $product_parentterms = [];
    
    $product_terms = [];
    
    if(!$activeCategoryId){
        
        $product_terms = get_the_terms( $post->ID, 'catalog' );
        
    }else{
        
        foreach( $activeCategoryId as $category_id ){
            
            array_push( $product_terms, get_term($category_id) );
            
        }
        
    }
    
    foreach( $product_terms as $product_term ){
        
        if($product_term->parent == 0){
            
            array_push($product_parentterms, $product_term->term_id);
            
        }else{
            
            array_push($product_parentterms, $product_term->term_id);
            $parent_id = $product_term->parent;
            
        	while( $parent_id ){
        		$product_term = get_term_by( 'id', $parent_id, $product_term->taxonomy );
        		$parent_id = $product_term->parent;
        	}
        	
            array_push($product_parentterms, $product_term->term_id);
        }
        
    }
    
    return $product_parentterms;
 }

function wholesalePrice_metabox( $post ) {
    
    
    //Добавляем фильтр который преобразует name цен в числа для правильной сортировки
    add_filter('get_terms_orderby', 'sort_terms_clause', 10, 3);
    
    $args = array(
    	'taxonomy' => 'wholesalePrice',
    	'hide_empty' => false,
    );
    
    $terms = get_terms( $args );
    
    
        if( $terms && ! is_wp_error($terms) ){
            
            wp_nonce_field( basename( __FILE__ ), 'detailed_fields' );
            $wholesalePrice = get_post_meta($post->ID, '_wholesalePrice',true);
            
            ?>
            
            <?php
        	echo "<ul class='advanced-fields'>";
        	foreach( $terms as $term ){
        	    ?>
                    
                     <li class="advanced-field"> 
                         <div class="span-advanced-fields">от <?php echo $term->name ?></div>
                         <input type="text" name="wholesalePrice[<?php echo $term->term_id ?>]" placeholder="Цена по скидке" value="<?php echo $wholesalePrice[$term->term_id]?>">
                     
                     </li>
                    
                    <?php 
                    
                
        
        	}
        	echo "</ul>";
        	
        }
        
    // удаляем фильтр
    remove_filter('get_terms_orderby', 'sort_terms_clause', 10);    
        
}



$characteristics_in_stock = false;

function characteristics_metabox( $post, $taxonomy, $activeCategoryId, $characteristicsTextField, $characteristicsCheckboxField ) {
    
    if($activeCategoryId != "no categories") {
        $product_parentterms = get_product_parentterms($post, $activeCategoryId);
    }else{
        echo "Нет подходящих характеристик</br>Выберите категорию в каталоге";
        return;
    }
    
    
    $args = array(
    	'taxonomy' => 'characteristics',
    	'hide_empty' => false,
    );
    
    $terms = get_terms( $args );
  
        if( $terms && ! is_wp_error($terms) ){
            
            wp_nonce_field( basename( __FILE__ ), 'detailed_fields' );
            
            //Получаем данные со значениями полей из базы данных, если они не передавались по ajax
            if(!$characteristicsTextField) $characteristicsTextField = get_post_meta($post->ID, '_characteristics_text_field',true); 
            if(!$characteristicsCheckboxField) $characteristicsCheckboxField = get_post_meta($post->ID, '_characteristics_checkbox_field',true);
            
            
            ?>
            
            <?php
            
        	echo "<ul class='advanced-fields'>";
        	
        	$characterictics_price = [];
        	$characterictics_list = [];
        	
        	
        	//Делим Характеристики на неимеющих(ввод значения) и имеющих(выбор варианта) дочерние элементы
        	foreach( $terms as $term ){
        	    
        	    if($term->parent == 0){
        	        $termChildren = get_term_children($term->term_id,characteristics);
        	        
        	        if(!$termChildren[0]){
        	            array_push($characterictics_price,$term);
        	        }else{
        	            //Создаем массив с элементом(выбор варианта) и вариантами
        	            $termList = [$term,$termChildren];
        	            array_push($characterictics_list,$termList);
        	        }
        	        
        	    }
        	}
        	
        	//Функция отдает выбранные(чекбоксами) категории
        	function searchFieldsTerm($term){
        	    $filds_term = [];
        	    $filds = get_term_meta( $term->term_id, 'checkCategory',1);
        
                foreach($filds as $key => $value){
                       
                    array_push($filds_term,$key);
                }
                
                return $filds_term;
        	}
        	
        	//Перебираем характеристики с вводом значений
        	foreach( $characterictics_price as $term ){
        	    $filds_term = searchFieldsTerm($term);
        	    
        	     //Поиск совпадений в id родительских категорий товара и отмеченных категорий у текущей характеристики
        	     if( array_intersect( $product_parentterms,$filds_term ) ){
                    
                    if($characteristics_in_stock == false) { $characteristics_in_stock = true;} ?>
                    
                     <li class="advanced-field"> 
                         <div class="span-advanced-fields"><?php echo $term->name ?></div>
                         <input type="text" name="characteristics_text_field[<?php echo $term->term_id ?>]" placeholder="Значение" value="<?php echo $characteristicsTextField[$term->term_id]?>">
                     </li>
                    
                    <?php 
                    
                }
        	    
        	    
        	}
        	
        	//Перебираем характеристики с выбором
        	foreach( $characterictics_list as $term ){
        	    $filds_term = searchFieldsTerm($term[0]);
        	    
        	     //Если сама характеристика подходит для текущего товара
        	     if( array_intersect( $product_parentterms,$filds_term ) ){
                    
                    if($characteristics_in_stock == false) { $characteristics_in_stock = true;}  ?>
                    
                     <li class="advanced-field"> 
                         <div class="span-advanced-fields"><?php echo $term[0]->name ?></div>
                         
                         <?php foreach( $term[1] as $characterictics_id){ 
                            
                             $characterictics_term = get_term($characterictics_id, 'characteristics' );
                             
                             $filds_term = searchFieldsTerm($characterictics_term);
                             
                             //то проверям и её дочерние элементы(варианты выбора)
                             if( array_intersect( $product_parentterms,$filds_term ) ){
                    
                                if($characteristics_in_stock == false) { $characteristics_in_stock = true;} ?>
                                
                                
                                    <div class="checkbox-advanced-fields" data-parentid ="<?php echo $term[0]->term_id ?>">
                                        <?php //Выводим чекбоксы, проверяем не выбран ли чекбокс(по значениям из базы данных или ajax, массив ЧЕКБОКСЫ[ТМ]["Основит": "on", "Основит2": "on"]  ?>
                                        <input name="characteristics_checkbox_field[<?php echo $characterictics_term->term_id ?>]" type="checkbox" <?php if($characteristicsCheckboxField[$term[0]->term_id][$characterictics_term->term_id]) { echo "checked"; } ?> /> 
                                        <?php echo $characterictics_term->name ?>
                                    </div>
                                
                                
                                <?php 
                                
                            }
                         
                         ?>
                         
                         
                         <?php } ?>
                         
                     </li>
                    
                    <?php 
                    
                }
        	    
        	    
        	}
        	
        	
        	echo "</ul>";
        	
        	if($characteristics_in_stock == false) { 
        	    
        	    echo "Нет подходящих характеристик</br>Выберите категорию в каталоге";
        	    
        	}
        	
        }
        
}

function save_detailed_fields( $post_id ) {
    
     // проверяем, пришёл ли запрос со страницы с метабоксом
     if ( !isset( $_POST['detailed_fields'] )
     || !wp_verify_nonce( $_POST['detailed_fields'], basename( __FILE__ ) ) )
            return $post_id;
            
     // проверяем, является ли запрос автосохранением
     if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
     return $post_id;
     
     // проверяем права пользователя, может ли он редактировать записи
     if ( !current_user_can( 'edit_post', $post_id ) )
     return $post_id;
     
     // теперь также проверим тип записи 
     $post = get_post($post_id);
     
     
     if ($post->post_type == 'product') { 
         
         
         if($_POST['characteristics_checkbox_field']){
             
             $characteristicsCheckbox = [];
             
             foreach($_POST['characteristics_checkbox_field'] as $key => $value){
             
                 $characteristic = get_term($key, 'characteristics' );
                 
                 $characteristicsCheckbox[$characteristic->parent][$key] = $value;
                 
                 
             }
             
             update_post_meta($post_id,'_characteristics_checkbox_field',$characteristicsCheckbox);
         
         }else{
             
             delete_post_meta($post_id,'_characteristics_checkbox_field');
             
         }
         
         if( check_saleSteps() ){
             
             $wholesalePrice = $_POST['wholesalePrice'];
             $completeWholesalePrice = cheak_detailed_fields($wholesalePrice);
             
             as_update_meta($post_id, '_wholesalePrice', $completeWholesalePrice);
             
             /*
             if( $completeWholesalePrice ){
             
                 update_post_meta($post_id,'_wholesalePrice',$completeWholesalePrice);
                 
             }else{
                 
                 delete_post_meta($post_id,'_wholesalePrice');
                 
             }*/
             
         }
         
         $price = $_POST['_price'];
         as_update_meta($post_id, '_price', $price);
         
         $weight = $_POST['_weight'];
         as_update_meta($post_id, '_weight', $weight);
         
         $article = $_POST['_article'];
         as_update_meta($post_id, '_article', $article);
         
         $hit = $_POST['_hit'];
         as_update_meta($post_id, '_hit', $hit);
         
         $newProduct = $_POST['_newProduct'];
         as_update_meta($post_id, '_newProduct', $newProduct);
         
         
         $characteristicsTextField = $_POST['characteristics_text_field'];
         $completeCharacteristicsTextField = cheak_detailed_fields($characteristicsTextField);
         
         as_update_meta($post_id, '_characteristics_text_field', $completeCharacteristicsTextField);
         
         
     }
     
     return $post_id;
     
}
 
add_action('save_post','save_detailed_fields');


//Добавляем ajax функцию для обновления характеристик при выборе категорий в админке
add_action('admin_print_footer_scripts', 'updateCharacteristicsAjax', 99);
function updateCharacteristicsAjax() {
	?>
	<script>
	    
	    function updateCharacteristics(activeCategoryId,characteristicsTextField,characteristicsCheckboxField){
	       
	        //Отправляемые данные
	        //Событие update_characteristics
	        var data = {
    			action: 'update_characteristics',
    			activeCategoryId: activeCategoryId,
    			characteristicsTextField: characteristicsTextField,
    			characteristicsCheckboxField: characteristicsCheckboxField
    		};

    		
    		jQuery.post( ajaxurl, data, function(response) {
    		    
    		    jQuery("#characteristics_meta_box").children(".inside").html(response);
    		    
    		});
	       
	    }
	
	</script>
	<?php
}

//При срабатывание action update_characteristics вызываем функцию
add_action('wp_ajax_update_characteristics', 'updateCharacteristicsFunction');

function updateCharacteristicsFunction(){
    
    $activeCategoryId = $_POST['activeCategoryId'];
    
    //Получаем значения полей которые были заполнены(в случае их наличия)
    $characteristicsTextField = $_POST['characteristicsTextField'];
    $characteristicsCheckboxField = $_POST['characteristicsCheckboxField'];
    
    $url = wp_get_referer();
    $postIdUrl  = preg_match("/post=(\d+)/s", $url, $matches)? $matches[1] : 0;
    
    
    characteristics_metabox( get_post($postIdUrl), "", $activeCategoryId, $characteristicsTextField, $characteristicsCheckboxField );
    
    wp_die();
    
    
}