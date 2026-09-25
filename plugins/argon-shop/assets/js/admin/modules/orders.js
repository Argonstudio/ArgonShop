/**
 * AJAX-функционал страницы заказа в админ-панели
 * 
 * @package Argon_Shop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ: WordPress Админка → Заказы → Редактирование заказа
 * 
 * ЗАВИСИМОСТИ ОТ orderAjax.php:
 * - searchSuitableProduct( searchStr, blockResult, addedProduct )
 * - addProduct( product )
 * - addFields( type, fieldsInStock, blockFields )
 * - window.recountOrderPrices() — определяется в этом файле
 */

(function(){
    
    'use strict';
    
    
    // ============================================
    // ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
    // ============================================
    
    /**
     * Приводит число к целому или к строке с двумя знаками после запятой.
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
     * Проверяет, является ли значение числом.
     * Аналог $.isNumeric() из jQuery.
     * 
     * @param  {*} value
     * @return {boolean}
     */
    const isNumeric = ( value ) => {
        return ! isNaN( parseFloat( value ) ) && isFinite( value );
    };
    
    
    // ============================================
    // ИНИЦИАЛИЗАЦИЯ
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
    // ПОИСК ТОВАРА
    // ============================================
    
    /**
     * ГДЕ: Поле "Добавить товар" → ввод текста
     * ЧТО: отправляет AJAX-запрос через searchSuitableProduct()
     *      (функция из orderAjax.php)
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
            
            // Собираем ID уже добавленных товаров
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
    // ДОБАВЛЕНИЕ ПОЛЕЙ ФОРМЫ
    // ============================================
    
    /**
     * ГДЕ: Блок "Информация от заказчика" → кнопка "Добавить"
     * ЧТО: отправляет AJAX-запрос через addFields() (функция из orderAjax.php)
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
            
            // Собираем уже существующие поля
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
    // ИЗМЕНЕНИЕ КОЛИЧЕСТВА ТОВАРА
    // ============================================
    
    /**
     * ГДЕ: Поле количества товара в заказе
     * ЧТО: валидирует ввод и вызывает recountOrderPrices()
     * 
     * ВАЖНО: делегирование события на document — работает для
     * динамически добавленных товаров.
     */
    const initProductAmountChange = () => {
        
        document.addEventListener( 'input', ( event ) => {
            
            const target = event.target;
            
            if ( ! target.classList.contains( 'orderAmountProduct' ) ) {
                return;
            }
            
            const value = target.value;
            
            // Валидация ввода
            if ( ! value || value <= 0 || ! isNumeric( value ) ) {
                target.value = 1;
            }
            
            // Единая функция пересчёта
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
    // КНОПКИ "+" И "-"
    // ============================================
    
    /**
     * ГДЕ: Кнопки "+" и "-" у поля количества
     * ЧТО: увеличивает/уменьшает количество и вызывает recountOrderPrices()
     */
    const initQuantityButtons = () => {
        
        document.addEventListener( 'click', ( event ) => {
            
            const target = event.target;
            
            // --- Кнопка "+" ---
            if ( target.classList.contains( 'plusProduct' ) ) {
                
                const productID  = target.getAttribute( 'data-productid' );
                const blockAmount = document.querySelector( '.orderAmountProduct[data-productid="' + productID + '"]' );
                
                if ( blockAmount ) {
                    blockAmount.value = parseInt( blockAmount.value, 10 ) + 1;
                    recountOrderPrices();
                }
                
                return;
            }
            
            // --- Кнопка "-" ---
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
    // УДАЛЕНИЕ ТОВАРА
    // ============================================
    
    /**
     * ГДЕ: Кнопка удаления товара в заказе
     * ЧТО: удаляет блок товара и вызывает recountOrderPrices()
     */
    const initDeleteProduct = () => {
    
        document.addEventListener( 'click', ( event ) => {
            
            const target = event.target;
            
            if ( ! target.classList.contains( 'deleteProduct' ) ) {
                return;
            }
            
            // Ищем родительскую таблицу товара по классу (без data-productid,
            // потому что он есть и у самой кнопки — closest вернул бы её).
            const productParentBlock = target.closest( '.blockOrderProduct' );
            
            if ( productParentBlock ) {
                
                productParentBlock.remove();
                
                recountOrderPrices();
            }
        });
    };
    
    
    // ============================================
    // УДАЛЕНИЕ ВСЕХ ТОВАРОВ
    // ============================================
    
    /**
     * ГДЕ: Кнопка "Очистить заказ"
     * ЧТО: очищает список товаров и вызывает recountOrderPrices()
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
    // ЕДИНАЯ ФУНКЦИЯ ПЕРЕСЧЁТА ЗАКАЗА
    // ============================================
    
    /**
     * ФУНКЦИЯ: Пересчёт заказа
     * 
     * Вызывается при любом изменении состава заказа (количество,
     * добавление, удаление товара). Отправляет на сервер AJAX-запрос
     * с текущим состоянием заказа и обновляет цены, веса и итоги
     * на странице.
     * 
     * ГЛОБАЛЬНАЯ: определена как window.recountOrderPrices — вызывается
     * из функций searchSuitableProduct, addProduct (orderAjax.php).
     */
    window.recountOrderPrices = function() {
        
        // ============================================
        // ПОЛУЧЕНИЕ ID ЗАКАЗА
        // ============================================
        
        const postIDField = document.getElementById( 'post_ID' ) || document.querySelector( 'input[name="post_ID"]' );
        const postID      = postIDField ? postIDField.value : '';
        
        if ( ! postID ) {
            console.warn( 'recountOrderPrices: не найден ID заказа' );
            return;
        }
        
        // ============================================
        // СБОР ТОВАРОВ СО СТРАНИЦЫ
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
        
        // Если товаров нет — очищаем итоги
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
        // AJAX-ЗАПРОС
        // ============================================
        
        const body = new URLSearchParams();
        
        body.append( 'action', 'as_admin_recount_order' );
        body.append( 'postID', postID );
        body.append( 'nonce',  window.argonShopOrderNonce || '' );
        
        // orderProducts — объект, сериализуем как orderProducts[ID]=amount
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
                console.error( 'recountOrderPrices: невалидный JSON в ответе:', text );
                return;
            }
            
            // ============================================
            // ОБНОВЛЕНИЕ ЦЕН ТОВАРОВ
            // ============================================
            
            if ( data.actualPrices ) {
                
                Object.keys( data.actualPrices ).forEach( productID => {
                    
                    const price = data.actualPrices[ productID ];
                    
                    const productBlock = document.querySelector( '.blockOrderProduct[data-productid="' + productID + '"]' );
                    
                    if ( ! productBlock ) {
                        return;
                    }
                    
                    // --- Цена за единицу ---
                    const priceBlock = productBlock.querySelector( '.productPrice span' );
                    
                    if ( priceBlock ) {
                        priceBlock.innerHTML = price;
                    }
                    
                    // --- Вес ---
                    const amountInput = productBlock.querySelector( '.orderAmountProduct' );
                    const amount      = amountInput ? parseFloat( amountInput.value ) : 0;
                    const weight      = ( data.weights && data.weights[ productID ] ) ? parseFloat( data.weights[ productID ] ) : 0;
                    
                    const weightBlock = productBlock.querySelector( '.amountProductWeight span' );
                    
                    if ( weightBlock && weight > 0 ) {
                        weightBlock.innerHTML = formatNumber( weight * amount );
                    }
                    
                    // --- Итоговая цена товара ---
                    const totalPriceBlock = productBlock.querySelector( '.amountProductPrice span' );
                    
                    if ( totalPriceBlock ) {
                        totalPriceBlock.innerHTML = formatNumber( parseFloat( price ) * amount );
                    }
                });
            }
            
            // ============================================
            // ОБНОВЛЕНИЕ ОБЩЕЙ СУММЫ
            // ============================================
            
            if ( data.totalPrice !== undefined ) {
                
                const totalBlock = document.querySelector( '.cartTotalPrice span' );
                
                if ( totalBlock ) {
                    totalBlock.innerHTML = formatNumber( data.totalPrice );
                }
            }
            
            // ============================================
            // ОБНОВЛЕНИЕ ОБЩЕГО ВЕСА
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
    // ЗАПУСК
    // ============================================
    
    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }
    
})();