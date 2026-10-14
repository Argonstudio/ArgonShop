/**
 * ArgonShop — product page module.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * Manages the interactive elements of the product page:
 *
 *   1. Changing the product quantity (field + +/- buttons).
 *   2. Recalculating the wholesale price when the quantity changes.
 *   3. "Add to cart" button — adds the product without navigating away.
 *   4. "Buy" button — adds the product and opens the cart.
 *   5. Adding the product to the view history (see history.js).
 *
 * ============================================================
 * DOM ELEMENTS
 * ============================================================
 *
 * #amountProduct           — quantity field.
 * .plusProduct-Page        — increase quantity button.
 * .minusProduct-Page       — decrease quantity button.
 *
 * #addShoppingCart         — "Add to cart" button (data-productid).
 * #productPageCartBuy      — "Buy" button (data-productid).
 *
 * #howManyProducts         — "N items added" block.
 * #howManyProducts span    — number of product units in the cart.
 * #addProductError         — add-to-cart error block.
 *
 * .totalPrice              — total amount container.
 * .totalPrice span         — total amount for the selected quantity.
 * #basePrice               — unit price (without wholesale discount).
 *
 * ============================================================
 * AJAX ACTION
 * ============================================================
 *
 * wholesalePrice_shoppingCart (type=get)
 *   Accepts: productID, amountProduct.
 *   Returns: a number (total price) or the string "none".
 *
 * ============================================================
 * USAGE
 * ============================================================
 *
 *     import { arsProduct } from './modules/product.js';
 *     arsProduct.init();
 *
 * The module is safe to call on any page: if the page has
 * no #addShoppingCart, nothing is executed.
 */

'use strict';

import { arsApi }              from '../core/api.js';
import { t }            from '../core/config.js';
import { qs, delegate }        from '../../shared/dom.js';
import { parseNumber }         from '../../shared/format.js';
import { addProductToCart }    from './add-to-cart.js';
import { arsHistory }          from './history.js';

/**
 * Product page module.
 */
class ArsProduct {

    /** @type {boolean} */
    #initialized = false;

    /**
     * Initialization. Idempotent.
     *
     * @return {void}
     */
    init() {

        if ( this.#initialized ) {
            return;
        }

        // The product page always has a quantity field.
        // If it is missing, this is not a product page — exit.
        if ( ! qs( '#amountProduct' ) ) {
            return;
        }

        this.#initialized = true;

        this.#bindAmountInput();
        this.#bindPlusMinus();
        this.#bindAddToCart();
        this.#bindBuyNow();

        this.#addToHistory();
        this.#updateWholesalePrice();
    }

    // ============================================================
    // EVENT HANDLERS
    // ============================================================

    /**
     * Changing the quantity via the input field.
     *
     * @return {void}
     */
    #bindAmountInput() {

        const input = qs( '#amountProduct' );

        if ( ! input ) {
            return;
        }

