/**
 * ArgonShop — слайдер изображений товара.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Инициализирует два связанных слайдера Swiper на странице товара
 * и GLightbox для просмотра изображений в полном размере.
 * Заменяет старый слайдер Slick и Fancybox.
 *
 * HTML-разметку выводит PHP-функция as_slider_product()
 * (см. includes/interface/slider/sliderProduct.php).
 *
 * ============================================================
 * DOM-ЭЛЕМЕНТЫ
 * ============================================================
 *
 * .as-slider-product
 *   Общий контейнер слайдера товара.
 *
 * .as-slider-product-main
 *   Основной Swiper с крупными изображениями. Слайды содержат
 *   ссылки с классом .glightbox-gallery и атрибутом data-glightbox.
 *
 * .as-slider-product-thumbs-stage
 *   Обёртка миниатюр со стрелками навигации.
 *
 * .as-slider-product-thumbs
 *   Swiper с миниатюрами. Служит визуальной лентой: показывает
 *   3 миниатюры одновременно и подсвечивает активную.
 *
 * .as-slider-product-thumbs-prev / -next
 *   Кнопки навигации по миниатюрам. Показываются, если миниатюр
 *   больше, чем помещается в видимой области.
 *
 * .as-slider-product-thumb-active
 *   Класс активной миниатюры. Ставится вручную из JS, потому что
 *   встроенный .swiper-slide-active не подходит для миниатюр:
 *   при slidesPerView: 3 он всегда стоит на левом слайде и не
 *   отражает реальный активный индекс.
 *
 * ============================================================
 * ИСТОЧНИК ИСТИНЫ — ОСНОВНОЙ СЛАЙДЕР
 * ============================================================
 *
 * В основном слайдере по одному слайду на изображение,
 * его activeIndex всегда в диапазоне 0..N-1. Миниатюры же
 * показывают 3 слайда одновременно и имеют только позиции привязки
 * 0, 1, 2 — activeIndex миниатюр НЕ МОЖЕТ быть 3 или 4 при 5
 * слайдах и slidesPerView: 3. Поэтому встроенная
 * опция slideToClickedSlide не работает для миниатюр дальше третьего
 * изображения.
 *
 * Поэтому весь активный индекс живёт в основном слайдере:
 *
 *   - клик по стрелке → mainSwiper.slideNext() / slidePrev();
 *   - клик по миниатюре → mainSwiper.slideTo(index);
 *   - при изменении activeIndex основного слайдера:
 *       1) обновляем подсветку активной миниатюры (наш класс),
 *       2) прокручиваем thumbsSwiper к нужному индексу,
 *          чтобы миниатюра была видна на экране,
 *       3) обновляем состояние стрелок (disabled на краях).
 *
 * ============================================================
 * ИСПОЛЬЗОВАНИЕ
 * ============================================================
 *
 *     import { arsSliderProduct } from './modules/slider-product.js';
 *     arsSliderProduct.init();
 *
 * Модуль безопасно вызывать на любой странице: если слайдера нет,
 * ничего не выполняется.
 */

'use strict';

import { qs, qsa } from '../../shared/dom.js';

/**
 * Модуль слайдера товара.
 */
class ArsSliderProduct {

    /** @type {boolean} */
    #initialized = false;

    /**
     * Основной экземпляр Swiper.
     *
     * @type {Object|null}
     */
    #mainSwiper = null;

    /**
     * Экземпляр Swiper миниатюр.
     *
     * @type {Object|null}
     */
    #thumbsSwiper = null;

    /**
     * Экземпляр GLightbox для галереи.
     *
     * @type {Object|null}
     */
    #glightbox = null;

    /**
     * Обёртка миниатюр со стрелками.
     *
     * @type {Element|null}
     */
    #stage = null;

    /**
     * Кнопка «Назад».
     *
     * @type {Element|null}
     */
    #prevButton = null;

    /**
     * Кнопка «Вперёд».
     *
     * @type {Element|null}
     */
    #nextButton = null;

    /**
     * Массив слайдов-миниатюр (реальные DOM-элементы).
     * Нужен для ручной подсветки активной миниатюры.
     *
     * @type {Element[]}
     */
    #thumbSlides = [];

    /**
     * Сколько слайдов-миниатюр видно одновременно.
     *
     * @type {number}
     */
    #thumbsPerView = 3;

