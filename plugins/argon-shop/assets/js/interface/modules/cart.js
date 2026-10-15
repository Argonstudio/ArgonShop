/**
 * ArgonShop — модуль корзины.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Управляет страницей корзины и виджетом корзины:
 *
 *   1. Изменение количества товара (поле ввода и кнопки +/-).
 *   2. Удаление одного товара.
 *   3. Очистка всей корзины.
 *   4. Пересчёт цен, веса и итогов после каждого изменения.
 *   5. Отправка формы оформления заказа.
 *
 * Все действия идут через AJAX-обработчики плагина
 * (см. includes/interface/shoppingCart/shoppingCart.php).
 *
 * ============================================================
 * AJAX-ДЕЙСТВИЯ
 * ============================================================
 *
 * amountProducts_shoppingCart
 *   Изменение количества одного товара.
 *   Принимает: productID, productAmount.
 *   Возвращает JSON: { weight, actualPrices, productsShoppingCart }
 *
 * deleteProducts_shoppingCart
 *   Удаление одного товара.
 *   Принимает: productID.
 *   Возвращает JSON actualPrices или строку "false", если корзина пуста.
 *
 * deleteAllProducts_shoppingCart
 *   Полная очистка корзины. Nonce тоже отправляется (сервер проверяет его у всех действий).
 *   Ничего не возвращает.
 *
 * submitCart_shoppingCart
 *   Отправка формы оформления заказа.
 *   Принимает FormData: typeForm, required, productsCart, поля формы.
 *   Возвращает JSON: { type, typeForm, message }.
 *
 * ============================================================
 * DOM-ЭЛЕМЕНТЫ
 * ============================================================
 *
 * .shoppingCartAmountProduct[data-productid]
 *   Поле количества товара.
 *
 * .plusProduct[data-productid] / .minusProduct[data-productid]
 *   Кнопки увеличения и уменьшения количества.
 *
 * .deleteProduct[data-productid]
 *   Кнопка удаления одного товара.
 *
 * .deleteAllProducts
 *   Кнопка очистки всей корзины.
 *
 * .blockCartProduct[data-productid]
 *   Контейнер одного товара в корзине.
 *
 * .productPrice span           — цена товара за единицу.
 * .amountProductPrice span     — общая цена за количество этого товара.
 * .amountProductWeight span    — вес за количество этого товара.
 * .shoppingCartError           — блок ошибки на карточке товара.
 *
 * .cartTotalPrice span         — итог всей корзины.
 * .cartTotalWeight span        — общий вес всей корзины.
 * .cartTotalWeight             — контейнер общего веса (управляет видимостью).
 *
 * #shoppingCart                — контейнер корзины (для сообщения «Корзина пуста»).
 * .errorDeleteProducts         — блок ошибки очистки корзины.
 *
 * .form-cart[data-type]        — форма оформления заказа.
 * .submitError span            — блок ошибки отправки формы.
 *
 * ============================================================
 * ИСПОЛЬЗОВАНИЕ
 * ============================================================
 *
 *     import { arsCart } from './modules/cart.js';
 *     arsCart.init();
 *
 * Модуль безопасно вызывать на любой странице: если элементов
 * корзины нет, обработчики не срабатывают.
 */

'use strict';

import { arsApi }            from '../core/api.js';
import { t }               from '../core/config.js';
import { qs, qsa, delegate } from '../../shared/dom.js';
import { formatPrice, formatWeight, parseNumber } from '../../shared/format.js';

/**
 * Модуль корзины.
 */
class ArsShoppingCart {

    /**
     * Текущий незавершённый запрос изменения количества.
     * Используется для отмены предыдущего запроса, если пользователь
     * быстро меняет количество, — иначе ответы приходят в другом
     * порядке и перезаписывают данные.
     *
     * @type {AbortController|null}
     */
    #amountController = null;

    /**
     * Инициализация обработчиков. Повторный вызов
     * ничего не делает.
     *
     * @return {void}
     */
    init() {

        if ( this.#initialized ) {
            return;
        }

        this.#initialized = true;

        this.#bindAmountChange();
        this.#bindPlusMinus();
        this.#bindDeleteProduct();
        this.#bindDeleteAll();
        this.#bindFormSubmit();
    }

