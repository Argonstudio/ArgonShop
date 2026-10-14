/**
 * AJAX features of the order page in the admin panel
 * 
 * @package Argon_Shop
 * @author  Ivan Voitkov
 * 
 * WHERE: WordPress Admin → Orders → Edit order
 * 
 * DEPENDENCIES ON orderAjax.php:
 * - searchSuitableProduct( searchStr, blockResult, addedProduct )
 * - addProduct( product )
 * - addFields( type, fieldsInStock, blockFields )
 * - window.recountOrderPrices() — defined in this file
 */

(function(){
    
    'use strict';
    
    
    // ============================================
    // HELPER FUNCTIONS
    // ============================================
    
    /**
     * Converts a number to an integer or to a string with two decimal places.
     * 
     * @param  {number} value
     * @return {string|number}
     */
    const formatNumber = ( value ) => {
        
        value = parseFloat( value );
        
        if ( ! Number.isFinite( value ) ) {
            return '0';
        }
        
        return Number.isInteger( value ) ? value : value.toFixed( 2 );
    };
    
    
    /**
     * Checks whether a value is a number.
     * An analogue of jQuery's $.isNumeric().
     * 
     * @param  {*} value
     * @return {boolean}
     */
    const isNumeric = ( value ) => {
        return ! isNaN( parseFloat( value ) ) && isFinite( value );
    };
    
    
    // ============================================
    // INITIALIZATION
    // ============================================
    
    const init = () => {
        
        initSearch();
        initAddFields();
        initProductAmountChange();
        initQuantityButtons();
        initDeleteProduct();
        initDeleteAllProducts();
    };
    
    
    // ============================================
    // PRODUCT SEARCH
    // ============================================
    
    /**
     * WHERE: "Add product" field → typing text
     * WHAT: sends an AJAX request via searchSuitableProduct()
     *      (a function from orderAjax.php)
     */
    const initSearch = () => {
        
        const searchInput = document.querySelector( '.valueAddOrder' );
        
        if ( ! searchInput ) {
            return;
        }
        
        searchInput.addEventListener( 'input', () => {
            
            const searchStr   = searchInput.value;
            const blockResult = document.querySelector( '.addOrderResult' );
            
            if ( ! blockResult ) {
                return;
            }
            
            // Collect the IDs of products already added
            const addedProduct = [];
            
            document.querySelectorAll( '.orderAmountProduct' ).forEach( input => {
                addedProduct.push( input.getAttribute( 'data-productid' ) );
            });
            
            if ( searchStr.length > 1 ) {
                
                if ( typeof window.searchSuitableProduct === 'function' ) {
                    window.searchSuitableProduct( searchStr, blockResult, addedProduct );
                }
                
            } else if ( blockResult.style.display === 'block' ) {
                
                blockResult.style.display = 'none';
            }
        });
    };
    
    
    // ============================================
    // ADDING FORM FIELDS
    // ============================================
    
    /**
     * WHERE: "Customer information" block → "Add" button
     * WHAT: sends an AJAX request via addFields() (a function from orderAjax.php)
     */
    const initAddFields = () => {
        
        const addFieldsBtn = document.querySelector( '.as-order-addFieldsSend' );
        
        if ( ! addFieldsBtn ) {
            return;
        }
        
        addFieldsBtn.addEventListener( 'click', () => {
            
            const typeSelect  = document.querySelector( '.as-order-addFieldsSelect' );
            const blockFields = document.querySelector( '.advanced-fields' );
            
            if ( ! typeSelect || ! blockFields ) {
                return;
            }
            
            const type = typeSelect.value;
            
            // Collect the fields that already exist
            const fieldsInStock = [];
            
            document.querySelectorAll( '.advanced-field' ).forEach( field => {
                fieldsInStock.push( field.getAttribute( 'data-keyfield' ) );
            });
            
            if ( typeof window.addFields === 'function' ) {
                window.addFields( type, fieldsInStock, blockFields );
            }
        });
    };
    
    
    // ============================================
    // PRODUCT QUANTITY CHANGE
    // ============================================
    
    /**
     * WHERE: Product quantity field in the order
     * WHAT: validates the input and calls recountOrderPrices()
     * 
     * IMPORTANT: the event is delegated to document — it works for
     * dynamically added products.
     */
    const initProductAmountChange = () => {
        
        document.addEventListener( 'input', ( event ) => {
            
            const target = event.target;
            
            if ( ! target.classList.contains( 'orderAmountProduct' ) ) {
                return;
            }
            
            const value = target.value;
            
            // Input validation
            if ( ! value || value <= 0 || ! isNumeric( value ) ) {
                target.value = 1;
            }
            
            // Shared recalculation function
            recountOrderPrices();
        });
        
        document.addEventListener( 'keyup', ( event ) => {
            
            const target = event.target;
            
            if ( ! target.classList.contains( 'orderAmountProduct' ) ) {
                return;
            }
            
            const value = target.value;
            
            if ( ! value || value <= 0 || ! isNumeric( value ) ) {
                target.value = 1;
            }
            
            recountOrderPrices();
        });
    };
    
    
    // ============================================
    // "+" AND "-" BUTTONS
    // ============================================
    
    /**
     * WHERE: "+" and "-" buttons next to the quantity field
     * WHAT: increases/decreases the quantity and calls recountOrderPrices()
     */
    const initQuantityButtons = () => {
        
        document.addEventListener( 'click', ( event ) => {
            
            const target = event.target;
            
            // --- "+" button ---
            if ( target.classList.contains( 'plusProduct' ) ) {
                
                const productID  = target.getAttribute( 'data-productid' );
                const blockAmount = document.querySelector( '.orderAmountProduct[data-productid="' + productID + '"]' );
                
                if ( blockAmount ) {
                    blockAmount.value = parseInt( blockAmount.value, 10 ) + 1;
                    recountOrderPrices();
                }
                
                return;
            }
            
            // --- "-" button ---
            if ( target.classList.contains( 'minusProduct' ) ) {
                
                const productID  = target.getAttribute( 'data-productid' );
                const blockAmount = document.querySelector( '.orderAmountProduct[data-productid="' + productID + '"]' );
                
                if ( blockAmount && parseInt( blockAmount.value, 10 ) > 1 ) {
                    blockAmount.value = parseInt( blockAmount.value, 10 ) - 1;
                    recountOrderPrices();
                }
            }
        });
    };
    
    
    // ============================================
    // REMOVING A PRODUCT
    // ============================================
    
    /**
     * WHERE: Product remove button in the order
     * WHAT: removes the product block and calls recountOrderPrices()
     */
    const initDeleteProduct = () => {
    
        document.addEventListener( 'click', ( event ) => {
            
            const target = event.target;
            
            if ( ! target.classList.contains( 'deleteProduct' ) ) {
                return;
            }
            
            // Find the parent product table by class (not by data-productid,
            // because the button itself has it too — closest would return the button).
            const productParentBlock = target.closest( '.blockOrderProduct' );
            
            if ( productParentBlock ) {
                
                productParentBlock.remove();
                
                recountOrderPrices();
            }
        });
    };
    
    
    // ============================================
    // REMOVING ALL PRODUCTS
    // ============================================
    
    /**
     * WHERE: "Clear order" button
     * WHAT: clears the product list and calls recountOrderPrices()
     */
    const initDeleteAllProducts = () => {
        
        document.addEventListener( 'click', ( event ) => {
            
            const target = event.target;
            
            if ( ! target.classList.contains( 'deleteAllProducts' ) ) {
                return;
            }
            
            const productsBlock = document.querySelector( '.orderAllProducts' );
            
            if ( productsBlock ) {
                productsBlock.innerHTML = '';
            }
            
            recountOrderPrices();
        });
    };
    
    
    // ============================================
    // SHARED ORDER RECALCULATION FUNCTION
    // ============================================
    
    /**
     * FUNCTION: Order recalculation
     * 
     * Called on any change of the order contents (quantity,
     * adding, removing a product). Sends an AJAX request to the server
     * with the current order state and updates prices, weights and totals
     * on the page.
     * 
     * GLOBAL: defined as window.recountOrderPrices — called
     * from the searchSuitableProduct and addProduct functions (orderAjax.php).
     */
    window.recountOrderPrices = function() {
        
        // ============================================
        // GETTING THE ORDER ID
        // ============================================
        
        const postIDField = document.getElementById( 'post_ID' ) || document.querySelector( 'input[name="post_ID"]' );
        const postID      = postIDField ? postIDField.value : '';
        
        if ( ! postID ) {
            console.warn( 'recountOrderPrices: order ID not found' );
            return;
        }
        
        // ============================================
        // COLLECTING PRODUCTS FROM THE PAGE
        // ============================================
        
        const orderProducts = {};
        
        document.querySelectorAll( '.blockOrderProduct' ).forEach( block => {
            
            const productID = block.getAttribute( 'data-productid' );
            const amountInput = block.querySelector( '.orderAmountProduct' );
            const amount    = amountInput ? parseFloat( amountInput.value ) : 0;
            
            if ( productID && amount > 0 ) {
                orderProducts[ productID ] = amount;
            }
        });
        
        // If there are no products — clear the totals
        if ( Object.keys( orderProducts ).length === 0 ) {
            
            const totalWeightBlock = document.querySelector( '.cartTotalWeight' );
            
            if ( totalWeightBlock ) {
                totalWeightBlock.style.display = 'none';
            }
            
            const totalPriceBlock = document.querySelector( '.cartTotalPrice span' );
            
            if ( totalPriceBlock ) {
                totalPriceBlock.innerHTML = '0';
            }
            
            return;
        }
        
        // ============================================
        // AJAX REQUEST
        // ============================================
        
        const body = new URLSearchParams();
        
        body.append( 'action', 'as_admin_recount_order' );
        body.append( 'postID', postID );
        body.append( 'nonce',  window.argonShopOrderNonce || '' );
        
        // orderProducts is an object, serialized as orderProducts[ID]=amount
        Object.keys( orderProducts ).forEach( productID => {
            body.append( 'orderProducts[' + productID + ']', orderProducts[ productID ] );
        });
        
        const ajaxUrl = ( typeof ajaxurl !== 'undefined' ) ? ajaxurl : '/wp-admin/admin-ajax.php';
        
        fetch( ajaxUrl, {
            method:      'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: body
        })
        .then( response => {
            
            if ( ! response.ok ) {
                throw new Error( 'HTTP ' + response.status );
            }
            
            return response.text();
        })
        .then( text => {
            
            let data;
            
            try {
                data = JSON.parse( text );
            } catch ( e ) {
                console.error( 'recountOrderPrices: invalid JSON in the response:', text );
                return;
            }
            
            // ============================================
            // UPDATING PRODUCT PRICES
            // ============================================
            
            if ( data.actualPrices ) {
                
                Object.keys( data.actualPrices ).forEach( productID => {
                    
                    const price = data.actualPrices[ productID ];
                    
                    const productBlock = document.querySelector( '.blockOrderProduct[data-productid="' + productID + '"]' );
                    
                    if ( ! productBlock ) {
                        return;
                    }
                    
                    // --- Unit price ---
                    const priceBlock = productBlock.querySelector( '.productPrice span' );
                    
                    if ( priceBlock ) {
                        priceBlock.innerHTML = price;
                    }
                    
                    // --- Weight ---
                    const amountInput = productBlock.querySelector( '.orderAmountProduct' );
                    const amount      = amountInput ? parseFloat( amountInput.value ) : 0;
                    const weight      = ( data.weights && data.weights[ productID ] ) ? parseFloat( data.weights[ productID ] ) : 0;
                    
                    const weightBlock = productBlock.querySelector( '.amountProductWeight span' );
                    
                    if ( weightBlock && weight > 0 ) {
                        weightBlock.innerHTML = formatNumber( weight * amount );
                    }
                    
                    // --- Product total ---
                    const totalPriceBlock = productBlock.querySelector( '.amountProductPrice span' );
                    
                    if ( totalPriceBlock ) {
                        totalPriceBlock.innerHTML = formatNumber( parseFloat( price ) * amount );
                    }
                });
            }
            
            // ============================================
            // UPDATING THE ORDER TOTAL
            // ============================================
            
            if ( data.totalPrice !== undefined ) {
                
                const totalBlock = document.querySelector( '.cartTotalPrice span' );
                
                if ( totalBlock ) {
                    totalBlock.innerHTML = formatNumber( data.totalPrice );
                }
            }
            
            // ============================================
            // UPDATING THE TOTAL WEIGHT
            // ============================================
            
            if ( data.totalWeight ) {
                
                const totalWeight = document.querySelector( '.cartTotalWeight' );
                const totalWeightSpan = document.querySelector( '.cartTotalWeight span' );
                
                if ( totalWeightSpan ) {
                    totalWeightSpan.innerHTML = formatNumber( data.totalWeight );
                }
                
                if ( totalWeight ) {
                    totalWeight.style.display = 'block';
                }
            }
        })
        .catch( error => {
            console.error( 'recountOrderPrices:', error );
        });
    };
    
    
    // ============================================
    // START
    // ============================================
    
    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }
    
})();