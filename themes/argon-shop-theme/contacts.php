<?php
/**
 * Template Name: Контакты
 * 
 * Шаблон страницы «Контакты»
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 *  
 * ЧТО ВЫВОДИТ:
 * 1. Хлебные крошки
 * 2. Заголовок и содержимое страницы
 * 3. Карта адреса (часть шаблона 'addressMap')
 * 4. Форма обратной связи (Contact Form 7)
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 * 
 * Этот шаблон использует следующие функции плагина:
 * 
 * - as_kama_breadcrumbs() → хлебные крошки (includes/interface/breadcrumbs.php)
 *  
 * ВНЕШНИЕ ЗАВИСИМОСТИ:
 * - Contact Form 7 — для формы обратной связи (шорткод [contact-form-7])
 * - Часть шаблона 'addressMap' — шаблон карты (addressMap.php в корне темы)
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

            <div class="page pageContacts">

                <h1 class="title titleHistory"><?php esc_html_e( 'Contacts', 'argon-shop-theme' ); ?></h1>
                
                <div class="descr"><?php the_content(); ?></div><!-- .descr -->
                
                <div class="pageMap">
                    
                    <?php 
                    // Часть шаблона 'addressMap' — шаблон карты
                    // Файл должен находиться: /themes/argon-shop-theme/addressMap.php
                    get_template_part( 'addressMap' ); 
                    ?>
                    
                </div>
                
                <div class="pageContactForm">
                    
                    <div class="h2 pageContactForm-title"><?php esc_html_e( 'Feedback', 'argon-shop-theme' ); ?></div>
                    
                    <?php 
                    // Форма обратной связи через Contact Form 7
                    echo do_shortcode( '[contact-form-7 id="7" title="Feedback"]' );
                    ?>
                    
                </div>
                
                
            </div><!-- #production_list_page -->
                
            <?php endwhile; ?>

        </main>

    </div><!-- .row -->
</section><!-- #out -->


<?php get_footer(); ?>