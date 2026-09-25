<?php
/**
 * Шаблон одиночной записи (single)
 *
 * @package ArgonShopTheme
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Страницы отдельных записей (постов)
 * URL: /название-записи/
 * 
 * ЧТО ВЫВОДИТ:
 * 1. Хлебные крошки (через kama_breadcrumbs из плагина Argon Shop)
 * 2. Заголовок записи
 * 3. Изображение записи (если есть)
 * 4. Контент записи
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 * 
 * В этом шаблоне используется функция плагина:
 * 
 * - kama_breadcrumbs() → хлебные крошки (includes/interface/breadcrumbs.php)
 * 
 * Для одиночной записи плагин автоматически строит цепочку:
 * "Главная » [Рубрика] » [Заголовок записи]"
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header(); ?>

<section id="out">
    <div class="row">
        
        <?php get_sidebar(); ?>
        
        <main class="block-content block-contentPage block-entryPage">
            
            <?php 
            // Функция плагина Argon Shop (includes/interface/breadcrumbs.php)
            if ( function_exists( 'kama_breadcrumbs' ) ) {
                kama_breadcrumbs( ' » ' );
            }
            ?>
            
            <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            
            <h1 class="title"><?php the_title(); ?></h1>
            
            <div class="block-entryContent">
                
                <?php if ( has_post_thumbnail() ) { ?>
                    <div class="block-entryImage">
                        <?php the_post_thumbnail( '', array( 'class' => '' ) ); ?>
                    </div>
                <?php } ?>
                
                <div class="block-entryText">
                    <?php the_content(); ?>
                </div>
                
            </div>
            
            
            <?php endwhile; endif; ?>
        </main>

    </div>
</section>


<?php get_footer(); ?>