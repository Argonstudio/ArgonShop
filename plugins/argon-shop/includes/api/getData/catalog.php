<?php

//Получаем термины каталога(для меню), иерархическое представление
function get_catalog_terms(){
    
                $catalogArgs = array(
                    	'hierarchical'  => 1,
                    	'taxonomy'      => 'catalog',
                    	'hide_empty'    => 0
                    );
                
                
                $catalogTerms = get_categories($catalogArgs);
                
                $menuCatalog = Array();
                
                foreach($catalogTerms as $key => $value){
                    
                    
                    if( get_option("catalog_".$value->term_id."_catalog_type") === "request" ){
                        
                        //Убираем элементы запросы
                        unset($catalogTerms[$key]);
                        continue;
                    }
                    
                    //Создаем массив id => Array("data")
                    $menuCatalog[$value->term_id] = Array(
                            
                                "data"     => Array(
                                    
                                    "name"              => $value->name,
                                    "slug"              => $value->slug,
                                    "term_id"           => $value->term_id,
                                    "parent"            => $value->parent,
                                    "firstLevelChild"   => 0,
                                    "secondLevelChild"  => 0
                                    
                                    
                                    )
                            
                            );
                    
                    //echo "<p></p>создал: ";
                    //print_r($menuCatalog[$value->term_id]);
                    //echo "<p></p>";
                }
                
                foreach($menuCatalog as $key => $value){
                    
                    //Добавляем элементы имеющие родителей к ним
                    if( $value["data"]["parent"] !== 0 ){
                        
                        $parentID = $value["data"]["parent"];
                        
                        //[id родителя]
                       $menuCatalog[$parentID]["children"][$key] = $value;
                       $menuCatalog[$parentID]["data"]["firstLevelChild"] += 1; 
                        
                        //echo $key."<p></p>";
                        //print_r($menuCatalog[$value["data"]->parent]);
                        //echo "<p></p>";
                    }
                    
                }
                
                foreach($menuCatalog as $key => &$value){
                    
                    //Если у элемента есть дочерние
                    if($value["children"]){
                        
                        //echo "<p></p>нашел дочерние элементы у: ".$value["data"]["name"]."<p></p>";
                        //print_r($menuCatalog[$key]);
                        //echo "<p></p>";
                        $settingLevelsMenu($menuCatalog[$key]);
                        
                    }
                    
                    
                    $settingLevelsMenu = function(&$element) use (&$settingLevelsMenu, &$menuCatalog) {
                        
                        //echo "<p></p>начали проход элемента: ".$element["data"]["name"]."<p></p>";
                        //print_r($element["data"]);
                        //echo "<p></p>";
                    
                        //Перебираем дочерние элементы
                        foreach($element["children"] as $keyChild => $valueChild){
                                
                                if( isset($menuCatalog[$keyChild]) ){
                                        
                                    //Копируем дочерний элемент(вместе с дочерними к нему)
                                    $element["children"][$keyChild] = $menuCatalog[$keyChild];
                                    $element["data"]["secondLevelChild"] += $menuCatalog[$keyChild]["data"]["firstLevelChild"];
                                    
                                    //echo "удаляю: ".$keyChild."</br>";
                                    //print_r($menuCatalog[$keyChild]);
                                    //echo $menuCatalog[$keyChild]["data"]["name"]."<p></p>";
                                    
                                    unset($menuCatalog[$keyChild]);
                                    
                                
                                
                                    //Если у добавленного элемента в свою очередь есть дочерние элементы - повторяем 
                                    if($element["children"][$keyChild]["children"]){
                                        
                                        //echo "вижу дочерние элементы у: ".$menuCatalog[$keyChild]["data"]->name."<p></p>";
                                        $settingLevelsMenu($element["children"][$keyChild]);
                                        
                                    }
                                
                                }
                                
                            }
                        
                        //echo "<p></p>завершили проход элемента: ".$element["data"]["name"]."<p></p>";
                        //print_r($element);
                        //view_menu_elements($menuCatalog);
                        
                    };
                    
                    unset($value);
                    
                }
                //view_menu_elements($menuCatalog);
                return $menuCatalog;
    
    
}



