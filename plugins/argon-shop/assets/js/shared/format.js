/**
 * ArgonShop — утилиты форматирования чисел.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Единое место для форматирования числовых значений, которые
 * отображаются в интерфейсе: цена, вес, количество товара.
 *
 * В старом jQuery-коде каждая функция делала это по-своему:
 * где-то через Number.isInteger(), где-то через parseFloat(),
 * где-то через .toFixed(2) без проверок. В результате
 * одно и то же число отображалось как «500», «500.00» или
 * «500,00» — в зависимости от места.
 *
 * Здесь единый источник истины:
 *   - целые числа выводятся без дробной части («500»)
 *   - дроби — с двумя цифрами после точки («500.25»)
 *   - пустые и некорректные значения становятся 0
 *
 * ============================================================
 * ЭКСПОРТ
 * ============================================================
 *
 * formatPrice( value )  — форматирование цены
 * formatWeight( value ) — форматирование веса
 * formatAmount( value ) — форматирование количества
 * parseNumber( value )  — безопасное преобразование строки в число
 *
 * ============================================================
 * ПРИМЕРЫ
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
 * Читает число целиком; некорректные и бесконечные значения возвращают 0.
 * Одиночный разделитель — десятичный; повторяющиеся должны группировать по три цифры.
 * Если есть оба разделителя, последний — десятичный. Поддерживается научная запись.
 * Синхронизируйте контракт с as_to_float() и tests/number-cases.json.
 * @since 1.1.0 Строгое чтение числа целиком, согласованное с as_to_float().
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
 * Форматирует цену для интерфейса.
 *
 * @param  {number|string|null|undefined} value — значение цены
 * @return {string} Готовая строка для вставки в DOM
 */
export const formatPrice = ( value ) => {
    const num = parseNumber( value );

    return String( Number( num.toFixed( 2 ) ) );
};

/**
 * Форматирует вес для интерфейса.
 *
 * Логика идентична formatPrice, но вынесена отдельно, чтобы
 * поведение можно было менять независимо при необходимости
 * (например, чтобы добавить единицу измерения).
 *
 * @param  {number|string|null|undefined} value — значение веса
 * @return {string} Готовая строка для вставки в DOM
 */
export const formatWeight = ( value ) => {

    const num = parseNumber( value );

    return Number.isInteger( num )
        ? String( num )
        : num.toFixed( 2 );
};

/**
 * Форматирует количество товара для интерфейса.
 *
 * Количество всегда целое, поэтому используется Math.round.
 *
 * @param  {number|string|null|undefined} value — значение количества
 * @return {string} Готовая строка для вставки в DOM
 */
export const formatAmount = ( value ) => {

    const num = parseNumber( value );

    return String( Math.round( num ) );
};
