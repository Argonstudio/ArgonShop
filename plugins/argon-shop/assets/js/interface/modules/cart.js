/**
 * ArgonShop — shopping cart module.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * Controls the cart page and the cart widget:
 *
 *   1. Changing the product quantity (input field and +/- buttons).
 *   2. Removing a single product.
 *   3. Clearing the whole cart.
 *   4. Recalculating prices, weight and totals after every change.
 *   5. Submitting the checkout form.
 *
 * All actions go through the plugin AJAX handlers
 * (see includes/interface/shoppingCart/shoppingCart.php).
 *
 * ============================================================
 * AJAX ACTIONS
 * ============================================================
 *
 * amountProducts_shoppingCart
 *   Changing the quantity of one product.
 *   Accepts: productID, productAmount.
 *   Returns JSON: { weight, actualPrices, productsShoppingCart }
 *
 * deleteProducts_shoppingCart
 *   Removing one product.
 *   Accepts: productID.
 *   Returns the actualPrices JSON or the string "false" if the cart is empty.
 *
 * deleteAllProducts_shoppingCart
 *   Clearing the cart completely. The nonce is sent too (the server checks it for all actions).
 *   Returns nothing.
 *
 * submitCart_shoppingCart
 *   Submitting the checkout form.
 *   Accepts FormData: typeForm, required, productsCart, form fields.
 *   Returns JSON: { type, typeForm, message }.
 *
 * ============================================================
 * DOM ELEMENTS
 * ============================================================
 *
 * .shoppingCartAmountProduct[data-productid]
 *   Product quantity field.
 *
 * .plusProduct[data-productid] / .minusProduct[data-productid]
 *   Quantity increase and decrease buttons.
 *
 * .deleteProduct[data-productid]
 *   Remove button for one product.
 *
 * .deleteAllProducts
 *   Button for clearing the whole cart.
 *
 * .blockCartProduct[data-productid]
 *   Container of one product in the cart.
 *
 * .productPrice span           — product unit price.
 * .amountProductPrice span     — total price for the quantity of this product.
 * .amountProductWeight span    — weight for the quantity of this product.
 * .shoppingCartError           — error block on the product card.
 *
 * .cartTotalPrice span         — total of the whole cart.
 * .cartTotalWeight span        — total weight of the whole cart.
 * .cartTotalWeight             — total weight container (controls visibility).
 *
 * #shoppingCart                — cart container (for the "Cart is empty" message).
 * .errorDeleteProducts         — error block for clearing the cart.
 *
 * .form-cart[data-type]        — checkout form.
 * .submitError span            — error block for the form submission.
 *
 * ============================================================
 * USAGE
 * ============================================================
 *
 *     import { arsCart } from './modules/cart.js';
 *     arsCart.init();
 *
 * The module is safe to call on any page: if there are no cart
 * elements, the handlers do not fire.
 */

'use strict';

import { arsApi }            from '../core/api.js';
import { t }               from '../core/config.js';
import { qs, qsa, delegate } from '../../shared/dom.js';
import { formatPrice, formatWeight, parseNumber } from '../../shared/format.js';

/**
 * Shopping cart module.
 */
class ArsShoppingCart {

    /**
     * The current unfinished quantity change request.
     * Used to cancel the previous request if the user
     * changes the quantity quickly — otherwise the responses arrive in a different
     * order and overwrite the data.
     *
     * @type {AbortController|null}
     */
    #amountController = null;

    /**
     * Handler initialization. A repeated call
     * does nothing.
     *
     * @return {void}
     */
    init() {

        if ( this.#initialized ) {
            return;
        }

        this.#initialized = true;

        this.#bindAmountChange();
        this.#bindPlusMinus();
        this.#bindDeleteProduct();
        this.#bindDeleteAll();
        this.#bindFormSubmit();
    }

    /** @type {boolean} */
    #initialized = false;

    // ============================================================
    // EVENT HANDLERS
    // ============================================================

    /**
     * Changing the quantity via the input field.
     *
     * On an invalid value (empty, zero, not a number) resets it
     * to 1 and sends it to the server.
     *
     * @return {void}
     */
    #bindAmountChange() {

