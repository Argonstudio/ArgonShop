<?php

function as_sort($settingSort, $catID, $pageNum){
    
    $typeSort = as_getCookie('AS_CatalogSorting');
    
    if(!is_array($typeSort) || !isset($typeSort['typeSort'], $typeSort['howSort'])){
        
        $typeSort = $settingSort["baseSort"];
        
    } ?>
    
    <span class="sortParameters" data-page="<?php echo absint( $pageNum ); ?>" data-cat-id="<?php echo absint( $catID ); ?>">
		
		<?php 
		    foreach($settingSort["typesSort"] as $key => $value){ 
		
		        $class = "as-sort as-sort-".$key;
		        
		        if( $typeSort['typeSort'] === $key ){
		            
		            switch($typeSort['howSort']){
		                
		                case "DESC":
		                    $class .= " sort-max";
		                    break;
		                case "ASC":
		                    $class .= " sort-min";
		                    break;      
		                
		            }
		            
		        }
		        
		?>
		    
		    <span class="<?php echo esc_attr( $class ); ?>" name="<?php echo esc_attr( $key ); ?>"><span><?php echo esc_html( $value["name"] ); ?></span></span>
		    
		<?php }	?>	
					            
	</span>
    
    <?php
    
}

function as_catalog($catalogSetting, $catID, $pageNum){
    
    $typeSort = as_getCookie('AS_CatalogSorting');
    
    if($typeSort){
					            
		$productsList = as_get_sort_products($typeSort['typeSort'], $typeSort['howSort'], $catID, $pageNum);
					            
	}else{
					            
		$productsList = as_get_sort_products($catalogSetting["baseSort"]["typeSort"], $catalogSetting["baseSort"]["howSort"], $catID, $pageNum);
					            
	}
					        
	as_view_products_list($productsList);
    
}