<?php
/**
 * Шаблон статической страницы (page)
 *
 * @package ArgonShopTheme
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Любая статическая страница WordPress
 * URL: /название-страницы/
 * 
 * ЧТО ВЫВОДИТ:
 * 1. Хлебные крошки (через kama_breadcrumbs из плагина Argon Shop)
 * 2. Заголовок страницы
 * 3. Изображение страницы (если есть)
 * 4. Контент страницы
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 * 
 * В этом шаблоне используется функция плагина:
 * 
 * - kama_breadcrumbs( ' » ' )
 *   Файл: includes/interface/breadcrumbs.php
 *   Выводит цепочку вида: "Главная » Название страницы"
 *   Для вложенных страниц: "Главная » Родитель » Дочерняя"
 * 
 * ============================================================
 * ШАБЛОНЫ, ПЕРЕОПРЕДЕЛЯЮЩИЕ ЭТОТ ФАЙЛ
 * ============================================================
 * 
 * Некоторые страницы имеют собственные шаблоны и не используют page.php:
 * 
 * - page-history.php (или Template Name: История просмотров) → history.php
 * - page-contacts.php (Template Name: Контакты) → Контакты
 * - page-about.php (Template Name: О компании) → aboutCompany.php
 * - page-shoppingCart.php (Template Name: shoppingCart) → shoppingCartPage.php
 * - Архив акций Москва → arhiv-aktsij-moskva.php
 * 
 * Этот файл — fallback для всех остальных статических страниц.
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
                kama_breadcrumbs( ' » ' );
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