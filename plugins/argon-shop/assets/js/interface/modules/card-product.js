/**
 * ArgonShop — кнопки карточек товаров.
 *
 * @package ArgonShop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Обрабатывает кнопки добавления товара в корзину на карточках
 * товаров в каталоге, на главной, в сайдбаре и в результатах
 * поиска.
 *
 * Карточки товаров выводятся в нескольких местах сайта и содержат
 * две кнопки:
 *
 *   .card-inBascet — «В корзину»: добавление без перехода.
 *   .card-buy      — «Купить»: добавление и переход в корзину.
 *
 * После успешного добавления через «В корзину» блок с кнопками
 * скрывается, а вместо него показывается блок «Товар добавлен
 * в корзину» (.card-alreadyAdded), который содержит ссылку на
 * страницу корзины.
 *
 * ============================================================
 * DOM-ЭЛЕМЕНТЫ
 * ============================================================
 *
 * .card-inBascet[data-productid]      — кнопка «В корзину».
 * .card-buy[data-productid]           — кнопка «Купить».
 * .block-cardProductBascet            — блок с обеими кнопками.
 * .card-alreadyAdded                  — блок «Товар добавлен в корзину».
 * .card-addProductError               — блок ошибки на карточке.
 *
 * ============================================================
 * ИСПОЛЬЗОВАНИЕ
 * ============================================================
 *
 *     import { arsCardProduct } from './modules/card-product.js';
 *     arsCardProduct.init();
 *
 * Модуль безопасен для вызова на любой странице: если карточек
 * с кнопками нет, обработчики не сработают.
 */

'use strict';

import { addProductToCart } from './add-to-cart.js';
import { delegate } from '../../shared/dom.js';

/**
 * Модуль кнопок карточек товаров.
 */
class ArsCardProduct {

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

        this.#initialized = true;

        this.#bindAddToCart();
        this.#bindBuyNow();
    }

    /**
     * Кнопка «В корзину» на карточке.
     *
     * @return {void}
     */
    #bindAddToCart() {

        delegate( document, 'click', '.card-inBascet', async ( event, button ) => {

            const productId = button.dataset.productid;

            await addProductToCart( productId, 1, {
                onSuccess: () => this.#showAdded( button ),
                onError:   ( error ) => this.#showError( button, error ),
            });
        });
    }

    /**
     * Кнопка «Купить» на карточке.
     *
     * @return {void}
     */
    #bindBuyNow() {

        delegate( document, 'click', '.card-buy', async ( event, button ) => {

            const productId = button.dataset.productid;

            await addProductToCart( productId, 1, {
                onSuccess: () => this.#goToCart(),
                onError:   ( error ) => this.#showError( button, error ),
            });
        });
    }

    /**
     * Показывает блок «Товар добавлен в корзину».
     *
     * @param  {Element} button — нажатая кнопка
     * @return {void}
     */
    #showAdded( button ) {

        const buttonsBlock = button.closest( '.block-cardProductBascet' );

        if ( ! buttonsBlock ) {
            return;
        }

        const errorBlock  = buttonsBlock.nextElementSibling;
        const addedBlock  = buttonsBlock.parentElement
            ? buttonsBlock.parentElement.querySelector( '.card-alreadyAdded' )
            : null;

        if ( errorBlock && errorBlock.classList.contains( 'card-addProductError' ) ) {
            errorBlock.style.display = 'none';
            errorBlock.textContent = '';
        }

        buttonsBlock.style.display = 'none';

        if ( addedBlock ) {
            addedBlock.style.display = 'block';
        }
    }

    /**
     * Показывает блок ошибки на карточке.
     *
     * @param  {Element} button — нажатая кнопка
     * @param  {Error}   error  — объект ошибки
     * @return {void}
     */
    #showError( button, error ) {

        const buttonsBlock = button.closest( '.block-cardProductBascet' );

        if ( ! buttonsBlock ) {
            return;
        }

        const errorBlock = buttonsBlock.parentElement
            ? buttonsBlock.parentElement.querySelector( '.card-addProductError' )
            : null;

        if ( errorBlock ) {
            errorBlock.textContent = 'Ошибка при выполнении запроса';
            errorBlock.style.display = 'block';
        }
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
 * Готовый синглтон модуля кнопок карточек.
 *
 * @type {ArsCardProduct}
 */
export const arsCardProduct = new ArsCardProduct();