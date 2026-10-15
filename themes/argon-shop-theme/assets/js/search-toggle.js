/**
 * ArgonShopTheme — разворачивание поиска в шапке.
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Управляет только блоком .block-topSearch в шапке:
 *
 *   1. Наведение разворачивает его — чистый CSS.
 *   2. Фокус на поле поиска фиксирует блок в развёрнутом
 *      состоянии (класс .is-open) — увод мыши не сворачивает его.
 *   3. Клик по иконке в свёрнутом состоянии разворачивает блок
 *      и фокусирует поле. Форма при этом не отправляется.
 *   4. Клик вне .block-topSearch или Esc снимает фиксацию,
 *      скрывает выпадающие результаты и убирает фокус.
 *
 * AJAX-поиск (debounce, fetch, рендеринг) живёт в модуле search.js
 * плагина ArgonShop. Здесь нам нужно только знать, что
 * .searchAjaxResult должен скрываться при закрытии.
 *
 * ============================================================
 * DOM
 * ============================================================
 *
 * .block-topSearch      — контейнер поиска в шапке
 * .topSearchSubmit      — иконка (отправка)
 * .asInputSearchForm    — поле ввода
 * .searchAjaxResult     — выпадающий список результатов
 */

'use strict';

document.addEventListener( 'DOMContentLoaded', () => {

    const roots = document.querySelectorAll( '.block-topSearch' );

    if ( ! roots.length ) {
        return;
    }

    // ============================================================
    // ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
    // ============================================================

    /**
     * Снимает фиксацию со всех блоков поиска и скрывает выпадающий список.
     *
     * @return {void}
     */
    const unpinAll = () => {

        document.querySelectorAll( '.block-topSearch.is-open' ).forEach( root => {

            root.classList.remove( 'is-open' );

            root.querySelectorAll( '.searchAjaxResult' ).forEach( dropdown => {
                dropdown.style.display = 'none';
            });
        });
    };

    // ============================================================
    // ФИКСАЦИЯ ПРИ ФОКУСЕ
    // ============================================================

    document.querySelectorAll( '.block-topSearch .asInputSearchForm' ).forEach( input => {

        input.addEventListener( 'focus', () => {

            const root = input.closest( '.block-topSearch' );

            if ( root ) {
                root.classList.add( 'is-open' );
            }
        });
    });

    // ============================================================
    // КЛИК ПО ИКОНКЕ
    // ============================================================

    document.addEventListener( 'click', event => {

        const submit = event.target.closest( '.block-topSearch .topSearchSubmit' );

        if ( ! submit ) {
            return;
        }

        const root = submit.closest( '.block-topSearch' );

        if ( ! root ) {
            return;
        }

        // Уже развёрнут — даём браузеру отправить форму
        if ( root.classList.contains( 'is-open' ) ) {
            return;
        }

        // Свёрнут — разворачиваем, фокусируем поле, форму не отправляем
        event.preventDefault();

        root.classList.add( 'is-open' );

        const input = root.querySelector( '.asInputSearchForm' );

        if ( input ) {
            input.focus();
        }
    });

    // ============================================================
    // КЛИК ВНЕ БЛОКА И ESC
    // ============================================================

    document.addEventListener( 'mouseup', event => {

        if ( event.target.closest( '.block-topSearch' ) ) {
            return;
        }

        unpinAll();
    });

    document.addEventListener( 'keydown', event => {

        if ( event.key !== 'Escape' ) {
            return;
        }

        unpinAll();

        const active = document.activeElement;

        if ( active && active.classList && active.classList.contains( 'asInputSearchForm' ) ) {
            active.blur();
        }
    });

});