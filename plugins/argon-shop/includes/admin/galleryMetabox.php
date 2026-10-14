<?php
/**
 * ArgonShop — "Gallery" meta box on the product page.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * Adds a meta box with the product image gallery. Replaces
 * the third-party ACF Photo Gallery Field plugin, built into
 * ArgonShop without depending on ACF.
 *
 * ============================================================
 * STORAGE FORMAT
 * ============================================================
 *
 * Meta field: slider_imgs
 * Value:      a string of comma-separated image IDs,
 *            for example "12,15,18,22".
 *
 * The format is compatible with the old ACF Photo Gallery plugin —
 * data from existing galleries is read without migration.
 *
 * ============================================================
 * SAVING MECHANICS
 * ============================================================
 *
 * Each image in the list comes with a hidden input:
 *
 *     <input type="hidden" name="slider_imgs[]" value="12">
 *
 * When the post is saved, WordPress passes the array of IDs in $_POST.
 * The save_post_gallery() handler collects it, cleans it and
 * saves it to the meta field as a single string.
 *
 * Image order in the form = order of the inputs in the DOM.
 * The user changes the order with drag-and-drop
 * (jQuery UI Sortable from WordPress core), the DOM is rebuilt,
 * and the order is stored on save.
 *
 * ============================================================
 * LOADING
 * ============================================================
 *
 * The file is loaded in argon-shop.php via require_once.
 * Meta box registration — the add_meta_boxes hook.
 * Save handler — the save_post_product hook.
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ============================================================
// META BOX REGISTRATION
// ============================================================

add_action( 'add_meta_boxes', 'ars_gallery_register_metabox' );

/**
 * Registers the "Gallery" meta box on the product page.
 *
 * @return void
 */
function ars_gallery_register_metabox() {

    add_meta_box(
        'ars_gallery_metabox',
        __( 'Gallery', 'argon-shop' ),
        'ars_gallery_render_metabox',
        'product',
        'normal',
        'default'
    );
}

// ============================================================
// META BOX OUTPUT
// ============================================================

/**
 * Outputs the gallery meta box HTML.
 *
 * @param  WP_Post $post — product being edited
 * @return void
 */
function ars_gallery_render_metabox( $post ) {

    // Nonce for the check on save
    wp_nonce_field( 'ars_gallery_save', 'ars_gallery_nonce' );

    // List of image IDs (compatible with the old format)
    $ids = function_exists( 'as_get_product_gallery_ids' )
        ? as_get_product_gallery_ids( $post->ID )
        : array();

    ?>

    <div class="ars-gallery" data-postid="<?php echo esc_attr( $post->ID ); ?>">

        <ul class="ars-gallery-list">

            <?php foreach ( $ids as $image_id ) : ?>

                <?php
                $thumb_url = wp_get_attachment_thumb_url( $image_id );

                if ( ! $thumb_url ) {
                    continue;
                }
                ?>

                <li class="ars-gallery-item" data-id="<?php echo esc_attr( $image_id ); ?>">

                    <input type="hidden"
                           name="slider_imgs[]"
                           value="<?php echo esc_attr( $image_id ); ?>" />

                    <img src="<?php echo esc_url( $thumb_url ); ?>"
                         alt="" />

                    <button type="button"
                            class="ars-gallery-remove"
                            title="<?php esc_attr_e( 'Remove image', 'argon-shop' ); ?>">
                        <span class="dashicons dashicons-dismiss"></span>
                    </button>

                </li>

            <?php endforeach; ?>

        </ul>

        <button type="button"
                class="button button-primary button-large ars-gallery-add">
            <?php esc_html_e( 'Add images', 'argon-shop' ); ?>
        </button>

        <p class="ars-gallery-hint">
            <?php esc_html_e( 'Drag images with the mouse to change the order. The first image is the main one and is shown first.', 'argon-shop' ); ?>
        </p>

    </div>

    <?php
}

// ============================================================
// SAVING
// ============================================================

add_action( 'save_post_product', 'ars_gallery_save_metabox' );

/**
 * Saves the gallery image list when the product is saved.
 *
 * @param  int $post_id — product ID
 * @return void
 */
function ars_gallery_save_metabox( $post_id ) {

    // Nonce check
    if ( ! isset( $_POST['ars_gallery_nonce'] )
        || ! wp_verify_nonce( $_POST['ars_gallery_nonce'], 'ars_gallery_save' ) ) {
        return;
    }

    // Skip autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Skip revisions
    if ( wp_is_post_revision( $post_id ) ) {
        return;
    }

    // Capability check
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Read the array of IDs from the form
    if ( ! isset( $_POST['slider_imgs'] ) || ! is_array( $_POST['slider_imgs'] ) ) {
        delete_post_meta( $post_id, 'slider_imgs' );
        return;
    }

    // Clean: absint every item, drop zeros and duplicates
    $ids = array_map( 'absint', wp_unslash( $_POST['slider_imgs'] ) );
    $ids = array_filter( $ids );
    $ids = array_unique( $ids );
    $ids = array_values( $ids );

    if ( empty( $ids ) ) {
        delete_post_meta( $post_id, 'slider_imgs' );
        return;
    }

    // Save as a comma-separated string — compatible with the old format
    update_post_meta( $post_id, 'slider_imgs', implode( ',', $ids ) );
}