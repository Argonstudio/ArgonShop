/**
 * ArgonShop — слайдер на главной странице.
 *
 * @package ArgonShop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Инициализирует Swiper на главной странице сайта. Заменяет
 * старый слайдер на Slick.
 *
 * HTML-разметка выводится PHP-функцией as_slider_main()
 * (см. includes/interface/slider/sliderMain.php).
 *
 * ============================================================
 * DOM-ЭЛЕМЕНТЫ
 * ============================================================
 *
 * .as-slider-main
 *   Контейнер слайдера. Внутри — .swiper-wrapper со слайдами
 *   .swiper-slide, пагинация .swiper-pagination и стрелки
 *   .swiper-button-prev / .swiper-button-next.
 *
 * ============================================================
 * ИСПОЛЬЗОВАНИЕ
 * ============================================================
 *
 *     import { arsSliderMain } from './modules/slider-main.js';
 *     arsSliderMain.init();
 *
 * Модуль безопасен для вызова на любой странице: если слайдера
 * нет, ничего не выполняется.
 */

'use strict';

import { qs } from '../../shared/dom.js';

/**
 * Модуль слайдера главной страницы.
 */
class ArsSliderMain {

    /** @type {boolean} */
    #initialized = false;

    /**
     * Экземпляр Swiper.
     *
     * @type {Object|null}
     */
    #swiper = null;

    /**
     * Инициализация. Безопасна для повторного вызова.
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
     * Возвращает экземпляр Swiper для внешнего управления.
     *
     * @return {Object|null}
     */
    getInstance() {
        return this.#swiper;
    }
}

/**
 * Готовый синглтон модуля слайдера главной.
 *
 * @type {ArsSliderMain}
 */
export const arsSliderMain = new ArsSliderMain();