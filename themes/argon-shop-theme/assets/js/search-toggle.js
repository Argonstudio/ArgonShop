/**
 * ArgonShopTheme — раскрытие поиска в шапке.
 *
 * @package ArgonShopTheme
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Управляет только блоком .block-topSearch в шапке:
 *
 *   1. Hover раскрывает — чистый CSS.
 *   2. Focus на поле поиска фиксирует блок в раскрытом
 *      состоянии (класс .is-open) — уход мыши его не схлопывает.
 *   3. Клик по иконке в свёрнутом состоянии раскрывает блок
 *      и ставит фокус в поле. Форму не отправляет.
 *   4. Клик вне .block-topSearch или Esc — снимает фиксацию,
 *      скрывает dropdown результатов, снимает фокус.
 *
 * AJAX-поиск (debounce, fetch, рендер) — в модуле search.js
 * плагина ArgonShop. Здесь про него знаем только то, что нужно
 * спрятать .searchAjaxResult при закрытии.
 *
 * ============================================================
 * DOM
 * ============================================================
 *
 * .block-topSearch      — контейнер поиска в шапке
 * .topSearchSubmit      — иконка (submit)
 * .asInputSearchForm    — поле ввода
 * .searchAjaxResult     — dropdown результатов
 */

'use strict';

document.addEventListener( 'DOMContentLoaded', () => {

    const roots = document.querySelectorAll( '.block-topSearch' );

    if ( ! roots.length ) {
        return;
    }

    // ============================================================
    // ВСПОМОГАТЕЛЬНЫЕ
    // ============================================================

    /**
     * Снимает фиксацию со всех блоков поиска и прячет dropdown.
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
    // ФИКСАЦИЯ ПО ФОКУСУ
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

        // Уже раскрыт — отдаём форму браузеру
        if ( root.classList.contains( 'is-open' ) ) {
            return;
        }

        // Свёрнут — раскрываем, фокус в поле, форму не отправляем
        event.preventDefault();

        root.classList.add( 'is-open' );

        const input = root.querySelector( '.asInputSearchForm' );

        if ( input ) {
            input.focus();
        }
    });

    // ============================================================
    // КЛИК ВНЕ И ESC
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