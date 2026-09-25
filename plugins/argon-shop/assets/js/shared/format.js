/**
 * ArgonShop — утилиты форматирования чисел.
 *
 * @package ArgonShop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Единое место для форматирования числовых значений, которые
 * выводятся в интерфейс: цена, вес, количество товара.
 *
 * В старом коде на jQuery каждая функция делала это по-своему:
 * где-то через Number.isInteger(), где-то через parseFloat(),
 * где-то через .toFixed(2) без проверок. Это приводило к тому,
 * что одно и то же число выводилось как "500", "500.00" или
 * "500,00" в зависимости от места.
 *
 * Здесь одна точка правды:
 *   - целые числа печатаются без дробной части ("500")
 *   - дробные — с двумя знаками после точки ("500.25")
 *   - пустые и некорректные значения превращаются в 0
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
 * Безопасно преобразует значение в число.
 *
 * Понимает:
 *   - числа (500, 500.25)
 *   - строки с точкой ("500.25")
 *   - строки с запятой ("500,25")
 *   - строки с пробелами ("5 000,50")
 *   - null, undefined, NaN, пустые строки → 0
 *
 * @param  {number|string|null|undefined} value — исходное значение
 * @return {number} Число; 0, если преобразование невозможно
 */
export const parseNumber = ( value ) => {

    if ( value === null || value === undefined ) {
        return 0;
    }

    if ( typeof value === 'number' ) {
        return Number.isFinite( value ) ? value : 0;
    }

    // Убираем пробелы (в том числе неразрывные) и заменяем запятую на точку
    const normalized = String( value )
        .replace( /\s+/g, '' )
        .replace( ',', '.' );

    const parsed = parseFloat( normalized );

    return Number.isFinite( parsed ) ? parsed : 0;
};

/**
 * Форматирует цену для вывода в интерфейс.
 *
 * Целые числа печатаются без десятичной части, дробные — с двумя
 * знаками после точки.
 *
 * @param  {number|string|null|undefined} value — значение цены
 * @return {string} Готовая строка для вставки в DOM
 */
export const formatPrice = ( value ) => {

    const num = parseNumber( value );

    return Number.isInteger( num )
        ? String( num )
        : num.toFixed( 2 );
};

/**
 * Форматирует вес для вывода в интерфейс.
 *
 * Логика идентична formatPrice, но вынесена отдельно, чтобы
 * при необходимости (например, добавить единицу измерения)
 * можно было менять поведение независимо.
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
 * Форматирует количество товара для вывода в интерфейс.
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
