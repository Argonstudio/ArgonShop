<?php
/**
 * Шапка сайта
 *
 * @package ArgonShopTheme
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 *  
 * Функции плагина, используемые в этом файле:
 * 
 * 1. as_infocart()
 *    ─────────────────────────────────────────────
 *    ОТКУДА: includes/interface/shoppingCart/infoCart.php
 *    
 *    ЧТО ДЕЛАЕТ:
 *    - Читает куки корзины через getCookie("productsShoppingCart")
 *    - Считает число и сумму товаров через getDataCart()
 *    - Выводит блок .viewBlock-shoppingCart с числом и ценой
 *    - Не показывается на странице корзины (проверка get_cartPageID())
 *    
 *    ИСПОЛЬЗУЕТСЯ В: шапке, чтобы показать иконку корзины с суммой
 * 
 * 2. as_search( $searchParameters )
 *    ─────────────────────────────────────────────
 *    ОТКУДА: includes/interface/search/searchForm.php
 *    
 *    ЧТО ДЕЛАЕТ:
 *    - Выводит форму поиска с настройками
 *    - Подключает AJAX-обработчик getAjaxSearchResult
 *      (из searchAjax.php)
 *    - Поддерживает поиск по товарам, категориям и записям
 *    
 *    ИСПОЛЬЗУЕТСЯ В: шапке, для поиска товаров

 * ============================================================
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>

<!DOCTYPE html>
<html>
<head lang="ru" class="no-js" dir="ltr">
    
    <meta charset="UTF-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
	
</head>
<body <?php body_class(); ?>>

<header id="header">
    
    <?php
    
    // Проверяем, является ли текущая страница результатами поиска
    $pageSearch = false;
    
    if ( isset( $_GET['s'] ) && is_string( $_GET['s'] ) ) {
        $pageSearch = true;
    }
    
    ?>
    
    <div class="top-block compensate-for-scrollbar"> 
        <div class="top-content">
            
            <input type="button" class="openMobileMenu">
            
            <a href="/adres-na-karte-moskva/"
               data-modal-ajax="/adres-na-karte-moskva/">
                <input type="button" class="openMobileMap">
            </a>
            
            <nav class="block-menu" id="block-menu">   
                <?php 
                wp_nav_menu( array(
                    'theme_location' => 'top_menu',
                    'menu_id'        => 'top-menu',
                ) );
                ?>
            </nav> 
        
            <div class="block-rightElementsTopContent" style="<?php echo ( ! $pageSearch ) ? 'margin-right:51px' : ''; ?>">
        
                <a href="/lichnyj-kabinet/"><input type="button" class="myCabinetButton" value="Мой кабинет"></a>
                
                <?php 
                // ============================================
                // ВЫЗОВ ФУНКЦИИ ПЛАГИНА: as_infocart()
                // ============================================
                // 
                // Файл: includes/interface/shoppingCart/infoCart.php
                // 
                // Функция выводит блок с числом и суммой товаров в корзине.
                // Внутри использует:
                //   - getCookie("productsShoppingCart") — из getData.php
                //   - getDataCart() — из getData.php
                // 
                // Блок НЕ выводится на странице корзины.
                // 
                // ДОБАВЛЕНО ПЛАГИНОМ: в argon-shop.php через require_once
                
                as_infocart(); 
                ?>
                
            </div>
            
            <?php if ( ! $pageSearch ) { ?>
            
                <div class="block-topSearch">
                        
                    <?php 
                    /**
                     * ============================================
                     * ВЫЗОВ ФУНКЦИИ ПЛАГИНА: as_search()
                     * ============================================
                     * 
                     * Файл: includes/interface/search/searchForm.php
                     * 
                     * ФУНКЦИЯ: Вывод формы поиска
                     * 
                     * ПОДДЕРЖИВАЕТ:
                     * 1. Обычный поиск (отправка GET на /?s=запрос)
                     * 2. AJAX-поиск (результаты без перезагрузки страницы)
                     * 3. Поиск по нескольким типам записей (товары, статьи, страницы)
                     * 4. Фильтрацию по таксономиям (каталог, рубрики)
                     * 5. Несколько блоков результатов (акции, новости, блог)
                     * 
                     * ВАЖНО: Поиск по записям и рубрикам записей работает "из коробки",
                     *        без дополнительных доработок плагина. Если в системе нет
                     *        товаров или таксономии catalog — соответствующие блоки
                     *        в результатах просто не появятся (без ошибок).
                     * 
                     * AJAX-ОБРАБОТЧИК:
                     * Форма отправляет запрос на action=getAjaxSearchResult
                     * (см. includes/interface/search/searchAjax.php)
                     * 
                     * СТРУКТУРА ПАРАМЕТРОВ:                     * 
                     * 
                     * @param array $searchParameters {
                     *     Массив параметров формы поиска.
                     * 
                     *     @type array  $post_type      Массив типов записей для поиска.
                     *                                  Для товаров: array('product').
                     *                                  Пока что тестировался только с array('product') , может поддерживать другие типы записей.   
                     * 
                     * 
                     *     @type array  $taxonomy       Массив таксономий для фильтрации.
                     *                                  Для каталога: array('catalog' => 'all').
                     * 
                     *                                  Таксономия "catalog" зашита в AJAX-обработчике (searchAjax.php) жёстко. Прочее не тестировалось и сделано на перспективу.
                     * 
                     *     @type string $classForm      CSS-класс для <form>.
                     *                                  По умолчанию: 'asSearchForm'.
                     * 
                     *     @type string $classInput     CSS-класс для <input type="text">.
                     *                                  По умолчанию: 'asInputSearchForm'.
                     * 
                     *     @type string $classSubmit    CSS-класс для <input type="submit">.
                     *                                  По умолчанию: 'asSubmitSearchForm'.
                     * 
                     *     @type string $valueSubmit    Текст кнопки поиска.
                     *                                  По умолчанию: 'Поиск'.
                     * 
                     *     @type array  $ajax {
                     *         Настройки AJAX-поиска. Если отсутствует — обычный поиск.
                     * 
                     *         @type string       $classBlockResult CSS-класс контейнера результатов.
                     *         @type string       $positionResult   Позиция контейнера: 'top' или 'bottom'.
                     *                                              По умолчанию: 'bottom'.
                     * 
                     *         @type bool         $productResult    Искать ли товары.
                     *                                              Если товаров нет в системе — блок не появится.
                     *                                              По умолчанию: не передаётся.
                     * 
                     *         @type bool         $catalogResult    Искать ли категории каталога.
                     *                                              Если таксономии catalog нет — блок не появится.
                     *                                              По умолчанию: не передаётся.
                     * 
                     *         @type array|bool   $postResult {
                     *             Массив дополнительных блоков поиска записей.
                     *             Работает независимо от productResult и catalogResult.
                     * 
                     *             @type array $item {
                     *                 @type string $name     Заголовок блока (например, "Акции и статьи").
                     *                 @type string $class    CSS-класс блока.
                     *                 @type array  $category Массив ID рубрик записей для поиска.
                     *             }
                     *         }
                     *     }
                     * }
                     */
                    
                    $searchParameters = array(
                        'post_type'   => array( 'product' ),
                        'taxonomy'    => array(
                            'catalog' => 'all',
                        ),
                        'classForm'   => 'topSearchForm',
                        'classInput'  => 'topSearchInput',
                        'classSubmit' => 'topSearchSubmit',
                        'valueSubmit' => '',
                        
                        'ajax' => array(
                            'classBlockResult' => 'topSearchBlockResult',
                            'positionResult'   => 'bottom',
                            'productResult'    => true,
                            'catalogResult'    => true,
                            'postResult'       => array(
                                array(
                                    'name'     => 'Акции и статьи',
                                    'class'    => 'resultEntry',
                                    'category' => array( 36, 37 ),
                                ),
                                array(
                                    'name'     => 'Новости',
                                    'class'    => 'resultEntry',
                                    'category' => array( 1 ),
                                ),
                            ),
                        ),
                    );
                    
                    // Вызов функции плагина Argon Shop
                    as_search( $searchParameters );
                    ?>
                    
                </div>
                
            <?php } ?>
        
        </div>
    </div>
    
    <div class="header_top">
        <div class="row align-justify">
        
            <div class="icon_call_address menu_mobile menu_mobile_address topmenu_mobile column"></div>
            
            <div class="logo">
                <a href="/"><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/img/logo.webp" alt=""></a>
            </div>
            
            <div class="block-address">
                
                <p class="address"><span class="map_icon"></span><a href="/adres-na-karte-moskva/" class="maps_link" data-modal-ajax="/adres-na-karte-moskva/">Москва, ул. Ленина, дом 1 </a></p>
                
                <p class="work_time">ПН-ПТ: с 9:00 до 18:00</br>СБ: с 10:00 до 15:00</p>
                
            </div>
            
            <div class="block-communication">
                <a href="tel:89999999999" class="phone"><span class="phone_icon"></span> +7 (999) 999-99-99 </a>
                    
                <a href="#feedback"
                   id="linkFeedback"
                   data-modal-inline="#feedback">
                    ОБРАТНАЯ СВЯЗЬ
                </a>
                
                <?php 
                
                // ============================================
                // ПЕРЕКЛЮЧАТЕЛЬ ГОРОДА (Москва / Истра)
                // ============================================
                // 
                // При переходе на сайт Истры передаются:
                // - data-promocategory — слаг рубрики акций (ID 35)
                // - data-promoarchive  — слаг страницы архива акций (ID 642)
                // 
                // Эти данные используются JS-скриптом для подмены ссылок
                // на страницы акций при переключении города.
                // 
                // PHP 8.5: $istra_dataSitySelect инициализируется пустой строкой,
                // иначе при отсутствии условий будет Warning "Undefined variable"
                
                $istra_dataSitySelect = '';
                
                if ( is_archive() || is_single() || is_page( 639 ) ) {
                    
                    $istra_promoCategory = get_category( 35 );
                    
                    if ( $istra_promoCategory && ! is_wp_error( $istra_promoCategory ) && isset( $istra_promoCategory->slug ) ) {
                        $istra_dataSitySelect = "data-promocategory='/" . esc_attr( $istra_promoCategory->slug ) . "/'";
                    }
                    
                    $istra_promoArchive = get_post( 642 );
                    
                    if ( $istra_promoArchive && isset( $istra_promoArchive->post_name ) ) {
                        $istra_dataSitySelect .= " data-promoarchive='/" . esc_attr( $istra_promoArchive->post_name ) . "/'";
                    }
                }
                
                ?>
                
                <select class="sitySelect">
                    <option selected="" value="/">Москва</option>
                    <!--<option <?//php echo $istra_dataSitySelect; ?> value="http://istra.voitkoze.beget.tech">Истра</option>-->
                </select>
                
            </div>
            
            <div style="display: none;" id="feedback">
                <?php echo do_shortcode( '[contact-form-7 id="7" title="Обратная связь"]' ); ?>
            </div>
            
        </div>
    </div>
    
</header>