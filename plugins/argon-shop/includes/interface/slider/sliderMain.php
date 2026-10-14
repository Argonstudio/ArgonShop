<?php
/**
 * ArgonShop — home page slider output.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * The function outputs the HTML structure of the Swiper slider. Used
 * in the theme's home page template (index.php)
 *
 * The HTML structure is Swiper-compatible and contains:
 *   - the .as-slider-main container with the .swiper class;
 *   - the .swiper-wrapper wrapper;
 *   - .swiper-slide slides;
 *   - pagination and navigation arrows.
 *
 * Swiper is initialized by the slider-main.js JS module
 * of the plugin (see assets/js/interface/modules/slider-main.js).
 *
 * ============================================================
 * WHERE IT IS USED
 * ============================================================
 *
 * In the theme's index.php template:
 *
 *     <?php as_slider_main(); ?>
 *
 * If there are no slides — the function outputs nothing.
 *
 * ============================================================
 * DATA
 * ============================================================
 *
 * Slides are posts of the 'slider' type (registered in createPostType.php).
 * Meta field:
 *   slide_link — URL the slide click leads to (optional).
 *
 * Image size: slider_thumb (registered in the theme's functions.php).
 *
 * ============================================================
 * PARAMETERS
 * ============================================================
 *
 * @param array $args {
 *     WP_Query arguments. Default:
 *
 *     @type string $post_type      'slider'
 *     @type string $post_status    'publish'
 *     @type int    $posts_per_page -1 (all slides)
 * }
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Outputs the home page slider HTML.
 *
 * @param  array $args — WP_Query arguments
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