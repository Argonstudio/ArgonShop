/**
 * ArgonShop — утилиты для работы с DOM.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Небольшой набор хелперов поверх нативного DOM API. Заменяет
 * повседневные операции jQuery (querySelector, on, delegate),
 * не пытаясь имитировать объект jQuery — имена честные и
 * однозначные, поведение предсказуемое.
 *
 * ВАЖНО: в коде НЕ используются псевдонимы $ и $$ — они
 * визуально и по смыслу путаются с jQuery. Вместо них —
 * qs / qsa (querySelector / querySelectorAll).
 *
 * ============================================================
 * ЭКСПОРТ
 * ============================================================
 *
 * qs( selector, context ) — querySelector, один элемент или null
 * qsa( selector, context ) — querySelectorAll, всегда массив
 * on( target, event, handler ) — addEventListener, возвращает функцию отписки
 * delegate( target, event, selector, handler )
 *                                 — делегированный обработчик,
 *                                   возвращает функцию отписки
 *
 * ============================================================
 * ОТПИСКА ОТ СОБЫТИЙ
 * ============================================================
 *
 * Функции on() и delegate() возвращают функцию, которая
 * при вызове удаляет обработчик. Это полезно, если компонент создаётся
 * и уничтожается динамически, — иначе обработчики останутся
 * на контейнере и будут удерживать ссылки в памяти.
 *
 *     const off = on( window, 'resize', handler );
 *     // ... позже
 *     off();
 *
 * Если отписка не нужна — возвращаемое значение можно просто
 * игнорировать.
 *
 * ============================================================
 * ПРИМЕРЫ
 * ============================================================
 *
 *     import { qs, qsa, on, delegate } from '../core/dom.js';
 *
 *     // Один элемент
 *     const cart = qs( '#shoppingCart' );
 *
 *     // Массив всех элементов
 *     qsa( '.shoppingCartAmountProduct' ).forEach( input => { ... } );
 *
 *     // Делегированный обработчик клика по кнопке удаления
 *     delegate( document, 'click', '.deleteProduct', ( e, btn ) => {
 *         const id = btn.dataset.productid;
 *     });
 *
 *     // Обычный обработчик
 *     on( window, 'resize', () => { ... } );
 */

'use strict';

/**
 * querySelector — короткая обёртка.
 *
 * Возвращает первый найденный элемент или null. Если контекст
 * поиска не передан или равен null — возвращает null, не выбрасывая
 * исключение.
 *
 * @param  {string}           selector             — CSS-селектор
 * @param  {Element|Document} [context=document]   — контекст поиска
 * @return {Element|null}
 */
export const qs = ( selector, context = document ) => {

    if ( ! context ) {
        return null;
    }

    return context.querySelector( selector );
};

/**
 * querySelectorAll — короткая обёртка.
 *
 * Всегда возвращает настоящий массив (не NodeList), чтобы
 * работали .forEach(), .map(), .filter() и т. д.
 * Если контекст поиска не передан или равен null — возвращает
 * пустой массив.
 *
 * @param  {string}           selector             — CSS-селектор
 * @param  {Element|Document} [context=document]   — контекст поиска
 * @return {Element[]}
 */
export const qsa = ( selector, context = document ) => {

    if ( ! context ) {
        return [];
    }

    return Array.from( context.querySelectorAll( selector ) );
};

/**
 * Вешает обычный обработчик события.
 *
 * Отличие от нативного addEventListener:
 *   - если target не передан — молча игнорируем (не выбрасываем ошибку);
 *   - возвращает функцию, которая при вызове удаляет обработчик.
 *
 * @param {EventTarget} target — элемент, window или document
 * @param {string} event — имя события ('click', 'input', ...)
 * @param {Function} handler — обработчик
 * @param {Object|boolean} [options={}] — опции addEventListener
 * @return {Function} Функция отписки (её вызов удаляет обработчик)
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
 * Делегированный обработчик события.
 *
 * Позволяет повесить один обработчик на контейнер и реагировать
 * на события от динамически добавленных элементов. Внутри
 * обработчика `this` — найденный элемент, подходящий под селектор.
 *
 * Возвращает функцию отписки. Внутренняя обёртка сохраняется
 * в замыкании, поэтому removeEventListener вызывается ровно
 * для того обработчика, который был добавлен.
 *
 * @param  {EventTarget} target    — контейнер, к которому привязан обработчик
 * @param  {string}      event     — имя события
 * @param  {string}      selector  — CSS-селектор для делегирования
 * @param  {Function}    handler   — обработчик ( event, element )
 * @return {Function} Функция отписки
 */
export const delegate = ( target, event, selector, handler ) => {

    if ( ! target ) {
        return () => {};
    }

    const wrapper = ( event ) => {

        // Находит ближайший элемент, соответствующий селектору
        const element = event.target.closest( selector );

        // Убеждаемся, что элемент действительно внутри target
        // и не «улетел» за границы (например, из другого виджета)
        if ( element && target.contains( element ) ) {
            handler.call( element, event, element );
        }
    };

    target.addEventListener( event, wrapper );

    return () => {
        target.removeEventListener( event, wrapper );
    };
};
