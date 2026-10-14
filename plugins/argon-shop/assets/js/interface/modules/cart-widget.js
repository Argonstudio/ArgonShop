/**
 * ArgonShop — cart widget in the site header.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * Controls the display of the number of products and the cart total in the site
 * header. The widget is present on all pages except the cart
 * page (see includes/interface/shoppingCart/infoCart.php).
 *
 * The module is used by other modules:
 *   - add-to-cart.js — after a product is added
 *   - cart.js        — when the cart is emptied or an item is removed
 *
 * ============================================================
 * DOM ELEMENTS
 * ============================================================
 *
 * .viewBlock-amountProducts span
 *   Number of products in the cart.
 *
 * .viewBlock-priceProducts
 *   Total container. Hidden if the cart is empty.
 *
 * .viewBlock-priceProducts span
 *   Total of the products in the cart.
 *
 * ============================================================
 * USAGE
 * ============================================================
 *
 *     import { arsCartWidget } from './modules/cart-widget.js';
 *
 *     arsCartWidget.update({
 *         productAmountCart: 5,
 *         totalPrice: 12500,
 *     });
 *
 * ============================================================
 * EXPORTS
 * ============================================================
 *
 * arsCartWidget.update( data )   — update the number and total
 * arsCartWidget.clear()          — reset the widget (the cart is empty)
 */

'use strict';

import { qs } from '../../shared/dom.js';
import { formatPrice } from '../../shared/format.js';

/**
 * Cart widget in the site header.
 */
class ArsCartWidget {

    /**
     * Updates the number of products and the total in the widget.
     *
     * @param  {Object} data                         — cart data
     * @param  {number} [data.productAmountCart=0]   — number of products
     * @param  {number} [data.totalPrice=0]          — cart total
     * @return {void}
     */
    update( data ) {

        const amount   = Number( data && data.productAmountCart ) || 0;
        const total    = Number( data && data.totalPrice ) || 0;

        const amountSpan  = qs( '.viewBlock-amountProducts span' );
        const priceBlock  = qs( '.viewBlock-priceProducts' );
        const priceSpan   = qs( '.viewBlock-priceProducts span' );

        if ( amountSpan ) {
            amountSpan.classList.add( 'viewBlock-productsStock' );
            amountSpan.textContent = String( amount );
        }

        if ( ! priceBlock || ! priceSpan ) {
            return;
        }

        if ( total > 0 ) {
            priceSpan.textContent = formatPrice( total );
            priceBlock.style.display = 'block';
        } else {
            priceSpan.textContent = '';
            priceBlock.style.display = 'none';
        }
    }

    /**
     * Resets the widget — used after the cart is fully cleared.
     *
     * @return {void}
     */
    clear() {
        this.update({ productAmountCart: 0, totalPrice: 0 });
    }
}

/**
 * Ready cart widget singleton.
 *
 * @type {ArsCartWidget}
 */
export const arsCartWidget = new ArsCartWidget();