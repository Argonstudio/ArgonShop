/**
 * ArgonShop — утилиты для работы с DOM.
 *
 * @package ArgonShop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Небольшой набор хелперов над нативным DOM API. Заменяет
 * повседневные операции jQuery (querySelector, on, delegate)
 * без попыток имитировать jQuery-объект — имена честные и
 * однозначные, поведение предсказуемое.
 *
 * ВАЖНО: в коде НЕ используются псевдонимы $ и $$ — они
 * визуально и семантически путаются с jQuery. Вместо них —
 * qs / qsa (querySelector / querySelectorAll).
 *
 * ============================================================
 * ЭКСПОРТ
 * ============================================================
 *
 * qs( selector, context )         — querySelector, один элемент или null
 * qsa( selector, context )        — querySelectorAll, всегда массив
 * on( target, event, handler )    — addEventListener, возвращает функцию отписки
 * delegate( target, event, selector, handler )
 *                                 — делегированный обработчик,
 *                                   возвращает функцию отписки
 *
 * ============================================================
 * ОТПИСКА ОТ СОБЫТИЙ
 * ============================================================
 *
 * Функции on() и delegate() возвращают функцию, вызов которой
 * снимает обработчик. Это полезно, если компонент создаётся
 * и уничтожается динамически — иначе обработчики будут висеть
 * на контейнере и удерживать ссылки в памяти.
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
 * поиска не передан или равен null — возвращает null, не бросая
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
 * работали методы .forEach(), .map(), .filter() и т.д.
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
 * Навешивает обычный обработчик события.
 *
 * Отличие от нативного addEventListener:
 *   - если target не передан — тихо игнорируем (не бросаем ошибку);
 *   - возвращает функцию, вызов которой снимает обработчик.
 *
 * @param  {EventTarget}     target              — элемент, окно или document
 * @param  {string}          event               — имя события ('click', 'input', ...)
 * @param  {Function}        handler             — обработчик
 * @param  {Object|boolean}  [options={}]        — опции addEventListener
 * @return {Function} Функция отписки (вызов снимает обработчик)
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
 * Позволяет навесить один обработчик на контейнер и реагировать
 * на события от динамически добавляемых элементов. Внутри
 * обработчика `this` — найденный элемент, совпавший с селектором.
 *
 * Возвращает функцию отписки. Внутренняя обёртка запоминается
 * в замыкании, поэтому removeEventListener вызывается именно
 * для того обработчика, который был добавлен.
 *
 * @param  {EventTarget} target    — контейнер, на который вешается обработчик
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

        // Ищем ближайший элемент, совпадающий с селектором
        const element = event.target.closest( selector );

        // Проверяем, что элемент действительно внутри target,
        // а не «пролетел» через границы (например, из другого виджета)
        if ( element && target.contains( element ) ) {
            handler.call( element, event, element );
        }
    };

    target.addEventListener( event, wrapper );

    return () => {
        target.removeEventListener( event, wrapper );
    };
};
