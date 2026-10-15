<?php 
/**
 * Шаблон страницы результатов поиска
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Страница результатов поиска после отправки формы
 * URL: /?s=query&post_type[]=product&as_taxonomy[catalog]=all
 * 
 * ЧТО ВЫВОДИТ:
 * 1. Хлебные крошки (из плагина Argon Shop)
 * 2. Форма поиска (чтобы поискать снова)
 * 3. Результаты поиска карточками товаров
 * 4. Пагинация
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 * 
 * Этот шаблон использует следующие функции плагина:
 * 
 * 1. as_kama_breadcrumbs()
 *    File: includes/interface/breadcrumbs.php
 *    Выводит хлебные крошки: «Главная » Результаты поиска для - [запрос]»
 *    Если запрос пуст — «Главная » Поиск»
 * 
 * 2. as_search( $searchParameters )
 *    File: includes/interface/search/searchForm.php
 *    Выводит форму поиска с её настройками. На этой странице форма
 *    продублирована, чтобы пользователь мог поискать снова.
 *    
 *    AJAX-обработчик формы: action=getAjaxSearchResult
 *    (см. includes/interface/search/searchAjax.php)
 * 
 *    ВАЖНО: поиск работает «из коробки» для товаров и
 *    таксономии каталога. Поиск записей требует расширения
 *    searchAjax.php — параметр postResult в этой версии
 *    обработан не полностью.
 * 
 * 3. as_getCookie( 'productsShoppingCart' )
 *    File: includes/api/getData/getData.php
 *    Читает cookie корзины — нужно, чтобы передать их в generate_product_card()
 *    для показа кнопок «В корзину» / «Товар добавлен».
 * 
 * 4. generate_product_card( $post, $cart )
 *    File: includes/api/view/product/productsLists/productsLists.php
 *    Выводит HTML-карточку товара в результатах поиска.
 * 
 * 5. as_kama_pagenavi()
 *    File: includes/interface/pagenavi.php
 *    Выводит пагинацию.
 * 
 * ФИЛЬТРЫ ПОИСКА:
 * 
 * tax_query применяется к результатам поиска в:
 * - includes/interface/search/searchGetFilters.php
 * 
 * Он читает $_GET['as_taxonomy'] и $_GET['post_type'] и добавляет
 * фильтр к основному запросу WordPress.
 * 
 * ============================================================
 * PHP 8.5:
 * - защита ABSPATH
 * - защита от null при вызове функций плагина
 * - проверки function_exists() для всех функций плагина
 * - проверка WP_Query через have_posts()
 * - wp_reset_postdata() после цикла
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content">
       
       <?php 
       // Функция плагина Argon Shop (includes/interface/breadcrumbs.php)
       if ( function_exists( 'as_kama_breadcrumbs' ) ) {
           AS_Kama_Breadcrumbs( '<span class="b"> » </span>' );
       }
       
       echo "<div class='block-pageSearch'>";
       
       $searchParameters = array(
            
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
            * СТРУКТУРА ПАРАМЕТРОВ:
            * 
            * @param array $searchParameters {
            *     Массив параметров формы поиска.
            * 
            *     @type array  $post_type      Массив типов записей для поиска.
            *                                  Для товаров: array('product').
            * 
            * 
            *     @type array  $taxonomy       Массив таксономий для фильтрации.
            *                                  Для каталога: array('catalog' => 'all').
            * 
            *                                  Таксономия «catalog» зашита в AJAX-обработчике (searchAjax.php). Сделано изменяемым на будущее.
            *                                  Вместо «all» можно указать конкретные категории каталога (по ID).
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
            
            // Искать только товары
            'post_type'   => array( 'product' ),
            
            // Фильтрация по таксономии каталога
            'taxonomy'    => array(
                'catalog' => 'all',
            ),
            
            'classForm'   => 'pageSearchForm',
            'classInput'  => 'pageSearchInput',
            'classSubmit' => 'pageSearchSubmit',
            'valueSubmit' => '',
            
            // AJAX-поиск: результаты без перезагрузки страницы
            'ajax' => array(
                'classBlockResult' => 'pageSearchBlockResult',
                'positionResult'   => 'bottom',
                'productResult'    => true,
                'catalogResult'    => true,
                'postResult'       => array(
                    array(
                        'name'     => __( 'Promotions and articles', 'argon-shop-theme' ),
                        'class'    => 'resultEntry',
                        'category' => array( 36, 37 ),
                    ),
                ),
            ),
        );
        
        // Вызов функции плагина Argon Shop
        if ( function_exists( 'as_search' ) ) {
            as_search( $searchParameters );
        }
       
       echo "</div>";
       
       // Проверяем, введён ли поисковый запрос
       $search_query = get_search_query();
       
       if ( $search_query ) {
           
           // Функция плагина Argon Shop (includes/api/getData/getData.php)
           $productsShoppingCart = function_exists( 'as_getCookie' ) ? as_getCookie( 'productsShoppingCart' ) : false;
           
       ?>
       
           <div class="block-pageSearchResult catalogProductList">
               
                <?php 
                if ( have_posts() ) : 
                    
                    while ( have_posts() ) : the_post(); 
                    
                        global $post;
                        
                        // Пропускаем, если запись некорректна
                        if ( ! $post instanceof WP_Post ) {
                            continue;
                        }
                        
                        // Функция плагина Argon Shop
                        // (includes/api/view/product/productsLists/productsLists.php)
                        if ( function_exists( 'generate_product_card' ) ) {
                            generate_product_card( $post, $productsShoppingCart );
                        }
                    
                    endwhile; 
                    
                    wp_reset_postdata();
                    
                endif; 
                ?>
                
                <?php 
                // Функция плагина Argon Shop (includes/interface/pagenavi.php)
                if ( function_exists( 'as_kama_pagenavi' ) ) {
                    as_kama_pagenavi();
                }
                ?>	
            </div>
       
       <?php
       
       } else {
           echo __( 'Enter a search query', 'argon-shop-theme' );
       }
       
       ?>
        </main>
        
    </div>
</section>

<?php get_footer(); ?>