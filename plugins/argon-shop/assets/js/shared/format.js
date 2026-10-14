/**
 * ArgonShop — number formatting utilities.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * A single place for formatting numeric values that
 * are shown in the interface: price, weight, product quantity.
 *
 * In the old jQuery code each function did it its own way:
 * somewhere via Number.isInteger(), somewhere via parseFloat(),
 * somewhere via .toFixed(2) without checks. As a result
 * the same number was shown as "500", "500.00" or
 * "500,00" depending on the place.
 *
 * Here is a single source of truth:
 *   - integers are printed without a fractional part ("500")
 *   - fractions — with two digits after the point ("500.25")
 *   - empty and invalid values become 0
 *
 * ============================================================
 * EXPORTS
 * ============================================================
 *
 * formatPrice( value )  — price formatting
 * formatWeight( value ) — weight formatting
 * formatAmount( value ) — quantity formatting
 * parseNumber( value )  — safe string-to-number conversion
 *
 * ============================================================
 * EXAMPLES
 * ============================================================
 *
 *     import { formatPrice, parseNumber } from '../core/format.js';
 *
 *     formatPrice( 500 );        // "500"
 *     formatPrice( 500.5 );      // "500.50"
 *     formatPrice( '500,25' );   // "500.25"
 *     formatPrice( null );       // "0"
 *     formatPrice( 'abc' );      // "0"
 *
 *     parseNumber( '5 000,50' ); // 5000.5
 *     parseNumber( '500.25' );   // 500.25
 *     parseNumber( '' );         // 0
 */

'use strict';

/**
 * Parse a complete human-entered number; malformed/non-finite values return 0.
 * A single separator is decimal; repeated separators must group three digits.
 * With both separators, the last is decimal. Scientific notation is supported.
 * Keep this contract in sync with as_to_float() and tests/number-cases.json.
 * @since 1.1.0 Strict whole-number parsing shared with as_to_float().
 */
export const parseNumber = ( value ) => {
    if ( typeof value === 'number' ) {
        return Number.isFinite( value ) ? value : 0;
    }
    if ( typeof value !== 'string' ) {
        return 0;
    }
    const text = value.replace( /[\u0009-\u000D\u0020\u00A0\u1680\u2000-\u200A\u2028\u2029\u202F\u205F\u3000\uFEFF]+/g, '' );
    const parts = /^([+-]?)([0-9.,]+)([eE][+-]?[0-9]+)?$/.exec( text );
    if ( ! parts || parts[0] !== text ) {
        return 0;
    }
    let mantissa = parts[2];
    const commas = ( mantissa.match( /,/g ) || [] ).length;
    const dots = ( mantissa.match( /\./g ) || [] ).length;
    if ( commas && dots ) {
        const decimal = mantissa.lastIndexOf( ',' ) > mantissa.lastIndexOf( '.' ) ? ',' : '.';
        const group = decimal === ',' ? '.' : ',';
        const pos = mantissa.lastIndexOf( decimal );
        const integer = mantissa.slice( 0, pos );
        const fraction = mantissa.slice( pos + 1 );
        const grouped = group === ',' ? /^[0-9]{1,3}(?:,[0-9]{3})+$/ : /^[0-9]{1,3}(?:\.[0-9]{3})+$/;
        if ( ! grouped.test( integer ) || ! /^[0-9]*$/.test( fraction ) ) {
            return 0;
        }
        mantissa = integer.split( group ).join( '' ) + '.' + fraction;
    } else if ( commas > 1 || dots > 1 ) {
        const group = commas > 1 ? ',' : '.';
        const grouped = group === ',' ? /^[0-9]{1,3}(?:,[0-9]{3})+$/ : /^[0-9]{1,3}(?:\.[0-9]{3})+$/;
        if ( ! grouped.test( mantissa ) ) {
            return 0;
        }
        mantissa = mantissa.split( group ).join( '' );
    } else {
        mantissa = mantissa.replace( ',', '.' );
    }
    const normalized = parts[1] + mantissa + ( parts[3] || '' );
    if ( ! /^[+-]?(?:[0-9]+(?:\.[0-9]*)?|\.[0-9]+)(?:[eE][+-]?[0-9]+)?$/.test( normalized ) ) {
        return 0;
    }
    const number = Number( normalized );
    return Number.isFinite( number ) ? number : 0;
};

/**
 * Formats a price for the interface.
 *
 * @param  {number|string|null|undefined} value — price value
 * @return {string} Ready string for inserting into the DOM
 */
export const formatPrice = ( value ) => {
    const num = parseNumber( value );

    return String( Number( num.toFixed( 2 ) ) );
};

/**
 * Formats a weight for the interface.
 *
 * The logic is identical to formatPrice but kept separate so that
 * the behaviour can be changed independently if needed
 * (for example, to add a unit of measure).
 *
 * @param  {number|string|null|undefined} value — weight value
 * @return {string} Ready string for inserting into the DOM
 */
export const formatWeight = ( value ) => {

    const num = parseNumber( value );

    return Number.isInteger( num )
        ? String( num )
        : num.toFixed( 2 );
};

/**
 * Formats a product quantity for the interface.
 *
 * The quantity is always an integer, so Math.round is used.
 *
 * @param  {number|string|null|undefined} value — quantity value
 * @return {string} Ready string for inserting into the DOM
 */
export const formatAmount = ( value ) => {

    const num = parseNumber( value );

    return String( Math.round( num ) );
};