    /**
     * Инициализация. Безопасно вызывать повторно.
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

        // Сначала основной слайдер — он источник истины.
        this.#initMain( container );

        // Затем миниатюры, связанные с основным слайдером.
        this.#initThumbs( container );

        // GLightbox для галереи.
        this.#initLightbox( container );

        // Обработчики кликов по стрелкам и миниатюрам.
        this.#bindArrows();
        this.#bindThumbClicks();

        // Начальная подсветка, состояние стрелок, позиция миниатюр.
        this.#refreshActiveThumb();
        this.#refreshNavState();
    }

    /**
     * Инициализирует основной Swiper.
     *
     * @param  {Element} container — общий контейнер слайдера
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

        // При смене активного слайда основного слайдера
        // обновляем подсветку миниатюр, прокрутку и стрелки.
        this.#mainSwiper.on( 'activeIndexChange', () => {
            this.#refreshActiveThumb();
            this.#scrollThumbsToActive();
            this.#refreshNavState();
        });

        // На случай, если activeIndex не изменился,
        // а край изменился (resize и т. п.).
        this.#mainSwiper.on( 'reachBeginning reachEnd fromEdge toEdge', () => {
            this.#refreshNavState();
        });
    }

    /**
     * Инициализирует Swiper миниатюр.
     *
     * Встроенная навигация и slideToClickedSlide не используются —
     * их работу выполняют собственные обработчики и основной
     * слайдер как источник истины.
     *
     * @param  {Element} container — общий контейнер слайдера
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

        // Собираем DOM-элементы слайдов-миниатюр для ручной
        // подсветки активного. Используем querySelectorAll
        // по реальному контейнеру, чтобы порядок совпадал
        // с порядком в PHP-разметке.
        this.#thumbSlides = qsa(
            '.as-slider-product-thumbs .swiper-slide',
            container
        );

        // Обновляем видимость стрелок — например, если миниатюр меньше,
        // чем помещается, стрелки не нужны.
        this.#updateThumbsVisibility();
    }

    /**
     * Вешает обработчики кликов на кнопки-стрелки.
     *
     * Обе стрелки управляют основным слайдером — это гарантирует
     * переключение ровно на один слайд вперёд или назад.
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
     * Вешает обработчики кликов на каждую миниатюру.
     *
     * Клик по миниатюре переключает основной слайдер на индекс
     * этой миниатюры. Дальнейшая синхронизация (подсветка,
     * прокрутка миниатюр, стрелки) происходит автоматически
     * по событию activeIndexChange основного слайдера.
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

                // Используем индекс из замыкания — он соответствует
                // реальному порядку слайдов в разметке.
                this.#mainSwiper.slideTo( index );
            });
        });
    }

    /**
     * Обновляет подсветку активной миниатюры.
     *
     * Снимает класс со всех миниатюр и ставит его на миниатюру,
     * индекс которой равен main.activeIndex. Используется собственный класс
     * вместо встроенного .swiper-slide-active, потому что последний
     * при slidesPerView: 3 всегда стоит на левой миниатюре.
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
     * Прокручивает миниатюры так, чтобы активная миниатюра была
     * видна на экране.
     *
     * slideTo вызывается с runCallbacks = false, чтобы
     * прокрутка миниатюр не запускала цепочку синхронизации заново.
     *
     * @return {void}
     */
    #scrollThumbsToActive() {

        if ( ! this.#mainSwiper || ! this.#thumbsSwiper ) {
            return;
        }

        const activeIndex = this.#mainSwiper.activeIndex;

        // Если уже совпадает — ничего не делаем
        if ( this.#thumbsSwiper.activeIndex === activeIndex ) {
            return;
        }

        this.#thumbsSwiper.slideTo( activeIndex, 300, false );
    }

    /**
     * Инициализирует GLightbox для просмотра изображений
     * в полном размере.
     *
     * @param  {Element} container — общий контейнер слайдера
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
     * Обновляет видимость стрелок: если все миниатюры помещаются,
     * стрелки скрываются.
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
     * Обновляет состояние стрелок: видимость и disabled.
     *
     *   - Если видны все миниатюры, стрелки полностью скрываются.
     *   - На первом слайде левая стрелка отключена.
     *   - На последнем слайде правая стрелка отключена.
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
 * Готовый синглтон модуля слайдера товара.
 *
 * @type {ArsSliderProduct}
 */
export const arsSliderProduct = new ArsSliderProduct();