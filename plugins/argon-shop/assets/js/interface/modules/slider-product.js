/**
 * ArgonShop — product image slider.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * Initializes two linked Swiper sliders on the product page
 * and GLightbox for viewing images in full size.
 * Replaces the old Slick slider and Fancybox.
 *
 * The HTML markup is printed by the PHP function as_slider_product()
 * (see includes/interface/slider/sliderProduct.php).
 *
 * ============================================================
 * DOM ELEMENTS
 * ============================================================
 *
 * .as-slider-product
 *   Common product slider container.
 *
 * .as-slider-product-main
 *   Main Swiper with large images. Slides contain
 *   links with the .glightbox-gallery class and the data-glightbox attribute.
 *
 * .as-slider-product-thumbs-stage
 *   Thumbnail wrapper with navigation arrows.
 *
 * .as-slider-product-thumbs
 *   Swiper with thumbnails. Serves as a visual strip: shows
 *   3 thumbnails at a time and highlights the active one.
 *
 * .as-slider-product-thumbs-prev / -next
 *   Thumbnail navigation buttons. Shown if there are more
 *   thumbnails than fit in the visible area.
 *
 * .as-slider-product-thumb-active
 *   Active thumbnail class. Set manually from JS, because
 *   the built-in .swiper-slide-active is not suitable for thumbnails:
 *   with slidesPerView: 3 it always sits on the left slide and does not
 *   reflect the real active index.
 *
 * ============================================================
 * SOURCE OF TRUTH — THE MAIN SLIDER
 * ============================================================
 *
 * The main slider has one slide per image,
 * its activeIndex is always in the range 0..N-1. Thumbnails, however,
 * show 3 slides at a time and only have snap positions
 * 0, 1, 2 — the thumbnails' activeIndex CANNOT be 3 or 4 with 5
 * slides and slidesPerView: 3. That is why the built-in
 * slideToClickedSlide option does not work for thumbnails beyond the third
 * image.
 *
 * Therefore the whole active index lives in the main slider:
 *
 *   - arrow click → mainSwiper.slideNext() / slidePrev();
 *   - thumbnail click → mainSwiper.slideTo(index);
 *   - when main's activeIndex changes:
 *       1) update the active thumbnail highlight (our class),
 *       2) scroll thumbsSwiper to the required index
 *          so that the thumbnail is visible on screen,
 *       3) update the arrow state (disabled at the edges).
 *
 * ============================================================
 * USAGE
 * ============================================================
 *
 *     import { arsSliderProduct } from './modules/slider-product.js';
 *     arsSliderProduct.init();
 *
 * The module is safe to call on any page: if there is no slider,
 * nothing is executed.
 */

'use strict';

import { qs, qsa } from '../../shared/dom.js';

/**
 * Product slider module.
 */
class ArsSliderProduct {

    /** @type {boolean} */
    #initialized = false;

    /**
     * Main Swiper instance.
     *
     * @type {Object|null}
     */
    #mainSwiper = null;

    /**
     * Thumbnail Swiper instance.
     *
     * @type {Object|null}
     */
    #thumbsSwiper = null;

    /**
     * GLightbox instance for the gallery.
     *
     * @type {Object|null}
     */
    #glightbox = null;

    /**
     * Thumbnail wrapper with arrows.
     *
     * @type {Element|null}
     */
    #stage = null;

    /**
     * "Previous" button.
     *
     * @type {Element|null}
     */
    #prevButton = null;

    /**
     * "Next" button.
     *
     * @type {Element|null}
     */
    #nextButton = null;

    /**
     * Array of thumbnail slides (real DOM elements).
     * Needed for manual highlighting of the active thumbnail.
     *
     * @type {Element[]}
     */
    #thumbSlides = [];

    /**
     * How many thumbnail slides are visible at once.
     *
     * @type {number}
     */
    #thumbsPerView = 3;

