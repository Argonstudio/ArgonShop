<?php
/**
 * Шапка сайта
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 *  
 * Функции плагина, используемые в этом файле:
 * 
 * 1. as_infocart()
 *    ─────────────────────────────────────────────
 *    SOURCE: includes/interface/shoppingCart/infoCart.php
 *    
 *    ЧТО ОН ДЕЛАЕТ:
 *    - Читает cookie корзины через as_getCookie("productsShoppingCart")
 *    - Считает количество и сумму товаров через as_getDataCart()
 *    - Выводит блок .viewBlock-shoppingCart с количеством и ценой
 *    - Не показывается на странице корзины (проверка as_get_cartPageID())
 *    
 *    ИСПОЛЬЗУЕТСЯ В: шапке, чтобы показать иконку корзины с суммой
 * 
 * 2. as_search( $searchParameters )
 *    ─────────────────────────────────────────────
 *    SOURCE: includes/interface/search/searchForm.php
 *    
 *    ЧТО ОН ДЕЛАЕТ:
 *    - Выводит форму поиска с её настройками
 *    - Подключает AJAX-обработчик getAjaxSearchResult
 *      (из searchAjax.php)
 *    - Поддерживает поиск товаров, категорий и записей
 *    
 *    ИСПОЛЬЗУЕТСЯ В: шапке, для поиска товаров

 * ============================================================
 */

// Запрет прямого доступа к файлу
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
    
    // Проверяем, является ли текущая страница страницей результатов поиска
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
        
                
				<a href="<?php echo esc_url( as_get_cabinetPageURL() ); ?>">
					<input type="button" class="myCabinetButton" value="<?php esc_attr_e( 'My account', 'argon-shop-theme' ); ?>">
				</a>
                
                <?php 
                // ============================================
                // PLUGIN FUNCTION CALL: as_infocart()
                // ============================================
                // 
                // File: includes/interface/shoppingCart/infoCart.php
                // 
                // Функция выводит блок с количеством и суммой товаров в корзине.
                // Внутри использует:
                //   - as_getCookie("productsShoppingCart") — из getData.php
                //   - as_getDataCart() — из getData.php
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
                     * PLUGIN FUNCTION CALL: as_search()
                     * ============================================
                     * 
                     * File: includes/interface/search/searchForm.php
                     * 
                     * ФУНКЦИЯ: выводит форму поиска
                     * 
                     * ПОДДЕРЖИВАЕТ:
                     * 1. Обычный поиск (GET-запрос на /?s=query)
                     * 2. AJAX-поиск (результаты без перезагрузки страницы)
                     * 3. Поиск по нескольким типам записей (товары, статьи, страницы)
                     * 4. Фильтрация по таксономиям (каталог, категории)
                     * 5. Несколько блоков результатов (акции, новости, блог)
                     * 
                     * ВАЖНО: поиск записей и категорий записей работает «из коробки»,
                     *        без дополнительных изменений плагина. Если в системе нет
                     *        товаров или таксономии каталога, соответствующие блоки
                     *        просто не появляются в результатах (без ошибок).
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
                     *                                  Пока протестировано только с array('product'); другие типы записей могут поддерживаться.
                     * 
                     * 
                     *     @type array  $taxonomy       Массив таксономий для фильтрации.
                     *                                  Для каталога: array('catalog' => 'all').
                     * 
                     *                                  Таксономия «catalog» зашита в AJAX-обработчике (searchAjax.php). Всё остальное не протестировано и сделано на будущее.
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
                     *                                  По умолчанию: 'Search' (переводится).
                     * 
                     *     @type array  $ajax {
                     *         Настройки AJAX-поиска. Если отсутствует — обычный поиск.
                     * 
                     *         @type string       $classBlockResult CSS-класс контейнера результатов.
                     *         @type string       $positionResult   Позиция контейнера: 'top' или 'bottom'.
                     *                                              По умолчанию: 'bottom'.
                     * 
                     *         @type bool         $productResult    Искать ли товары.
                     *                                              Если в системе нет товаров, блок не появляется.
                     *                                              По умолчанию: не передаётся.
                     * 
                     *         @type bool         $catalogResult    Искать ли категории каталога.
                     *                                              Если таксономии каталога нет, блок не появляется.
                     *                                              По умолчанию: не передаётся.
                     * 
                     *         @type array|bool   $postResult {
                     *             Массив дополнительных блоков поиска по записям.
                     *             Работает независимо от productResult и catalogResult.
                     * 
                     *             @type array $item {
                     *                 @type string $name     Заголовок блока (например, «Акции и статьи»).
                     *                 @type string $class    CSS-класс блока.
                     *                 @type array  $category Массив ID категорий записей для поиска.
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
                                    'name'     => __( 'Promotions and articles', 'argon-shop-theme' ),
                                    'class'    => 'resultEntry',
                                    'category' => array( 36, 37 ),
                                ),
                                array(
                                    'name'     => __( 'News', 'argon-shop-theme' ),
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
                
                <p class="address"><span class="map_icon"></span><a href="/adres-na-karte-moskva/" class="maps_link" data-modal-ajax="/adres-na-karte-moskva/"><?php esc_html_e( 'Moscow, Lenina St., 1', 'argon-shop-theme' ); ?> </a></p>
                
                <p class="work_time"><?php esc_html_e( 'Mon–Fri: 9:00 to 18:00', 'argon-shop-theme' ); ?></br><?php esc_html_e( 'Sat: 10:00 to 15:00', 'argon-shop-theme' ); ?></p>
                
            </div>
            
            <div class="block-communication">
                <a href="tel:89999999999" class="phone"><span class="phone_icon"></span> +7 (999) 999-99-99 </a>
                    
                <a href="#feedback"
                   id="linkFeedback"
                   data-modal-inline="#feedback">
                    <?php esc_html_e( 'FEEDBACK', 'argon-shop-theme' ); ?>
                </a>
                
                <?php 
                
                // ============================================
                // ПЕРЕКЛЮЧАТЕЛЬ ГОРОДА (Москва / Истра)
                // ============================================
                // 
                // При переключении на сайт Истры передаётся следующее:
                // - data-promocategory — слаг категории акций (ID 35)
                // - data-promoarchive  — слаг страницы архива акций (ID 642)
                // 
                // Эти данные используются JS-скриптом для замены ссылок
                // на страницы акций при переключении города.
                // 
                // PHP 8.5: $istra_dataSitySelect инициализируется пустой строкой,
                // иначе возникает предупреждение «Undefined variable», когда ни одно условие не совпало
                
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
                    <option selected="" value="/"><?php esc_html_e( 'Moscow', 'argon-shop-theme' ); ?></option>
                    <!--<option <?//php echo $istra_dataSitySelect; ?> value="http://istra.site.com">Istra</option>-->
                </select>
                
            </div>
            
            <div style="display: none;" id="feedback">
                <?php echo do_shortcode( '[contact-form-7 id="7" title="Feedback"]' ); ?>
            </div>
            
        </div>
    </div>
    
</header>