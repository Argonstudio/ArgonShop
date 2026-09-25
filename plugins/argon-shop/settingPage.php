<?php
/**
 * Страница настроек магазина
 *
 * @package Argon_Shop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * WordPress Админка → Настройки → Магазин
 * URL: /wp-admin/options-general.php?page=settings-shop
 * 
 * ЧТО ДЕЛАЕТ:
 * 1. Регистрирует страницу настроек в админке
 * 2. Выводит поля: ID корзины, ID кабинета, тип скидок, формы заказа
 * 3. Валидирует и сохраняет настройки
 * 
 * PHP 8.5:
 * - убран extract()
 * - проверки isset() и типов
 * - защита от undefined index
 * - экранирование вывода
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ============================================
// РЕГИСТРАЦИЯ СТРАНИЦЫ НАСТРОЕК
// ============================================

add_action( 'admin_menu', 'setting_register_admin_page' );

function setting_register_admin_page() {
    
    add_options_page(
        'Настройки магазина',           // Заголовок страницы
        'Магазин',                      // Название пункта меню
        'manage_options',               // Права доступа
        'settings-shop',                // Слаг страницы
        'settings_shop_rendering'       // Функция вывода
    );
}

/**
 * ФУНКЦИЯ: Вывод страницы настроек
 * 
 * ГДЕ: WordPress Админка → Настройки → Магазин
 */
