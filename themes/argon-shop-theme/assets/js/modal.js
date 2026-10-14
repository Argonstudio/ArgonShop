/**
 * ArgonShopTheme — lightweight modal window.
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * Replaces Fancybox for the map and the feedback form. Pulls in
 * no libraries: plain ES6+ and the DOM API.
 *
 * Supports three ways of opening:
 *
 *   1. AJAX — a request to a URL, the received HTML is inserted into the modal.
 *      Used for the map (the "Map AJAX" template in the theme).
 *
 *   2. INLINE — moving a DOM element into the modal by selector.
 *      Used for the CF7 form (#feedback in header.php).
 *      The element is NOT cloned but moved — this keeps the
 *      Contact Form 7 handlers bound to the original node.
 *
 *   3. IFRAME — opening a URL in an iframe. For the future, currently
 *      not used.
 *
 * ============================================================
 * MARKUP
 * ============================================================
 *
 * The button that opens the modal must have one of the attributes:
 *
 *   data-modal-ajax="/url/"      — load via AJAX
 *   data-modal-inline="#elem"    — open as inline
 *   data-modal-iframe="/url/"    — open in an iframe
 *
 * Example:
 *
 *   <a href="#" data-modal-ajax="/adres-na-karte-moskva/">Map</a>
 *   <a href="#feedback" data-modal-inline="#feedback">Feedback</a>
 *
 * ============================================================
 * CLOSING
 * ============================================================
 *
 *   - a click on the backdrop outside the window;
 *   - a click on a button with data-modal-close;
 *   - pressing Esc.
 *
 * ============================================================
 * NOTES ON INLINE AND CF7
 * ============================================================
 *
 * Contact Form 7 replaces the form content with a "Thank you"
 * message after a successful submission — right inside the same
 * DOM node. If we just return this node to its place on
 * close, on the next opening the user will see
 * the success message instead of the form.
 *
 * Therefore on opening we remember the original HTML and the original
 * inline style of the element, and on closing:
 *   - if CF7 replaced the content with the success message, we restore
 *     the original HTML;
 *   - we return the original inline style (usually display: none).
 *
 * ============================================================
 */

'use strict';

