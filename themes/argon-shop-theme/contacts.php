<?php
/**
 * Template Name: Контакты
 * 
 * Шаблон страницы "Контакты"
 *
 * @package ArgonShopTheme
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0 
 *  
 * ЧТО ВЫВОДИТ:
 * 1. Хлебные крошки
 * 2. Заголовок и контент страницы
 * 3. Карту адреса (template part 'addressMap')
 * 4. Форму обратной связи (Contact Form 7)
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 * 
 * В этом шаблоне используются функции плагина:
 * 
 * - kama_breadcrumbs() → хлебные крошки (includes/interface/breadcrumbs.php)
 *  
 * ВНЕШНИЕ ЗАВИСИМОСТИ:
 * - Contact Form 7 — для формы обратной связи (шорткод [contact-form-7])
 * - Template part 'addressMap' — шаблон карты (файл addressMap.php в корне темы)
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

            <div class="page pageContacts">

                <h1 class="title titleHistory">Контакты</h1>
                
                <div class="descr"><?php the_content(); ?></div><!-- .descr -->
                
                <div class="pageMap">
                    
                    <?php 
                    // Template part 'addressMap' — шаблон карты
                    // Файл должен быть: /themes/argon-shop-theme/addressMap.php
                    get_template_part( 'addressMap' ); 
                    ?>
                    
                </div>
                
                <div class="pageContactForm">
                    
                    <div class="h2 pageContactForm-title">Обратная связь</div>
                    
                    <?php 
                    // Форма обратной связи через Contact Form 7
                    echo do_shortcode( '[contact-form-7 id="7" title="Обратная связь"]' ); 
                    ?>
                    
                </div>
                
                
            </div><!-- #production_list_page -->
                
            <?php endwhile; ?>

        </main>

    </div><!-- .row -->
</section><!-- #out -->


<?php get_footer(); ?>