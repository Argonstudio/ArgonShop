/**
 * ArgonShop — global AJAX response interceptor for updating the nonce.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * WordPress sessions and nonces are invalidated in two situations:
 *
 * 1. wp_set_password() — when the password is changed in the account,
 *    all active user sessions are reset and the old
 *    nonce becomes invalid.
 *
 * 2. wp_signon() — when a user registers from the order form,
 *    the session is recreated as well.
 *
 * If the user keeps working on the page after that
 * (for example, opens the cart or changes the order filter), their
 * AJAX requests will be rejected by the server because the nonce is stale.
 *
 * Solution: the PHP handlers return a new nonce in the response
 * (the newNonce field). The interceptor reads this field and updates
 * the nonce in config and in arsApi.
 *
 * ============================================================
 * HOW IT WORKS
 * ============================================================
 *
 * XMLHttpRequest.prototype.send is intercepted — this catches all
 * AJAX requests of the page, including those made via jQuery and
 * third-party libraries. On every successful response the text
 * is parsed as JSON, and if it contains newNonce —
 * the value is applied.
 *
 * This interception is global and needed once per page. Do not
 * try to call initNonceInterceptor() again.
 *
 * IMPORTANT: fetch() is covered too, because when fetch is used
 * on top of XMLHttpRequest (in browsers without native fetch)
 * the chain is kept. In modern browsers fetch does not go
 * through XHR, but all our own requests go through
 * ArsApiClient, which checks newNonce in the response itself.
 *
 * ============================================================
 * EXPORTS
 * ============================================================
 *
 * initNonceInterceptor() — enables the interceptor (called once)
 *
 * ============================================================
 * EXAMPLE
 * ============================================================
 *
 *     import { initNonceInterceptor } from './core/nonce.js';
 *
 *     initNonceInterceptor();
 */

'use strict';

import { config } from './config.js';

/**
 * Updates the nonce in config and everywhere it is cached.
 *
 * The function deliberately does not import arsApi from api.js to
 * avoid a circular dependency (api.js can also update the
 * nonce via its setNonce method). So only config is changed here,
 * and ArsApiClient reads the nonce from config on every request.
 *
 * @param {string} newNonce — new nonce value from the server
 * @return {void}
 */
const applyNonce = ( newNonce ) => {

    if ( typeof newNonce !== 'string' || newNonce === '' ) {
        return;
    }

    if ( config.nonce === newNonce ) {
        return;
    }

    config.nonce = newNonce;

    // An event for those who need to know about the update
    // (for example, the account module may show a notice).
    window.dispatchEvent( new CustomEvent( 'ars:nonce-updated', {
        detail: { nonce: newNonce },
    }));
};

/**
 * Tries to extract newNonce from the response text.
 *
 * The response can be:
 *   - a JSON object with the newNonce field
 *   - plain text (then there is nowhere to look for newNonce)
 *
 * @param {string} responseText — raw response text
 * @return {void}
 */
const tryExtractNonce = ( responseText ) => {

    if ( ! responseText || typeof responseText !== 'string' ) {
        return;
    }

    // Quick check: searching for "newNonce" in the text is cheaper than
    // trying to parse JSON on every response (and there can be many).
    if ( responseText.indexOf( 'newNonce' ) === -1 ) {
        return;
    }

    try {

        const data = JSON.parse( responseText );

        if ( data && typeof data.newNonce === 'string' ) {
            applyNonce( data.newNonce );
        }

    }catch(e) {
        // Not JSON — do nothing
    }
};

/**
 * Installs the interceptor on XMLHttpRequest.
 *
 * Idempotent: a repeated call does nothing.
 *
 * @return {void}
 */
export const initNonceInterceptor = () => {

    if ( typeof XMLHttpRequest === 'undefined' ) {
        return;
    }

    // Protection against double initialization
    if ( XMLHttpRequest.prototype.__arsNoncePatched ) {
        return;
    }

    const originalSend = XMLHttpRequest.prototype.send;

    XMLHttpRequest.prototype.send = function( ...args ) {

        this.addEventListener( 'load', () => {

            // Read only successful responses (2xx) and redirects
            // with a body — the rest are not interesting
            if ( this.status < 200 || this.status >= 400 ) {
                return;
            }

            tryExtractNonce( this.responseText );
        });

        return originalSend.apply( this, args );
    };

    XMLHttpRequest.prototype.__arsNoncePatched = true;
};

/**
 * Exports applyNonce for cases when a module gets the nonce
 * not from the XHR interception but from its own fetch response.
 *
 * Example: ArsApiClient.postJson() parses the JSON itself and can
 * call applyNonce directly without waiting for the interceptor.
 *
 * @param {string} newNonce — new nonce value
 * @return {void}
 */
export { applyNonce };