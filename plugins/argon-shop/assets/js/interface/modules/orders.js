/**
 * ArgonShop — order filter in the customer account.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * Handles changes of the status dropdown in the orders section
 * of the customer account. When a new status is selected, an
 * AJAX request is sent and the server returns HTML with the filtered
 * orders table. The result is inserted into the block without a page reload.
 *
 * ============================================================
 * DOM ELEMENTS
 * ============================================================
 *
 * .as-orderCabinet-filterStatus
 *   Filter select. Contains:
 *     data-userid — user ID.
 *   Value — comma-separated status IDs ("1,2,3")
 *   or an empty string to show all orders.
 *
 * .as-ordersCabinet-block
 *   Container that receives the orders HTML table.
 *
 * ============================================================
 * AJAX ACTION
 * ============================================================
 *
 * ordersFiltersStatus
 *   Accepts: userID, status.
 *   Returns the orders HTML table.
 *
 *   The nonce is added automatically, as for all other actions
 *   (config.skipNonceActions is empty).
 *
 * ============================================================
 * USAGE
 * ============================================================
 *
 *     import { arsOrders } from './modules/orders.js';
 *     arsOrders.init();
 */

'use strict';

import { arsApi }      from '../core/api.js';
import { t }            from '../core/config.js';
import { qs, on }      from '../../shared/dom.js';

/**
 * Account order filter module.
 */
class ArsOrders {

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

        const select = qs( '.as-orderCabinet-filterStatus' );

        if ( ! select ) {
            return;
        }

        this.#initialized = true;

        on( select, 'change', () => this.#applyFilter( select ) );
    }

    /**
     * Sends the filter request and updates the orders table.
     *
     * @param  {HTMLSelectElement} select — filter select
     * @return {Promise<void>}
     */
    async #applyFilter( select ) {

        const userID      = select.dataset.userid;
        const status      = select.value;
        const blockResult = qs( '.as-ordersCabinet-block' );

        if ( ! blockResult ) {
            return;
        }

        try {

            const html = await arsApi.post( 'ordersFiltersStatus', {
                userID,
                status,
            });

            blockResult.innerHTML = html;

        } catch {
            blockResult.innerHTML = t( 'serverError' );
        }
    }
}

/**
 * Ready-made singleton of the order filter module.
 *
 * @type {ArsOrders}
 */
export const arsOrders = new ArsOrders();