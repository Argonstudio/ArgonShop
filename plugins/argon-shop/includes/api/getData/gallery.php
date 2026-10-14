<?php
/**
 * ArgonShop — getting the product image gallery.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * Reads the list of image IDs from the product meta field and
 * turns it into an array of objects with ready URLs, sizes
 * and captions for use in theme templates and sliders.
 *
 * ============================================================
 * STORAGE FORMAT
 * ============================================================
 *
 * Meta field: slider_imgs
 * Value:      a string of comma-separated image IDs, for example
 *            "12,15,18,22"
 *
 * This format is compatible with the old ACF Photo Gallery plugin,
 * which used the same field name and the same format. This
 * avoids a data migration: existing galleries are read
 * as is, and the new meta box writes to the same place.
 *
 * ============================================================
 * USAGE
 * ============================================================
 *
 * Get a product gallery with ready URLs:
 *
 *     $gallery = as_get_product_gallery( $product_id );
 *
 *     foreach ( $gallery as $image ) {
 *         echo '<img src="' . esc_url( $image['thumbnail_image_url'] ) . '">';
 *     }
 *
 * Get only the list of IDs:
 *
 *     $ids = as_get_product_gallery_ids( $product_id );
 *
 * ============================================================
 * RETURNED FIELDS
 * ============================================================
 *
 * id                     — attachment ID in the media library
 * title                  — attachment title
 * caption                — attachment caption
 * alt                    — alternative text
 * full_image_url         — full-size URL
 * full_image_width       — full image width
 * full_image_height      — full image height
 * thumbnail_image_url    — thumbnail URL
 * media_details          — array [ width, height ]
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Gets the list of gallery image IDs of a product.
 *
 * Reads the slider_imgs meta field and returns a cleaned array
 * of integers. If the field is missing or empty — returns
 * an empty array.
 *
 * @param  int $product_id — product ID
 * @return int[] Array of attachment IDs
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
 * Gets the product image gallery with data ready for output.
 *
 * Returns an array of objects (associative arrays), each
 * of which contains everything needed to render the image
 * in a template or slider.
 *
 * @param  int $product_id — product ID
 * @return array[] Array of images
 */
function as_get_product_gallery( $product_id ) {

    $ids = as_get_product_gallery_ids( $product_id );

    if ( empty( $ids ) ) {
        return array();
    }

    $gallery = array();

    foreach ( $ids as $image_id ) {

        $full = wp_get_attachment_image_src( $image_id, 'full' );

        // Skip attachments that no longer exist
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