<?php
/**
 * ArgonShop — вывод слайдера изображений товара.
 *
 * @package ArgonShop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Функция выводит HTML-структуру слайдера изображений для страницы
 * товара. Состоит из двух связанных Swiper-слайдеров:
 *
 *   1. .as-slider-product-main  — большие изображения.
 *   2. .as-slider-product-thumbs — миниатюры под основным.
 *
 * Слайдеры синхронизированы: клик по миниатюре переключает
 * основной слайдер, а переключение основного выделяет активную
 * миниатюру. Связка настраивается в JS-модуле slider-product.js.
 *
 * ============================================================
 * ГДЕ ИСПОЛЬЗУЕТСЯ
 * ============================================================
 *
 * В шаблоне single-product.php темы:
 *
 *     <?php as_slider_product( get_the_ID() ); ?>
 *
 * Если у товара нет изображений — функция ничего не выводит.
 *
 * ============================================================
 * ДАННЫЕ
 * ============================================================
 *
 * Мета-поле товара:
 *   slider_imgs — строка с ID изображений через запятую.
 *                 Например: "12,15,18,22".
 *
 * ============================================================
 * ПАРАМЕТРЫ
 * ============================================================
 *
 * @param int $product_id — ID товара
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Выводит HTML слайдера изображений товара.
 *
 * @param  int $product_id — ID товара
 * @return void
 */
function as_slider_product( $product_id ) {

    $product_id = absint( $product_id );

    if ( 0 === $product_id ) {
        return;
    }

    // Список ID изображений галереи товара.
    // См. includes/api/getData/gallery.php.
    if ( ! function_exists( 'as_get_product_gallery_ids' ) ) {
        return;
    }

    $img_ids = as_get_product_gallery_ids( $product_id );

    if ( empty( $img_ids ) ) {
        return;
    }

    ?>

    <div class="as-slider as-slider-product">

        <!-- Основной слайдер: большие изображения -->
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

        <!-- Миниатюры с боковыми стрелками навигации -->
        <div class="as-slider-product-thumbs-stage">

            <button type="button"
                    class="as-slider-product-thumbs-prev"
                    aria-label="Предыдущие миниатюры">

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
                    aria-label="Следующие миниатюры">

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