<?php
/**
 * Template Name: О компании
 * 
 * Шаблон страницы "О компании"
 *
 * @package ArgonShopTheme
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ТЕСТИРОВАТЬ:
 * Страница "О компании"
 * URL: /o-kompanii/ (или другая)
 * 
 * ЧТО ВЫВОДИТ:
 * 1. Хлебные крошки (через kama_breadcrumbs из плагина Argon Shop)
 * 2. Заголовок и контент страницы
 * 3. Блок "Наши преимущества" (4 статичные карточки)
 * 4. Текст "О преимуществах" (поле ACF our_advantages)
 * 5. Блок "Наши партнёры" (тип записи ourclients)
 * 6. Текст "О клиентах" (поле ACF our_clients)
 * 7. Блок "Дипломы и сертификаты" (тип записи sertificates)
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
 * - ACF (Advanced Custom Fields) — поля our_advantages, our_clients
 * - Типы записей ourclients / sertificates (регистрируются в functions.php темы)
 * - Мета-поле client_link у постов ourclients
 * - Swiper (из плагина ArgonShop) — слайдер сертификатов
 * - GLightbox (из плагина ArgonShop) — просмотр сертификата в полном размере
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
        
        <main class="block-content block-contentPage block-contentAboutCompany">

            <?php 
            // ============================================
            // ХЛЕБНЫЕ КРОШКИ
            // ============================================
            // 
            // Функция плагина Argon Shop (includes/interface/breadcrumbs.php)
            // Выводит: Главная » О компании
            
            if ( function_exists( 'kama_breadcrumbs' ) ) {
                kama_breadcrumbs( ' » ' );
            }
            ?>

            <?php 
            // Стандартный цикл WordPress
            while ( have_posts() ) : the_post(); 
            ?>

            <div class="page">

                <h1 class="title titlePage"><?php the_title(); ?></h1>
                
                <?php the_content(); ?>
                
                <?php 
                // ============================================
                // ТЕКСТ "О ПРЕИМУЩЕСТВАХ" (поле ACF)
                // ============================================
                // 
                
                $textOurAdvantages = '';
                
                if ( function_exists( 'get_field' ) ) {
                    $field_value = get_field( 'our_advantages', get_the_ID() );
                    
                    if ( $field_value ) {
                        $textOurAdvantages = wpautop( $field_value );
                    }
                }
                
                if ( $textOurAdvantages ) {
                    ?>
                    
                    <div class="text-ourAdvantages">
                        
                        <?php echo $textOurAdvantages; ?>
                        
                    </div>
                
                <?php } ?>
                
                
                <div class="block-ourClients">
                
                    <div class="aboutCompany-block-title">Наши партнеры</div>
                    
                    <div class="block-logoClients">
                    
                        <?php
                        
                        // ============================================
                        // НАШИ ПАРТНЁРЫ (тип записи ourclients)
                        // ============================================
                        
                        $arg = array(
                            'post_status'    => 'publish',
                            'post_type'      => 'ourclients',
                            'posts_per_page' => -1,
                        );
                        
                        $clients = new WP_Query( $arg );
                        
                        if ( $clients instanceof WP_Query && $clients->have_posts() ) :
                            
                            while ( $clients->have_posts() ) : $clients->the_post();
                            
                                $linkClient = get_post_meta( get_the_ID(), 'client_link', true );
                                $linkClient_url = ( ! empty( $linkClient ) && is_string( $linkClient ) ) 
                                    ? esc_url( $linkClient ) 
                                    : '';
                                
                                ?>
                                
                                <div class="block-client">
                                    
                                    <?php if ( $linkClient_url ) : ?>
                                        <a href="<?php echo $linkClient_url; ?>" target="_blank" rel="noopener noreferrer">
                                    <?php endif; ?>
                                    
                                        <span><?php the_post_thumbnail( 'slider_thumb', array( 'class' => 'img-responsive' ) ); ?></span>
                                    
                                    <?php if ( $linkClient_url ) : ?>
                                        </a>
                                    <?php endif; ?>
                                    
                                </div>
                                
                            <?php 
                            
                            endwhile; 
                            
                            wp_reset_postdata(); 
                            
                        endif;  
                        
                        ?>
                        
                    </div>
                    
                </div>
                
                <?php 
                // ============================================
                // ТЕКСТ "О КЛИЕНТАХ" (поле ACF)
                // ============================================
                
                $textOurClients = '';
                
                if ( function_exists( 'get_field' ) ) {
                    $field_value = get_field( 'our_clients', get_the_ID() );
                    
                    if ( $field_value ) {
                        $textOurClients = wpautop( $field_value );
                    }
                }
                
                if ( $textOurClients ) {
                    ?>
                    
                    <div class="text-ourClients">
                        
                        <?php echo $textOurClients; ?>
                        
                    </div>
                
                <?php } ?>
                
                <div class="block-sertificats">

                    <div class="aboutCompany-block-title">Дипломы и сертификаты</div>

                    <div class="block-sliderSertificats">

                        <?php

                        // ============================================
                        // СЕРТИФИКАТЫ (тип записи sertificates)
                        // ============================================
                        //
                        // Разметка Swiper-слайдера. Инициализация —
                        // в assets/js/slider-sertificates.js темы.
                        // Просмотр в полном размере — GLightbox.

                        $arg = array(
                            'post_status'    => 'publish',
                            'post_type'      => 'sertificates',
                            'posts_per_page' => -1,
                        );

                        $sertificates = new WP_Query( $arg );

                        if ( $sertificates instanceof WP_Query && $sertificates->have_posts() ) :
                        ?>

                        <div class="as-slider as-slider-sertificates swiper">

                            <div class="swiper-wrapper">

                                <?php
                                while ( $sertificates->have_posts() ) : $sertificates->the_post();

                                    $imgSerticat     = get_the_post_thumbnail_url( get_the_ID(), 'medium' );
                                    $imgSerticatFull = get_the_post_thumbnail_url( get_the_ID(), 'full' );

                                    if ( ! $imgSerticat || ! $imgSerticatFull ) {
                                        continue;
                                    }
                                    ?>

                                    <div class="swiper-slide">

                                        <a href="<?php echo esc_url( $imgSerticatFull ); ?>"
                                           class="glightbox-sertificate"
                                           data-glightbox="type: image;">

                                            <img src="<?php echo esc_url( $imgSerticat ); ?>"
                                                 alt="<?php echo esc_attr( get_the_title() ); ?>">

                                        </a>

                                    </div>

                                <?php endwhile; ?>

                            </div><!-- .swiper-wrapper -->

                            <div class="swiper-button-prev"></div>
                            <div class="swiper-button-next"></div>

                        </div><!-- .as-slider-sertificates -->

                        <?php
                        wp_reset_postdata();
                        endif;
                        ?>

                    </div>

                </div>
                
            </div><!-- #production_list_page -->
                
            <?php endwhile; ?>

        </main>

    </div><!-- .row -->
</section><!-- #out -->


<?php get_footer(); ?>