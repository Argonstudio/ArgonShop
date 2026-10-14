/**
 * ArgonShop — account module.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * Controls the interactive elements of the account:
 *
 *   1. Enabling the email field (the "Change" button).
 *   2. Submitting the email and password change form.
 *   3. Saving account data (individual and legal entity).
 *   4. Toggling the legal entity fields block (the "Legal entity" checkbox).
 *
 * ============================================================
 * DOM ELEMENTS
 * ============================================================
 *
 * "Settings" tab:
 *   #editEmail                       — email field (disabled by default).
 *   #editEmailActive                 — "Change" button (unlocks the field).
 *   #editOldPass, 
 *   #editNewPass, 
 *   #editNewPassConfirm              — password fields.
 *   #editEmailPasswordButton         — "Save" button.
 *
 *   .editEmailError span             — email error message.
 *   .editPassError span              — old password error message.
 *   .editNewPassError span           — new password error message.
 *   .editNewPassConfirmError span    — password mismatch message.
 *   .editResult span                 — save result.
 *
 * "Account and address" tab:
 *   .accountCustomCheckbox           — styled "Legal entity" checkbox.
 *   #accountLegal                    — the real hidden checkbox.
 *   #accountLegalBlock               — legal entity fields table.
 *   #accountSaveButton               — "Save" button (data-userid).
 *   .accountResult span              — save result.
 *
 * Form field classes:
 *   .accountDetail                   — individual fields.
 *   .accountLegalDetail              — legal entity fields.
 *
 * ============================================================
 * AJAX ACTIONS
 * ============================================================
 *
 * cabinetEditEmailPassword
 *   Accepts: userID, email, oldEmail, pass, newPass, newPassConfirm.
 *   Returns JSON:
 *     {
 *       edited:   { newEmail?: '...', newPass?: '...' },
 *       errors:   { email?: '...', pass?: '...', newPass?: '...' },
 *       newNonce: '...'   // if wp_set_password was called
 *     }
 *
 * cabinetAccountSave
 *   Accepts: userID, forBack (JSON string).
 *   Returns text: a success or error message.
 *
 * ============================================================
 * USAGE
 * ============================================================
 *
 *     import { arsCabinet } from './modules/cabinet.js';
 *     arsCabinet.init();
 */

'use strict';

import { arsApi }        from '../core/api.js';
import { t }             from '../core/config.js';
import { qs, qsa, on } from '../../shared/dom.js';

/**
 * Pattern of allowed form field names.
 * Latin and Cyrillic letters, digits and the underscore are allowed (keys of saved data).
 * Random fields from browser extensions are filtered out.
 *
 * @type {RegExp}
 */
const FIELD_NAME_PATTERN = /^[\w\u0410-\u044F\u0451]+$/;

/**
 * Account module.
 */
class ArsCabinet {

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

        // We are in the account if at least one
        // of the key elements is present.
        if ( ! qs( '#editEmailPasswordButton' ) && ! qs( '#accountSaveButton' ) ) {
            return;
        }

        this.#initialized = true;

