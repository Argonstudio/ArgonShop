/**
 * ArgonShop — DOM utilities.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * A small set of helpers over the native DOM API. Replaces
 * everyday jQuery operations (querySelector, on, delegate)
 * without trying to imitate a jQuery object — the names are honest and
 * unambiguous, the behaviour is predictable.
 *
 * IMPORTANT: the code does NOT use the $ and $$ aliases — they
 * are visually and semantically confused with jQuery. Instead —
 * qs / qsa (querySelector / querySelectorAll).
 *
 * ============================================================
 * EXPORTS
 * ============================================================
 *
 * qs( selector, context )         — querySelector, one element or null
 * qsa( selector, context )        — querySelectorAll, always an array
 * on( target, event, handler )    — addEventListener, returns an unsubscribe function
 * delegate( target, event, selector, handler )
 *                                 — delegated handler,
 *                                   returns an unsubscribe function
 *
 * ============================================================
 * UNSUBSCRIBING FROM EVENTS
 * ============================================================
 *
 * The on() and delegate() functions return a function that
 * removes the handler when called. This is useful if a component is created
 * and destroyed dynamically — otherwise the handlers will stay
 * on the container and hold references in memory.
 *
 *     const off = on( window, 'resize', handler );
 *     // ... later
 *     off();
 *
 * If unsubscribing is not needed — the return value can simply
 * be ignored.
 *
 * ============================================================
 * EXAMPLES
 * ============================================================
 *
 *     import { qs, qsa, on, delegate } from '../core/dom.js';
 *
 *     // One element
 *     const cart = qs( '#shoppingCart' );
 *
 *     // Array of all elements
 *     qsa( '.shoppingCartAmountProduct' ).forEach( input => { ... } );
 *
 *     // Delegated click handler for the remove button
 *     delegate( document, 'click', '.deleteProduct', ( e, btn ) => {
 *         const id = btn.dataset.productid;
 *     });
 *
 *     // Regular handler
 *     on( window, 'resize', () => { ... } );
 */

'use strict';

/**
 * querySelector — a short wrapper.
 *
 * Returns the first found element or null. If the search
 * context is not passed or is null — returns null without throwing
 * an exception.
 *
 * @param  {string}           selector             — CSS selector
 * @param  {Element|Document} [context=document]   — search context
 * @return {Element|null}
 */
export const qs = ( selector, context = document ) => {

    if ( ! context ) {
        return null;
    }

    return context.querySelector( selector );
};

/**
 * querySelectorAll — a short wrapper.
 *
 * Always returns a real array (not a NodeList), so that
 * .forEach(), .map(), .filter() etc. work.
 * If the search context is not passed or is null — returns
 * an empty array.
 *
 * @param  {string}           selector             — CSS selector
 * @param  {Element|Document} [context=document]   — search context
 * @return {Element[]}
 */
export const qsa = ( selector, context = document ) => {

    if ( ! context ) {
        return [];
    }

    return Array.from( context.querySelectorAll( selector ) );
};

/**
 * Attaches a regular event handler.
 *
 * Difference from the native addEventListener:
 *   - if target is not passed — silently ignore it (do not throw);
 *   - returns a function that removes the handler when called.
 *
 * @param  {EventTarget}     target              — element, window or document
 * @param  {string}          event               — event name ('click', 'input', ...)
 * @param  {Function}        handler             — handler
 * @param  {Object|boolean}  [options={}]        — addEventListener options
 * @return {Function} Unsubscribe function (calling it removes the handler)
 */
export const on = ( target, event, handler, options = {} ) => {

    if ( ! target ) {
        return () => {};
    }

    target.addEventListener( event, handler, options );

    return () => {
        target.removeEventListener( event, handler, options );
    };
};

/**
 * Delegated event handler.
 *
 * Lets you attach one handler to a container and react
 * to events from dynamically added elements. Inside
 * the handler `this` is the found element matching the selector.
 *
 * Returns an unsubscribe function. The inner wrapper is kept
 * in a closure, so removeEventListener is called exactly
 * for the handler that was added.
 *
 * @param  {EventTarget} target    — container the handler is attached to
 * @param  {string}      event     — event name
 * @param  {string}      selector  — CSS selector for delegation
 * @param  {Function}    handler   — handler ( event, element )
 * @return {Function} Unsubscribe function
 */
export const delegate = ( target, event, selector, handler ) => {

    if ( ! target ) {
        return () => {};
    }

    const wrapper = ( event ) => {

        // Find the closest element matching the selector
        const element = event.target.closest( selector );

        // Make sure the element is really inside target
        // and did not "fly" across boundaries (for example, from another widget)
        if ( element && target.contains( element ) ) {
            handler.call( element, event, element );
        }
    };

    target.addEventListener( event, wrapper );

    return () => {
        target.removeEventListener( event, wrapper );
    };
};
