/**
 * ArgonShop — модуль страницы товара.
 *
 * @package ArgonShop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Управляет интерактивными элементами страницы товара:
 *
 *   1. Изменение количества товара (поле + кнопки +/-).
 *   2. Пересчёт оптовой цены при изменении количества.
 *   3. Кнопка «В корзину» — добавить товар без перехода.
 *   4. Кнопка «Купить» — добавить товар и открыть корзину.
 *   5. Добавление товара в историю просмотров (см. history.js).
 *
 * ============================================================
 * DOM-ЭЛЕМЕНТЫ
 * ============================================================
 *
 * #amountProduct           — поле количества.
 * .plusProduct-Page        — кнопка увеличения количества.
 * .minusProduct-Page       — кнопка уменьшения количества.
 *
 * #addShoppingCart         — кнопка «В корзину» (data-productid).
 * #productPageCartBuy      — кнопка «Купить» (data-productid).
 *
 * #howManyProducts         — блок «добавлено N единиц товара».
 * #howManyProducts span    — число единиц товара в корзине.
 * #addProductError         — блок ошибки добавления.
 *
 * .totalPrice              — контейнер итоговой суммы.
 * .totalPrice span         — итоговая сумма за выбранное количество.
 * #basePrice               — цена за единицу (без оптовой скидки).
 *
 * ============================================================
 * AJAX-ДЕЙСТВИЕ
 * ============================================================
 *
 * wholesalePrice_shoppingCart (type=get)
 *   Принимает: productID, amountProduct.
 *   Возвращает: число (итоговая цена) или строку "none".
 *
 * ============================================================
 * ИСПОЛЬЗОВАНИЕ
 * ============================================================
 *
 *     import { arsProduct } from './modules/product.js';
 *     arsProduct.init();
 *
 * Модуль безопасен для вызова на любой странице: если на странице
 * нет #addShoppingCart, ничего не выполняется.
 */

'use strict';

import { arsApi }              from '../core/api.js';
import { qs, delegate }        from '../../shared/dom.js';
import { parseNumber }         from '../../shared/format.js';
import { addProductToCart }    from './add-to-cart.js';
import { arsHistory }          from './history.js';

/**
 * Модуль страницы товара.
 */
class ArsProduct {

    /** @type {boolean} */
    #initialized = false;

    /**
     * Инициализация. Идемпотентна.
     *
     * @return {void}
     */
    init() {

        if ( this.#initialized ) {
            return;
        }

        // На странице товара всегда есть поле количества.
        // Если его нет — мы не на странице товара, выходим.
        if ( ! qs( '#amountProduct' ) ) {
            return;
        }

        this.#initialized = true;

        this.#bindAmountInput();
        this.#bindPlusMinus();
        this.#bindAddToCart();
        this.#bindBuyNow();

        this.#addToHistory();
        this.#updateWholesalePrice();
    }

    // ============================================================
    // ОБРАБОТЧИКИ СОБЫТИЙ
    // ============================================================

    /**
     * Изменение количества через поле ввода.
     *
     * @return {void}
     */
    #bindAmountInput() {

        const input = qs( '#amountProduct' );

        if ( ! input ) {
            return;
        }

