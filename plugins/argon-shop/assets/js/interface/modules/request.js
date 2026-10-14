/**
 * ArgonShop — popular queries block in the catalog.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * Manages the "Popular queries" block on the catalog category
 * page:
 *
 *   1. If the popular queries block does not fit in height
 *      (content is clipped by CSS max-height), shows the
 *      "View all" button.
 *
 *   2. Clicking the button expands the block to full height,
 *      clicking again collapses it.
 *
 * Expanding and collapsing is done by toggling the
 * .is-expanded class. The CSS rule for this class must be added
 * to the theme:
 *
 *     .block-cards-request.is-expanded {
 *         max-height: none;
 *     }
 *
 * ============================================================
 * DOM ELEMENTS
 * ============================================================
 *
 * .block-cards-request      — container with query cards.
 * .view-allCards-request    — "View all" button.
 *
 * ============================================================
 * USAGE
 * ============================================================
 *
 *     import { arsRequest } from './modules/request.js';
 *     arsRequest.init();
 *
 * The module is safe to call on any page: if the block is missing,
 * nothing is executed.
 */

'use strict';

import { qs, on } from '../../shared/dom.js';

/**
 * Class added to the block in the expanded state.
 *
 * @type {string}
 */
const EXPANDED_CLASS = 'is-expanded';

/**
 * Tolerance in pixels by which the block may "under-expand".
 * If the difference between the content height and the visible height is less
 * than this value, the "View all" button is not shown.
 *
 * @type {number}
 */
const HEIGHT_THRESHOLD = 20;

/**
 * Popular queries block module.
 */
class ArsRequest {

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

        const block  = qs( '.block-cards-request' );
        const button = qs( '.view-allCards-request' );

        if ( ! block || ! button ) {
            return;
        }

        this.#initialized = true;

        // Show the button only if the content is clipped
        if ( this.#isContentClipped( block ) ) {
            button.style.display = 'block';
        }

        on( button, 'click', () => {
            block.classList.toggle( EXPANDED_CLASS );
        });
    }

    /**
     * Checks whether the block content is clipped in height.
     *
     * @param  {Element} block — query cards container
     * @return {boolean}
     */
    #isContentClipped( block ) {

        return block.scrollHeight > block.clientHeight + HEIGHT_THRESHOLD;
    }
}

/**
 * Ready-made singleton of the popular queries module.
 *
 * @type {ArsRequest}
 */
export const arsRequest = new ArsRequest();