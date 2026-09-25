<?php
/**
 * ArgonShop — вывод слайдера на главной странице.
 *
 * @package ArgonShop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Функция выводит HTML-структуру слайдера для Swiper. Используется
 * в шаблоне главной страницы темы (index.php)
 *
 * Структура HTML совместима со Swiper и содержит:
 *   - контейнер .as-slider-main с классом .swiper;
 *   - обёртку .swiper-wrapper;
 *   - слайды .swiper-slide;
 *   - пагинацию и стрелки навигации.
 *
 * Инициализация Swiper выполняется JS-модулем slider-main.js
 * из плагина (см. assets/interface/js/modules/slider-main.js).
 *
 * ============================================================
 * ГДЕ ИСПОЛЬЗУЕТСЯ
 * ============================================================
 *
 * В шаблоне index.php темы:
 *
 *     <?php as_slider_main(); ?>
 *
 * Если слайдов нет — функция ничего не выводит.
 *
 * ============================================================
 * ДАННЫЕ
 * ============================================================
 *
 * Слайды — это записи типа 'slider' (регистрируется в createPostType.php).
 * Мета-поле:
 *   slide_link — URL, куда ведёт клик по слайду (опционально).
 *
 * Размер изображения: slider_thumb (регистрируется в functions.php темы).
 *
 * ============================================================
 * ПАРАМЕТРЫ
 * ============================================================
 *
 * @param array $args {
 *     Аргументы WP_Query. По умолчанию:
 *
 *     @type string $post_type      'slider'
 *     @type string $post_status    'publish'
 *     @type int    $posts_per_page -1 (все слайды)
 * }
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Выводит HTML слайдера главной страницы.
 *
 * @param  array $args — аргументы WP_Query
 * @return void
 */
function as_slider_main( $args = array() ) {

    $defaults = array(
        'post_type'      => 'slider',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
    );

    $args = wp_parse_args( $args, $defaults );

    $slides = new WP_Query( $args );

    if ( ! $slides instanceof WP_Query || ! $slides->have_posts() ) {
        return;
    }

    ?>

    <div class="as-slider as-slider-main swiper">

        <div class="swiper-wrapper">

            <?php
            while ( $slides->have_posts() ) :

                $slides->the_post();

                $link = get_post_meta( get_the_ID(), 'slide_link', true );

                $link_url = ( ! empty( $link ) && is_string( $link ) )
                    ? esc_url( $link )
                    : '';
                ?>

                <div class="swiper-slide">

                    <?php if ( $link_url ) : ?>
                        <a href="<?php echo $link_url; ?>">
                    <?php endif; ?>

                    <?php the_post_thumbnail( 'slider_thumb', array( 'class' => 'img-responsive' ) ); ?>

                    <?php if ( $link_url ) : ?>
                        </a>
                    <?php endif; ?>

                </div>

            <?php endwhile; ?>

        </div><!-- .swiper-wrapper -->

        <div class="swiper-pagination"></div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>

    </div><!-- .as-slider-main -->

    <?php

    wp_reset_postdata();
}