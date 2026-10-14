/**
 * ArgonShop — catalog product sorting.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * Handles clicks on the catalog sort buttons. On each
 * click an AJAX request is sent and the server returns an already
 * sorted HTML product list. The result is inserted
 * into the .catalogProductList block without a page reload.
 *
 * ============================================================
 * DOM ELEMENTS
 * ============================================================
 *
 * .as-sort
 *   Sort button. Has a name attribute — the sort type
 *   ('price', 'name', 'date'). The sort-min / sort-max classes
 *   show the current direction: ascending or descending.
 *
 * .sortParameters
 *   Container above the buttons. Stores:
 *     data-cat-id  — current catalog category ID
 *     data-page    — current pagination page number
 *
 * .catalogProductList
 *   Block that receives the product list.
 *
 * .as-error-sort
 *   Block for displaying sorting errors.
 *
 * ============================================================
 * AJAX ACTION
 * ============================================================
 *
 * sortingCatalog
 *   Accepts: typeSort, howSorting, catId, pageNum.
 *   Returns the HTML product list.
 *
 * ============================================================
 * SORT DIRECTION LOGIC
 * ============================================================
 *
 * If the button already has the sort-min class, the current
 * sorting was "ascending" and a new click switches to
 * descending (DESC).
 *
 * In all other cases the new direction is ASC (ascending),
 * and this switch is remembered by the sort-min class.
 *
 * ============================================================
 * USAGE
 * ============================================================
 *
 *     import { arsSort } from './modules/sort.js';
 *     arsSort.init();
 */

'use strict';

import { arsApi }             from '../core/api.js';
import { t }            from '../core/config.js';
import { qs, qsa, delegate }  from '../../shared/dom.js';

/**
 * Catalog sorting module.
 */
class ArsSort {

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

        // If the page has no sort buttons at all,
        // this is not a catalog page — exit.
        if ( ! qs( '.as-sort' ) ) {
            return;
        }

        this.#initialized = true;

        delegate( document, 'click', '.as-sort', ( event, button ) => {
            event.preventDefault();
            this.#handleSort( button );
        });
    }

    // ============================================================
    // CLICK HANDLING
    // ============================================================

    /**
     * Handles a click on a sort button.
     *
     * @param  {Element} button — .as-sort button
     * @return {Promise<void>}
     */
    async #handleSort( button ) {

        const sortParameters = button.closest( '.sortParameters' );

        if ( ! sortParameters ) {
            return;
        }

        const typeSort = button.getAttribute( 'name' );
        const catId    = parseInt( sortParameters.dataset.catId, 10 ) || 0;
        const pageNum  = parseInt( sortParameters.dataset.page, 10 )  || 1;

        // Determine the sort direction
        const howSorting = button.classList.contains( 'sort-min' ) ? 'DESC' : 'ASC';

        try {

            const html = await arsApi.post( 'sortingCatalog', {
                typeSort:   typeSort,
                howSorting: howSorting,
                catId:      catId,
                pageNum:    pageNum,
            });

            this.#showResult( html, button, howSorting );

        } catch {
            this.#showError();
        }
    }

    // ============================================================
    // UI UPDATE
    // ============================================================

    /**
     * Applies the sorting result to the page.
     *
     * @param  {string}  html       — ready HTML product list
     * @param  {Element} button     — the clicked button
     * @param  {string}  howSorting — 'ASC' or 'DESC'
     * @return {void}
     */
    #showResult( html, button, howSorting ) {

        // Reset the highlight on all buttons
        qsa( '.as-sort' ).forEach( el => {
            el.classList.remove( 'sort-min', 'sort-max' );
        });

        // Highlight the active button with the direction
        if ( howSorting === 'ASC' ) {
            button.classList.add( 'sort-min' );
        } else {
            button.classList.add( 'sort-max' );
        }

        // Update the product list
        const productList = qs( '.catalogProductList' );

        if ( productList ) {
            productList.innerHTML = html;
        }

        // Hide the error block if it was visible
        const errorBlock = qs( '.as-error-sort' );

        if ( errorBlock ) {
            errorBlock.style.display = 'none';
        }
    }

    /**
     * Shows a sorting error message.
     *
     * @return {void}
     */
    #showError() {

        const errorBlock = qs( '.as-error-sort' );

        if ( ! errorBlock ) {
            return;
        }

        errorBlock.textContent = t( 'serverError' );
        errorBlock.style.display = 'block';
    }
}

/**
 * Ready-made singleton of the sorting module.
 *
 * @type {ArsSort}
 */
export const arsSort = new ArsSort();