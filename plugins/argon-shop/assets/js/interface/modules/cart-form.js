/**
 * ArgonShop — cart form fields.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * Handles the checkout form fields that are not related
 * to products and the cart:
 *
 *   1. A click on the .block-regQuestion area toggles
 *      the .cartReg checkbox — "Register an account?".
 *      The checkbox itself is hidden, the click goes to the whole label with text.
 *
 *   2. Checking .cartReg shows/hides the .cartLogin block
 *      with the login field.
 *
 *   3. A click on the .labelAddFile button programmatically opens
 *      the file picker of input[type=file][name="fileCart[]"].
 *
 *   4. After files are chosen — validation by the data attributes
 *      (data-sizeFile, data-amountFiles) and the list output
 *      in .loadedFiles.
 *
 * All work with products and the order is in cart.js.
 *
 * ============================================================
 * WHY DIRECT HANDLERS AND NOT DELEGATION
 * ============================================================
 *
 * Delegation to document does not work for .cartReg because
 * the checkbox is hidden via CSS (display:none), a historical theme implementation.
 * 
 * Decided not to rewrite it, a direct handler is more reliable.
 *
 * ============================================================
 * DOM ELEMENTS
 * ============================================================
 *
 * .block-regQuestion    — label with the checkbox and text
 * .cartReg              — the checkbox itself (hidden)
 * .cartLogin            — block with the login field (hidden)
 *
 * .labelAddFile         — "Attach files" button
 * input[type=file][name="fileCart[]"]
 *     data-sizeFile    — max file size in MB
 *     data-amountFiles — max number of files
 *
 * .block-fileCartMessage — shared output block container
 * .fileCartMessage      — counter and error messages
 * .loadSaccess          — "Files uploaded: N" heading (translated, see i18n.filesUploaded)
 * .loadError            — error message
 * .loadedFiles          — list container (hidden)
 * .loadedFiles > ol > li — rows with file names
 *
 * ============================================================
 */

'use strict';

import { qs, qsa } from '../../shared/dom.js';
import { t } from '../core/config.js';

/**
 * Cart form fields module.
 */
class ArsCartForm {

    /** @type {boolean} */
    #initialized = false;

    /**
     * Handler initialization.
     *
     * Safe for any page: if there is no .form-cart,
     * the module simply does nothing.
     *
     * @return {void}
     */
    init() {

        if ( this.#initialized ) {
            return;
        }

        // No cart form on the page — exit
        if ( ! qs( '.form-cart' ) ) {
            return;
        }

        this.#initialized = true;

        this.#bindRegisterQuestion();
        this.#bindRegisterToggle();
        this.#bindFileAttach();
        this.#bindFileUpload();
    }

    // ============================================================
    // REGISTRATION
    // ============================================================

    /**
     * A click on the .block-regQuestion label emulates a click
     * on the hidden .cartReg checkbox.
     *
     * @return {void}
     */
    #bindRegisterQuestion() {