( () => {

    /**
     * Interface texts. They come from PHP (wp_localize_script →
     * arsModalI18n, text domain argon-shop-theme); English is the fallback.
     *
     * @since 1.1.0
     * @type {{ close: string, loading: string, loadError: string }}
     */
    const i18n = Object.assign( {
        close:     'Close',
        loading:   'Loading…',
        loadError: 'Loading failed. Please try again.',
    }, window.arsModalI18n || {} );

    /**
     * Escapes a string for HTML text.
     *
     * @param  {string} value
     * @return {string}
     */
    const escapeHtml = ( value ) => String( value )
        .replace( /&/g, '&amp;' )
        .replace( /</g, '&lt;' )
        .replace( />/g, '&gt;' );

    /**
     * Escapes a string for an HTML attribute.
     *
     * @param  {string} value
     * @return {string}
     */
    const escapeAttr = ( value ) => escapeHtml( value ).replace( /"/g, '&quot;' );

    /** @type {Element|null} */
    let modalRoot = null;

    /** @type {Element|null} */
    let modalBody = null;

    /** @type {boolean} */
    let isOpen = false;

    /** @type {Element|null} */
    let inlineElement = null;

    /** @type {Comment|null} */
    let inlineAnchor = null;

    /**
     * Original HTML of the inline element — to restore the form
     * after CF7 replaces it with the success message.
     *
     * @type {string}
     */
    let inlineOriginalHTML = '';

    /**
     * Original inline style of the element. null — there was no style
     * attribute initially; an empty string — there was one, but empty;
     * otherwise — a string with the value.
     *
     * @type {string|null}
     */
    let inlineOriginalStyle = null;

    /**
     * The element that had focus before the modal was opened.
     * Used to return the focus on close.
     *
     * @type {Element|null}
     */
    let lastFocused = null;

    // ============================================================
    // SKELETON
    // ============================================================

    /**
     * Creates the modal skeleton in the DOM if it does not exist yet.
     *
     * @return {void}
     */
    const ensureRoot = () => {

        if ( modalRoot ) {
            return;
        }

        modalRoot = document.createElement( 'div' );
        modalRoot.className = 'ars-modal';
        modalRoot.setAttribute( 'role', 'dialog' );
        modalRoot.setAttribute( 'aria-modal', 'true' );
        modalRoot.setAttribute( 'aria-hidden', 'true' );

        modalRoot.innerHTML =
            '<div class="ars-modal__backdrop" data-modal-close></div>' +
            '<div class="ars-modal__window" role="document">' +
                '<button type="button" class="ars-modal__close" data-modal-close aria-label="' + escapeAttr( i18n.close ) + '">' +
                    '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">' +
                        '<path d="M6 6 L18 18 M18 6 L6 18" ' +
                              'fill="none" stroke="currentColor" ' +
                              'stroke-width="2" stroke-linecap="round"/>' +
                    '</svg>' +
                '</button>' +
                '<div class="ars-modal__body"></div>' +
            '</div>';

        document.body.appendChild( modalRoot );

        modalBody = modalRoot.querySelector( '.ars-modal__body' );
    };

    // ============================================================
    // OPEN / CLOSE
    // ============================================================

    /**
     * Opens the modal with the given content.
     *
     * @param  {string} html — ready HTML
     * @return {void}
     */
    const open = ( html ) => {

        ensureRoot();

        modalBody.innerHTML = html;

        modalRoot.classList.add( 'ars-modal--open' );
        modalRoot.setAttribute( 'aria-hidden', 'false' );

        document.body.classList.add( 'ars-modal-open' );

        isOpen = true;

        // Focus return — remember where to return it on close
        lastFocused = document.activeElement;

        // Focus on the close button
        const closeButton = modalRoot.querySelector( '.ars-modal__close' );

        if ( closeButton ) {
            closeButton.focus();
        }
    };

    /**
     * Closes the modal.
     *
     * @return {void}
     */
    const close = () => {

        if ( ! modalRoot || ! isOpen ) {
            return;
        }

        // Return the inline element to its original place.
        // Do this before clearing modalBody so as not to lose the reference.
        if ( inlineElement && inlineAnchor && inlineAnchor.parentNode ) {

            // If CF7 replaced the form with the success message,
            // restore the original HTML.
            const hasSuccess = inlineElement.querySelector( '.wpcf7-mail-sent-ok' );

            if ( hasSuccess && inlineOriginalHTML ) {
                inlineElement.innerHTML = inlineOriginalHTML;
            }

            // Return the element to its place
            inlineAnchor.parentNode.insertBefore( inlineElement, inlineAnchor );

            // Return the original inline style
            if ( inlineOriginalStyle !== null ) {
                inlineElement.setAttribute( 'style', inlineOriginalStyle );
            } else {
                inlineElement.removeAttribute( 'style' );
            }

            // Reset the saved references
            inlineElement         = null;
            inlineAnchor          = null;
            inlineOriginalHTML    = '';
            inlineOriginalStyle   = null;
        }

        modalRoot.classList.remove( 'ars-modal--open' );
        modalRoot.setAttribute( 'aria-hidden', 'true' );

        document.body.classList.remove( 'ars-modal-open' );

        // Clear the body after a delay so that the closing animation
        // does not flash an empty block.
        window.setTimeout( () => {
            if ( ! isOpen && modalBody ) {
                modalBody.innerHTML = '';
            }
        }, 200 );

        isOpen = false;

        // Return the focus to the element that opened the modal
        if ( lastFocused && typeof lastFocused.focus === 'function' ) {
            lastFocused.focus();
        }

        lastFocused = null;
    };

    // ============================================================
    // CONTENT TYPES
    // ============================================================

    /**
     * Opens the modal with AJAX content.
     *
     * @param  {string} url — page address
     * @return {Promise<void>}
     */
    const openAjax = async ( url ) => {

        // If the previous modal is still open — close it
        if ( isOpen ) {
            close();
        }

        open( '<div class="ars-modal__loader">' + escapeHtml( i18n.loading ) + '</div>' );

        try {

            const response = await fetch( url, {
                credentials: 'same-origin',
                headers:     { 'X-Requested-With': 'XMLHttpRequest' },
            });

            if ( ! response.ok ) {
                throw new Error( 'HTTP ' + response.status );
            }

            const html = await response.text();

            if ( isOpen ) {
                modalBody.innerHTML = html;
            }

        } catch {
            if ( isOpen ) {
                modalBody.innerHTML = '<div class="ars-modal__error">' + escapeHtml( i18n.loadError ) + '</div>';
            }
        }
    };

    /**
     * Opens the modal with inline content.
     *
     * MOVES the element into the modal instead of cloning it.
     * Cloning does not work with Contact Form 7: its scripts
     * are attached to the original DOM node, and the clone has no
     * handlers — the form is visible but does not react.
     *
     * An anchor comment is left in place of the original element,
     * by which we return it on close.
     *
     * @param  {string} selector — CSS selector of the element
     * @return {void}
     */
    const openInline = ( selector ) => {

        const source = document.querySelector( selector );

        if ( ! source ) {
            return;
        }

        // If the previous modal is still open — close it
        if ( isOpen ) {
            close();
        }

        // Save the original inline style to return it on close
        inlineOriginalStyle = source.getAttribute( 'style' );

        // Save the reference HTML of the form (for restoring after CF7)
        inlineOriginalHTML = source.innerHTML;

        // Leave an anchor in the place of the future return
        inlineAnchor = document.createComment( 'ars-modal-anchor' );
        source.parentNode.insertBefore( inlineAnchor, source );

        // Remember the element and move it into the modal
        inlineElement = source;

        // First remove the original inline style (display: none),
        // then show the element explicitly
        inlineElement.removeAttribute( 'style' );
        inlineElement.style.display = 'block';

        // Open an empty modal and insert the element into it
        open( '' );
        modalBody.appendChild( inlineElement );
    };

    /**
     * Opens the modal with an iframe.
     *
     * @param  {string} url — address
     * @return {void}
     */
    const openIframe = ( url ) => {

        // If the previous modal is still open — close it
        if ( isOpen ) {
            close();
        }

        const html =
            '<iframe src="' + url + '" ' +
                    'frameborder="0" ' +
                    'allowfullscreen ' +
                    'loading="lazy" ' +
                    'style="width:100%;height:100%;border:0;display:block;"></iframe>';

        open( html );
    };

    // ============================================================
    // HANDLERS
    // ============================================================

    /**
     * Document click handler.
     *
     * @param  {MouseEvent} event — event
     * @return {void}
     */
    const onClick = ( event ) => {

        // In case of a click on a text node — cast the target
        // to an element.
        const target = ( event.target instanceof Element )
            ? event.target
            : event.target.parentElement;

        if ( ! target ) {
            return;
        }

        // Closing on a click on data-modal-close
        const closeTarget = target.closest( '[data-modal-close]' );

        if ( closeTarget ) {
            event.preventDefault();
            close();
            return;
        }

        // Opening
        const trigger = target.closest( '[data-modal-ajax], [data-modal-inline], [data-modal-iframe]' );

        if ( ! trigger ) {
            return;
        }

        event.preventDefault();

        if ( trigger.hasAttribute( 'data-modal-ajax' ) ) {
            openAjax( trigger.getAttribute( 'data-modal-ajax' ) );
            return;
        }

        if ( trigger.hasAttribute( 'data-modal-inline' ) ) {
            openInline( trigger.getAttribute( 'data-modal-inline' ) );
            return;
        }

        if ( trigger.hasAttribute( 'data-modal-iframe' ) ) {
            openIframe( trigger.getAttribute( 'data-modal-iframe' ) );
        }
    };

    /**
     * Closing on Esc.
     *
     * @param  {KeyboardEvent} event — event
     * @return {void}
     */
    const onKeyDown = ( event ) => {

        if ( event.key === 'Escape' && isOpen ) {
            close();
        }
    };

    // ============================================================
    // START
    // ============================================================

    document.addEventListener( 'click', onClick );
    document.addEventListener( 'keydown', onKeyDown );

})();