function get_catalog_termsWP(){
    
                $catalogArgs = array(
                    	'hierarchical'  => 1,
                    	'taxonomy'      => 'catalog',
                    	'hide_empty'    => 0
                    );
                
                
                $catalogTerms = get_categories($catalogArgs);
                
                $menuCatalog = Array();
                
                foreach($catalogTerms as $key => $value){
                    
                    
                    if( get_option("catalog_".$value->term_id."_catalog_type") === "request" ){
                        
                        //Убираем элементы запросы
                        unset($catalogTerms[$key]);
                        continue;
                    }
                    
                    //Создаем массив id => Array("data")
                    $menuCatalog[$value->term_id] = Array(
                            
                                "data"     => $value
                            
                            );
                    
                }
                
                foreach($menuCatalog as $key => $value){
                    
                    //Добавляем элементы имеющие родителей к ним
                    if( $value["data"]->parent !== 0 ){
                        
                        //[id родителя]
                       $menuCatalog[$value["data"]->parent]["children"][$key] = $value;
                        
                    }
                    
                }
                
                
                foreach($menuCatalog as $key => &$value){
                    
                    //Если у элемента есть дочерние
                    if($value["children"]){
                        
                        //echo "<p></p>нашел дочерние элементы у: ".$value["data"]->name."<p></p>";
                        
                        $settingLevelsMenu($menuCatalog[$key]);
                        
                    }
                    
                    
                    $settingLevelsMenu = function(&$element) use (&$settingLevelsMenu, &$menuCatalog) {
                    
                        //Перебираем дочерние элементы
                        foreach($element["children"] as $keyChild => $valueChild){
                                
                                if( isset($menuCatalog[$keyChild]) ){
                                
                                    //Копируем дочерний элемент(вместе с дочерними к нему)
                                    $element["children"][$keyChild] = $menuCatalog[$keyChild];
                                    
                                    //echo "удаляю: ".$keyChild."</br>";
                                    //echo $menuCatalog[$keyChild]["data"]->name."<p></p>";
                                    unset($menuCatalog[$keyChild]);
                                    
                                    //Если у добавленного элемента в свою очередь есть дочерние элементы - повторяем 
                                    if($element["children"][$keyChild]["children"]){
                                        
                                        //echo "вижу дочерние элементы у: ".$menuCatalog[$keyChild]["data"]->name."<p></p>";
                                        $settingLevelsMenu($element["children"][$keyChild]);
                                        
                                    }
                                
                                }
                                
                            }
                        
                        //echo "<p></p>завершили проход элемента: ".$element["data"]->name."<p></p>";
                        
                    };
                    
                    unset($value);
                    
                }
                
                return $menuCatalog;
    
}

//Разбивает переданный массив меню на несколько частей (массив, на сколько частей, сумма переданных элементов)
/**
 * Принимает: 
 * элементы меню(полученные из функции выше)
 * на сколько столбцов разбивать
 * первый уровень меню(заголовки) число
 * второй уровень(пункты) число
 * на сколько больше высота заголовков в верстке *n
 * корректировка(прибавляем к полученному среднему размеру столбца + столбец*корректировка)
 * количество элементов дополнительного пункта(если нужно добавить к последнему списку )
 */ 
