<?php
/**
 * ArgonShop — product image slider output.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * The function outputs the HTML structure of the image slider for the product
 * page. It consists of two linked Swiper sliders:
 *
 *   1. .as-slider-product-main  — large images.
 *   2. .as-slider-product-thumbs — thumbnails below the main one.
 *
 * The sliders are synchronized: clicking a thumbnail switches
 * the main slider, and switching the main one highlights the active
 * thumbnail. The link is configured in the slider-product.js JS module.
 *
 * ============================================================
 * WHERE IT IS USED
 * ============================================================
 *
 * In the theme's single-product.php template:
 *
 *     <?php as_slider_product( get_the_ID() ); ?>
 *
 * If the product has no images — the function outputs nothing.
 *
 * ============================================================
 * DATA
 * ============================================================
 *
 * Product meta field:
 *   slider_imgs — a string of comma-separated image IDs.
 *                 For example: "12,15,18,22".
 *
 * ============================================================
 * PARAMETERS
 * ============================================================
 *
 * @param int $product_id — product ID
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Outputs the product image slider HTML.
 *
 * @param  int $product_id — product ID
 * @return void
 */
function as_slider_product( $product_id ) {

    $product_id = absint( $product_id );

    if ( 0 === $product_id ) {
        return;
    }

    // List of product gallery image IDs.
    // See includes/api/getData/gallery.php.
    if ( ! function_exists( 'as_get_product_gallery_ids' ) ) {
        return;
    }

    $img_ids = as_get_product_gallery_ids( $product_id );

    if ( empty( $img_ids ) ) {
        return;
    }

    ?>

    <div class="as-slider as-slider-product">

        <!-- Main slider: large images -->
        <div class="swiper as-slider-product-main">

            <div class="swiper-wrapper">

                <?php
                foreach ( $img_ids as $img_id ) :

                    $image = wp_get_attachment_image_src( $img_id, 'full' );

                    if ( ! $image ) {
                        continue;
                    }

                    $image_url    = $image[0];
                    $image_width  = $image[1];
                    $image_height = $image[2];
                    ?>

                    <div class="swiper-slide">

                        <a href="<?php echo esc_url( $image_url ); ?>"
                           class="glightbox-gallery"
                           data-glightbox="type: image;">

                            <img src="<?php echo esc_url( $image_url ); ?>"
                                 alt=""
                                 width="<?php echo esc_attr( $image_width ); ?>"
                                 height="<?php echo esc_attr( $image_height ); ?>">

                        </a>

                    </div>

                <?php endforeach; ?>

            </div><!-- .swiper-wrapper -->

        </div><!-- .as-slider-product-main -->

        <!-- Thumbnails with side navigation arrows -->
        <div class="as-slider-product-thumbs-stage">

            <button type="button"
                    class="as-slider-product-thumbs-prev"
                    aria-label="<?php esc_attr_e( 'Previous thumbnails', 'argon-shop' ); ?>">

                <svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true">
                    <path d="M15 5 L8 12 L15 19"
                          fill="none"
                          stroke="currentColor"
                          stroke-width="2"
                          stroke-linecap="round"
                          stroke-linejoin="round"/>
                </svg>

            </button>

            <div class="swiper as-slider-product-thumbs">

                <div class="swiper-wrapper">

                    <?php
                    foreach ( $img_ids as $img_id ) :

                        $thumb = wp_get_attachment_image_src( $img_id, 'medium' );

                        if ( ! $thumb ) {
                            continue;
                        }
                        ?>

                        <div class="swiper-slide">

                            <img src="<?php echo esc_url( $thumb[0] ); ?>" alt="">

                        </div>

                    <?php endforeach; ?>

                </div><!-- .swiper-wrapper -->

            </div><!-- .as-slider-product-thumbs -->

            <button type="button"
                    class="as-slider-product-thumbs-next"
                    aria-label="<?php esc_attr_e( 'Next thumbnails', 'argon-shop' ); ?>">

                <svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true">
                    <path d="M9 5 L16 12 L9 19"
                          fill="none"
                          stroke="currentColor"
                          stroke-width="2"
                          stroke-linecap="round"
                          stroke-linejoin="round"/>
                </svg>

            </button>

        </div><!-- .as-slider-product-thumbs-stage -->

    </div><!-- .as-slider-product -->

    <?php
}