    /** @type {boolean} */
    #initialized = false;

    // ============================================================
    // ОБРАБОТЧИКИ СОБЫТИЙ
    // ============================================================

    /**
     * Изменение количества через поле ввода.
     *
     * При недопустимом значении (пусто, ноль, не число) сбрасывает его
     * на 1 и отправляет на сервер.
     *
     * @return {void}
     */
    #bindAmountChange() {

        delegate( document, 'input', '.shoppingCartAmountProduct', ( event, input ) => {

            const productId = input.dataset.productid;

            let amount = parseInt( input.value, 10 );

            if ( ! amount || amount <= 0 || Number.isNaN( amount ) ) {
                amount = 1;
                input.value = 1;
            }

            this.#requestAmountChange( productId, amount );
        });
    }

    /**
     * Кнопки увеличения и уменьшения количества.
     *
     * После программного изменения значения поля вызывается событие input —
     * так срабатывает тот же обработчик, что и при ручном вводе.
     *
     * @return {void}
     */
    #bindPlusMinus() {

        delegate( document, 'click', '.plusProduct', ( event, button ) => {

            const productId = button.dataset.productid;
            const input = qs( `.shoppingCartAmountProduct[data-productid="${productId}"]` );

            if ( ! input ) {
                return;
            }

            input.value = ( parseInt( input.value, 10 ) || 0 ) + 1;
            input.dispatchEvent( new Event( 'input', { bubbles: true } ) );
        });

        delegate( document, 'click', '.minusProduct', ( event, button ) => {

            const productId = button.dataset.productid;
            const input = qs( `.shoppingCartAmountProduct[data-productid="${productId}"]` );

            if ( ! input ) {
                return;
            }

            const current = parseInt( input.value, 10 ) || 0;

            if ( current > 1 ) {
                input.value = current - 1;
                input.dispatchEvent( new Event( 'input', { bubbles: true } ) );
            }
        });
    }

    /**
     * Удаление одного товара.
     *
     * @return {void}
     */
    #bindDeleteProduct() {

        delegate( document, 'click', '.deleteProduct', async ( event, button ) => {

            const productId = button.dataset.productid;
            const parent    = button.closest( `.blockCartProduct[data-productid="${productId}"]` );

            try {

                const text = await arsApi.post( 'deleteProducts_shoppingCart', {
                    productID: productId,
                });

                if ( text === 'false' || text === '0' ) {

                    const cart = qs( '#shoppingCart' );

                    if ( cart ) {
                        cart.textContent = t( 'cartEmpty' );
                    }

                    return;
                }

                const actualPrices = JSON.parse( text );

                if ( parent ) {
                    parent.remove();
                }

                this.#recountCart( actualPrices );

            } catch {
                this.#showError( productId, t( 'productNotRemoved' ) );
            }
        });
    }

    /**
     * Очистка всей корзины.
     *
     * @return {void}
     */
    #bindDeleteAll() {

        delegate( document, 'click', '.deleteAllProducts', async () => {

            try {

                await arsApi.post( 'deleteAllProducts_shoppingCart' );

                const cart = qs( '#shoppingCart' );

                if ( cart ) {
                    cart.textContent = t( 'cartEmpty' );
                }

                const errorBlock = qs( '.errorDeleteProducts' );

                if ( errorBlock ) {
                    errorBlock.style.display = 'none';
                }

            } catch {

                const errorBlock = qs( '.errorDeleteProducts' );

                if ( errorBlock ) {
                    errorBlock.textContent = t( 'productsNotRemoved' );
                    errorBlock.style.display = 'block';
                }
            }
        });
    }

    /**
     * Отправка формы оформления заказа.
     *
     * Помимо данных формы собирает:
     *   - typeForm — тип формы (quick / person / legalPerson)
     *   - productsCart — текущее количество всех товаров корзины
     *   - required — карта обязательных полей формы
     *
     * @return {void}
     */
    #bindFormSubmit() {

        delegate( document, 'submit', '.form-cart', ( event, form ) => {

            event.preventDefault();

            const formData = new FormData( form );

            // Текущее количество всех товаров корзины.
            const productsCart = {};

            qsa( '.shoppingCartAmountProduct' ).forEach( input => {
                productsCart[ input.dataset.productid ] = {
                    amountProduct: input.value,
                };
            });

            // Карта обязательных полей формы.
            const required = {};

            qsa( '[required]', form ).forEach( field => {
                if ( field.name ) {
                    required[ field.name ] = true;
                }
            });

            formData.append( 'typeForm', form.dataset.type );
            formData.append( 'required', JSON.stringify( required ) );
            formData.append( 'productsCart', JSON.stringify( productsCart ) );

            this.#submitCart( formData );
        });
    }

    // ============================================================
    // AJAX-ОПЕРАЦИИ
    // ============================================================

    /**
     * Отправляет запрос изменения количества одного товара.
     *
     * Предыдущий незавершённый запрос отменяется — это исключает
     * ситуацию, когда ответы приходят в другом порядке и портят
     * итоговое состояние.
     *
     * @param  {string} productId — ID товара
     * @param  {number} amount    — новое количество
     * @return {Promise<void>}
     */
    async #requestAmountChange( productId, amount ) {

        if ( this.#amountController ) {
            this.#amountController.abort();
            this.#amountController = null;
        }

        const controller = new AbortController();
        this.#amountController = controller;

        try {

            const body = new URLSearchParams({
                action:        'amountProducts_shoppingCart',
                productID:     productId,
                productAmount: amount,
                nonce:         arsApi.getNonce(),
            });

            const response = await fetch( arsApi.url, {
                method:      'POST',
                credentials: 'same-origin',
                headers:     { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                body,
                signal:      controller.signal,
            });

            const text = await response.text();
            const data = JSON.parse( text );

            this.#amountController = null;

            this.#updateProductWeight( productId, data.weight, amount );
            this.#recountCart( data.actualPrices );

            this.#hideError( productId );

        } catch ( error ) {

            if ( error.name === 'AbortError' ) {
                return;
            }

            this.#amountController = null;

            this.#showError( productId, t( 'changesNotAccepted' ) );
        }
    }

    /**
     * Отправка формы оформления заказа.
     *
     * @param  {FormData} formData — данные формы
     * @return {Promise<void>}
     */
    async #submitCart( formData ) {

        try {

            const data = await arsApi.postFormData( 'submitCart_shoppingCart', formData );

            const form = qs( `.form-cart${data.typeForm}` );

            if ( data.type === 'error' ) {
                this.#showFormError( form, data.message );
                return;
            }

            if ( data.type === 'success' ) {

                this.#hideFormError( form );

                const cart = qs( '#shoppingCart' );

                if ( cart ) {
                    cart.innerHTML = data.message;
                }
                
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

        } catch {
            this.#showFormError(
                qs( '.form-cart' ),
                t( 'orderNotSent' )
            );
        }
    }

    // ============================================================
    // ПЕРЕСЧЁТ ЦЕН И ВЕСА
    // ============================================================

    /**
     * Обновляет вес одного товара на странице.
     *
     * Сервер присылает вес одной единицы товара, здесь
     * он умножается на количество и записывается во все
     * элементы .amountProductWeight span внутри карточки.
     *
     * @param  {string} productId — ID товара
     * @param  {number} weight    — вес одной единицы
     * @param  {number} amount    — количество
     * @return {void}
     */
    #updateProductWeight( productId, weight, amount ) {

        const block = qs( `.blockCartProduct[data-productid="${productId}"]` );

        if ( ! block ) {
            return;
        }

        const totalWeight = ( parseNumber( weight ) || 0 ) * amount;

        qsa( '.amountProductWeight span', block ).forEach( span => {
            span.textContent = formatWeight( totalWeight );
        });
    }

    /**
     * Пересчитывает цены и вес всей корзины.
     *
     * @param  {Object<string, number>} actualPrices — актуальные цены по ID товара
     * @return {void}
     */
    #recountCart( actualPrices ) {

        let totalPrice  = 0;
        let totalWeight = 0;

        for ( const [ productId, price ] of Object.entries( actualPrices ) ) {

            const block = qs( `.blockCartProduct[data-productid="${productId}"]` );

            if ( ! block ) {
                continue;
            }

            const amountInput = qs( '.shoppingCartAmountProduct', block );
            const amount = parseNumber( amountInput ? amountInput.value : 0 ) || 0;

            // Обновляем цену за единицу (все элементы span, включая мобильный блок).
            qsa( '.productPrice span', block ).forEach( span => {
                span.textContent = formatPrice( price );
            });

            // Обновляем общую цену за количество этого товара.
            qsa( '.amountProductPrice span', block ).forEach( span => {
                span.textContent = formatPrice( price * amount );
            });

            // Вес за количество товара уже записан в span, суммируем его.
            const weightSpan = qs( '.amountProductWeight span', block );
            const weight = parseNumber( weightSpan ? weightSpan.textContent : 0 ) || 0;

            if ( weight ) {
                totalWeight += weight;
            }

            totalPrice += price * amount;
        }

        this.#updateCartTotals( totalPrice, totalWeight );
    }

    /**
     * Обновляет итоги корзины в интерфейсе.
     *
     * @param  {number} totalPrice  — итог
     * @param  {number} totalWeight — общий вес
     * @return {void}
     */
    #updateCartTotals( totalPrice, totalWeight ) {

        const totalPriceSpan  = qs( '.cartTotalPrice span' );
        const totalWeightSpan = qs( '.cartTotalWeight span' );
        const totalWeightBlock = qs( '.cartTotalWeight' );

        if ( totalPriceSpan ) {
            totalPriceSpan.textContent = formatPrice( totalPrice );
        }

        if ( totalWeight && totalWeightBlock && totalWeightSpan ) {
            totalWeightSpan.textContent = formatWeight( totalWeight );
            totalWeightBlock.style.display = 'block';
        } else if ( totalWeightBlock ) {
            totalWeightBlock.style.display = 'none';
        }
    }

    // ============================================================
    // СООБЩЕНИЯ ОБ ОШИБКАХ
    // ============================================================

    /**
     * Показывает сообщение об ошибке на карточке товара.
     *
     * @param  {string} productId — ID товара
     * @param  {string} message   — текст ошибки
     * @return {void}
     */
    #showError( productId, message ) {

        const block = qs( `.blockCartProduct[data-productid="${productId}"]` );

        if ( ! block ) {
            return;
        }

        const errorBlock = qs( '.shoppingCartError', block );

        if ( ! errorBlock ) {
            return;
        }

        const span = qs( 'span', errorBlock );

        if ( span ) {
            span.textContent = message;
        }

        errorBlock.style.display = 'block';
    }

    /**
     * Скрывает сообщение об ошибке на карточке товара.
     *
     * @param  {string} productId — ID товара
     * @return {void}
     */
    #hideError( productId ) {

        const block = qs( `.blockCartProduct[data-productid="${productId}"]` );

        if ( ! block ) {
            return;
        }

        const errorBlock = qs( '.shoppingCartError', block );

        if ( ! errorBlock ) {
            return;
        }

        const span = qs( 'span', errorBlock );

        if ( span ) {
            span.textContent = '';
        }

        errorBlock.style.display = 'none';
    }

    /**
     * Показывает сообщение об ошибке под формой.
     *
     * @param  {Element|null} form    — форма
     * @param  {string}       message — текст ошибки
     * @return {void}
     */
    #showFormError( form, message ) {

        if ( ! form ) {
            return;
        }

        const errorBlock = qs( '.submitError', form );

        if ( ! errorBlock ) {
            return;
        }

        const span = qs( 'span', errorBlock );

        if ( span ) {
            span.textContent = message;
        }

        errorBlock.style.display = 'block';
    }

    /**
     * Скрывает сообщение об ошибке под формой.
     *
     * @param  {Element|null} form — форма
     * @return {void}
     */
    #hideFormError( form ) {

        if ( ! form ) {
            return;
        }

        const errorBlock = qs( '.submitError', form );

        if ( errorBlock ) {
            errorBlock.style.display = 'none';
        }
    }
}

/**
 * Готовый синглтон модуля корзины.
 *
 * @type {ArsShoppingCart}
 */
export const arsCart = new ArsShoppingCart();