        this.#bindEmailActivation();
        this.#bindEmailPasswordSubmit();
        this.#bindAccountSave();
        this.#bindLegalBlockToggle();
    }

    // ============================================================
    // EMAIL ACTIVATION
    // ============================================================

    /**
     * The "Change" button unlocks the email field.
     *
     * @return {void}
     */
    #bindEmailActivation() {

        const button = qs( '#editEmailActive' );
        const input  = qs( '#editEmail' );

        if ( ! button || ! input ) {
            return;
        }

        on( button, 'click', () => {

            const currentValue = input.value;

            input.disabled = false;
            input.focus();
            input.value = currentValue;

            button.style.display = 'none';
        });
    }

    // ============================================================
    // EMAIL AND PASSWORD CHANGE
    // ============================================================

    /**
     * Submitting the email and password change form.
     *
     * @return {void}
     */
    #bindEmailPasswordSubmit() {

        const button = qs( '#editEmailPasswordButton' );

        if ( ! button ) {
            return;
        }

        on( button, 'click', async () => {

            const userID      = button.dataset.userid;
            const emailInput  = qs( '#editEmail' );
            const oldPassInput = qs( '#editOldPass' );
            const newPassInput = qs( '#editNewPass' );
            const confirmInput = qs( '#editNewPassConfirm' );

            if ( ! emailInput || ! oldPassInput || ! newPassInput || ! confirmInput ) {
                return;
            }

            const email          = emailInput.value;
            const oldEmail       = emailInput.dataset.oldemail || '';
            const pass           = oldPassInput.value;
            const newPass        = newPassInput.value;
            const newPassConfirm = confirmInput.value;

            try {

                const data = await arsApi.postJson( 'cabinetEditEmailPassword', {
                    userID,
                    email,
                    oldEmail,
                    pass,
                    newPass,
                    newPassConfirm,
                });

                this.#handleEmailPasswordResponse( data, email );

            } catch {
                this.#setResultMessage( t( 'serverError' ) );
            }
        });
    }

    /**
     * Handles the server response to the email/password change.
     *
     * @param  {Object} response — server response
     * @param  {string} email    — entered email (to update data-oldemail)
     * @return {void}
     */
        /**
     * Handles the server response to the email/password change.
     *
     * @param  {Object} response — server response
     * @param  {string} email    — entered email (to update data-oldemail)
     * @return {void}
     */
    #handleEmailPasswordResponse( response, email ) {

        // Fully reset all error messages
        this.#clearAllErrors();

        // Clear the result
        const resultBlock = qs( '.editResult span' );

        if ( resultBlock ) {
            resultBlock.textContent = '';
        }

        const hasErrors = response.errors && Object.keys( response.errors ).length > 0;

        // Show the errors, if any
        if ( hasErrors ) {
            this.#showErrors( response.errors );
            return;
        }

        // Handle successful changes
        if ( ! response.edited ) {
            return;
        }

        const edited = response.edited;
        const messages = [];

        // Password change
        if ( edited.newPass ) {

            this.#clearPasswordFields();

            messages.push( edited.newPass );

            // The session and nonce are invalidated — the most reliable option
            // is to get a fresh state from a new request.
            if ( resultBlock ) {
                resultBlock.textContent = messages.join( ', ' );
            }

            window.setTimeout( () => window.location.reload(), 2000 );
            return;
        }

        // Email change
        if ( edited.newEmail ) {

            const emailInput = qs( '#editEmail' );

            if ( emailInput ) {
                emailInput.dataset.oldemail = email;
            }

            // After the email change clear the password fields — otherwise the password
            // stays in plain sight in the field, and old password errors
            // keep hanging on the screen.
            this.#clearPasswordFields();

            messages.push( edited.newEmail );
        }

        if ( resultBlock ) {
            resultBlock.textContent = messages.join( ', ' );
        }
    }
    
    /**
     * Resets all error messages of the email/password change form.
     *
     * Removes the editErrorActive class from the containers and clears the text
     * inside their span elements.
     *
     * @return {void}
     */
    #clearAllErrors() {

        qsa( '.editError' ).forEach( block => {

            block.classList.remove( 'editErrorActive' );

            const span = qs( 'span', block );

            if ( span ) {
                span.textContent = '';
            }
        });
    }

    /**
     * Fully clears the password fields and the related error
     * messages. Used after a successful email or password change.
     *
     * @return {void}
     */
    #clearPasswordFields() {

        // Clear the field values
        const oldPassInput = qs( '#editOldPass' );
        const newPassInput = qs( '#editNewPass' );
        const confirmInput = qs( '#editNewPassConfirm' );

        if ( oldPassInput ) oldPassInput.value = '';
        if ( newPassInput ) newPassInput.value = '';
        if ( confirmInput ) confirmInput.value = '';

        // Explicitly remove the class and clear the text for the three blocks
        // related to passwords
        [
            '.editPassError',
            '.editNewPassError',
            '.editNewPassConfirmError',
        ].forEach( selector => {

            const block = qs( selector );

            if ( ! block ) {
                return;
            }

            block.classList.remove( 'editErrorActive' );

            const span = qs( 'span', block );

            if ( span ) {
                span.textContent = '';
            }
        });
    }

    /**
     * Shows the errors of the email/password change form.
     *
     * @param  {Object} errors — error map { email, pass, newPass, newPassConfirm }
     * @return {void}
     */
    #showErrors( errors ) {

        const map = {
            email:          '.editEmailError span',
            pass:           '.editPassError span',
            newPass:        '.editNewPassError span',
            newPassConfirm: '.editNewPassConfirmError span',
        };

        for ( const [ key, selector ] of Object.entries( map ) ) {

            if ( ! errors[ key ] ) {
                continue;
            }

            const span = qs( selector );

            if ( ! span ) {
                continue;
            }

            span.textContent = errors[ key ];
            span.parentElement.classList.add( 'editErrorActive' );
        }
    }

    /**
     * Prints a message into the email/password change result block.
     *
     * @param  {string} message — message text
     * @return {void}
     */
    #setResultMessage( message ) {

        const resultBlock = qs( '.editResult span' );

        if ( resultBlock ) {
            resultBlock.textContent = message;
        }
    }

    // ============================================================
    // SAVING THE ACCOUNT
    // ============================================================

    /**
     * Saving the account data.
     *
     * @return {void}
     */
    #bindAccountSave() {

        const button = qs( '#accountSaveButton' );

        if ( ! button ) {
            return;
        }

        on( button, 'click', async () => {

            const userID   = button.dataset.userid;
            const checkbox = qs( '#accountLegal' );

            const forBack = {
                accountDetail: this.#collectFields( '.accountDetail' ),
            };

            if ( checkbox && checkbox.checked ) {

                const legalFields = this.#collectFields( '.accountLegalDetail' );

                if ( Object.keys( legalFields ).length > 0 ) {
                    forBack.accountLegalDetail = legalFields;
                }
            }

            try {

                const text = await arsApi.post( 'cabinetAccountSave', {
                    userID,
                    forBack: JSON.stringify( forBack ),
                });

                this.#setAccountResult( text );

            } catch {
                this.#setAccountResult( t( 'dataNotSaved' ) );
            }
        });
    }

    /**
     * Collects the form fields into an object { name: value }.
     *
     * @param  {string} selector — CSS selector of the fields
     * @return {Object}
     */
    #collectFields( selector ) {

        const fields = qsa( selector );

        const result = {};

        fields.forEach( field => {

            const name = field.getAttribute( 'name' );

            if ( ! name || ! FIELD_NAME_PATTERN.test( name ) ) {
                return;
            }

            result[ name ] = field.value;
        });

        return result;
    }

    /**
     * Prints a message into the account save result block.
     *
     * @param  {string} message — message text
     * @return {void}
     */
    #setAccountResult( message ) {

        const resultBlock = qs( '.accountResult span' );

        if ( resultBlock ) {
            resultBlock.textContent = message;
        }
    }

    // ============================================================
    // LEGAL ENTITY CHECKBOX
    // ============================================================

    /**
     * Toggling the legal entity fields block.
     *
     * Logic:
     *   - a click on .accountCustomCheckbox toggles #accountLegal
     *     and shows or hides #accountLegalBlock;
     *   - on page load the block is shown only if
     *     the checkbox is checked.
     *
     * @return {void}
     */
    #bindLegalBlockToggle() {

        const checkbox = qs( '#accountLegal' );
        const block    = qs( '#accountLegalBlock' );
        const custom   = qs( '.accountCustomCheckbox' );

        if ( ! checkbox || ! block ) {
            return;
        }

        // Initial state on page load
        block.style.display = checkbox.checked ? 'table' : 'none';

        const toggle = () => {

            const nextState = ! checkbox.checked;

            checkbox.checked = nextState;
            block.style.display = nextState ? 'table' : 'none';
        };

        // Click on the styled checkbox
        if ( custom ) {
            on( custom, 'click', toggle );
        }

        // Click on the real checkbox (in case it is visible)
        on( checkbox, 'click', () => {
            block.style.display = checkbox.checked ? 'table' : 'none';
        });
    }
}

/**
 * Ready singleton of the account module.
 *
 * @type {ArsCabinet}
 */
export const arsCabinet = new ArsCabinet();