/**
 * ArgonShop — product card buttons.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * Handles the add-to-cart buttons on product cards
 * in the catalog, on the home page, in the sidebar and in the search
 * results.
 *
 * Product cards are shown in several places of the site and contain
 * two buttons:
 *
 *   .card-inBascet — "Add to cart": adds without navigating away.
 *   .card-buy      — "Buy": adds and goes to the cart.
 *
 * After a successful add via "Add to cart" the buttons block
 * is hidden and the "Product added to cart" block
 * (.card-alreadyAdded) with a link to the cart page
 * is shown instead.
 *
 * ============================================================
 * DOM ELEMENTS
 * ============================================================
 *
 * .card-inBascet[data-productid]      — "Add to cart" button.
 * .card-buy[data-productid]           — "Buy" button.
 * .block-cardProductBascet            — block with both buttons.
 * .card-alreadyAdded                  — "Product added to cart" block.
 * .card-addProductError               — error block on the card.
 *
 * ============================================================
 * USAGE
 * ============================================================
 *
 *     import { arsCardProduct } from './modules/card-product.js';
 *     arsCardProduct.init();
 *
 * The module is safe to call on any page: if there are no cards
 * with buttons, the handlers do not fire.
 */

'use strict';

import { addProductToCart } from './add-to-cart.js';
import { t } from '../core/config.js';
import { delegate } from '../../shared/dom.js';

/**
 * Product card buttons module.
 */
class ArsCardProduct {

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

        this.#initialized = true;

        this.#bindAddToCart();
        this.#bindBuyNow();
    }

    /**
     * The "Add to cart" button on a card.
     *
     * @return {void}
     */
    #bindAddToCart() {

        delegate( document, 'click', '.card-inBascet', async ( event, button ) => {

            const productId = button.dataset.productid;

            await addProductToCart( productId, 1, {
                onSuccess: () => this.#showAdded( button ),
                onError:   ( error ) => this.#showError( button, error ),
            });
        });
    }

    /**
     * The "Buy" button on a card.
     *
     * @return {void}
     */
    #bindBuyNow() {

        delegate( document, 'click', '.card-buy', async ( event, button ) => {

            const productId = button.dataset.productid;

            await addProductToCart( productId, 1, {
                onSuccess: () => this.#goToCart(),
                onError:   ( error ) => this.#showError( button, error ),
            });
        });
    }

    /**
     * Shows the "Product added to cart" block.
     *
     * @param  {Element} button — clicked button
     * @return {void}
     */
    #showAdded( button ) {

        const buttonsBlock = button.closest( '.block-cardProductBascet' );

        if ( ! buttonsBlock ) {
            return;
        }

        const errorBlock  = buttonsBlock.nextElementSibling;
        const addedBlock  = buttonsBlock.parentElement
            ? buttonsBlock.parentElement.querySelector( '.card-alreadyAdded' )
            : null;

        if ( errorBlock && errorBlock.classList.contains( 'card-addProductError' ) ) {
            errorBlock.style.display = 'none';
            errorBlock.textContent = '';
        }

        buttonsBlock.style.display = 'none';

        if ( addedBlock ) {
            addedBlock.style.display = 'block';
        }
    }

    /**
     * Shows the error block on the card.
     *
     * @param  {Element} button — clicked button
     * @param  {Error}   error  — error object
     * @return {void}
     */
    #showError( button, error ) {

        const buttonsBlock = button.closest( '.block-cardProductBascet' );

        if ( ! buttonsBlock ) {
            return;
        }

        const errorBlock = buttonsBlock.parentElement
            ? buttonsBlock.parentElement.querySelector( '.card-addProductError' )
            : null;

        if ( errorBlock ) {
            errorBlock.textContent = t( 'requestError' );
            errorBlock.style.display = 'block';
        }
    }

    /**
     * Going to the cart page.
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
 * Ready singleton of the product card buttons module.
 *
 * @type {ArsCardProduct}
 */
export const arsCardProduct = new ArsCardProduct();