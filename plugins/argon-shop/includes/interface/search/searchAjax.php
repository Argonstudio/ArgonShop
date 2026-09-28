<?php

add_action('wp_footer', 'asSearch', 99); 
function asSearch() {
	?>
	
	<script>
	
	
	    function getAjaxSearchResult(inputForm, dataAjax, searchStr, blockResult){		    
		    
		    var data = {
    			action: 'getAjaxSearchResult',
    			dataAjax: dataAjax,
    			searchStr: searchStr
    		};
    		
		    $.ajax({
                url: myajax.url,
                data: data,
                type: 'POST',
                success: function(response) {                   
                  
                  var classBlockResult = inputForm.attr('data-blockresult');
                  
                  var blockResult = $("."+classBlockResult); 
				  
                  blockResult.html(response);                  
                  
                },
                error: function(data){
                    
                    console.log(data);
                    
                    var classBlockResult = inputForm.attr('data-blockresult');
                  
                    var blockResult = $("."+classBlockResult);
                      
                    blockResult.html("Ошибка на сервере");
                    
                }
              });
		    
    		
		}
		

	</script>
	<?php
}

add_action('wp_ajax_getAjaxSearchResult', 'getAjaxSearchResult_callback');
add_action('wp_ajax_nopriv_getAjaxSearchResult', 'getAjaxSearchResult_callback');



function getAjaxSearchResult_callback(){
    
    $searchStr = sanitize_text_field( $_POST["searchStr"] );
    
    $searchParameters = stripslashes(strip_tags($_POST["dataAjax"]));
    $searchParameters = json_decode( $searchParameters );    
    
    if( gettype($searchParameters) === "object" ){
        
        $searchResult = queryManagerSearchResult($searchStr, $searchParameters);
        
        if( empty($searchResult) && !ctype_digit($searchStr) ){
            
            $searchStrFix = fixKeyboardlayout($searchStr);
             
             if($searchStrFix != $searchStr){
                 
                 $searchResult = queryManagerSearchResult($searchStrFix, $searchParameters);
                 
             }
             
        }        
        
         if( !empty($searchResult) ){
             
             showResult($searchResult);
        
         }else{
             
             echo "Нет подходящих результатов";
             
         }
        
    }
    
    wp_die();
}


/*
 ** Получение подходящих категорий и товаров из базы данных
*/

//Управляющая функция
function queryManagerSearchResult($searchStr, $searchParameters){
    
    $searchResult = Array();
        
    $searchParameters = cleanParameters($searchParameters);
    
     if(!$searchParameters->productResult || $searchParameters->productResult === true){
            
            $searchResultProduct = queryAllocatorSearchResult("product", 10, $searchStr);
            
            if($searchResultProduct){
                
                $searchResult["product"] = Array(
                    
                    "nameBlock" => "Подходящие товары",
                    "listResult" => prepProduct($searchResultProduct)
                    
                    );
                
            }
            
        }
        
        
        if(!$searchParameters->catalogResult || $searchParameters->catalogResult === true){
            
            $searchResultCatalog = queryAllocatorSearchResult("category", 10, $searchStr);
            
            if($searchResultCatalog){
                
                $searchResult["catalog"] = Array(
                    
                    "nameBlock" => "Категории",
                    "listResult" => prepCatalog($searchResultCatalog)
                    
                    );                
            }
            
        }
        
        if( !empty($searchParameters->postResult) ){
            
            foreach($searchParameters->postResult as $key => $value){
                
                $searchResultPost = queryAllocatorSearchResult("post", 10, $searchStr, $value);
                
                if( $searchResultPost ){
                    
                    $postListResult = Array(
                        
                        "nameBlock" => $value->name,
                        "listResult" => prepPost($searchResultPost)
                        
                        );
                        
                    if($value->class){
                        
                        $postListResult["classBlock"] = $value->class;
                        
                    }    
                        
                    $searchResult["post"][] = $postListResult; 
                    
                }
                
            }          
            
        }
    
    return $searchResult;     
    
}

function queryAllocatorSearchResult($type, $quantity, $searchStr, $parameters){
    
    $searchResult;
    
    switch($type){
        
        case "product":
            $searchResult = queryProductSearchResult($searchStr, $quantity);
            break;
            
        case "post":
            $searchResult = queryPostSearchResult($searchStr, $quantity, $parameters);
            break;
            
        case "category":
            $searchResult = queryTaxonomySearchResult($searchStr, $quantity);
            break;    
    }
    
    return $searchResult;
    
    
}


/*
 ** Запросы в базе данных
*/

function queryTaxonomySearchResult($searchStr, $quantity){
    
    $argumentSearch = array(
		'taxonomy'       => 'catalog',
		'number'         => $quantity,
		'hierarchical'   => true,
		'fields'         => 'id=>name',
		'search'         => $searchStr,
		
	);
	
	//Отправка основного запроса
    $querySearchTerms = get_terms( $argumentSearch );
    
    //return $argumentSearch;
    return $querySearchTerms;
    
}

