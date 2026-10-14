/**
 * ArgonShop — home page slider.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * Initializes Swiper on the site home page. Replaces
 * the old Slick slider.
 *
 * The HTML markup is printed by the PHP function as_slider_main()
 * (see includes/interface/slider/sliderMain.php).
 *
 * ============================================================
 * DOM ELEMENTS
 * ============================================================
 *
 * .as-slider-main
 *   Slider container. Inside — .swiper-wrapper with
 *   .swiper-slide slides, .swiper-pagination and arrows
 *   .swiper-button-prev / .swiper-button-next.
 *
 * ============================================================
 * USAGE
 * ============================================================
 *
 *     import { arsSliderMain } from './modules/slider-main.js';
 *     arsSliderMain.init();
 *
 * The module is safe to call on any page: if there is no slider,
 * nothing is executed.
 */

'use strict';

import { qs } from '../../shared/dom.js';

/**
 * Home page slider module.
 */
class ArsSliderMain {

    /** @type {boolean} */
    #initialized = false;

    /**
     * Swiper instance.
     *
     * @type {Object|null}
     */
    #swiper = null;

    /**
     * Initialization. Safe to call repeatedly.
     *
     * @return {void}
     */
    init() {

        if ( this.#initialized ) {
            return;
        }

        const slider = qs( '.as-slider-main' );

        if ( ! slider ) {
            return;
        }

        if ( typeof Swiper !== 'function' ) {
            return;
        }

        this.#initialized = true;

        this.#swiper = new Swiper( slider, {

            loop: true,
            speed: 1500,
            slidesPerView: 1,
            slidesPerScroll: 1,

            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },

            pagination: {
                el: slider.querySelector( '.swiper-pagination' ),
                clickable: true,
            },

            navigation: {
                nextEl: slider.querySelector( '.swiper-button-next' ),
                prevEl: slider.querySelector( '.swiper-button-prev' ),
            },

            a11y: {
                enabled: true,
            },
        });
    }

    /**
     * Returns the Swiper instance for external control.
     *
     * @return {Object|null}
     */
    getInstance() {
        return this.#swiper;
    }
}

/**
 * Ready-made singleton of the home slider module.
 *
 * @type {ArsSliderMain}
 */
export const arsSliderMain = new ArsSliderMain();