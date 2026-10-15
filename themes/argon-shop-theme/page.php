<?php
/**
 * Шаблон статической страницы (page)
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Любая статическая страница WordPress
 * URL: /page-name/
 * 
 * ЧТО ВЫВОДИТ:
 * 1. Хлебные крошки (через kama_breadcrumbs из плагина Argon Shop)
 * 2. Заголовок страницы
 * 3. Изображение страницы (если есть)
 * 4. Содержимое страницы
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 * 
 * Этот шаблон использует следующую функцию плагина:
 * 
 * - as_kama_breadcrumbs( ' » ' )
 *   File: includes/interface/breadcrumbs.php
 *   Выводит цепочку вида: «Главная » Заголовок страницы»
 *   Для вложенных страниц: «Главная » Родитель » Дочерняя»
 * 
 * ============================================================
 * ШАБЛОНЫ, ПЕРЕОПРЕДЕЛЯЮЩИЕ ЭТОТ ФАЙЛ
 * ============================================================
 * 
 * У некоторых страниц есть собственные шаблоны, и page.php они не используют:
 * 
 * - page-history.php (или Template Name: View history) → history.php
 * - page-contacts.php (Template Name: Contacts) → Контакты
 * - page-about.php (Template Name: About company) → aboutCompany.php
 * - page-shoppingCart.php (Template Name: Cart page) → shoppingCartPage.php
 * - Архив акций (Москва) → arhiv-aktsij-moskva.php
 * 
 * Этот файл — запасной для всех остальных статических страниц.
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
                AS_Kama_Breadcrumbs( ' » ' );
            }
            ?>

            <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            
            <div id="single_page" class="grey_block">
                <h1 class="title"><?php the_title(); ?></h1>
                <article class="top_line">
                    <?php if ( has_post_thumbnail() ) { ?>
                        <div class="image">
                            <?php the_post_thumbnail( '', array( 'class' => '' ) ); ?>
                        </div>
                    <?php } ?>
                    <div class="text">
                        <?php the_content(); ?>
                    </div>
                </article>

            </div>
            
            <?php endwhile; endif; ?>
        </main>

    </div>
</section>


<?php get_footer(); ?>