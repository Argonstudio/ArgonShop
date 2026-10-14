/**
 * ArgonShop — frontend configuration.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * Central storage of parameters that must be
 * available to all frontend ES modules. The values come from PHP
 * via wp_localize_script() in argon-shop.php:
 *
 *     wp_localize_script( 'ars-main', 'arsWpAjax', array(
 *         'url'   => admin_url( 'admin-ajax.php' ),
 *         'nonce' => wp_create_nonce( 'argon_shop_nonce' ),
 *     ) );
 *
 * So as not to depend on the global arsWpAjax variable directly
 * (it may be missing if the script is enqueued without localization),
 * it is read safely here with fallback values.
 *
 * ============================================================
 * EXPORTS
 * ============================================================
 *
 * config — object with the fields:
 *   url    {string} — admin-ajax.php handler URL
 *   nonce  {string} — current nonce for the server check
 *
 * t( key, ...args ) — returns a translated UI string. Translations
 *   come from PHP (arsWpAjax.i18n, prepared with __() in argon-shop.php,
 *   text domain "argon-shop"). If a key is missing, the English default
 *   from I18N_DEFAULTS is used. Supports printf-style placeholders
 *   %s, %d and numbered %1$s / %2$d.
 *
 * ============================================================
 * USAGE
 * ============================================================
 *
 *     import { config } from '../core/config.js';
 *
 *     console.log( config.url );   // https://site.ru/wp-admin/admin-ajax.php
 *     console.log( config.nonce ); // a1b2c3d4e5
 *
 *     import { t } from '../core/config.js';
 *
 *     el.textContent = t( 'filesUploaded', 3 ); // "Files uploaded: 3"
 */

'use strict';

/**
 * The global arsWpAjax object created by WordPress via wp_localize_script().
 *
 * @type {Object|undefined}
 */
const localized = ( typeof window.arsWpAjax !== 'undefined' ) ? window.arsWpAjax : undefined;

/**
 * ArgonShop frontend configuration.
 *
 * @type {{ url: string, nonce: string, skipNonceActions: string[] }}
 */
export const config = {

    /**
     * WordPress AJAX handler URL.
     *
     * @type {string}
     */
    url: ( localized && typeof localized.url === 'string' && localized.url !== '' )
        ? localized.url
        : '/wp-admin/admin-ajax.php',

    /**
     * Nonce for checking AJAX requests on the server.
     * Updated automatically if the server returned a new value
     * in the newNonce field (see core/nonce.js).
     *
     * @type {string}
     */
    nonce: ( localized && typeof localized.nonce === 'string' )
        ? localized.nonce
        : '',

    /**
     * Cart page URL.
     * Used by the product and card-product modules for the redirect
     * when the "Buy" button is clicked. If the cart page is not configured
     * — an empty string, no redirect is made.
     *
     * @type {string}
     */
    cartUrl: ( localized && typeof localized.cartUrl === 'string' )
        ? localized.cartUrl
        : '',

    /**
     * List of AJAX actions the nonce is not added to.
     *
     * @type {string[]}
     */
    // deleteAllProducts_shoppingCart and ordersFiltersStatus used to be here —
    // now the server checks the nonce for all actions.
    skipNonceActions: [],
};
/**
 * English defaults for UI strings used by the frontend modules.
 * The translated values are passed from PHP in arsWpAjax.i18n
 * (see argon_shop_js_i18n() in argon-shop.php). Keep both lists in sync.
 *
 * @since 1.1.0
 * @type {Object<string, string>}
 */
const I18N_DEFAULTS = {
    serverError:        'Server error',
    dataNotSaved:       'Error: the data was not saved',
    requestError:       'Request failed',
    tooManyFiles:       'You can attach no more than %d files.',
    fileTooBig:         'The file "%1$s" exceeds %2$s MB.',
    filesUploaded:      'Files uploaded: %d',
    cartEmpty:          'Cart is empty',
    productNotRemoved:  'Server error, the product was not removed',
    productsNotRemoved: 'Server error, the products were not removed',
    changesNotAccepted: 'Server error, the changes were not accepted',
    orderNotSent:       'Server error: the order was not sent',
    messageSent:        'Your message has been sent',
    searching:          'Searching...',
};

/**
 * Translated strings: PHP values override the English defaults.
 *
 * @type {Object<string, string>}
 */
const i18n = Object.assign(
    {},
    I18N_DEFAULTS,
    ( localized && localized.i18n && typeof localized.i18n === 'object' ) ? localized.i18n : {}
);

/**
 * Returns a translated UI string with placeholders substituted.
 *
 * @since 1.1.0
 * @param  {string}    key  — string key (see I18N_DEFAULTS)
 * @param  {...*}      args — values for %s / %d / %1$s placeholders
 * @return {string}
 */
export function t( key, ...args ) {
    const template = ( typeof i18n[ key ] === 'string' ) ? i18n[ key ] : key;
    let index = 0;

    return template.replace( /%(?:(\d+)\$)?([sd%])/g, ( match, position, type ) => {
        if ( type === '%' ) {
            return '%';
        }
        const value = position ? args[ Number( position ) - 1 ] : args[ index++ ];
        if ( value === undefined ) {
            return match;
        }
        return ( type === 'd' ) ? String( parseInt( value, 10 ) ) : String( value );
    } );
}
