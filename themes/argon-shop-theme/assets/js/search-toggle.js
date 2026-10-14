/**
 * ArgonShopTheme — expanding the search in the header.
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * Manages only the .block-topSearch block in the header:
 *
 *   1. Hover expands it — pure CSS.
 *   2. Focus on the search field locks the block in the expanded
 *      state (the .is-open class) — moving the mouse away does not collapse it.
 *   3. A click on the icon in the collapsed state expands the block
 *      and focuses the field. It does not submit the form.
 *   4. A click outside .block-topSearch or Esc removes the lock,
 *      hides the results dropdown and removes the focus.
 *
 * AJAX search (debounce, fetch, rendering) lives in the search.js module
 * of the ArgonShop plugin. Here we only need to know that
 * .searchAjaxResult must be hidden on close.
 *
 * ============================================================
 * DOM
 * ============================================================
 *
 * .block-topSearch      — search container in the header
 * .topSearchSubmit      — icon (submit)
 * .asInputSearchForm    — input field
 * .searchAjaxResult     — results dropdown
 */

'use strict';

document.addEventListener( 'DOMContentLoaded', () => {

    const roots = document.querySelectorAll( '.block-topSearch' );

    if ( ! roots.length ) {
        return;
    }

    // ============================================================
    // HELPERS
    // ============================================================

    /**
     * Removes the lock from all search blocks and hides the dropdown.
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
    // LOCK ON FOCUS
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
    // CLICK ON THE ICON
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

        // Already expanded — let the browser submit the form
        if ( root.classList.contains( 'is-open' ) ) {
            return;
        }

        // Collapsed — expand, focus the field, do not submit the form
        event.preventDefault();

        root.classList.add( 'is-open' );

        const input = root.querySelector( '.asInputSearchForm' );

        if ( input ) {
            input.focus();
        }
    });

    // ============================================================
    // CLICK OUTSIDE AND ESC
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