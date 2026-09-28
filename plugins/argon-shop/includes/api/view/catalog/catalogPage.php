<?php

function as_sort($settingSort, $catID, $pageNum){
    
    $typeSort = getCookie('AS_CatalogSorting');
    
    if(!$typeSort){
        
        $typeSort = $settingSort["baseSort"];
        
    } ?>
    
    <span class="sortParameters" data-page="<?=$pageNum ?>" data-cat-id="<?=$catID ?>">
		
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
		    
		    <span class="<?=$class ?>" name="<?=$key ?>"><span><?=$value["name"] ?></span></span>
		    
		<?php }	?>	
					            
	</span>
    
    <?php
    
}

function as_catalog($catalogSetting, $catID, $pageNum){
    
    $typeSort = getCookie('AS_CatalogSorting');
    
    if($typeSort){
					            
		$productsList = get_sort_products($typeSort['typeSort'], $typeSort['howSort'], $catID, $pageNum);
					            
	}else{
					            
		$productsList = get_sort_products($catalogSetting["baseSort"]["typeSort"], $catalogSetting["baseSort"]["howSort"], $catID, $pageNum);
					            
	}
					        
	view_products_list($productsList);
    
}