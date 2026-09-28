<?php

add_action('wp_footer', 'asCatalogSort', 99); 
function asCatalogSort() {
	?>
	
	<script>
	
	
	    function sortingCatalog(sorting, typeSort, howSorting, catId, pageNum){
		    
		    var data = {
    			action: 'sortingCatalog',
    			typeSort: typeSort,
    			howSorting: howSorting,
    			catId: catId,
    			pageNum: pageNum
    		};
    		
		    $.ajax({
                url: myajax.url,
                data: data,
                type: 'POST',
                success: function(response) {
                  
                  successSortingCatalog(response, sorting, howSorting);
                  
                },
                error: function(response){
                    
                    errorSortingCatalog(response);
                    
                }
              });
		    
    		
		}
		

	</script>
	<?php
}

add_action('wp_ajax_sortingCatalog', 'sortingCatalog_callback');
add_action('wp_ajax_nopriv_sortingCatalog', 'sortingCatalog_callback');

function sortingCatalog_callback(){
    
    $typeSort = sanitize_text_field( $_POST["typeSort"] );
    $howSorting = $_POST["howSorting"];
    $catId = $_POST["catId"];
    $pageNum = $_POST["pageNum"];
 
    if( !is_numeric($catId) || !is_numeric($pageNum) ){
        wp_die();
    }
    
    $cookieCatalogSort['typeSort'] = $typeSort;
    $cookieCatalogSort['howSort'] = $howSorting;
    
    $cookieCatalogSort = wp_json_encode($cookieCatalogSort);
    
    setcookie( "AS_CatalogSorting", $cookieCatalogSort, time()+1209600, COOKIEPATH, COOKIE_DOMAIN);
    
    // api/product/productList/catalog.php
    $productsList = get_sort_products($typeSort, $howSorting, $catId, $pageNum);
    
    view_products_list($productsList);
    
    wp_die();
}

