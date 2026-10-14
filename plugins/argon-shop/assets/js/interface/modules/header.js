/**
 * ArgonShop — city selection in the site header.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * When the city in the dropdown changes, the browser goes to
 * the corresponding page of the current section of the other city.
 *
 * Navigation rules:
 *
 *   1. If the user is on the promotions page — go to the
 *      promotions page of the same city.
 *
 *   2. If the user is on the promotions archive page — go
 *      to the promotions archive of the other city.
 *
 *   3. In all other cases — go to the same URL
 *      path, but in the other city.
 *
 * ============================================================
 * DOM ELEMENTS
 * ============================================================
 *
 * .sitySelect
 *   City select. The value is the base URL of the city.
 *
 * The select options may have the attributes:
 *   data-promocategory — path to the city's promotions page.
 *   data-promoarchive  — path to the city's promotions archive.
 *
 * main
 *   Content container. Its id is used to determine
 *   the current page:
 *     #block-contentPromo       — promotions page.
 *     #block-contentPromoPage   — a single promotion.
 *     #block-contentPromoArchive — promotions archive.
 *
 * ============================================================
 * USAGE
 * ============================================================
 *
 *     import { arsHeader } from './modules/header.js';
 *     arsHeader.init();
 */

'use strict';


import { qs, on } from '../../shared/dom.js';

/**
 * City selection module.
 */
class ArsHeader {

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

        const select = qs( '.sitySelect' );

        if ( ! select ) {
            return;
        }

        this.#initialized = true;

        on( select, 'change', () => this.#handleChange( select ) );
    }

    /**
     * Handles the city change.
     *
     * @param  {HTMLSelectElement} select — city select
     * @return {void}
     */
    #handleChange( select ) {

        const value    = select.value;
        const selected = select.options[ select.selectedIndex ];

        if ( ! value || ! selected ) {
            return;
        }

        const main = qs( 'main' );
        const mainId = main ? main.id : '';

        // Promotions page or a single promotion
        if ( mainId === 'block-contentPromo' || mainId === 'block-contentPromoPage' ) {

            const promoCategory = selected.dataset.promocategory || '';
            window.location.href = value + promoCategory;
            return;
        }

        // Promotions archive
        if ( mainId === 'block-contentPromoArchive' ) {

            const promoArchive = selected.dataset.promoarchive || '';
            window.location.href = value + promoArchive;
            return;
        }

        // All other pages — the same path on another domain
        window.location.href = value + window.location.pathname;
    }
}

/**
 * Ready singleton of the city selection module.
 *
 * @type {ArsHeader}
 */
export const arsHeader = new ArsHeader();