/**
 * ArgonShop — view history module.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * Adds the ID of the viewed product to the history. The full version
 * of the module (with the list of viewed products) will be implemented
 * in one of the next steps.
 *
 * Currently only the add() method is implemented; it is called from
 * the product page when it opens.
 *
 * ============================================================
 * AJAX ACTION
 * ============================================================
 *
 * addCookieHistory_history
 *   Accepts: productID.
 *   Saves the ID in a cookie via the server.
 *
 * ============================================================
 * USAGE
 * ============================================================
 *
 *     import { arsHistory } from './modules/history.js';
 *     arsHistory.add( 42 );
 */

'use strict';

import { arsApi } from '../core/api.js';

/**
 * View history module.
 */
class ArsHistory {

    /**
     * Adds a product to the view history.
     *
     * The request does not wait for a response — an error when adding
     * is not critical and must not break the interface.
     *
     * @param  {string|number} productId — product ID
     * @return {void}
     */
    add( productId ) {

        if ( ! productId ) {
            return;
        }

        arsApi.post( 'addCookieHistory_history', {
            productID: productId,
        }).catch( () => {
            // Ignore the error — the history is not critical
        });
    }
}

/**
 * Ready singleton of the view history module.
 *
 * @type {ArsHistory}
 */
export const arsHistory = new ArsHistory();