/**
 * ArgonShop — entry point for the frontend ES modules.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * PURPOSE
 * ============================================================
 *
 * This file is enqueued in argon-shop.php as <script type="module">
 * and is the only entry point for all the modern
 * JavaScript code of the plugin. All other modules are ES modules
 * imported from here.
 *
 * Modules live in two folders:
 *
 *   core/     — base utilities (config, dom, format, api, nonce)
 *   modules/  — feature modules (cart, product, search, ...)
 *
 * Each module exports a singleton object with an init() method.
 * The init() method checks for its own elements
 * on the page and does nothing if they are missing —
 * this makes it safe to call init() of all modules on all
 * pages without the risk of errors.
 *
 * ============================================================
 * WHY THERE IS NO document.ready
 * ============================================================
 *
 * Scripts with the type="module" attribute are executed by the browser
 * deferred — after the HTML is fully parsed
 * but before the DOMContentLoaded event. By the time the code runs
 * the DOM is ready, so a wrapper like $(document).ready() or
 * document.addEventListener('DOMContentLoaded', ...) is not needed.
 *
 * ============================================================
 * jQuery is not used
 * ============================================================
 *
 * This file and all its modules do NOT use jQuery. jQuery may
 * still be enqueued on the page (for example, for Slick
 * sliders or third-party plugins), but the new ArgonShop code
 * works only with the native DOM API.
 *
 * ============================================================
 * INITIALIZATION ORDER
 * ============================================================
 *
 * 1. initNonceInterceptor() — global AJAX response interceptor.
 *    Enabled FIRST, before any requests, so that it can update
 *    the nonce if the server returns a new value.
 *
 * 2. Modules — called in arbitrary order, each decides on its own
 *    what to do on the current page.
 *
 * ============================================================
 * HOW TO ADD A NEW MODULE
 * ============================================================
 *
 * 1. Create a file in modules/ (for example, modules/new-feature.js).
 * 2. Export a singleton with an init() method from it:
 *
 *        export const arsNewFeature = {
 *            init() { ... }
 *        };
 *
 * 3. Import it in this file and call init() in initApp().
 *
 * ============================================================
 */

'use strict';

import { initNonceInterceptor } from './core/nonce.js';

// ============================================================
// MODULE IMPORTS
// ============================================================

// Cart — cart page
import { arsCart } from './modules/cart.js';

import { arsCartForm } from './modules/cart-form.js';

// Product page
import { arsProduct } from './modules/product.js';

// Product card buttons (Add to cart / Buy)
import { arsCardProduct } from './modules/card-product.js';

// AJAX search
import { arsSearch } from './modules/search.js';

// Catalog sorting
import { arsSort } from './modules/sort.js';

// Account — email, password, account data
import { arsCabinet } from './modules/cabinet.js';

// Account — order filter
import { arsOrders } from './modules/orders.js';

// Data processing consent checkboxes
import { arsConsent } from './modules/consent.js';

// Toggle panels (tabs)
import { arsControlPanels } from './modules/control-panels.js';

// Mobile site menu
import { arsMobileMenu } from './modules/mobile-menu.js';

// Catalog menu
import { arsMenuCatalog } from './modules/menu-catalog.js';

// City selection in the header
import { arsHeader } from './modules/header.js';

// Contact form (CF7)
import { arsContactForm } from './modules/contact-form.js';

// Catalog — opening mobile blocks
import { arsCatalogPage } from './modules/catalog-page.js';

// Popular queries block
import { arsRequest } from './modules/request.js';

// Home page slider (Swiper)
import { arsSliderMain } from './modules/slider-main.js';

// Product image slider (Swiper + GLightbox)
import { arsSliderProduct } from './modules/slider-product.js';

// ============================================================
// APPLICATION INITIALIZATION
// ============================================================

/**
 * Entry point. Called once when the module loads.
 *
 * Call order:
 *   1. The nonce interceptor — before any requests.
 *   2. Module initialization — each decides what to do.
 *
 * @return {void}
 */
const initApp = () => {

    // 1. Global AJAX response interceptor for updating the nonce.
    //    Must be enabled before the first AJAX request.
    initNonceInterceptor();

    // 2. Modules.
    arsCart.init();
    arsCartForm.init();
    arsProduct.init();
    arsCardProduct.init();
    arsSearch.init();
    arsSort.init();
    arsCabinet.init();
    arsOrders.init();
    arsConsent.init();
    arsControlPanels.init();
    arsMobileMenu.init();
    arsMenuCatalog.init();
    arsHeader.init();
    arsContactForm.init();
    arsCatalogPage.init();
    arsRequest.init();
    arsSliderMain.init();
    arsSliderProduct.init();
};


// ============================================================
// START
// ============================================================

// The module is enqueued as <script type="module">, so the browser
// guarantees execution after the DOM is parsed.
initApp();
