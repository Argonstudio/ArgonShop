/**
 * ArgonShop — меню каталога в сайдбаре, большое меню.
 *
 * @package ArgonShop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Управляет поведением меню каталога в сайдбаре:
 *
 *   1. На десктопе — компенсирует высоту контейнера, если
 *      раскрывающееся подменю не помещается на странице.
 *      Это делается через hover: пока курсор над пунктом,
 *      проверяем высоту, при необходимости увеличиваем min-height
 *      у .row внутри #out.
 *
 *   2. Открытие и закрытие всего меню на мобильных через кнопку
 *      .openMobileCatalog.
 *
 *   3. Раскрытие подменю при клике на пункт .itemHasChildren
 *      на мобильных.
 *
 * ============================================================
 * DOM-ЭЛЕМЕНТЫ
 * ============================================================
 *
 * .openMobileCatalog
 *   Кнопка «Каталог продукции» для мобильных.
 *
 * .block-menuCatalog
 *   Контейнер меню каталога.
 *
 * .block-linkSidebar
 *   Блок дополнительных ссылок под меню.
 *
 * .menuCatalogItem
 *   Пункт меню верхнего уровня.
 *
 * .itemHasChildren
 *   Пункт меню, у которого есть подменю.
 *
 * .submenuCatalog
 *   Вложенное подменю.
 *
 * #header          — шапка сайта (для расчёта высоты).
 * #out .row        — обёртка контента страницы.
 *
 * ============================================================
 * ИСПОЛЬЗОВАНИЕ
 * ============================================================
 *
 *     import { arsMenuCatalog } from './modules/menu-catalog.js';
 *     arsMenuCatalog.init();
 */

'use strict';

import { qs, qsa, on, delegate } from '../../shared/dom.js';

/**
 * Пороговая ширина окна, ниже которой включается мобильный режим.
 *
 * @type {number}
 */
const MOBILE_WIDTH = 1000;

/**
 * Отступ между пунктом меню и началом подменю, в пикселях.
 * Используется при расчёте высоты.
 *
 * @type {number}
 */
const MENU_OFFSET = 46;

/**
 * Запас высоты, добавляемый при необходимости увеличить
 * min-height контейнера, в пикселях.
 *
 * @type {number}
 */
const HEIGHT_BUFFER = 300;

/**
 * Модуль меню каталога.
 */
class ArsMenuCatalog {

    /** @type {boolean} */
    #initialized = false;

    /**
     * Инициализация. Безопасна для повторного вызова.
     *
     * @return {void}
     */
    init() {

        if ( this.#initialized ) {
            return;
        }

        if ( ! qs( '.block-menuCatalog' ) && ! qs( '.openMobileCatalog' ) ) {
            return;
        }

        this.#initialized = true;

        this.#bindHoverCompensation();
        this.#bindMobileToggle();
        this.#bindSubmenuToggle();
    }

    // ============================================================
    // КОМПЕНСАЦИЯ ВЫСОТЫ НА ДЕСКТОПЕ
    // ============================================================

    /**
     * При наведении на пункт меню проверяет, помещается ли
     * выпадающее подменю на странице. Если нет — увеличивает
     * min-height контейнера.
     *
     * @return {void}
     */
    #bindHoverCompensation() {

        const items = qsa( '.menuCatalogItem' );

        if ( ! items.length ) {
            return;
        }

        const contentRow = qs( '#out .row' );

        if ( ! contentRow ) {
            return;
        }

        items.forEach( item => {

            item.addEventListener( 'mouseenter', () => {

                if ( document.body.clientWidth <= MOBILE_WIDTH ) {
                    return;
                }

                const header = qs( '#header' );

                if ( ! header ) {
                    return;
                }

                const submenu = qs( '.submenuCatalog', item );

                if ( ! submenu ) {
                    return;
                }

                const pageHeight = document.body.scrollHeight;
                const needHeight = header.offsetHeight + MENU_OFFSET + submenu.offsetHeight;

                if ( needHeight >= pageHeight ) {
                    contentRow.style.minHeight =
                        ( document.body.scrollHeight + ( needHeight - pageHeight ) + HEIGHT_BUFFER ) + 'px';
                }
            });

            item.addEventListener( 'mouseleave', () => {
                contentRow.style.minHeight = '';
            });
        });
    }

    // ============================================================
    // МОБИЛЬНОЕ ОТКРЫТИЕ МЕНЮ
    // ============================================================

    /**
     * Кнопка «Каталог продукции» открывает и закрывает меню.
     *
     * @return {void}
     */
    #bindMobileToggle() {

        const button = qs( '.openMobileCatalog' );

        if ( ! button ) {
            return;
        }

        on( button, 'click', () => {

            const menu = qs( '.block-menuCatalog' );
            const links = qs( '.block-linkSidebar' );

            if ( ! menu ) {
                return;
            }

            const isVisible = menu.style.display === 'block';

            menu.style.display = isVisible ? 'none' : 'block';

            if ( links ) {
                links.style.display = isVisible ? 'none' : 'block';
            }
        });
    }

    // ============================================================
    // РАСКРЫТИЕ ПОДМЕНЮ НА МОБИЛЬНЫХ
    // ============================================================

    /**
     * Клик по пункту .itemHasChildren раскрывает подменю
     * на мобильных.
     *
     * @return {void}
     */
    #bindSubmenuToggle() {

        delegate( document, 'click', '.itemHasChildren', ( event, item ) => {

            if ( document.body.clientWidth > MOBILE_WIDTH ) {
                return;
            }

            const target = event.target;

            // Клик на самом пункте или на его прямом потомке
            const isDirectClick = ( target === item ) || ! target.closest( '.submenuCatalog' );

            if ( ! isDirectClick ) {
                return;
            }

            const submenu = qs( '.submenuCatalog', item );

            if ( ! submenu ) {
                return;
            }

            const isVisible = submenu.style.display === 'block';

            if ( isVisible ) {
                submenu.style.display = 'none';
            } else {
                event.preventDefault();
                submenu.style.display = 'block';
            }

            event.stopPropagation();
        });
    }
}

/**
 * Готовый синглтон модуля меню каталога.
 *
 * @type {ArsMenuCatalog}
 */
export const arsMenuCatalog = new ArsMenuCatalog();