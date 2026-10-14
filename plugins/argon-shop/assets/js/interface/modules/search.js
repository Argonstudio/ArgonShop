/**
 * ArgonShop — AJAX site search.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * Manages the AJAX search form in the site header and on the search
 * results page. Sends a request while the user types,
 * shows results in a dropdown block and hides it
 * on a click outside the form.
 *
 * ============================================================
 * DOM ELEMENTS
 * ============================================================
 *
 * .asInputSearchForm
 *   Search input. May be present on the page several
 *   times (header, search page).
 *
 * Input attributes:
 *   data-ajax         — JSON string with search parameters
 *                       (productResult, catalogResult, postResult).
 *                       Without this attribute it is a regular GET search,
 *                       the module does not handle it.
 *
 *   data-blockresult  — CSS class of the block that receives
 *                       the search results (for example,
 *                       "topSearchBlockResult").
 *
 * Results block:
 *   .{classBlockResult}  — results container; shown
 *                          as display: block, hidden as none.
 *
 * ============================================================
 * AJAX ACTION
 * ============================================================
 *
 * getAjaxSearchResult
 *   Accepts: dataAjax, searchStr.
 *   Returns ready HTML with result blocks.
 *
 * ============================================================
 * BEHAVIOR
 * ============================================================
 *
 * 1. When the text is longer than 1 character, an AJAX request is sent
 *    after 300 ms (debounce prevents extra
 *    requests during fast typing).
 *
 * 2. If the text is shorter than 2 characters, the results block is hidden
 *    and the timer of the previous request is reset.
 *
 * 3. Each request gets a token. If new input arrives while
 *    waiting for the response, the old response is ignored.
 *    This prevents stale results from appearing on screen
 *    when the text is erased quickly.
 *
 * 4. On focus of a field that already contains text, the results
 *    appear immediately, without debounce.
 *
 * 5. On a click outside the form, the results block is hidden.
 *
 * ============================================================
 * USAGE
 * ============================================================
 *
 *     import { arsSearch } from './modules/search.js';
 *     arsSearch.init();
 */

'use strict';

import { arsApi }       from '../core/api.js';
import { t }            from '../core/config.js';
import { qs, qsa, on }  from '../../shared/dom.js';

/**
 * Delay before sending a request while typing, in milliseconds.
 * Prevents excessive requests during fast typing.
 *
 * @type {number}
 */
const SEARCH_DEBOUNCE_MS = 300;

/**
 * Minimum query length that triggers the search.
 * Shorter strings are ignored.
 *
 * @type {number}
 */
const SEARCH_MIN_LENGTH = 2;

/**
 * AJAX search module.
 */
class ArsSearch {

    /** @type {boolean} */
    #initialized = false;

    /**
     * Debounce timers for each input field.
     * The key is the input element itself.
     *
     * @type {WeakMap<Element, number>}
     */
    #timers = new WeakMap();

    /**
     * Tokens of the current requests for each field.
     * A new request overwrites the token — this way old responses
     * are recognized and ignored.
     *
     * @type {WeakMap<Element, symbol>}
     */
    #tokens = new WeakMap();

