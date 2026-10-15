<?php
/**
 * Template Name: История просмотров
 * 
 * Шаблон страницы «История просмотров»
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 *  * 
 * ЧТО ВЫВОДИТ:
 * 1. Хлебные крошки
 * 2. Заголовок и содержимое страницы
 * 3. Список просмотренных товаров (карточками)
 * 4. Пагинация
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 * 
 * Этот шаблон использует следующие функции плагина:
 * 
 * - as_kama_breadcrumbs()       → хлебные крошки (breadcrumbs.php)
 * - as_kama_pagenavi()          → пагинация (pagenavi.php)
 * - as_getCookie()              → чтение cookie корзины (getData.php)
 * - as_get_history_products()   → история просмотров (history.php)
 * - generate_product_card()  → карточка товара (productsLists.php)
 * 
 * ============================================================
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header(); ?>

<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content">

            <?php 
            // Функция плагина Argon Shop (includes/interface/breadcrumbs.php)
            if ( function_exists( 'as_kama_breadcrumbs' ) ) {
                AS_Kama_Breadcrumbs( '<span class="b"> » </span>' );
            }
            ?>

            <?php 
            // Стандартный цикл WordPress
            while ( have_posts() ) : the_post(); 
            ?>

            <div class="page">

                <h1 class="title titleHistory"><?php the_title(); ?></h1>
                <div class="descr"><?php the_content(); ?></div><!-- .descr -->
                
                <?php 
                
                // Функция плагина Argon Shop (includes/api/getData/getData.php)
                // Получаем товары в корзине (чтобы проверить, добавлен ли товар)
                $productsShoppingCart = function_exists( 'as_getCookie' ) 
                    ? as_getCookie( 'productsShoppingCart' ) 
                    : false;
                
                // Функция плагина Argon Shop (includes/interface/history.php)
                // Получаем список просмотренных товаров
                $history_products = function_exists( 'as_get_history_products' ) 
                    ? as_get_history_products() 
                    : false;
                
                if ( $history_products instanceof WP_Query && $history_products->have_posts() ) : 
                ?>
                
                     <div class="block-products-list products-list-history">
                        
                        <div class="products-list catalogProductList">
                            
                            <?php 
                            
                            while ( $history_products->have_posts() ) : $history_products->the_post();
                                
                                global $post;
                                
                                if ( ! $post instanceof WP_Post ) {
                                    continue;
                                }
                                
                                // Функция плагина Argon Shop
                                // (includes/api/view/product/productsLists/productsLists.php)
                                if ( function_exists( 'generate_product_card' ) ) {
                                    generate_product_card( $post, $productsShoppingCart );
                                }
                                
                            endwhile; 
                            ?>
                            
                            <?php 
                            // Функция плагина Argon Shop (includes/interface/pagenavi.php)
                            // ПРИМЕЧАНИЕ: as_kama_pagenavi() использует глобальную $wp_query,
                            // а не $history_products. Пагинация работает некорректно,
                            // если в истории больше товаров, чем помещается на одной странице.
                            // Для корректного поведения нужно передавать $history_products:
                            // as_kama_pagenavi( '', '', true, array(), $history_products );
                            
                            if ( function_exists( 'as_kama_pagenavi' ) ) {
                                as_kama_pagenavi( '', '', true, array(), $history_products );
                            }
                            ?>
                        </div>
                        
                    </div>
                    
                <?php endif; ?>
                
                <?php 
                // PHP 8.5: сброс записей вынесен за пределы условия,
                // чтобы он выполнялся всегда, даже при пустом запросе
                wp_reset_postdata(); 
                ?>
                
                
            </div><!-- #production_list_page -->
                
            <?php endwhile; ?>

        </main>

    </div><!-- .row -->
</section><!-- #out -->


<?php get_footer(); ?>