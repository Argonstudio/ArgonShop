<?php 
/**
 * Шаблон страницы кабинета покупателя (CabinetPage)
 * 
 * Template Name: Страница кабинета
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * 
 * ЧТО ВЫВОДИТ:
 * 1. Хлебные крошки (через kama_breadcrumbs из плагина Argon Shop)
 * 3. Три части: Кабинет и адрес / Настройки / Заказы
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 * 
 * Этот шаблон — обёртка для трёх частей кабинета покупателя.
 * Основная работа с данными пользователя происходит внутри них.
 * 
 * ФУНКЦИИ ПЛАГИНА, ИСПОЛЬЗУЕМЫЕ В ШАБЛОНЕ:
 * 
 * 1. as_kama_breadcrumbs( ' » ' )
 *    File: includes/interface/breadcrumbs.php
 *    Выводит хлебные крошки: Главная » Мой кабинет
 * 
 * 2. as_get_cabinetPageID()
 *    File: includes/api/getSetting.php
 *    Используется файлом redirectUser.php плагина для защиты страницы
 *    от неавторизованных пользователей (см. ниже).
 * 
 * ПОДКЛЮЧАЕМЫЕ ЧАСТИ ШАБЛОНА (get_template_part):
 * 
 * 1. template-parts/cabinet/account 
 * 2. template-parts/cabinet/setting 
 * 3. template-parts/cabinet/ordering
 * 
 * ЗАЩИТА СТРАНИЦЫ ОТ ГОСТЕЙ:
 * 
 * Файл includes/interface/cabinet/redirectUser.php содержит
 * хук template_redirect, который проверяет:
 * 
 *   if ( ! is_user_logged_in() && is_page( as_get_cabinetPageID() ) ) {
 *       wp_safe_redirect( wp_login_url() );
 *       exit;
 *   }
 * 
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
                AS_Kama_Breadcrumbs( ' » ' );
            }
            ?> 

            <h1 class="title"><?php the_title(); ?></h1>
            
            <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            
            <div id="product_page" class="white_block">
                
                <div class="pageControlPanel" id="cabinetControlPanel">
                    
                    <div class="itemControlPanel itemControlPanelActive" data-type="switch" name="cabinetAccount"><?php esc_html_e( 'Account and address', 'argon-shop-theme' ); ?></div>
                    <div class="itemControlPanel" data-type="switch" name="cabinetSetting"><?php esc_html_e( 'Settings', 'argon-shop-theme' ); ?></div>
                    <div class="itemControlPanel" data-type="switch" name="cabinetOrdering"><?php esc_html_e( 'Orders', 'argon-shop-theme' ); ?></div>
                    
                </div>
                
                <div class="mobileItemControlPanel" data-mobileWidth="720" data-type="openingList" name="cabinetAccount"><?php esc_html_e( 'Account and address', 'argon-shop-theme' ); ?></div>
                
                <div class="blockItemPage" id="block_cabinetAccount">
                    
                    <?php get_template_part( 'template-parts/cabinet/account' ); ?>
                    
                </div>
                
                <div class="mobileItemControlPanel" data-mobileWidth="720" data-type="openingList" name="cabinetSetting"><?php esc_html_e( 'Settings', 'argon-shop-theme' ); ?></div>
                
                <div class="blockItemPage" id="block_cabinetSetting">
                    
                    <?php get_template_part( 'template-parts/cabinet/setting' ); ?>
                    
                </div>
                
                <div class="mobileItemControlPanel" data-mobileWidth="720" data-type="openingList" name="cabinetOrdering"><?php esc_html_e( 'Orders', 'argon-shop-theme' ); ?></div>
                
                <div class="blockItemPage" id="block_cabinetOrdering">
                    
                    <?php get_template_part( 'template-parts/cabinet/ordering' ); ?>
                    
                </div>
				
            </div>
            
            <?php endwhile; endif; ?>
           
        </main>
        
    </div>
</section>

<?php get_footer(); ?>