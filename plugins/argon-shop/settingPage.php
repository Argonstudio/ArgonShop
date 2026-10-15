<?php
/**
 * Страница настроек магазина
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Админка WordPress → Настройки → Магазин
 * URL: /wp-admin/options-general.php?page=settings-shop
 * 
 * ЧТО ОН ДЕЛАЕТ:
 * 1. Регистрирует страницу настроек в админке
 * 2. Выводит поля: ID корзины, ID кабинета, тип скидки, валюта, формы заказа
 * 3. Проверяет и сохраняет настройки
 * 
 * PHP 8.5:
 * - убран extract()
 * - проверки isset() и типов
 * - защита от неопределённого индекса
 * - экранирование вывода
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ============================================
// РЕГИСТРАЦИЯ СТРАНИЦЫ НАСТРОЕК
// ============================================

add_action( 'admin_menu', 'as_setting_register_admin_page' );

function as_setting_register_admin_page() {
    
    add_options_page(
        __( 'Store settings', 'argon-shop' ),           // Заголовок страницы
        __( 'Store', 'argon-shop' ),                      // Заголовок пункта меню
        'manage_options',               // Право доступа
        'settings-shop',                // Слаг страницы
        'as_settings_shop_rendering'       // Функция отрисовки
    );
}

/**
 * ФУНКЦИЯ: отрисовывает страницу настроек
 * 
 * ГДЕ: Админка WordPress → Настройки → Магазин
 */
