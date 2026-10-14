/**
 * ArgonShop — admin page scripts.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * Controls the interactive elements of the plugin admin:
 *
 *   1. The category tree in the "Characteristics" form:
 *      - expanding and collapsing nested levels
 *        with the arrow button;
 *      - cascading checkbox selection (selecting a parent
 *        checks all descendants, selecting the "Select all"
 *        checkbox checks the whole tree).
 *
 *   2. Recalculating the product characteristics list when the
 *      catalog category changes: collects the current state of the
 *      characteristic fields and the selected categories, then passes
 *      them to the global updateCharacteristics() function
 *      defined in PHP (see includes/admin/fieldsProduct.php).
 *
 *   3. The store settings save button: passes the IDs
 *      of the cart and account pages to the global function
 *      saveSettingPage().
 *
 * ============================================================
 * FILE STRUCTURE
 * ============================================================
 *
 * The file is standalone: it does not import the plugin's core utilities
 * from the interface.
 *
 * ============================================================
 * GLOBAL FUNCTIONS FROM PHP
 * ============================================================
 *
 * updateCharacteristics( categories, textFields, checkboxFields )
 *   Recalculates the characteristics block when the category changes.
 *   Defined in includes/admin/fieldsProduct.php via
 *   admin_print_footer_scripts.
 *
 * saveSettingPage( cartPageID, cabinetPageID )
 *   Saves the IDs of the cart and account pages.
 *   Optional: if the function is not defined, the call is silently
 *   ignored.
 */

'use strict';