function splitMenu($menuElements, $countResult, $catalogFirstLevelChild, $catalogSecondLevelChild, $heightFirstRelativeSecond, $adjustment, $additionalElementLength){
    
    if(!$heightFirstRelativeSecond){
        $heightFirstRelativeSecond = 1;
    }
    
    $sumElements = $catalogFirstLevelChild*$heightFirstRelativeSecond + $catalogSecondLevelChild;
    
    if($additionalElementLength){
        $sumElements += $additionalElementLength;
    }
    
    if(!$adjustment){
        $adjustment = 5;
    }
    
    $childsOneResult = round( $sumElements/$countResult );
    
    //echo $childsOneResult;
    
    $result = Array();
    $resultCountsList = Array();
    
    for($i = 0; $i < $countResult; $i++){
        $result[$i] = Array();
        $resultCountsList[$i] = 0;
    }
    
    foreach($result as $key => $value){
        
        $keyResult = 0;
        $numberElement = 0;
        
        foreach($menuElements as $menuElementKey => $menuElementValue){
            
            //Добавляем первый элемент
            //Добавляем элемент в список если (желаемое значение одного списка + корректировка) больше чем уже добавленное значение + заголовок списка + значение добавляемого элемента
            $add = ( $numberElement === 0 || round( $childsOneResult+$childsOneResult/$adjustment) >= $keyResult + 1*$heightFirstRelativeSecond + $menuElements[$menuElementKey]["data"]["firstLevelChild"] )?true:false;
            
            if($add){
                
                $result[$key][$menuElementKey] = $menuElements[$menuElementKey];
                $resultCountsList[$key] += 1*$heightFirstRelativeSecond + $menuElements[$menuElementKey]["data"]["firstLevelChild"];
                
                $keyResult += 1*$heightFirstRelativeSecond + $menuElements[$menuElementKey]["data"]["firstLevelChild"];
                $numberElement++;
                
                unset($menuElements[$menuElementKey]);
                
            }
            
        }
        
    }
    
    if( !empty($menuElements) ){
        
        $numberResult = 0;
        
        foreach($menuElements as $menuElementKey => $menuElementValue){
            
            $result[$numberResult][$menuElementKey] = $menuElements[$menuElementKey];
            
            if($numberResult < $countResult) $numberResult++;
            else $numberResult = 0;
            
        }
        
    }
    
    //print_r($resultCount);
    
    //В случае добавления элемента в конец последнего списка(функция получает его длину)
    if($additionalElementLength ){
        
        //Получаем индексы максимальных списков и минимального
        $mostList = array_keys( $resultCountsList, max($resultCountsList) );
        $leactList = array_keys( $resultCountsList, min($resultCountsList) )[0];
        
        //Если последний список попал в число списков с максимальным значением
        if( array_search( $countResult-1, $mostList ) ){
            
            $minValue;
            $minValueID;
            $numberIteration = 0;
            
            //Ищем самый мелкий(длина) его элемент
            foreach( $result[$countResult-1] as $key => $value ){
                
                if($numberIteration === 0){
                    $minValue = $value["data"]["firstLevelChild"];
                    $minValueID = $key;
                    continue;
                }
                
                if($minValue > $value["data"]["firstLevelChild"]){
                    $minValue = $value["data"]["firstLevelChild"];
                    $minValueID = $key;
                }
                
                $numberIteration++;
                
            }
            
            //Если при переносе найденного выше элемента в самый короткий список он не станет значительно больше последнего списка(минус минимальный элемент + длина добавляемого элемента)
            if( ($resultCountsList[$leactList] + $minValue) < ($resultCountsList[$countResult-1] - $minValue + $additionalElementLength)*1.5 ){
                $result[$leactList][$minValueID] = $result[$countResult-1][$minValueID];
                
                $resultCountsList[$leactList] += 1*$heightFirstRelativeSecond + $result[$countResult-1][$minValueID]["data"]["firstLevelChild"];
                $resultCountsList[$countResult-1] -= 1*$heightFirstRelativeSecond + $result[$countResult-1][$minValueID]["data"]["firstLevelChild"];
                
                unset($result[$countResult-1][$minValueID]);
            }
            
        }
        
        
    }
    
    $result = Array($result, $resultCountsList);
    
    //view_menu_elements($menuElements);
    return $result;
    
}




//Рекурсивно выводим вложенные пункты меню
function view_menu_elements($menuElements, $classUL, $howManylevels){
                
        echo ($classUL)?"<ul class='$classUL'>":"<ul>";
                    
        foreach($menuElements as $key => $value){
            
            
            if($value["data"]["class"]){
                $class = $value["data"]["class"];
            }
            
            echo $levels;        
            echo ($value["children"])?'<li class="itemHasChildren '.$class.'" style="list-style-type:none">':'<li class="'.$class.'" style="list-style-type:none">';
            
            if($value["data"]["term_id"]){
                
                $url = get_category_link($value["data"]["term_id"]);
                
            }else if($value["data"]["post_id"]){
                
                $url = get_permalink($value["data"]["post_id"]);
                
            }
            
            
            
            ?>
            
            <a href="<?=$url ?>"><span><?=$value["data"]["name"] ?></span></a>
            
            <?php
                            
            if( $value["children"] && $howManylevels > 1){
                
                view_menu_elements($value["children"], "", $howManylevels-1);
                                
            }
            
                            
            echo "</li>";
        }
                    
        echo "</ul>";
        
};