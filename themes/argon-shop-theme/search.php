<?php 
/**
 * Шаблон страницы результатов поиска
 *
 * @package ArgonShopTheme
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Страница результатов поиска после отправки формы
 * URL: /?s=запрос&post_type[]=product&as_taxonomy[catalog]=all
 * 
 * ЧТО ВЫВОДИТ:
 * 1. Хлебные крошки (из плагина Argon Shop)
 * 2. Форму поиска (повторный ввод запроса)
 * 3. Результаты поиска карточками товаров
 * 4. Пагинацию
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 * 
 * В этом шаблоне используются функции плагина:
 * 
 * 1. kama_breadcrumbs()
 *    Файл: includes/interface/breadcrumbs.php
 *    Выводит крошки: "Главная » Результаты поиска по запросу - [запрос]"
 *    Если запрос пустой — "Главная » Поиск"
 * 
 * 2. as_search( $searchParameters )
 *    Файл: includes/interface/search/searchForm.php
 *    Выводит форму поиска с настройками. На этой странице форма
 *    дублируется для возможности повторного поиска.
 *    
 *    AJAX-обработчик формы: action=getAjaxSearchResult
 *    (см. includes/interface/search/searchAjax.php)
 * 
 *    ВАЖНО: поиск работает "из коробки" для товаров и таксономии
 *    catalog. Поиск по записям (post) требует доработки
 *    searchAjax.php — параметр postResult в этой версии не 
 *    обрабатывается полностью.
 * 
 * 3. getCookie( 'productsShoppingCart' )
 *    Файл: includes/api/getData/getData.php
 *    Читает куки корзины — нужно для передачи в generate_product_card(),
 *    чтобы показать кнопки "В корзину" / "Товар добавлен".
 * 
 * 4. generate_product_card( $post, $cart )
 *    Файл: includes/api/view/product/productsLists/productsLists.php
 *    Выводит HTML-карточку товара в результатах поиска.
 * 
 * 5. kama_pagenavi()
 *    Файл: includes/interface/pagenavi.php
 *    Выводит пагинацию.
 * 
 * ФИЛЬТРЫ ПОИСКА:
 * 
 * Применение tax_query к результатам поиска выполняется в:
 * - includes/interface/search/searchGetFilters.php
 * 
 * Он читает $_GET['as_taxonomy'] и $_GET['post_type'] и добавляет
 * фильтр на главный запрос WordPress.
 * 
 * ============================================================
 * PHP 8.5:
 * - защита ABSPATH
 * - защита от null при вызове функций плагина
 * - проверки function_exists() для всех функций плагина
 * - проверка WP_Query через have_posts()
 * - wp_reset_postdata() после цикла
 */

// Защита от прямого вызова файла
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
       if ( function_exists( 'kama_breadcrumbs' ) ) {
           kama_breadcrumbs( '<span class="b"> » </span>' );
       }
       
       echo "<div class='block-pageSearch'>";
       
       $searchParameters = array(
            
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
            *                                  Таксономия "catalog" хардкод в AJAX-обработчике (searchAjax.php). На перспективу сделано с возможностью менять.
            *                                  Есть возможность указать только определенные категории каталога(по ID), вместо "all".   
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
            
            // Поиск только по товарам
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
                        'name'     => 'Акции и статьи',
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
       
       // Проверяем, был ли введён поисковый запрос
       $search_query = get_search_query();
       
       if ( $search_query ) {
           
           // Функция плагина Argon Shop (includes/api/getData/getData.php)
           $productsShoppingCart = function_exists( 'getCookie' ) ? getCookie( 'productsShoppingCart' ) : false;
           
       ?>
       
           <div class="block-pageSearchResult catalogProductList">
               
                <?php 
                if ( have_posts() ) : 
                    
                    while ( have_posts() ) : the_post(); 
                    
                        global $post;
                        
                        // Пропускаем, если пост некорректный
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
                if ( function_exists( 'kama_pagenavi' ) ) {
                    kama_pagenavi();
                }
                ?>	
            </div>
       
       <?php
       
       } else {
           echo 'Введите поисковой запрос';
       }
       
       ?>
        </main>
        
    </div>
</section>

<?php get_footer(); ?>