function queryPostSearchResult($searchStr, $quantity, $parameters){
    
    $argumentSearch = array(
		'post_type'      => 'post',
		'posts_per_page' => $quantity,
		'fields'         => 'ids',
		's'              => $searchStr,
		
		'tax_query'      => array(
    			array(
    				'taxonomy' => 'category',
    				'field'    => 'id',
    				'terms'    => $parameters->category
    			),
	    )
	);
	
	//Отправка основного запроса
    $querySearchStr = new WP_Query( $argumentSearch );
    $querySearchStr = $querySearchStr->posts;
    
    return $querySearchStr;
    
}

function queryProductSearchResult($searchStr, $quantity){
    
    $searchResultArticle = Array();
    $searchResultStr = Array();
    
    $quantityResults = $quantity;
    
    $argumentSearch = array(
		'post_type' => 'product',
		'posts_per_page' => -1,
	);	
        
    
    if( ctype_digit($searchStr) ){
        
        $argumentSearch['meta_query'] = array(
			array(
				'key'	 	=> '_article',
				'value'	  	=> $searchStr
			),
	    );
        
        $queryArticle = new WP_Query( $argumentSearch );
        
        if( $queryArticle->posts ){
            $searchResultArticle = $queryArticle->posts;
        }
        
        unset($argumentSearch['meta_query']);
        
    }
    
    $argumentSearch['s'] = $searchStr;
    
    //Отправка основного запроса
    $querySearchStr = new WP_Query( $argumentSearch );
    $searchResultStr = $querySearchStr->posts;
    
    
    
    $postSearchResult = Array();
    
    //Выход если результаты по запросу отсуствуют
    if( empty($searchResultStr) ){
        
        $postSearchResult = $searchResultArticle;
        
        return $postSearchResult;
        
    }else{
        
        $exactMatchTitle = Array();
        $otherResult = Array();
        
        foreach($searchResultStr as $keyResultStr => $valueResultStr){
            
            //Убираем повторяющиеся значения с поиском по _article
            if( !empty($searchResultArticle) ){
                
                foreach($searchResultArticle as $keyResultArticle => $valueResultArticle){
                    
                    if($valueResultArticle->ID === $valueResultStr->ID){
                    
                        unset( $searchResultStr[$keyResultStr] );
                        continue(2);
                    }
                    
                }
                
            }
            
            //Делим на точные совпадения в заголовке и прочие
            if( stripos($valueResultStr->post_title, $searchStr) !== false){
                $exactMatchTitle[$keyResultStr] = $valueResultStr;
            }else{
                $otherResult[$keyResultStr] = $valueResultStr;
            }; 
        
        }
        
    
        $forMergeResult = Array($searchResultArticle, $exactMatchTitle, $otherResult);
        $postSearchResult = merge_search_result($forMergeResult, $quantityResults); 
        
    }
    
    
    return $postSearchResult;
    
}  

/*
 ** Создаем массивы с информацией для вывода(url/title)
*/

function prepCatalog($searchResultCatalog){
    
    $prepCatalog;
    
    foreach($searchResultCatalog as $key => $value){
        
        $prepCatalog[$key] = Array(
            
                'post_title' => $value,
                'post_url'   => get_category_link($key)
            
            );
       
    }
    
    return $prepCatalog;
    
    
    
}

function prepPost($searchResultPost){
    
    $prepPost;
    
    foreach($searchResultPost as $key => $value){
        
        $prepPost[$value] = Array(
            
                'post_title' => get_the_title($value),
                'post_url'   => get_permalink($value)
            
            );
            
    }
    
    return $prepPost;
    
}

function prepProduct($searchResultProduct){
    
    $prepProduct;
    
    foreach($searchResultProduct as $key => $value){
        
        $prepProduct[$value->ID] = Array(
            
                'post_title' => $value->post_title,
                'post_url'   => get_permalink($value->ID)
            
            );
        
        $categoryProduct = get_the_terms($value->ID, 'catalog');
        
        if(!empty($categoryProduct)){
            
            $prepProduct[$value->ID]["category_name"] = $categoryProduct[0]->name;
            $prepProduct[$value->ID]["category_url"] = get_category_link($categoryProduct[0]->term_id);
            
        }       
        
        
    }
    
    return $prepProduct;
    
}


/*
 ** Создаем html вид результата
*/


function showResult($searchResult){
    
    foreach($searchResult as $keyEntirelyResult => $valueEntirelyResult){
                 
        if( $keyEntirelyResult === "post" ){
                     
            foreach($valueEntirelyResult as $keyOneTypeResults => $valueOneTypeResults){
                         
                generateResultHTML($keyEntirelyResult, $valueOneTypeResults);
                         
            }
                     
        }else{
                     
            generateResultHTML($keyEntirelyResult, $valueEntirelyResult);
                     
        }
                 
                 
    }
    
}