        input.addEventListener( 'input', () => {

            let amount = parseInt( input.value, 10 );

            if ( ! amount || amount <= 0 || Number.isNaN( amount ) ) {
                amount = 1;
                input.value = 1;
            }

            this.#updateWholesalePrice();
        });
    }

    /**
     * Кнопки +/-.
     *
     * @return {void}
     */
    #bindPlusMinus() {

        delegate( document, 'click', '.plusProduct-Page', () => {

            const input = qs( '#amountProduct' );

            if ( ! input ) {
                return;
            }

            input.value = ( parseInt( input.value, 10 ) || 0 ) + 1;
            input.dispatchEvent( new Event( 'input', { bubbles: true } ) );
        });

        delegate( document, 'click', '.minusProduct-Page', () => {

            const input = qs( '#amountProduct' );

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
     * Кнопка «В корзину».
     *
     * @return {void}
     */
    #bindAddToCart() {

        delegate( document, 'click', '#addShoppingCart', async ( event, button ) => {

            const productId = button.dataset.productid;
            const input     = qs( '#amountProduct' );
            const amount    = input ? ( parseInt( input.value, 10 ) || 1 ) : 1;

            await addProductToCart( productId, amount, {
                onSuccess: ( data ) => this.#showAdded( productId, data ),
                onError:   () => this.#showAddError( 'Ошибка при выполнении запроса' ),
            });
        });
    }

    /**
     * Кнопка «Купить».
     *
     * @return {void}
     */
    #bindBuyNow() {

        delegate( document, 'click', '#productPageCartBuy', async ( event, button ) => {

            const productId = button.dataset.productid;
            const input     = qs( '#amountProduct' );
            const amount    = input ? ( parseInt( input.value, 10 ) || 1 ) : 1;

            await addProductToCart( productId, amount, {
                onSuccess: () => this.#goToCart(),
                onError:   () => this.#showAddError( 'Ошибка при выполнении запроса' ),
            });
        });
    }

    // ============================================================
    // ОПТОВАЯ ЦЕНА
    // ============================================================

    /**
     * Запрашивает у сервера актуальную цену за выбранное количество.
     *
     * Если скидок нет, сервер возвращает "none" — тогда сумма
     * считается локально как basePrice × amount.
     *
     * @return {Promise<void>}
     */
    async #updateWholesalePrice() {

        const input = qs( '#amountProduct' );
        const button = qs( '#addShoppingCart' );

        if ( ! input || ! button ) {
            return;
        }

        const productId = button.dataset.productid;
        const amount    = parseInt( input.value, 10 ) || 0;

        const totalPriceBlock = qs( '.totalPrice' );
        const totalPriceSpan  = qs( '.totalPrice span' );

        if ( ! totalPriceBlock || ! totalPriceSpan ) {
            return;
        }

        if ( amount <= 0 ) {
            totalPriceBlock.style.display = 'none';
            return;
        }

        try {

            const response = await arsApi.post( 'wholesalePrice_shoppingCart', {
                type:          'get',
                productID:     productId,
                amountProduct: amount,
            });

            let totalPrice;

            if ( response === 'none' || response === '' ) {

                // Скидок нет — считаем локально
                const basePriceSpan = qs( '#basePrice' );
                const basePrice = parseNumber( basePriceSpan ? basePriceSpan.textContent : 0 );

                totalPrice = basePrice * amount;

            } else {

                totalPrice = parseNumber( response );
            }

            totalPriceSpan.textContent = Number.isInteger( totalPrice )
                ? String( totalPrice )
                : totalPrice.toFixed( 2 );

            totalPriceBlock.style.display = 'block';

        } catch {
            totalPriceSpan.textContent = 'Ошибка на сервере';
        }
    }

    // ============================================================
    // ВСПОМОГАТЕЛЬНЫЕ
    // ============================================================

    /**
     * Добавляет товар в историю просмотров.
     *
     * @return {void}
     */
    #addToHistory() {

        const button = qs( '#addShoppingCart' );

        if ( ! button ) {
            return;
        }

        const productId = button.dataset.productid;

        if ( productId ) {
            arsHistory.add( productId );
        }
    }

    /**
     * Показывает блок «добавлено N единиц товара».
     *
     * @param  {string} productId — ID товара
     * @param  {Object} data      — ответ сервера
     * @return {void}
     */
    #showAdded( productId, data ) {

        const block     = qs( '#howManyProducts' );
        const blockSpan = qs( '#howManyProducts span' );
        const errorBlock = qs( '#addProductError' );

        if ( block && blockSpan ) {
            blockSpan.textContent = String( data.amountProduct );
            block.style.display = 'block';
        }

        if ( errorBlock ) {
            errorBlock.style.display = 'none';
            errorBlock.textContent = '';
        }
    }

    /**
     * Показывает блок ошибки добавления.
     *
     * @param  {string} message — текст ошибки
     * @return {void}
     */
    #showAddError( message ) {

        const errorBlock = qs( '#addProductError' );

        if ( ! errorBlock ) {
            return;
        }

        errorBlock.textContent = message;
        errorBlock.style.display = 'block';
    }

    /**
     * Переход на страницу корзины.
     *
     * @return {void}
     */
    #goToCart() {

        const cartUrl = window.arsWpAjax && window.arsWpAjax.cartUrl
            ? window.arsWpAjax.cartUrl
            : '';

        if ( cartUrl ) {
            window.location.href = cartUrl;
        }
    }
}

/**
 * Готовый синглтон модуля страницы товара.
 *
 * @type {ArsProduct}
 */
export const arsProduct = new ArsProduct();