function settings_shop_rendering() {
    ?>
    <div class="wrap">
        <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

        <form action="options.php" class="settingShopForm" method="POST">
            <?php
            // Защитные поля
            settings_fields( 'option_group' );
            
            // Секции с настройками
            do_settings_sections( 'settingShopPage' );
            
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// ============================================
// РЕГИСТРАЦИЯ НАСТРОЕК
// ============================================

add_action( 'admin_init', 'plugin_settings' );

function plugin_settings() {
    
    // Регистрируем группу опций
    register_setting( 'option_group', 'settingShop', 'sanitize_callback' );
    
    // ============================================
    // СЕКЦИЯ: ОСНОВНЫЕ НАСТРОЙКИ
    // ============================================
    
    add_settings_section(
        'mainSettingShop',
        'Основные настройки',
        '',
        'settingShopPage'
    );
    
    add_settings_field(
        'shoppingCartPage',
        'ID страницы корзины',
        'shoppingCartPage_callback',
        'settingShopPage',
        'mainSettingShop'
    );
    
    add_settings_field(
        'cabinetPage',
        'ID страницы кабинета',
        'cabinetPage_callback',
        'settingShopPage',
        'mainSettingShop'
    );
    
    // ============================================
    // СЕКЦИЯ: СКИДКИ
    // ============================================
    
    add_settings_section(
        'discountsSettingShop',
        'Скидки',
        '',
        'settingShopPage'
    );
    
    $saleParemeters = array(
        'typeSale' => array(
            'noSale'              => 'Без скидок',
            'wholesalePriceSteps' => 'Шаги оптовых цен',
        ),
    );
    
    add_settings_field(
        'typeSale',
        'Тип системы скидок',
        'typeSale_callback',
        'settingShopPage',
        'discountsSettingShop',
        $saleParemeters
    );
    
    // ============================================
    // СЕКЦИЯ: ДАННЫЕ ПОЛЬЗОВАТЕЛЯ
    // ============================================
    
    add_settings_section(
        'userDataShop',
        'Данные пользователя',
        '',
        'settingShopPage'
    );
    
    add_settings_field(
        'quickLine',
        'Быстрый заказ',
        'quick_callback',
        'settingShopPage',
        'userDataShop'
    );
    
    add_settings_field(
        'personLine',
        'Физические лица',
        'person_callback',
        'settingShopPage',
        'userDataShop'
    );
    
    add_settings_field(
        'legalPersonLine',
        'Юридические лица',
        'legalPerson_callback',
        'settingShopPage',
        'userDataShop'
    );
}

// ============================================
// CALLBACK-ФУНКЦИИ ПОЛЕЙ
// ============================================

/**
 * CALLBACK: Поле выбора типа скидок (radio)
 * 
 * PHP 8.5: убран extract(), используется явное обращение к $arguments['typeSale']
 * 
 * @param array $arguments — массив с параметрами поля
 */
function typeSale_callback( $arguments ) {
    
    // PHP 8.5: проверяем, что передан массив и есть ключ
    if ( ! is_array( $arguments ) || ! isset( $arguments['typeSale'] ) ) {
        return;
    }
    
    $typeSale = $arguments['typeSale'];
    
    if ( ! is_array( $typeSale ) ) {
        return;
    }
    
    $setting = get_option( 'settingShop' );
    
    if ( ! is_array( $setting ) ) {
        $setting = array();
    }
    
    $option_name = 'settingShop';
    $id = 'typeSale';
    
    $current_value = isset( $setting[ $id ] ) ? $setting[ $id ] : '';
    
    echo '<fieldset>';
    
    foreach ( $typeSale as $key => $value ) {
        
        $key_esc = esc_attr( $key );
        $value_esc = esc_html( $value );
        $option_name_esc = esc_attr( $option_name );
        $id_esc = esc_attr( $id );
        
        // PHP 8.5: строгие сравнения
        $is_checked = (
            ( empty( $current_value ) && $key === 'noSale' )
            || ( $current_value === $key )
        );
        
        $checked = $is_checked ? "checked='checked'" : '';
        
        echo "<label><input type='radio' name='{$option_name_esc}[{$id_esc}]' value='{$key_esc}' {$checked} />{$value_esc}</label><br />";
    }
    
    echo '</fieldset>';
}

/**
 * CALLBACK: ID страницы корзины
 */
function shoppingCartPage_callback() {
    
    $setting = get_option( 'settingShop' );
    
    $val = ( is_array( $setting ) && isset( $setting['shoppingCartPage'] ) ) 
        ? $setting['shoppingCartPage'] 
        : '';
    
    ?>
    <input type="text" name="settingShop[shoppingCartPage]" value="<?php echo esc_attr( $val ); ?>" />
    <?php
}

/**
 * CALLBACK: ID страницы кабинета
 */
function cabinetPage_callback() {
    
    $setting = get_option( 'settingShop' );
    
    $val = ( is_array( $setting ) && isset( $setting['cabinetPage'] ) ) 
        ? $setting['cabinetPage'] 
        : '';
    
    ?>
    <input type="text" name="settingShop[cabinetPage]" value="<?php echo esc_attr( $val ); ?>" />
    <?php
}

/**
 * CALLBACK: Быстрый заказ
 */
function quick_callback() {
    
    $setting = get_option( 'settingShop' );
    
    $val = ( is_array( $setting ) && isset( $setting['quickLine'] ) ) 
        ? $setting['quickLine'] 
        : '';
    
    ?>
    <textarea class="large-text code" name="settingShop[quickLine]"><?php echo esc_textarea( $val ); ?></textarea>
    <?php
}

/**
 * CALLBACK: Физические лица
 */
function person_callback() {
    
    $setting = get_option( 'settingShop' );
    
    $val = ( is_array( $setting ) && isset( $setting['personLine'] ) ) 
        ? $setting['personLine'] 
        : '';
    
    ?>
    <textarea class="large-text code" name="settingShop[personLine]"><?php echo esc_textarea( $val ); ?></textarea>
    <?php
}

/**
 * CALLBACK: Юридические лица
 */
function legalPerson_callback() {
    
    $setting = get_option( 'settingShop' );
    
    $val = ( is_array( $setting ) && isset( $setting['legalPersonLine'] ) ) 
        ? $setting['legalPersonLine'] 
        : '';
    
    ?>
    <textarea class="large-text code" name="settingShop[legalPersonLine]"><?php echo esc_textarea( $val ); ?></textarea>
    <?php
}

// ============================================
// ВАЛИДАЦИЯ И СОХРАНЕНИЕ
// ============================================

/**
 * CALLBACK: Очистка и преобразование значений перед сохранением * 
 * 
 * @param mixed $options — сырые значения из формы
 * 
 * @return array Очищенный массив настроек
 */
function sanitize_callback( $options ) {
    
    if ( ! is_array( $options ) ) {
        return array();
    }
    
    foreach ( $options as $name => $value ) {
        
        // Очистка от HTML-тегов
        $options[ $name ] = strip_tags( $value );
        
        // ============================================
        // ОБРАБОТКА МНОГОСТРОЧНЫХ ПОЛЕЙ ФОРМ
        // ============================================
        
        if ( in_array( $name, array( 'quickLine', 'personLine', 'legalPersonLine' ), true ) ) {
            
            $nameArray = str_replace( 'Line', '', $name );
            
            $options[ $nameArray ] = explode( "\n", $value );
            
            $newValue = array();
            
            foreach ( $options[ $nameArray ] as $numberLine => $undividedLine ) {
                
                // Пропускаем пустые строки
                if ( trim( $undividedLine ) === '' ) {
                    continue;
                }
                
                // PHP 8.5: проверяем наличие разделителя "["
                $separationKeyValue = explode( '[', $undividedLine );
                
                if ( ! isset( $separationKeyValue[1] ) ) {
                    continue;
                }
                
                $separationKeyValue[1] = str_replace( ']', '', $separationKeyValue[1] );
                
                // Ключ поля должен быть непустой
                if ( empty( $separationKeyValue[0] ) ) {
                    continue;
                }
                
                // ============================================
                // РАЗБОР ЗНАЧЕНИЙ
                // Формат строки: ключ[Имя,placeholder,размер]
                // ============================================
                
                $separationValueOfLine = explode( ',', $separationKeyValue[1] );
                
                $field_name = isset( $separationValueOfLine[0] ) 
                    ? trim( $separationValueOfLine[0] ) 
                    : '';
                
                $field_placeholder = isset( $separationValueOfLine[1] ) 
                    ? trim( $separationValueOfLine[1] ) 
                    : '';
                
                $field_size = isset( $separationValueOfLine[2] ) 
                    ? trim( $separationValueOfLine[2] ) 
                    : '100';
                
                // Замена спец-метки на запятую
                $field_placeholder = str_replace( '/запятая/', ',', $field_placeholder );
                
                $keyField = trim( $separationKeyValue[0] );
                
                $transformInArray = array(
                    'name'        => $field_name,
                    'placeholder' => $field_placeholder,
                    'size'        => $field_size,
                );
                
                $newValue[ $keyField ] = $transformInArray;
            }
            
            $options[ $nameArray ] = $newValue;
        }
        
        // ============================================
        // ОБРАБОТКА ФЛАЖКА ОПТОВЫХ ЦЕН
        // ============================================
        
        if ( $name === 'wholesalePriceSteps' ) {
            $options[ $name ] = intval( $value );
        }
    }
    
    return $options;
}