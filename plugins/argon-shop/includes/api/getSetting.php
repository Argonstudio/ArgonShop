<?php
/**
 * Получение настроек магазина и вспомогательные функции
 *
 * @package Argon_Shop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ЧТО ДЕЛАЕТ:
 * 1. Проверка активации оптовых скидок
 * 2. Получение ID и URL страниц корзины и кабинета
 * 3. Вывод полей формы пользователя
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ФУНКЦИЯ: Проверка активации оптовых скидок
 * 
 * @return bool true — скидки включены, false — выключены
 */
function check_saleSteps() {
    
    $setting = get_option( 'settingShop' );
    
    if ( isset( $setting['typeSale'] ) && $setting['typeSale'] === 'wholesalePriceSteps' ) {
        return true;
    }
    
    return false;
}

/**
 * ФУНКЦИЯ: Получение ID страницы корзины
 * 
 * @return int ID страницы или 0
 */
function get_cartPageID() {
    
    $val = get_option( 'settingShop' );
    $val = ( $val && isset( $val['shoppingCartPage'] ) ) ? absint( $val['shoppingCartPage'] ) : 0;
    
    return $val;
}

/**
 * ФУНКЦИЯ: Получение URL страницы корзины
 * 
 * @return string|false URL страницы или false
 */
function get_cartPageURL() {
    
    $cartID = get_cartPageID();
    
    $cartURL = $cartID ? get_permalink( $cartID ) : false;
    
    return $cartURL;
}

/**
 * ФУНКЦИЯ: Получение ID страницы личного кабинета
 * 
 * @return int ID страницы или 0
 */
function get_cabinetPageID() {
    
    $val = get_option( 'settingShop' );
    $val = ( $val && isset( $val['cabinetPage'] ) ) ? absint( $val['cabinetPage'] ) : 0;
    
    return $val;
}

/**
 * ФУНКЦИЯ: Получение URL страницы личного кабинета
 * 
 * @return string|false URL страницы или false
 */
function get_cabinetPageURL() {
    
    $cabinetID = get_cabinetPageID();
    
    $cabinetURL = $cabinetID ? get_permalink( $cabinetID ) : false;
    
    return $cabinetURL;
}

/**
 * ФУНКЦИЯ: Вывод полей формы пользователя
 * 
 * @param int|null    $userID      — ID пользователя
 * @param string|null $type        — тип формы: quick, person, legalPerson
 * @param string|null $wrap        — обёртка: tr или div
 * @param string|null $class50     — CSS-класс для поля 50%
 * @param string|null $class100    — CSS-класс для поля 100%
 * @param string|null $classTitle  — CSS-класс заголовка
 * @param string|null $classInput  — CSS-класс input
 * @param array|null  $required    — массив обязательных полей
 * @param bool|null   $admin       — режим админки (пользователь видит свои данные, администратор пустую форму для заполнения)
 */
function show_user_fields($userID = null, $type = null, $wrap = null, $class50 = null, $class100 = null, $classTitle = null, $classInput = null, $required = null, $admin = null){
    
    $settingShop = get_option('settingShop');
    
    $userFildsSetting = ( !empty($settingShop[$type]) )?$settingShop[$type]:0;
    
    $userID = absint($userID);
    $accountData = get_user_meta($userID, "accountData",true);
    
    if( !is_array($accountData) ){
        $accountData = array();
    }
    
    if( !empty($accountData["accountLegalDetail"]["Название компании"]) ){
        echo esc_html( wptexturize($accountData["accountLegalDetail"]["Название компании"]) );
    }    
    
    $fieldsSaveKey = '';
    
    if($type == "quick"){
        $fieldsSaveKey = "accountQuick";
    }else if($type == "person"){
        $fieldsSaveKey = "accountDetail";
    }else if($type == "legalPerson"){
        $fieldsSaveKey = "accountLegalDetail";
    }
    
    $inputClass = ( !empty($fieldsSaveKey) )?$fieldsSaveKey:'';
    
    if($classInput){
        $inputClass .= " ".esc_attr($classInput);
    }else{
        $inputClass = esc_attr($inputClass);
    }
    
    if($wrap == "tr"){
        $subWrap = "td";
    }else{
        $subWrap = "div";
    }
    
    if($userFildsSetting){
        
        foreach($userFildsSetting as $key => $value){
        
            $class = '';
            $colspan = "";
            
            $size = isset($value["size"]) ? (int)$value["size"] : 100;
            
            if( $size == 50 ){
                $class = $class50;
            }else if( $size == 100 ){
                $class = $class100;
                $colspan = "2";
            }
            
            $class_esc = $class ? esc_attr($class) : '';
            $wrap_esc = $wrap ? esc_attr($wrap) : 'div';
            
            echo "<".$wrap_esc;
            echo " class='".$class_esc."'";
            echo ">";
            
            $subWrap_esc = esc_attr($subWrap);
            $classTitle_esc = $classTitle ? esc_attr($classTitle) : '';
            
            ?>
            
                <<?php echo $subWrap_esc; ?> class="<?php echo $classTitle_esc; ?>" <?php if($wrap == "tr" && $colspan !== ""){echo "colspan='".esc_attr($colspan)."'"; } ?> > 
                
                    <?php echo esc_html($value["name"]); ?>
            
                <?php echo ($colspan !== "" && $subWrap == "td") ? "" : "</".$subWrap_esc.">"; ?>
            
                <?php
            
                if($subWrap == "td"){ 
                    
                    if($colspan === ""){
                        echo "<td>";
                    }else{
                        echo "<p>";
                    }
                    
                }
                
                $inputValue = '';
                
                if(!$admin && !empty($accountData[$fieldsSaveKey][$key])){
                    $inputValue = $accountData[$fieldsSaveKey][$key];
                }
                
                $requiredInput = false;
                
                if($required && is_array($required)){
                    
                    foreach($required as $requiredKey){
                        
                        if($requiredKey == $key){
                            $requiredInput = true;
                        }
                    }
                }
                
                $inputClass_esc = esc_attr($inputClass);
                $key_esc = esc_attr($key);
                $placeholder_esc = isset($value["placeholder"]) ? esc_attr($value["placeholder"]) : '';
                $inputValue_esc = esc_attr($inputValue);
                $required_attr = $requiredInput ? "required='required'" : '';
                
                ?>
                
                <input type="text" class="<?php echo $inputClass_esc; ?>" name="<?php echo $key_esc; ?>" placeholder="<?php echo $placeholder_esc; ?>" value="<?php echo $inputValue_esc; ?>" <?php echo $required_attr; ?>>
            
                <?php
                
                if($subWrap == "td"){ 
                    
                    if($colspan === ""){
                        echo "</td>";
                    }else{
                        echo "</p></td>";
                    }
                }
                
                ?>
                
                <?php
            
            echo "</".$wrap_esc.">";
        }
    }
    
}