        input.addEventListener( 'input', () => {

            let amount = parseInt( input.value, 10 );

            if ( ! amount || amount <= 0 || Number.isNaN( amount ) ) {
                amount = 1;
                input.value = 1;
            }

            this.#updateWholesalePrice();
        });
    }

    /**
     * +/- buttons.
     *
     * @return {void}
     */
    #bindPlusMinus() {

        delegate( document, 'click', '.plusProduct-Page', () => {

            const input = qs( '#amountProduct' );

            if ( ! input ) {
                return;
            }

            input.value = ( parseInt( input.value, 10 ) || 0 ) + 1;
            input.dispatchEvent( new Event( 'input', { bubbles: true } ) );
        });

        delegate( document, 'click', '.minusProduct-Page', () => {

            const input = qs( '#amountProduct' );

            if ( ! input ) {
                return;
            }

            const current = parseInt( input.value, 10 ) || 0;

            if ( current > 1 ) {
                input.value = current - 1;
                input.dispatchEvent( new Event( 'input', { bubbles: true } ) );
            }
        });
    }

    /**
     * "Add to cart" button.
     *
     * @return {void}
     */
    #bindAddToCart() {

        delegate( document, 'click', '#addShoppingCart', async ( event, button ) => {

            const productId = button.dataset.productid;
            const input     = qs( '#amountProduct' );
            const amount    = input ? ( parseInt( input.value, 10 ) || 1 ) : 1;

            await addProductToCart( productId, amount, {
                onSuccess: ( data ) => this.#showAdded( productId, data ),
                onError:   () => this.#showAddError( t( 'requestError' ) ),
            });
        });
    }

    /**
     * "Buy" button.
     *
     * @return {void}
     */
    #bindBuyNow() {

        delegate( document, 'click', '#productPageCartBuy', async ( event, button ) => {

            const productId = button.dataset.productid;
            const input     = qs( '#amountProduct' );
            const amount    = input ? ( parseInt( input.value, 10 ) || 1 ) : 1;

            await addProductToCart( productId, amount, {
                onSuccess: () => this.#goToCart(),
                onError:   () => this.#showAddError( t( 'requestError' ) ),
            });
        });
    }

    // ============================================================
    // WHOLESALE PRICE
    // ============================================================

    /**
     * Requests the current price for the selected quantity from the server.
     *
     * If there are no discounts, the server returns "none" — then the total
     * is calculated locally as basePrice × amount.
     *
     * @return {Promise<void>}
     */
    async #updateWholesalePrice() {

        const input = qs( '#amountProduct' );
        const button = qs( '#addShoppingCart' );

        if ( ! input || ! button ) {
            return;
        }

        const productId = button.dataset.productid;
        const amount    = parseInt( input.value, 10 ) || 0;

        const totalPriceBlock = qs( '.totalPrice' );
        const totalPriceSpan  = qs( '.totalPrice span' );

        if ( ! totalPriceBlock || ! totalPriceSpan ) {
            return;
        }

        if ( amount <= 0 ) {
            totalPriceBlock.style.display = 'none';
            return;
        }

        try {

            const response = await arsApi.post( 'wholesalePrice_shoppingCart', {
                type:          'get',
                productID:     productId,
                amountProduct: amount,
            });

            let totalPrice;

            if ( response === 'none' || response === '' ) {

                // No discounts — calculate locally
                const basePriceSpan = qs( '#basePrice' );
                const basePrice = parseNumber( basePriceSpan ? basePriceSpan.textContent : 0 );

                totalPrice = basePrice * amount;

            } else {

                totalPrice = parseNumber( response );
            }

            totalPriceSpan.textContent = Number.isInteger( totalPrice )
                ? String( totalPrice )
                : totalPrice.toFixed( 2 );

            totalPriceBlock.style.display = 'block';

        } catch {
            totalPriceSpan.textContent = t( 'serverError' );
        }
    }

    // ============================================================
    // HELPERS
    // ============================================================

    /**
     * Adds the product to the view history.
     *
     * @return {void}
     */
    #addToHistory() {

        const button = qs( '#addShoppingCart' );

        if ( ! button ) {
            return;
        }

        const productId = button.dataset.productid;

        if ( productId ) {
            arsHistory.add( productId );
        }
    }

    /**
     * Shows the "N items added" block.
     *
     * @param  {string} productId — product ID
     * @param  {Object} data      — server response
     * @return {void}
     */
    #showAdded( productId, data ) {

        const block     = qs( '#howManyProducts' );
        const blockSpan = qs( '#howManyProducts span' );
        const errorBlock = qs( '#addProductError' );

        if ( block && blockSpan ) {
            blockSpan.textContent = String( data.amountProduct );
            block.style.display = 'block';
        }

        if ( errorBlock ) {
            errorBlock.style.display = 'none';
            errorBlock.textContent = '';
        }
    }

    /**
     * Shows the add-to-cart error block.
     *
     * @param  {string} message — error text
     * @return {void}
     */
    #showAddError( message ) {

        const errorBlock = qs( '#addProductError' );

        if ( ! errorBlock ) {
            return;
        }

        errorBlock.textContent = message;
        errorBlock.style.display = 'block';
    }

    /**
     * Navigates to the cart page.
     *
     * @return {void}
     */
    #goToCart() {

        const cartUrl = window.arsWpAjax && window.arsWpAjax.cartUrl
            ? window.arsWpAjax.cartUrl
            : '';

        if ( cartUrl ) {
            window.location.href = cartUrl;
        }
    }
}

/**
 * Ready-made singleton of the product page module.
 *
 * @type {ArsProduct}
 */
export const arsProduct = new ArsProduct();