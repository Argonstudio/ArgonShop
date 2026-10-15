<?php
/**
 * Получение настроек магазина и вспомогательные функции
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * ЧТО ОН ДЕЛАЕТ:
 * 1. Проверяет, включены ли оптовые скидки
 * 2. Получает ID и URL страниц корзины и кабинета
 * 3. Выводит поля формы пользователя
 * 4. Валюта магазина: код, символ и форматирование цены (с 1.1.0)
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ФУНКЦИЯ: проверяет, включены ли оптовые скидки
 * 
 * @return bool true — скидки включены, false — выключены
 */
function as_check_saleSteps() {
    
    $setting = get_option( 'settingShop' );
    
    if ( isset( $setting['typeSale'] ) && $setting['typeSale'] === 'wholesalePriceSteps' ) {
        return true;
    }
    
    return false;
}

/**
 * ФУНКЦИЯ: получает ID страницы корзины
 * 
 * @return int ID страницы или 0
 */
function as_get_cartPageID() {
    
    $val = get_option( 'settingShop' );
    $val = ( $val && isset( $val['shoppingCartPage'] ) ) ? absint( $val['shoppingCartPage'] ) : 0;
    
    return $val;
}

/**
 * ФУНКЦИЯ: получает URL страницы корзины
 * 
 * @return string|false URL страницы или false
 */
function as_get_cartPageURL() {
    
    $cartID = as_get_cartPageID();
    
    $cartURL = $cartID ? get_permalink( $cartID ) : false;
    
    return $cartURL;
}

/**
 * ФУНКЦИЯ: получает ID страницы кабинета
 * 
 * @return int ID страницы или 0
 */
function as_get_cabinetPageID() {
    
    $val = get_option( 'settingShop' );
    $val = ( $val && isset( $val['cabinetPage'] ) ) ? absint( $val['cabinetPage'] ) : 0;
    
    return $val;
}

/**
 * ФУНКЦИЯ: получает URL страницы кабинета
 * 
 * @return string|false URL страницы или false
 */
function as_get_cabinetPageURL() {
    
    $cabinetID = as_get_cabinetPageID();
    
    $cabinetURL = $cabinetID ? get_permalink( $cabinetID ) : false;
    
    return $cabinetURL;
}

/**
 * ФУНКЦИЯ: выводит поля формы пользователя
 * 
 * @param int|null    $userID      — ID пользователя
 * @param string|null $type        — тип формы: quick, person, legalPerson
 * @param string|null $wrap        — обёртка: tr или div
 * @param string|null $class50     — CSS-класс для поля шириной 50%
 * @param string|null $class100    — CSS-класс для поля шириной 100%
 * @param string|null $classTitle  — CSS-класс заголовка
 * @param string|null $classInput  — CSS-класс input
 * @param array|null  $required    — массив обязательных полей
 * @param bool|null   $admin       — режим админа (пользователь видит свои данные, администратор видит пустую форму для заполнения)
 */