        qsa( '.block-regQuestion' ).forEach( ( question ) => {

            question.addEventListener( 'click', ( event ) => {

                const checkbox = question.querySelector( '.cartReg' );

                if ( ! checkbox ) {
                    return;
                }

                // The click was on the checkbox itself — the browser
                // toggles it on its own, do not interfere.
                if ( event.target === checkbox ) {
                    return;
                }

                event.preventDefault();
                checkbox.click();
            });
        });
    }

    /**
     * Checking .cartReg shows or hides
     * the .cartLogin block in the same form.
     *
     * @return {void}
     */
    #bindRegisterToggle() {

        qsa( '.cartReg' ).forEach( ( checkbox ) => {

            checkbox.addEventListener( 'change', () => {

                const form = checkbox.closest( '.form-cart' );

                if ( ! form ) {
                    return;
                }

                const loginBlock = form.querySelector( '.cartLogin' );

                if ( ! loginBlock ) {
                    return;
                }

                loginBlock.style.display = checkbox.checked ? 'block' : 'none';
            });
        });
    }

    // ============================================================
    // ATTACHING FILES
    // ============================================================

    /**
     * A click on the .labelAddFile button opens the file picker
     * of the corresponding input.
     *
     * @return {void}
     */
    #bindFileAttach() {

        qsa( '.labelAddFile' ).forEach( ( label ) => {

            label.addEventListener( 'click', ( event ) => {

                event.preventDefault();

                const form = label.closest( '.form-cart' );

                if ( ! form ) {
                    return;
                }

                const input = form.querySelector( 'input[type="file"][name="fileCart[]"]' );

                if ( input ) {
                    input.click();
                }
            });
        });
    }

    /**
     * Handling the file selection: validation, counter, list.
     *
     * @return {void}
     */
    #bindFileUpload() {

        qsa( 'input[type="file"][name="fileCart[]"]' ).forEach( ( input ) => {

            input.addEventListener( 'change', () => {

                const form = input.closest( '.form-cart' );

                if ( ! form ) {
                    return;
                }

                const blockMessage    = form.querySelector( '.block-fileCartMessage' );
                const fileCartMessage = form.querySelector( '.fileCartMessage' );
                const loadedFiles     = form.querySelector( '.loadedFiles' );

                if ( ! blockMessage || ! fileCartMessage || ! loadedFiles ) {
                    return;
                }

                // Show the container
                blockMessage.style.display = 'block';

                // Reset the previous output
                fileCartMessage.innerHTML = '';
                loadedFiles.innerHTML = '';
                loadedFiles.style.display = 'none';

                const files = Array.from( input.files || [] );

                // Files were removed — hide the whole block
                if ( ! files.length ) {
                    blockMessage.style.display = 'none';
                    return;
                }

                // ---------- Checks ----------

                const maxSizeMb = parseFloat( input.dataset.sizefile ) || 0;
                const maxFiles  = parseInt( input.dataset.amountfiles, 10 ) || 0;
                const maxBytes  = maxSizeMb * 1024 * 1024;

                if ( maxFiles > 0 && files.length > maxFiles ) {
                    this.#showFileError( fileCartMessage, loadedFiles, t( 'tooManyFiles', maxFiles ) );
                    input.value = '';
                    return;
                }

                for ( let i = 0; i < files.length; i++ ) {

                    if ( maxBytes > 0 && files[i].size > maxBytes ) {
                        this.#showFileError( fileCartMessage, loadedFiles, t( 'fileTooBig', files[i].name, maxSizeMb ) );
                        input.value = '';
                        return;
                    }
                }

                // ---------- Counter heading ----------

                const counter = document.createElement( 'div' );
                counter.className = 'loadSaccess';
                counter.textContent = t( 'filesUploaded', files.length );

                fileCartMessage.appendChild( counter );

                // ---------- File list ----------

                const list = document.createElement( 'ol' );

                files.forEach( ( file ) => {

                    const item = document.createElement( 'li' );
                    const sizeMb = ( file.size / 1048576 ).toFixed( 2 );

                    item.textContent = file.name + ' [' + sizeMb + 'mb]';

                    list.appendChild( item );
                });

                loadedFiles.appendChild( list );
                loadedFiles.style.display = 'block';
            });
        });
    }

    /**
     * Shows a file selection error message.
     *
     * @param  {Element} fileCartMessage — message container
     * @param  {Element} loadedFiles     — list container
     * @param  {string}  message         — error text
     * @return {void}
     */
    #showFileError( fileCartMessage, loadedFiles, message ) {

        const err = document.createElement( 'div' );
        err.className = 'loadError';
        err.textContent = message;

        fileCartMessage.appendChild( err );

        loadedFiles.style.display = 'none';
    }
}

/**
 * Ready singleton of the cart form fields module.
 *
 * @type {ArsCartForm}
 */
export const arsCartForm = new ArsCartForm();