/**
 * ArgonShop — слайдер изображений товара.
 *
 * @package ArgonShop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Инициализирует два связанных Swiper-слайдера на странице товара
 * и GLightbox для просмотра изображений в полном размере.
 * Заменяет старый слайдер на Slick и Fancybox.
 *
 * HTML-разметка выводится PHP-функцией as_slider_product()
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
 *   Основной Swiper с большими изображениями. Слайды содержат
 *   ссылки с классом .glightbox-gallery и атрибутом data-glightbox.
 *
 * .as-slider-product-thumbs-stage
 *   Обёртка миниатюр со стрелками навигации.
 *
 * .as-slider-product-thumbs
 *   Swiper с миниатюрами. Служит визуальной полосой: показывает
 *   по 3 миниатюры за раз и подсвечивает активную.
 *
 * .as-slider-product-thumbs-prev / -next
 *   Кнопки навигации миниатюр. Показываются, если миниатюр
 *   больше, чем помещается в видимой области.
 *
 * .as-slider-product-thumb-active
 *   Класс активной миниатюры. Ставится вручную из JS, потому
 *   что встроенный .swiper-slide-active у миниатюр не подходит:
 *   при slidesPerView: 3 он всегда лежит на левом слайде и не
 *   отражает реальный активный индекс.
 *
 * ============================================================
 * ИСТОЧНИК ИСТИНЫ — ОСНОВНОЙ СЛАЙДЕР
 * ============================================================
 *
 * Основной слайдер содержит по одному слайду на изображение,
 * его activeIndex всегда лежит в диапазоне 0..N-1. Миниатюры же
 * показывают по 3 слайда за раз, и у них есть только snap-позиции
 * 0, 1, 2 — activeIndex миниатюр НЕ может быть 3 или 4 при 5
 * слайдах и slidesPerView: 3. Именно поэтому встроенная опция
 * slideToClickedSlide у миниатюр не работает дальше третьей
 * картинки.
 *
 * Поэтому весь активный индекс живёт в основном слайдере:
 *
 *   - клик по стрелке → mainSwiper.slideNext() / slidePrev();
 *   - клик по миниатюре → mainSwiper.slideTo(index);
 *   - при смене activeIndex у main:
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
 * Модуль безопасен для вызова на любой странице: если слайдера
 * нет, ничего не выполняется.
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
     * Экземпляр основного Swiper.
     *
     * @type {Object|null}
     */
    #mainSwiper = null;

    /**
     * Экземпляр Swiper с миниатюрами.
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
     * Кнопка «назад».
     *
     * @type {Element|null}
     */
    #prevButton = null;

    /**
     * Кнопка «вперёд».
     *
     * @type {Element|null}
     */
    #nextButton = null;

    /**
     * Массив слайдов миниатюр (реальных DOM-элементов).
     * Нужен для ручной подсветки активной миниатюры.
     *
     * @type {Element[]}
     */
    #thumbSlides = [];

    /**
     * Сколько слайдов миниатюр видно одновременно.
     *
     * @type {number}
     */
    #thumbsPerView = 3;

    /**
     * Инициализация. Безопасна для повторного вызова.
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

        // Затем миниатюры, привязанные к основному.
        this.#initThumbs( container );

        // GLightbox для галереи.
        this.#initLightbox( container );

        // Обработчики стрелок и кликов по миниатюрам.
        this.#bindArrows();
        this.#bindThumbClicks();

        // Первичная подсветка, состояние стрелок, позиция миниатюр.
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

        // На случай, если activeIndex не менялся,
        // но край изменился (resize и т.п.).
        this.#mainSwiper.on( 'reachBeginning reachEnd fromEdge toEdge', () => {
            this.#refreshNavState();
        });
    }

    /**
     * Инициализирует Swiper с миниатюрами.
     *
     * Встроенная навигация и slideToClickedSlide не используются —
     * их работу берут на себя собственные обработчики и основной
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

        // Собираем DOM-элементы слайдов миниатюр для ручной
        // подсветки активной. Используем querySelectorAll
        // по реальному контейнеру, чтобы порядок совпадал
        // с порядком в разметке PHP.
        this.#thumbSlides = qsa(
            '.as-slider-product-thumbs .swiper-slide',
            container
        );

        // Обновляем видимость стрелок — например, если миниатюр
        // меньше, чем помещается, они не нужны.
        this.#updateThumbsVisibility();
    }

    /**
     * Навешивает обработчики клика на кнопки-стрелки.
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
     * Навешивает обработчики клика на каждую миниатюру.
     *
     * Клик по миниатюре переключает основной слайдер на индекс
     * этой миниатюры. Дальнейшая синхронизация (подсветка,
     * прокрутка миниатюр, стрелки) сработает автоматически
     * по событию activeIndexChange у main.
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

                // Используем индекс из замыкания — он совпадает
                // с реальным порядком слайдов в разметке.
                this.#mainSwiper.slideTo( index );
            });
        });
    }

    /**
     * Обновляет подсветку активной миниатюры.
     *
     * Снимает класс со всех миниатюр и ставит его на миниатюру
     * с индексом, равным main.activeIndex. Свой класс используется
     * вместо встроенного .swiper-slide-active, потому что последний
     * у миниатюр при slidesPerView: 3 всегда лежит на левом слайде.
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
     * Используем slideTo с параметром runCallbacks = false, чтобы
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
     * Обновляет видимость стрелок: если миниатюр не больше,
     * чем помещается — стрелки скрываются.
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
     *   - Если миниатюры все видны — стрелки скрываются целиком.
     *   - На первом слайде стрелка влево disabled.
     *   - На последнем слайде стрелка вправо disabled.
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