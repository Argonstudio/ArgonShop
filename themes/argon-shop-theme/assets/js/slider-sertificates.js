/**
 * ArgonShopTheme — certificate slider on the "About the company" page.
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * Initializes everything related to the "Diplomas and certificates" block:
 *
 *   1. Swiper — certificate slider with arrows and responsive breakpoints.
 *   2. GLightbox — full-size certificate view
 *      on a click on the image.
 *
 * The markup is output in the aboutCompany.php template.
 * The script is enqueued conditionally — only on the "About the company" page
 * (see functions.php, the astheme_scripts function).
 *
 * ============================================================
 * DEPENDENCIES
 * ============================================================
 *
 * Global objects from the ArgonShop plugin:
 *   Swiper     — assets/vendor/swiper/swiper-bundle.min.js
 *   GLightbox  — assets/vendor/glightbox/glightbox.min.js
 *
 * ============================================================
 * RESPONSIVE BREAKPOINTS
 * ============================================================
 *
 *   >= 780px  — 3 certificates
 *   >= 550px  — 2 certificates
 *   < 550px   — 1 certificate
 *
 * ============================================================
 */

'use strict';

document.addEventListener( 'DOMContentLoaded', () => {

    const slider = document.querySelector( '.as-slider-sertificates' );

    if ( ! slider ) {
        return;
    }

    // ============================================================
    // SWIPER
    // ============================================================

    if ( typeof Swiper === 'function' ) {

        new Swiper( slider, {

            slidesPerView: 3,
            slidesPerScroll: 1,
            spaceBetween: 28,
            loop: true,
            draggable: false,

            breakpoints: {
                0:   { slidesPerView: 1, spaceBetween: 10 },
                550: { slidesPerView: 2, spaceBetween: 20 },
                780: { slidesPerView: 3, spaceBetween: 28 },
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

    // ============================================================
    // GLIGHTBOX
    // ============================================================

    const links = slider.querySelectorAll( '.glightbox-sertificate' );

    if ( links.length && typeof GLightbox === 'function' ) {

        GLightbox({
            selector: '.as-slider-sertificates .glightbox-sertificate',
            touchNavigation: true,
            loop: true,
            zoomable: true,
        });
    }
});