function generateResultHTML($type, $valueEntirelyResult){
   
   $classBlockOneTypeResult = "blockOneTypeResult";
   
   if( $valueEntirelyResult["classBlock"] ){
                            
            $classBlockOneTypeResult .= " ".$valueEntirelyResult["classBlock"];
                            
        }
   
    ?>
             
    <div class="<?=$classBlockOneTypeResult ?>">
                     
        <div class="titleOneTypeResult">
            <?=$valueEntirelyResult["nameBlock"] ?>
        </div>
    <?php
                
        $classBlockProduct = "resultProduct";
                
        foreach($valueEntirelyResult["listResult"] as $idOneResult => $valueOneResult){
                        
        ?>
                        
            <div class="<?=$classBlockProduct ?>">
                            
                            
            <?php if($valueOneResult["category_name"]){ ?>
                                
                    <span><a href="<?=$valueOneResult['category_url'] ?>"><?=$valueOneResult["category_name"] ?></a></span> → 
                                
            <?php } ?>
                            
                            
                    <a href="<?=$valueOneResult['post_url'] ?>"><?=$valueOneResult["post_title"] ?></a>
                            
            </div>
                        
            <?php
        }
                 
        ?>
        
    </div>
    <?php
    
}


/*
  ** Вспомогательные функции
*/

//Объединяет массивы с полученными результатами 
//Возвращается массив с $quantity числом элементов, 
//приоритет в заполнении итогового массива имеют значений из массива вложенного в $typesResultArray первым(итд)  
function merge_search_result($typesResultArray, $quantity){
        
    $quantityResults = (int)$quantity;
    $searchStrResult = Array();
     
    foreach($typesResultArray as $key => $value){
        
        if( isset($value) ){
                
            $searchStrResult = array_partial_merge($quantityResults, $searchStrResult, $value);
            $quantityResults -= count($value);
            
        }
            
            
    }
        
    return $searchStrResult;
        
}

//Проверяем полученный с сайта массив 
//stdClass Object ( [productResult] => 1 [catalogResult] => 1 [postResult] => Array ( [0] => stdClass Object ( [name] => Акции и статьи [class] => searchResultBlockProduct [category] => Array ( [0] => 36 [1] => 37 ) ) ) ) 
function cleanParameters($searchParameters){
    
    $cleanSearchParameters = (object)[];
    
    if($searchParameters->productResult && gettype($searchParameters->productResult) === "boolean"){
        
        $cleanSearchParameters->productResult = $searchParameters->productResult;
        
    }
    
    if($searchParameters->catalogResult && gettype($searchParameters->catalogResult) === "boolean"){
        
        $cleanSearchParameters->catalogResult = $searchParameters->catalogResult;
        
    }
    
    if($searchParameters->postResult && gettype($searchParameters->postResult) === "array"){
        
        foreach($searchParameters->postResult as $key => $value){
            
            $cleanValue = (object)[];
            
            if(gettype($value) === "object"){
                
                if( $value->name && gettype($value->name) === "string" ){
                
                    $cleanValue->name = sanitize_text_field( $value->name );
                
                }
                
                if( $value->class && gettype($value->class) === "string" ){
                    
                    $cleanValue->class = sanitize_text_field( $value->class );
                    
                }
                
                if( $value->category && gettype($value->category) === "array" ){
                    
                    $cleanCategory = [];
                    
                    foreach($value->category as $idCategory){
                        
                        if( gettype($idCategory) === "integer" ){
                            
                            $cleanCategory[] = $idCategory;
                            
                        }
                        
                    }
                    
                    if( !empty($cleanCategory) ){
                        
                        $cleanValue->category = $cleanCategory;
                        
                    }
                    
                    
                    
                }
                
            }
            
            $cleanSearchParameters->postResult[] = $cleanValue;
            
        }
    }
    
    return $cleanSearchParameters;
    
}

function fixKeyboardlayout($searchStr){
    
    $LangEn = array("&","q","w","e","r","t","y","u","i","o","p","[","]",
                   "a","s","d","f","g","h","j","k","l",";","'",
                   "z","x","c","v","b","n","m",",",".","/",
                   "Q","W","E","R","T","Y","U","I","O","P","[","]",
                   "A","S","D","F","G","H","J","K","L",";","'",
                   "Z","X","C","V","B","N","M",",","/");
    $LangRu = array("?","й","ц","у","к","е","н","г","ш","щ","з","х","ъ",
                       "ф","ы","в","а","п","р","о","л","д","ж","э",
                       "я","ч","с","м","и","т","ь","б","ю",".",
                       "Й","Ц","У","К","Е","Н","Г","Ш","Щ","З","Х","Ъ",
                       "Ф","Ы","В","А","П","Р","О","Л","Д","Ж","Э",
                       "Я","Ч","С","М","И","Т","Ь","Б","Ю",".");
    
    $searchStr = str_replace($LangEn,$LangRu, $searchStr);
    
    return $searchStr;
    
}
