<?php 
/**
 * Шаблон страницы личного кабинета (CabinetPage)
 * 
 * Template Name: CabinetPage
 *
 * @package ArgonShopTheme
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * 
 * ЧТО ВЫВОДИТ:
 * 1. Хлебные крошки (через kama_breadcrumbs из плагина Argon Shop)
 * 3. Три части: Аккаунт и адрес / Настройки / Заказы
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 * 
 * Этот шаблон — обёртка для трёх частей личного кабинета.
 * Основная работа с данными пользователя ведётся внутри них.
 * 
 * ФУНКЦИИ ПЛАГИНА, ИСПОЛЬЗУЕМЫЕ В ШАБЛОНЕ:
 * 
 * 1. kama_breadcrumbs( ' » ' )
 *    Файл: includes/interface/breadcrumbs.php
 *    Выводит крошки: Главная » Личный кабинет
 * 
 * 2. get_cabinetPageID()
 *    Файл: includes/api/getSetting.php
 *    Используется плагином redirectUser.php для защиты страницы
 *    от неавторизованных пользователей (см. below).
 * 
 * ПОДКЛЮЧАЕМЫЕ ШАБЛОНЫ ЧАСТЕЙ (get_template_part):
 * 
 * 1. template-parts/cabinet/account 
 * 2. template-parts/cabinet/setting 
 * 3. template-parts/cabinet/ordering
 * 
 * ЗАЩИТА СТРАНИЦЫ ОТ ГОСТЕЙ:
 * 
 * Файл includes/interface/cabinet/redirectUser.php содержит хук
 * template_redirect, который проверяет:
 * 
 *   if ( ! is_user_logged_in() && is_page( get_cabinetPageID() ) ) {
 *       wp_safe_redirect( wp_login_url() );
 *       exit;
 *   }
 * 
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
                kama_breadcrumbs( ' » ' );
            }
            ?> 

            <h1 class="title"><?php the_title(); ?></h1>
            
            <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            
            <div id="product_page" class="white_block">
                
                <div class="pageControlPanel" id="cabinetControlPanel">
                    
                    <div class="itemControlPanel itemControlPanelActive" data-type="switch" name="cabinetAccount">Аккаунт и адрес</div>
                    <div class="itemControlPanel" data-type="switch" name="cabinetSetting">Настройки</div>
                    <div class="itemControlPanel" data-type="switch" name="cabinetOrdering">Заказы</div>
                    
                </div>
                
                <div class="mobileItemControlPanel" data-mobileWidth="720" data-type="openingList" name="cabinetAccount">Аккаунт и адрес</div>
                
                <div class="blockItemPage" id="block_cabinetAccount">
                    
                    <?php get_template_part( 'template-parts/cabinet/account' ); ?>
                    
                </div>
                
                <div class="mobileItemControlPanel" data-mobileWidth="720" data-type="openingList" name="cabinetSetting">Настройки</div>
                
                <div class="blockItemPage" id="block_cabinetSetting">
                    
                    <?php get_template_part( 'template-parts/cabinet/setting' ); ?>
                    
                </div>
                
                <div class="mobileItemControlPanel" data-mobileWidth="720" data-type="openingList" name="cabinetOrdering">Заказы</div>
                
                <div class="blockItemPage" id="block_cabinetOrdering">
                    
                    <?php get_template_part( 'template-parts/cabinet/ordering' ); ?>
                    
                </div>
				
            </div>
            
            <?php endwhile; endif; ?>
           
        </main>
        
    </div>
</section>

<?php get_footer(); ?>