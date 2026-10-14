/**
 * ArgonShop — control panels (tabs).
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * Controls switching of page blocks via horizontal
 * toggle panels. Used:
 *   - in the account (Account / Settings / Orders);
 *   - in the cart (Quick order / Individuals / Legal entities);
 *   - on the product page (Description / Characteristics / Usage).
 *
 * The module supports two modes:
 *
 *   switch       — switching between blocks: only one element
 *                  is active, the others are hidden.
 *
 *   openingList  — collapsible list: a click opens or
 *                  closes the linked block, several can be
 *                  open at the same time.
 *
 * A mobile set of elements is also supported —
 * .mobileItemControlPanel with the data-mobileWidth attribute. If
 * the window is narrower than the given width, the mobile set is used.
 *
 * ============================================================
 * DOM ELEMENTS
 * ============================================================
 *
 * .itemControlPanel, .mobileItemControlPanel
 *   Clickable toggle panels. Attributes:
 *     data-type — behaviour type: "switch" or "openingList".
 *     name      — block name; links the panel to the block
 *                 #block_{name}.
 *
 * .blockItemPage[id="block_{name}"]
 *   The block that is shown or hidden.
 *
 * .itemControlPanelActive
 *   Class of the active panel in switch mode.
 *
 * ============================================================
 * MARKUP EXAMPLE
 * ============================================================
 *
 *   <div class="itemControlPanel itemControlPanelActive"
 *        data-type="switch" name="account">Account</div>
 *   <div class="itemControlPanel"
 *        data-type="switch" name="settings">Settings</div>
 *
 *   <div class="blockItemPage" id="block_account">...</div>
 *   <div class="blockItemPage" id="block_settings">...</div>
 *
 * ============================================================
 * USAGE
 * ============================================================
 *
 *     import { arsControlPanels } from './modules/control-panels.js';
 *     arsControlPanels.init();
 */

'use strict';

import { qs, qsa, on } from '../../shared/dom.js';

/**
 * Control panels module.
 */
class ArsControlPanels {

    /** @type {boolean} */
    #initialized = false;

    /**
     * Active panel selector (.itemControlPanel or
     * .mobileItemControlPanel — depends on the window width).
     *
     * @type {string}
     */
    #activeSelector = '.itemControlPanel';

    /**
     * Window width below which the mobile panel set is used.
     * 0 — the mobile set is not used.
     *
     * @type {number}
     */
    #mobileWidth = 0;

    /**
     * Initialization. Safe to call repeatedly.
     *
     * @return {void}
     */
    init() {

        if ( this.#initialized ) {
            return;
        }

        const hasDesktop = qsa( '.itemControlPanel' ).length > 0;
        const hasMobile  = qsa( '.mobileItemControlPanel' ).length > 0;

        if ( ! hasDesktop && ! hasMobile ) {
            return;
        }

        this.#initialized = true;

        if ( hasMobile ) {

            const mobileSample = qs( '.mobileItemControlPanel' );
            this.#mobileWidth = parseInt( mobileSample.dataset.mobilewidth, 10 ) || 0;

            if ( this.#mobileWidth > 0 && window.innerWidth <= this.#mobileWidth ) {
                this.#activeSelector = '.mobileItemControlPanel';
            }
        }

        this.#bindClicks();
        this.#applyInitialState();
        this.#bindResize();
    }

    // ============================================================
    // HANDLERS
    // ============================================================

    /**
     * Clicks on the panels.
     *
     * @return {void}
     */
    #bindClicks() {

        on( document, 'click', ( event ) => {

            const panel = event.target.closest( '.itemControlPanel, .mobileItemControlPanel' );

            if ( ! panel ) {
                return;
            }

            // If this is not the active panel set (mobile vs desktop) — ignore it.
            if ( ! panel.matches( this.#activeSelector ) ) {
                return;
            }

            this.#handlePanelClick( panel );
        });
    }

    /**
     * Handles a click on one panel.
     *
     * @param  {Element} panel — toggle panel
     * @return {void}
     */
    #handlePanelClick( panel ) {

        const type = panel.dataset.type || 'switch';
        const name = panel.getAttribute( 'name' );

        if ( ! name ) {
            return;
        }

        if ( type === 'switch' ) {
            this.#handleSwitch( panel, name );
            return;
        }

        if ( type === 'openingList' ) {
            this.#handleOpeningList( panel, name );
        }
    }

    /**
     * "switch" mode: activates one panel and deactivates the others.
     *
     * @param  {Element} panel — panel
     * @param  {string}  name  — panel name
     * @return {void}
     */
    #handleSwitch( panel, name ) {

        qsa( this.#activeSelector ).forEach( item => {
            item.classList.remove( 'itemControlPanelActive' );
        });

        panel.classList.add( 'itemControlPanelActive' );

        // Hide all blocks and show the active one
        qsa( '.blockItemPage' ).forEach( block => {
            block.style.display = 'none';
        });

        const activeBlocks = qsa( `${this.#activeSelector}.itemControlPanelActive` );

        activeBlocks.forEach( item => {

            const itemName = item.getAttribute( 'name' );

            if ( ! itemName ) {
                return;
            }

            const block = qs( `#block_${itemName}` );

            if ( block ) {
                block.style.display = 'block';
            }
        });
    }

    /**
     * "openingList" mode: a click opens or closes the linked block.
     *
     * @param  {Element} panel — panel
     * @param  {string}  name  — panel name
     * @return {void}
     */
    #handleOpeningList( panel, name ) {

        const block = qs( `#block_${name}` );

        if ( ! block ) {
            return;
        }

        const isActive = panel.classList.contains( 'itemControlPanelActive' );

        if ( isActive ) {
            panel.classList.remove( 'itemControlPanelActive' );
            block.style.display = 'none';
        } else {
            panel.classList.add( 'itemControlPanelActive' );
            block.style.display = 'block';
        }
    }

    // ============================================================
    // INITIAL STATE AND RESIZE
    // ============================================================

    /**
     * Applies the initial state on page load:
     * in switch mode only the active block is shown.
     *
     * @return {void}
     */
    #applyInitialState() {

        // Hide all blocks
        qsa( '.blockItemPage' ).forEach( block => {
            block.style.display = 'none';
        });

        // Show the blocks linked to the active panels
        qsa( `${this.#activeSelector}.itemControlPanelActive` ).forEach( item => {

            const name = item.getAttribute( 'name' );

            if ( ! name ) {
                return;
            }

            const block = qs( `#block_${name}` );

            if ( block ) {
                block.style.display = 'block';
            }
        });
    }

    /**
     * Switching between the mobile and desktop panel sets
     * when the window width changes.
     *
     * @return {void}
     */
    #bindResize() {

        if ( this.#mobileWidth === 0 ) {
            return;
        }

        window.addEventListener( 'resize', () => {

            const wasMobile = this.#activeSelector === '.mobileItemControlPanel';
            const isMobile  = window.innerWidth <= this.#mobileWidth;

            if ( wasMobile === isMobile ) {
                return;
            }

            this.#activeSelector = isMobile
                ? '.mobileItemControlPanel'
                : '.itemControlPanel';

            this.#applyInitialState();
        });
    }
}

/**
 * Ready singleton of the control panels module.
 *
 * @type {ArsControlPanels}
 */
export const arsControlPanels = new ArsControlPanels();