function as_show_user_fields($userID = null, $type = null, $wrap = null, $class50 = null, $class100 = null, $classTitle = null, $classInput = null, $required = null, $admin = null){
    
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
// ============================================
// ВАЛЮТА МАГАЗИНА (с 1.1.0)
// ============================================

/**
 * ФУНКЦИЯ: поддерживаемые валюты магазина
 *
 * Код => подпись для поля «Валюта» в Настройки → Магазин.
 *
 * @since 1.1.0
 * @return array<string, string>
 */
function as_get_currencies() {

    $currencies = array(
        'USD' => __( 'US dollar ($)', 'argon-shop' ),
        'EUR' => __( 'Euro (€)', 'argon-shop' ),
        'RUB' => __( 'Russian ruble (₽)', 'argon-shop' ),
    );

    /**
     * Фильтрует список валют, предлагаемых в настройках магазина.
     * Новому коду также нужен символ (argon_shop_currency_symbol).
     *
     * @since 1.1.0
     * @param array<string, string> $currencies Код => подпись.
     */
    return apply_filters( 'argon_shop_currencies', $currencies );
}

/**
 * ФУНКЦИЯ: текущий код валюты магазина
 *
 * Читается из ключа «currency» опции settingShop.
 * Если настройка отсутствует или некорректна, используется USD.
 *
 * @since 1.1.0
 * @return string Код валюты: USD, EUR или RUB
 */
function as_get_currency() {

    $setting  = get_option( 'settingShop' );
    $currency = ( is_array( $setting ) && isset( $setting['currency'] ) ) ? strtoupper( (string) $setting['currency'] ) : '';

    if ( ! array_key_exists( $currency, as_get_currencies() ) ) {
        $currency = 'USD';
    }

    /**
     * Фильтрует код валюты магазина.
     *
     * @since 1.1.0
     * @param string $currency Код валюты.
     */
    return (string) apply_filters( 'argon_shop_currency', $currency );
}

/**
 * ФУНКЦИЯ: символ валюты
 *
 * Символ рубля проходит через перевод (msgid «₽»):
 * русский перевод выводит кириллическую букву «Эр», как в предыдущих версиях.
 *
 * @since 1.1.0
 * @param string $currency Код валюты. Пусто — валюта магазина.
 * @return string Символ (не экранирован)
 */
function as_get_currency_symbol( $currency = '' ) {

    $currency = $currency ? strtoupper( $currency ) : as_get_currency();

    switch ( $currency ) {
        case 'RUB':
            $symbol = __( '₽', 'argon-shop' );
            break;
        case 'EUR':
            $symbol = '€';
            break;
        case 'USD':
            $symbol = '$';
            break;
        default:
            $symbol = $currency;
    }

    /**
     * Фильтрует символ валюты.
     *
     * @since 1.1.0
     * @param string $symbol   Символ валюты.
     * @param string $currency Код валюты.
     */
    return (string) apply_filters( 'argon_shop_currency_symbol', $symbol, $currency );
}

/**
 * ФУНКЦИЯ: позиция символа относительно суммы
 *
 * USD: перед суммой ($10). EUR и RUB: после суммы (10 €, 10 ₽).
 *
 * @since 1.1.0
 * @param string $currency Код валюты. Пусто — валюта магазина.
 * @return string 'before' или 'after'
 */
function as_get_currency_position( $currency = '' ) {

    $currency = $currency ? strtoupper( $currency ) : as_get_currency();
    $position = ( $currency === 'USD' ) ? 'before' : 'after';

    /**
     * Фильтрует позицию символа валюты.
     *
     * @since 1.1.0
     * @param string $position 'before' или 'after'.
     * @param string $currency Код валюты.
     */
    $position = apply_filters( 'argon_shop_currency_position', $position, $currency );

    return ( $position === 'before' ) ? 'before' : 'after';
}

/**
 * ФУНКЦИЯ: сумма с символом валюты
 *
 * $amount_html вставляется как есть, поэтому сумма может оставаться внутри
 * <span>, обновляемого JS на фронтенде (например «<span>120</span>»).
 * Экранируйте обычные значения перед передачей.
 *
 * Примеры (единица «pc»):
 *   USD → $<span>120</span>/pc
 *   EUR → <span>120</span> €/pc
 *   RUB → <span>120</span> ₽/pc (или русский перевод символа и единицы)
 *
 * @since 1.1.0
 * @param string $amount_html Экранированная сумма или HTML с суммой.
 * @param string $unit        Единица измерения (не экранирована), необязательно.
 * @return string HTML
 */
function as_price_html( $amount_html, $unit = '' ) {

    $symbol = esc_html( as_get_currency_symbol() );
    $unit   = ( $unit !== '' ) ? '/' . esc_html( $unit ) : '';

    if ( as_get_currency_position() === 'before' ) {
        return $symbol . $amount_html . $unit;
    }

    return $amount_html . ' ' . $symbol . $unit;
}

/**
 * ФУНКЦИЯ: подпись валюты «за штуку» для полей админки
 *
 * @since 1.1.0
 * @return string Например «$/pc», «€/pc» или «₽/pc» (не экранировано)
 */
function as_get_currency_per_piece() {
    /* translators: %s: символ валюты. */
    return sprintf( __( '%s/pc', 'argon-shop' ), as_get_currency_symbol() );
}