    /**
     * Initialization. Safe to call repeatedly.
     *
     * @return {void}
     */
    init() {

        if ( this.#initialized ) {
            return;
        }

        const container = qs( '.as-slider-product' );

        if ( ! container ) {
            return;
        }

        if ( typeof Swiper !== 'function' ) {
            return;
        }

        this.#initialized = true;

        this.#stage      = container.querySelector( '.as-slider-product-thumbs-stage' );
        this.#prevButton = container.querySelector( '.as-slider-product-thumbs-prev' );
        this.#nextButton = container.querySelector( '.as-slider-product-thumbs-next' );

        // Main slider first — it is the source of truth.
        this.#initMain( container );

        // Then the thumbnails linked to the main slider.
        this.#initThumbs( container );

        // GLightbox for the gallery.
        this.#initLightbox( container );

        // Arrow and thumbnail click handlers.
        this.#bindArrows();
        this.#bindThumbClicks();

        // Initial highlight, arrow state, thumbnail position.
        this.#refreshActiveThumb();
        this.#refreshNavState();
    }

    /**
     * Initializes the main Swiper.
     *
     * @param  {Element} container — common slider container
     * @return {void}
     */
    #initMain( container ) {

        const main = container.querySelector( '.as-slider-product-main' );

        if ( ! main ) {
            return;
        }

        this.#mainSwiper = new Swiper( main, {

            slidesPerView: 1,
            slidesPerScroll: 1,
            spaceBetween: 0,
            loop: false,