        delegate( document, 'input', '.shoppingCartAmountProduct', ( event, input ) => {

            const productId = input.dataset.productid;

            let amount = parseInt( input.value, 10 );

            if ( ! amount || amount <= 0 || Number.isNaN( amount ) ) {
                amount = 1;
                input.value = 1;
            }

            this.#requestAmountChange( productId, amount );
        });
    }

    /**
     * Quantity increase and decrease buttons.
     *
     * After the field value is changed manually the input event
     * is dispatched — so the same handler fires as on manual input.
     *
     * @return {void}
     */
    #bindPlusMinus() {

        delegate( document, 'click', '.plusProduct', ( event, button ) => {

            const productId = button.dataset.productid;
            const input = qs( `.shoppingCartAmountProduct[data-productid="${productId}"]` );

            if ( ! input ) {
                return;
            }

            input.value = ( parseInt( input.value, 10 ) || 0 ) + 1;
            input.dispatchEvent( new Event( 'input', { bubbles: true } ) );
        });

        delegate( document, 'click', '.minusProduct', ( event, button ) => {

            const productId = button.dataset.productid;
            const input = qs( `.shoppingCartAmountProduct[data-productid="${productId}"]` );

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
     * Removing one product.
     *
     * @return {void}
     */
    #bindDeleteProduct() {

        delegate( document, 'click', '.deleteProduct', async ( event, button ) => {

            const productId = button.dataset.productid;
            const parent    = button.closest( `.blockCartProduct[data-productid="${productId}"]` );

            try {

                const text = await arsApi.post( 'deleteProducts_shoppingCart', {
                    productID: productId,
                });

                if ( text === 'false' || text === '0' ) {

                    const cart = qs( '#shoppingCart' );

                    if ( cart ) {
                        cart.textContent = t( 'cartEmpty' );
                    }

                    return;
                }

                const actualPrices = JSON.parse( text );

                if ( parent ) {
                    parent.remove();
                }

                this.#recountCart( actualPrices );

            } catch {
                this.#showError( productId, t( 'productNotRemoved' ) );
            }
        });
    }

    /**
     * Clearing the whole cart.
     *
     * @return {void}
     */
    #bindDeleteAll() {

        delegate( document, 'click', '.deleteAllProducts', async () => {

            try {

                await arsApi.post( 'deleteAllProducts_shoppingCart' );

                const cart = qs( '#shoppingCart' );

                if ( cart ) {
                    cart.textContent = t( 'cartEmpty' );
                }

                const errorBlock = qs( '.errorDeleteProducts' );

                if ( errorBlock ) {
                    errorBlock.style.display = 'none';
                }

            } catch {

                const errorBlock = qs( '.errorDeleteProducts' );

                if ( errorBlock ) {
                    errorBlock.textContent = t( 'productsNotRemoved' );
                    errorBlock.style.display = 'block';
                }
            }
        });
    }

    /**
     * Submitting the checkout form.
     *
     * In addition to the form data it collects:
     *   - typeForm — form type (quick / person / legalPerson)
     *   - productsCart — current quantity of all cart products
     *   - required — map of required form fields
     *
     * @return {void}
     */
    #bindFormSubmit() {

        delegate( document, 'submit', '.form-cart', ( event, form ) => {

            event.preventDefault();

            const formData = new FormData( form );

            // Current quantity of all cart products.
            const productsCart = {};

            qsa( '.shoppingCartAmountProduct' ).forEach( input => {
                productsCart[ input.dataset.productid ] = {
                    amountProduct: input.value,
                };
            });

            // Map of required form fields.
            const required = {};

            qsa( '[required]', form ).forEach( field => {
                if ( field.name ) {
                    required[ field.name ] = true;
                }
            });

            formData.append( 'typeForm', form.dataset.type );
            formData.append( 'required', JSON.stringify( required ) );
            formData.append( 'productsCart', JSON.stringify( productsCart ) );

            this.#submitCart( formData );
        });
    }

    // ============================================================
    // AJAX OPERATIONS
    // ============================================================

    /**
     * Sends the quantity change request for one product.
     *
     * The previous unfinished request is cancelled — this rules out
     * the situation when responses arrive in a different order and spoil
     * the final state.
     *
     * @param  {string} productId — product ID
     * @param  {number} amount    — new quantity
     * @return {Promise<void>}
     */
    async #requestAmountChange( productId, amount ) {

        if ( this.#amountController ) {
            this.#amountController.abort();
            this.#amountController = null;
        }

        const controller = new AbortController();
        this.#amountController = controller;

        try {

            const body = new URLSearchParams({
                action:        'amountProducts_shoppingCart',
                productID:     productId,
                productAmount: amount,
                nonce:         arsApi.getNonce(),
            });

            const response = await fetch( arsApi.url, {
                method:      'POST',
                credentials: 'same-origin',
                headers:     { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                body,
                signal:      controller.signal,
            });

            const text = await response.text();
            const data = JSON.parse( text );

            this.#amountController = null;

            this.#updateProductWeight( productId, data.weight, amount );
            this.#recountCart( data.actualPrices );

            this.#hideError( productId );

        } catch ( error ) {

            if ( error.name === 'AbortError' ) {
                return;
            }

            this.#amountController = null;

            this.#showError( productId, t( 'changesNotAccepted' ) );
        }
    }

    /**
     * Submitting the checkout form.
     *
     * @param  {FormData} formData — form data
     * @return {Promise<void>}
     */
    async #submitCart( formData ) {

        try {

            const data = await arsApi.postFormData( 'submitCart_shoppingCart', formData );

            const form = qs( `.form-cart${data.typeForm}` );

            if ( data.type === 'error' ) {
                this.#showFormError( form, data.message );
                return;
            }

            if ( data.type === 'success' ) {

                this.#hideFormError( form );

                const cart = qs( '#shoppingCart' );

                if ( cart ) {
                    cart.innerHTML = data.message;
                }
                
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

        } catch {
            this.#showFormError(
                qs( '.form-cart' ),
                t( 'orderNotSent' )
            );
        }
    }

    // ============================================================
    // RECALCULATING PRICES AND WEIGHT
    // ============================================================

    /**
     * Updates the weight of one product on the page.
     *
     * The server sends the weight of one unit of the product, here
     * it is multiplied by the quantity and written into all
     * .amountProductWeight span elements inside the card.
     *
     * @param  {string} productId — product ID
     * @param  {number} weight    — weight of one unit
     * @param  {number} amount    — quantity
     * @return {void}
     */
    #updateProductWeight( productId, weight, amount ) {

        const block = qs( `.blockCartProduct[data-productid="${productId}"]` );

        if ( ! block ) {
            return;
        }

        const totalWeight = ( parseNumber( weight ) || 0 ) * amount;

        qsa( '.amountProductWeight span', block ).forEach( span => {
            span.textContent = formatWeight( totalWeight );
        });
    }

    /**
     * Recalculates the prices and weights of the whole cart.
     *
     * @param  {Object<string, number>} actualPrices — current prices by product ID
     * @return {void}
     */
    #recountCart( actualPrices ) {

        let totalPrice  = 0;
        let totalWeight = 0;

        for ( const [ productId, price ] of Object.entries( actualPrices ) ) {

            const block = qs( `.blockCartProduct[data-productid="${productId}"]` );

            if ( ! block ) {
                continue;
            }

            const amountInput = qs( '.shoppingCartAmountProduct', block );
            const amount = parseNumber( amountInput ? amountInput.value : 0 ) || 0;

            // Update the unit price (all span elements, including the mobile block).
            qsa( '.productPrice span', block ).forEach( span => {
                span.textContent = formatPrice( price );
            });

            // Update the total price for the quantity of this product.
            qsa( '.amountProductPrice span', block ).forEach( span => {
                span.textContent = formatPrice( price * amount );
            });

            // The weight for the product quantity is already written to the span, sum it up.
            const weightSpan = qs( '.amountProductWeight span', block );
            const weight = parseNumber( weightSpan ? weightSpan.textContent : 0 ) || 0;

            if ( weight ) {
                totalWeight += weight;
            }

            totalPrice += price * amount;
        }

        this.#updateCartTotals( totalPrice, totalWeight );
    }

    /**
     * Updates the cart totals in the interface.
     *
     * @param  {number} totalPrice  — total
     * @param  {number} totalWeight — total weight
     * @return {void}
     */
    #updateCartTotals( totalPrice, totalWeight ) {

        const totalPriceSpan  = qs( '.cartTotalPrice span' );
        const totalWeightSpan = qs( '.cartTotalWeight span' );
        const totalWeightBlock = qs( '.cartTotalWeight' );

        if ( totalPriceSpan ) {
            totalPriceSpan.textContent = formatPrice( totalPrice );
        }

        if ( totalWeight && totalWeightBlock && totalWeightSpan ) {
            totalWeightSpan.textContent = formatWeight( totalWeight );
            totalWeightBlock.style.display = 'block';
        } else if ( totalWeightBlock ) {
            totalWeightBlock.style.display = 'none';
        }
    }

    // ============================================================
    // ERROR MESSAGES
    // ============================================================

    /**
     * Shows an error message on the product card.
     *
     * @param  {string} productId — product ID
     * @param  {string} message   — error text
     * @return {void}
     */
    #showError( productId, message ) {

        const block = qs( `.blockCartProduct[data-productid="${productId}"]` );

        if ( ! block ) {
            return;
        }

        const errorBlock = qs( '.shoppingCartError', block );

        if ( ! errorBlock ) {
            return;
        }

        const span = qs( 'span', errorBlock );

        if ( span ) {
            span.textContent = message;
        }

        errorBlock.style.display = 'block';
    }

    /**
     * Hides the error message on the product card.
     *
     * @param  {string} productId — product ID
     * @return {void}
     */
    #hideError( productId ) {

        const block = qs( `.blockCartProduct[data-productid="${productId}"]` );

        if ( ! block ) {
            return;
        }

        const errorBlock = qs( '.shoppingCartError', block );

        if ( ! errorBlock ) {
            return;
        }

        const span = qs( 'span', errorBlock );

        if ( span ) {
            span.textContent = '';
        }

        errorBlock.style.display = 'none';
    }

    /**
     * Shows an error message under the form.
     *
     * @param  {Element|null} form    — form
     * @param  {string}       message — error text
     * @return {void}
     */
    #showFormError( form, message ) {

        if ( ! form ) {
            return;
        }

        const errorBlock = qs( '.submitError', form );

        if ( ! errorBlock ) {
            return;
        }

        const span = qs( 'span', errorBlock );

        if ( span ) {
            span.textContent = message;
        }

        errorBlock.style.display = 'block';
    }

    /**
     * Hides the error message under the form.
     *
     * @param  {Element|null} form — form
     * @return {void}
     */
    #hideFormError( form ) {

        if ( ! form ) {
            return;
        }

        const errorBlock = qs( '.submitError', form );

        if ( errorBlock ) {
            errorBlock.style.display = 'none';
        }
    }
}

/**
 * Ready singleton of the cart module.
 *
 * @type {ArsShoppingCart}
 */
export const arsCart = new ArsShoppingCart();