/**
 * ArgonShop — adding a product to the cart.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * Shared add-to-cart logic used by:
 *   - product.js      — the "Add to cart" and "Buy" buttons on the product page
 *   - card-product.js — the same buttons in product cards
 *
 * One AJAX request, shared result and error handling.
 * After a successful add it updates the cart widget in the header
 * and returns the updated state via callbacks.
 *
 * ============================================================
 * AJAX ACTION
 * ============================================================
 *
 * addProducts_shoppingCart
 *   Accepts: productID, amountProduct.
 *   Returns JSON:
 *     {
 *       amountProduct,      // how many units of this product are in the cart
 *       productAmountCart,  // total products in the cart
 *       totalPrice,         // cart total
 *     }
 *
 * ============================================================
 * USAGE
 * ============================================================
 *
 *     import { addProductToCart } from './modules/add-to-cart.js';
 *
 *     await addProductToCart( 42, 3, {
 *         onSuccess: ( data ) => { ... },
 *         onError:   ( error ) => { ... },
 *     });
 *
 * ============================================================
 * EXPORTS
 * ============================================================
 *
 * addProductToCart( productId, amount, options ) — add a product
 */

'use strict';

import { arsApi }        from '../core/api.js';
import { arsCartWidget } from './cart-widget.js';

/**
 * Adds a product to the cart.
 *
 * On a successful response updates the cart widget in the header and calls
 * onSuccess with the full response data. On error calls onError
 * with an Error object.
 *
 * @param  {string|number} productId — product ID
 * @param  {string|number} amount    — quantity
 * @param  {Object}        [options] — callbacks
 * @param  {Function}      [options.onSuccess] — ( data ) => void
 * @param  {Function}      [options.onError]   — ( error ) => void
 * @return {Promise<Object|null>} Response data or null on error
 */
export const addProductToCart = async ( productId, amount, options = {} ) => {

    const { onSuccess, onError } = options;

    try {

        const data = await arsApi.postJson( 'addProducts_shoppingCart', {
            productID:     productId,
            amountProduct: amount,
        });

        // Update the cart widget in the header
        arsCartWidget.update({
            productAmountCart: data.productAmountCart,
            totalPrice:        data.totalPrice,
        });

        if ( typeof onSuccess === 'function' ) {
            onSuccess( data );
        }

        return data;

    } catch ( error ) {

        if ( typeof onError === 'function' ) {
            onError( error );
        }

        return null;
    }
};