( function () {

    /**
     * querySelector — a short wrapper.
     *
     * @param  {string}           selector             — CSS selector
     * @param  {Element|Document} [context=document]   — search context
     * @return {Element|null}
     */
    const qs = ( selector, context = document ) => {

        if ( ! context ) {
            return null;
        }

        return context.querySelector( selector );
    };

    /**
     * querySelectorAll — a short wrapper.
     * Returns a real array for convenient .forEach / .map / .filter.
     *
     * @param  {string}           selector             — CSS selector
     * @param  {Element|Document} [context=document]   — search context
     * @return {Element[]}
     */
    const qsa = ( selector, context = document ) => {

        if ( ! context ) {
            return [];
        }

        return Array.from( context.querySelectorAll( selector ) );
    };

    // ============================================================
    // CATEGORY TREE IN THE "CHARACTERISTICS" FORM
    // ============================================================

    /**
     * Expanding and collapsing a nested category list.
     *
     * The button with the .showHideButton class (input[type=button])
     * is inside li.checkCategory and controls the visibility
     * of the nested <ul> in the same li.
     *
     * The handler is delegated — it works for dynamically
     * added tree elements.
     *
     * @return {void}
     */
    const initTreeToggle = () => {

        document.addEventListener( 'click', ( event ) => {

            const button = event.target.closest( '.choiceCategory input[type="button"]' );

            if ( ! button ) {
                return;
            }

            const li = button.closest( 'li' );

            if ( ! li || ! li.classList.contains( 'checkCategory' ) ) {
                return;
            }

            const sublist = li.querySelector( 'ul' );

            if ( ! sublist ) {
                return;
            }

            sublist.style.display = ( sublist.style.display === 'block' ) ? 'none' : 'block';
        });
    };

    /**
     * Cascading checkbox selection.
     *
     * Rules:
     *   - a checkbox inside li.checkCategory sets the same
     *     state on all checkboxes inside that li (including
     *     nested levels);
     *   - a checkbox inside li.checkAllCategory sets the
     *     same state on all checkboxes of the .choiceCategory tree.
     *
     * The handler is delegated.
     *
     * @return {void}
     */
    const initTreeCheckboxes = () => {

        document.addEventListener( 'change', ( event ) => {

            const checkbox = event.target;

            if ( checkbox.type !== 'checkbox' ) {
                return;
            }

            if ( ! checkbox.closest( '.choiceCategory' ) ) {
                return;
            }

            const li = checkbox.closest( 'li' );

            if ( ! li ) {
                return;
            }

            if ( li.classList.contains( 'checkCategory' ) ) {

                qsa( 'input[type="checkbox"]', li ).forEach( ( cb ) => {
                    cb.checked = checkbox.checked;
                });

                return;
            }

            if ( li.classList.contains( 'checkAllCategory' ) ) {

                qsa( '.choiceCategory input[type="checkbox"]' ).forEach( ( cb ) => {
                    cb.checked = checkbox.checked;
                });
            }
        });
    };

    // ============================================================
    // RECALCULATING CHARACTERISTICS WHEN THE CATEGORY CHANGES
    // ============================================================

    /**
     * Extracts the category ID from the taxonomy checkbox id.
     *
     * WordPress builds ids like:
     *   in-catalog-123
     *   in-catalog-123-hierarchy
     *
     * Algorithm:
     *   1. Split the id by hyphen.
     *   2. If there are 3 parts — drop the first one (in),
     *      take the second part as the ID.
     *   3. If there are 4 parts — drop the first two
     *      (in + taxonomy name), take the second part
     *      of the remaining ones as the ID.
     *
     * @param  {string} checkboxId — checkbox id
     * @return {string}            — category ID or an empty string
     */
    const extractCategoryId = ( checkboxId ) => {

        if ( ! checkboxId || typeof checkboxId !== 'string' ) {
            return '';
        }

        const parts = checkboxId.split( '-' );

        if ( parts.length === 3 ) {
            parts.splice( 0, 1 );
        } else if ( parts.length === 4 ) {
            parts.splice( 0, 2 );
        }

        return parts[ 1 ] || '';
    };

    /**
     * Collects the IDs of the selected categories in the #taxonomy-catalog container.
     *
     * @return {string[]|string} Array of IDs or the string 'no categories'
     */
    const collectSelectedCategories = () => {

        const container = qs( '#taxonomy-catalog' );

        if ( ! container ) {
            return 'no categories';
        }

        const ids = [];

        qsa( 'input[type="checkbox"]:checked', container ).forEach( ( checkbox ) => {

            const id = extractCategoryId( checkbox.id );

            if ( ! id ) {
                return;
            }

            if ( ids.indexOf( id ) === -1 ) {
                ids.push( id );
            }
        });

        if ( ids.length < 1 ) {
            return 'no categories';
        }

        return ids;
    };

    /**
     * Collects the values of text characteristics from the form fields.
     *
     * Looks for input[name^="characteristics_text_field"] inside
     * .advanced-field, extracts the characteristic ID from a name
     * like characteristics_text_field[24] and puts the value
     * into an object { 24: 'value' }.
     *
     * @return {Object<string, string>}
     */
    const collectTextFields = () => {

        const result = {};

        qsa( '.advanced-field input[name^="characteristics_text_field"]' ).forEach( ( input ) => {

            const name = input.getAttribute( 'name' );

            if ( ! name ) {
                return;
            }

            const match = name.match( /\[([^\]]+)\]/ );

            if ( ! match ) {
                return;
            }

            result[ match[ 1 ] ] = input.value;
        });

        return result;
    };

    /**
     * Collects the values of checkbox characteristics from the form fields.
     *
     * Looks for checked input[name^="characteristics_checkbox_field"]
     * inside .advanced-field. The parent ID is taken from
     * data-parentid of the parent element (usually it is
     * div.checkbox-advanced-fields).
     *
     * @return {Object<string, Object<string, string>>}
     */
    const collectCheckboxFields = () => {

        const result = {};

        qsa( '.advanced-field input[name^="characteristics_checkbox_field"][type="checkbox"]:checked' ).forEach( ( input ) => {

            const name = input.getAttribute( 'name' );

            if ( ! name ) {
                return;
            }

            const match = name.match( /\[([^\]]+)\]/ );

            if ( ! match ) {
                return;
            }

            const elementId = match[ 1 ];

            const parent = input.closest( '[data-parentid]' );

            if ( ! parent ) {
                return;
            }

            const parentId = parent.getAttribute( 'data-parentid' );

            if ( ! parentId ) {
                return;
            }

            if ( ! result[ parentId ] ) {
                result[ parentId ] = {};
            }

            result[ parentId ][ elementId ] = input.value;
        });

        return result;
    };

    /**
     * Catalog category change handler.
     *
     * On every checkbox change inside #taxonomy-catalog
     * collects the current state of the categories and the
     * characteristic fields and passes everything to the PHP function
     * updateCharacteristics(), which updates the characteristics
     * meta box via AJAX.
     *
     * @return {void}
     */
    const initCategoryChange = () => {

        document.addEventListener( 'change', ( event ) => {

            const checkbox = event.target;

            if ( checkbox.type !== 'checkbox' ) {
                return;
            }

            if ( ! checkbox.closest( '#taxonomy-catalog' ) ) {
                return;
            }

            const categories     = collectSelectedCategories();
            const textFields     = collectTextFields();
            const checkboxFields = collectCheckboxFields();

            if ( typeof window.updateCharacteristics === 'function' ) {
                window.updateCharacteristics( categories, textFields, checkboxFields );
            }
        });
    };

    // ============================================================
    // SAVING STORE SETTINGS
    // ============================================================

    /**
     * Store settings save button.
     *
     * Passes the IDs of the cart and account pages to the global
     * saveSettingPage() function. If the function is not defined
     * (it is not used in the current plugin version), the call
     * is ignored.
     *
     * @return {void}
     */
    const initSettingSave = () => {

        document.addEventListener( 'click', ( event ) => {

            const button = event.target.closest( '#settingPageSave' );

            if ( ! button ) {
                return;
            }

            const cartPageInput    = qs( '#shoppingCartPage' );
            const cabinetPageInput = qs( '#cabinetPage' );

            const cartPageID    = cartPageInput    ? cartPageInput.value    : '';
            const cabinetPageID = cabinetPageInput ? cabinetPageInput.value : '';

            if ( typeof window.saveSettingPage === 'function' ) {
                window.saveSettingPage( cartPageID, cabinetPageID );
            }
        });
    };

    // ============================================================
    // INITIALIZATION
    // ============================================================

    /**
     * Entry point. Called once the DOM is ready.
     *
     * @return {void}
     */
    const initApp = () => {

        initTreeToggle();
        initTreeCheckboxes();
        initCategoryChange();
        initSettingSave();
    };

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', initApp );
    } else {
        initApp();
    }

})();
