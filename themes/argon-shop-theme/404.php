<?php
/**
 * Шаблон страницы 404 (не найдено)
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Любой несуществующий URL
 * Пример: /no-such-page/
 * 
 * ЧТО ВЫВОДИТ:
 * 1. Хлебные крошки (через kama_breadcrumbs из плагина Argon Shop)
 * 2. Сообщение об ошибке 404
 * 3. Список возможных причин
 * 4. Ссылку на главную страницу
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 * 
 * Этот шаблон использует следующую функцию плагина:
 * 
 * - as_kama_breadcrumbs() → хлебные крошки (includes/interface/breadcrumbs.php)
 * 
 * На странице 404 as_kama_breadcrumbs() выводит:
 * «Главная » Ошибка 404»
 * (см. строку '_404' в локализации класса Kama_Breadcrumbs)
 * 
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
            // ============================================
            // ХЛЕБНЫЕ КРОШКИ
            // ============================================
            // 
            // Функция плагина Argon Shop (includes/interface/breadcrumbs.php)
            // На странице 404 выводит: «Главная » Ошибка 404»
            
            if ( function_exists( 'as_kama_breadcrumbs' ) ) {
                AS_Kama_Breadcrumbs( '<span class="b"> » </span>' );
            }
            ?>
            
            <div class="page">
                <h1 class="title"><?php esc_html_e( 'This page does not exist!', 'argon-shop-theme' ); ?></h1>
                <article class="top_line">
                    <div class="text">
                        
                        <p><?php esc_html_e( 'Unfortunately, the page you requested does not exist on this site.', 'argon-shop-theme' ); ?></p>
                        
                        <p><?php esc_html_e( 'This may have happened for one of these reasons:', 'argon-shop-theme' ); ?></p>
                        
                        <ul>
                            
                            <li><?php esc_html_e( 'you mistyped the page address (URL)', 'argon-shop-theme' ); ?></li>
                            <li><?php esc_html_e( 'you followed a broken (non-working or incorrect) link', 'argon-shop-theme' ); ?></li>
                            <li><?php esc_html_e( 'the requested page never existed on the site or has been deleted', 'argon-shop-theme' ); ?></li>
                            
                        </ul>
                        
                        
                        <p><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( '« to the home page', 'argon-shop-theme' ); ?></a></p>
                    </div>
                </article>

            </div>
        </main>

    </div><!-- .row -->
</section><!-- #out -->


<?php get_footer(); ?>