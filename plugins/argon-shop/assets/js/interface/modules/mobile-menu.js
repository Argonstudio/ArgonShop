/**
 * ArgonShop — mobile site menu.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * Handles opening and closing the mobile menu in the site header,
 * as well as expanding nested submenus.
 *
 * ============================================================
 * DOM ELEMENTS
 * ============================================================
 *
 * .openMobileMenu
 *   Menu open/close button.
 *
 * #block-menu
 *   Menu container.
 *
 * .menu-item-has-children
 *   Menu item with a submenu.
 *
 * .sub-menu
 *   Nested submenu inside .menu-item-has-children.
 *
 * ============================================================
 * USAGE
 * ============================================================
 *
 *     import { arsMobileMenu } from './modules/mobile-menu.js';
 *     arsMobileMenu.init();
 */

'use strict';

import { qs, qsa, on, delegate } from '../../shared/dom.js';


/**
 * Window width threshold below which the mobile menu is active.
 *
 * @type {number}
 */
const MOBILE_WIDTH = 1200;

/**
 * Mobile site menu module.
 */
class ArsMobileMenu {

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

        if ( ! qs( '.openMobileMenu' ) || ! qs( '#block-menu' ) ) {
            return;
        }

        this.#initialized = true;

        this.#bindToggle();
        this.#bindSubmenu();
    }

    /**
     * Menu open/close button.
     *
     * @return {void}
     */
    #bindToggle() {

        const button = qs( '.openMobileMenu' );
        const menu   = qs( '#block-menu' );

        if ( ! button || ! menu ) {
            return;
        }

        on( button, 'click', () => {

            const isVisible = menu.style.display === 'block';

            if ( isVisible ) {
                menu.style.display = 'none';
                button.style.backgroundColor = '';
            } else {
                menu.style.display = 'block';
                button.style.backgroundColor = '#659bdd';
            }
        });
    }

    /**
     * Expanding nested submenus.
     *
     * @return {void}
     */
    #bindSubmenu() {

        delegate( document, 'click', '.menu-item-has-children', ( event, item ) => {

            if ( window.innerWidth > MOBILE_WIDTH ) {
                return;
            }

            const target = event.target;

            // React only to a click on the item itself or
            // its direct child — not on a link inside the submenu.
            if ( target !== item && target.parentNode !== item ) {
                return;
            }

            const submenu = qs( '.sub-menu', item );

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
        });
    }
}

/**
 * Ready-made singleton of the mobile menu module.
 *
 * @type {ArsMobileMenu}
 */
export const arsMobileMenu = new ArsMobileMenu();