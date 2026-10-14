/**
 * ArgonShop — mobile blocks of the catalog page.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * Controls the collapsible blocks on the catalog page that
 * are hidden on mobile devices and expand on click:
 *
 *   - "Category description";
 *   - "Child categories";
 *   - "Popular queries";
 *   - "Sort by".
 *
 * Classes and structure are defined by the theme template taxonomy-catalog.php.
 *
 * ============================================================
 * DOM ELEMENTS
 * ============================================================
 *
 * .catalogPage_openMobile
 *   Header of the collapsible block (button).
 *
 * .catalogPage_mobileExtensible
 *   Block content, the next sibling of the button.
 *
 * ============================================================
 * USAGE
 * ============================================================
 *
 *     import { arsCatalogPage } from './modules/catalog-page.js';
 *     arsCatalogPage.init();
 *
 * The module is safe to call on any page: if there are no blocks,
 * nothing happens.
 */

'use strict';

import { delegate } from '../../shared/dom.js';

/**
 * Catalog mobile blocks module.
 */
class ArsCatalogPage {

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

        this.#initialized = true;

        delegate( document, 'click', '.catalogPage_openMobile', ( event, button ) => {

            const content = button.nextElementSibling;

            if ( ! content || ! content.classList.contains( 'catalogPage_mobileExtensible' ) ) {
                return;
            }

            const isVisible = content.style.display === 'block';

            content.style.display = isVisible ? 'none' : 'block';
        });
    }
}

/**
 * Ready singleton of the catalog mobile blocks module.
 *
 * @type {ArsCatalogPage}
 */
export const arsCatalogPage = new ArsCatalogPage();