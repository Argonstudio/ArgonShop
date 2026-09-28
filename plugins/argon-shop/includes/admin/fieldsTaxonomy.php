<?php

// Поля при добавлении элемента таксономии
add_action("characteristics_add_form_fields", 'add_new_custom_fields');
// Поля при редактировании элемента таксономии
add_action("characteristics_edit_form_fields", 'edit_new_custom_fields');

// Сохранение при добавлении элемента таксономии
add_action("create_characteristics", 'save_custom_taxonomy_meta');
// Сохранение при редактировании элемента таксономии
add_action("edited_characteristics", 'save_custom_taxonomy_meta');

function edit_new_custom_fields( $term ) {
	?>
	
	    <tr class="form-field">
			<th scope="row" valign="top"><label>Относится к категориям:</label></th>
			<td>
			    
			    <?php 
			    
			        //Получаем выбранные категории из базы данных
			        $checkCategory = get_term_meta( $term->term_id, 'checkCategory',1);
			        
			        //Получаем термины каталога
			        function get_terms_catalog($parent){
			            if(!$parent) {
			                $parent = 0;
			            }
			            
			           $args = array(
                    	'taxonomy' => 'catalog',
                    	'hide_empty' => false,
                    	'parent' => $parent
                        );
                        
                        return( get_terms( $args ) );
			        }
			    
    			    $terms = get_terms_catalog();
               
                    
                    //Показываем термины на странице
                    function view_terms($terms,$level,$checkCategory){ 
                        
                       
                        
                    ?>    
                    <ul class="choiceCategory<?php if($level != 0){ echo 'Child'; } ?>">
                        
                        
                        
                    <?php  
                        
                        if($level == 0){
                            ?><li class="checkAllCategory"> <input name="checkCategory[all]" type="checkbox" <?php if($checkCategory["all"]){ echo "checked"; } ?> /> <b>Выбрать все / Снять выбор</b> </li><?php
                        }
                    
                        
                        foreach($terms as $term){ 
                            
                    ?>
                
                
                        <li class="checkCategory"> <input name="checkCategory[<?php echo $term->term_id; ?>]" type="checkbox" <?php if($checkCategory[$term->term_id]) { echo "checked"; } ?> /> <?php echo $term->name; ?>
                        
                            <?php 
                                
                                //Получаем дочерние термины для вывода 2,3 итд уровней
                                $childs_term = get_terms_catalog($term->term_id); 
                            
                                if($childs_term){
                                    
                                    ?> <input type="button" class="showHideButton" value=""> <?php
                                    //$level показывает текущий уровень вложенности термина
                                    view_terms($childs_term,++$level,$checkCategory);  
                                    $level = --$level;
                                    
                                }    
                            
                            ?>
                        
                        </li>
                
                
                <?php 
                        }
                ?>    
                    
                    </ul>
                        
                        
                    
                    <?php 
                        
                    }
                    
                    view_terms($terms,0,$checkCategory);
                    
                
			    
			    ?>
			        
			</td>
		</tr>
		
	<?php
}

function add_new_custom_fields( $taxonomy_slug ){
	?>
	
	
	<div class="form-field">
	    <label>Относится к категориям:</label>
			    
			    <?php 
			    
			        function get_terms_catalog($parent){
			            if(!$parent) {
			                $parent = 0;
			            }
			            
			           $args = array(
                    	'taxonomy' => 'catalog',
                    	'hide_empty' => false,
                    	'parent' => $parent
                        );
                        
                        return( get_terms( $args ) );
			        }
			    
    			    $terms = get_terms_catalog();
               
                
                    function view_terms($terms,$level){ 
                        
                        $dash = "";
                        
                        for($i=0; $i < $level; $i++ ){
                            $dash = $dash." — ";
                        }
                        
                        
                    ?>    
                    <ul class="choiceCategory<?php if($level != 0){ echo 'Child'; } ?>">
                        
                        
                        
                    <?php  
                        
                        if($level == 0){
                            ?><li class="checkAllCategory"> <input name="checkCategory[all]" type="checkbox" id="<?php echo 'all' ?>" checked/> <b>Выбрать все / Снять выбор</b> </li><?php
                        }
                    
                        
                        foreach($terms as $term){ 
                            
                    ?>
                
                
                        <li class="checkCategory"> 
                            <input name="checkCategory[<?php echo $term->term_id; ?>]" type="checkbox" id="<?php echo $term->term_id; ?>" checked/> 
                            <?php echo $term->name; ?>
                        
                            <?php 
                            
                                $childs_term = get_terms_catalog($term->term_id); 
                            
                                if($childs_term){
                                    ?> <input type="button" class="showHideButton" value=""> <?php
                                    view_terms($childs_term,++$level);  
                                    $level = --$level;
                                    
                                }    
                            
                            ?>
                        
                        </li>
                
                
                <?php 
                        }
                ?>    
                    
                    </ul>
                        
                        
                    
                    <?php 
                        
                    }
                    
                    view_terms($terms,0);
                    
                
			    
			    ?>
			        
		</div>
	
	<?php
}

function save_custom_taxonomy_meta( $term_id ) {
	
	if ( ! current_user_can('edit_term', $term_id) ) return;
	if (
		! wp_verify_nonce( $_POST['_wpnonce'], "update-tag_$term_id" ) && // wp_nonce_field( 'update-tag_' . $tag_ID );
		! wp_verify_nonce( $_POST['_wpnonce_add-tag'], "add-tag" ) // wp_nonce_field('add-tag', '_wpnonce_add-tag');
	) return;

    
    if ( ! isset($_POST['checkCategory']) ) update_term_meta( $term_id, "checkCategory", 0 );
    
    //Получаем чекбоксы
    $checkCategory = wp_unslash($_POST['checkCategory']);
    
    foreach( $checkCategory as $key => $val ){
        if(!is_int($key) && $key !== "all" ){
            echo "Attempt to write with an unknown key: ".$key."<p></p>";
            echo "Contact the developer of the website voitkov.ne@yandex.ru";
        }
    }
    
    update_term_meta( $term_id, "checkCategory", $checkCategory );
    

	return $term_id;
}