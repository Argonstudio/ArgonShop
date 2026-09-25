<?php
/**
 * ArgonShop — получение галереи изображений товара.
 *
 * @package ArgonShop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Читает список ID изображений из мета-поля товара и
 * преобразует их в массив объектов с готовыми URL, размерами
 * и подписями для использования в шаблонах темы и слайдерах.
 *
 * ============================================================
 * ФОРМАТ ХРАНЕНИЯ
 * ============================================================
 *
 * Мета-поле: slider_imgs
 * Значение:  строка с ID изображений через запятую, например
 *            "12,15,18,22"
 *
 * Такой формат совместим со старым плагином ACF Photo Gallery,
 * который использовал то же имя поля и тот же формат. Это
 * позволяет обойтись без миграции данных: существующие галереи
 * читаются как есть, а новый метабокс пишет туда же.
 *
 * ============================================================
 * ИСПОЛЬЗОВАНИЕ
 * ============================================================
 *
 * Получить галерею товара с готовыми URL:
 *
 *     $gallery = as_get_product_gallery( $product_id );
 *
 *     foreach ( $gallery as $image ) {
 *         echo '<img src="' . esc_url( $image['thumbnail_image_url'] ) . '">';
 *     }
 *
 * Получить только список ID:
 *
 *     $ids = as_get_product_gallery_ids( $product_id );
 *
 * ============================================================
 * ВОЗВРАЩАЕМЫЕ ПОЛЯ
 * ============================================================
 *
 * id                     — ID вложения в медиабиблиотеке
 * title                  — заголовок вложения
 * caption                — подпись вложения
 * alt                    — альтернативный текст
 * full_image_url         — URL полного размера
 * full_image_width       — ширина полного изображения
 * full_image_height      — высота полного изображения
 * thumbnail_image_url    — URL миниатюры
 * media_details          — массив [ width, height ]
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Получает список ID изображений галереи товара.
 *
 * Читает мета-поле slider_imgs и возвращает очищенный массив
 * целых чисел. Если поля нет или оно пустое — возвращает
 * пустой массив.
 *
 * @param  int $product_id — ID товара
 * @return int[] Массив ID вложений
 */
function as_get_product_gallery_ids( $product_id ) {

    $product_id = absint( $product_id );

    if ( 0 === $product_id ) {
        return array();
    }

    $raw = get_post_meta( $product_id, 'slider_imgs', true );

    if ( empty( $raw ) || ! is_string( $raw ) ) {
        return array();
    }

    $ids = explode( ',', $raw );
    $ids = array_map( 'absint', $ids );
    $ids = array_filter( $ids );
    $ids = array_values( $ids );

    return $ids;
}

/**
 * Получает галерею изображений товара с готовыми данными для вывода.
 *
 * Возвращает массив объектов (ассоциативных массивов), каждый
 * из которых содержит всё, что нужно для отрисовки изображения
 * в шаблоне или слайдере.
 *
 * @param  int $product_id — ID товара
 * @return array[] Массив изображений
 */
function as_get_product_gallery( $product_id ) {

    $ids = as_get_product_gallery_ids( $product_id );

    if ( empty( $ids ) ) {
        return array();
    }

    $gallery = array();

    foreach ( $ids as $image_id ) {

        $full = wp_get_attachment_image_src( $image_id, 'full' );

        // Пропускаем вложения, которые больше не существуют
        if ( ! $full ) {
            continue;
        }

        $thumb = wp_get_attachment_image_src( $image_id, 'thumbnail' );

        $gallery[] = array(
            'id'                  => $image_id,
            'title'               => get_the_title( $image_id ),
            'caption'             => wp_get_attachment_caption( $image_id ),
            'alt'                 => get_post_meta( $image_id, '_wp_attachment_image_alt', true ),
            'full_image_url'      => $full[0],
            'full_image_width'    => $full[1],
            'full_image_height'   => $full[2],
            'thumbnail_image_url' => $thumb ? $thumb[0] : $full[0],
            'media_details'       => array(
                'width'  => $full[1],
                'height' => $full[2],
            ),
        );
    }

    return $gallery;
}