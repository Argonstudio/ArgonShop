<?php

function as_search($searchParameters){    
    
    $classForm = "asSearchForm";
    $classInputForm = "asInputSearchForm";
    $classButtonForm = "asSubmitSearchForm";
    
    if($searchParameters["classForm"]){
        $classForm .= " ".$searchParameters["classForm"];
    }
    
    if($searchParameters["classInput"]){
        $classInputForm .= " ".$searchParameters["classInput"];
    }
    
    if($searchParameters["classSubmit"]){
        $classButtonForm .= " ".$searchParameters["classSubmit"];
    }
    
    $valueSubmit;
    
    if( isset($searchParameters["valueSubmit"]) ){
        $valueSubmit = $searchParameters["valueSubmit"];
    }else{
        $valueSubmit = "Поиск";
    }
    
    $dataAjax = Array();
    $ajaxPositionResult = "bottom";

    if( isset($searchParameters["ajax"]) ){
        
        if( $searchParameters["ajax"]["productResult"] ) $dataAjax["productResult"] = $searchParameters["ajax"]["productResult"]; 
        if( $searchParameters["ajax"]["catalogResult"] ) $dataAjax["catalogResult"] = $searchParameters["ajax"]["catalogResult"]; 
        if( $searchParameters["ajax"]["postResult"] )    $dataAjax["postResult"]    = $searchParameters["ajax"]["postResult"]; 
        
        //Замена кавычки для корректного вывода в html
        $dataAjax = str_replace("'",'"', json_encode($dataAjax) );
        
        if( $searchParameters["ajax"]["positionResult"] ) $ajaxPositionResult = $searchParameters["ajax"]["positionResult"];
        
    }
    
    if( isset($searchParameters["ajax"]) && $ajaxPositionResult == "top"){
        
        as_ajaxBlock($searchParameters["ajax"]["classBlockResult"]);
        
    }
    
    $linkResultBlock = ( isset($searchParameters["ajax"]) ) ? "data-blockresult='".$searchParameters["ajax"]["classBlockResult"]."'" : "";
    
    ?>
       
        <form role="search" method="get" class="<?=$classForm ?>" action="<?php echo esc_url( home_url( '/' ) ); ?>">
            <div>
                <input type="text" <?=$linkResultBlock ?> <?php echo ($dataAjax) ? "data-ajax='".$dataAjax."'" : "" ?> class="<?=$classInputForm ?>" placeholder="Поиск" value="<?php echo get_search_query(); ?>" name="s" autocomplete="off" />
                
                
                <?php if( $searchParameters["post_type"] ){                        
                        
                        foreach($searchParameters["post_type"] as $key){
                            
                            $postType = 'post_type[]';
                            
                            ?>
                            
                            <input type="hidden" value="<?=$key ?>" name="<?=$postType ?>" />
                            
                            
                            <?php
                        }
                        
                        foreach($searchParameters["taxonomy"] as $key => $value){
                            
                            if($value != "all"){
                                $value = serialize($value);
                            }
                            
                            $nameGet = 'as_taxonomy['.$key.']';
                            
                            ?>
                            
                            <input type="hidden" value="<?=$value ?>" name="<?=$nameGet ?>" />
                            
                            
                            <?php
                        }               
                
                }                
                
                ?>
                
                <input type="submit" class="<?=$classButtonForm ?>" value="<?=$valueSubmit ?>" />
            </div>
         </form>
       
    <?php
    
    if( isset($searchParameters["ajax"]) && $ajaxPositionResult == "bottom"){
        
        as_ajaxBlock($searchParameters["ajax"]["classBlockResult"]);
        
    }
    
}

function as_ajaxBlock($class){
    
    $ajaxBlockClass = "searchAjaxResult";
    
    if($class) $ajaxBlockClass .= " ".$class;
    
    ?>
    
    <div class="<?=$ajaxBlockClass ?>" style="display:none"></div>
    
    <?php
    
}

