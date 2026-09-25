<?php
/**
 * Template Name: История просмотров
 * 
 * Шаблон страницы "История просмотров"
 *
 * @package ArgonShopTheme
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 *  * 
 * ЧТО ВЫВОДИТ:
 * 1. Хлебные крошки
 * 2. Заголовок и контент страницы
 * 3. Список просмотренных товаров (карточками)
 * 4. Пагинацию
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 * 
 * В этом шаблоне используются функции плагина:
 * 
 * - kama_breadcrumbs()       → хлебные крошки (breadcrumbs.php)
 * - kama_pagenavi()          → пагинация (pagenavi.php)
 * - getCookie()              → чтение куки корзины (getData.php)
 * - get_history_products()   → история просмотров (history.php)
 * - generate_product_card()  → карточка товара (productsLists.php)
 * 
 * ============================================================
 */

// Защита от прямого вызова файла
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
            if ( function_exists( 'kama_breadcrumbs' ) ) {
                kama_breadcrumbs( '<span class="b"> » </span>' );
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
                // Получаем товары из корзины (для проверки, добавлен ли товар)
                $productsShoppingCart = function_exists( 'getCookie' ) 
                    ? getCookie( 'productsShoppingCart' ) 
                    : false;
                
                // Функция плагина Argon Shop (includes/interface/history.php)
                // Получаем список просмотренных товаров
                $history_products = function_exists( 'get_history_products' ) 
                    ? get_history_products() 
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
                            // ВНИМАНИЕ: kama_pagenavi() использует глобальный $wp_query,
                            // а не $history_products. Пагинация работает некорректно,
                            // если в истории больше товаров, чем на одной странице.
                            // Для правильной работы нужно передать $history_products:
                            // kama_pagenavi( '', '', true, array(), $history_products );
                            
                            if ( function_exists( 'kama_pagenavi' ) ) {
                                kama_pagenavi( '', '', true, array(), $history_products );
                            }
                            ?>
                        </div>
                        
                    </div>
                    
                <?php endif; ?>
                
                <?php 
                // PHP 8.5: сброс постов вынесен за пределы условия,
                // чтобы гарантированно выполниться, даже если запрос пустой
                wp_reset_postdata(); 
                ?>
                
                
            </div><!-- #production_list_page -->
                
            <?php endwhile; ?>

        </main>

    </div><!-- .row -->
</section><!-- #out -->


<?php get_footer(); ?>