    /**
     * Initialization. Safe to call repeatedly.
     *
     * @return {void}
     */
    init() {

        if ( this.#initialized ) {
            return;
        }

        const inputs = qsa( '.asInputSearchForm' );

        if ( ! inputs.length ) {
            return;
        }

        this.#initialized = true;

        inputs.forEach( input => {

            on( input, 'input', () => this.#onInput( input ) );
            on( input, 'focus', () => this.#onFocus( input ) );
        });

        // Global handler: a click outside the form hides the results.
        on( document, 'mouseup', ( event ) => this.#onDocumentClick( event ) );
    }

    // ============================================================
    // HANDLERS
    // ============================================================

    /**
     * Search input handler.
     *
     * @param  {HTMLInputElement} input — input field
     * @return {void}
     */
    #onInput( input ) {

        if ( ! this.#hasAjaxParams( input ) ) {
            return;
        }

        const searchStr   = input.value.trim();
        const resultBlock = this.#getResultBlock( input );

        // Always reset the previous timer — otherwise an old
        // debounce may fire after the field is cleared and show
        // stale results.
        this.#cancelPending( input );

        // Query too short — hide the block and invalidate
        // the current request so that its response is not rendered.
        if ( searchStr.length < SEARCH_MIN_LENGTH ) {

            if ( resultBlock ) {
                resultBlock.style.display = 'none';
            }

            return;
        }

        // Debounce: start a new timer
        const timer = window.setTimeout( () => {
            this.#requestSearch( input, searchStr );
        }, SEARCH_DEBOUNCE_MS );

        this.#timers.set( input, timer );
    }

    /**
     * Search field focus handler.
     *
     * If the field already contains long enough text, the search
     * starts immediately, without debounce.
     *
     * @param  {HTMLInputElement} input — input field
     * @return {void}
     */
    #onFocus( input ) {

        if ( ! this.#hasAjaxParams( input ) ) {
            return;
        }

        const searchStr = input.value.trim();

        if ( searchStr.length >= SEARCH_MIN_LENGTH ) {
            this.#requestSearch( input, searchStr );
        }
    }

    /**
     * Document click handler.
     *
     * Hides all open result blocks if the click was
     * neither inside the search form nor inside the results block.
     *
     * @param  {MouseEvent} event — click event
     * @return {void}
     */
    #onDocumentClick( event ) {

        const target = event.target;

        if ( target.closest( '.asInputSearchForm' ) ) {
            return;
        }

        qsa( '.searchAjaxResult' ).forEach( block => {

            if ( block.style.display !== 'block' ) {
                return;
            }

            if ( block.contains( target ) ) {
                return;
            }

            block.style.display = 'none';
        });
    }

    // ============================================================
    // AJAX REQUEST
    // ============================================================

    /**
     * Sends the search request and renders the results.
     *
     * Each request gets a token. If a new request was started
     * (or the field was cleared) while waiting for the response,
     * the response is ignored.
     *
     * @param  {HTMLInputElement} input     — input field
     * @param  {string}           searchStr — search text
     * @return {Promise<void>}
     */
    async #requestSearch( input, searchStr ) {

        const resultBlock = this.#getResultBlock( input );

        if ( ! resultBlock ) {
            return;
        }

        // Remember the token of this request
        const token = Symbol( 'search' );
        this.#tokens.set( input, token );

        // Show the block immediately — otherwise the user will not
        // understand that the search has started.
        resultBlock.style.display = 'block';

        if ( resultBlock.innerHTML === '' ) {
            resultBlock.innerHTML = t( 'searching' );
        }

        const dataAjax = input.dataset.ajax || '';

        try {

            const html = await arsApi.post( 'getAjaxSearchResult', {
                dataAjax:  dataAjax,
                searchStr: searchStr,
            });

            // If another request was made meanwhile — ignore the response
            if ( this.#tokens.get( input ) !== token ) {
                return;
            }

            // If the field is already cleared or too short — do not show
            if ( input.value.trim().length < SEARCH_MIN_LENGTH ) {
                resultBlock.style.display = 'none';
                return;
            }

            resultBlock.innerHTML = html;

        } catch {

            // Show the error only if the request is still current
            if ( this.#tokens.get( input ) !== token ) {
                return;
            }

            resultBlock.innerHTML = t( 'serverError' );
        }
    }

    // ============================================================
    // HELPERS
    // ============================================================

    /**
     * Resets the pending debounce timer and invalidates
     * the active request for the given field.
     *
     * @param  {HTMLInputElement} input — input field
     * @return {void}
     */
    #cancelPending( input ) {

        const timer = this.#timers.get( input );

        if ( timer ) {
            clearTimeout( timer );
            this.#timers.delete( input );
        }

        // Switching to an "empty" token — old responses will not be rendered
        this.#tokens.set( input, Symbol( 'cancelled' ) );
    }

    /**
     * Checks whether AJAX search is configured for the field.
     *
     * @param  {HTMLInputElement} input — input field
     * @return {boolean}
     */
    #hasAjaxParams( input ) {

        const dataAjax = input.dataset.ajax;

        if ( ! dataAjax ) {
            return false;
        }

        try {
            JSON.parse( dataAjax );
            return true;
        } catch {
            return false;
        }
    }

    /**
     * Returns the results block bound to the input field.
     *
     * @param  {HTMLInputElement} input — input field
     * @return {Element|null}
     */
    #getResultBlock( input ) {

        const classBlockResult = input.dataset.blockresult;

        if ( ! classBlockResult ) {
            return null;
        }

        return qs( '.' + classBlockResult );
    }
}

/**
 * Ready-made singleton of the search module.
 *
 * @type {ArsSearch}
 */
export const arsSearch = new ArsSearch();
