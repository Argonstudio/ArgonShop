<?php

function check_saleSteps(){
    
    $setting = get_option('settingShop');
        
     if($setting["typeSale"] == "wholesalePriceSteps"){
         return true;
     }else{
         return false;
     }
    
}

function get_cartPageID(){
    
	$val = get_option('settingShop');
	$val = $val ? $val['shoppingCartPage'] : false;
	
	return esc_attr( $val );

}

function get_cartPageURL(){
    
    $cartID = get_cartPageID();
    
    $cartURL = $cartID ? get_permalink( esc_attr( $cartID ) ) : false;
	
	return $cartURL;

}

function get_cabinetPageID(){
    
	$val = get_option('settingShop');
	$val = $val ? $val['cabinetPage'] : false;
	
	return esc_attr( $val );

}

function get_cabinetPageURL(){
    
    $cabinetID = get_cabinetPageID();
    
    $cabinetURL = $cabinetID ? get_permalink( esc_attr( $cabinetID ) ) : false;
	
	return $cabinetURL;

}

function show_user_fields($userID, $type, $wrap, $class50, $class100, $classTitle, $classInput, $required, $admin){
    
    $settingShop = get_option('settingShop');
    $userFildsSetting = $settingShop[$type];
    
    $accountData = get_user_meta($userID, "accountData",true);
    
    //print_r($accountData);
        
    echo wptexturize($accountData["accountLegalDetail"]["Название компании"]);
    
    //Ключ по которому поля будут сохраняться в базе
    $fieldsSaveKey;
    
    if($type == "quick"){
        
        $fieldsSaveKey = "accountQuick";
        
    }else if($type == "person"){
        
        $fieldsSaveKey = "accountDetail";
        
    }else if($type == "legalPerson"){
        
        $fieldsSaveKey = "accountLegalDetail";
        
    }
    
    $inputClass = $fieldsSaveKey;
    
    
    if($classInput){
        $inputClass .= " ".$classInput;
    }
    $subWrap;
    
    if($wrap == "tr"){
        $subWrap = "td";
    }else{
        $subWrap = "div";
    } 
    
    //print_r($userFildsSetting);
    
    foreach($userFildsSetting as $key => $value){
        
        $class;
        $colspan = "";
        
        if( (int)$value["size"] == 50 ){
            $class = $class50;
        }else if( (int)$value["size"] == 100 ){
            $class = $class100;
            $colspan = "2";
        }
        
        echo "<".$wrap;
        echo " class='".$class."'";
        echo ">";
        
        ?>
        
            <<?=$subWrap?> class="<?=$classTitle?>" <?php if($wrap == "tr" && $colspan !== ""){echo "colspan='".$colspan."'"; } ?> > 
            
                <?=$value["name"]?>
        
            <!-- закрываем если это не двойная ячейка таблицы -->
            <?php echo ($colspan !== "" && $subWrap == "td") ? "" : "</".$subWrap.">";
        
        
            if($subWrap == "td"){ 
                
                if($colspan === ""){
                    echo "<td>";
                }else{
                    echo "<p>";
                }
                
            }
            
            $inputValue;
            
            if(!$admin){
                
                $inputValue = $accountData[$fieldsSaveKey][$key];
            }
            
            $requiredInput = false;
            
            if($required){
                
                foreach($required as $requiredKey){
                    
                    if($requiredKey == $key){
                        
                        $requiredInput = true;
                        
                    }
                    
                }
                
            }
            
            
            
                ?>
                
                <input type="text" class="<?=$inputClass?>" name="<?=$key?>" placeholder="<?=$value["placeholder"]?>" value="<?=$inputValue?>" <?php if($requiredInput){ echo "required='required'"; } ?>>
            
                <?php
            
            if($subWrap == "td"){ 
                
                if($colspan === ""){
                    echo "</td>";
                }else{
                    //Закрываем ячейку с title, если ячейка двойная
                    echo "</p></td>";
                }
                
            }
                
            ?>
            
            
            <?php
        
        
        //echo "<td>1</td>";
        
        echo "</".$wrap.">";
    }
}