/**
 * ArgonShop — AJAX client for admin-ajax.php.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * A single entry point for all frontend AJAX requests. Replaces
 * dozens of scattered $.ajax({...}) calls from the old code.
 *
 * Automatically:
 *   - uses the URL from config.url
 *   - adds action and nonce to the request body
 *   - does not add the nonce to actions from config.skipNonceActions
 *   - parses JSON and updates the nonce if the server returned newNonce
 *   - turns network and HTTP errors into a single form (throw Error)
 *
 * ============================================================
 * EXPORTS
 * ============================================================
 *
 * ArsApiClient  — client class
 * arsApi        — ready singleton for use in all modules
 *
 * ============================================================
 * METHODS
 * ============================================================
 *
 * post( action, data )              — POST, returns the response text
 * postJson( action, data )          — POST, returns the parsed JSON
 * postFormData( action, formData )  — POST with FormData (for files)
 * setNonce( nonce )                 — update the nonce manually
 * getNonce()                        — get the current nonce
 *
 * ============================================================
 * EXAMPLES
 * ============================================================
 *
 *     import { arsApi } from '../core/api.js';
 *
 *     // Text response
 *     const text = await arsApi.post( 'deleteProducts_shoppingCart', {
 *         productID: 42,
 *     });
 *
 *     // JSON response
 *     const data = await arsApi.postJson( 'amountProducts_shoppingCart', {
 *         productID: 42,
 *         productAmount: 3,
 *     });
 *
 *     // Form with files
 *     const result = await arsApi.postFormData( 'submitCart_shoppingCart', formData );
 */

'use strict';

import { config } from './config.js';
import { applyNonce } from './nonce.js';

/**
 * ArgonShop AJAX client.
 */
export class ArsApiClient {

    constructor() {
        // There is no nonce cache here — config is read every time
        // so as not to diverge from the interceptor (nonce.js).
    }

    /**
     * admin-ajax.php handler URL.
     *
     * @return {string}
     */
    get url() {
        return config.url;
    }

    /**
     * Current nonce.
     *
     * @return {string}
     */
    getNonce() {
        return config.nonce;
    }

    /**
     * Updates the nonce manually.
     * Used if a module gets the nonce not from the XHR interception.
     *
     * @param {string} nonce — new nonce
     * @return {void}
     */
    setNonce( nonce ) {
        applyNonce( nonce );
    }

    /**
     * Checks whether the nonce should be added to this action.
     *
     * @param  {string} action — AJAX action name
     * @return {boolean}
     */
    shouldSkipNonce( action ) {
        return config.skipNonceActions.indexOf( action ) !== -1;
    }

    /**
     * Builds the request body as URLSearchParams.
     *
     * @param  {string} action — AJAX action name
     * @param  {Object} data   — request data
     * @return {URLSearchParams}
     */
    buildBody( action, data = {} ) {

        const body = new URLSearchParams();

        body.append( 'action', action );

        if ( ! this.shouldSkipNonce( action ) && config.nonce ) {
            body.append( 'nonce', config.nonce );
        }

        for ( const [ key, value ] of Object.entries( data ) ) {

            if ( value === undefined || value === null ) {
                continue;
            }

            if ( Array.isArray( value ) ) {
                value.forEach( item => body.append( `${key}[]`, item ) );
                continue;
            }

            body.append( key, value );
        }

        return body;
    }

    /**
     * POST request, returns the raw response text.
     *
     * @param  {string} action — AJAX action name
     * @param  {Object} [data={}] — request data
     * @return {Promise<string>}
     *
     * @throws {Error} on a network error or an HTTP status outside 2xx
     */
    async post( action, data = {} ) {

        const response = await fetch( config.url, {
            method:      'POST',
            credentials: 'same-origin',
            headers:     { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body:        this.buildBody( action, data ),
        });

        if ( ! response.ok ) {
            throw new Error( `HTTP ${response.status} for the "${action}" request` );
        }

        const text = await response.text();

        // Check newNonce manually — in case the XHR interceptor
        // did not fire (for example, the server returned JSON not via XHR).
        this.tryApplyNonceFromText( text );

        return text;
    }

    /**
     * POST request, returns the parsed JSON.
     *
     * If the response is not valid JSON — returns the object
     * { raw: <response text> } so that the calling code can decide
     * what to do. This is better than throwing an exception: some
     * WordPress AJAX handlers return plain strings.
     *
     * @param  {string} action — AJAX action name
     * @param  {Object} [data={}] — request data
     * @return {Promise<Object>}
     *
     * @throws {Error} on a network error or an HTTP status outside 2xx
     */
    async postJson( action, data = {} ) {

        const text = await this.post( action, data );

        try {
            return JSON.parse( text );
        } catch {
            return { raw: text };
        }
    }

    /**
     * POST request with FormData (for forms with files).
     *
     * @param  {string}   action   — AJAX action name
     * @param  {FormData} formData — ready FormData with the form data
     * @return {Promise<Object>} Parsed JSON response
     *
     * @throws {Error} on a network error or an HTTP status outside 2xx
     */
    async postFormData( action, formData ) {

        formData.append( 'action', action );

        if ( ! this.shouldSkipNonce( action ) && config.nonce ) {
            formData.append( 'nonce', config.nonce );
        }

        const response = await fetch( config.url, {
            method:      'POST',
            credentials: 'same-origin',
            body:        formData,
        });

        if ( ! response.ok ) {
            throw new Error( `HTTP ${response.status} for the "${action}" request` );
        }

        const text = await response.text();

        this.tryApplyNonceFromText( text );

        try {
            return JSON.parse( text );
        } catch {
            return { raw: text };
        }
    }

    /**
     * Looks for newNonce in the response text and applies it.
     *
     * @param  {string} text — response text
     * @return {void}
     */
    tryApplyNonceFromText( text ) {

        if ( ! text || text.indexOf( 'newNonce' ) === -1 ) {
            return;
        }

        try {

            const data = JSON.parse( text );

            if ( data && typeof data.newNonce === 'string' ) {
                applyNonce( data.newNonce );
            }

        } catch {
            // Not JSON — ignore
        }
    }
}

/**
 * Ready AJAX client singleton.
 *
 * Use it in all modules:
 *
 *     import { arsApi } from '../core/api.js';
 *     const data = await arsApi.postJson( 'some_action', { ... } );
 *
 * @type {ArsApiClient}
 */
export const arsApi = new ArsApiClient();
