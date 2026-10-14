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
 * Controls the interactive elements of the gallery meta box:
 *
 *   1. Adding images via wp.media (the WordPress media
 *      library). Supports multiple selection.
 *
 *   2. Removing an image from the gallery.
 *
 *   3. Drag-and-drop sorting of images via jQuery UI
 *      Sortable. The order of thumbnails in the DOM defines the order
 *      saved in the database.
 *
 * ============================================================
 * DEPENDENCIES
 * ============================================================
 *
 * The file uses:
 *
 *   - jQuery — a global WordPress dependency.
 *     Needed because jQuery UI Sortable and wp.media work
 *     through jQuery. This is a CMS core interface, there is no point
 *     in rewriting it in native JS (the main code is native JS)
 *
 *   - jquery-ui-sortable — from WordPress core.
 *
 *   - wp.media — from WordPress core.
 *
 * The file is wrapped in an IIFE so as not to pollute
 * the global admin scope. It defines no utilities
 * of its own — all work goes through jQuery.
 *
 * ============================================================
 * DOM ELEMENTS
 * ============================================================
 *
 * .ars-gallery                  — meta box container
 * .ars-gallery-list             — thumbnail list
 * .ars-gallery-item             — a single thumbnail
 * .ars-gallery-remove           — remove button
 * .ars-gallery-add              — "Add images" button
 *
 * ============================================================
 * SAVING
 * ============================================================
 *
 * Each thumbnail contains a hidden input:
 *
 *     <input type="hidden" name="slider_imgs[]" value="12">
 *
 * Order of the inputs in the DOM = order of the images.
 * Saving is done by the PHP handler on the save_post_product hook
 * (see includes/admin/galleryMetabox.php).
 *
 * ============================================================
 */

'use strict';

( function ( $ ) {

    /**
     * Interface texts. They come from PHP (wp_localize_script → arsGalleryI18n)
     * and are translated with the plugin .po files; English is the fallback.
     *
     * @type {{ selectImages: string, addToGallery: string, removeImage: string }}
     */
    const i18n = Object.assign( {
        selectImages: 'Select images',
        addToGallery: 'Add to gallery',
        removeImage:  'Remove image',
    }, window.arsGalleryI18n || {} );

    /**
     * Escapes a string for an HTML attribute.
     *
     * @param  {string} value
     * @return {string}
     */
    const escAttr = ( value ) => String( value )
        .replace( /&/g, '&amp;' ).replace( /"/g, '&quot;' )
        .replace( /</g, '&lt;' ).replace( />/g, '&gt;' );

    /**
     * Initializes all gallery meta boxes on the page.
     *
     * There may be several meta boxes on a page if
     * the gallery is used not only for products. But currently there is only
     * one — so we work with the first one.
     *
     * @return {void}
     */
    const initGallery = () => {

        const $gallery = $( '.ars-gallery' );

        if ( ! $gallery.length ) {
            return;
        }

        // Sortable initialization — the parameters are exactly the same as
        // in the original ACF Photo Gallery Field plugin.
        //
        //   containment: 'parent' — the helper does not leave the
        //     list. This is critical for a correct swap with
        //     tolerance: 'pointer'.
        //
        //   placeholder — a separate class without .ars-gallery-item.
        //
        //   disableSelection() — removes text and icon selection
        //     while dragging.
        $gallery.find( '.ars-gallery-list' ).sortable({
            containment: 'parent',
            placeholder: 'ars-gallery-sortable-placeholder',
            tolerance:   'pointer',
        }).disableSelection();

        // Delegated handlers — they also work for new thumbnails
        bindAddImages( $gallery );
        bindRemoveImage( $gallery );
    };

    /**
     * Adding images via the media library.
     *
     * Opens wp.media with multiple: true. After the
     * selection is confirmed, adds each image to the
     * gallery list.
     *
     * @param  {jQuery} $gallery — meta box container
     * @return {void}
     */
    const bindAddImages = ( $gallery ) => {

        $gallery.on( 'click', '.ars-gallery-add', ( event ) => {

            event.preventDefault();

            if ( typeof wp === 'undefined' || ! wp.media ) {
                return;
            }

            const frame = wp.media({
                title:    i18n.selectImages,
                button:   { text: i18n.addToGallery },
                library:  { type: 'image' },
                multiple: true,
            });

            frame.on( 'select', () => {

                const selection = frame.state().get( 'selection' );

                selection.each( ( attachment ) => {

                    const data = attachment.toJSON();

                    addImage( $gallery, data );
                });
            });

            frame.open();
        });
    };

    /**
     * Removing an image from the gallery.
     *
     * @param  {jQuery} $gallery — meta box container
     * @return {void}
     */
    const bindRemoveImage = ( $gallery ) => {

        $gallery.on( 'click', '.ars-gallery-remove', ( event ) => {

            event.preventDefault();

            const $item = $( event.currentTarget ).closest( '.ars-gallery-item' );

            if ( $item.length ) {
                $item.remove();
            }
        });
    };

    /**
     * Adds one thumbnail to the gallery list.
     *
     * If an image with this ID is already there — do not add it again.
     *
     * @param  {jQuery} $gallery — meta box container
     * @param  {Object} data     — attachment data from wp.media
     * @return {void}
     */
    const addImage = ( $gallery, data ) => {

        if ( ! data || ! data.id ) {
            return;
        }

        const id = parseInt( data.id, 10 );

        if ( ! id ) {
            return;
        }

        // Duplicate check
        const exists = $gallery
            .find( '.ars-gallery-item[data-id="' + id + '"]' )
            .length > 0;

        if ( exists ) {
            return;
        }

        // Thumbnail URL. Use sizes.thumbnail if it exists,
        // otherwise just the attachment url.
        let thumbUrl = data.url || '';

        if ( data.sizes && data.sizes.thumbnail && data.sizes.thumbnail.url ) {
            thumbUrl = data.sizes.thumbnail.url;
        }

        const $list = $gallery.find( '.ars-gallery-list' );

        const html =
            '<li class="ars-gallery-item" data-id="' + id + '">' +
                '<input type="hidden" name="slider_imgs[]" value="' + id + '" />' +
                '<img src="' + thumbUrl + '" alt="" />' +
                '<button type="button" class="ars-gallery-remove" title="' + escAttr( i18n.removeImage ) + '">' +
                    '<span class="dashicons dashicons-dismiss"></span>' +
                '</button>' +
            '</li>';

        $list.append( html );

        // Refresh the sortable state so that the placeholder
        // works correctly with the new elements.
        if ( $list.hasClass( 'ui-sortable' ) ) {
            $list.sortable( 'refresh' );
        }
    };

    // ============================================================
    // START
    // ============================================================

    $( document ).ready( () => {
        initGallery();
    });

})( jQuery );