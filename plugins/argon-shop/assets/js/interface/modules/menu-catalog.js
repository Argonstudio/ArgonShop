/**
 * ArgonShop — catalog menu in the sidebar, mega menu.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * Controls the behaviour of the catalog menu in the sidebar:
 *
 *   1. On desktop — compensates the container height if
 *      the dropdown submenu does not fit on the page.
 *      This is done on hover: while the cursor is over an item,
 *      the height is checked and, if needed, min-height
 *      of .row inside #out is increased.
 *
 *   2. Opening and closing the whole menu on mobile via the button
 *      .openMobileCatalog.
 *
 *   3. Expanding a submenu on click on an .itemHasChildren item
 *      on mobile.
 *
 * ============================================================
 * DOM ELEMENTS
 * ============================================================
 *
 * .openMobileCatalog
 *   The "Product catalog" button for mobile.
 *
 * .block-menuCatalog
 *   Catalog menu container.
 *
 * .block-linkSidebar
 *   Block of additional links under the menu.
 *
 * .menuCatalogItem
 *   Top-level menu item.
 *
 * .itemHasChildren
 *   Menu item that has a submenu.
 *
 * .submenuCatalog
 *   Nested submenu.
 *
 * #header          — site header (for the height calculation).
 * #out .row        — page content wrapper.
 *
 * ============================================================
 * USAGE
 * ============================================================
 *
 *     import { arsMenuCatalog } from './modules/menu-catalog.js';
 *     arsMenuCatalog.init();
 */

'use strict';

import { qs, qsa, on, delegate } from '../../shared/dom.js';

/**
 * Window width threshold below which the mobile mode is enabled.
 *
 * @type {number}
 */
const MOBILE_WIDTH = 1000;

/**
 * Gap between the menu item and the start of the submenu, in pixels.
 * Used in the height calculation.
 *
 * @type {number}
 */
const MENU_OFFSET = 46;

/**
 * Extra height added when the container min-height
 * has to be increased, in pixels.
 *
 * @type {number}
 */
const HEIGHT_BUFFER = 300;

/**
 * Catalog menu module.
 */
class ArsMenuCatalog {

    /** @type {boolean} */
    #initialized = false;

    /**
     * Initialization. Safe to call repeatedly.
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
    // HEIGHT COMPENSATION ON DESKTOP
    // ============================================================

    /**
     * On hover over a menu item checks whether
     * the dropdown submenu fits on the page. If not — increases
     * the container min-height.
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
    // OPENING THE MENU ON MOBILE
    // ============================================================

    /**
     * The "Product catalog" button opens and closes the menu.
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
    // EXPANDING SUBMENUS ON MOBILE
    // ============================================================

    /**
     * A click on an .itemHasChildren item expands the submenu
     * on mobile.
     *
     * @return {void}
     */
    #bindSubmenuToggle() {

        delegate( document, 'click', '.itemHasChildren', ( event, item ) => {

            if ( document.body.clientWidth > MOBILE_WIDTH ) {
                return;
            }

            const target = event.target;

            // A click on the item itself or on its direct child
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
 * Ready singleton of the catalog menu module.
 *
 * @type {ArsMenuCatalog}
 */
export const arsMenuCatalog = new ArsMenuCatalog();