            a11y: {
                enabled: true,
            },
        });

        // When the active slide of the main slider changes,
        // update the thumbnail highlight, scrolling and arrows.
        this.#mainSwiper.on( 'activeIndexChange', () => {
            this.#refreshActiveThumb();
            this.#scrollThumbsToActive();
            this.#refreshNavState();
        });

        // In case activeIndex did not change
        // but the edge did (resize, etc.).
        this.#mainSwiper.on( 'reachBeginning reachEnd fromEdge toEdge', () => {
            this.#refreshNavState();
        });
    }

    /**
     * Initializes the thumbnail Swiper.
     *
     * Built-in navigation and slideToClickedSlide are not used —
     * their job is done by custom handlers and the main
     * slider as the source of truth.
     *
     * @param  {Element} container — common slider container
     * @return {void}
     */
    #initThumbs( container ) {

        const thumbs = container.querySelector( '.as-slider-product-thumbs' );

        if ( ! thumbs ) {
            return;
        }

        this.#thumbsSwiper = new Swiper( thumbs, {

            slidesPerView: this.#thumbsPerView,
            slidesPerScroll: 1,
            spaceBetween: 5,
            loop: false,
            watchSlidesProgress: true,
            watchOverflow: true,

            a11y: {
                enabled: true,
            },
        });

        // Collect the DOM elements of the thumbnail slides for manual
        // highlighting of the active one. Use querySelectorAll
        // on the real container so that the order matches
        // the order in the PHP markup.
        this.#thumbSlides = qsa(
            '.as-slider-product-thumbs .swiper-slide',
            container
        );

        // Update arrow visibility — for example, if there are fewer
        // thumbnails than fit, the arrows are not needed.
        this.#updateThumbsVisibility();
    }

    /**
     * Attaches click handlers to the arrow buttons.
     *
     * Both arrows control the main slider — this guarantees
     * switching exactly one slide forward or backward.
     *
     * @return {void}
     */
    #bindArrows() {

        if ( ! this.#mainSwiper ) {
            return;
        }

        if ( this.#prevButton ) {
            this.#prevButton.addEventListener( 'click', ( event ) => {
                event.preventDefault();
                this.#mainSwiper.slidePrev();
            });
        }

        if ( this.#nextButton ) {
            this.#nextButton.addEventListener( 'click', ( event ) => {
                event.preventDefault();
                this.#mainSwiper.slideNext();
            });
        }
    }

    /**
     * Attaches click handlers to each thumbnail.
     *
     * A thumbnail click switches the main slider to the index
     * of that thumbnail. Further synchronization (highlight,
     * thumbnail scrolling, arrows) happens automatically
     * on main's activeIndexChange event.
     *
     * @return {void}
     */
    #bindThumbClicks() {

        if ( ! this.#mainSwiper || ! this.#thumbSlides.length ) {
            return;
        }

        this.#thumbSlides.forEach( ( slide, index ) => {

            slide.addEventListener( 'click', ( event ) => {

                event.preventDefault();

                // Use the index from the closure — it matches
                // the real order of slides in the markup.
                this.#mainSwiper.slideTo( index );
            });
        });
    }

    /**
     * Updates the active thumbnail highlight.
     *
     * Removes the class from all thumbnails and sets it on the thumbnail
     * whose index equals main.activeIndex. A custom class is used
     * instead of the built-in .swiper-slide-active, because the latter
     * always sits on the left thumbnail with slidesPerView: 3.
     *
     * @return {void}
     */
    #refreshActiveThumb() {

        if ( ! this.#mainSwiper || ! this.#thumbSlides.length ) {
            return;
        }

        const activeIndex = this.#mainSwiper.activeIndex;

        this.#thumbSlides.forEach( ( slide, index ) => {
            slide.classList.toggle(
                'as-slider-product-thumb-active',
                index === activeIndex
            );
        });
    }

    /**
     * Scrolls the thumbnails so that the active thumbnail is
     * visible on screen.
     *
     * slideTo is used with runCallbacks = false so that
     * scrolling the thumbnails does not restart the synchronization chain.
     *
     * @return {void}
     */
    #scrollThumbsToActive() {

        if ( ! this.#mainSwiper || ! this.#thumbsSwiper ) {
            return;
        }

        const activeIndex = this.#mainSwiper.activeIndex;

        // If it already matches — do nothing
        if ( this.#thumbsSwiper.activeIndex === activeIndex ) {
            return;
        }

        this.#thumbsSwiper.slideTo( activeIndex, 300, false );
    }

    /**
     * Initializes GLightbox for viewing images
     * in full size.
     *
     * @param  {Element} container — common slider container
     * @return {void}
     */
    #initLightbox( container ) {

        const links = container.querySelectorAll( '.glightbox-gallery' );

        if ( ! links.length ) {
            return;
        }

        if ( typeof GLightbox !== 'function' ) {
            return;
        }

        this.#glightbox = GLightbox({
            selector: '.as-slider-product .glightbox-gallery',
            touchNavigation: true,
            loop: true,
            zoomable: true,
            draggable: true,
        });
    }

    /**
     * Updates arrow visibility: if the thumbnails all fit,
     * the arrows are hidden.
     *
     * @return {void}
     */
    #updateThumbsVisibility() {

        if ( ! this.#stage || ! this.#thumbSlides.length ) {
            return;
        }

        const slidesCount = this.#thumbSlides.length;

        if ( slidesCount <= this.#thumbsPerView ) {
            this.#stage.classList.add( 'as-slider-product-thumbs-no-nav' );
        } else {
            this.#stage.classList.remove( 'as-slider-product-thumbs-no-nav' );
        }
    }

    /**
     * Updates the arrow state: visibility and disabled.
     *
     *   - If all thumbnails are visible, the arrows are hidden entirely.
     *   - On the first slide the left arrow is disabled.
     *   - On the last slide the right arrow is disabled.
     *
     * @return {void}
     */
    #refreshNavState() {

        this.#updateThumbsVisibility();

        if ( ! this.#mainSwiper || ! this.#stage ) {
            return;
        }

        const slidesCount = this.#thumbSlides.length;

        if ( slidesCount <= this.#thumbsPerView ) {
            return;
        }

        if ( this.#prevButton ) {
            this.#prevButton.disabled = this.#mainSwiper.isBeginning;
        }

        if ( this.#nextButton ) {
            this.#nextButton.disabled = this.#mainSwiper.isEnd;
        }
    }
}

/**
 * Ready-made singleton of the product slider module.
 *
 * @type {ArsSliderProduct}
 */
export const arsSliderProduct = new ArsSliderProduct();