function as_settings_shop_rendering() {
    ?>
    <div class="wrap">
        <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

        <form action="options.php" class="settingShopForm" method="POST">
            <?php
            // Скрытые поля безопасности
            settings_fields( 'option_group' );
            
            // Разделы настроек
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

add_action( 'admin_init', 'as_plugin_settings' );

function as_plugin_settings() {
    
    // Регистрируем группу опций
    register_setting( 'option_group', 'settingShop', 'as_sanitize_settings' );
    
    // ============================================
    // РАЗДЕЛ: ОБЩИЕ НАСТРОЙКИ
    // ============================================
    
    add_settings_section(
        'mainSettingShop',
        __( 'General settings', 'argon-shop' ),
        '',
        'settingShopPage'
    );
    
    add_settings_field(
        'shoppingCartPage',
        __( 'Cart page ID', 'argon-shop' ),
        'as_shoppingCartPage_callback',
        'settingShopPage',
        'mainSettingShop'
    );
    
    add_settings_field(
        'cabinetPage',
        __( 'Account page ID', 'argon-shop' ),
        'as_cabinetPage_callback',
        'settingShopPage',
        'mainSettingShop'
    );
    
    add_settings_field(
        'currency',
        __( 'Currency', 'argon-shop' ),
        'as_currency_callback',
        'settingShopPage',
        'mainSettingShop'
    );
    
    // ============================================
    // РАЗДЕЛ: СКИДКИ
    // ============================================
    
    add_settings_section(
        'discountsSettingShop',
        __( 'Discounts', 'argon-shop' ),
        '',
        'settingShopPage'
    );
    
    $saleParemeters = array(
        'typeSale' => array(
            'noSale'              => __( 'No discounts', 'argon-shop' ),
            'wholesalePriceSteps' => __( 'Wholesale price steps', 'argon-shop' ),
        ),
    );
    
    add_settings_field(
        'typeSale',
        __( 'Discount system type', 'argon-shop' ),
        'as_typeSale_callback',
        'settingShopPage',
        'discountsSettingShop',
        $saleParemeters
    );
    
    // ============================================
    // РАЗДЕЛ: ДАННЫЕ ПОЛЬЗОВАТЕЛЯ
    // ============================================
    
    add_settings_section(
        'userDataShop',
        __( 'User data', 'argon-shop' ),
        '',
        'settingShopPage'
    );
    
    add_settings_field(
        'quickLine',
        __( 'Quick order', 'argon-shop' ),
        'as_quick_callback',
        'settingShopPage',
        'userDataShop'
    );
    
    add_settings_field(
        'personLine',
        __( 'Individuals', 'argon-shop' ),
        'as_person_callback',
        'settingShopPage',
        'userDataShop'
    );
    
    add_settings_field(
        'legalPersonLine',
        __( 'Legal entities', 'argon-shop' ),
        'as_legalPerson_callback',
        'settingShopPage',
        'userDataShop'
    );
}

// ============================================
// КОЛБЭКИ ПОЛЕЙ
// ============================================

/**
 * КОЛБЭК: селектор типа скидки (radio)
 * 
 * PHP 8.5: extract() убран, $arguments['typeSale'] доступен явно
 * 
 * @param array $arguments — массив параметров поля
 */
function as_typeSale_callback( $arguments ) {
    
    // PHP 8.5: убеждаемся, что передан массив с ключом
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
 * КОЛБЭК: валюта магазина (select)
 *
 * USD выбирается, когда настройка ещё не сохранена.
 *
 * @since 1.1.0
 */
function as_currency_callback() {
    
    $current = as_get_currency();
    
    echo '<select name="settingShop[currency]" id="settingShop-currency">';
    
    foreach ( as_get_currencies() as $code => $label ) {
        printf(
            '<option value="%1$s"%2$s>%3$s</option>',
            esc_attr( $code ),
            selected( $current, $code, false ),
            esc_html( $label )
        );
    }
    
    echo '</select>';
    echo '<p class="description">' . esc_html__( 'Used for all prices in the store, the cart, orders and emails.', 'argon-shop' ) . '</p>';
}

/**
 * КОЛБЭК: ID страницы корзины
 */
function as_shoppingCartPage_callback() {
    
    $setting = get_option( 'settingShop' );
    
    $val = ( is_array( $setting ) && isset( $setting['shoppingCartPage'] ) ) 
        ? $setting['shoppingCartPage'] 
        : '';
    
    ?>
    <input type="text" name="settingShop[shoppingCartPage]" value="<?php echo esc_attr( $val ); ?>" />
    <?php
}

/**
 * КОЛБЭК: ID страницы кабинета
 */
function as_cabinetPage_callback() {
    
    $setting = get_option( 'settingShop' );
    
    $val = ( is_array( $setting ) && isset( $setting['cabinetPage'] ) ) 
        ? $setting['cabinetPage'] 
        : '';
    
    ?>
    <input type="text" name="settingShop[cabinetPage]" value="<?php echo esc_attr( $val ); ?>" />
    <?php
}

/**
 * КОЛБЭК: Быстрый заказ
 */
function as_quick_callback() {
    
    $setting = get_option( 'settingShop' );
    
    $val = ( is_array( $setting ) && isset( $setting['quickLine'] ) ) 
        ? $setting['quickLine'] 
        : '';
    
    ?>
    <textarea class="large-text code" name="settingShop[quickLine]"><?php echo esc_textarea( $val ); ?></textarea>
    <?php
}

/**
 * КОЛБЭК: Физические лица
 */
function as_person_callback() {
    
    $setting = get_option( 'settingShop' );
    
    $val = ( is_array( $setting ) && isset( $setting['personLine'] ) ) 
        ? $setting['personLine'] 
        : '';
    
    ?>
    <textarea class="large-text code" name="settingShop[personLine]"><?php echo esc_textarea( $val ); ?></textarea>
    <?php
}

/**
 * КОЛБЭК: Юридические лица
 */
function as_legalPerson_callback() {
    
    $setting = get_option( 'settingShop' );
    
    $val = ( is_array( $setting ) && isset( $setting['legalPersonLine'] ) ) 
        ? $setting['legalPersonLine'] 
        : '';
    
    ?>
    <textarea class="large-text code" name="settingShop[legalPersonLine]"><?php echo esc_textarea( $val ); ?></textarea>
    <?php
}

// ============================================
// ПРОВЕРКА И СОХРАНЕНИЕ
// ============================================

/**
 * КОЛБЭК: очищает и преобразует значения перед сохранением
 * 
 * @param mixed $options — необработанные значения из формы
 * 
 * @return array Очищенный массив настроек
 */
function as_sanitize_settings( $options ) {
    
    if ( ! is_array( $options ) ) {
        return array();
    }
    
    foreach ( $options as $name => $value ) {
        
        // Уже разобранные значения (например «quick», когда сохранённые настройки
        // снова очищаются) пересобираются из их источника «...Line» ниже.
        if ( ! is_scalar( $value ) ) {
            continue;
        }
        
        // Убираем HTML-теги
        $options[ $name ] = strip_tags( $value );
        
        // ============================================
        // ОБРАБОТКА МНОГОСТРОЧНЫХ ПОЛЕЙ ФОРМЫ
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
                
                // PHP 8.5: убеждаемся, что разделитель «[» присутствует
                $separationKeyValue = explode( '[', $undividedLine );
                
                if ( ! isset( $separationKeyValue[1] ) ) {
                    continue;
                }
                
                $separationKeyValue[1] = str_replace( ']', '', $separationKeyValue[1] );
                
                // Ключ поля не должен быть пустым
                if ( empty( $separationKeyValue[0] ) ) {
                    continue;
                }
                
                // ============================================
                // РАЗБОР ЗНАЧЕНИЙ
                // Формат строки: key[Название,плейсхолдер,размер]
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
                
                // Заменяем специальный маркер запятой (/comma/; старый русский маркер тоже принимается)
                $field_placeholder = str_replace( array( '/comma/', '/запятая/' ), ',', $field_placeholder );
                
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
        // ОБРАБОТКА ЧЕКБОКСОВ ОПТОВЫХ ЦЕН
        // ============================================
        
        if ( $name === 'wholesalePriceSteps' ) {
            $options[ $name ] = intval( $value );
        }
        
        // ============================================
        // ВАЛЮТА: только поддерживаемые коды (с 1.1.0)
        // ============================================
        
        if ( $name === 'currency' ) {
            $currency = strtoupper( trim( (string) $value ) );
            $options[ $name ] = array_key_exists( $currency, as_get_currencies() ) ? $currency : 'USD';
        }
    }
    
    return $options;
}