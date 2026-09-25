/**
 * ArgonShopTheme — слайдер сертификатов на странице «О компании».
 *
 * @package ArgonShopTheme
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Инициализирует всё, что относится к блоку «Дипломы и сертификаты»:
 *
 *   1. Swiper — слайдер сертификатов со стрелками и адаптивом.
 *   2. GLightbox — просмотр сертификата в полном размере
 *      по клику на изображение.
 *
 * Разметка выводится в шаблоне aboutCompany.php.
 * Скрипт подключается условно — только на странице «О компании»
 * (см. functions.php, функция astheme_scripts).
 *
 * ============================================================
 * ЗАВИСИМОСТИ
 * ============================================================
 *
 * Глобальные объекты из плагина ArgonShop:
 *   Swiper     — assets/vendor/swiper/swiper-bundle.min.js
 *   GLightbox  — assets/vendor/glightbox/glightbox.min.js
 *
 * ============================================================
 * АДАПТИВ
 * ============================================================
 *
 *   >= 780px  — 3 сертификата
 *   >= 550px  — 2 сертификата
 *   < 550px   — 1 сертификат
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