/**
 * ArgonShop — consent checkboxes.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * Controls the data processing consent checkboxes that
 * block the submit buttons until the user checks
 * the box.
 *
 * Examples:
 *   - in the cart the checkbox controls the "Send order" button;
 *   - in the account the checkbox controls the "Save" button (account);
 *   - in the account the checkbox controls the "Save" button (email/password).
 *
 * ============================================================
 * DOM ELEMENTS
 * ============================================================
 *
 * input[type="checkbox"][data-consent-target]
 *   Consent checkbox.
 *
 *   The data-consent-target attribute contains a CSS selector for
 *   the target buttons. While the checkbox is unchecked the buttons are disabled;
 *   as soon as it is checked, disabled is removed.
 *
 * Markup examples:
 *
 *   <input type="checkbox" data-consent-target=".submitCart">
 *   <input type="checkbox" data-consent-target="#accountSaveButton">
 *   <input type="checkbox" data-consent-target="#editEmailPasswordButton">
 *
 * ============================================================
 * USAGE
 * ============================================================
 *
 *     import { arsConsent } from './modules/consent.js';
 *     arsConsent.init();
 *
 * The module is safe to call on any page: if there are no consent
 * checkboxes, nothing happens.
 */

'use strict';

import { qs, qsa, on } from '../../shared/dom.js';

/**
 * Consent checkboxes module.
 */
class ArsConsent {

    /** @type {boolean} */
    #initialized = false;

    /**
     * Initialization. Safe to call repeatedly.
     *
     * @return {void}
     */
    init() {

        if ( this.#initialized ) {
            return;
        }

        const checkboxes = qsa( 'input[type="checkbox"][data-consent-target]' );

        if ( ! checkboxes.length ) {
            return;
        }

        this.#initialized = true;

        checkboxes.forEach( checkbox => {

            // Set the initial state of the buttons
            this.#applyState( checkbox );

            // React to changes
            on( checkbox, 'change', () => this.#applyState( checkbox ) );
        });
    }

    /**
     * Applies the checkbox state to the target buttons.
     *
     * @param  {HTMLInputElement} checkbox — consent checkbox
     * @return {void}
     */
    #applyState( checkbox ) {

        const selector = checkbox.dataset.consentTarget;

        if ( ! selector ) {
            return;
        }

        const targets = qsa( selector );

        targets.forEach( target => {
            target.disabled = ! checkbox.checked;
        });
    }
}

/**
 * Ready singleton of the consent checkboxes module.
 *
 * @type {ArsConsent}
 */
export const arsConsent = new ArsConsent();